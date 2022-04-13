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
use App\Nodo;
use App\Integracion;

class IntegracionSMSController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'configuracion', 'title' => 'Integración SMS', 'icon' =>'fas fa-cogs']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $servicios = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->get();
        return view('configuracion.integracion_sms.index')->with(compact('servicios'));
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('id', $id)->first();

        if ($servicio) {
            view()->share(['title' => $servicio->nombre, 'precice' => true]);
            return view('configuracion.integracion_sms.show')->with(compact('servicio'));
        }
        return redirect('empresa/configuracion/integracion-sms')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('id', $id)->first();

        if ($servicio) {
            view()->share(['title' => $servicio->nombre, 'middel' => true]);
            return view('configuracion.integracion_sms.edit')->with(compact('servicio'));
        }
        return redirect('empresa/configuracion/integracion-sms')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('id', $id)->first();

        if ($servicio) {
            $servicio->user       = $request->user;
            $servicio->pass       = $request->pass;
            $servicio->status     = $request->status;
            $servicio->api_key    = $request->api_key;
            $servicio->updated_by = Auth::user()->id;
            $servicio->save();
            
            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE EL SERVICIO';
            return redirect('empresa/configuracion/integracion-sms')->with('success', $mensaje)->with('id', $servicio->id);
        }
        return redirect('empresa/configuracion/integracion-sms')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function act_desc(Request $request, $id){
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('id', $id)->first();

        if($servicio){
            if($servicio->status == 0){
                $servicio->status = 1;
                $mensaje = 'SE HA HABILITADO EL SERVICIO CORRECTAMENTE';
            }else{
                $servicio->status = 0;
                $mensaje = 'SE HA DESHABILITADO EL SERVICIO CORRECTAMENTE';
            }
            $servicio->save();
            return redirect('empresa/configuracion/integracion-sms')->with('success', $mensaje)->with('id', $servicio->id);
        }else{
            return redirect('empresa/configuracion/integracion-sms')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }

    public function envio_prueba($id){
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('id', $id)->first();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/marketing',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'
            {
                "toNumber":"xxxxxxxxxx",
                "sms":"SMS Prueba API3",
                "flash":"0",
                "sc":"890202",
                "request_dlvr_rcpt":"0",
                "sendDate": "1599158819"
            }',
            CURLOPT_HTTPHEADER => array(
                'account: ',
                'apiKey: ',
                'token: ',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);dd($response);
        return redirect('empresa/configuracion/integracion-sms')->with('danger', $response)->with('id', $servicio->id);

        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('id', $id)->first();

        if($servicio->nombre == 'Hablame SMS'){
            if($servicio->api_key && $servicio->user && $servicio->pass){

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/marketing',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'
{
    "toNumber":"xxxxxxxxxx",
    "sms":"SMS Prueba API3",
    "flash":"0",
    "sc":"890202",
    "request_dlvr_rcpt":"0",
    "sendDate": "1599158819"
}',
  CURLOPT_HTTPHEADER => array(
    'account: ',
    'apiKey: ',
    'token: ',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);


                return redirect('empresa/configuracion/integracion-sms')->with('danger', $response)->with('id', $servicio->id);
            }else{
                $mensaje = 'EL MENSAJE DE PRUEBA NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                return redirect('empresa/configuracion/integracion-sms')->with('danger', $mensaje)->with('id', $servicio->id);
            }
        }else{

        }
    }
}
