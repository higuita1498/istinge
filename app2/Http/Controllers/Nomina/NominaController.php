<?php

namespace App\Http\Controllers\Nomina;

use App\Model\Nomina\NominaTipoContrato;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use App\Model\Nomina\Nomina;
use App\Model\Nomina\NominaPeriodos;
use App\Model\Nomina\NominaCuentasGeneralDetalle;
use App\Model\Nomina\NominaDetalleUno;
use App\Model\Nomina\Persona;
use App\Categoria;
use App\NumeracionFactura;
use App\Model\Nomina\NominaPreferenciaPago;
use App\Funcion;
use App\Jobs\PagarNomina;
use App\Mail\NominaEmitida;
use App\Mail\NominaLiquidada;
use App\Model\Nomina\NominaPrestacionSocial;
use App\PlanesNomina;
use App\SuscripcionNomina;
use Illuminate\Support\Facades\Storage;
use stdClass;
use App\SuscripcionPagoNomina;
use Illuminate\Support\Facades\Mail;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

include_once(app_path() . '/../public/Spout/Autoloader/autoload.php');

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use ZipArchive;

class NominaController extends Controller
{

    protected $nominaDianController;


    public function __construct(NominaDianController $nominaDianController)
    {
        $this->middleware('nomina');
        $this->nominaDianController = $nominaDianController;
    }

    public function index()
    {

        $usuario = auth()->user();
        $this->getAllPermissions($usuario->id);

        view()->share(['seccion' => 'nomina', 'title' => 'Nómina Electrónica', 'icon' => 'fas fa-users']);


        $periodos = Nomina::with('nominaperiodos', 'persona')
            ->where('fk_idempresa', $usuario->empresa)
            ->groupBy('periodo', 'year')
            ->get();

        $modoLectura = (object) $usuario->modoLecturaNomina();


        return view('nomina.index', compact('periodos', 'modoLectura'));
    }

    /**
     * Método de la vista de liquidar nomina.
     * recibe como parametros el periodo a consultar, el año a consultar y el tipo (0=mensual,1=quincena 1, 2=quincena 2)
     *
     * @return view
     */
    public function liquidar($periodo, $year, $editar = false, $tipo = null)
    {
        $usuario = auth()->user();
        $this->getAllPermissions($usuario->id);
        view()->share(['seccion' => 'nomina', 'title' => 'Liquidar Nómina', 'icon' => 'fas fa-dollar-sign']);

        $fechaActual = now();

        // if($fechaActual->year != $year){
        //     return back();
        // }

        $guiasVistas = [];


        /* >>> si la primer nomina recuperada en el get tiene 2 periodos si o si todas las nominas traidas de ese año y periodo deben ser
        quincenales <<< */

        // obtenemos la nomina de la fecha recibida
        
        $nominasG = Nomina::with('nominaperiodos')
                            ->where('year', $year)
                            ->where('periodo', $periodo)
                            ->where('fk_idempresa',  $usuario->empresa)
                            ->get();
        
        $nomina = $nominasG->random();

        if ($nomina->isPagado && !$editar) {
            return redirect()->route('nomina.confirmar', ['year' => $year, 'periodo' => $periodo]);
        }

        // obtenemos los periodos de la fecha recivida, se valida si almenos una de 3 nominas tiene 2 miniperiodos para asi mostrar opciones de quincenas
        $variosPeriodos = $nomina->nominaperiodos;
        if($variosPeriodos->count() <= 1){
             $nomina = $nominasG->random();
             $variosPeriodos = $nomina->nominaperiodos;
             if($variosPeriodos->count() <= 1){
                  $nomina = $nominasG->random();
                  $variosPeriodos = $nomina->nominaperiodos;
                   if($variosPeriodos->count() <= 1){
                        $nomina = $nominasG->random();
                        $variosPeriodos = $nomina->nominaperiodos;
                   }
             }
        }

        /* >>> si el tipo es igual a null las nominas con los periodos que traeremos serán del primer periodo, sea mensual o quincenal <<< */
        if (!isset($tipo)) {

            if ($periodo == $fechaActual->month) {
                $vPeriodo =  $fechaActual->day > 15 ? $variosPeriodos->last() : $variosPeriodos->first();
            } else {
                $vPeriodo = $variosPeriodos->first();
            }

            if (isset($vPeriodo)) {
                $tipo = $vPeriodo->periodo;
            } else {
                return back()->with('error', 'Error al general la nomina del mes ' . $periodo);
            }
        }

        //obtenemos las nominas del periodo actual y si tenemos un miniperiodo de la quincena nos traremos esa nomina con ese periodo.
        $nominas = Nomina::with([
            'persona.nomina_tipo_contrato',
            'prestacionesSociales',
            'nominaperiodos' => function ($query) use ($tipo) {
                $query->where('periodo', $tipo);
            }
        ])
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('fk_idempresa',  $usuario->empresa)
            ->where('estado_nomina', 1)
            ->where('emitida', '<>', 3)
            ->where('emitida', '<>', 6)
            ->get();


        // foreach($nominas as $nomina){
        //     foreach($nomina->periodos as $periodo){
        //         echo $periodo->fk_idnomina . "<br>";
        //     }
        // }
        $idNominas = $nominas->keyBy('id')->keys();
        $costoPeriodo = $this->nominaDianController->costoPeriodo($tipo, $idNominas);

        $categorias1 = Categoria::where('empresa',  $usuario->empresa)->where(
            'fk_catgral',
            3
        )->where('fk_nomcuenta_tipo', 7)->get();
        $categorias2 = Categoria::where('empresa',  $usuario->empresa)->whereIn(
            'fk_catgral',
            [5,10]
        )->where('fk_nomcuenta_tipo', 8)->get();
        $categorias3 = Categoria::where('nombre', 'AUXILIO DE CONECTIVIDAD')->first();
        $categorias4 = Categoria::where('empresa',  $usuario->empresa)->where(
            'fk_catgral',
            6
        )->where('fk_nomcuenta_tipo', 10)->get();
        $categorias5 = Categoria::where('nombre', 'PRESTAMO')->first();
        $categorias6 = Categoria::where('nombre', 'RETENCION EN LA FUENTE')->first();
        $preferencia = NominaPreferenciaPago::where('empresa',  $usuario->empresa)->first();


        $mensajePeriodo = $preferencia->periodo($periodo, $year, $tipo);
        //        return $mensajePeriodo;


        $nominaConfiguracionCalculos =  $usuario->empresaObj->nominaConfiguracionCalculos;

        foreach ($nominas as $key => $nomina) {
            if ($nomina->persona->is_liquidado) {
                $nominas[$key]->persona_liquidada = true;
                $nomina->persona->status = 1; 
            }else{
                $nominas[$key]->persona_liquidada = false;
            }
        }


        $empleados = $nominas->filter(function ($value, $key) use ($usuario) {
            if (
                $value->persona->nomina_tipo_contrato->codigo == 01 && $value->persona->status == 1 ||
                $value->persona->nomina_tipo_contrato->codigo == 02 && $value->persona->status == 1 ||
                $value->persona->nomina_tipo_contrato->codigo == 04 && $value->persona->status == 1 ||
                $value->persona->nomina_tipo_contrato->codigo == 18 && $value->persona->status == 1
                && $value->persona->fk_empresa ==  $usuario->empresa
            ) {
                return $value;
            }
        })->values();

        $pensionados = $nominas->filter(function ($value, $key) use ($usuario) {

            if (($value->persona->nomina_tipo_contrato->codigo == 54 || $value->persona->nomina_tipo_contrato->codigo == 56)
                && $value->persona->fk_empresa == $usuario->empresa
            ) {
                return $value;
            }
        })->values();


        $contratados = $nominas->filter(function ($value, $key) use ($usuario) {
            if (($value->persona->nomina_tipo_contrato->codigo == 22 ||
                    $value->persona->nomina_tipo_contrato->codigo == 30 ||
                    $value->persona->nomina_tipo_contrato->codigo == 31 ||
                    $value->persona->nomina_tipo_contrato->codigo == 47)
                && $value->persona->fk_empresa ==  $usuario->empresa
            ) {
                return $value;
            }
        })->values();


        $estudiantes = $nominas->filter(function ($value, $key) use ($usuario) {
            if (($value->persona->nomina_tipo_contrato->codigo == 21 ||
                    $value->persona->nomina_tipo_contrato->codigo == 23 ||
                    $value->persona->nomina_tipo_contrato->codigo == 58)
                && $value->persona->fk_empresa ==  $usuario->empresa
            ) {
                return $value;
            }
        })->values();


        $aprendices = $nominas->filter(function ($value, $key) use ($usuario) {
            if (($value->persona->nomina_tipo_contrato->codigo == 12 || $value->persona->nomina_tipo_contrato->codigo == 19)
                && $value->persona->fk_empresa ==  $usuario->empresa
            ) {
                return $value;
            }
        })->values();

        $modoLectura = (object) $usuario->modoLecturaNomina();
        $guiasVistas = [];

        return view(
            'nomina.liquidar',
            [
                'guiasVistas' => $guiasVistas,
                'nominas' => $nominas,
                'moneda' =>  $usuario->empresaObj->moneda,
                'categorias1' => $categorias1,
                'categorias2' => $categorias2,
                'categorias3' => $categorias3,
                'categorias4' => $categorias4,
                'categorias5' => $categorias5,
                'categorias6' => $categorias6,
                'preferencia' => $preferencia,
                'periodo' => $periodo,
                'year' => $year,
                'tipo' => $tipo,
                'variosPeriodos' => $variosPeriodos,
                'mensajePeriodo' => $mensajePeriodo,
                'costoPeriodo' => $costoPeriodo,
                'idNominas' => $idNominas,
                'nominaConfiguracionCalculos' => $nominaConfiguracionCalculos,
                'contratados' => $contratados,
                'empleados' => $empleados,
                'aprendices' => $aprendices,
                'pensionados' => $pensionados,
                'estudiantes' => $estudiantes,
                'modoLectura' => $modoLectura
            ]
        );
    }

    public function validarPersonasPeriodo($periodo, $year){

        $personas = Persona::where('fecha_contratacion', '<=', $year.'-'.$periodo.'-31')
                            ->where('status', 1)
                            ->where('fk_empresa', auth()->user()->empresa)
                            ->get();
       
        foreach($personas as $p){
            $tipoContrato = NominaTipoContrato::find($p->fk_tipo_contrato);
            $data = app(PersonasController::class)->nominaPersona(
                $p,
                $year,
                $periodo,
                $tipoContrato
            );
        }

        return back();
    }


    public function enviarNominaLiquidada(Nomina $nomina, NominaPeriodos $periodo)
    {
        $nomina->load(['persona:id,nombre,apellido,nro_documento,correo', 'empresa:id,nombre,nit', 'nominaperiodos']);

        $empresa = auth()->user()->empresaObj;


        Storage::disk('public')->deleteDirectory("empresa{$empresa->id}/nominas/reporte");

        $fileName = "nomina-{$nomina->persona->nro_documento}.pdf";

        $response =  $this->details_pdf($periodo->id);

        Storage::disk('public')->put("empresa{$empresa->id}/nominas/reporte/{$fileName}", $response);

        $pdf = "/empresa{$empresa->id}/nominas/reporte/{$fileName}";


        Mail::to($nomina->persona->correo)
            ->queue(new NominaLiquidada($nomina, $empresa, $pdf));


        return back()->with('success', 'Se ha enviado la nómina por correo con éxito');
    }




    public function correoEmicionNomina(Nomina $nomina)
    {
        try {

            $nomina->load(['persona:id,nombre,apellido,nro_documento,correo', 'empresa:id,nombre,nit', 'nominaperiodos']);

            $empresa = auth()->user()->empresaObj;


            Storage::disk('public')->deleteDirectory("empresa{$empresa->id}/nominas/reporte");

            $fileName = "nomina-{$nomina->persona->nro_documento}.pdf";

            $response =  $this->generarPDFNominaCompleta($nomina);



            Storage::disk('public')->put("empresa{$empresa->id}/nominas/reporte/{$fileName}", $response);

            $pdf = "/empresa{$empresa->id}/nominas/reporte/{$fileName}";


            Mail::to($nomina->persona->correo)
                ->queue(new NominaEmitida($nomina, $empresa, $pdf));


            return back()->with('success', 'Se ha enviado la nómina por correo con éxito');
        } catch (\Throwable $th) {
            return back()->withErrors([$th->getMessage()]);
        }
    }




