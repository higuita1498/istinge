<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use stdClass;
use Auth; 
use DB;
use App\Empresa;
use Carbon\Carbon; 
use App\Aviso;
use App\Plantilla;
use App\Contrato;
use Mail;
use App\Mail\NotificacionMailable;
use Config;
use App\ServidorCorreo;
use App\Integracion;

class AvisosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['inicio' => 'master', 'seccion' => 'avisos', 'subseccion' => 'envios', 'title' => 'Envío de Notificaciones', 'icon' =>'fas fa-paper-plane']);
    }

    
    public function index()
    {
        $this->getAllPermissions(Auth::user()->id);
        return view('avisos.index');
    }
    
    public function create()
    {
        //respuest
    }
    
    public function store(Request $request)
    {
        //
    }
    
    public function show($id)
    {
        //
    }
    
    public function edit($id)
    {
        //
    }
    
    public function update(Request $request, $id)
    {
        //
    }
    
    public function destroy($id)
    {
        //
    }
    
    public function sms($id = false)
    {
        $this->getAllPermissions(Auth::user()->id);
        $opcion = 'SMS';
        
        view()->share(['title' => 'Envío de Notificaciones por '.$opcion, 'icon' => 'fas fa-paper-plane']);
        $plantillas = Plantilla::where('status', 1)->where('tipo', 0)->get();
        $contratos = Contrato::select('contracts.*', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.nit as c_nit', 'contactos.telefono1 as c_telefono', 'contactos.email as c_email', 'contactos.barrio as c_barrio')
			->join('contactos', 'contracts.client_id', '=', 'contactos.id')
			->where('contracts.status', 1)
            ->where('contracts.empresa', Auth::user()->empresa);

        if($id){
            $contratos = $contratos->where('contactos.id', $id);
        }
        $contratos = $contratos->get();
			
        return view('avisos.envio')->with(compact('plantillas','contratos','opcion','id'));
    }
    
    public function email($id = false)
    {
        $this->getAllPermissions(Auth::user()->id);
        $opcion = 'EMAIL';
        
        view()->share(['title' => 'Envío de Notificaciones por '.$opcion, 'icon' => 'fas fa-paper-plane']);
        $plantillas = Plantilla::where('status', 1)->where('tipo', 1)->get();
        $contratos = Contrato::select('contracts.*', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.nit as c_nit', 'contactos.telefono1 as c_telefono', 'contactos.email as c_email', 'contactos.barrio as c_barrio')
            ->join('contactos', 'contracts.client_id', '=', 'contactos.id')
            ->where('contracts.status', 1)
            ->where('contracts.empresa', Auth::user()->empresa);

        if($id){
            $contratos = $contratos->where('contactos.id', $id);
        }
        $contratos = $contratos->get();

        return view('avisos.envio')->with(compact('plantillas','contratos','opcion','id'));
    }
    
    public function envio_aviso(Request $request)
    {
        $empresa = Empresa::find(1);
        $type = ''; $mensaje = '';
        $fail = 0;
        $succ = 0;
        $cor = 0;
        $numeros = [];

        for ($i = 0; $i < count($request->contrato); $i++) {
            $contrato = Contrato::find($request->contrato[$i]);

            if ($contrato) {
                $plantilla = Plantilla::find($request->plantilla);
                
                if($request->type == 'SMS'){
                    $numero = str_replace('+','',$contrato->cliente()->celular);
                    $numero = str_replace(' ','',$numero);
                    array_push($numeros, '57'.$numero);
                }elseif($request->type == 'EMAIL'){
                    $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
                    if($host){
                        $existing = config('mail');
                        $new =array_merge(
                            $existing, [
                                'host' => $host->servidor,
                                'port' => $host->puerto,
                                'encryption' => $host->seguridad,
                                'username' => $host->usuario,
                                'password' => $host->password,
                            ]
                        );
                        config(['mail'=>$new]);
                    }

                    $datos = array(
                        'titulo'  => $plantilla->title,
                        'archivo' => $plantilla->archivo,
                        'cliente' => $contrato->cliente()->nombre,
                        'empresa' => Auth::user()->empresa()->nombre,
                        'nit' => Auth::user()->empresa()->nit.'-'.Auth::user()->empresa()->dv,
                        'date' => date('d-m-Y'),
                    );
                    $correo = new NotificacionMailable($datos);
                    Mail::to($contrato->cliente()->email)->send($correo);
                    $cor++;
                }
            }
        }

        if($request->type == 'SMS'){
            $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('status', 1)->first();
            if($servicio){
                if($servicio->nombre == 'Hablame SMS'){
                    if($servicio->api_key && $servicio->user && $servicio->pass){
                        $post['toNumber'] = $numeros;
                        $post['sms'] = $plantilla->contenido;

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
                            $fail++;
                        }else{
                            $succ++;
                        }
                        return redirect('empresa/configuracion/integracion-sms')->with($respuesta, $msj)->with('id', $servicio->id);
                    }else{
                        $mensaje = 'EL MENSAJE NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                        return redirect('empresa/avisos')->with('danger', $mensaje);
                    }
                }else{
                    if($servicio->user && $servicio->pass){
                        $post['to'] = $numeros;
                        $post['text'] = $plantilla->contenido;
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
                            return redirect('empresa/avisos')->with('danger', 'Respuesta API Colombia Red: '.$err);
                        }else{
                            $response = json_decode($result, true);

                            if(isset($response['error'])){
                                $fail++;
                            }else{
                                $succ++;
                            }
                        }
                    }else{
                        $mensaje = 'EL MENSAJE NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                        return redirect('empresa/avisos')->with('danger', $mensaje);
                    }
                }
                return redirect('empresa/avisos')->with('success', 'Proceso de envío realizado. SMS Enviados: '.$fail.' - SMS Fallidos: '.$succ);
            }else{
                return redirect('empresa/avisos')->with('danger', 'DISCULPE, NO POSEE NINGUN SERVICIO DE SMS HABILITADO. POR FAVOR HABILÍTELO PARA DISFRUTAR DEL SERVICIO');
            }
        }elseif($request->type == 'EMAIL'){
            return redirect('empresa/avisos')->with('success', 'Proceso de envío realizado con '.$cor.' notificaciones de email');
        }
    }
}
