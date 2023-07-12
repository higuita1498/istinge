<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;
use App\Model\Nomina\NominaPrestacionSocial;
use App\Model\Nomina\Nomina;
use App\Model\Nomina\Persona;
use Barryvdh\DomPDF\Facade as PDF;
use Validator;

class NominaPrestacionSocialController extends Controller
{


    public function __construct()
    {
        // $this->middleware('can_access_to_page:161')->only('imprimir');
        // $this->middleware('can_access_to_page:165')->only('prima', 'cesantias', 'interesesCesantias');
    }

    public function prima(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);

        $year = $request->year;
        $periodo = $request->periodo;
        $rango = $request->rango;
        $tipo = $request->tipo;

        if ($periodo <= 6) {
            $prima = 1;
            $desde = 1;
            $hasta = 6;
        } else {
            $prima = 2;
            $desde = 7;
            $hasta = 12;
        }


        $nominas = Nomina::with('nominaperiodos')
            ->whereNotIn('emitida', [1, 3, 5, 6])
            ->where('ne_nomina.year', $year)
            ->where('periodo', '>=', $desde)
            ->where('periodo', '<=', $hasta)
            ->where('estado_nomina',1)
            ->where('fk_idempresa', Auth::user()->empresa);

        if($request->persona){
           $nominas = $nominas->where('fk_idpersona', $request->persona);
        }

        $nominas = $nominas->latest()->get();


        $totalidades = [];