    public function ajustar($periodo, $year, $persona, $tipo = null)
    {
        
        $this->getAllPermissions(Auth::user()->id);
        $tipoPeriodo = $tipo;
        $empresa = Auth::user()->empresa;

        $guiasVistas = [];


        $variosPeriodos = Nomina::where('year', $year)
            ->where('periodo', $periodo)
            ->where('fk_idempresa', $empresa)
            ->first()
            ->nominaperiodos;

        if ($tipo == null) {
            $vPeriodo = $variosPeriodos->first();
            if ($vPeriodo) {
                $tipo = $vPeriodo->periodo;
            } else {
                return back()->with('error', 'Error al general la nomina del mes ' . $periodo);
            }
        }

        /* >>> Obtenemos la nómina que se le va a realizar un ajuste. <<< */
        $nomina = Nomina::with(['persona', 'prestacionesSociales'])
            ->with([
                'nominaperiodos' => function ($query) use ($tipo) {
                    $query->where('periodo', $tipo);
                }
            ])
            ->where('fk_idpersona', $persona)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('fk_idempresa', $empresa)
            ->first();

        /* >>> Si es la primera vez que ingresa a editar esta nomina creara una copia <<< */
        if ($tipoPeriodo == null) {

            $datosNomina = Nomina::with('persona', 'prestacionesSociales', 'nominaperiodos')
                ->where('fk_idpersona', $persona)
                ->where('ne_nomina.year', $year)
                ->where('ne_nomina.periodo', $periodo)
                ->where('fk_idempresa', $empresa)
                ->first();

            if($datosNomina->cune == null || $datosNomina->cune == ""){
                return redirect()->back();
            }
            
            $numeracionPreferida = NumeracionFactura::where('nomina', 1)
            ->where('preferida', 1)
            ->where('empresa', auth()->user()->empresa)
            ->where('tipo_nomina',2)
            ->orderByDesc('id')
            ->first();

            if(!$numeracionPreferida){
                return back()->with('error', 'No existe una numeración activa preferida');
            }

            $nominaReplicaId = $this->crearCopiaNominaEstadoInactiva($datosNomina);

            /* >>> Obtenemos la nómina replicada <<< */
            $nomina = Nomina::with(['persona', 'prestacionesSociales'])
                ->with([
                    'nominaperiodos' => function ($query) use ($tipo) {
                        $query->where('periodo', $tipo);
                    }
                ])
                ->where('id', $nominaReplicaId)
                ->first();
        } else {

            /* >>> Aqui entrara en caso de que  usa la seccion 1- Elige la quincena que deseas editar <<< */
            $nomina = Nomina::with(['persona', 'prestacionesSociales'])
                ->with([
                    'nominaperiodos' => function ($query) use ($tipo) {
                        $query->where('periodo', $tipo);
                    }
                ])
                ->where('fk_idpersona', $persona)
                ->where('ne_nomina.year', $year)
                ->where('ne_nomina.periodo', $periodo)
                ->where('fk_idempresa', $empresa)
                ->where('estado_nomina', 0)
                ->first();
        }


        view()->share([
            'seccion' => 'nomina',
            'title' => "Ajuste de Nómina {$nomina->persona->nombre()}",
            'icon' => 'fas fa-dollar-sign'
        ]);


        $idNomina = $nomina->id;

        $categorias1 = Categoria::where('empresa', $empresa)
            ->where('fk_catgral', 3)
            ->where('fk_nomcuenta_tipo', 7)
            ->get();

        $categorias2 = Categoria::where('empresa', $empresa)
            ->whereIn('fk_catgral', [5,10])
            ->where('fk_nomcuenta_tipo', 8)
            ->get();

        $categorias3 = Categoria::where('nombre', 'AUXILIO DE CONECTIVIDAD')->first();

        $categorias4 = Categoria::where('empresa', $empresa)
            ->where('fk_catgral', 6)
            ->where('fk_nomcuenta_tipo', 10)
            ->get();

        $categorias5 = Categoria::where('nombre', 'PRESTAMO')->first();
        $categorias6 = Categoria::where('nombre', 'RETENCION EN LA FUENTE')->first();


        $preferencia = NominaPreferenciaPago::where('empresa', $empresa)->first();

        $costoPeriodo = $this->nominaDianController->costoPeriodo($tipo, [$idNomina]);
        $tipoContrato = $nomina->persona->nomina_tipo_contrato->nombre;
        $mensajePeriodo = $preferencia->periodo($periodo, $year, $tipo);
        $nominaConfiguracionCalculos = Auth::user()->empresaObj->nominaConfiguracionCalculos;


        return view(
            'nomina.ajustar',
            [
                'guiasVistas' => $guiasVistas,
                'nomina' => $nomina,
                'moneda' => Auth::user()->empresaObj->moneda,
                'categorias1' => $categorias1,
                'categorias2' => $categorias2,
                'categorias3' => $categorias3,
                'categorias4' => $categorias4,
                'categorias5' => $categorias5,
                'categorias6' => $categorias6,
                'preferencia' => $preferencia,
                'periodo' => $periodo,
                'year' => $year,
                'tipo' => $tipo,
                'variosPeriodos' => $variosPeriodos,
                'mensajePeriodo' => $mensajePeriodo,
                'costoPeriodo' => $costoPeriodo,
                'idNomina' => $idNomina,
                'nominaConfiguracionCalculos' => $nominaConfiguracionCalculos,
                'tipoContrato' => $tipoContrato,
                'persona' => $persona
            ]
        );

        //        return  $nomina->persona->tipo_contrato;
    }


    protected function crearCopiaNominaEstadoInactiva(Nomina $nomina)
    {

        /* >>> Usamos uso del DB::transacition ejecutar un conjunto de operaciones dentro de una transacción de base de datos. 
         Si se lanza una excepción dentro del cierre de la transacción, la transacción se revertirá automáticamente <<< */
        $respuesta = DB::transaction(function () use ($nomina) {


            $numeracion = NumeracionFactura::where('nomina', 1)
                ->where('preferida', 1)
                ->where('empresa', auth()->user()->empresa)
                ->orderByDesc('id')
                ->first();

            /*>>> Hacemos uso del metodo replicate()->fill para replicar un modelo y le pasamos los 
            nuevos atributos que cambiaran del modelo anterior <<<*/
            $nominaBorrador = $nomina->replicate()->fill(['estado_nomina' => 0, 'tipo' => 2, 'cune_relacionado' => $nomina->cune]);
            $nominaBorrador->codigo_dian = $numeracion->prefijo . $numeracion->inicio;
            $nominaBorrador->cune_relacionado = $nomina->cune;
            $numeracion->inicio = $numeracion->inicio + 1;

            $numeracion->save();
            $nominaBorrador->save();

            foreach ($nomina->nominaperiodos as $nominaperiodo) {

                /*>>> Replicamos los periodos de la nominaperiodo <<<*/
                $nominaperiodoCreada = $nominaperiodo->replicate()->fill(['fk_idnomina' => $nominaBorrador->id]);
                $nominaperiodoCreada->save();

                /* >>> obtenemos los detalleas de la nominaperiodo <<< */
                $nominasDetalle = NominaDetalleUno::where('fk_nominaperiodo', $nominaperiodo->id)->get();

                /* >>> replicamos los detalles de la nominaperiodo y cambiuamos su atributo fk_nominaperiodo <<< */
                foreach ($nominasDetalle as $detalle) {
                    $detalle->replicate()
                        ->fill(['fk_nominaperiodo' => $nominaperiodoCreada->id])
                        ->save();
                }
            }

            return $nominaBorrador->id;
        });

        return $respuesta;
    }

    public function ajustarEstadoNominas($periodo, $year, $persona, $nomina)
    {
        $nominaNueva = Nomina::findOrFail($nomina);
        $nominaNueva->estado_nomina = 1;
        $nominaNueva->emitida = 4;
        $nominaNueva->save();

        $nominaAnterior = Nomina::where('year', $year)
            ->where('periodo', $periodo)
            ->where('fk_idpersona', $persona)
            ->where('emitida', 1)
            ->first();

        $nominaAnterior->emitida = 3;
        $nominaAnterior->save();

        return redirect()->route('nomina-dian.emitir', ['year' => $year, 'periodo' => $periodo]);
    }


    public function agregarObservacion(Request $request)
    {
        $nomina = Nomina::findOrFail($request->nomina);
        $nomina->nota = $request->observ;
        $nomina->save();

        return $nomina;
    }

    public function agregarObservacionPeriodo(Request $request)
    {

        $nominaPeriodo = NominaPeriodos::findOrFail($request->id);
        $nominaPeriodo->observaciones = $request->observacion;
        $nominaPeriodo->save();

        return response()->json(['ok' => true, 'observacion' => $nominaPeriodo->observaciones]);
    }

    public function traerObservacion(Request $request)
    {
        $nomina = Nomina::findOrFail($request->nomina);
        return $nomina->nota;
    }

    public function confirmar()
    {
        $request = request();
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['seccion' => 'nomina', 'title' => 'liquidar nómina', 'icon' => 'far fa-money-bill-alt']);


        Nomina::where('fk_idempresa', Auth::user()->empresa)
            ->where('year', $request->year)
            ->where('periodo', $request->periodo)
            ->update(['isPagado' => true]);

        $nomina = Nomina::with('nominaperiodos')
            ->where('year', $request->year)
            ->where('periodo', $request->periodo)
            ->where('fk_idempresa', auth()->user()->empresa)
            ->first();

        if (!$nomina) {
            return back()->with('error', 'No se ha encontrado la nómina con los datos proporcionados');
        }


        // obtenemos los periodos de la fecha recivida
        $variosPeriodos = $nomina->nominaperiodos;
        $fechaActual = now();


        /* >>> si el tipo es igual a null las nominas con los periodos que traeremos serán del primer periodo, sea mensual o quincenal <<< */
        if (!isset($tipo)) {

            if ($request->periodo == $fechaActual->month) {
                $vPeriodo =  $fechaActual->day > 15 ? $variosPeriodos->last() : $variosPeriodos->first();
            } else {
                $vPeriodo = $variosPeriodos->first();
            }

            if (isset($vPeriodo)) {
                $tipo = $vPeriodo->periodo;
            } else {
                return back()->with('error', 'Error al general la nomina del mes ' . $periodo);
            }
        }

        if (!$request->periodo_quincenal) {
            $miniPeriodo = $tipo;
        } else {
            $miniPeriodo = $request->periodo_quincenal;
            $tipo = $request->periodo_quincenal;
        }


        $nominas = Nomina::where('fk_idempresa', Auth::user()->empresa)
            ->where('year', $request->year)
            ->where('periodo', $request->periodo)
            ->get();

        foreach ($nominas as $nomina) {
            foreach ($nomina->nominaperiodos as $periodo) {
                if ($periodo->periodo == $miniPeriodo) {
                    $periodo->isPagado = true;
                    $periodo->update();
                }
            }
        }

        $preferencia = NominaPreferenciaPago::where('empresa', auth()->user()->empresa)->first();


        $mensajePeriodo = $preferencia->periodo($request->periodo, $request->year, $miniPeriodo);

