<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use DB;
use PDF;
use App\Model\Nomina\NominaTipoContrato;
use App\TipoIdentificacion;
use App\Categoria;
use App\Model\Nomina\Persona;
use App\Model\Nomina\Nomina;
use App\Model\Nomina\NominaPeriodos;
use App\Model\Nomina\NominaPreferenciaPago;
use App\Model\Nomina\NominaCuentasGeneralDetalle;
use App\Model\Nomina\NominaDetalleUno;
use App\Model\Nomina\NominaConfiguracionCalculos;
use App\Model\Nomina\ContratoPersona;
use App\Model\Nomina\ComprobanteLiquidacion;
use App\Model\Nomina\NominaPrestacionSocial;
use App\Model\Nomina\NominaCalculoFijo;

class PersonasController extends Controller
{
    public function __construct()
    {

    }

    /**
     * Lista principal para ver personas registradas.
     *
     * @return view
     */
    public function index(Request $request)
    {
        $usuario = auth()->user();
        $this->getAllPermissions($usuario->id);

        $busqueda = false;
        if ($request->name_1 || $request->name_2 || $request->name_3 || $request->name_4 || $request->name_5 || $request->name_6 || $request->name_7) {
            $busqueda = true;
        }
        $all = true;
        view()->share([
            'seccion' => 'nomina',
            'subseccion' => 'personas-nomina',
            'title' => 'Personas',
            'icon' => 'fas fa-users'
        ]);

        $campos = array(
            'id',
            'nombre',
            'apellido',
            'nro_documento',
            'cargo',
            'sede',
            'termino_contrato',
            'salario_base'
        );
        if (!$request->orderby) {
            $request->orderby = 0;
            $request->order = 1;
        }
        $orderby = $campos[$request->orderby];
        $order = $request->order == 1 ? 'DESC' : 'ASC';
        $appends = array('orderby' => $request->orderby, 'order' => $request->order);
        $personas = Persona::where('id', '>', 0)->with('terminoContrato');
        if ($request->name_1) {
            $appends['name_1'] = $request->name_1;
            $personas = $personas->where('nombre', 'like', '%' . $request->name_1 . '%');
        }
        if ($request->name_2) {
            $appends['name_2'] = $request->name_2;
            $personas = $personas->where('apellido', 'like', '%' . $request->name_2 . '%');
        }
        if ($request->name_3) {
            $appends['name_3'] = $request->name_3;
            $personas = $personas->where('nro_documento', 'like', '%' . $request->name_3 . '%');
        }
        if ($request->name_4) {
            $appends['name_4'] = $request->name_4;
            $personas = $personas->where('fk_sede', $request->name_4);
        }
        if ($request->name_5) {
            $appends['name_5'] = $request->name_5;
            $personas = $personas->where('fk_cargo', $request->name_5);
        }
        if ($request->name_6) {
            $appends['name_6'] = $request->name_6;
            $personas = $personas->where('fk_termino_contrato', $request->name_6);
        }
        if ($request->name_7) {
            $appends['name_7'] = $request->name_7;
            $personas = $personas->where('fk_salario_base', $request->name_7);
        }

        /*>>> Script de creación de configuraciones de calculos fijos <<<*/
        // $empresas = Empresa::all();
        // $configuracionesCalculos = NominaConfiguracionCalculos::where('fk_idempresa',1)->get();
        // foreach($empresas as $empresa){
        //     foreach($configuracionesCalculos as $cnf){
        //         $nominaConfig = new NominaConfiguracionCalculos;
        //         $nominaConfig->nro = $cnf->nro;
        //         $nominaConfig->nombre = $cnf->nombre;
        //         $nominaConfig->tipo = $cnf->tipo;
        //         $nominaConfig->simbolo = $cnf->simbolo;
        //         $nominaConfig->valor = $cnf->valor;
        //         $nominaConfig->observaciones = $cnf->observaciones;
        //         $nominaConfig->fk_idempresa = $empresa->id;
        //         $nominaConfig->save();
        //     }

        // }

         /*>>> Script de creación de centros de costos <<<*/
        //  $empresas = Empresa::all();
        //  $centros = DB::Table('ne_centro_costos')->where('fk_idempresa',1)->get();
        
        //  foreach($empresas as $empresa){
        //     foreach($centros as $centro){
        //         DB::table('ne_centro_costos')->insert([
        //             'nombre' => $centro->nombre,
        //             'prefijo_contable' => $centro->prefijo_contable,
        //             'codigo_contable' => $centro->codigo_contable,
        //             'fk_idempresa' => $empresa->id
        //         ]);
        //     }
        //  }
         

        $personas = $personas->where('fk_empresa', $usuario->empresa);
        $personas = $personas->OrderBy($orderby, $order)->paginate(15)->appends($appends);
        $cargos = DB::table('ne_cargos')->get();
        $sedes = DB::table('ne_sede_trabajo')->get();
        $termino_contratos = DB::table('ne_termino_contrato')->get();
        $salario_bases = DB::table('ne_salario_base')->get();

        // $guiasVistas = DB::connection('mysql')->table('tips_modulo_usuario')
        //     ->select('tips_modulo_usuario.*')
        //     ->join('permisos_modulo', 'permisos_modulo.id', '=', 'tips_modulo_usuario.fk_idpermiso_modulo')
        //     ->where('permisos_modulo.nombre_modulo', 'Nomina')
        //     ->where('fk_idusuario', $usuario->id)
        //     ->get();

        $modoLectura = (object) $usuario->modoLecturaNomina();

        return view('nomina.personas.index')->with(compact(
            'personas',
            'request',
            'busqueda',
            'cargos',
            'sedes',
            'termino_contratos',
            'salario_bases',
            'modoLectura'
        ));
    }

    /**
     * Formulario de crear una persona.
     *
     * @return view
     */
    public function create()
    {
        $this->getAllPermissions(Auth::user()->id);

        $preferenciaPago = NominaPreferenciaPago::where('empresa', auth()->user()->empresa)->first();

        if (!$preferenciaPago) {
            return back()->with('preferencia', '');
        }

        view()->share([
            'seccion' => 'nomina',
            'subseccion' => 'personas-nomina',
            'title' => 'Crear Persona',
            'icon' => 'fas fa-user'
        ]);

        $departamentos = DB::table('departamentos')->get();
        $identificaciones = TipoIdentificacion::all();
        $cargos = DB::table('ne_cargos')->where('fk_idempresa', Auth::user()->empresa)->get();
        $centro_costos = DB::table('ne_centro_costos')->get();
        $clase_riesgos = DB::table('ne_clase_riesgos')->get();
        $epss = DB::table('ne_eps')->get();
        $fondo_cesantias = DB::table('ne_fondo_cesantias')->get();
        $fondo_pensiones = DB::table('ne_fondo_pensiones')->get();
        $salario_bases = DB::table('ne_salario_base')->get();
        $sedes = DB::table('ne_sede_trabajo')->where('fk_idempresa', Auth::user()->empresa)->get();
        $areas = DB::table('ne_areas')->where('fk_idempresa', Auth::user()->empresa)->get();
        $termino_contratos = DB::table('ne_termino_contrato')->get();
        $tipo_contratos = DB::table('ne_tipo_contrato')->get();
        $bancos = DB::table('ne_bancos')->get();
        $metodo_pagos = DB::table('metodos_pago')->get();


        return view('nomina.personas.create')->with(compact(
            'identificaciones',
            'tipo_contratos',
            'termino_contratos',
            'departamentos',
            'cargos',
            'centro_costos',
            'clase_riesgos',
            'epss',
            'fondo_cesantias',
            'fondo_pensiones',
            'salario_bases',
            'sedes',
            'areas',
            'metodo_pagos',
            'bancos',
            'preferenciaPago'
        ));
    }

