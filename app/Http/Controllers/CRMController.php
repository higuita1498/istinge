<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Mail; 
use Validator;
use Illuminate\Validation\Rule;  
use Auth; 
use DB;
use Session;
use App\Etiqueta;

use Barryvdh\DomPDF\Facade as PDF;

include_once(app_path() .'/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel; 
use PHPExcel_IOFactory; 
use PHPExcel_Style_Alignment; 
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Shared_ZipArchive;

use App\User;
use App\Contrato;
use App\Contacto;
use App\CRM;
use App\Model\Ingresos\Factura;
use App\Servidor;
use App\GrupoCorte;
use App\Mikrotik;
use App\Integracion;
use App\CRMLOG;
use App\PromesaPago;
include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;


class CRMController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'crm', 'title' => 'CRM', 'icon' => 'fas fa-receipt']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['subseccion' => 'crm_cartera', 'title' => 'CRM: Cartera', 'invert' => true]);
        
        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? CRM::join('contactos', 'crm.cliente', '=', 'contactos.id')->where('contactos.oficina', Auth::user()->oficina)->where('crm.empresa', Auth::user()->empresa)->groupBy('crm.cliente')->get() : CRM::join('contactos', 'crm.cliente', '=', 'contactos.id')->where('crm.empresa', Auth::user()->empresa)->groupBy('crm.cliente')->get();
        $usuarios = User::where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servidores   = Mikrotik::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $grupos_corte = GrupoCorte::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $etiquetas = Etiqueta::where('empresa_id', Auth::user()->empresa)->get();
        $ini = Carbon::create(date('Y'), date('m'), date('d'))->startOfMonth()->format('d-m-Y');
        return view('crm.index')->with(compact('clientes', 'usuarios', 'servidores', 'grupos_corte', 'etiquetas', 'ini'));
    }
    
    public function informe(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['subseccion' => 'crm_informe', 'title' => 'CRM: Informe', 'invert' => true]);
        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? CRM::join('contactos', 'crm.cliente', '=', 'contactos.id')->where('contactos.oficina', Auth::user()->oficina)->where('crm.empresa', Auth::user()->empresa)->groupBy('crm.cliente')->get() : CRM::join('contactos', 'crm.cliente', '=', 'contactos.id')->where('crm.empresa', Auth::user()->empresa)->groupBy('crm.cliente')->get();
        //$clientes = CRM::join('contactos', 'crm.cliente', '=', 'contactos.id')->where('crm.empresa', Auth::user()->empresa)->groupBy('crm.cliente')->get();
        $usuarios = User::where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servidores   = Mikrotik::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $grupos_corte = GrupoCorte::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        
        $ini = Carbon::create(date('Y'), date('m'), date('d'))->startOfMonth()->format('d-m-Y');
        $fin = Carbon::create(date('Y'), date('m'), date('d'))->endOfMonth()->format('d-m-Y');
        return view('crm.informe')->with(compact('clientes', 'usuarios', 'ini', 'fin', 'servidores', 'grupos_corte'));
    }
    
    public function cartera(Request $request, $tipo){
        $modoLectura = auth()->user()->modo_lectura();
        $etiquetas = Etiqueta::where('empresa_id', auth()->user()->empresa)->get();
        $contratos = CRM::query()
			->select('crm.*', 'factura.fecha as fecha_factura', 'contactos.nit as c_nit', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.apellido1 as c_apellido1', 'contactos.apellido2 as c_apellido2', 'contactos.celular as c_celular', 'factura.codigo', 'factura.estatus', 'items_factura.precio', DB::raw('(select count(factura.id) from factura where factura.cliente = crm.cliente and factura.estatus = 1) AS facAbiertas'))
            ->join('contactos', 'crm.cliente', '=', 'contactos.id')
            ->leftjoin('factura', 'crm.factura', '=', 'factura.id')
            ->leftjoin('items_factura', 'items_factura.factura', '=', 'factura.id')
            ->where('crm.empresa', Auth::user()->empresa)
            ->distinct();
            
        if ($request->filtro == true) {
            if($request->cliente){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('crm.cliente', $request->cliente);
                });
            }
            if($request->estado){
                if($request->estado == 'A'){
                    $estado = 0;
                }else{
                    $estado = $request->estado;
                }
                $contratos->where(function ($query) use ($estado) {
                    $query->orWhere('crm.estado', $estado);
                });
            }
            if($request->etiqueta_id){
                $contratos->where(function ($query) use ($request){
                    $query->where('crm.etiqueta_id', $request->etiqueta_id);
                });
            }
            if($request->created_by){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('crm.created_by', $request->created_by);
                });
            }
            if($request->desde){
                $desde = Carbon::parse($request->desde.'00:00:00')->format('Y-m-d H:i:s');
                $contratos->where(function ($query) use ($desde) {
                    $query->orWhere('crm.updated_at', '>=', $desde);
                });
            }
            if($request->fecha_factura){
                $fechaFactura = Carbon::parse($request->fecha_factura)->format('Y-m-d');
                $contratos->where(function ($query) use ($fechaFactura) {
                    $query->orWhere('factura.fecha', '=', $fechaFactura);
                });
            }
            if($request->updated_at){
                $updatedAt = Carbon::parse($request->updated_at)->format('Y-m-d');
                $contratos->where(function ($query) use ($updatedAt) {
                    $query->orWhere('crm.updated_at', 'like', '%'.$updatedAt.'%');
                });
            }
            if($request->hasta){
                $hasta = Carbon::parse($request->hasta.'23:59:59')->format('Y-m-d H:i:s');
                $contratos->where(function ($query) use ($hasta) {
                    $query->orWhere('crm.updated_at', '<=', $hasta);
                });
            }
            if($request->estatus){
                if($request->estatus == 'A'){
                    $estatus = 0;
                }else{
                    $estatus = $request->estatus;
                }
                $contratos->where(function ($query) use ($estatus) {
                    $query->orWhere('factura.estatus', $estatus);
                });
            }
            if($request->grupo_corte){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('crm.grupo_corte', $request->grupo_corte);
                });
            }
            if($request->servidor){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('crm.servidor', $request->servidor);
                });
            }
        }
        
        if($tipo == 1){
            $contratos->whereIn('crm.estado', [1, 2, 6]);
        }else if($tipo == 0){
            $contratos->whereIn('crm.estado', [0, 3]);
        }else if($tipo == 'I'){
            $contratos->whereIn('crm.estado', [0, 1, 2, 3, 4, 5, 6]);
        }else{
            $contratos->where('crm.estado', $tipo);
        }

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $contratos->where('contactos.oficina', auth()->user()->oficina);
            }
        }
        
        return datatables()->eloquent($contratos)
            ->editColumn('nombre', function (CRM $crm) {

                if($crm->facAbiertas > 0){
                    $color = 'color:red';
                }else{
                    $color = 'color:green';
                }

                return "<a href=" . route('contactos.show', $crm->cliente) . " target='_blank'>{$crm->c_nombre} {$crm->c_apellido1} {$crm->c_apellido2} <span style='{$color}'>({$crm->facAbiertas})</span></div></a>";
            })
            ->editColumn('nit', function (CRM $crm) {
                return "<center>".$crm->c_nit."</center>";
            })
            ->editColumn('celular', function (CRM $crm) {
                return "<center>".$crm->c_celular."</center>";
            })
            ->addColumn('etiqueta', function(CRM $crm) use($etiquetas){
                return view('etiquetas.select', compact('etiquetas','crm'));
            })
            ->editColumn('estado', function (CRM $crm) {
                return "<center><span class='text-{$crm->estado('true')}'><strong>{$crm->estado()}</strong></span></center>";
            })
            ->editColumn('fecha_factura', function (CRM $crm) {
                return "<center><span><strong>{$crm->fecha_factura}</strong></span></center>";
            })
            ->editColumn('updated_at', function (CRM $crm) {
                return "<center><span><strong>{$crm->updated_at->format('Y-m-d H:i:s')}</strong></span></center>";
            })
            ->editColumn('created_by', function (CRM $crm) {
                return "<center>".$crm->created_by()."</center>";
            })
            ->editColumn('estatus', function (CRM $crm) {
                return "<center><span class='text-{$crm->factura('true')}'><strong>{$crm->factura()}</strong></span></center>";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "crm.acciones-cartera")
            ->rawColumns(['acciones', 'nombre', 'nit', 'celular', 'estado', 'fecha_factura', 'updated_at', 'created_by', 'updated_at', 'estatus'])
            ->toJson();
    }

    private function getInfo(){
        $usuarios =  DB::table("usuarios")
                        ->get();   
        
        $users = [];
        $users[] = [
            "id"=>0,
            "username"=>"Sin Asignar",
            "nombres"=>"Sin Asignar"
        ];
        foreach($usuarios as $usuario){
            $users[] = [
                "id"=>$usuario->id,
                "username"=>$usuario->username,
                "nombres"=>$usuario->nombres
            ];
        }
        
        $chats = DB::table('chats_whatsapp')
                        ->orderBy("last_update","desc")
                        ->get();
        
        return [$chats,$users];
    }
    public function whatsapp(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $instancia = DB::table("instancia")
                            ->first();
       
        if(is_null($instancia) || empty($instancia)){
            return view("crm.whatsapp")->with(compact("instancia"));
        }
        if($instancia->status!=0){
            $info = $this->getInfo();
            return view("crm.whatsapp")->with(compact("instancia","info"));
        }
        return view("crm.whatsapp")->with(compact("instancia"));
        
    }
    public function whatsappActions(Request $request){
        $unique = uniqid();
        file_put_contents("uniqueid",$unique);

        switch ($request->input("action")) {
            case 'getChat':
                $instancia = DB::table("instancia")
                                        ->first();
                $secret = "sk_wh47s1v3"; //no borrar, id para seguridad
                $url = 'https://api.whatsive.com/aliance/';
                $data = array(
                    'secret' => $secret,
                    'action' => 'getChat',
                    'id' => $request->input("id"),
                    'limit' => $request->input("limit"),
                    'idVerification'=>$unique,
                    'addr'=>$instancia->addr,
                    'port'=>$instancia->port
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                $response = json_decode($response);
                if($response->salida != "success"){
                    return json_encode(["salida"=>"error","message"=>$response->message]);
                }
                if (curl_errno($ch)) {
                    return json_encode(["salida"=>"error","message"=>"No se pudo recuperar el archivo"]);
                }
                curl_close($ch);
                return json_encode(["salida"=>"success","messages"=>$response->chats]);
                break;
            case 'closeChat':
                DB::statement("UPDATE `chats_whatsapp` set `estado`= 'cerrado' where number='".explode("@",$request->input("id"))[0]."'");
                return "true";
                break;
            case 'changeTecnico':
                DB::statement("UPDATE `chats_whatsapp` set `asigned_to`= '".$request->input("tecnico")."' where number='".explode("@",$request->input("id"))[0]."'");
                return "true";
                break;
            case 'changeName':
                DB::statement("UPDATE `chats_whatsapp` set `name`= '".$request->input("nombre")."' where number='".explode("@",$request->input("id"))[0]."'");
                return "true";
                break;
            case 'getMedia':
                $instancia = DB::table("instancia")
                                        ->first();
                $secret = "sk_wh47s1v3"; //no borrar, id para seguridad
                $url = 'https://api.whatsive.com/aliance/';
                $data = array(
                    'secret' => $secret,
                    'action' => 'getMedia',
                    'id' => $request->input("id"),
                    'idVerification'=>$unique,
                    'addr'=>$instancia->addr,
                    'port'=>$instancia->port
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                $response = json_decode($response);
                if($response->salida != "success"){
                    return json_encode(["salida"=>"error","message"=>$response->message]);
                }
                if (curl_errno($ch)) {
                    return json_encode(["salida"=>"error","message"=>"No se pudo recuperar el archivo"]);
                }
                curl_close($ch);
                return json_encode(["salida"=>"success","src"=>"data:".$request->input("mimetype").";base64,".$response->src]);
                break;
            case 'sendMessage':
                $instancia = DB::table("instancia")
                                        ->first();
                if($request->input("cron") == "true"){ 
                    $usuario = DB::table("usuarios")
                                        ->where("id","=","1")
                                        ->first();
                }else{
                    $usuario = DB::table("usuarios")
                                        ->where("id","=",Auth::user()->id)
                                        ->first();
                }
                $secret = "sk_wh47s1v3"; //no borrar, id para seguridad
                $message;
                if($request->input("cron") == "true"){ 
                    $message = $request->input("message");
                }else{
                    $message = "*".trim($usuario->nombres)."*\n".$request->input("message");
                }
                $url = 'https://api.whatsive.com/aliance/';
                $data = array(
                    'secret' => $secret,
                    'action' => 'sendMessage',
                    'isFile' => 'false',
                    'message' => $message,
                    'recipient' => $request->input("id"),
                    'idVerification'=>$unique,
                    'addr'=>$instancia->addr,
                    'port'=>$instancia->port
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                $response = json_decode($response);
                if($response->salida != "success"){
                    return json_encode(["salida"=>"error","message"=>$response->message]);
                }
                if (curl_errno($ch)) {
                    return json_encode(["salida"=>"error","message"=>"No se pudo enviar el mensaje"]);
                }
                curl_close($ch);
                DB::statement("UPDATE `chats_whatsapp` SET `last_message`= '".$request->input("message")."', `last_update`='".date("Y-m-d H:i:s")."', `notRead`='0', `fromMe`='1' WHERE `number`= '".explode("@",$request->input("id"))[0]."' ");
                return json_encode(["salida"=>"success","message"=>"mensaje enviado correctamente","from"=>$request->input("id"),"body"=>$request->input("message"),"timestamp"=>strtotime(date("Y-m-d H:i:s"))]);
                break;
            case 'sendFile':
                $instancia = DB::table("instancia")
                                        ->first();
                $usuario;
                if($request->input("cron") == "true"){ 
                    $usuario = DB::table("usuarios")
                                        ->where("id","=","1")
                                        ->first();
                }else{
                    $usuario = DB::table("usuarios")
                                        ->where("id","=",Auth::user()->id)
                                        ->first();
                }
                
                $secret = "sk_wh47s1v3"; //no borrar, id para seguridad
                $message;
                if($request->input("cron") == "true"){ 
                    $message = $request->input("mensaje");
                }else{
                    $message = "*".trim($usuario->nombres)."*\n".$request->input("mensaje");
                }
                if($request->input("cron") == "true"){ 
                    $content = base64_encode(file_get_contents($request->input("file")));

                }else{

                    $content = base64_encode(file_get_contents(Storage::disk('local')->path($request->input("file"))));
                }
                $d = "false";
                if(strpos($request->input("mime"),"image")>=0 || strpos($request->input("mime"),"video")>=0){
                    $d = "true";
                }
                $url = 'https://api.whatsive.com/aliance/';
                $data = array(
                    'secret' => $secret,
                    'action' => 'sendMessage',
                    'isFile' => 'true',
                    'description'=>$d,
                    'mimetype'=>$request->input("mime"),
                    "namefile"=>(is_null($request->input("namefile"))? "documento":$request->input("namefile")),
                    'file'=>$content,
                    'message' => $message,
                    'recipient' => $request->input("id"),
                    'idVerification'=>$unique,
                    'addr'=>$instancia->addr,
                    'port'=>$instancia->port
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                $response = json_decode($response);
                
                if($response->salida != "success"){
                    return json_encode(["salida"=>"error","message"=>"Error al enviar el mensaje"]);
                }
                if (curl_errno($ch)) {
                    return json_encode(["salida"=>"error","message"=>"No se pudo enviar el mensaje"]);
                }
                curl_close($ch);

                $chat =  json_decode(json_encode($response->chat),true);
                $typechats = [
                    "video"=> "  <span class = 'fas fa-video fa-lg' ></span> Video",
                    "ptt"=> "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                    "audio"=> "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                    "image"=> "  <span class = 'fas fa-image fa-lg' ></span> Imagen",
                    "sticker"=> "  <span class = 'fas fa-file fa-lg' ></span> Sticker",
                    "document"=> "  <span class = 'fas fa-file-archive fa-lg' ></span> Archivo",
                    "location"=> "  <span class = 'fas fa-map fa-lg' ></span> Ubicacion",
                    "call_log"=> "  <span style = 'color:red' class = 'fa fa-phone fa-lg' ></span> Llamada perdida ",
                    "e2e_notification" =>"Respuesta automatica",
                    "ciphertext" => "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                    "revoked" => "<span class = 'fa fa-ban fa-lg' ></span> Elimino el mensaje",
                    "vcard" => "<span class = 'fa fa-user fa-lg' ></span> Contacto",
                    "notification_template" => "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                    "gp2" => "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                ];
                if(!isset($chat["timestamp"])){
                    $chat["timestamp"] = strtotime(date("Y-m-d H:i:s")." -30 days");
                }
                $hora = date("Y-m-d H:i:s",$chat["timestamp"]);

                if(isset($chat['lastMessage'])){
                    if($chat['lastMessage']["type"] != "chat"){
                        $chat['lastMessage']["body"] = $typechats[$chat['lastMessage']["type"]];
                    }
                    DB::statement("UPDATE `chats_whatsapp` SET `last_message`= '".str_replace("'","\"",$chat['lastMessage']["body"])."', `last_update`='".$hora."', `notRead`='0', `fromMe`='1' WHERE `number`= '".explode("@",$request->input("id"))[0]."' ");
                    return json_encode(["salida"=>"success","message"=>"mensaje enviado correctamente","from"=>$request->input("id"),"body"=>str_replace("'","\"",$chat['lastMessage']["body"]),"type"=>$chat['lastMessage']["type"],"timestamp"=>strtotime($hora)]);
                }else{
                    DB::statement("UPDATE `chats_whatsapp` SET `last_message`= '', `last_update`='".$hora."', `notRead`='0', `fromMe`='1' WHERE `number`= '".explode("@",$request->input("id"))[0]."' ");
                    return json_encode(["salida"=>"success","message"=>"mensaje enviado correctamente","from"=>$request->input("id"),"body"=>"","type"=>"chat","timestamp"=>strtotime($hora)]); 
                }


                
                
                
                break;
            case 'getChats':
                $instancia = DB::table("instancia")
                                        ->first();
                                      
                $secret = "sk_wh47s1v3"; //no borrar, id para seguridad
                $url = 'https://api.whatsive.com/aliance/';
                $data = array(
                    'secret' => $secret,
                    'action' => 'getChats',
                    'idVerification'=>$unique,
                    'addr'=>$instancia->addr,
                    'port'=>$instancia->port
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                $response = json_decode($response);
                if($response->salida != "success"){
                    return json_encode(["salida"=>"error","message"=>$response->message]);
                }
                if (curl_errno($ch)) {
                    return json_encode(["salida"=>"error","message"=>"No se pudieron recuperar los mensajes de la instancia"]);
                }
                curl_close($ch);
                DB::statement('DELETE FROM `chats_whatsapp`');
                foreach (json_decode($response->chats,true) as $chat) {
                    $typechats = [
                        "video"=> "  <span class = 'fas fa-video fa-lg' ></span> Video",
                        "ptt"=> "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                        "audio"=> "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                        "image"=> "  <span class = 'fas fa-image fa-lg' ></span> Imagen",
                        "sticker"=> "  <span class = 'fas fa-file fa-lg' ></span> Sticker",
                        "document"=> "  <span class = 'fas fa-file-archive fa-lg' ></span> Archivo",
                        "location"=> "  <span class = 'fas fa-map fa-lg' ></span> Ubicacion",
                        "call_log"=> "  <span style = 'color:red' class = 'fa fa-phone fa-lg' ></span> Llamada perdida ",
                        "e2e_notification" =>"Respuesta automatica",
                        "ciphertext" => "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                        "revoked" => "<span class = 'fa fa-ban fa-lg' ></span> Elimino el mensaje",
                        "vcard" => "<span class = 'fa fa-user fa-lg' ></span> Contacto",
                        "notification_template" => "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                        "gp2" => "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                    ];
                    if($chat['id']['user'] == "status"){
                        continue;
                    }
                    if($chat["isGroup"]){
                        continue;
                    }
                    if(!isset($chat["timestamp"])){
                        $chat["timestamp"] = strtotime(date("Y-m-d H:i:s")." -30 days");
                    }
                    try {
                        $hora = date("Y-m-d H:i:s",$chat["timestamp"]);
                        if(isset($chat['lastMessage'])){
                            if($chat['lastMessage']["type"] != "chat"){
                                $chat['lastMessage']["body"] = $typechats[$chat['lastMessage']["type"]];
                            }
                            DB::statement("INSERT INTO `chats_whatsapp` (`number`,`name`,`last_update`,`asigned_to`,`last_message`,`type`,`notRead`,`photo`) values('".$chat['id']['user']."','".(isset($chat["contact"]['name'])?$chat["contact"]['name']:$chat['id']['user'])."','".$hora."','0','".str_replace("'","\"",$chat['lastMessage']["body"])."', '".$chat['lastMessage']["type"]."','".$chat["unreadCount"]."','".(!isset($chat["picUrl"]) || is_null($chat["picUrl"])?"https://ramenparados.com/wp-content/uploads/2019/03/no-avatar-png-8.png":$chat["picUrl"])."')");
                        }else{
                            DB::statement("INSERT INTO `chats_whatsapp` (`number`,`name`,`last_update`,`asigned_to`,`last_message`, `notRead`,`photo`) values('".$chat['id']['user']."','".(isset($chat["contact"]['name'])?$chat["contact"]['name']:$chat['id']['user'])."','".$hora."','0','','".$chat["unreadCount"]."','".(!isset($chat["picUrl"]) || is_null($chat["picUrl"])?"https://ramenparados.com/wp-content/uploads/2019/03/no-avatar-png-8.png":$chat["picUrl"])."') ");
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    
                   

                }
                return json_encode(["salida"=>"success","message"=>"Instancia iniciada correctamente"]);
                break;
            case 'create':
                
                $secret = "sk_wh47s1v3"; //no borrar, id para seguridad
                $url = 'https://api.whatsive.com/aliance/';
                $data = array(
                    'secret' => $secret,
                    'action' => 'create',
                    'idVerification'=>$unique,
                    'addr'=>$request->input("addr")
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                $response = json_decode($response);
                if($response->salida != "success"){
                    return json_encode(["salida"=>"error","message"=>$response->message]);
                }
                if (curl_errno($ch)) {
                    return json_encode(["salida"=>"error","message"=>"la instancia no pudo ser creada"]);
                }
                curl_close($ch);
                
                DB::statement("INSERT INTO `instancia` (`port`, `unique`, `status`, `addr`) values('".$response->puerto."','".$response->unique."','0','".$request->input("addr")."')");
                return json_encode(["salida"=>"success","message"=>"Instancia creada correctamente"]);
                break;
            
            case "reloadInstancia":
                $instancia = DB::table("instancia")
                                        ->first();
                DB::statement("UPDATE instancia set `status`= '0'");
                $secret = "sk_wh47s1v3"; //no borrar, id para seguridad
                $url = 'https://api.whatsive.com/aliance/';
                $data = array(
                    'secret' => $secret,
                    'action' => 'reload',
                    'idVerification'=>$unique,
                    'port'=>$instancia->port,
                    'addr'=>$instancia->addr
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                $response = json_decode($response);
                
                if(isset($response->salida) && $response->salida != "success"){
                    return json_encode(["salida"=>"error","message"=>$response->message]);
                }
                if (curl_errno($ch)) {
                    return json_encode(["salida"=>"error","message"=>"la instancia no pudo ser creada"]);
                }
                return json_encode(["salida"=>"success","message"=>"Instancia reiniciada correctamente"]);
                curl_close($ch);
                break;
            default:
                # code...
                break;
        }
    }

    public function carteraContacto($contacto, Request $request){
        $modoLectura = auth()->user()->modo_lectura();
      

        $columns = array(
            0 => 'radicados.codigo',
            1 => 'radicados.fecha',
            2 => 'radicados.estado',
            3 => 'radicados.status'
        );

        $requestData =  $request;

        $contratos = CRM::query()
			->select('crm.*', 'contactos.nit as c_nit', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.apellido1 as c_apellido1', 'contactos.apellido2 as c_apellido2', 'contactos.celular as c_celular', 'factura.codigo', 'factura.estatus', 'items_factura.precio')
            ->join('contactos', 'crm.cliente', '=', 'contactos.id')
            ->leftjoin('factura', 'crm.factura', '=', 'factura.id')
            ->join('items_factura', 'items_factura.factura', '=', 'factura.id')
            ->where('crm.empresa', Auth::user()->empresa)
            ->where('contactos.id', $contacto);
        

            if (isset($requestData->search['value'])) {
                $contratos=$contratos->where(function ($query) use ($requestData) {
                    $query->where('contactos.nit', 'like', '%'.$requestData->search['value'].'%')
                    ->orwhere('factura.codigo', 'like', '%'.$requestData->search['value'].'%')
                    ->orwhere('crm.informacion', 'like', '%'.$requestData->search['value'].'%')
                    ->orwhere('factura.estatus', 'like', '%'.$requestData->search['value'].'%');
                });
            }
    
            $totalFiltered=$totalData=$contratos->count();
            
            $contratos=$contratos->skip($requestData['start'])->take($requestData['length']);
            $contratos=$contratos->orderBy('created_at', 'desc');
            $contratos=$contratos->distinct()->get();
            $data = array();
            foreach ($contratos as $c) {
                $link = (route('crm.show', $c->id));
                $nestedData = array();
                $nestedData[] = "<a target='_blank' href='{$link}'>{$c->id}</a>";
                $nestedData[] = date('d-m-Y', strtotime($c->created_at));
                $nestedData[] = "<center><span class='text-{$c->estado('true')}'><strong>{$c->estado()}</strong></span></center>";
                $nestedData[] = "<center><span class='text-{$c->factura('true')}'><strong>{$c->factura()}</strong></span></center>";
                $nestedData[] = "<div class='text-center-c'>".($c->informacion)."</div>";
                $data[] = $nestedData;
            }
           
            $json_data = array(
                "draw" => intval($requestData->draw),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data
            );

            return json_encode($json_data);
    }
    
    public function reporte(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $contratos = CRM::query()
			->select('crm.*', 'contactos.nit as c_nit', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.apellido1 as c_apellido1', 'contactos.apellido2 as c_apellido2', 'contactos.celular as c_celular', 'factura.codigo', 'factura.estatus', 'items_factura.precio')
            ->join('contactos', 'crm.cliente', '=', 'contactos.id')
            ->join('factura', 'crm.factura', '=', 'factura.id')
            ->join('items_factura', 'items_factura.factura', '=', 'factura.id')
            ->whereIn('crm.estado', [1, 2, 3, 4, 5, 6])
            ->where('crm.empresa', Auth::user()->empresa);
            
        if ($request->filtro == true) {
            if($request->cliente){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('crm.cliente', $request->cliente);
                });
            }
            if($request->estado){
                if($request->estado == 'A'){
                    $estado = 0;
                }else{
                    $estado = $request->estado;
                }
                $contratos->where(function ($query) use ($estado) {
                    $query->orWhere('crm.estado', $estado);
                });
            }
            if($request->created_by){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('crm.created_by', $request->created_by);
                });
            }
            if($request->desde){
                $desde = Carbon::parse($request->desde.'00:00:00')->format('Y-m-d H:i:s');
                $contratos->where(function ($query) use ($desde) {
                    $query->orWhere('crm.updated_at', '>=', $desde);
                });
            }
            if($request->hasta){
                $hasta = Carbon::parse($request->hasta.'23:59:59')->format('Y-m-d H:i:s');
                $contratos->where(function ($query) use ($hasta) {
                    $query->orWhere('crm.updated_at', '<=', $hasta);
                });
            }
            if($request->estatus){
                if($request->estatus == 'A'){
                    $estatus = 0;
                }else{
                    $estatus = $request->estatus;
                }
                $contratos->where(function ($query) use ($estatus) {
                    $query->orWhere('factura.estatus', $estatus);
                });
            }
            if($request->grupo_corte){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('crm.grupo_corte', $request->grupo_corte);
                });
            }
            if($request->servidor){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('crm.servidor', $request->servidor);
                });
            }
        }

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $contratos->where('contactos.oficina', auth()->user()->oficina);
            }
        }
        
        return datatables()->eloquent($contratos)
            ->editColumn('nombre', function (CRM $crm) {
                return "<a href=" . route('contactos.show', $crm->cliente) . " target='_blank'>{$crm->c_nombre} {$crm->c_apellido1} {$crm->c_apellido2}</div></a>";
            })
            ->editColumn('nit', function (CRM $crm) {
                return "<center>".$crm->c_nit."</center>";
            })
            ->editColumn('celular', function (CRM $crm) {
                return "<center>".$crm->c_celular."</center>";
            })
            ->editColumn('estado', function (CRM $crm) {
                return "<center><span class='text-{$crm->estado('true')}'><strong>{$crm->estado()}</strong></span></center>";
            })
            ->editColumn('created_by', function (CRM $crm) {
                return "<center>".$crm->created_by()."</center>";
            })
            ->editColumn('updated_at', function (CRM $crm) {
                return "<center>".$crm->updated_at()."</center>";
            })
            ->editColumn('estatus', function (CRM $crm) {
                return "<center><span class='text-{$crm->factura('true')}'><strong>{$crm->factura()}</strong></span></center>";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "crm.acciones-informe")
            ->rawColumns(['acciones', 'nombre', 'nit', 'celular', 'estado', 'created_by', 'updated_at', 'estatus'])
            ->toJson();
    }
    
    public function store(Request $request){
        $request->validate([
            'idcliente' => 'required',
            'llamada' => 'required',
            'tiempo' => 'required'
        ]);

        $comunicar = true;

        if(request()->modalGestion){
            $comunicar = request()->send_mail;
        }
        
        //$crm = CRM::where('cliente', $request->idcliente)->where('empresa', Auth::user()->empresa)->where('id', $idCRM)->get()->last();
        $crm = CRM::find($request->idCRM);

        $accion_log = '';
        if($crm){
            if($request->llamada == 0){
                $estado = 3;
                $accion_log .= 'CRM Gestionado/Sin Contestar';
            }else{
                $estado = 1;
                $accion_log .= 'CRM Gestionado';
            }
            
            if($request->retirado == 1){
                $estado = 4;
                $accion_log .= ': Cliente Retirado<br>';
            }else if($request->retirado == 2){
                $estado = 5;
                $accion_log .= ': Cliente Retirado Total<br>';
            }
            
            $crm->llamada      = $request->llamada;
            //$crm->informacion  = $request->informacion;
            $crm->informacion  = ($crm->informacion) ? $crm->informacion.'<hr><b>Fecha: </b>'.date('d-m-Y g:i:s A').'<br><b>Gestionado:</b> '.Auth::user()->nombres.'<br><b>Información: </b>'.$request->informacion : '<b>Fecha: </b>'.date('d-m-Y g:i:s A').'<br><b>Gestionado:</b> '.Auth::user()->nombres.'<br><b>Información: </b>'.$request->informacion;
            $crm->promesa_pago = $request->promesa_pago;
            $crm->fecha_pago   = $request->fecha;
            $crm->hora_pago    = $request->hora_pago;
            $crm->tiempo       = $request->tiempo;
            $crm->created_by   = auth()->user()->id;
            $crm->empresa      = Auth::user()->empresa;
            
            if($request->promesa_pago && $request->fecha){
                $factura = Factura::find($crm->factura);
                $factura->vencimiento = date('Y-m-d', strtotime($request->fecha));
                $factura->promesa_pago = date('Y-m-d', strtotime($request->fecha));
                $factura->observaciones = $factura->observaciones.' | Compromiso de Pago ('.$request->fecha.') creada por '.Auth::user()->nombres.' el '.date('d-m-Y g:i:s A');
                $factura->save();

                ### PROMESA DE PAGO ###

                $nro_promesa = 0;
                $nro_promesa = PromesaPago::all()->count();
                $nro_promesa++;

                $promesa_pago              = New PromesaPago;
                $promesa_pago->nro         = $nro_promesa;
                $promesa_pago->factura     = $factura->id;
                $promesa_pago->cliente     = $factura->cliente;
                $promesa_pago->fecha       = date('Y-m-d');
                $promesa_pago->vencimiento = date('Y-m-d', strtotime($request->fecha));
                $promesa_pago->hora_pago   = $request->hora_pago;
                $promesa_pago->created_by  = Auth::user()->id;
                $promesa_pago->save();

                /* VERIFICAR SI EL CONTRATO ESTÁ DESHABILITADO PARA HABILITARLO */

                // $contrato = $factura->cliente()->contrato();
                // if ($contrato) {
                //     $mikrotik = Mikrotik::find($contrato->server_configuration_id);
                //     $API = new RouterosAPI();
                //     $API->port = $mikrotik->puerto_api;

                //     if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                //         $API->write('/ip/firewall/address-list/print', TRUE);
                //         $ARRAYS = $API->read();

                //         #ELIMINAMOS DE MOROSOS#

                //         $API->write('/ip/firewall/address-list/print', false);
                //         $API->write('?address='.$contrato->ip, false);
                //         $API->write("?list=morosos",false);
                //         $API->write('=.proplist=.id');
                //         $ARRAYS = $API->read();
                //         if(count($ARRAYS)>0){
                //             $API->write('/ip/firewall/address-list/remove', false);
                //             $API->write('=.id='.$ARRAYS[0]['.id']);
                //             $READ = $API->read();
                //         }
                //         #ELIMINAMOS DE MOROSOS#

                //         #AGREGAMOS A IP_AUTORIZADAS#
                //         $API->comm("/ip/firewall/address-list/add",
                //             array(
                //                 "address" => $contrato->ip,
                //                 "list" => 'ips_autorizadas'
                //             )
                //         );
                //         #AGREGAMOS A IP_AUTORIZADAS#

                //         $contrato->state = 'enabled';
                //         $contrato->save();
                //         $API->disconnect();
                //     }
                // }

                ### LOG CRM ###
                $accion_log .= ': Asociando Promesa de Pago N° '.$nro_promesa.'<br>';

                ### EVÍO DE SMS AL CLIENTE ##

                $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('status', 1)->first();
                if($servicio && $comunicar){
                    $mensaje = "Hola, usted ha realizado una promesa de pago para el ".$request->fecha.". Lo esperamos en ".auth()->user()->empresa()->nombre;
                    $factura = Factura::find($crm->factura);

                    if($servicio->nombre == 'Hablame SMS'){
                        if($servicio->api_key && $servicio->user && $servicio->pass){
                            if($factura->cliente()->celular){
                                $numero = str_replace('+', '', $factura->cliente()->celular);
                                $numero = str_replace(' ', '', $numero);
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
                            }
                        }
                    }elseif($servicio->nombre == 'SmsEasySms'){
                        if($servicio->user && $servicio->pass){
                            if($factura->cliente()->celular){
                                $numero = str_replace('+', '', $factura->cliente()->celular);
                                $numero = str_replace(' ', '', $numero);
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
                                curl_setopt(
                                    $ch,
                                    CURLOPT_HTTPHEADER,
                                    array(
                                        "Accept: application/json",
                                        "Authorization: Basic " . base64_encode($login . ":" . $password)
                                    )
                                );
                                $result = curl_exec($ch);
                                $err  = curl_error($ch);
                                curl_close($ch);
                            }
                        }
                    }else{
                        if($servicio->user && $servicio->pass){
                            if($factura->cliente()->celular){
                                $numero = str_replace('+', '', $factura->cliente()->celular);
                                $numero = str_replace(' ', '', $numero);
                                $post['to'] = array('57'.$numero);
                                $post['text'] = $mensaje;
                                $post['from'] = "";
                                $login = $servicio->user;
                                $password = $servicio->pass;
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, "https://masivos.colombiared.com.co/Api/rest/message");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                                curl_setopt(
                                    $ch,
                                    CURLOPT_HTTPHEADER,
                                    array(
                                        "Accept: application/json",
                                        "Authorization: Basic " . base64_encode($login . ":" . $password)
                                    )
                                );
                                $result = curl_exec($ch);
                                $err  = curl_error($ch);
                                curl_close($ch);
                            }
                        }
                    }
                }
            }
            
            if($request->numero_nuevo){
                $contacto = Contacto::find($crm->cliente);
                $contacto->celular = $request->numero_nuevo;
                $contacto->save();
                $estado = 6;

                $accion_log .= ': Actualización de número telefónico '.$request->numero_nuevo.'<br>';
            }
            
            $crm->estado = $estado;
            $crm->save();

            ### LOG CRM ###

            $log             = New CRMLOG;
            $log->id_crm     = $crm->id;
            $log->accion     = $accion_log;
            $log->created_by = Auth::user()->id;
            $log->save();
            
            return response()->json([
                'success' => true,
                'text' => 'SE HA REALIZADO LA GESTIÓN DEL CLIENTE DE MANERA SATISFACTORIA',
                'title' => 'REGISTRO GUARDADO',
                'icon'  => 'success'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'text' => 'EL CLIENTE QUE INTENTA GESTIONAR, YA SE ENCUENTRA CON ESTADO DE GESTIONADO',
            'title' => 'ERROR',
            'icon'  => 'error'
        ]);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $crm = CRM::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        
        if($crm){
            view()->share(['subseccion' => 'crm_cartera', 'title' => 'Detalles CRM: '.$crm->id]);
            return view('crm.show')->with(compact('crm'));
        }
    }
    
    public function edit($id){
        
    }

    public function update(Request $request, $id){
        
    }
    
    public function destroy($id){
        
    }
    
    public function contacto($id, $crm){
        //$contacto = DB::select("SELECT C.id, C.nombre, C.apellido1, C.apellido2, C.nit, C.tip_iden, C.telefono1, C.celular FROM contactos AS C WHERE C.id = '$id'");

        $contacto = Contacto::join('crm', 'crm.cliente', '=', 'contactos.id')->where('contactos.id', $id)->where('crm.id', $crm)->select('contactos.id', 'contactos.nombre', 'contactos.apellido1', 'contactos.apellido2', 'contactos.nit', 'contactos.tip_iden', 'contactos.telefono1', 'contactos.celular')->get();
        if ($contacto) {
            return json_encode($contacto);
        }
    }
    
    public function exportar(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Informe CRM";
        //$titulosColumnas = array('Nombre', 'Identificacion', 'Telefono', 'Estado', 'Gestionado por', 'Gestionado el', 'Factura', 'Corte', 'Servidor');
        $titulosColumnas = array('Nombre', 'Identificacion', 'Celular', 'Estado', 'Gestionado por', 'Gestionado el', 'Grupo Corte', 'Servidor');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $objPHPExcel->getProperties()->setCreator("Sistema")
        ->setLastModifiedBy("Sistema")
        ->setTitle("Informe CRM")
        ->setSubject("Informe CRM")
        ->setDescription("Informe CRM")
        ->setKeywords("Informe CRM")
        ->setCategory("Informe CRM");
        
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:H1');
            
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
            
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:H2');
            
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2','Desde '.$request->desde.' hasta '.$request->hasta); // Titulo del reporte

        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:H3')->applyFromArray($estilo);

        $estilo = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '267eb5')
            ),
            'font'  => array(
                'color' => array('rgb' => 'ffffff')
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->applyFromArray($estilo);


        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i=4;
        $letra=0;
        
        $crms = CRM::select('crm.*', 'contactos.nit as c_nit', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.celular as c_celular', 'factura.codigo', 'factura.estatus', 'items_factura.precio')
        ->join('contactos', 'crm.cliente', '=', 'contactos.id')
        ->join('factura', 'crm.factura', '=', 'factura.id')
        ->join('items_factura', 'items_factura.factura', '=', 'factura.id')
        ->where('crm.empresa', Auth::user()->empresa);

        if(isset($request->cliente)){
            $crms->where('crm.cliente', $request->cliente);
        }
        if(isset($request->created_by)){
            $crms->where('crm.created_by', $request->created_by);
        }
        if(isset($request->desde)){
            $desde = Carbon::parse($request->desde.'00:00:00')->format('Y-m-d H:i:s');
            $crms->where('crm.updated_at', '>=', $desde);
        }
        if(isset($request->hasta)){
            $hasta = Carbon::parse($request->hasta.'23:59:59')->format('Y-m-d H:i:s');
            $crms->where('crm.updated_at', '<=', $hasta);
        }
        if($request->estatus){
            if($request->estatus == 'A'){
                $estatus = 0;
            }else{
                $estatus = $request->estatus;
            }
            $crms->where('factura.estatus', $estatus);
        }
        if(isset($request->servidor)){
            $crms->where('crm.servidor', $request->servidor);
        }
        if(isset($request->grupo_corte)){
            $crms->where('crm.grupo_corte', $request->grupo_corte);
        }
        if(isset($request->estado)){
            $crms->where('crm.estado', $request->estado);
        }else{
            $crms->where('crm.estado', '<>', 0);
        }
        
        $crms=$crms->orderBy('crm.updated_at', 'desc')->get();

        foreach ($crms as $crm) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $crm->c_nombre)
                ->setCellValue($letras[1].$i, $crm->c_nit)
                ->setCellValue($letras[2].$i, $crm->c_celular)
                ->setCellValue($letras[3].$i, $crm->estado())
                ->setCellValue($letras[4].$i, $crm->created_by())
                ->setCellValue($letras[5].$i, $crm->updated_at())
                ->setCellValue($letras[6].$i, $crm->grupo_corte())
                ->setCellValue($letras[7].$i, $crm->servidor());
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:H'.$i)->applyFromArray($estilo);

        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Informe CRM');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Informe_CRM.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    public static function notificacion(){
        $fecha = date('d-m-Y');
        $fecha = date('d-m-Y', strtotime("-1 days", strtotime($fecha)));
        $notificaciones = CRM::join('factura as f','f.id','=','crm.factura')->where('f.estatus',1)->where('crm.fecha_pago', $fecha)->where('crm.created_by', Auth::user()->id)->select('f.id as factura', 'f.cliente', 'f.estatus', 'crm.id', 'crm.estado')->get();
        
        foreach($notificaciones as $notificacion){
            $notificacion->estado = 2;
            $notificacion->notificacion = 1;
            $notificacion->save();

            
            /*$crm = new CRM();
            $crm->cliente = $notificacion->cliente;
            $crm->factura = $notificacion->factura;
            $crm->save();*/
        }
        
        return response()->json(['success' => true, 'data' => $notificaciones]);
    }
    
    public function status($id){
        $crm = CRM::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if($crm){
            $cliente = Contacto::find($crm->cliente);
            $contrato = Contrato::where('client_id', $cliente->id)->where('empresa', Auth::user()->empresa)->where('status', 1)->first();

            if($contrato){
                $contrato->status = 0;
                $contrato->save();
            }
            $crm->estado = 5;
            $crm->update();
            
            return response()->json([
                'success' => true,
                'text' => 'SE HA CAMBIADO DE ESTADO EL REGISTRO DE MANERA SATISFACTORIA',
                'title' => 'REGISTRO ACTUALIZADO',
                'icon'  => 'success'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'text' => 'EL CLIENTE QUE INTENTA GESTIONAR, YA SE ENCUENTRA CON ESTADO DE GESTIONADO',
            'title' => 'ERROR',
            'icon'  => 'error'
        ]);
    }

    public function log($id){
        $this->getAllPermissions(Auth::user()->id);
        $crm = CRM::find($id);
        if ($crm) {
            view()->share(['icon' => 'fas fa-clipboard-list', 'title' => 'Log | CRM: '.$crm->id]);
            return view('crm.log')->with(compact('crm'));
        } else {
            return back()->with('error', 'NO SE HA PODIDO OBTENER EL LOG DEL CRM');
        }
        return back()->with('error', 'NO SE HA PODIDO OBTENER EL LOG DEL CRM');
    }

    public function logsCRM(Request $request, $crm){
        $modoLectura = auth()->user()->modo_lectura();
        $crms = CRMLOG::query();
        $crms->where('id_crm', $crm);

        return datatables()->eloquent($crms)
        ->editColumn('created_at', function (CRMLOG $crm) {
            return date('d-m-Y g:i:s A', strtotime($crm->created_at));
        })
        ->editColumn('created_by', function (CRMLOG $crm) {
            return $crm->created_by()->nombres;
        })
        ->editColumn('accion', function (CRMLOG $crm) {
            return $crm->accion;
        })
        ->editColumn('informacion', function (CRMLOG $crm) {
            return $crm->crmObj->informacion;
        })
        ->rawColumns(['created_at', 'created_by', 'accion', 'informacion'])
        ->toJson();
    }

    public function cambiarEtiqueta($etiqueta, $crm){

        $crm =  CRM::where('id', $crm)->where('empresa', Auth::user()->empresa)->first();

        $crm->etiqueta_id = $etiqueta;

        $crm->update();

        return $crm->etiqueta;
    }
}