        return view('nomina.confirmar', ['request' => $request, 'mensajePeriodo' => $mensajePeriodo, 'tipo' => $tipo]);
    }

    public function historialPeriodos(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $empresa = Auth::user()->empresa;

        $historialNomina =    Nomina::join('ne_nomina_periodos as np', 'ne_nomina.id', '=', 'np.fk_idnomina')
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('np.isPagado', 1)
            ->select(
                'np.id',
                'np.fecha_desde',
                'np.fecha_hasta',
                DB::raw('(select SUM(case when np.mini_periodo != 1 then np.pago_empleado / 2 else np.pago_empleado end)) as pagoEmpleado'),
                DB::raw('(select count(np.id)) as numeroNominas'),
                DB::raw('(select SUM(np.valor_total)) as costo_total')
            );

        $historialPrestaciones = Nomina::join('ne_nomina_periodos as np', 'ne_nomina.id', '=', 'np.fk_idnomina')
            ->join('ne_nomina_prestaciones_sociales as ne_prestacion', 'ne_prestacion.fk_idnomina', '=', 'ne_nomina.id')
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('np.isPagado', 1)
            ->select(
                'np.id',
                'np.created_at',
                DB::raw('(select SUM(ne_prestacion.valor_pagar)) as prestacionValor'),
                DB::raw('(select SUM(ne_prestacion.valor_pagar)) as costo_total_prestacion')
            );

        $historialDetallado = Nomina::join('ne_nomina_periodos as np', 'ne_nomina.id', '=', 'np.fk_idnomina')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', '=', 'np.id')
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('np.isPagado', 1)
            ->select('np.id')
            ->selectRaw('
                SUM(case when ncd.fk_nomina_cuenta_tipo in (1,2,3,4,5,6,7,8,9) then ncd.valor_categoria else 0 end) as otrosPagos
            ')
            ->selectRaw('
                SUM(case when ncd.fk_nomina_cuenta_tipo in (10,11,12) then ncd.valor_categoria else 0 end) as deduccPrestRet
        ');

        /* >>> Si no establecemos fecha mostramos los dos ultimos periodos en el historial de la nomina <<< */
        if (!isset($request->fecha_inicial)) {

            $historialNomina = $historialNomina->orderBy('np.fecha_desde', 'desc')->groupBy('np.fecha_desde')->limit(6)->get();
            $historialPrestaciones = $historialPrestaciones->orderBy('np.fecha_desde', 'desc')->groupBy('np.fecha_desde')->limit(6)->get();
            $historialDetallado = $historialDetallado->orderBy('np.fecha_desde', 'desc')->groupBy('np.fecha_desde')->limit(6)->get();
        } else {

            $historialNomina = $historialNomina
                ->where('np.fecha_desde', '>=', $request->fecha_inicial)
                ->where('np.fecha_hasta', '<=', $request->fecha_final)
                ->orderBy('np.fecha_desde')->groupBy('np.fecha_desde')->get();

            $historialPrestaciones = $historialPrestaciones
                ->where('np.fecha_desde', '>=', $request->fecha_inicial)
                ->where('np.fecha_hasta', '<=', $request->fecha_final)
                ->orderBy('np.fecha_desde')->groupBy('np.fecha_desde')->get();

            $historialDetallado = $historialDetallado
                ->where('np.fecha_desde', '>=', $request->fecha_inicial)
                ->where('np.fecha_hasta', '<=', $request->fecha_final)
                ->orderBy('np.fecha_desde')->groupBy('np.fecha_desde')->get();
        }

        //Creamos la coleccion la cual contendrá el detalle de cada factura ocmo lo requiere el softland.
        $historial = collect();

        foreach ($historialNomina as $h1) {

            $detalleClass = new stdClass();
            $detalleClass->pagoEmpleado = $h1->pagoEmpleado;
            $detalleClass->numeroNominas  = $h1->numeroNominas;
            $detalleClass->fecha_desde = $h1->fecha_desde;
            $detalleClass->fecha_hasta = $h1->fecha_hasta;
            $detalleClass->costo_total = $h1->costo_total;
            $detalleClass->otrosPagos = 0;
            $detalleClass->prestacionValor = 0;
            $detalleClass->deduccPrestRet = 0;
            $detalleClass->costo_total_prestacion = 0;

            foreach ($historialDetallado as $h2) {
                if ($h1->id == $h2->id) {
                    $detalleClass->otrosPagos = $h2->otrosPagos;
                    $detalleClass->deduccPrestRet = $h2->deduccPrestRet;
                }
            }


            foreach ($historialPrestaciones as $h3) {
                if ($h1->id == $h3->id) {
                    if(strtotime($detalleClass->fecha_hasta) <= strtotime($h3->created_at)){
                        $detalleClass->prestacionValor = $h3->prestacionValor;
                        $detalleClass->costo_total += $h3->costo_total_prestacion;
                    }
                }
            }

            $historial->push($detalleClass);
        }

        view()->share(['seccion' => 'nomina', 'title' => 'Historial de periodos', 'icon' => 'fas fa-history']);

        /*>>> Obetner los distintos periodos de una empresa que se han facturado <<<*/
        $rangos = NominaPeriodos::rangosFechas();

        return view('nomina.historial-periodos', compact('rangos', 'historial'));
    }

    public function contabilidad()
    {
        $this->getAllPermissions(Auth::user()->id);

        view()->share(['seccion' => 'nomina', 'title' => 'Contabilidad', 'icon' => 'fas fa-calculator']);

        return view('nomina.contabilidad');
    }


    public function informeNovedades($periodo, $year, $tipo = null)
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['seccion' => 'nomina', 'title' => 'Novedades', 'icon' => 'fas fa-comments-dollar']);

        if ($tipo == null) {
            $variosPeriodos = Nomina::with('nominaperiodos')->where('year', $year)->where(
                'periodo',
                $periodo
            )->first()->nominaperiodos;
            $tipo = $variosPeriodos->first()->periodo;
        }

        $empresa = Auth::user()->empresa;

        /* >>>
        manera de obtener las novedades de la sección de vacaciones
        (solamente se obtienen personas que han obtenido novedades en esas fechas)
        <<< */
        $seccion_vacaciones = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [4, 5, 6])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.fecha_inicio', '!=', null)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATEDIFF(ncd.fecha_fin, ncd.fecha_inicio) + 1 as num_dias')
            ->get();

        /* >>>
       manera de obtener las novedades de la sección de extras y recargos
       (solamente se obtienen personas que han obtenido novedades en esas fechas)
       <<< */

        $seccion_extrasRecargos = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [1, 2, 3])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.numero_horas', '!=', 0)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATE_FORMAT(ncd.updated_at, "%Y/%m/%d") as fecha_registro')
            ->get();

        /* >>>
       manera de obtener las novedades de la sección de constitutivos de salario (SECCION DE INGRESOS ADICIONALES)
       (solamente se obtienen personas que han obtenido novedades en esas fechas)
       <<< */

        $seccion_constitutivos = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [7])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.valor_categoria', '!=', 0)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATE_FORMAT(ncd.updated_at, "%Y/%m/%d") as fecha_registro')
            ->get();

        /* >>>
        manera de obtener las novedades de la sección de NO constitutivos de salario (SECCION DE INGRESOS ADICIONALES)
        (solamente se obtienen personas que han obtenido novedades en esas fechas)
        <<< */

        $seccion_noConstitutivos = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [8])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.valor_categoria', '!=', 0)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATE_FORMAT(ncd.updated_at, "%Y/%m/%d") as fecha_registro')
            ->get();

        /* >>>
       manera de obtener las novedades de la sección Deducciones, prestamos y retefuente
       (solamente se obtienen personas que han obtenido novedades en esas fechas)
       <<< */

        $seccion_deducciones = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [10, 11, 12])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.valor_categoria', '!=', 0)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATE_FORMAT(ncd.updated_at, "%Y/%m/%d") as fecha_registro')
            ->get();

        $preferencia = NominaPreferenciaPago::where('empresa', auth()->user()->empresa)->first();
        $mensajePeriodo = $preferencia->periodo($periodo, $year, $tipo);

        return view(
            'nomina.informe-novedades',
            [
                'periodo' => $periodo,
                'year' => $year,
                'tipo' => $tipo,
                'mensajePeriodo' => $mensajePeriodo,
                // 'nominas'      => $nominas,
                'seccion_vacaciones' => $seccion_vacaciones,
                'seccion_extrasRecargos' => $seccion_extrasRecargos,
                'seccion_constitutivos' => $seccion_constitutivos,
                'seccion_noConstitutivos' => $seccion_noConstitutivos,
                'seccion_deducciones' => $seccion_deducciones
            ]
        );
    }

    public function exportarinformeNovedades($periodo, $year, $tipo = null)
    {
        /* >>>
        Obtenemos la data a mostrar en el Excel
        <<< */
        $empresa = Auth::user()->empresa;

        $seccion_vacaciones = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [4, 5, 6])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.fecha_inicio', '!=', null)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATEDIFF(ncd.fecha_fin, ncd.fecha_inicio) + 1 as num_dias')
            ->get();

        $seccion_extrasRecargos = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [1, 2, 3])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.numero_horas', '!=', 0)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATE_FORMAT(ncd.updated_at, "%Y/%m/%d") as fecha_registro')
            ->get();

        $seccion_constitutivos = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [7])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.valor_categoria', '!=', 0)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATE_FORMAT(ncd.updated_at, "%Y/%m/%d") as fecha_registro')
            ->get();

        $seccion_noConstitutivos = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [8])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.valor_categoria', '!=', 0)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATE_FORMAT(ncd.updated_at, "%Y/%m/%d") as fecha_registro')
            ->get();

        $seccion_deducciones = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
            ->join('ne_personas as nper', 'nper.id', 'ne_nomina.fk_idpersona')
            ->join('ne_nomina_cuentas_detalle as ncd', 'ncd.fk_nominaperiodo', 'np.id')
            ->whereIn('ncd.fk_nomina_cuenta_tipo', [10, 11, 12])
            ->where('ne_nomina.fk_idempresa', $empresa)
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('np.periodo', $tipo)
            ->where('ncd.valor_categoria', '!=', 0)
            ->select('ncd.*', 'nper.nombre as nombrePersona', 'nper.apellido', 'nper.id as idpersona')
            ->selectRaw('DATE_FORMAT(ncd.updated_at, "%Y/%m/%d") as fecha_registro')
            ->get();

        /* >>>
        Armado del Excel
        <<< */
        $objPHPExcel = new PHPExcel();
        $preferencia = NominaPreferenciaPago::where('empresa', auth()->user()->empresa)->first();

        $tituloReporte = "Reporte de Novedades " . $preferencia->periodo($periodo, $year, $tipo);
        $titulosColumnas = array('Nombre', 'Tipo', 'Inicio', 'Fin', 'Cantidad', 'Valor');
        $letras = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z'
        );

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
            ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modificó
            ->setTitle("Reporte Excel Novedades") // Titulo
            ->setSubject("Reporte Excel Novedades") //Asunto
            ->setDescription("Reporte de Novedades") //Descripción
            ->setKeywords("reporte Novedades") //Etiquetas
            ->setCategory("Reporte excel"); //Categorias

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:F1');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $tituloReporte);

        $estiloA = array(
            'font' => array('bold' => true, 'size' => 12, 'name' => 'Times New Roman'),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($estiloA);

        $estiloB = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'd08f50')
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:F3')->applyFromArray($estiloB);

        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        /* >>>
        Escribimos el archivo
        <<< */
        $i = 4;
        foreach ($seccion_vacaciones as $vacacion) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0] . $i, $vacacion->nombrePersona . " " . $vacacion->apellido)
                ->setCellValue($letras[1] . $i, $vacacion->nombre)
                ->setCellValue($letras[2] . $i, date('d-m-Y', strtotime($vacacion->fecha_inicio)))
                ->setCellValue($letras[3] . $i, date('d-m-Y', strtotime($vacacion->fecha_fin)))
                ->setCellValue($letras[4] . $i, $vacacion->num_dias)
                ->setCellValue(
                    $letras[5] . $i,
                    Auth::user()->empresaObj->moneda . " " . Funcion::Parsear($vacacion->valor_categoria)
                );
            $i++;
        }

        $estiloC = array(
            'font' => array('size' => 12, 'name' => 'Times New Roman'),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:F' . $i)->applyFromArray($estiloC);


        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(true);
        }

        /* >>>
        Se asigna el nombre a la hoja
        <<< */
        $objPHPExcel->getActiveSheet()->setTitle('Vacac. Incap. y Licen.');

        /* >>>
        Inmovilizar paneles
        <<< */
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 4);

        /* >>>
        Creando nueva hoja
        <<< */
        $objPHPExcel->createSheet();

        /* >>>
        Seleccionamos la hoja a trabajar
        <<< */
        $objPHPExcel->setActiveSheetIndex(1);

        $titulosColumnas = array('Nombre', 'Tipo', 'Cantidad', 'Fecha', 'Valor');

        $objPHPExcel->setActiveSheetIndex(1)->mergeCells('A1:E1');
        $objPHPExcel->setActiveSheetIndex(1)->setCellValue('A1', $tituloReporte);

        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($estiloA);

        $objPHPExcel->getActiveSheet()->getStyle('A3:E3')->applyFromArray($estiloB);

        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(1)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        /* >>>
        Escribimos el archivo
        <<< */
        $i = 4;
        foreach ($seccion_extrasRecargos as $extra) {
            $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue($letras[0] . $i, $extra->nombrePersona . " " . $extra->apellido)
                ->setCellValue($letras[1] . $i, $extra->nombre)
                ->setCellValue($letras[2] . $i, $extra->numero_horas)
                ->setCellValue($letras[3] . $i, date('d-m-Y', strtotime($extra->fecha_registro)))
                ->setCellValue(
                    $letras[4] . $i,
                    Auth::user()->empresaObj->moneda . " " . Funcion::Parsear($extra->valor_categoria)
                );
            $i++;
        }

        $objPHPExcel->getActiveSheet()->getStyle('A3:E' . $i)->applyFromArray($estiloC);

        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(1)->getColumnDimension($i)->setAutoSize(true);
        }

        /* >>>
        Se asigna el nombre a la hoja
        <<< */
        $objPHPExcel->getActiveSheet()->setTitle('Extras y Recargos');

        /* >>>
        Inmovilizar paneles
        <<< */
        $objPHPExcel->getActiveSheet(1)->freezePane('A2');
        $objPHPExcel->getActiveSheet(1)->freezePaneByColumnAndRow(0, 4);

        /* >>>
        Creando nueva hoja
        <<< */
        $objPHPExcel->createSheet();

        /* >>>
        Seleccionamos la hoja a trabajar
        <<< */
        $objPHPExcel->setActiveSheetIndex(2);

        $titulosColumnas = array('Nombre', 'Tipo', 'Fecha', 'Valor');

        $objPHPExcel->setActiveSheetIndex(2)->mergeCells('A1:D1');
        $objPHPExcel->setActiveSheetIndex(2)->setCellValue('A1', $tituloReporte);

        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estiloA);

        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($estiloB);

        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(2)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        /* >>>
        Escribimos el archivo
        <<< */
        $i = 4;
        foreach ($seccion_constitutivos as $constitutivo) {
            $objPHPExcel->setActiveSheetIndex(2)
                ->setCellValue($letras[0] . $i, $constitutivo->nombrePersona . " " . $constitutivo->apellido)
                ->setCellValue($letras[1] . $i, $constitutivo->nombre)
                ->setCellValue($letras[2] . $i, date('d-m-Y', strtotime($constitutivo->fecha_registro)))
                ->setCellValue(
                    $letras[3] . $i,
                    Auth::user()->empresaObj->moneda . " " . Funcion::Parsear($constitutivo->valor_categoria)
                );
            $i++;
        }

        $objPHPExcel->getActiveSheet()->getStyle('A3:D' . $i)->applyFromArray($estiloC);

        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(2)->getColumnDimension($i)->setAutoSize(true);
        }

        /* >>>
        Se asigna el nombre a la hoja
        <<< */
        $objPHPExcel->getActiveSheet()->setTitle('Ingresos Constitutivos');

        /* >>>
        Inmovilizar paneles
        <<< */
        $objPHPExcel->getActiveSheet(2)->freezePane('A2');
        $objPHPExcel->getActiveSheet(2)->freezePaneByColumnAndRow(0, 4);

        /* >>>
        Creando nueva hoja
        <<< */
        $objPHPExcel->createSheet();

        /* >>>
        Seleccionamos la hoja a trabajar
        <<< */
        $objPHPExcel->setActiveSheetIndex(3);

        $titulosColumnas = array('Nombre', 'Tipo', 'Fecha', 'Valor');

        $objPHPExcel->setActiveSheetIndex(3)->mergeCells('A1:D1');
        $objPHPExcel->setActiveSheetIndex(3)->setCellValue('A1', $tituloReporte);

        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estiloA);
        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($estiloB);

        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(3)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        /* >>>
        Escribimos el archivo
        <<< */
        $i = 4;
        foreach ($seccion_noConstitutivos as $no_constitutivo) {
            $objPHPExcel->setActiveSheetIndex(3)
                ->setCellValue($letras[0] . $i, $no_constitutivo->nombrePersona . " " . $no_constitutivo->apellido)
                ->setCellValue($letras[1] . $i, $no_constitutivo->nombre)
                ->setCellValue($letras[2] . $i, date('d-m-Y', strtotime($no_constitutivo->fecha_registro)))
                ->setCellValue(
                    $letras[3] . $i,
                    Auth::user()->empresaObj->moneda . " " . Funcion::Parsear($no_constitutivo->valor_categoria)
                );
            $i++;
        }

        $objPHPExcel->getActiveSheet()->getStyle('A3:D' . $i)->applyFromArray($estiloC);

        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(3)->getColumnDimension($i)->setAutoSize(true);
        }

        /* >>>
        Se asigna el nombre a la hoja
        <<< */
        $objPHPExcel->getActiveSheet()->setTitle('Ingresos No Constitutivos');

        /* >>>
        Inmovilizar paneles
        <<< */
        $objPHPExcel->getActiveSheet(3)->freezePane('A2');
        $objPHPExcel->getActiveSheet(3)->freezePaneByColumnAndRow(0, 4);

        /* >>>
        Creando nueva hoja
        <<< */
        $objPHPExcel->createSheet();

        /* >>>
        Seleccionamos la hoja a trabajar
        <<< */
        $objPHPExcel->setActiveSheetIndex(4);

        $titulosColumnas = array('Nombre', 'Tipo', 'Fecha', 'Valor');

        $objPHPExcel->setActiveSheetIndex(4)->mergeCells('A1:D1');
        $objPHPExcel->setActiveSheetIndex(4)->setCellValue('A1', $tituloReporte);

        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estiloA);
        $objPHPExcel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($estiloB);

        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(4)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        /* >>>
        Escribimos el archivo
        <<< */
        $i = 4;
        foreach ($seccion_deducciones as $deduccion) {
            $objPHPExcel->setActiveSheetIndex(4)
                ->setCellValue($letras[0] . $i, $deduccion->nombrePersona . " " . $deduccion->apellido)
                ->setCellValue($letras[1] . $i, $deduccion->nombre)
                ->setCellValue($letras[2] . $i, date('d-m-Y', strtotime($deduccion->fecha_registro)))
                ->setCellValue(
                    $letras[3] . $i,
                    Auth::user()->empresaObj->moneda . " " . Funcion::Parsear($deduccion->valor_categoria)
                );
            $i++;
        }

        $objPHPExcel->getActiveSheet()->getStyle('A3:D' . $i)->applyFromArray($estiloC);

        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(4)->getColumnDimension($i)->setAutoSize(true);
        }

        /* >>>
        Se asigna el nombre a la hoja
        <<< */
        $objPHPExcel->getActiveSheet()->setTitle('Deducciones');

        /* >>>
        Inmovilizar paneles
        <<< */
        $objPHPExcel->getActiveSheet(4)->freezePane('A2');
        $objPHPExcel->getActiveSheet(4)->freezePaneByColumnAndRow(0, 4);

        /* >>>
        Activamos la primera hoja para que sea la predeterminada
        <<< */
        $objPHPExcel->setActiveSheetIndex(0);

        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte Novedades ' . $preferencia->periodo(
            $periodo,
            $year,
            $tipo
        ) . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * guardar nomina de un empleado de un periodo n.
     *
     * @return void
     */
    public function store($persona)
    {
        return $persona;
    }

    public function edit_extras($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nomina = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn('fk_nomina_cuenta_tipo', [1, 2, 3])->get();

        if ($nomina) {
            $arrayPost['status'] = 'OK';
            $arrayPost['id'] = $id;
            $arrayPost['nomina'] = $nomina;
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function update_extras(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nomina = null;
        $refresh = '';
        $nomina = null;
        for ($i = 0; $i < count($request->horas_extras); $i++) {
            $nomina = NominaDetalleUno::where('nombre', $request->id_extras[$i])->where(
                'fk_nominaperiodo',
                $request->id
            )->first();
            if (!$nomina) {
                $nomina = new NominaDetalleUno();
                $nomina->nombre = $request->id_extras[$i];
                $nomina->fk_nominaperiodo = $request->id;
            }
            $nomina->numero_horas = $request->horas_extras[$i];
            $nomina->valor_categoria = $request->horas_extras_value[$i];
            $nomina->save();

            $nominaDeta = NominaCuentasGeneralDetalle::where(
                'fk_nominaperiodo',
                $request->id
            )->where('fk_nomina_cuenta', 1)->first();

            if ($nominaDeta) {
                $nominaDeta = NominaCuentasGeneralDetalle::where(
                    'fk_nominaperiodo',
                    $request->id
                )->where('fk_nomina_cuenta', 1)->update(['total_hora' => $request->horas_extras[$i]]);
            } else {
                $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                    'fk_nominaperiodo' => $request->id,
                    'fk_nomina_cuenta' => 1,
                    'total_hora' => $request->horas_extras[$i]
                ]);
            }
        }

        for ($i = 0; $i < count($request->horas_extras_ordinaria); $i++) {
            $nomina = NominaDetalleUno::where('nombre', $request->id_extras_ordinaria[$i])->where(
                'fk_nominaperiodo',
                $request->id
            )->first();
            if (!$nomina) {
                $nomina = new NominaDetalleUno();
                $nomina->nombre = $request->id_extras_ordinaria[$i];
                $nomina->fk_nominaperiodo = $request->id;
            }
            $nomina->numero_horas = $request->horas_extras_ordinaria[$i];
            $nomina->valor_categoria = $request->horas_extras_ordinaria_value[$i];
            $nomina->save();

            $nominaDeta = NominaCuentasGeneralDetalle::where(
                'fk_nominaperiodo',
                $request->id
            )->where('fk_nomina_cuenta', 1)->first();
            if ($nominaDeta) {
                $nominaDeta = NominaCuentasGeneralDetalle::where(
                    'fk_nominaperiodo',
                    $request->id
                )->where(
                    'fk_nomina_cuenta',
                    1
                )->update(['total_hora' => $request->horas_extras_ordinaria[$i]]);
            } else {
                $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                    'fk_nominaperiodo' => $request->id,
                    'fk_nomina_cuenta' => 1,
                    'total_hora' => $request->horas_extras_ordinaria[$i]
                ]);
            }
        }

        if (isset($request->otros_horas)) {
            for ($i = 0; $i < count($request->otros_horas); $i++) {
                $nomina = NominaDetalleUno::where('nombre', $request->otros_id[$i])->where(
                    'fk_nominaperiodo',
                    $request->id
                )->first();

                if (!$nomina) {
                    $nomina = new NominaDetalleUno();
                    $nomina->nombre = $request->otros_id[$i];
                    $nomina->fk_nominaperiodo = $request->id;
                }

                $nomina->numero_horas = $request->otros_horas[$i];
                $nomina->valor_categoria = $request->otros_horas_value[$i];
                $nomina->save();

                $nominaDeta = NominaCuentasGeneralDetalle::where(
                    'fk_nominaperiodo',
                    $request->id
                )->where('fk_nomina_cuenta', 1)->first();

                if ($nominaDeta) {
                    $nominaDeta = NominaCuentasGeneralDetalle::where(
                        'fk_nominaperiodo',
                        $request->id
                    )->where(
                        'fk_nomina_cuenta',
                        1
                    )->update(['total_hora' => $request->otros_horas[$i]]);
                } else {
                    $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                        'fk_nominaperiodo' => $request->id,
                        'fk_nomina_cuenta' => 1,
                        'total_hora' => $request->otros_horas[$i]
                    ]);
                }
            }
        }

        for ($i = 0; $i < count($request->otros_new_nombres); $i++) {
            if ($request->otros_new_nombres[$i]) {
                $nro = Categoria::where('empresa', Auth::user()->empresa)->get()->last()->nro;
                $categoria = new Categoria();
                $categoria->empresa = Auth::user()->empresa;
                $categoria->nro = $nro + 1;
                $categoria->nombre = $request->otros_new_nombres[$i];
                $categoria->nomina = '1';
                $categoria->fk_catgral = '4';
                $categoria->fk_nomcuenta_tipo = '3';
                $categoria->codigo = '0515';
                $categoria->asociado = Categoria::where('empresa', Auth::user()->empresa)->where(
                    'codigo',
                    '510515'
                )->get()->last()->nro;
                $categoria->valor_hora_ordinaria = $request->otros_new_valor[$i];
                $categoria->save();

                $nominas = Nomina::all();

                foreach ($nominas as $nomina) {
                    foreach ($nomina->nominaperiodos as $nominaperiodo) {
                        $nominaDetalleUno = new NominaDetalleUno();
                        $nominaDetalleUno->nombre = $categoria->nombre;
                        $nominaDetalleUno->numero_horas = ($nominaperiodo->id == $request->id) ? $request->otros_new_horas[$i] : null;
                        $nominaDetalleUno->valor_hora_ordinaria = $categoria->valor_hora_ordinaria;
                        $nominaDetalleUno->fk_nominaperiodo = $nominaperiodo->id;
                        $nominaDetalleUno->fk_nomina_cuenta_tipo = 3;
                        $nominaDetalleUno->fk_nomina_cuenta = 1;
                        $nominaDetalleUno->fk_categoria = $categoria->id;
                        $nominaDetalleUno->save();
                    }
                }

                $nominaDetalleUno = NominaDetalleUno::where('fk_nominaperiodo', $request->id)->where(
                    'nombre',
                    $categoria->nombre
                )->get()->last();

                $nominaDeta = NominaCuentasGeneralDetalle::where(
                    'fk_nominaperiodo',
                    $request->id
                )->where('fk_nomina_cuenta', 1)->first();

                if ($nominaDeta) {
                    $nominaDeta = NominaCuentasGeneralDetalle::where(
                        'fk_nominaperiodo',
                        $request->id
                    )->where(
                        'fk_nomina_cuenta',
                        1
                    )->update(['total_hora' => $request->otros_new_horas[$i]]);
                } else {
                    $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                        'fk_nominaperiodo' => $request->id,
                        'fk_nomina_cuenta' => 1,
                        'total_hora' => $request->otros_new_horas[$i]
                    ]);
                }
                $refresh = 'OK';
            }
        }


        $nominaPeriodo = NominaPeriodos::find($request->id);
        $nominaPeriodo->editValorTotal();

        if ($nominaPeriodo) {
            $arrayPost['status'] = 'OK';
            $arrayPost['refresh'] = $refresh;
            $arrayPost['id'] = $request->id;
            $arrayPost['horas'] = NominaDetalleUno::where(
                'fk_nominaperiodo',
                $request->id
            )->whereIn('fk_nomina_cuenta_tipo', [1, 2, 3])->sum('numero_horas');
            $arrayPost['valor_total'] = Funcion::precision($nominaPeriodo->valor_total);
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function edit_vacaciones($id)
    {

        if (NominaPeriodos::find($id)->nomina->fk_idempresa != Auth::user()->empresa) {
            return false;
        }

        $this->getAllPermissions(Auth::user()->id);
        $vacaciones = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn('fk_nomina_cuenta_tipo', [4])->get();
        $incapacidades = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn('fk_nomina_cuenta_tipo', [5])->get();
        $licencias = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn(
            'fk_nomina_cuenta_tipo',
            [6]
        )->get()->map(function ($value) {
            $value->is_remunerado($value->nombre);
            return $value;
        });

        $vacacionesCompensadasDinero = NominaDetalleUno::where('fk_nominaperiodo', $id)->where('nombre','LIKE','%VACACIONES COMPENSADAS EN DINERO%')->first();

        if ($vacaciones || $incapacidades || $licencias) {
            $nominaPeriodo = NominaPeriodos::find($id);
            $arrayPost['status'] = 'OK';
            $arrayPost['id'] = $id;
            $arrayPost['vacaciones'] = $vacaciones;
            $arrayPost['incapacidades'] = $incapacidades;
            $arrayPost['licencias'] = $licencias;
            $arrayPost['base'] = $nominaPeriodo->valor_total;
            $arrayPost['vac_compensadas_dinero'] = $vacacionesCompensadasDinero ? $vacacionesCompensadasDinero->valor_categoria : null;
            $arrayPost['vac_compensadas_dias'] = $vacacionesCompensadasDinero? $vacacionesCompensadasDinero->dias_compensados_dinero : null;
            $arrayPost['limit_inicio'] = $nominaPeriodo->fecha_desde->subDays(15)->format('Y-m-d');
            $arrayPost['limit_final'] = $nominaPeriodo->fecha_hasta->addDays(15)->format('Y-m-d');

            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function update_vacaciones(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nomina = null;
        //VACACIONES

        $countV = collect($request->v_id)->filter(function ($value, $key) {
            return $value != null;
        })->count();
        if (!$countV) {
            $countV = 1;
        }
        if (isset($request->v_id)) {
            for ($i = 0; $i < count($request->v_id); $i++) {
                $nomina = NominaDetalleUno::where('nombre', $request->v_nombre)->where(
                    'fk_nominaperiodo',
                    $request->id
                )->where('id', $request->v_id[$i])->first();

                if (!$nomina) {
                    $nomina = new  NominaDetalleUno();
                }

                if ($request->v_desde[$i]) {
                    $nomina->fecha_inicio = $request->v_desde[$i];
                    $nomina->fecha_fin = $request->v_hasta[$i];
                    $nomina->valor_categoria = $request->v_total_consolidado / $countV;
                    $nomina->dias_compensados_dinero = $request->total_dias_consolidados;
                    $nomina->pago_anticipado = $request->total_dias_consolidados;
                    $nomina->nombre = $request->v_nombre ? $request->v_nombre : 'VACACIONES';
                    $nomina->valor_hora_ordinaria = $request->v_hora;
                    $nomina->fk_nominaperiodo = $request->id;
                    $nomina->fk_nomina_cuenta_tipo = 4;
                    $nomina->fk_nomina_cuenta = 2;
                    $nomina->fk_categoria = $request->v_cate;
                    $nomina->save();
                }
            }
        } else {
            $i = 0;
        }

        //Compensadas en dinero por retiro
        // $vacacionesCompensadas= NominaDetalleUno::where('fk_nominaperiodo',$request->id)->where('nombre','LIKE','%VACACIONES COMPENSADAS EN DINERO%')->first();
        // if($vacacionesCompensadas){
        //     $vacacionesCompensadas->valor_categoria = $request->vac_compensada_dinero;
        //     $vacacionesCompensadas->dias_compensados_dinero = $request->vac_compensada_dias;
        //     $vacacionesCompensadas->save();
        // }else if($request->vac_compensada_dinero && $request->vac_compensada_dias){
            
        //     $vacacionesCompensadas = new  NominaDetalleUno();
        //     $vacacionesCompensadas->valor_categoria = $request->vac_compensada_dinero;
        //     $vacacionesCompensadas->dias_compensados_dinero = $request->vac_compensada_dias;
        //     $vacacionesCompensadas->nombre = 'VACACIONES COMPENSADAS EN DINERO POR RETIRO';
        //     $vacacionesCompensadas->fk_nominaperiodo = $request->id;
        //     $vacacionesCompensadas->fk_nomina_cuenta_tipo = 8;
        //     $vacacionesCompensadas->fk_nomina_cuenta = 3;
        //     $vacacionesCompensadas->fk_categoria = $request->v_cate;
        //     $vacacionesCompensadas->save();
        // }

        //INCAPACIDADES
        if (isset($request->i_id)) {
            for ($i = 0; $i < count($request->i_id); $i++) {
                $nomina = NominaDetalleUno::find($request->i_id[$i]);
                if ($nomina && $request->i_desde[$i]) {
                    if (strtolower($nomina->nombre) == "incapacidad general") {
                        $totalCategoria = $request->total_dias_incap_g;
                        $nomina->tipo_incapacidad =  1;
                    } else {
                        $totalCategoria = $request->total_dias_incap_p;
                        $nomina->tipo_incapacidad =  3;
                    }
                    $nomina->fecha_inicio = $request->i_desde[$i];
                    $nomina->fecha_fin = $request->i_hasta[$i];
                    $nomina->valor_categoria = floatval($totalCategoria);
                    $nomina->fk_nominaperiodo = $request->id;
                    $nomina->fk_nomina_cuenta_tipo = 5;
                    $nomina->fk_nomina_cuenta = 2;
                    $nomina->save();
                }
            }
        }

        //LICENCIAS REMUNERADAS
        $countLr = collect($request->lr_desde)->filter(function ($value, $key) {
            return $value != null;
        })->count();

        if (isset($request->lr_id)) {
            for ($i = 0; $i < count($request->lr_id); $i++) {
                $nomina = NominaDetalleUno::find($request->lr_id[$i]);
                if ($nomina) {
                    if($nomina->is_remunerado(null, false) == true){
                        if($request->lr_desde[$i]){
                                $nomina->fecha_inicio = $request->lr_desde[$i];
                                $nomina->fecha_fin = $request->lr_hasta[$i];
                                $nomina->fk_nominaperiodo = $request->id;
                                $nomina->valor_categoria = floatval($request->total_dias_licencia) / $countLr;
                                $nomina->fk_nomina_cuenta_tipo = 6;
                                $nomina->fk_nomina_cuenta = 2;
                        }else{
                            $nomina->fecha_inicio = null;
                            $nomina->fecha_fin = null;
                            $nomina->valor_categoria = 0;
                        }
                        $nomina->save();
                    }
                }
            }
        }

        //LICENCIAS NO REMUNERADAS
        $countLnr = collect($request->lnr_desde)->filter(function ($value, $key) {
            return $value != null;
        })->count();

        if (isset($request->lnr_id)) {
            for ($i = 0; $i < count($request->lnr_id); $i++) {
                $nomina = NominaDetalleUno::find($request->lnr_id[$i]);
                if ($nomina) {
                    if($nomina->is_remunerado(null, false) == false){
                        if($request->lnr_desde[$i]){
                            $nomina->fecha_inicio = $request->lnr_desde[$i];
                            $nomina->fecha_fin = $request->lnr_hasta[$i];
                            $nomina->fk_nominaperiodo = $request->id;
                            $nomina->valor_categoria = floatval($request->total_dias_licencia_no_remunerado) / $countLnr;
                            $nomina->fk_nomina_cuenta_tipo = 6;
                            $nomina->fk_nomina_cuenta = 2;
                        }else{
                            $nomina->fecha_inicio = null;
                            $nomina->fecha_fin = null;
                            $nomina->valor_categoria = 0;
                        }
                        $nomina->save();
                    }
                }
            }
        }

        $detalles = NominaDetalleUno::where('fk_nominaperiodo', $request->id)->whereIn(
            'fk_nomina_cuenta_tipo',
            [4, 5, 6]
        )->get();
        $dias = 0;
        foreach ($detalles as $detalle) {
            if ($detalle->fecha_inicio) {
                $fechaEmision = Carbon::parse($detalle->fecha_inicio);
                $fechaExpiracion = Carbon::parse($detalle->fecha_fin);
                $dias += $fechaExpiracion->diffInDays($fechaEmision);
                $dias += $detalle->dias_compensados_dinero;
                if ($dias >= 1) {
                    $dias += 1;
                } else {
                    $dias = 1;
                }
            }
        }


        $calculosFijos = [
            'subsidio_transporte' => (object)[
                'valor' => $request->subsidio_transporte,
                'simbolo' => '+'
            ]
        ];
        $nominaPeriodo = NominaPeriodos::find($request->id);

        $nominaPeriodo->editValorTotal($calculosFijos);

        if ($nominaPeriodo) {
            $arrayPost['status'] = 'OK';
            $arrayPost['id'] = $request->id;
            $arrayPost['horas'] = $dias;
            $arrayPost['valor_total'] = Funcion::precision($nominaPeriodo->valor_total);
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function destroy_vacaciones($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nomina = NominaDetalleUno::find($id);
        $fk_nomina = $nomina->fk_nominaperiodo;
        $tipo = $nomina->fk_nomina_cuenta_tipo;

        if ($nomina->nominaPeriodo->nomina->fk_idempresa != Auth::user()->empresa) {
            return false;
        }

        if ($nomina) {
            if ($tipo == 4) {
                $nomina->delete();
            } else {
                $nomina->fecha_inicio = null;
                $nomina->fecha_fin = null;
                $nomina->valor_categoria = 0;
                $nomina->save();
            }

            /*
            $detalles = NominaDetalleUno::where('fk_nominaperiodo', $fk_nomina)->whereIn('fk_nomina_cuenta_tipo', [4,5,6])->get();
            $dias = 0;
            foreach ($detalles as $detalle) {
                if ($detalle->fecha_inicio) {
                    $fechaEmision = Carbon::parse($detalle->fecha_inicio);
                    $fechaExpiracion = Carbon::parse($detalle->fecha_fin);
                    $dias += $fechaExpiracion->diffInDays($fechaEmision);
                    $dias += $detalle->dias_compensados_dinero;
                    if ($dias>1) {
                        $dias += 1;
                    }
                }
            }
            */

            $arrayPost['id'] = $fk_nomina;
            $arrayPost['status'] = 'OK';
            // $arrayPost['horas']  = $dias;
            $arrayPost['tipo'] = $tipo;
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function edit_adicionales($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $constitutivos = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn('fk_nomina_cuenta_tipo', [7])->get();
        $no_constitutivos = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn(
            'fk_nomina_cuenta_tipo',
            [8]
        )->get();
        $conectividad = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn('fk_nomina_cuenta_tipo', [9])->get();

        if ($constitutivos || $no_constitutivos || $conectividad) {
            $arrayPost['status'] = 'OK';
            $arrayPost['id'] = $id;
            $arrayPost['constitutivos'] = $constitutivos;
            $arrayPost['no_constitutivos'] = $no_constitutivos;
            $arrayPost['conectividad'] = $conectividad;
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function update_adicionales(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nomina = null;
        //CONSTITUTIVOS
        for ($i = 0; $i < count($request->constitutivos_id); $i++) {
            if (isset($request->constitutivos_id[$i])) {
                if (isset($request->constitutivos_ids[$i])) {
                    $nomina = NominaDetalleUno::find($request->constitutivos_ids[$i]);
                    if ($nomina) {
                        $nomina->valor_categoria = $request->constitutivos_valor[$i];
                        $nominaDeta = NominaCuentasGeneralDetalle::where(
                            'fk_nominaperiodo',
                            $request->id
                        )->where(
                            'fk_nomina_cuenta',
                            $nomina->id
                        )->update(['total_hora' => $request->constitutivos_valor[$i]]);
                        $nomina->save();
                    }
                } else {
                    $nomina = new NominaDetalleUno();
                    $nomina->fk_nominaperiodo = $request->id;
                    $nomina->fk_nomina_cuenta = 3;
                    $nomina->fk_nomina_cuenta_tipo = 7;
                    $nomina->fk_categoria = $request->constitutivos_id[$i];
                    $nomina->nombre = Categoria::where('id', $request->constitutivos_id[$i])->first()->nombre;
                    $nomina->valor_categoria = $request->constitutivos_valor[$i];
                    $nomina->save();
                    $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                        'fk_nominaperiodo' => $request->id,
                        'fk_nomina_cuenta' => $nomina->id,
                        'total_hora' => $request->constitutivos_valor[$i]
                    ]);
                }
            }
        }

        //NO CONSTITUTIVOS
        for ($j = 0; $j < count($request->no_constitutivos_id); $j++) {
            if (isset($request->no_constitutivos_id[$j])) {
                if (isset($request->no_constitutivos_ids[$j])) {
                    $nomina = NominaDetalleUno::find($request->no_constitutivos_ids[$j]);
                    if ($nomina) {
                        $nomina->valor_categoria = $request->no_constitutivos_valor[$j];
                        $nominaDeta = NominaCuentasGeneralDetalle::where(
                            'fk_nominaperiodo',
                            $request->id
                        )->where(
                            'fk_nomina_cuenta',
                            $nomina->id
                        )->update(['total_hora' => $request->no_constitutivos_valor[$j]]);
                        $nomina->save();
                    }
                } else {
                    $nomina = new NominaDetalleUno();
                    $nomina->fk_nominaperiodo = $request->id;
                    $nomina->fk_nomina_cuenta = 3;
                    $nomina->fk_nomina_cuenta_tipo = 8;
                    $nomina->fk_categoria = $request->no_constitutivos_id[$j];
                    $nomina->nombre = Categoria::where('id', $request->no_constitutivos_id[$j])->first()->nombre;
                    $nomina->valor_categoria = $request->no_constitutivos_valor[$j];
                    $nomina->save();
                    $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                        'fk_nominaperiodo' => $request->id,
                        'fk_nomina_cuenta' => $nomina->id,
                        'total_hora' => $request->no_constitutivos_valor[$j]
                    ]);
                }
            }
        }

        //AUXILIARES
        if ($request->auxiliar_id) {
            $nomina = NominaDetalleUno::find($request->auxiliar_ids);
            if ($nomina) {
                $nomina->valor_categoria = $request->auxiliar_valor;
                $nominaDeta = NominaCuentasGeneralDetalle::where(
                    'fk_nominaperiodo',
                    $request->id
                )->where(
                    'fk_nomina_cuenta',
                    $nomina->id
                )->update(['total_hora' => $request->auxiliar_valor]);
                $nomina->save();
            } else {
                $nomina = new NominaDetalleUno();
                $nomina->fk_nominaperiodo = $request->id;
                $nomina->fk_nomina_cuenta = 3;
                $nomina->fk_nomina_cuenta_tipo = 9;
                $nomina->fk_categoria = $request->auxiliar_id;
                $nomina->nombre = Categoria::where('id', $request->auxiliar_id)->first()->nombre;
                $nomina->valor_categoria = $request->auxiliar_valor;
                $nomina->save();
                $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                    'fk_nominaperiodo' => $request->id,
                    'fk_nomina_cuenta' => $nomina->id,
                    'total_hora' => $request->auxiliar_valor
                ]);
            }
        }

        $nominaPeriodo = NominaPeriodos::find($request->id);
        $nominaPeriodo->editValorTotal();

        if ($nominaPeriodo) {
            $arrayPost['status'] = 'OK';
            $arrayPost['id'] = $request->id;
            $arrayPost['ingresos'] = NominaDetalleUno::where(
                'fk_nominaperiodo',
                $request->id
            )->whereIn('fk_nomina_cuenta_tipo', [7, 8, 9])->sum('valor_categoria');
            $arrayPost['valor_total'] = Funcion::precision($nominaPeriodo->valor_total);
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function destroy_adicionales($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nomina = NominaDetalleUno::find($id);
        $fk_nomina = $nomina->fk_nominaperiodo;

        if ($nomina->nominaPeriodo->nomina->fk_idempresa != Auth::user()->empresa) {
            return false;
        }

        if ($nomina) {
            $nomina->delete();
            $arrayPost['id'] = $fk_nomina;
            $arrayPost['status'] = 'OK';
            $arrayPost['ingresos'] = NominaDetalleUno::where(
                'fk_nominaperiodo',
                $fk_nomina
            )->whereIn('fk_nomina_cuenta_tipo', [7, 8, 9])->sum('valor_categoria');
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function edit_deducciones($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $deducciones = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn('fk_nomina_cuenta_tipo', [10])->get();
        $prestamos = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn('fk_nomina_cuenta_tipo', [11])->get();
        $retefuente = NominaDetalleUno::where('fk_nominaperiodo', $id)->whereIn('fk_nomina_cuenta_tipo', [12])->get();

        if ($deducciones || $prestamos || $retefuente) {
            $arrayPost['status'] = 'OK';
            $arrayPost['id'] = $id;
            $arrayPost['deducciones'] = $deducciones;
            $arrayPost['prestamos'] = $prestamos;
            $arrayPost['retefuente'] = $retefuente;
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function update_deducciones(Request $request)
    { //dd($request->all());
        $this->getAllPermissions(Auth::user()->id);
        $nomina = null;
        //DEDUCCIONES
        for ($i = 0; $i < count($request->deducciones_id); $i++) {
            if (isset($request->deducciones_id[$i])) {
                if (isset($request->deducciones_ids[$i])) {
                    $nomina = NominaDetalleUno::find($request->deducciones_ids[$i]);
                    if ($nomina) {
                        $nomina->valor_categoria = $request->deducciones_valor[$i];
                        $nominaDeta = NominaCuentasGeneralDetalle::where(
                            'fk_nominaperiodo',
                            $request->id
                        )->where(
                            'fk_nomina_cuenta',
                            $nomina->id
                        )->update(['total_hora' => $request->deducciones_valor[$i]]);
                        $nomina->save();
                    }
                } else {
                    $nomina = new NominaDetalleUno();
                    $nomina->fk_nominaperiodo = $request->id;
                    $nomina->fk_nomina_cuenta = 4;
                    $nomina->fk_nomina_cuenta_tipo = 10;
                    $nomina->fk_categoria = $request->deducciones_id[$i];
                    $nomina->nombre = Categoria::where('id', $request->deducciones_id[$i])->first()->nombre;
                    $nomina->valor_categoria = $request->deducciones_valor[$i];
                    $nomina->save();
                    $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                        'fk_nominaperiodo' => $request->id,
                        'fk_nomina_cuenta' => $nomina->id,
                        'total_hora' => $request->deducciones_valor[$i]
                    ]);
                }
            }
        }

        //PRESTAMOS
        for ($j = 0; $j < count($request->prestamos_id); $j++) {
            if (isset($request->prestamos_id[$j])) {
                if (isset($request->prestamos_ids[$j])) {
                    $nomina = NominaDetalleUno::find($request->prestamos_ids[$j]);
                    if ($nomina) {
                        $nomina->valor_categoria = $request->prestamos_valor[$j];
                        $nominaDeta = NominaCuentasGeneralDetalle::where(
                            'fk_nominaperiodo',
                            $request->id
                        )->where(
                            'fk_nomina_cuenta',
                            $nomina->id
                        )->update(['total_hora' => $request->prestamos_valor[$j]]);
                        $nomina->save();
                    }
                } else {
                    $nomina = new NominaDetalleUno();
                    $nomina->fk_nominaperiodo = $request->id;
                    $nomina->fk_nomina_cuenta = 4;
                    $nomina->fk_nomina_cuenta_tipo = 11;
                    $nomina->fk_categoria = $request->prestamos_id[$j];
                    $nomina->nombre = Categoria::where('id', $request->prestamos_id[$j])->first()->nombre;
                    $nomina->valor_categoria = $request->prestamos_valor[$j];
                    $nomina->save();
                    $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                        'fk_nominaperiodo' => $request->id,
                        'fk_nomina_cuenta' => $nomina->id,
                        'total_hora' => $request->prestamos_valor[$j]
                    ]);
                }
            }
        }

        //RETEFUENTE
        if ($request->retefuente_id) {
            $nomina = NominaDetalleUno::find($request->retefuente_ids);
            if ($nomina) {
                $nomina->valor_categoria = $request->retefuente_valor;
                $nominaDeta = NominaCuentasGeneralDetalle::where(
                    'fk_nominaperiodo',
                    $request->id
                )->where(
                    'fk_nomina_cuenta',
                    $nomina->id
                )->update(['total_hora' => $request->retefuente_valor]);
                $nomina->save();
            } else {
                $nomina = new NominaDetalleUno();
                $nomina->fk_nominaperiodo = $request->id;
                $nomina->fk_nomina_cuenta = 4;
                $nomina->fk_nomina_cuenta_tipo = 12;
                $nomina->fk_categoria = $request->retefuente_id;
                $nomina->nombre = Categoria::where('id', $request->retefuente_id)->first()->nombre;
                $nomina->valor_categoria = $request->retefuente_valor;
                $nomina->save();
                $nominaDeta = DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                    'fk_nominaperiodo' => $request->id,
                    'fk_nomina_cuenta' => $nomina->id,
                    'total_hora' => $request->retefuente_valor
                ]);
            }
        }

        $nominaPeriodo = NominaPeriodos::find($request->id);
        $nominaPeriodo->editValorTotal();

        if ($nominaPeriodo) {
            $arrayPost['status'] = 'OK';
            $arrayPost['id'] = $request->id;
            $arrayPost['deducciones'] = NominaDetalleUno::where(
                'fk_nominaperiodo',
                $request->id
            )->whereIn('fk_nomina_cuenta_tipo', [10, 11, 12])->sum('valor_categoria');
            $arrayPost['valor_total'] = Funcion::precision($nominaPeriodo->valor_total);
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function destroy_deducciones($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nomina = NominaDetalleUno::find($id);
        $fk_nomina = $nomina->fk_nominaperiodo;

        if ($nomina->nominaPeriodo->nomina->fk_idempresa != Auth::user()->empresa) {
            return false;
        }

        if ($nomina) {
            $nomina->delete();
            $arrayPost['id'] = $fk_nomina;
            $arrayPost['status'] = 'OK';
            $arrayPost['deducciones'] = NominaDetalleUno::where(
                'fk_nominaperiodo',
                $fk_nomina
            )->whereIn('fk_nomina_cuenta_tipo', [10, 11, 12])->sum('valor_categoria');
            return json_encode($arrayPost);
        } else {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'No existe un registro con ese ID';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function calculos($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nomina = NominaPeriodos::with('nomina')->where('id', $id)->first();
        $moneda = Auth::user()->empresaObj->moneda;

        if (!$nomina) {
            return back();
        }

        if ($nomina->nomina->fk_idempresa != Auth::user()->empresa) {
            return back();
        }

        $totalidad = $nomina->resumenTotal();
        $persona = $nomina->nomina->persona;
        $mensajePeriodo = request()->periodo;
        view()->share([
            'seccion' => 'nomina',
            'title' => "Cálculos de {$persona->nombre} | {$mensajePeriodo}",
            'icon' => 'fas fa-dollar-sign'
        ]);
        return view('nomina.calculos', compact('nomina', 'moneda', 'totalidad', 'persona', 'mensajePeriodo'));
    }

    public function GuardarPreferenciaPago(Request $request)
    {
        $request->validate([
            'frecuencia_pago' => 'required',
            'medio_pago' => 'required',
            'fecha_constitucion' => 'required',
        ]);

        if ($request->medio_pago != 1) {
            $request->validate([
                'banco' => 'required',
                'tipo_cuenta' => 'required',
                'nro_cuenta' => 'required|numeric',
            ]);
        }

        NominaPreferenciaPago::updateOrCreate(
            ['empresa' => auth()->user()->empresa,],
            [
                'empresa' => auth()->user()->empresa,
                'arl' => $request->arl,
                'frecuencia_pago' => $request->frecuencia_pago,
                'medio_pago' => $request->medio_pago,
                'banco' => $request->banco,
                'tipo_cuenta' => $request->tipo_cuenta,
                'nro_cuenta' => $request->nro_cuenta,
                'fecha_constitucion' => $request->fecha_constitucion
            ]
        );

        $personas = Persona::where('fk_empresa', Auth::user()->empresa)->get();

        foreach ($personas as $persona) {
            PersonasController::nominaPersona($persona);
        }


        return back()->with('success', 'Preferencia de pago actualizada correctamente');
    }

    public function preferenciaPago()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share([
            'seccion' => 'nomina',
            'title' => 'Ingresa las preferencias de pago de tu nómina',
            'icon' => 'fas fa-money-bill-wave'
        ]);

        $mediosPago = DB::table('metodos_pago')->get();
        $bancos = DB::table('ne_bancos')->get();
        $preferencia = NominaPreferenciaPago::where('empresa', auth()->user()->empresa)->first();
        $aseguradoras = DB::table('ne_arl')->get();
        $guiasVistas = [];

        return view('nomina.preferencias-pago', compact('mediosPago', 'bancos', 'preferencia', 'aseguradoras', 'guiasVistas'));
    }

    /**
     * Método para generar una nueva nomina dinamicamente desde la accion generar nueva nomina.
     *
     * @return view
     */
    public function store_nomina(Request $request)
    {

        $this->getAllPermissions(Auth::user()->id);
        $personas = Persona::where('fk_empresa', Auth::user()->empresa)->where('status', 1)->get();


        /**
         * Validación de que no haya una nomina anterior generada en el mismo rango que escogió el cliente.
         */

        $validateNomina = Nomina::where('periodo', $request->periodo)->where(
            'year',
            $request->year
        )->where('fk_idempresa', Auth::user()->empresa)->first();

        if ($validateNomina) {
            $arrayPost['success'] = false;
            $arrayPost['message_error'] = "La nómina que desea generar ya ha sido generada anteriormente.";
            return json_encode($arrayPost);
            exit;
        }

        if (count($personas) == 0) {
            $arrayPost['success'] = false;
            $arrayPost['message_error'] = "No hay personas registradas o habilitadas para generar una nueva nómina.";
            return json_encode($arrayPost);
            exit;
        }

        if ($personas) {

            foreach ($personas as $persona) {

                $tipoContrato = NominaTipoContrato::find($persona->fk_tipo_contrato);
                $data = app(PersonasController::class)->nominaPersona(
                    $persona,
                    $request->year,
                    $request->periodo,
                    $tipoContrato
                );
            }

            /* >>> Si depsués de haber generado la nomina de cada persona no queda ninguna  Nomina creada se revalida <<<*/
            $validateNomina = Nomina::where('periodo', $request->periodo)->where(
                'year',
                $request->year
            )->where('fk_idempresa', Auth::user()->empresa)->first();

            if (!$validateNomina) {
                $arrayPost['success'] = false;
                $arrayPost['message_error'] = "La nómina que desea generar no pudo ser creada.";
                return json_encode($arrayPost);
                exit;
            }

            $date = Carbon::create($request->year, $request->periodo, 1)->locale('es');
            $arrayPost['success'] = true;
            $arrayPost['nomina'] = ucfirst($date->monthName) . ' ' . $request->year;
            $arrayPost['periodo'] = $request->periodo;
            $arrayPost['year'] = $request->year;
            $arrayPost['empleados'] = Nomina::where('periodo', $request->periodo)->where(
                'year',
                $request->year
            )->count();
            $arrayPost['url'] = Route('nomina.liquidar', ['periodo' => $request->periodo, 'year' => $request->year]);
            return json_encode($arrayPost);
            exit;
        } else {
            $arrayPost['success'] = false;
            $arrayPost['message_error'] = "Disculpe ha ocurrido un error, inténtelo nuevamente";
            echo json_encode($arrayPost);
            exit;
        }
    }


    public function estadoEliminado(Nomina $nomina)
    {
        $nomina->emitida = 6;
        $nomina->save();
        return back()->with('success', 'La nomina ha sido eliminada con exito');
    }


    public function details_pdf($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $request = request();
        $nominaPeriodo = NominaPeriodos::with('nomina')->where('id', $id)->first();
        $nomina = $nominaPeriodo->nomina;

        if ($nomina->fk_idempresa != Auth::user()->empresa) {
            return false;
        }
        
        $empresa = auth()->user()->empresaObj;

        $persona = Persona::find($nomina->fk_idpersona);
        $title = 'RESUMEN DE PAGO ' . $persona->nombre();
        $totalidad = $nominaPeriodo->resumenTotal();
        $user = Auth::user();
        $prestacionSocial = null;
        $adicionales = 0;
        if ($idPrestacionSocial = $request->prestacion_social) {
            if ($idPrestacionSocial == 'todas') {
                $prestacionSocial = $nomina->prestacionesSociales->all();
            } else {
                $prestacionSocial = NominaPrestacionSocial::find($idPrestacionSocial);
            }
        }
        $fechaDesde = (new Carbon($nominaPeriodo->fecha_desde))->format('d/m/Y');
        $fechaHasta = (new Carbon($nominaPeriodo->fecha_hasta))->format('d/m/Y');

        $numeracion = $nomina->codigo_dian ? $nomina->codigo_dian : $nomina->nro;

        $pdf = PDF::loadView(
            'pdf.nomina.detalle',
            compact(
                'persona',
                'title',
                'nomina',
                'nominaPeriodo',
                'totalidad',
                'user',
                'prestacionSocial',
                'adicionales',
                'fechaDesde',
                'fechaHasta',
                'numeracion',
                'empresa'
            )
        );
        return response($pdf->stream())->withHeaders([
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function resumen($nominaPeriodo)
    {
        $nomina = NominaPeriodos::find($nominaPeriodo);
        dd($nomina->resumenTotal());
    }

    public function calculosCompleto($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nominas = NominaPeriodos::with('nomina')->where('fk_idnomina', $id)->get();
        $moneda = Auth::user()->empresaObj->moneda;
        $i = 0;

        if (!$nominas) {
            return back();
        }

        $totalidad = [
            'pago' => [
                'salario' => 0,
                'subsidioDeTransporte' => 0,
                'retencionesDeducciones' => 0,
                'total' => 0,
                'extrasOrdinariasRecargos' => 0,
                'vacaciones' => 0,
                'ingresosAdicionales' => 0
            ],
            'diasTrabajados' => ['diasPeriodo' => 0, 'total' => 0],
            'salarioSubsidio' => ['salario' => 0, 'subsidioTransporte' => 0, 'total' => 0],
            'ibcSeguridadSocial' => [
                'salario' => 0,
                'total' => 0,
                'vacaciones' => 0,
                'ingresosyExtras' => 0,
                'incapacidades' => 0,
                'salarioParcial' => 0,
                'licencias' => 0
            ],
            'retenciones' => [
                'salud' => 0,
                'pension' => 0,
                'total' => 0,
                'porcentajeSalud' => 0,
                'porcentajePension' => 0
            ],
            'seguridadSocial' => ['pension' => 0, 'riesgo1' => 0, 'total' => 0],
            'parafiscales' => ['cajaCompensacion' => 0, 'total' => 0],
            'provisionPrestacion' => [
                'cesantias' => 0,
                'interesesCesantias' => 0,
                'primaServicios' => 0,
                'vacaciones' => 0,
                'total' => 0
            ],
            'deducciones' => ['total' => 0]
        ];

        foreach ($nominas as $nomina) {
            $totalidad['pago']['salario'] += $nomina->resumenTotal()['pago']['salario'];
            $totalidad['pago']['subsidioDeTransporte'] += $nomina->resumenTotal()['pago']['subsidioDeTransporte'];
            $totalidad['pago']['retencionesDeducciones'] += $nomina->resumenTotal()['pago']['retencionesDeducciones'];
            $totalidad['pago']['total'] += $nomina->resumenTotal()['pago']['total'];
            $totalidad['pago']['extrasOrdinariasRecargos'] += $nomina->resumenTotal()['pago']['extrasOrdinariasRecargos'];
            $totalidad['pago']['vacaciones'] += $nomina->resumenTotal()['pago']['vacaciones'];
            $totalidad['pago']['ingresosAdicionales'] += $nomina->resumenTotal()['pago']['ingresosAdicionales'];

            $totalidad['diasTrabajados']['diasPeriodo'] += $nomina->resumenTotal()['diasTrabajados']['diasPeriodo'];
            $totalidad['diasTrabajados']['total'] += $nomina->resumenTotal()['diasTrabajados']['total'];
            $totalidad['diasTrabajados']['ausencia'] = $nomina->diasAusenteDetalle();

            $totalidad['salarioSubsidio']['salario'] += $nomina->resumenTotal()['salarioSubsidio']['salario'];
            $totalidad['salarioSubsidio']['subsidioTransporte'] += $nomina->resumenTotal()['salarioSubsidio']['subsidioTransporte'];
            $totalidad['salarioSubsidio']['total'] += $nomina->resumenTotal()['salarioSubsidio']['total'];
            $totalidad['salarioSubsidio']['salarioCompleto'] = $nomina->resumenTotal()['salarioSubsidio']['salarioCompleto'];
            $totalidad['salarioSubsidio']['valorDia'] = $nomina->resumenTotal()['salarioSubsidio']['valorDia'];

            $totalidad['ibcSeguridadSocial']['salario'] += $nomina->resumenTotal()['ibcSeguridadSocial']['salario'];
            $totalidad['ibcSeguridadSocial']['total'] += $nomina->resumenTotal()['ibcSeguridadSocial']['total'];
            $totalidad['ibcSeguridadSocial']['vacaciones'] += $nomina->resumenTotal()['ibcSeguridadSocial']['vacaciones'];
            $totalidad['ibcSeguridadSocial']['ingresosyExtras'] += $nomina->resumenTotal()['ibcSeguridadSocial']['ingresosyExtras'];
            $totalidad['ibcSeguridadSocial']['incapacidades'] += $nomina->resumenTotal()['ibcSeguridadSocial']['incapacidades'];
            $totalidad['ibcSeguridadSocial']['salarioParcial'] += $nomina->resumenTotal()['ibcSeguridadSocial']['salarioParcial'];
            $totalidad['ibcSeguridadSocial']['licencias'] += $nomina->resumenTotal()['ibcSeguridadSocial']['licencias'];

            $totalidad['retenciones']['salud'] += $nomina->resumenTotal()['retenciones']['salud'];
            $totalidad['retenciones']['pension'] += $nomina->resumenTotal()['retenciones']['pension'];
            $totalidad['retenciones']['total'] += $nomina->resumenTotal()['retenciones']['total'];
            $totalidad['retenciones']['porcentajeSalud'] = $nomina->resumenTotal()['retenciones']['porcentajeSalud'];
            $totalidad['retenciones']['porcentajePension'] = $nomina->resumenTotal()['retenciones']['porcentajePension'];

            $totalidad['seguridadSocial']['pension'] += $nomina->resumenTotal()['seguridadSocial']['pension'];
            $totalidad['seguridadSocial']['riesgo1'] += $nomina->resumenTotal()['seguridadSocial']['riesgo1'];
            $totalidad['seguridadSocial']['total'] += $nomina->resumenTotal()['seguridadSocial']['total'];

            $totalidad['parafiscales']['cajaCompensacion'] += $nomina->resumenTotal()['parafiscales']['cajaCompensacion'];
            $totalidad['parafiscales']['total'] += $nomina->resumenTotal()['parafiscales']['total'];

            $totalidad['provisionPrestacion']['cesantias'] += $nomina->resumenTotal()['provisionPrestacion']['total'];
            $totalidad['provisionPrestacion']['interesesCesantias'] += $nomina->resumenTotal()['provisionPrestacion']['interesesCesantias'];
            $totalidad['provisionPrestacion']['primaServicios'] += $nomina->resumenTotal()['provisionPrestacion']['primaServicios'];
            $totalidad['provisionPrestacion']['vacaciones'] += $nomina->resumenTotal()['provisionPrestacion']['vacaciones'];
            $totalidad['provisionPrestacion']['total'] += $nomina->resumenTotal()['provisionPrestacion']['total'];

            $totalidad['deducciones']['total'] += $nomina->resumenTotal()['deducciones']['total'];

            //$totalidad[$i] = $nomina->resumenTotal();
            $persona = $nomina->nomina->persona;
            $periodo = $nomina->nomina->periodo;
            $year = $nomina->nomina->year;
            $i++;
        }

        $preferencia = NominaPreferenciaPago::where('empresa', auth()->user()->empresa)->first();
        $mensajePeriodo = $preferencia->periodoCompleto($periodo, $year);
        view()->share([
            'seccion' => 'nomina',
            'title' => "Cálculos de {$persona->nombre} {$persona->apellido} | {$mensajePeriodo}",
            'icon' => 'fas fa-dollar-sign'
        ]);
        return view('nomina.calculos', compact('nomina', 'moneda', 'totalidad', 'persona', 'mensajePeriodo'));
    }

    public function exportarResumenNomina($periodo, $year, $tipo = null)
    {
        /* >>>
        Obtenemos la data a mostrar en el Excel
        <<< */
        $empresa = Auth::user()->empresa;

        /* >>> si la primer nomina recuperada en el get tiene 2 periodos si o si todas las nominas traidas de ese año y periodo deben ser
        quincenales <<< */

        $variosPeriodos = Nomina::with('nominaperiodos')->where('year', $year)
            ->where('periodo', $periodo)->where('fk_idempresa', Auth::user()->empresa)->first()->nominaperiodos;


        /* >>> si el tipo es igual a null las nominas con los periodos que traeremos serán del primer periodo, sea mensual o quincenal <<< */
        if ($tipo == null) {
            $vPeriodo = $variosPeriodos->first();
            if ($vPeriodo) {
                $tipo = $vPeriodo->periodo;
            } else {
                return back()->with('error', 'Error al general la nomina del mes ' . $periodo);
            }
        }

        //obtenemos las nominas del periodo actual y si tenemos un miniperiodo de la quincena nos traremos esa nomina con ese periodo.
        $nominas = Nomina::with([
            'persona.nomina_tipo_contrato',
            'prestacionesSociales',
            'nominaperiodos' => function ($query) use ($tipo) {
                $query->where('periodo', $tipo);
            }
        ])
            ->where('ne_nomina.year', $year)
            ->where('ne_nomina.periodo', $periodo)
            ->where('fk_idempresa', Auth::user()->empresa)
            ->where('estado_nomina', 1)
            ->where('emitida', '<>', 3)
            // ->where('emitida', '<>', 6)
            ->get();

        // foreach($nominas as $nomina){
        //     foreach($nomina->periodos as $periodo){
        //         echo $periodo->fk_idnomina . "<br>";
        //     }
        // }
        $idNominas = $nominas->keyBy('id')->keys();
        $costoPeriodo = $this->nominaDianController->costoPeriodo($tipo, $idNominas);

        /* >>>
        Armado del Excel
        <<< */
        $objPHPExcel = new PHPExcel();
        $preferencia = NominaPreferenciaPago::where('empresa', auth()->user()->empresa)->first();

        $tituloReporte = "Resumen Nómina " . $preferencia->periodo($periodo, $year, $tipo);
        $titulosColumnas = array('Nombre', 'Apellido', 'Numero de identificacion', 'Sede', 'Area', 'Cargo', 'Centro de Costos', 'Salario Base', 'Horas Extras y recargos', 'Vacaciones, Incap y Lic', 'Ingresos adicionales', 'Deducc, prest y ReteFuen', 'Pago empleado', 'Prima', 'Cesantias', 'Intereses Cesantias');
        $letras = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
            ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modificó
            ->setTitle("Resumen Nomina") // Titulo
            ->setSubject("Resumen Nomina") //Asunto
            ->setDescription("Resumen Nmina") //Descripción
            ->setKeywords("resumen nomina") //Etiquetas
            ->setCategory("reporte excel"); //Categorias

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:P1');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $tituloReporte);

        $estiloA = array(
            'font' => array('bold' => true, 'size' => 12, 'name' => 'Times New Roman'),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->applyFromArray($estiloA);

        $estiloB = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'd08f50')
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:P3')->applyFromArray($estiloB);

        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        /* >>>
        Escribimos el archivo
        <<< */
        $i = 4;
        foreach ($nominas as $nomina) {

            $interesCesantiaValor = $cesantiaValor = $primaValor = 0;

            foreach ($nomina->prestacionesSociales as $prestacion) {

                switch ($prestacion->nombre) {

                    case 'prima':
                        $primaValor =  Auth::user()->empresaObj->moneda . ' ' . Funcion::Parsear($prestacion->valor_pagar);
                        break;
                    case 'cesantia':
                        $cesantiaValor =  Auth::user()->empresaObj->moneda . ' ' . Funcion::Parsear($prestacion->valor_pagar);
                        break;

                    case 'intereses_cesantia':
                        $interesCesantiaValor =  Auth::user()->empresaObj->moneda . ' ' . Funcion::Parsear($prestacion->valor_pagar);
                        break;
                }
            }

            foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($letras[0] . $i, $nomina->persona->nombre)
                    ->setCellValue($letras[1] . $i, $nomina->persona->apellido)
                    ->setCellValue($letras[2] . $i, $nomina->persona->nro_documento)
                    ->setCellValue($letras[3] . $i, $nomina->persona->sede()->nombre)
                    ->setCellValue($letras[4] . $i, $nomina->persona->area()->nombre)
                    ->setCellValue($letras[5] . $i, $nomina->persona->cargo()->nombre)
                    ->setCellValue($letras[6] . $i, $nomina->persona->centro_costo()->nombre)
                    ->setCellValue($letras[7] . $i, Auth::user()->empresaObj->moneda . ' ' . Funcion::Parsear($nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total))
                    ->setCellValue($letras[8] . $i, $nominaPeriodo->extras())
                    ->setCellValue($letras[9] . $i, $nominaPeriodo->vacaciones())
                    ->setCellValue($letras[10] . $i, Auth::user()->empresaObj->moneda . ' ' . Funcion::Parsear($nominaPeriodo->ingresos()))
                    ->setCellValue($letras[11] . $i, Auth::user()->empresaObj->moneda . ' ' . Funcion::Parsear($nominaPeriodo->deducciones()))
                    ->setCellValue($letras[12] . $i, Auth::user()->empresaObj->moneda . ' ' . Funcion::Parsear($nominaPeriodo->valor_total ? $nominaPeriodo->valor_total : 0))
                    ->setCellValue($letras[13] . $i, $primaValor)
                    ->setCellValue($letras[14] . $i, $cesantiaValor)
                    ->setCellValue($letras[15] . $i, $interesCesantiaValor);
            }
            $i++;
        }

        $estiloC = array(
            'font' => array('size' => 12, 'name' => 'Times New Roman'),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:P' . $i)->applyFromArray($estiloC);


        for ($i = 'A'; $i <= $letras[23]; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(true);
        }

        /* >>>
        Se asigna el nombre a la hoja
        <<< */
        $objPHPExcel->getActiveSheet()->setTitle('Resumen Nomina');

        /* >>>
        Inmovilizar paneles
        <<< */
        $objPHPExcel->getActiveSheet(0)->freezePane('A2');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 4);
        /* >>>
        Activamos la primera hoja para que sea la predeterminada
        <<< */
        $objPHPExcel->setActiveSheetIndex(0);

        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Resumen_Nomina_' . $preferencia->periodo($periodo, $year, $tipo) . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function planes()
    {
        $this->getAllPermissions(Auth::user()->id);
        $empresa = Auth::user()->empresa;

        view()->share(['seccion' => 'nomina', 'title' => 'Planes Nómina Electrónica', 'icon' => '']);

        $personal = Persona::where('fk_empresa', $empresa)->where('status', 1)->count();
        $suscripcionesPagos = SuscripcionPagoNomina::where('id_empresa', $empresa)->get()->last();
        $plan = ($suscripcionesPagos) ? $suscripcionesPagos->plan() : 'Nómina Electrónica Básico Gratis por 15 días';
        $suscripcion = SuscripcionNomina::where('id_empresa', $empresa)->first();

        if (!$suscripcion) {
            return view('nomina.planes', compact('personal', 'plan'));
        }

        $rango = Carbon::parse($suscripcion->fec_inicio)->format('d/m/Y') . ' - ' . Carbon::parse($suscripcion->fec_vencimiento)->format('d/m/Y');
        $fecha1 = date_create(Carbon::now());
        $fecha2 = date_create($suscripcion->fec_vencimiento);
        $dias = date_diff($fecha1, $fecha2)->format('%R%a');
        $estado = ($dias <= 0) ? '<span class="text-danger">Suscripción Vencida</span>' : '<span class="text-success">Suscripción Vigente</span>';
        return view('nomina.planes', compact('personal', 'plan', 'rango', 'estado'));
    }

    public function plan_pago($valor, $personalizado = false)
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['seccion' => 'nomina', 'title' => 'Planes Nómina Electrónica', 'icon' => '']);

        if ($valor) {
            switch ($valor) {
                case 15000:
                    $tipo = "Nómina Electrónica Básico";
                    $plan = 0;
                    break;
                case 20000:
                    $tipo = "Nómina Electrónica Emprendedor";
                    $plan = 1;
                    break;
                case 30000:
                    $tipo = "Nómina Electrónica PYME";
                    $plan = 2;
                    break;
                case 40000:
                    $tipo = "Nómina Electrónica Avanzado";
                    $plan = 3;
                    break;
                default:
                    redirect('/nomina/planes');
            }
        } else {
            return redirect('/nomina/planes');
        }
        return view('nomina.pagosplan', compact('valor', 'tipo', 'plan', 'personalizado'));
    }

    public function plan_pago_informacion($monto)
    {
        $sw = 1;
        while ($sw == 1) {
            $reference = "pago-nomina-" . PlanesNomina::generateRandomString();
            if (PlanesNomina::where('referencia_pago', $reference)->first()) {
                $reference = "pago-" . PlanesNomina::generateRandomString();
            } else {
                $sw = 0;
            }
        }

        $info_pago = new stdClass();
        //PRODUCCIÓN
        $info_pago->url = "https://checkout.wompi.co/p/";
        $info_pago->public_key = "pub_prod_Fab6QAeiWFPB1IVzMFIZQKPUZLcNN3nq";

        //SANDBOX
        //$info_pago->public_key = "pub_test_CreiABg6OhVH3vUC5NrWkXTPyAdu6CCZ";
        //$info_pago->url = "https://sandbox.wompi.co/v1";

        $info_pago->currency = "COP";
        $info_pago->amount_in_cents = $monto . '00';
        $info_pago->reference = $reference;
        $info_pago->customer_data_email = Auth()->user()->email;
        $info_pago->customer_data_full_name = Auth()->user()->nombres;
        //$info_pago->redirect_url = "https://gestordepartes.net/respuestapagowompi";
        $info_pago->redirect_url = config('app.url') . "/respuestapagowompi";
        return response()->json($info_pago);
    }

    public function plan_pago_preguardar(Request $data)
    {
        if ($data->pMeses != 0) {
            $meses = $data->pMeses;
        } else {
            //Validamos que mes escogio el usuario por medio del id del radiobutton
            if ($data->meses == "optradio1") {
                $meses = 1;
            } elseif ($data->meses == "optradio2") {
                $meses = 6;
            } elseif ($data->meses == "optradio3") {
                $meses = 12;
            } else {
                return null;
            }
        }

        $suplente_pago = PlanesNomina::firstOrCreate(
            [
                'id_empresa' => auth()->user()->empresa,
                'monto' => ($data->amount / 100),
                'meses' => $meses,
                'plan' => $data->plan,
                'personalizado' => $data->personalPlan,
                'referencia_pago' => $data->reference,
                'transactionState' => 0,
                'EstadoTransaccion' => 'PENDING',
                'estado' => 0,
                'description' => 'Pago ' . $data->tipo . ' ' . $meses . ' Meses',
            ]
        );

        $suplente_pago->plazo = date('Y-m-d H:m:s', strtotime($suplente_pago->created_at . "+ 1 day"));
        $suplente_pago->save();

        return response()->json("hecho");
    }

    public function suscripciones()
    {
        view()->share(['seccion' => 'nomina', 'subseccion' => 'planes-nomina', 'title' => 'Suscripciones Nómina Electrónica', 'icon' => 'fas fa-money-bill-wave']);
        if (Auth::user()->rol == 1) {
            $suscripcionesPagos = SuscripcionPagoNomina::all();
            $certificado = SuscripcionNomina::where('id_empresa', 1)->first();
            $sw = 1;
            return view('nomina.suscripciones')->with(compact('suscripcionesPagos', 'certificado', 'sw'));
        } else {
            $this->getAllPermissions(Auth::user()->id);
            $suscripcionesPagos = SuscripcionPagoNomina::where('id_empresa', Auth::user()->empresa)
                ->get();
            $sw = 0;
            $certificado = SuscripcionNomina::where('id_empresa', auth()->user()->empresa)->first();
            return view('nomina.suscripciones')->with(compact('suscripcionesPagos', 'certificado', 'sw'));
        }
    }

    /**
     * 
     * Generamos en pdf la nomina NORMAL o la nomina ante la Dian completa de una persona.
     *
     * @return pdf
     */
    public function generarPDFNominaCompleta(Nomina $nomina)
    {

        $this->getAllPermissions(Auth::user()->id);
        $data = $this->nominaDianController->calculoCompletoNomina($nomina->id);
        $totalDetallesNomina = $this->nominaDianController->obtenerTotalDetalleNomina($nomina);

        $empresa = auth()->user()->empresaObj;

        $persona = $data['persona'];
        $totalidad = $data['totalidad'];
        $moneda = $data['moneda'];
        $nominaPeriodo = $data['nominas'];
        $mensajePeriodo = $data['mensajePeriodo'];


        $title = 'RESUMEN DE PAGO ' . $persona->nombre();

        $user = Auth::user();
        $prestacionSocial = $nomina->prestacionesSociales;
        $adicionales = 0;
        $numeracion = $nomina->codigo_dian ? $nomina->codigo_dian : $nomina->nro;

        /* >>> Atributos para uso de la DIAN <<< */
        $codqr = null;
        if ($nomina->emitida == 1 || $nomina->emitida == 5) {
            $codqr = "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$nomina->cune}";
        }


        $pdf = PDF::loadView(
            'pdf.nomina.detalle-completa',
            compact(
                'persona',
                'title',
                'nominaPeriodo',
                'totalidad',
                'user',
                'prestacionSocial',
                'adicionales',
                'mensajePeriodo',
                'totalDetallesNomina',
                'numeracion',
                'nomina',
                'codqr',
                'empresa'
            )
        );

        // return view('pdf.nomina.detalle-completa',compact(
        //     'persona',
        //     'title',
        //     'nominaPeriodo',
        //     'totalidad',
        //     'user',
        //     'prestacionSocial',
        //     'adicionales',
        //     'mensajePeriodo',
        //     'totalDetallesNomina',
        //     'numeracion',
        //     'nomina',
        //     'codqr'
        // ));

        return response($pdf->stream())->withHeaders([
            'Content-Type' => 'application/pdf',
        ]);
    }


    public function nominasAgrupadas($periodo, $year, $tipo = null)
    {

        if (!isset($tipo)) {
            return back()->with('error', 'Se debe especificar el periodo del mes de la nómina');
        }

        $empresa = auth()->user()->empresa;

        $nominas = Nomina::with('nominaperiodos')
            ->where('year', $year)
            ->where('periodo', $periodo)
            ->where('fk_idempresa', $empresa)
            ->get();

        if (count($nominas) === 1) {
            $periodos = $nominas->first()->nominaperiodos;

            $response =  $this->details_pdf($tipo == 1 ? $periodos->first()->id : $periodos->last()->id);
            return $response;
        }


        $invoice_file = time() . "nomina.pdf";

        $oMerger = PDFMerger::init();
        foreach ($nominas as $nomina) {
            $fileName = $nomina->persona->nro_documento . ".pdf";

            $periodos = $nomina->nominaperiodos;

            $response =  $this->details_pdf($tipo == 1 ? $periodos->first()->id : $periodos->last()->id);
            Storage::disk('public')->put("empresa$empresa/nominas/individuales/$fileName", $response);
            $filePath = public_path() . "/storage/empresa$empresa/nominas/individuales/$fileName";

            $oMerger->addPDF($filePath, 'all');  //Add all pages
        }
        $oMerger->merge();
        $oMerger->setFileName($invoice_file);
        // $oMerger->save();

        Storage::disk('public')->deleteDirectory("empresa$empresa/nominas/individuales");


        return response($oMerger->stream())->withHeaders([
            'Content-Type' => 'application/pdf',
        ]);
    }


    public function nominasIndividuales($periodo, $year, $tipo = null)
    {

        if (!isset($tipo)) {
            return back()->with('error', 'Se debe especificar el periodo del mes de la nómina');
        }

        $empresa = auth()->user()->empresa;

        $nominas = Nomina::with('nominaperiodos', 'persona')
            ->where('year', $year)
            ->where('periodo', $periodo)
            ->where('fk_idempresa', $empresa)
            ->get();

        $zipFileName  = time() . 'invoices.zip'; // Name of our archive to download
        $public_dir = public_path();


        // Initializing PHP class
        $zip = new ZipArchive();
        if ($zip->open($public_dir . '/' . $zipFileName, ZipArchive::CREATE) === TRUE) {
            // Add Multiple file
            foreach ($nominas as $nomina) {
                $fileName = $nomina->persona->nro_documento . ".pdf";

                $periodos = $nomina->nominaperiodos;

                $response =  $this->details_pdf($tipo == 1 ? $periodos->first()->id : $periodos->last()->id);
                Storage::disk('public')->put("empresa$empresa/nominas/agrupadas/$fileName", $response);
                $filePath = public_path() . "/storage/empresa$empresa/nominas/agrupadas/$fileName";

                $zip->addFile($filePath, $fileName);
            }
            $zip->close();
            Storage::disk('public')->deleteDirectory("empresa$empresa/nominas/agrupadas");
        }

        $headers = array(
            'Content-Type' => 'application/octet-stream',
        );
        $filetopath = $public_dir . '/' . $zipFileName;
        // Create Download Response
        if (file_exists($filetopath)) {
            return response()
                ->download($filetopath, $zipFileName, $headers)
                ->deleteFileAfterSend(true);
        }
    }

    public function notificarLiquidacion($year, $periodo, $tipo = null)
    {

        try {

            if (!isset($tipo)) {
                return back()->with('error', 'Se debe especificar el periodo del mes de la nómina');
            }

            $empresa =  auth()->user()->empresaObj;

            $nominas = Nomina::with('nominaperiodos', 'persona:id,nombre,apellido,correo', 'empresa:id,nombre,nit')
                ->where('year', $year)
                ->where('periodo', $periodo)
                ->where('fk_idempresa', $empresa->id)
                ->where('isPagado', 1)
                ->get();

            $nominasPdf = Nomina::where('year', $year)
                ->where('periodo', $periodo)
                ->where('fk_idempresa', $empresa->id)
                ->where('isPagado', 1)
                ->get();

            Storage::disk('public')->deleteDirectory("empresa$empresa->id/nominas/reporte");

            $pdfs = [];

            // Add Multiple file
            foreach ($nominasPdf as $key => $nomina) {
                $fileName = time() . "-$key-nomina.pdf";

                $periodos = $nomina->nominaperiodos;

                $response =  $this->details_pdf($tipo == 1 ? $periodos->first()->id : $periodos->last()->id);

                Storage::disk('public')->put("empresa$empresa->id/nominas/reporte/$fileName", $response);
                $filePath = "/empresa$empresa->id/nominas/reporte/$fileName";
                array_push($pdfs, $filePath);
            }

            PagarNomina::dispatch($nominas, $pdfs);

            return back()->with('success', 'Notificación enviada a  través de correo');
        } catch (\Throwable $th) {
            dd($th);
            return back()->with('error', $th->getMessage());
        }
    }


    public function refrescarNomina($idNominaPeriodo){

        $nominaPeriodo = NominaPeriodos::find($idNominaPeriodo);

        if($nominaPeriodo->nomina->fk_idempresa == Auth::user()->empresa){
            $nominaPeriodo->editValorTotal();
        }

        return response()->json(['valorTotal' => $nominaPeriodo->valor_total, 'idPeriodoNomina' => $nominaPeriodo->id]);
    }

}
