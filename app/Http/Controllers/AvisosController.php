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
use App\Model\Ingresos\Factura;
use Mail;
use App\Mail\NotificacionMailable;
use Config;
use App\ServidorCorreo;
use App\Integracion;
use App\Contacto;
use App\Mikrotik;
use App\GrupoCorte;
use Illuminate\Support\Facades\View;

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
        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        return view('avisos.index', compact('clientes'));
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
        $contratos = Contrato::select('contracts.*', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.apellido1 as c_apellido1', 'contactos.apellido2 as c_apellido2', 'contactos.nit as c_nit', 'contactos.telefono1 as c_telefono', 'contactos.email as c_email', 'contactos.barrio as c_barrio')
			->join('contactos', 'contracts.client_id', '=', 'contactos.id')
			/* ->where('contracts.status', 1) */
            ->where('contracts.empresa', Auth::user()->empresa)
            ->whereNotNull('contactos.celular');

        if($id){
            $contratos = $contratos->where('contactos.id', $id);
        }

        if(request()->vencimiento){
            $contratos->join('factura', 'factura.contrato_id', '=', 'contracts.id')
                      ->where('factura.vencimiento', date('Y-m-d', strtotime(request()->vencimiento)))
                      ->groupBy('contracts.id');
        }


        $contratos = $contratos->get();

        $servidores = Mikrotik::where('empresa', auth()->user()->empresa)->get();
        $gruposCorte = GrupoCorte::where('empresa', Auth::user()->empresa)->get();

        return view('avisos.envio')->with(compact('plantillas','contratos','opcion','id', 'servidores', 'gruposCorte'));
    }

    public function email($id = false)
    {
        $this->getAllPermissions(Auth::user()->id);
        $opcion = 'EMAIL';

        view()->share(['title' => 'Envío de Notificaciones por '.$opcion, 'icon' => 'fas fa-paper-plane']);
        $plantillas = Plantilla::where('status', 1)->where('tipo', 1)->get();
        $contratos = Contrato::select('contracts.*', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.apellido1 as c_apellido1', 'contactos.apellido2 as c_apellido2', 'contactos.nit as c_nit', 'contactos.telefono1 as c_telefono', 'contactos.email as c_email', 'contactos.barrio as c_barrio')
            ->join('contactos', 'contracts.client_id', '=', 'contactos.id')
           /* ->where('contracts.status', 1) */
            ->where('contracts.empresa', Auth::user()->empresa);

        if($id){
            $contratos = $contratos->where('contactos.id', $id);
        }

        if(request()->vencimiento){
            $contratos->join('factura', 'factura.contrato_id', '=', 'contracts.id')
                      ->where('factura.vencimiento', date('Y-m-d', strtotime(request()->vencimiento)))
                      ->groupBy('contracts.id');
        }

        $contratos = $contratos->get();
        return view('avisos.envio')->with(compact('plantillas','contratos','opcion','id'));
    }

    public function envio_aviso(Request $request){

        Ini_set ('max_execution_time', 500);
        $empresa = Empresa::find(1);
        $type = ''; $mensaje = '';
        $fail = 0;
        $succ = 0;
        $cor = 0;
        $numeros = [];
        $bulk = '';

        for ($i = 0; $i < count($request->contrato); $i++) {
            $contrato = Contrato::find($request->contrato[$i]);

            if($request->isAbierta){
                $factura =  Factura::where('contrato_id')->latest()
                                             ->first();

                if($factura->estatus == 3 || $factura->estatus == 4 || $factura->estatus == 0 || $factura->estatus == 2){
                    continue;
                }
            }

            if ($contrato) {
                $plantilla = Plantilla::find($request->plantilla);

                if($request->type == 'SMS'){
                    $numero = str_replace('+','',$contrato->cliente()->celular);
                    $numero = str_replace(' ','',$numero);
                    array_push($numeros, '57'.$numero);
                    if(strlen($numero) >= 10  && $plantilla->contenido){
                        $bulk .= '{"numero": "57'.$numero.'", "sms": "'.$plantilla->contenido.'"},';
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
                                'from' => [
                                    'address' => $host->address,
                                    'name' => $host->name
                                ],
                            ]
                        );
                        config(['mail'=>$new]);
                    }

                    $datos = array(
                        'titulo'  => $plantilla->title,
                        'archivo' => $plantilla->archivo,
                        'cliente' => $contrato->cliente()->nombre.' '.$contrato->cliente()->apellidos(),
                        'empresa' => Auth::user()->empresa()->nombre,
                        'nit' => Auth::user()->empresa()->nit.'-'.Auth::user()->empresa()->dv,
                        'date' => date('d-m-Y'),
                    );
                    $correo = new NotificacionMailable($datos);

                    if($mailC = $contrato->cliente()->email){
                        $tituloCorreo = $plantilla->title;
                        if(str_contains($mailC, '@')){
                            // Mail::send($email, $data, function($message) use ($to_name, $to_email) {
                            //     $message->to($to_email, $to_name)
                            //             ->subject('Prueba de correo electrónico');
                            //     $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                            // });
                            // try {
                                // $cor++;
                      $template = 'emails.'.$plantilla->archivo;
                      $content = View::make($template, $datos)->render();
                                 self::sendInBlue($content, $correo->subject, [$mailC], $correo->name, []);

                                //  self::sendMail($mailC, $tituloCorreo, $correo, function($message) use ($mailC, $tituloCorreo, $correo) {
                                //      $message->to($mailC)
                                //              ->subject($tituloCorreo)
                                //              ->setBody($correo);
                                //  });
                                // self::sendMail(function($message) use ($mailC, $tituloCorreo){
                                //     $message->to($mailC)
                                //             ->subject($tituloCorreo)
                                //             ->setBody($correo);
                                // });
                                // Mail::to($mailC)->send($correo);

                            // } catch (\Throwable $th) {

                            // }
                        }
                    }
                }
            }
        }

        if($request->type == 'EMAIL'){
            return redirect('empresa/avisos')->with('success', 'Proceso de envío realizado con '.$cor.' notificaciones de email');
        }

        if($request->type == 'SMS'){
            $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('status', 1)->first();
            if($servicio){
                if($servicio->nombre == 'Hablame SMS'){
                    if($servicio->api_key && $servicio->user && $servicio->pass){
                        $curl = curl_init();

                        if(count($request->contrato)>1){
                            curl_setopt_array($curl, [
                                CURLOPT_URL => "https://api103.hablame.co/api/sms/v3/send/marketing/bulk",
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 30,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS => "{\n  \"bulk\": [\n    ".substr($bulk, 0, -1)."\n  ]\n}",
                                CURLOPT_HTTPHEADER => [
                                    'Content-Type: application/json',
                                    'account: '.$servicio->user,
                                    'apiKey: '.$servicio->api_key,
                                    'token: '.$servicio->pass,
                                    ],
                            ]);
                        }else{
                            $post['toNumber'] = $numero;
                            $post['sms'] = $plantilla->contenido;
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
                        }

                        $response = curl_exec($curl);
                        $err = curl_error($curl);
                        curl_close($curl);

                        $response = json_decode($response, true);
                        if(isset($response['error'])){
                            if($response['error']['code'] == 1000303){
                                $msj = 'Cuenta no encontrada';
                            }else if($response['error']['code'] == '1x023'){
                                $msj = 'Debe tener más de 2 contactos seleccionado para hacer uso de los envíos masivos';
                            }else{
                                $msj = $response['error']['details'];
                            }

                            if(is_array($msj)){
                                return back()->with('danger', 'Envío Fallido: '. implode(",", $msj));
                            }else{
                                return back()->with('danger', 'Envío Fallido: '.$msj);
                            }

                        }else{
                            if($response['status'] == '1x000'){
                                $msj = 'SMS recíbido por hablame exitosamente';
                            }else if($response['status'] == '1x152'){
                                $msj = 'SMS entregado al operador';
                            }else if($response['status'] == '1x153'){
                                $msj = 'SMS entregado al celular';
                            }
                            return redirect('empresa/avisos')->with('success', 'Envío Éxitoso: '.$msj);
                        }
                    }else{
                        $mensaje = 'EL MENSAJE NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                        return redirect('empresa/avisos')->with('danger', $mensaje);
                    }
                }elseif($servicio->nombre == 'SmsEasySms'){
                    if($servicio->user && $servicio->pass){
                        $post['to'] = $numeros;
                        $post['text'] = $plantilla->contenido;
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
                            return redirect('empresa/avisos')->with('danger', 'Respuesta API SmsEasySms: '.$err);
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
            }
        }else{
                return redirect('empresa/avisos')->with('danger', 'DISCULPE, NO POSEE NINGUN SERVICIO DE SMS HABILITADO. POR FAVOR HABILÍTELO PARA DISFRUTAR DEL SERVICIO');
        }
    }

    public function envio_personalizado(Request $request){
        $numero = str_replace('+','',$request->numero_sms);
        $numero = str_replace(' ','',$numero);
        $mensaje = $request->text_sms;

        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('status', 1)->first();
        if($servicio){
            if($servicio->nombre == 'Hablame SMS'){
                if($servicio->api_key && $servicio->user && $servicio->pass){
                    $post['toNumber'] = $numero;
                    $post['sms'] = $mensaje;

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/marketing',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',CURLOPT_POSTFIELDS => json_encode($post),
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
                            $msj = 'Cuenta no encontrada';
                        }else{
                            $msj = $response['error']['details'];
                        }
                        return response()->json(['success' => false, 'title' => 'Envío Fallido', 'message' => $msj , 'type' => 'error']);
                    }else{
                        if($response['status'] == '1x000'){
                            $msj = 'SMS recíbido por hablame exitosamente';
                        }else if($response['status'] == '1x152'){
                            $msj = 'SMS entregado al operador';
                        }else if($response['status'] == '1x153'){
                            $msj = 'SMS entregado al celular';
                        }
                        return response()->json(['success' => true, 'title' => 'Envío Realizado', 'message' => $msj , 'type' => 'success']);
                    }
                }else{
                    return response()->json(['success' => false, 'title' => 'Envío Fallido', 'message' => 'El mensaje no se pudo enviar porque falta información en la configuración del servicio', 'type' => 'error']);
                }
            }elseif($servicio->nombre == 'SmsEasySms'){
                if($servicio->user && $servicio->pass){
                    $post['to'] = array('57'.$numero);
                    $post['text'] = $mensaje;
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
                        return response()->json(['success' => false, 'title' => 'Envío Fallido', 'message' => '', 'type' => 'error']);
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
                            return response()->json(['success' => false, 'title' => 'Envío Fallido', 'message' => $msj , 'type' => 'error']);
                        }else{
                            return response()->json(['success' => true, 'title' => 'Envío Realizado', 'message' => '', 'type' => 'success']);
                        }
                    }
                }else{
                    return response()->json(['success' => false, 'title' => 'Envío Fallido', 'message' => 'El mensaje no se pudo enviar porque falta información en la configuración del servicio', 'type' => 'error']);
                }
            }else{
                if($servicio->user && $servicio->pass){
                    $post['to'] = array('57'.$numero);
                    $post['text'] = $mensaje;
                    $post['from'] = "SMS";
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
                        return back()->with('danger', 'Envío Fallido');
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
                            return response()->json(['success' => false, 'title' => 'Envío Fallido', 'message' => $msj , 'type' => 'error']);
                        }else{
                            return response()->json(['success' => true, 'title' => 'Envío Realizado', 'message' => '', 'type' => 'success']);
                        }
                    }
                }else{
                    return response()->json(['success' => false, 'title' => 'Envío Fallido', 'message' => 'El mensaje no se pudo enviar porque falta información en la configuración del servicio', 'type' => 'error']);
                }
            }
        }else{
            return response()->json(['success' => false, 'title' => 'Envío Fallido', 'message' => 'Disculpe, no posee ningun servicio de sms habilitado. Por favor habilítelo para disfrutar del servicio', 'type' => 'error']);
        }
    }

    public function automaticos(){
        $this->getAllPermissions(Auth::user()->id);

        $empresa = Empresa::find(auth()->user()->empresa);

        view()->share(['subseccion' => 'envio-automatico']);

        return view('avisos.automaticos', compact('empresa'));
    }

    public function storeAutomaticos(Request $request){

        $empresa = Empresa::find(auth()->user()->empresa);

        $empresa->sms_pago = strip_tags(trim($request->sms_pago));
        $empresa->sms_factura_generada = strip_tags(trim($request->sms_factura_generada));

        $empresa->update();

        return back()->with(['success' => 'mensajes guardados correctamente']);
    }

}