        foreach ($nominas as $key => $nomina) {
            if($nomina->persona->is_liquidado){
              //  unset($nominas[$key]);
            }

            foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                if (!isset($totalidades[$nomina->fk_idpersona])) {
                    $totalidades[$nomina->fk_idpersona] = [];
                }

                $totalidad = $nominaPeriodo->resumenTotal();
                $totalidad['idNomina'] = $nomina->id;

                $totalidades[$nomina->fk_idpersona][] = $totalidad;
            }
        }

        $totalidadesPersonas = collect($totalidades);
        $personas = Persona::whereIn('id', $totalidadesPersonas->keys())->get();

        foreach ($personas as $key => $persona) {
            $totalidad = collect($totalidadesPersonas[$persona->id]);
            $base = ['diasTrabajados' => 0, 'salarioSubsidio' => 0];
            $base['diasTrabajados'] = $totalidad->sum('diasTrabajados.diasPeriodo');
            $base['diasTrabajadosFijos'] = $base['diasTrabajados'];
            $base['salarioSubsidio'] = ($base['salarioBase'] = (($base['totalSalarioPeriodo'] = $totalidad->sum('salarioSubsidio.salario')) * 30) / $base['diasTrabajados']) + ($base['subsidioTransporte'] = ($totalidad->sum('salarioSubsidio.subsidioTransporte') / ($data['diasPeriodos'] = $totalidad->groupBy('idNomina')->count())));
            if ($base['diasTrabajados'] <= 30) {
                $base['diasTrabajados'] = 180;
            }
            $base['valorTotal'] = ($base['salarioSubsidio'] * $base['diasTrabajados']) / 360;
            $persona->totalidades = $base;
            $persona->nominaSeleccionada = $nominas->where('periodo', $periodo)->where('fk_idpersona',
                $persona->id)->first();
        }


        view()->share([
            'seccion' => 'nomina',
            'title' => 'Prima de servicios ' . $year . '-' . $prima,
            'icon' => 'fas fa-dollar-sign'
        ]);
        return view('nomina.prestacion-social.primas',
            compact('year', 'periodo', 'rango', 'prima', 'personas', 'desde', 'hasta', 'request'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id_nominas.*' => 'required',
            'nombres.*' => 'required|string',
            'bases.*' => 'required|numeric',
            'diasTrabajados.*' => 'required|numeric|min:0|max:365',
            'valores.*' => 'required|numeric|min:0',
            'valoresPagar.*' => 'required|numeric|min:0',
        ], [
            'required' => 'Este campo es obligatorio.',
            'numeric' => 'Este campo debe ser numerico',
            'min' => 'El campo debe ser al menos :min',
            'max' => 'El campo  no debe ser mayor a :max'
        ])->validate();


        foreach ($request->id_nominas as $key => $nomina) {

            $nombre = $request->nombres[$key];
            $base = $request->bases[$key];
            $diasTrabajados = $request->diasTrabajados[$key];
            $valor = $request->valores[$key];
            $valorPagar = $request->valoresPagar[$key];

            // if ($valorPagar > $valor) {
            //     return back()->with('error',
            //         'Error al guardar. Los valores a pagar deben ser inferiores al valor total');
            // }

            $prestacionSocial = NominaPrestacionSocial::where('fk_idnomina', $nomina)->where('nombre',
                $nombre)->first();

            if (!$prestacionSocial) {
                if($valorPagar){
                    $prestacionSocial = new NominaPrestacionSocial();
                }else{
                    continue;
                }
            }

            $prestacionSocial->nombre = $nombre;
            $prestacionSocial->base = $base;
            $prestacionSocial->dias_trabajados = $diasTrabajados;
            $prestacionSocial->valor = $valor;
            $prestacionSocial->valor_pagar = $valorPagar;
            $prestacionSocial->fk_idnomina = $nomina;
            $prestacionSocial->save();

        }
        return back();
    }

    public function cesantias(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);

        $year = $request->year;
        $periodo = $request->periodo;
        $rango = $request->rango;
        $tipo = $request->tipo;
        $subYear = 1;

        if($request->presente == 'si'){
            $subYear = 0;
        }

        $nominas = Nomina::with('nominaperiodos')
            ->where('ne_nomina.year', $year - $subYear)
            //    ->where('ne_nomina.periodo', $periodo)
            ->where('fk_idempresa', Auth::user()->empresa);

        if($request->persona){
            $nominas = $nominas->where('fk_idpersona', $request->persona);
        }

        $nominas = $nominas->get();

        $totalidades = [];


        foreach ($nominas as $key => $nomina) {
            if($nomina->persona->is_liquidado){
               // unset($nominas[$key]);
            }
            foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                if (!isset($totalidades[$nomina->fk_idpersona])) {
                    $totalidades[$nomina->fk_idpersona] = [];
                }

                $totalidad = $nominaPeriodo->resumenTotal();
                $totalidad['idNomina'] = $nomina->id;

                $totalidades[$nomina->fk_idpersona][] = $totalidad;
            }
        }

        $totalidadesPersonas = collect($totalidades);
        $personas = Persona::whereIn('id', $totalidadesPersonas->keys())->get();

        foreach ($personas as $key => $persona) {
            $totalidad = collect($totalidadesPersonas[$persona->id]);
            $base = ['diasTrabajados' => 0, 'salarioSubsidio' => 0];
            $base['diasTrabajados'] = $totalidad->sum('diasTrabajados.diasPeriodo');
            $base['diasTrabajadosFijos'] = $base['diasTrabajados'];
            $base['salarioSubsidio'] = ($base['salarioBase'] = (($base['totalSalarioPeriodo'] = $totalidad->sum('salarioSubsidio.salario') * 30) / $base['diasTrabajados'])) + ($base['subsidioTransporte'] = ($totalidad->sum('salarioSubsidio.subsidioTransporte') / ($totalidad->groupBy('idNomina')->count())));
            $base['valorTotal'] = ($base['salarioSubsidio'] * $base['diasTrabajados']) / 360;
            $persona->totalidades = $base;
            $persona->nominaSeleccionada = Nomina::with('nominaperiodos')
                                                ->whereNotIn('ne_nomina.emitida', [1, 3, 5, 6])
                                                ->where('ne_nomina.year', $year)
                                                ->where('ne_nomina.periodo', $periodo)
                                                ->where('fk_idempresa', Auth::user()->empresa)
                                                ->where('fk_idpersona', $persona->id)
                                                ->latest()
                                                ->first();
        }
        
        $personasVigentes = Persona::where('fk_empresa', Auth::user()->empresa);
        
        if($request->persona){
            $personasVigentes->where('id', $request->persona);
        }
        
        $personasVigentes->get();
    
        foreach($personasVigentes as $kV => $pV){
            
            if(!isset($totalidadesPersonas->all()[$pV->id])){
                
                $nominas = Nomina::with('nominaperiodos')
                ->where('ne_nomina.year', $year)
                //    ->where('ne_nomina.periodo', $periodo)
                ->where('fk_idempresa', Auth::user()->empresa)
                ->where('fk_idpersona', $pV->id);
    
                $nominas = $nominas->get();
    
                $totalidad = collect([]);

                foreach ($nominas as $key => $nomina) {
                    foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                        $totalidad->push($nominaPeriodo->resumenTotal());
                    }
                }
                
                

                if($totalidad->count() > 0){

                    $base = ['diasTrabajados' => 0, 'salarioSubsidio' => 0];
                    $base['diasTrabajados'] = $totalidad->sum('diasTrabajados.diasPeriodo');
                    $base['diasTrabajadosFijos'] = $base['diasTrabajados'];
                    $base['salarioSubsidio'] = ($base['salarioBase'] = (($base['totalSalarioPeriodo'] = $totalidad->sum('salarioSubsidio.salario') * 30) / $base['diasTrabajados'])) + ($base['subsidioTransporte'] = ($totalidad->sum('salarioSubsidio.subsidioTransporte') / ($totalidad->count())));
                    $base['valorTotal'] = ($base['salarioSubsidio'] * $base['diasTrabajados']) / 360;
                    $pV->totalidades = $base;
                    $pV->nominaSeleccionada = $nominas->where('periodo', $periodo)->first();

                }else{
                    unset($personasVigentes[$kV]);
                }

            }else{
                unset($personasVigentes[$kV]);
            }

        }
      
        $personas = $personas->concat($personasVigentes);
        
        view()->share(['seccion' => 'nomina', 'title' => 'Cesantias ' . $year, 'icon' => 'fas fa-dollar-sign']);
        return view('nomina.prestacion-social.cesantias', compact('year', 'periodo', 'rango', 'personas', 'request'));
    }

    public function interesesCesantias(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);

        $year = $request->year;
        $periodo = $request->periodo;
        $rango = $request->rango;
        $tipo = $request->tipo;
        $subYear = 1;

        if($request->presente == 'si'){
            $subYear = 0;
        }

        $nominas = Nomina::with('nominaperiodos')
            ->where('ne_nomina.year', $year - $subYear)
            //    ->where('ne_nomina.periodo', $periodo)
            ->where('fk_idempresa', Auth::user()->empresa);

        if($request->persona){
            $nominas = $nominas->where('fk_idpersona', $request->persona);
        }

        $nominas = $nominas->get();

        $totalidades = [];


        foreach ($nominas as $key => $nomina) {
            if($nomina->persona->is_liquidado){
               // unset($nominas[$key]);
            }
            foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                if (!isset($totalidades[$nomina->fk_idpersona])) {
                    $totalidades[$nomina->fk_idpersona] = [];
                }

                $totalidad = $nominaPeriodo->resumenTotal();
                $totalidad['idNomina'] = $nomina->id;

                $totalidades[$nomina->fk_idpersona][] = $totalidad;
            }
        }

        $totalidadesPersonas = collect($totalidades);
        $personas = Persona::whereIn('id', $totalidadesPersonas->keys())->get();

        foreach ($personas as $key => $persona) {
            $totalidad = collect($totalidadesPersonas[$persona->id]);
            $base = ['diasTrabajados' => 0, 'salarioSubsidio' => 0];
            $base['diasTrabajados'] = $totalidad->sum('diasTrabajados.diasPeriodo');

            $base['diasTrabajadosFijos'] = $base['diasTrabajados'];
            $base['salarioSubsidio'] = ($base['salarioBase'] = (($base['totalSalarioPeriodo'] = $totalidad->sum('salarioSubsidio.salario') * 30) / $base['diasTrabajados'])) + ($base['subsidioTransporte'] = ($totalidad->sum('salarioSubsidio.subsidioTransporte') / ($totalidad->groupBy('idNomina')->count())));

            $base['cesantiaBase'] = ($base['salarioSubsidio'] * $base['diasTrabajados']) / 360;
            $base['interesesCesantia'] = $base['cesantiaBase'] * 0.12 * $base['diasTrabajados'] / 360;

            $persona->totalidades = $base;
            $persona->nominaSeleccionada = Nomina::with('nominaperiodos')
                                                ->whereNotIn('ne_nomina.emitida', [1, 3, 5, 6])
                                                ->where('ne_nomina.year', $year)
                                                ->where('ne_nomina.periodo', $periodo)
                                                ->where('fk_idempresa', Auth::user()->empresa)
                                                ->where('fk_idpersona', $persona->id)
                                                ->latest()
                                                ->first();
        }




        $personasVigentes = Persona::where('fk_empresa', Auth::user()->empresa);
        
         if($request->persona){
            $personasVigentes->where('id', $request->persona);
        }
        
        $personasVigentes->get();

        foreach($personasVigentes as $kV => $pV){

            if(!isset($totalidadesPersonas[$pV->id])){

                $nominas = Nomina::with('nominaperiodos')
                ->where('ne_nomina.year', $year)
                //    ->where('ne_nomina.periodo', $periodo)
                ->where('fk_idempresa', Auth::user()->empresa)
                ->where('fk_idpersona', $pV->id);
    
                $nominas = $nominas->get();
    
                $totalidad = collect([]);

                foreach ($nominas as $key => $nomina) {
                    foreach ($nomina->nominaperiodos as $nominaPeriodo) {
                        $totalidad->push($nominaPeriodo->resumenTotal());
                    }
                }


                if($totalidad->count() > 0){

                    $base = ['diasTrabajados' => 0, 'salarioSubsidio' => 0];
                    $base['diasTrabajados'] = $totalidad->sum('diasTrabajados.diasPeriodo');
        
                    $base['diasTrabajadosFijos'] = $base['diasTrabajados'];
                    $base['salarioSubsidio'] = ($base['salarioBase'] = (($base['totalSalarioPeriodo'] = $totalidad->sum('salarioSubsidio.salario') * 30) / $base['diasTrabajados'])) + ($base['subsidioTransporte'] = ($totalidad->sum('salarioSubsidio.subsidioTransporte') / ($totalidad->count())));
        
                    $base['cesantiaBase'] = ($base['salarioSubsidio'] * $base['diasTrabajados']) / 360;
                    $base['interesesCesantia'] = $base['cesantiaBase'] * 0.12 * $base['diasTrabajados'] / 360;
        
                    $pV->totalidades = $base;
                    $pV->nominaSeleccionada = $nominas->where('periodo', $periodo)->first();

                }


            }else{
                unset($personasVigentes[$kV]);
            }

        }

        $personas = $personas->concat($personasVigentes);


        view()->share([
            'seccion' => 'nomina',
            'title' => 'Intereses a las Cesantías ' . $year,
            'icon' => 'fas fa-dollar-sign'
        ]);
        return view('nomina.prestacion-social.intereses-cesantias', compact('year', 'periodo', 'rango', 'personas', 'request'));
    }


    public function refrescar($idPrestacionSocial = null)
    {
        $prestacionSocial = NominaPrestacionSocial::select('ne_nomina_prestaciones_sociales.*')->join('ne_nomina',
            'ne_nomina.id', '=',
            'ne_nomina_prestaciones_sociales.fk_idnomina')->where('ne_nomina_prestaciones_sociales.id',
            $idPrestacionSocial)->where('fk_idempresa', Auth::user()->empresa)->first();

        if ($prestacionSocial) {
            $prestacionSocial->delete();
        }

        return back();
    }

    public function imprimir($idPrestacion)
    {
        $prestacionSocial = NominaPrestacionSocial::find($idPrestacion);

        if($prestacionSocial->nomina->fk_idempresa != Auth::user()->empresa){
            return false;
        }

        switch ($prestacionSocial->nombre) {

            case 'prima':
                return $this->imprimir_prima($prestacionSocial->id);
                break;
            case 'cesantia':
                return $this->imprimir_cesantia($prestacionSocial->id);
                break;

            case 'intereses_cesantia':
                return $this->imprirmir_intereses_de_cesantias($prestacionSocial->id);
                break;
        }

    }

    public function imprimir_prima($idPrestacionSocial)
    {
        $this->getAllPermissions(Auth::user()->id);
        $prestacionSocial = NominaPrestacionSocial::find($idPrestacionSocial);

        if($prestacionSocial->nomina->fk_idempresa != Auth::user()->empresa){
            return false;
        }

        $nomina = $prestacionSocial->nomina;
        $persona = Persona::find($nomina->fk_idpersona);
        $title = 'RESUMEN DE PAGO ' . $persona->nombre();
        $user = Auth::user();
        $year = $nomina->year;

        if ($nomina->periodo <= 6) {
            $periodoPrima = 1;
        } else {
            $periodoPrima = 2;
        }

        $pdf = PDF::loadView('pdf.nomina.prima',
            compact('prestacionSocial', 'persona', 'title', 'nomina', 'user', 'year', 'periodoPrima'));
        return response($pdf->stream())->withHeaders([
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function imprimir_cesantia($idPrestacionSocial)
    {
        $this->getAllPermissions(Auth::user()->id);
        $prestacionSocial = NominaPrestacionSocial::find($idPrestacionSocial);

        if($prestacionSocial->nomina->fk_idempresa != Auth::user()->empresa){
            return false;
        }

        $nomina = $prestacionSocial->nomina;
        $persona = Persona::find($nomina->fk_idpersona);
        $title = 'RESUMEN DE PAGO ' . $persona->nombre();
        $user = Auth::user();
        $year = $nomina->year;
        $periodo = $nomina->periodo;

        $pdf = PDF::loadView('pdf.nomina.cesantia',
            compact('prestacionSocial', 'persona', 'title', 'nomina', 'user', 'year'));
        return response($pdf->stream())->withHeaders([
            'Content-Type' => 'application/pdf',
        ]);
    }


    public function imprirmir_intereses_de_cesantias($idPrestacionSocial)
    {
        $this->getAllPermissions(Auth::user()->id);
        $prestacionSocial = NominaPrestacionSocial::find($idPrestacionSocial);

        if($prestacionSocial->nomina->fk_idempresa != Auth::user()->empresa){
            return false;
        }

        $nomina = $prestacionSocial->nomina;
        $persona = Persona::find($nomina->fk_idpersona);
        $title = 'RESUMEN DE PAGO ' . $persona->nombre();
        $user = Auth::user();
        $year = $nomina->year;
        $periodo = $nomina->periodo;
        $pdf = PDF::loadView('pdf.nomina.intereses-cesantia',
            compact('prestacionSocial', 'persona', 'title', 'nomina', 'user', 'year'));
        return response($pdf->stream())->withHeaders([
            'Content-Type' => 'application/pdf',
        ]);
    }

}