    /**
     *  Método para guardar el registro de una nueva persona.
     *  También se registra un nuevo detalle de nómina para el periodo actual en que se haga la creación de la persona
     *
     * @return view
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'apellido' => 'required',
            'tipo_documento' => 'required',
            'nro_documento' => 'required',
            'correo' => 'required|email',
            'tipo_contrato' => 'required',
            'termino_contrato' => 'required',
            'fecha_contratacion' => 'required',
            'salario_base' => 'required',
            'valor' => 'required',
            'metodo_pago' => 'required',
            'sede' => 'required',
            'area' => 'required',
            'cargo' => 'required',
            'centro_costo' => 'required',
            'eps' => 'required',
            'fondo_pension' => 'required',
            'fondo_cesantia' => 'required',
            'departamento' => 'required',
            'municipio' => 'required',
        ]);


        $persona = Persona::where('nro_documento', $request->nro_documento)
            ->where('fk_empresa', auth()->user()->empresa)
            ->first();

        if ($persona) {
            return back()->with('danger', 'Persona registrada o el N° de Documento se encuentra en uso')->withInput();
        }

        try {

            DB::beginTransaction();

            $data = $request->all();
            $dias_descanso = '';

            if (isset($data['dias_descanso'])) {
                for ($x = 0; $x < count($data['dias_descanso']); $x++) {
                    $dias_descanso .= $data['dias_descanso'][$x] . ' ';
                }
            }


            $persona = new Persona();

            /* >>> DATOS PRINCIPALES <<< */

            $persona->nombre = $request->nombre;
            $persona->apellido = $request->apellido;
            $persona->fk_empresa = auth()->user()->empresa;
            $persona->fk_tipo_documento = $request->tipo_documento;
            $persona->nro_documento = $request->nro_documento;
            $persona->correo = $request->correo;
            if ($request->nacimiento) {
                $persona->nacimiento = date('Y-m-d', strtotime($request->nacimiento));
            }
            $persona->nro_celular = $request->nro_celular;
            $persona->direccion = $request->direccion;

            /* >>> DATOS PRINCIPALES <<< */

            $persona->fk_tipo_contrato = $request->tipo_contrato;
            $persona->fk_termino_contrato = $request->termino_contrato;
            $persona->fecha_contratacion = date('Y-m-d', strtotime($request->fecha_contratacion));
            $persona->fecha_finalizacion = ($request->termino_contrato == '1') ? null : date('Y-m-d', strtotime($request->fecha_finalizacion));
            $persona->fk_salario_base = $request->salario_base;
            $persona->valor = str_replace('.', '', $request->valor);
            $persona->subsidio = $request->subsidio;
            $persona->fk_clase_riesgo = $request->clase_riesgo;
            $persona->dias_vacaciones = $request->dias_vacaciones;
            $persona->dias_descanso = $dias_descanso;
            $persona->fk_iddepartamento = $request->departamento;
            $persona->fk_idmunicipio = $request->municipio;

            /* >>> DATOS PAGO <<< */

            $persona->fk_metodo_pago = $request->metodo_pago;
            $persona->fk_banco = $request->banco;
            $persona->tipo_cuenta = $request->tipo_cuenta;
            $persona->nro_cuenta = $request->nro_cuenta;

            /* >>> DATOS PUESTO DE TRABAJO <<< */

            $persona->fk_sede = $request->sede;
            $persona->fk_area = $request->area;
            $persona->fk_cargo = $request->cargo;
            $persona->fk_centro_costo = $request->centro_costo;

            /* >>> ENTIDADES DE SEGURIDAD SOCIAL <<< */

            $persona->fk_eps = $request->eps;
            $persona->fk_fondo_pension = $request->fondo_pension;
            $persona->fk_fondo_cesantia = $request->fondo_cesantia;
            $persona->status = 1;
            $persona->save();

            /* >>> CREACION DE NUEVA NOMINA DE LA PERSONA CREADA PARA EL PERIODO ACTUAL <<< */


            $tipoContrato = NominaTipoContrato::find($persona->fk_tipo_contrato);

            $this->nominaPersona($persona, false, false, $tipoContrato);

            DB::commit();

