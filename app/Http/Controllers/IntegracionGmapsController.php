<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;  
use Mail; 
use Validator;
use Illuminate\Validation\Rule;  
use Auth; 
use DB;
use Session;

use App\User;
use App\Integracion;

class IntegracionGmapsController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'configuracion', 'title' => 'Integración Google Maps', 'icon' => 'fas fa-maps']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'GMAPS')->first();
        return view('configuracion.integracion_gmaps.index')->with(compact('servicio'));
    }

    public function update(Request $request, $id){
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'GMAPS')->where('id', $request->id)->first();

        if ($servicio) {
            $request->validate([
                'latitude'  => 'required',
                'longitude' => 'required'
            ]);

            $servicio->latitude  = $request->latitude;
            $servicio->longitude = $request->longitude;
            $servicio->save();

            $mensaje='SE HA ACTUALIZADO SATISFACTORIAMENTE LA CONFIGURACIÓN DE GOOGLE MAPS';
            return redirect('empresa/configuracion/integracion-gmaps')->with('success', $mensaje);
        }
    }
}
