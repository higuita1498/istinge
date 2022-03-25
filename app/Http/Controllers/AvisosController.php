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
        
        for ($i = 0; $i < count($request->contrato); $i++) {
            $contrato = Contrato::find($request->contrato[$i]);

            if ($contrato) {
                $plantilla = Plantilla::find($request->plantilla);
                
                if($request->type == 'SMS'){
                    if($empresa->device_id && $empresa->sms_gateway){
                        $curl = curl_init();
                    
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://smsgateway.me/api/v4/message/send',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS =>'[{
                                                    "phone_number": "'.$empresa->codigo.''.$contrato->cliente()->celular.'",
                                                    "message": "'.strip_tags($plantilla->contenido).'",
                                                    "device_id": '.$empresa->device_id.'
                                                }]',
                            CURLOPT_HTTPHEADER => array(
                                'Authorization: '.$empresa->sms_gateway,
                                'Content-Type: text/plain'
                            ),
                        ));
                        
                        $response = curl_exec($curl);
                        $err = curl_error($curl);
                        curl_close($curl);
                    }else{
                        return redirect('empresa/avisos')->with('danger', 'ERROR: No tiene los parámetros de SMS GATEWAY ME registrado en el sistema. Dirígase a Configuración > Empresa');
                    }
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
                    );
                    $correo = new NotificacionMailable($datos);
                    Mail::to($contrato->cliente()->email)->send($correo);
                    return redirect('empresa/avisos')->with('success', 'Notificaciones por EMAIL, enviadas con éxito');
                }
            }
        }
        $response = json_decode($response, true);
        
        if($response[0]['status'] == 'fail'){
            $type = 'danger';
            if($response[0]['message'] == 'Could not process request'){
                $mensaje = 'SMS GATEWAY ME API: No se pudo procesar la solicitud';
            }elseif($response[0]['message'] == 'failed validation'){
                $mensaje = 'SMS GATEWAY ME API: Validación fallida';
            }
        }elseif($response[0]['status'] == 'pending'){
            $type = 'success';
            $mensaje = 'SMS GATEWAY ME API: Respuesta exitosa. Una lista de mensajes que ahora están pendientes de enviarse.';
        }
        
        if($request->type == 'SMS'){
            if ($err) {
                return redirect('empresa/avisos')->with('danger', $err);
            } else {
                return redirect('empresa/avisos')->with($type, $mensaje);
            }
        }
    }
}