            $mensaje = 'Se ha creado satisfactoriamente la persona';
            return redirect('empresa/nomina/personas')->with('success', $mensaje)->with('persona_id', $persona->id);
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', $th->getMessage());
        }
    }

    /**
     * Formulario para editar una persona.
     *
     * @return view
     */
    public function edit($id)
    {

        $persona = Persona::where('id', $id)->where('fk_empresa', Auth::user()->empresa)->first();
        $usuario = auth()->user();

        $this->getAllPermissions($usuario->id);

        view()->share([
            'seccion' => 'nomina',
            'subseccion' => 'personas-nomina',
            'title' => (request()->reincorporar ? "Reincorporar {$persona->nombre} {$persona->apellido}" : 'Editar Persona'),
            'icon' => 'fas fa-user'
        ]);

        $identificaciones = TipoIdentificacion::all();
        $cargos = DB::table('ne_cargos')->where('fk_idempresa', $usuario->empresa)->get();
        $centro_costos = DB::table('ne_centro_costos')->get();
        $clase_riesgos = DB::table('ne_clase_riesgos')->get();
        $epss = DB::table('ne_eps')->get();
        $fondo_cesantias = DB::table('ne_fondo_cesantias')->get();
        $fondo_pensiones = DB::table('ne_fondo_pensiones')->get();
        $salario_bases = DB::table('ne_salario_base')->get();
        $sedes = DB::table('ne_sede_trabajo')->where('fk_idempresa', $usuario->empresa)->get();
        $areas = DB::table('ne_areas')->where('fk_idempresa', $usuario->empresa)->get();
        $termino_contratos = DB::table('ne_termino_contrato')->get();
        $tipo_contratos = DB::table('ne_tipo_contrato')->get();
        $bancos = DB::table('ne_bancos')->get();
        $metodo_pagos = DB::table('metodos_pago')->get();

        $diasDescanso = explode(" ", $persona->dias_descanso);

        return view('nomina.personas.edit')->with(compact(
            'identificaciones',
            'tipo_contratos',
            'termino_contratos',
            'cargos',
            'centro_costos',
            'clase_riesgos',
            'epss',
            'fondo_cesantias',
            'fondo_pensiones',
            'salario_bases',
            'sedes',
            'areas',
            'metodo_pagos',
            'persona',
            'bancos',
            'diasDescanso'
        ));
    }

    /**
     * Método ara actualizar la información de una persona.
     *
     * @return view
     */
    public function update($id, Request $request)
    {
        $persona = Persona::where('id', $id)->where('fk_empresa', Auth::user()->empresa)->first();
    
        if ($persona) {
            $request->validate([
                'nombre' => 'required',
                'apellido' => 'required',
                'tipo_documento' => 'required',
                'nro_documento' => 'required',
                'correo' => 'required|email',
                'tipo_contrato' => 'required',
                'termino_contrato' => 'required',
                'fecha_contratacion' => 'required',
                'salario_base' => 'required',
                'valor' => 'required',
                'metodo_pago' => 'required',
                'sede' => 'required',
                'area' => 'required',
                'cargo' => 'required',
                'centro_costo' => 'required',
                'eps' => 'required',
                'fondo_pension' => 'required',
                'fondo_cesantia' => 'required'
            ]);

            $fechaContratacionInicial = $persona->fecha_contratacion;

            $data = $request->all();
            $dias_descanso = '';

            if (isset($data['dias_descanso'])) {
                for ($x = 0; $x < count($data['dias_descanso']); $x++) {
                    $dias_descanso .= $data['dias_descanso'][$x] . ' ';
                }
            }

            /* >>> ACTUALIZACION VALOR_TOTAL NOMINA <<< */
            if ($request->valor != $persona->valor) {
                Nomina::where('fk_idpersona', $persona->id)->update([
                    'valor_total' => str_replace('.', '', $request->valor)
                ]);
            }

            /* >>> DATOS PRINCIPALES <<< */

            $persona->nombre = $request->nombre;
            $persona->apellido = $request->apellido;
            $persona->fk_empresa = auth()->user()->empresa;
            $persona->fk_tipo_documento = $request->tipo_documento;
            $persona->nro_documento = $request->nro_documento;
            $persona->correo = $request->correo;
            if ($request->nacimiento) {
                $persona->nacimiento = date('Y-m-d', strtotime($request->nacimiento));
            }
            $persona->nro_celular = $request->nro_celular;
            $persona->direccion = $request->direccion;

            /* >>> DATOS PRINCIPALES <<< */

            $persona->fk_tipo_contrato = $request->tipo_contrato;
            $persona->fk_termino_contrato = $request->termino_contrato;
            $persona->fecha_contratacion = date('Y-m-d', strtotime($request->fecha_contratacion));
            $persona->fecha_finalizacion = ($request->termino_contrato == '1') ? null : date('Y-m-d', strtotime($request->fecha_finalizacion));
            $persona->fk_salario_base = $request->salario_base;
            $persona->subsidio = $request->subsidio;
            $persona->fk_clase_riesgo = $request->clase_riesgo;
            $persona->dias_vacaciones = $request->dias_vacaciones;
            $persona->dias_descanso = $dias_descanso;

            /* >>> DATOS PAGO <<< */

            $persona->fk_metodo_pago = $request->metodo_pago;
            $persona->fk_banco = $request->banco;
            $persona->tipo_cuenta = $request->tipo_cuenta;
            $persona->nro_cuenta = $request->nro_cuenta;

            /* >>> DATOS PUESTO DE TRABAJO <<< */

            $persona->fk_sede = $request->sede;
            $persona->fk_area = $request->area;
            $persona->fk_cargo = $request->cargo;
            $persona->fk_centro_costo = $request->centro_costo;

            /* >>> ENTIDADES DE SEGURIDAD SOCIAL <<< */

            $persona->fk_eps = $request->eps;
            $persona->fk_fondo_pension = $request->fondo_pension;
            $persona->fk_fondo_cesantia = $request->fondo_cesantia;

            if ($request->reincorporar) {
                $tipoContrato = NominaTipoContrato::find($persona->fk_tipo_contrato);
                $data = app(PersonasController::class)->nominaPersona(
                    $persona,
                    date('Y', strtotime($persona->fecha_contratacion)),
                    date('n', strtotime($persona->fecha_contratacion)),
                    $tipoContrato
                );
                
                /* >>> Si depsués de haber generado la nomina de cada persona no queda ninguna  Nomina creada se revalida <<<*/
                $validateNomina = Nomina::where('periodo',  date('n', strtotime($persona->fecha_contratacion)))->where(
                    'year',
                    date('Y', strtotime($persona->fecha_contratacion))
                )->where('fk_idempresa', Auth::user()->empresa)
                ->where('fk_idpersona', $persona->id)
                ->first();
                
                if (!$validateNomina) {
                    return redirect('empresa/nomina/personas')->with('danger', 'ERROR: ocurrio un error al generar la nomina');
                }
            }
            
            /* >>> Si se cambia el valor de pago y la ultima nomina no ha sido emitida <<< */
            if($persona->valor != $valorNuevoPago = str_replace('.', '', $request->valor)){

                $nomina = Nomina::where('fk_idpersona', $persona->id)->orderBy('year','desc')->orderBy('periodo','desc')->first();
                
                if($nomina){
                    if($nomina->emitida != 1 || $nomina->emitida != 3 || $nomina->emitida != 5){
                        $mensaje2= " y la nómina del año " . $nomina->year . " del mes " . $nomina->periodo;
                        foreach ($nomina->nominaperiodos as $nominaPeriodo) {      
                            $nominaPeriodo->pago_empleado = $valorNuevoPago;
                            $nominaPeriodo->save();
                            $nominaPeriodo->editValorTotal();
                        }
                    }
                }
            }

            $persona->valor = $valorNuevoPago;
            $persona->save();
            
            if ($fechaContratacionInicial != $persona->fecha_contratacion) {   
                /* >>> REFRESCAR PERIODO NUEVO <<< */
                $yearPeriodo = date('Y', strtotime($persona->fecha_contratacion));
                $mesPeriodo = date('n', strtotime($persona->fecha_contratacion));

                if(now()->year == $yearPeriodo){
                    $nomina = Nomina::where('year', $yearPeriodo)->where('periodo', $mesPeriodo)->where('fk_idpersona', $persona->id)->first();
                    foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                        // if($nominaPeriodo->is_liquidado == false){
                        $nominaPeriodo->editValorTotal();
                        // }
                    }
    
                    if ($nomina) {
                        foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                            // if($nominaPeriodo->is_liquidado == false){
                            $nominaPeriodo->editValorTotal();
                            // }
                        }
                    }
    
    
                    /* >>> REFRESCAR PERIODO ANTERIOR <<< */
                    $yearPeriodo = date('Y', strtotime($fechaContratacionInicial));
                    $mesPeriodo = date('n', strtotime($fechaContratacionInicial));
    
    
                    $nomina = Nomina::where('year', $yearPeriodo)->where('periodo', $mesPeriodo)->where('fk_idpersona', $persona->id)->first();
    
                    if ($nomina) {
                        foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                            //  if($nominaPeriodo->is_liquidado == false){ Así esté liquidado debe calcular el nuevo valor
                            $nominaPeriodo->editValorTotal();
                            //  }
                        }
                    }
                } 
            }
            
            /* >>> Manera de crear una nomina de nuevo de una persona (mientras tanto por código) <<< */
            // $tipoContrato = NominaTipoContrato::find($persona->fk_tipo_contrato);
            // $this->nominaPersona($persona, false, false, $tipoContrato);
            $mensaje = 'Se ha actualizado satisfactoriamente la persona';
            if(isset($mensaje2)){
            $mensaje = 'Se ha actualizado satisfactoriamente la persona' . $mensaje2;    
            }
            
            return redirect('empresa/nomina/personas')->with('success', $mensaje)->with('persona_id', $persona->id);
        }
        return redirect('empresa/nomina/personas')->with('danger', 'ERROR: No existe un registro con ese ID');
    }

    /**
     * En esta vistase detalla la información general de una persona.
     *
     * @return view
     */
    public function show(Persona $persona)
    {

        $usuario = auth()->user();
        $this->getAllPermissions($usuario->id);
        $persona->load('nomina_tipo_contrato');


        view()->share([
            'seccion' => 'nomina',
            'subseccion' => 'personas-nomina',
            'title' => 'Ver Persona',
            'icon' => 'fas fa-user'
        ]);

        try {
            $moneda =  $usuario->empresaObj->moneda;

            $nominas = Nomina::with('nominaperiodos')
                ->where('fk_idpersona', $persona->id)
                ->orderBy('year', 'desc')
                ->orderBy('periodo', 'desc')
                ->get();


            $i = 0;
            $detalles = array();
            $fechaContratacion = new Carbon($persona->fecha_contratacion);
            $fechaActual = Carbon::now();
            $vacAcumuladas = (($fechaActual->year + 1) - $fechaContratacion->year) * 15;
            $vacPendientes = $vacAcumuladas - (!$persona->dias_vacaciones ? 0 : $persona->dias_vacaciones);

            /*
            foreach($persona->contratos as $contr){
                $vacPendientes = $vacPendientes - (!$contr->dias_vacaciones ? 0 : $contr->dias_vacaciones);
            }
            */

            if ($nominas) {

                foreach ($nominas as $nomina) {
                    $ingresos = 0;
                    $extras = 0;
                    $vacaciones = 0;
                    $deducciones = 0;
                    $total = 0;
                    $periodo = '';
                    $estado = '';
                    foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                        $ingresos += $nominaPeriodo->ingresos();
                        $extras += $nominaPeriodo->extras();
                        $vacaciones += $nominaPeriodo->vacaciones();
                        $deducciones += $nominaPeriodo->deducciones();
                        $total += $nominaPeriodo->valor_total;

                        if($nomina->year >= $fechaContratacion->year && $nomina->year <= $fechaActual->year){
                            $vacPendientes -= $vacaciones;
                        }

                    }
                    //$vacAcumuladas = $vacAcumuladas - $vacaciones;
                    $detalles[$i]['periodo'] = $nomina->periodo();
                    $detalles[$i]['ingresos'] = $ingresos;
                    $detalles[$i]['extras'] = $extras;
                    $detalles[$i]['vacaciones'] = $vacaciones;
                    $detalles[$i]['deducciones'] = $deducciones;
                    $detalles[$i]['total'] = $total;
                    $detalles[$i]['estado'] = $nomina->estado();
                    $detalles[$i]['text'] = $nomina->estado(true);
                    $detalles[$i]['nomina_id'] = $nomina->id;
                    $i++;
                }
            }

            $modoLectura = (object) $usuario->modoLecturaNomina();

            return view('nomina.personas.show', compact('persona', 'detalles', 'vacAcumuladas', 'nominas', 'moneda', 'modoLectura', 'vacPendientes'));
        } catch (\Throwable $th) {
            return redirect()->route('personas.index')->with('danger', 'No se ha encontrado una persona con ese identificador');
        }
    }

    /**
     * Método para eliminiar una persona de base de datos.
     *
     * @return view
     */
    public function destroy($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $persona = Persona::where('id', $id)->where('fk_empresa', Auth::user()->empresa)->first();

        if (!$persona) {
            return redirect('empresa/nomina/personas')->with('danger', 'ERROR: No existe un registro con ese ID');
        }

        /* >>> Si hay al menos una nomina emitida no se puede eliminar la persona <<< */
        foreach ($persona->nominas as $nomina) {
            if ($nomina->emitida == 1) {
                return redirect('empresa/nomina/personas')
                    ->with('danger', 'Esta persona no se puede eliminar por que tiene nóminas electrónicas emitidas');
            }
            if ($nomina->isPagado) {
                return redirect('empresa/nomina/personas')
                    ->with(
                        'danger',
                        'No es posible eliminar; esta persona ya tiene pagos de nomina en periodos anteriores. Por favor utilice la opción "deshabilitar" '
                    );
            }
        }

        $idNominas = $persona->nominas->keyBy('id')->keys();
        $nominaPeriodos = NominaPeriodos::whereIn('fk_idnomina', $idNominas);

        $nominaCuentas = DB::table('ne_nomina_cuentas_detalle')->whereIn(
            'fk_nominaperiodo',
            $nominaPeriodos->get()->keyBy('id')->keys()
        );

        /* >>> Eliminar de la tabla ne_nomina_cuentas_generales_detalle <<< */
        DB::table('ne_nomina_cuentas_generales_detalle')->whereIn(
            'fk_nomina_cuenta',
            $nominaCuentas->get()->keyBy('id')->keys()
        )->delete();

        /* >>> Eliminar de la tabla ne_nomina_cuentas_detalle <<< */
        $nominaCuentas->delete();

        /* >>> Eliminar los periodos de la nomina ne_nomina_periodos <<< */
        $nominaPeriodos->delete();

        /* >>> Eliminar de la tabla ne_nomina <<< */
        $persona->nominas()->delete();

        /* >>> Eliminar de la tabla ne_personas <<< */

        $persona->delete();
        return redirect('empresa/nomina/personas')->with('success', 'Se ha eliminado satisfactoriamente la persona');
    }

    /**
     * Método que se llama por función ajax y crea una nueva sede.
     *
     * @return json_encode
     */
    public function sede(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
        ]);

        if (!$request->nombre) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'El campo nombre es obligatorio';
            echo json_encode($arrayPost);
            exit;
        }
        $sede = DB::table('ne_sede_trabajo')->where('nombre', $request->nombre)->where(
            'fk_idempresa',
            Auth::user()->empresa
        )->first();
        if ($sede) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'Ya hay una sede almacenada con ese mismo nombre';
            return json_encode($arrayPost);
            exit;
        } else {
            $sede = DB::table('ne_sede_trabajo')->insert([
                'nombre' => $request->nombre,
                'fk_idempresa' => Auth::user()->empresa
            ]);
            if ($sede) {
                $sede = DB::table('ne_sede_trabajo')->latest('created_at')->first();
                $arrayPost['status'] = 'OK';
                $arrayPost['id'] = $sede->id;
                $arrayPost['sede'] = $sede->nombre;
                return json_encode($arrayPost);
                exit;
            }
        }
    }

    /**
     * Método que se llama por función ajax y crea una nueva área de trabajo.
     *
     * @return json_enconde
     */
    public function area(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
        ]);

        if (!$request->nombre) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'El campo nombre es obligatorio';
            echo json_encode($arrayPost);
            exit;
        }
        $area = DB::table('ne_areas')->where('nombre', $request->nombre)
            ->where('fk_idempresa', Auth::user()->empresa)
            ->first();
        if ($area) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'Ya hay una área almacenada con ese mismo nombre';
            return json_encode($arrayPost);
            exit;
        } else {
            $area = DB::table('ne_areas')->insert([
                'nombre' => $request->nombre,
                'fk_idempresa' => Auth::user()->empresa
            ]);
            if ($area) {
                $area = DB::table('ne_areas')->latest('created_at')->first();
                $arrayPost['status'] = 'OK';
                $arrayPost['id'] = $area->id;
                $arrayPost['area'] = $area->nombre;
                return json_encode($arrayPost);
                exit;
            }
        }
    }

    /**
     * Método que se llama por función ajax y crea un nuevo cargo.
     *
     * @return json_enconde
     */
    public function cargo(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
        ]);

        if (!$request->nombre) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'El campo nombre es obligatorio';
            echo json_encode($arrayPost);
            exit;
        }
        $cargo = DB::table('ne_cargos')->where('nombre', $request->nombre)
            ->where('fk_idempresa', Auth::user()->empresa)
            ->first();
        if ($cargo) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'Ya hay un cargo almacenado con ese mismo nombre';
            return json_encode($arrayPost);
            exit;
        } else {
            $cargo = DB::table('ne_cargos')->insert([
                'nombre' => $request->nombre,
                'fk_idempresa' => Auth::user()->empresa
            ]);
            if ($cargo) {
                $cargo = DB::table('ne_cargos')->latest('created_at')->first();
                $arrayPost['status'] = 'OK';
                $arrayPost['id'] = $cargo->id;
                $arrayPost['cargo'] = $cargo->nombre;
                return json_encode($arrayPost);
                exit;
            }
        }
    }

    /**
     * Método que guarda el detalle de una nomina para un periodo de una persona.
     *
     * @return void
     */
    public static function nominaPersona($persona, $year = false, $periodo = false, $tipoContrato = false)
    {
        // try{
        //     DB::beginTransaction();
        if(!$persona){
            return '';
        }
        $empresa = auth()->user()->empresa;

        if (!$year) {
            $year = date("Y", strtotime(Carbon::now()));
        }
        
        /* >>> Sino hay periodo es por que no estamos usando la funcion "generar nueva nómina" y podriamos estar creando una persona <<< */
        if (!$periodo) {
            $periodoActual = Nomina::where('fk_idempresa', $empresa)
                ->where('year', $year)
                ->orderBy(
                    'periodo',
                    'DESC'
                )->first();

            if ($periodoActual) {
                $periodo = $periodoActual->periodo;
                $fechaActualNomina = date("y-m", strtotime($periodoActual->year."-".$periodoActual->periodo));
            } else {
                $periodo = date("m", strtotime(Carbon::now()));
                $fechaActualNomina =date("y-m", strtotime(Carbon::now()));
            }
        }else{
            $fechaX = $year . "-" . "01-01";
            $fechaX = Carbon::parse($fechaX);
            $yearCopy = date("y", strtotime($fechaX));
            $fechaActualNomina = Carbon::parse($yearCopy . "-" . $periodo . "-01");
            $fechaActualNomina = date("y-m",strtotime($fechaActualNomina));
        }
        
        $nro = Nomina::where('fk_idempresa', $empresa)->where(
            'periodo',
            ($periodo) ? $periodo : date("m", strtotime(Carbon::now()))
        )->count() + 1;

        /* >>>
        Validamos si la persona puede estar dentro del mes que se está generando ya que pudo ser contratado
        ejm: en julio y se esta generando una nomina de un mes pasado, entonces no se deberia generar una nomina para esa persona
        <<< */
        $fechaContratacion = date('y-m',strtotime($persona->fecha_contratacion)); 
        
        $mesContratacion = date('m', strtotime($persona->fecha_contratacion));
        $periodoGeneradoNomina = $periodoGenerado = ($periodo) ? $periodo : date("m", strtotime(Carbon::now()));
        

        if ($fechaContratacion <= $fechaActualNomina) {

            $nomina = Nomina::where('fk_idpersona', $persona->id)
                            ->where('year', ($year) ? $year : date("Y", strtotime(Carbon::now())))
                            ->where('periodo', $periodoGeneradoNomina)
                            ->where('tipo', 1)
                            ->first();

            /* >>> Primer Registro de estructura de nomina, tabla principal <<< */
            if(!$nomina){
                $nomina = new Nomina();
            }else{
                if($nomina->emitida != 1 && $nomina->emitida != 3 && $nomina->emitida != 5){
                    $nominasPeriodos = NominaPeriodos::where('fk_idnomina', $nomina->id)->get()->keyBy('id')->keys()->all();
                    NominaCuentasGeneralDetalle::whereIn('fk_nominaperiodo', $nominasPeriodos)->delete();
                    NominaDetalleUno::whereIn('fk_nominaperiodo', $nominasPeriodos)->delete();
                    NominaCalculoFijo::whereIn('fk_nominaperiodo', $nominasPeriodos)->delete();
                    NominaPrestacionSocial::where('fk_idnomina', $nomina->id)->delete();
                    NominaPeriodos::where('fk_idnomina', $nomina->id)->delete();
                }else{
                    return '';
                }
            }
            $nomina->nro = $nro;
            $nomina->year = ($year) ? $year : date("Y", strtotime(Carbon::now()));
            $nomina->periodo = $periodoGeneradoNomina;
            $nomina->nota = "";
            $nomina->tipo = 1;
            $nomina->valor_total = $persona->valor;
            $nomina->fk_idempresa = $empresa;
            $nomina->fk_idpersona = $persona->id;

            if($persona->status == 0){
                return '';
            }

            $nomina->save();

            /*>>>  Segundo registro de la estructura de nomina: en donde se espcifican los periodos de
        pago segun las preferencias de pago del cliente en la tabla de ne_nomina_periodos  <<<*/
            $preferenciaPago = NominaPreferenciaPago::where('empresa', $empresa)->first();

            /* >>> CALCULOS PARA ESCOGER LAS FECHAS DESDE Y HASTA <<< */
            if ($year) {
                $generateNomina = $year . "-" . $periodo . "-01";
                $generateNomina = Carbon::parse($generateNomina);
            }

            $start = ($year) ? $generateNomina->startofMonth()->format('Y-m-d') : Carbon::now()->startofMonth()->format('Y-m-d');
            $end = ($year) ? $generateNomina->endOfMonth()->format('Y-m-d') : Carbon::now()->endOfMonth()->format('Y-m-d');

            $start = Carbon::parse($start);
            $end = Carbon::parse($end);

            /* >>> Obtenemos los valores de subsudio de tansporte, salud y pension configurados desde el modulo de calculos fijos. <<< */
            $subsidioTransporte = NominaConfiguracionCalculos::where('fk_idempresa', $empresa)->where('nro', 1)->first();
            $retenSalud = NominaConfiguracionCalculos::where('fk_idempresa', $empresa)->where('nro', 2)->first();
            $retenPension = NominaConfiguracionCalculos::where('fk_idempresa', $empresa)->where('nro', 3)->first();

            if ($persona->fk_tipo_contrato == 3 || $persona->fk_tipo_contrato == 4) {
                $retenSalud->valor = 0;
                $retenPension->valor = 0;
            }

            //pensionado con aporte a salud
            if ($persona->fk_tipo_contrato == 17){
                $retenPension->valor = 0;
            }

            /* >>> Como vamos a crear un detalle por cada periodo entonces creamos un array de los periodos creados en una nomina <<< */
            $arrayNominaPeriodos = array();

            /* >>> Manera de sacar el numero de la nomina perdiodo por empresa (no se repiten) <<< */
            $ultimaNomina = false;
            if (Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
                ->where('ne_nomina.fk_idempresa', $empresa)
                ->select('np.nro')->orderByDesc('np.id')->limit(1)->first()
            ) {
                $ultimaNomina = Nomina::join('ne_nomina_periodos as np', 'np.fk_idnomina', 'ne_nomina.id')
                    ->where('ne_nomina.fk_idempresa', $empresa)
                    ->select('np.nro')->orderByDesc('np.id')->limit(1)->first()->nro;
            }


            if (!$ultimaNomina || $ultimaNomina == null) {
                $nroPeriodo = 1;
            } else {
                $nroPeriodo = $ultimaNomina + 1;
            }

            /* >>> Frecuancia de pago 1 es quincenal <<< */
            if ($preferenciaPago->frecuencia_pago == 1) {
                // $start = "01-02-2024";
                // $end = "29-02-2024";

                $subsidio = ($persona->subsidio == 1) ? (($subsidioTransporte->valor * 15) / 30) : 0;
                $salud = (($persona->valor / 2) * $retenSalud->porcDecimal());
                $pension = (($persona->valor / 2) * $retenPension->porcDecimal());

                $diasMes = $start->diffInDays($end) + 1;

                $divisionDias = $diasMes / 2;
                $decimal = explode(".", $divisionDias);

                $miniPeriodo1 = round($divisionDias, 0, PHP_ROUND_HALF_DOWN);
                $miniPeriodo2 = isset($decimal[1]) ? round($divisionDias, 0) : round($divisionDias, 0, PHP_ROUND_HALF_DOWN);

                /* >>> Validacion de si el mes tiene 28 días se hace de esta manera <<< */
                if ($miniPeriodo1 == 14) {
                    $miniPeriodo1++;
                    $miniPeriodo2--;
                }

                /* >>> Si es quincenal, miniperiodo 1 <<< */
                $nominaPeriodos = new NominaPeriodos;
                $nominaPeriodos->nro = $nroPeriodo;
                $nominaPeriodos->fecha_desde = $start->format('Y-m-d');
                $nominaPeriodos->fecha_hasta = $start->addDays($miniPeriodo1 - 1)->format(('Y-m-d'));
                $nominaPeriodos->valor_total = (((($persona->valor / 2) + $subsidio) - $salud) - $pension);
                $nominaPeriodos->pago_empleado = $persona->valor;
                $nominaPeriodos->mini_periodo = 2;
                $nominaPeriodos->periodo = 1;
                $nominaPeriodos->fk_idnomina = $nomina->id;


                if ($fechaContratacion == $fechaActualNomina) {

                    $diaContratacion = date('y-d', strtotime($persona->fecha_contratacion));
                    $diaDesdeNomina = date('y-d', strtotime($nominaPeriodos->fecha_desde));
                    $diaHastaNomina = date('y-d', strtotime($nominaPeriodos->fecha_hasta));

                    if ($diaContratacion >= $diaDesdeNomina && $diaContratacion <= $diaHastaNomina) {
                        $nominaPeriodos->save();
                        $nominaPeriodos->editValorTotal();
                        array_push(
                            $arrayNominaPeriodos,
                            ($nominaPeriodos->id)
                        );
                    }

                } else {
                    if (strtotime($nominaPeriodos->fecha_desde) >= strtotime($persona->fecha_contratacion)) {
                        $nominaPeriodos->save();
                        $nominaPeriodos->editValorTotal();
                        array_push(
                            $arrayNominaPeriodos,
                            ($nominaPeriodos->id)
                        );
                    }
                }

                /* >>> Si es quincenal, miniperiodo 2 <<< */
                $nominaPeriodos = new NominaPeriodos;
                $nominaPeriodos->nro = $nroPeriodo + 1;
                $nominaPeriodos->fecha_desde = $start->addDays(1)->format(('Y-m-d'));
                $nominaPeriodos->fecha_hasta = $end->format('Y-m-d');
                $nominaPeriodos->valor_total = (((($persona->valor / 2) + $subsidio) - $salud) - $pension);
                $nominaPeriodos->pago_empleado = $persona->valor;
                $nominaPeriodos->mini_periodo = 2;
                $nominaPeriodos->periodo = 2;
                $nominaPeriodos->fk_idnomina = $nomina->id;

                /* >>> Validamos si la persona puede estar dentro del mes que se está generando <<< */
                if ($mesContratacion == $periodoGenerado) {

                    $diaContratacion = date('y-d', strtotime($persona->fecha_contratacion));
                    $diaHastaNomina = date('y-d', strtotime($nominaPeriodos->fecha_hasta));

                    if ($diaContratacion < $diaHastaNomina) {
                        $nominaPeriodos->save();
                        $nominaPeriodos->editValorTotal();
                        array_push(
                            $arrayNominaPeriodos,
                            ($nominaPeriodos->id)
                        );
                    }

                } else {
                    if (strtotime($nominaPeriodos->fecha_desde) >= strtotime($persona->fecha_contratacion)) {
                        $nominaPeriodos->save();
                        $nominaPeriodos->editValorTotal();
                        array_push(
                            $arrayNominaPeriodos,
                            ($nominaPeriodos->id)
                        );
                    }
                }
            } else {
                $subsidio = ($persona->subsidio == 1) ? (($subsidioTransporte->valor * 30) / 30) : 0;
                $salud = (($persona->valor) * $retenSalud->porcDecimal());
                $pension = (($persona->valor) * $retenPension->porcDecimal());

                $nominaPeriodos = new NominaPeriodos;
                $nominaPeriodos->nro = $nroPeriodo;
                $nominaPeriodos->fecha_desde = $start->format('Y-m-d');
                $nominaPeriodos->fecha_hasta = $end->format('Y-m-d');
                $nominaPeriodos->valor_total = (((($persona->valor / 1) + $subsidio) - $salud) - $pension);
                $nominaPeriodos->pago_empleado = $persona->valor;
                $nominaPeriodos->mini_periodo = 1;
                $nominaPeriodos->periodo = 0;
                $nominaPeriodos->fk_idnomina = $nomina->id;
                $nominaPeriodos->save();

                /* >>> SUBSIDIO - SALUD - PENSION <<< */
                $nominaPeriodos->editValorTotal();

                array_push(
                    $arrayNominaPeriodos,
                    ($nominaPeriodos->id)
                );
            }

            /* >>> Tercer registro de estructura de nomina: tabla de detalle para los 4 conceptos principales de las cuentas
        Horas Extras y recargos, Vacaciones / incap / lic, Ingresos Adicionales, Deducciones, prestaciones y retefuente <<<< */

            /* >>> obtenemos todas las cuentas GENERALES(4) de la nomina por las que pueden variar los pagos de un empleado <<< */
            $nominaCuentas = DB::table('ne_nomina_cuentas')->get();

            /* >>> Recorremos los diferentes periodos que puede tener una nómina para genera la estructura madre <<< */
            foreach ($arrayNominaPeriodos as $periodo => $keyPeriodo) {

                $cuentasGeneralDetalleCollection = collect([]);

                foreach ($nominaCuentas as $cuenta) {

                    DB::table('ne_nomina_cuentas_generales_detalle')->insert([
                        'fk_nominaperiodo' => $keyPeriodo,
                        'fk_nomina_cuenta' => $cuenta->id
                    ]);

                    $cuentasGeneralDetalle = NominaCuentasGeneralDetalle::where('fk_nominaperiodo', $keyPeriodo)->where('fk_nomina_cuenta', $cuenta->id)->first();

                    $cuentasGeneralDetalleCollection->push([
                        'fk_nominaperiodo' => $keyPeriodo,
                        'fk_nomina_cuenta' => $cuenta->id = $cuenta->id,
                        'cuentasTipo' => $cuentasGeneralDetalle->nominaCuentasTipo,
                    ]);
                }

                /* >>>
                Cuarto registro de estructura nomina: tabla de detalle PRINCIPAL donde detallamos el valor sobre hora ordinaria,
                # de horas de algunas categorias, pagoanticipado de vacaciones, dias compensados de vacac. y si hay algunos rangos
                de fechas donde la persona salio a vacaciones se guarda una clave foranea para detallar ese rango
                en NominaDetalleDos (estatabla guarda n registros para una misma ne_nomina ya que se detalla categoria x categoria)
                `nombre`, `numero_horas`, `valor_hora_ordinaria`, `valor_categoria`, `pago_anticipado`,
                `dias_compensados_dinero`, `fk_nomina_cuenta_tipo`, `fk_nomina_cuenta`, `fk_nomina_cuenta_ddos`, `fk_categoria`
             <<< */

                foreach ($cuentasGeneralDetalleCollection as $cgd) {
                    foreach ($cgd['cuentasTipo'] as $tipo) {
                        if ($tipo->fk_nomina_cuenta == 1 || $tipo->fk_nomina_cuenta == 2) {
                            $categorias = Categoria::where('empresa', $empresa)->where(
                                'fk_nomcuenta_tipo',
                                $tipo->id
                            )->get();

                            foreach ($categorias as $cat) {
                                $nominaDetalleUno = new NominaDetalleUno();
                                $nominaDetalleUno->nombre = $cat->nombre;
                                $nominaDetalleUno->numero_horas = null;
                                $nominaDetalleUno->valor_hora_ordinaria = $cat->valor_hora_ordinaria;
                                $nominaDetalleUno->valor_categoria = null;
                                $nominaDetalleUno->pago_anticipado = null;
                                $nominaDetalleUno->dias_compensados_dinero = null;
                                $nominaDetalleUno->fk_nominaperiodo = $keyPeriodo;
                                $nominaDetalleUno->fk_nomina_cuenta_tipo = $tipo->id;
                                $nominaDetalleUno->fk_nomina_cuenta = $cgd['fk_nomina_cuenta'];
                                $nominaDetalleUno->fk_categoria = $cat->id;
                                $nominaDetalleUno->save();
                            }
                        }
                    }
                }
            }
        } //* validacion de mescontrtacion > periodo generado *//
        // DB::commit();
        return "";
        //   }catch (\Throwable $th) {
        //         DB::rollback();
        //         return $th->getMessage();
        //     }
    }

    public function act_desc($idPersona)
    {
        $persona = Persona::where('id', $idPersona)->where('fk_empresa', Auth::user()->empresa)->first();

        if ($persona) {
            if ($persona->status == 1) {
                $persona->status = 0;
                $mensaje = 'La persona fue desactivada con éxito.';
            } else {
                $persona->status = 1;
                $mensaje = "Persona activada correctamente.";
            }
            $persona->update();
            return back()->with('success', $mensaje);
        } else {
            return back()->with('error', 'La persona ya no existe.');
        }
    }

    public function liquidar($id)
    {
        $this->getAllPermissions(Auth::user()->id);

        $persona = Persona::where('id', $id)->where('fk_empresa', Auth::user()->empresa)->first();
        $fechaContratacion = new Carbon($persona->fecha_contratacion);
        $fechaActual = Carbon::now();
        $nominas = Nomina::where('fk_idpersona', $persona->id)->where('fk_idempresa', Auth::user()->empresa)->latest('year')->limit(12)->get();
        $nPeriodos = 1;
        $sumaMesesPago = 0;
        $vacaciones = 0;
        $cesantias = 0;
        $prima = 0;
        $vacAcumuladas = (($fechaActual->year + 1) - $fechaContratacion->year) * 15;

        if ($nominas->count() == 0) {
            return back()->with('error', 'La persona no ha generado ninguna nomina, imposible liquidar');
        }

        foreach ($nominas as $nomina) {
            $nominaPeriodos =  $nomina->nominaperiodos;
            foreach ($nominaPeriodos as $periodo) {
                $totalidad = $periodo->resumenTotal();
                $sumaMesesPago += $totalidad['pago']['total'];
                $vacaciones += $totalidad['pago']['salario'];
                $prima += $totalidad['salarioSubsidio']['total'];
                $cesantias += $totalidad['salarioSubsidio']['total'];
                $vacAcumuladas = $vacAcumuladas - $periodo->vacaciones();
            }

            /*
           $prestacionesSociales = $nomina->prestacionesSociales;
           $prima += $prestacionesSociales->sum('prima');
           $cesantias += $prestacionesSociales->sum('cesantia');
           */

            $nPeriodos += $nominaPeriodos->count();
        }

        $salarioBase = $sumaMesesPago / $nominas->count();
        $prima = $prima / $nominas->count();
        $cesantias = $cesantias / $nominas->count();


        $diasLiquidar = $fechaContratacion->diffInDays(now()) + 1;

        view()->share([
            'seccion' => 'nomina',
            'subseccion' => 'personas-nomina',
            'title' => 'Liquidar a ' . $persona->nombre(),
            'icon' => 'fas fa-user'
        ]);

        return view('nomina.personas.liquidar', compact('salarioBase', 'vacaciones', 'cesantias', 'prima', 'persona', 'fechaContratacion', 'diasLiquidar', 'vacAcumuladas'));
    }

    public function storeLiquidar(Request $request)
    {


        $request->validate([
            'motivo' => 'required',
            'isCausal' => 'required',
            'isPrueba' => 'required',
            'diasLiquidar' => 'required',
            'diasVacaciones' => 'required',
            'salarioBase' => 'required',
            'vacaciones' => 'required',
            'cesantias' => 'required',
            'prima' => 'required',
            'isIncluirDominicales' => 'required',
            'otrosIngresos' => 'required',
            'valorPrestamos' => 'required',
            'otrasDeducciones' => 'required',
            'fechaContratacion' => 'required',
            'fechaTerminacion' => 'required',
            'total' => 'required',
            'idPersona' => 'required'
        ]);

        $comprobanteLiquidacion = new ComprobanteLiquidacion();

        $comprobanteLiquidacion->motivo = $request->motivo;
        $comprobanteLiquidacion->is_justa_causa = $request->isCausal;
        $comprobanteLiquidacion->is_periodo_prueba    = $request->isPrueba;
        $comprobanteLiquidacion->dias_liquidar = $request->diasLiquidar;
        $comprobanteLiquidacion->dias_vacaciones = $request->diasVacaciones;
        $comprobanteLiquidacion->base_salario     = str_replace(',', '', $request->salarioBase);
        $comprobanteLiquidacion->base_vacaciones  = str_replace(',', '', $request->vacaciones);
        $comprobanteLiquidacion->base_cesantias   = str_replace(',', '', $request->cesantias);
        $comprobanteLiquidacion->base_prima   = str_replace(',', '', $request->prima);
        $comprobanteLiquidacion->is_dominicales   = $request->isIncluirDominicales;
        $comprobanteLiquidacion->otros_ingresos   = str_replace(',', '', $request->otrosIngresos);
        $comprobanteLiquidacion->valor_prestamos  = str_replace(',', '', $request->valorPrestamos);
        $comprobanteLiquidacion->otras_deducciones  = str_replace(',', '', $request->otrasDeducciones);
        $comprobanteLiquidacion->notas = $request->notas;

        $comprobanteLiquidacion->fecha_contratacion = date('Y-m-d', strtotime($request->fechaContratacion));
        $comprobanteLiquidacion->fecha_terminacion   = date('Y-m-d', strtotime($request->fechaTerminacion));
        $comprobanteLiquidacion->total = $request->total;

        $totalNomina = NominaPeriodos::join('ne_nomina', 'ne_nomina.id', '=', 'ne_nomina_periodos.fk_idnomina')
            ->select('ne_nomina_periodos.valor_total')
            ->where('ne_nomina.fk_idpersona', $request->idPersona)
            ->where('ne_nomina.year', date('Y', strtotime($comprobanteLiquidacion->fecha_terminacion)))
            ->where('ne_nomina.periodo', date('n', strtotime($comprobanteLiquidacion->fecha_terminacion)))
            ->where('ne_nomina_periodos.fecha_hasta', '<=', $comprobanteLiquidacion->fecha_terminacion)
            ->where('ne_nomina_periodos.fecha_desde', '<=', $comprobanteLiquidacion->fecha_terminacion)
            ->first();

        if ($totalNomina) {
            $comprobanteLiquidacion->total_nomina = $totalNomina->valor_total;
        }

        $comprobanteLiquidacion->save();

        $persona = Persona::find($request->idPersona);

        $contratoPersona = new ContratoPersona();
        $contratoPersona->fk_idcomprobante_liquidacion = $comprobanteLiquidacion->id;
        $contratoPersona->fk_idpersona = $persona->id;
        $contratoPersona->fk_tipo_contrato = $persona->fk_tipo_contrato;
        $contratoPersona->fk_termino_contrato = $persona->fk_termino_contrato;
        $contratoPersona->fecha_contratacion = $persona->fecha_contratacion;
        $contratoPersona->fk_clase_riesgo = $persona->fk_clase_riesgo;
        $contratoPersona->dias_descanso = $persona->dias_descanso;
        $contratoPersona->dias_vacaciones = $persona->dias_vacaciones;
        $contratoPersona->status = 1;
        $contratoPersona->save();

        $persona->status = 0;
        $persona->is_liquidado    = 1;
        $persona->update();

        return redirect()->route('personas.show', $persona->id);
    }

    public function destroyLiquidar($id)
    {

        $contratoPersona = ContratoPersona::find($id);
        if($contratoPersona->persona->fk_empresa != Auth::user()->empresa){
            return false;
        }
        $comprobanteLiquidacion = $contratoPersona->comprobanteLiquidacion;

        $persona = $contratoPersona->persona;
        $persona->is_liquidado = 0;
        $persona->status = 1;

        $contratoPersona->delete();
        $comprobanteLiquidacion->delete();
        $persona->update();

        return back();
    }

    public function editLiquidar($id)
    {

        $this->getAllPermissions(Auth::user()->id);

        $contratoPersona = ContratoPersona::find($id);
        if($contratoPersona->persona->fk_empresa != Auth::user()->empresa){
            return false;
        }
        $comprobanteLiquidacion = $contratoPersona->comprobanteLiquidacion;
        $persona = $contratoPersona->persona;


        view()->share([
            'seccion' => 'nomina',
            'subseccion' => 'personas-nomina',
            'title' => 'Liquidación de ' . $persona->nombre(),
            'icon' => 'fas fa-user'
        ]);


        return view('nomina.personas.edit-liquidar', compact('comprobanteLiquidacion', 'persona', 'contratoPersona'));
    }


    public function updateLiquidar(Request $request)
    {

        $request->validate([
            'motivo' => 'required',
            'isCausal' => 'required',
            'isPrueba' => 'required',
            'diasLiquidar' => 'required',
            'diasVacaciones' => 'required',
            'salarioBase' => 'required',
            'vacaciones' => 'required',
            'cesantias' => 'required',
            'prima' => 'required',
            'isIncluirDominicales' => 'required',
            'otrosIngresos' => 'required',
            'valorPrestamos' => 'required',
            'otrasDeducciones' => 'required',
            'fechaContratacion' => 'required',
            'fechaTerminacion' => 'required',
            'total' => 'required',
            'idPersona' => 'required'
        ]);

        $comprobanteLiquidacion = ComprobanteLiquidacion::find($request->idComprobante);

        $comprobanteLiquidacion->motivo = $request->motivo;
        $comprobanteLiquidacion->is_justa_causa = $request->isCausal;
        $comprobanteLiquidacion->is_periodo_prueba    = $request->isPrueba;
        $comprobanteLiquidacion->dias_liquidar = $request->diasLiquidar;
        $comprobanteLiquidacion->dias_vacaciones = $request->diasVacaciones;
        $comprobanteLiquidacion->base_salario     = str_replace(',', '', $request->salarioBase);
        $comprobanteLiquidacion->base_vacaciones  = str_replace(',', '', $request->vacaciones);
        $comprobanteLiquidacion->base_cesantias   = str_replace(',', '', $request->cesantias);
        $comprobanteLiquidacion->base_prima   = str_replace(',', '', $request->prima);
        $comprobanteLiquidacion->is_dominicales   = $request->isIncluirDominicales;
        $comprobanteLiquidacion->otros_ingresos   = str_replace(',', '', $request->otrosIngresos);
        $comprobanteLiquidacion->valor_prestamos  = str_replace(',', '', $request->valorPrestamos);
        $comprobanteLiquidacion->otras_deducciones  = str_replace(',', '', $request->otrasDeducciones);
        $comprobanteLiquidacion->notas = $request->notas;

        $comprobanteLiquidacion->fecha_contratacion = date('Y-m-d', strtotime($request->fechaContratacion));
        $comprobanteLiquidacion->fecha_terminacion   = date('Y-m-d', strtotime($request->fechaTerminacion));
        $comprobanteLiquidacion->total = $request->total;
        $comprobanteLiquidacion->update();

        $persona = Persona::find($request->idPersona);

        $persona->status = 0;
        $persona->is_liquidado    = 1;
        $persona->update();

        return redirect()->route('personas.show', $persona->id);
    }

    public function reincorporar($idPersona)
    {
        $persona = Persona::find($idPersona);
        $persona->is_liquidado = 0;
        $persona->status = 1;
        $persona->update();

        return redirect()->route('personas.edit', ['persona' => $persona->id, 'reincorporar' => 'si']);
    }

    public function imprimirLiquidacion($idContrato)
    {

        $this->getAllPermissions(Auth::user()->id);

        $contrato = ContratoPersona::find($idContrato);
        $liquidacion = $contrato->comprobanteLiquidacion;
        $persona = $contrato->persona;
        $title = 'RESUMEN DE LIQUIDACION ' . $persona->nombre();
        $user = Auth::user();
        $totalidad = $liquidacion->totalidad(request()->nomina);

        $pdf = PDF::loadView('pdf.nomina.liquidacion-empleado', compact('contrato', 'persona', 'liquidacion', 'title', 'user', 'totalidad'));
        return response($pdf->stream())->withHeaders([
            'Content-Type' => 'application/pdf',
        ]);
    }
}
