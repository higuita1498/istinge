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

class IntegracionSMSController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'configuracion', 'title' => 'Integración SMS', 'icon' => 'far fa-envelope']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $servicios = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('lectura', 1)->get();
        return view('configuracion.integracion_sms.index')->with(compact('servicios'));
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('lectura', 1)->where('id', $id)->first();

        if ($servicio) {
            view()->share(['title' => $servicio->nombre, 'precice' => true]);
            return view('configuracion.integracion_sms.show')->with(compact('servicio'));
        }
        return redirect('empresa/configuracion/integracion-sms')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('lectura', 1)->where('id', $id)->first();

        if ($servicio) {
            view()->share(['title' => $servicio->nombre, 'middel' => true]);
            return view('configuracion.integracion_sms.edit')->with(compact('servicio'));
        }
        return redirect('empresa/configuracion/integracion-sms')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('lectura', 1)->where('id', $id)->first();

        if ($servicio) {
            $servicio->user       = $request->user;
            $servicio->pass       = $request->pass;
            $servicio->status     = $request->status;
            $servicio->api_key    = $request->api_key;
            $servicio->numero     = $request->numero;
            $servicio->updated_by = Auth::user()->id;
            $servicio->save();

            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE EL SERVICIO';
            return redirect('empresa/configuracion/integracion-sms')->with('success', $mensaje)->with('id', $servicio->id);
        }
        return redirect('empresa/configuracion/integracion-sms')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function act_desc(Request $request, $id){

        $servicios = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('lectura', 1)->where('status', 1)->where('id', '<>', $id)->count();
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('lectura', 1)->where('id', $id)->first();

        if ($servicios >= 1) {
            return redirect('empresa/configuracion/integracion-sms')->with('danger', 'Ya existe un servicio de SMS habilitado, deshabilítelo para poder habilitar el servicio de '.$servicio->nombre)->with('id', $servicio->id);
        }

        if($servicio){
            if($servicio->status == 0){
                if($servicio->nombre == 'Hablame SMS'){
                    if($servicio->api_key && $servicio->user && $servicio->pass){
                        $servicio->status = 1;
                        $mensaje = 'SE HA HABILITADO EL SERVICIO CORRECTAMENTE';
                    }else{
                        $mensaje = 'EL SERVICIO DE '.$servicio->nombre.' NO SE PUEDE HABILITAR POR FALTA DE INFORMACIÓN DE AUTENTICACIÓN';
                        return redirect('empresa/configuracion/integracion-sms')->with('danger', $mensaje)->with('id', $servicio->id);
                    }
                }elseif($servicio->nombre == 'Colombia RED' || $servicio->nombre == 'SmsEasySms'){
                    if($servicio->user && $servicio->pass){
                        $servicio->status = 1;
                        $mensaje = 'SE HA HABILITADO EL SERVICIO CORRECTAMENTE';
                    }else{
                        $mensaje = 'EL SERVICIO DE '.$servicio->nombre.' NO SE PUEDE HABILITAR POR FALTA DE INFORMACIÓN DE AUTENTICACIÓN';
                        return redirect('empresa/configuracion/integracion-sms')->with('danger', $mensaje)->with('id', $servicio->id);
                    }
                }elseif($servicio->nombre == '360nrs'){
                        $servicio->status = 1;
                        $mensaje = 'SE HA HABILITADO EL SERVICIO CORRECTAMENTE';
                    }else{
                        $mensaje = 'EL SERVICIO DE '.$servicio->nombre.' NO SE PUEDE HABILITAR POR FALTA DE INFORMACIÓN DE AUTENTICACIÓN';
                        return redirect('empresa/configuracion/integracion-sms')->with('danger', $mensaje)->with('id', $servicio->id);
                    }

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
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('lectura', 1)->where('id', $id)->first();

        if($servicio->nombre == 'Hablame SMS'){
            if($servicio->api_key && $servicio->user && $servicio->pass && $servicio->numero){
                $post['toNumber'] = $servicio->numero;
                $post['sms'] = "SMS Prueba Hablame SMS | Integra Colombia - Software Administrativo de ISP";

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
                    CURLOPT_POSTFIELDS => json_encode($post),
                    CURLOPT_HTTPHEADER => array(
                        'account: '.$servicio->user,
                        'apiKey: '.$servicio->api_key,
                        'token: '.$servicio->pass,
                        'Content-Type: application/json'
                    ),
                ));

                $result = curl_exec ($curl);
                $err  = curl_error($curl);
                curl_close($curl);

                $response = json_decode($result, true);
                if(isset($response['error'])){
                    if($response['error']['code'] == 1000303){
                        $msj = 'Respuesta API Hablame SMS: Cuenta no encontrada';
                    }else{
                        $msj = 'Respuesta API Hablame SMS:'.$response['error']['details'];
                    }
                    $respuesta = 'danger';
                }else{
                    if($response['status'] == '1x000'){
                        $msj = 'Respuesta API Hablame SMS: SMS recíbido por hablame exitosamente';
                    }else if($response['status'] == '1x152'){
                        $msj = 'Respuesta API Hablame SMS: SMS entregado al operador';
                    }else if($response['status'] == '1x153'){
                        $msj = 'Respuesta API Hablame SMS: SMS entregado al celular';
                    }
                    $respuesta = 'success';
                }
                return redirect('empresa/configuracion/integracion-sms')->with($respuesta, $msj)->with('id', $servicio->id);
            }else{
                $mensaje = 'EL MENSAJE DE PRUEBA NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                return redirect('empresa/configuracion/integracion-sms')->with('danger', $mensaje)->with('id', $servicio->id);
            }
        }elseif($servicio->nombre == 'SmsEasySms'){
            if($servicio->user && $servicio->pass && $servicio->numero){
                $post['to'] = array('57'.$servicio->numero);
                $post['text'] = "SMS Prueba SmsEasySms | Integra Colombia - Software Administrativo de ISP";
                $post['from'] = "SMS";
                $login = $servicio->user;
                $password = $servicio->pass;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://sms.istsas.com/Api/rest/message");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                array(
                    "Accept: application/json",
                    "Authorization: Basic ".base64_encode($login.":".$password)));
                $result = curl_exec ($ch);
                $err  = curl_error($ch);
                curl_close($ch);

                if ($err) {
                    return back()->with('danger', 'Respuesta API SmsEasySms: '.$err);
                }else{
                    $response = json_decode($result, true);

                    if(isset($response['error'])){
                        if($response['error']['code'] == 102){
                            $msj = "No hay destinatarios válidos (Cumpla con el formato de nro +5700000000000)";
                        }else if($response['error']['code'] == 103){
                            $msj = "Nombre de usuario o contraseña desconocidos";
                        }else if($response['error']['code'] == 104){
                            $msj = "Falta el mensaje de texto";
                        }else if($response['error']['code'] == 105){
                            $msj = "Mensaje de texto demasiado largo";
                        }else if($response['error']['code'] == 106){
                            $msj = "Falta el remitente";
                        }else if($response['error']['code'] == 107){
                            $msj = "Remitente demasiado largo";
                        }else if($response['error']['code'] == 108){
                            $msj = "No hay fecha y hora válida para enviar";
                        }else if($response['error']['code'] == 109){
                            $msj = "URL de notificación incorrecta";
                        }else if($response['error']['code'] == 110){
                            $msj = "Se superó el número máximo de piezas permitido o número incorrecto de piezas";
                        }else if($response['error']['code'] == 111){
                            $msj = "Crédito/Saldo insuficiente";
                        }else if($response['error']['code'] == 112){
                            $msj = "Dirección IP no permitida";
                        }else if($response['error']['code'] == 113){
                            $msj = "Codificación no válida";
                        }else{
                            $msj = $response['error']['description'];
                        }
                        return back()->with('danger', 'Respuesta API SmsEasySms: '.$msj);
                    }else{
                        return back()->with('success', 'Respuesta API SmsEasySms: Mensaje enviado correctamente');
                    }
                }
            }else{
                $mensaje = 'EL MENSAJE DE PRUEBA NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                return redirect('empresa/configuracion/integracion-sms')->with('danger', $mensaje)->with('id', $servicio->id);
            }
        }else{
            if($servicio->user && $servicio->pass && $servicio->numero){
                $post['to'] = array('57'.$servicio->numero);
                $post['text'] = "SMS Prueba Colombia Red | Integra Colombia - Software Administrativo de ISP";
                $post['from'] = "";
                $login = $servicio->user;
                $password = $servicio->pass;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://masivos.colombiared.com.co/Api/rest/message");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                curl_setopt($ch, CURLOPT_HTTPHEADER,
                array(
                    "Accept: application/json",
                    "Authorization: Basic ".base64_encode($login.":".$password)));
                $result = curl_exec ($ch);
                $err  = curl_error($ch);
                curl_close($ch);

                if ($err) {
                    return back()->with('danger', 'Respuesta API Colombia Red: '.$err);
                }else{
                    $response = json_decode($result, true);

                    if(isset($response['error'])){
                        if($response['error']['code'] == 102){
                            $msj = "No hay destinatarios válidos (Cumpla con el formato de nro +5700000000000)";
                        }else if($response['error']['code'] == 103){
                            $msj = "Nombre de usuario o contraseña desconocidos";
                        }else if($response['error']['code'] == 104){
                            $msj = "Falta el mensaje de texto";
                        }else if($response['error']['code'] == 105){
                            $msj = "Mensaje de texto demasiado largo";
                        }else if($response['error']['code'] == 106){
                            $msj = "Falta el remitente";
                        }else if($response['error']['code'] == 107){
                            $msj = "Remitente demasiado largo";
                        }else if($response['error']['code'] == 108){
                            $msj = "No hay fecha y hora válida para enviar";
                        }else if($response['error']['code'] == 109){
                            $msj = "URL de notificación incorrecta";
                        }else if($response['error']['code'] == 110){
                            $msj = "Se superó el número máximo de piezas permitido o número incorrecto de piezas";
                        }else if($response['error']['code'] == 111){
                            $msj = "Crédito/Saldo insuficiente";
                        }else if($response['error']['code'] == 112){
                            $msj = "Dirección IP no permitida";
                        }else if($response['error']['code'] == 113){
                            $msj = "Codificación no válida";
                        }else{
                            $msj = $response['error']['description'];
                        }
                        return back()->with('danger', 'Respuesta API Colombia Red: '.$msj);
                    }else{
                        return back()->with('success', 'Respuesta API Colombia Red: Mensaje enviado correctamente');
                    }
                }
            }else{
                $mensaje = 'EL MENSAJE DE PRUEBA NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                return redirect('empresa/configuracion/integracion-sms')->with('danger', $mensaje)->with('id', $servicio->id);
            }
        }
    }
}
