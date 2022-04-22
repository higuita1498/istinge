<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Mail;

use App\Model\Ingresos\Factura;
use App\NumeracionFactura;
use App\Model\Inventario\Bodega; 
use App\Model\Ingresos\ItemsFactura;
use App\Model\Inventario\ProductosBodega; 
use App\Model\Inventario\Inventario;
use App\Contrato;
use App\Contacto;
use App\TerminosPago;
use App\Empresa;
use App\GrupoCorte;
use App\Mikrotik;
use App\CRM;
use App\Blacklist;
use App\Mail\BlacklistMailable;
use App\ServidorCorreo;
use App\Integracion;
use App\PlanesVelocidad;

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;

class CronController extends Controller
{
    public static function CrearFactura(){
        $empresa = Empresa::find(1);

        if($empresa->factura_auto == 1){
            $i=0;
            $date = date('d') * 1;
            $numeros = [];
            $fail = 0;
            $succ = 0;

            $grupos_corte = GrupoCorte::where('fecha_factura', $date)->where('status', 1)->get();

            foreach($grupos_corte as $grupo_corte){
                $contratos = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->join('empresas as e', 'e.id', '=', 'contracts.empresa')->select('contracts.id', 'contracts.public_id', 'c.id as cliente', 'contracts.state', 'contracts.fecha_corte', 'contracts.fecha_suspension', 'contracts.facturacion', 'contracts.plan_id', 'c.nombre', 'c.nit', 'c.celular', 'c.telefono1', 'e.terminos_cond', 'e.notas_fact', 'contracts.servicio_tv')->where('contracts.grupo_corte',$grupo_corte->id)->where('contracts.status',1)->where('contracts.state','enabled')->get();

                $num = Factura::where('empresa',1)->orderby('nro','asc')->get()->last();
                if($num){
                    $numero = $num->nro;
                }else{
                    $numero = 0;
                }

                $qwerty = Carbon::now()->endOfMonth()->toDateString();
                $ultimo = explode("-", $qwerty);

                foreach ($contratos as $contrato) {
                    $numero++;

                    //Obtenemos el número depende del contrato que tenga asignado (con fact electrpinica o estandar).
                    $nro = NumeracionFactura::tipoNumeracion($contrato);

                    if($contrato->fecha_suspension){
                        $fecha_suspension = $contrato->fecha_suspension;
                    }else{
                        $fecha_suspension = $grupo_corte->fecha_suspension;
                    }

                    $plazo=TerminosPago::where('dias',$grupo_corte->fecha_pago)->first();

                    $tipo = 1; //1= normal, 2=Electrónica.

                    $electronica = Factura::booleanFacturaElectronica($contrato->cliente);

                    if($contrato->facturacion == 3 && !$electronica){
                        return redirect('empresa/facturas')->with('success', "La Factura Electrónica no pudo ser creada por que no ha pasado el tiempo suficiente desde la ultima factura");
                    }elseif($contrato->facturacion == 3 && $electronica){
                        $tipo = 2;
                    }

                    $inicio = $nro->inicio;
                    $nro->inicio += 1;
                    $factura = new Factura;
                    $factura->nro           = $numero;
                    $factura->codigo        = $nro->prefijo.$inicio;
                    $factura->numeracion    = $nro->id;
                    $factura->plazo         = $plazo->id;
                    $factura->term_cond     = $contrato->terminos_cond;
                    $factura->facnotas      = $contrato->notas_fact;
                    $factura->empresa       = 1;
                    $factura->cliente       = $contrato->cliente;
                    $factura->fecha         = $ultimo[0].'-'.$ultimo[1].'-'.$grupo_corte->fecha_factura;
                    $factura->tipo          = $tipo;
                    $factura->vencimiento   = $ultimo[0].'-'.($ultimo[1]+1).'-'.$fecha_suspension;
                    $factura->suspension    = $ultimo[0].'-'.($ultimo[1]+1).'-'.$fecha_suspension;
                    $factura->pago_oportuno = $ultimo[0].'-'.$ultimo[1].'-'.$grupo_corte->fecha_pago;
                    $factura->observaciones = 'Facturación Automática - Corte '.$grupo_corte->fecha_corte;
                    $factura->bodega        = 1;
                    $factura->vendedor      = 1;

                    if($contrato){
                        $factura->contrato_id = $contrato->id;
                    }
                    
                    $factura->save();

                    ## Se carga el item a la factura (Plan de Internet) ##

                    if($contrato->plan_id){
                        $plan = PlanesVelocidad::find($contrato->plan_id);
                        $item = Inventario::find($plan->item);

                        $item_reg = new ItemsFactura;
                        $item_reg->factura     = $factura->id;
                        $item_reg->producto    = $item->id;
                        $item_reg->ref         = $item->ref;
                        $item_reg->precio      = $item->precio;
                        $item_reg->descripcion = $plan->name;
                        $item_reg->id_impuesto = $item->id_impuesto;
                        $item_reg->impuesto    = $item->impuesto;
                        $item_reg->cant        = 1;
                        $item_reg->save();
                    }

                    ## Se carga el item a la factura (Plan de Televisión) ##

                    if($contrato->servicio_tv){
                        $item = Inventario::find($contrato->servicio_tv);
                        $item_reg = new ItemsFactura;
                        $item_reg->factura     = $factura->id;
                        $item_reg->producto    = $item->id;
                        $item_reg->ref         = $item->ref;
                        $item_reg->precio      = $item->precio;
                        $item_reg->descripcion = $item->producto;
                        $item_reg->id_impuesto = $item->id_impuesto;
                        $item_reg->impuesto    = $item->impuesto;
                        $item_reg->cant        = 1;
                        $item_reg->save();
                    }

                    $nro->save();
                    $i++;

                    $numero = str_replace('+','',$factura->cliente()->celular);
                    $numero = str_replace(' ','',$numero);

                    array_push($numeros, '57'.$numero);
                }
            }

            $servicio = Integracion::where('empresa', 1)->where('tipo', 'SMS')->where('status', 1)->first();
            if($servicio){
                if($servicio->nombre == 'Hablame SMS'){
                    if($servicio->api_key && $servicio->user && $servicio->pass){
                        $post['toNumber'] = $numeros;
                        $post['sms'] = "Estimado cliente, se le informa que su factura de internet ha sido generada. ".$empresa->slogan;

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
                }else{
                    if($servicio->user && $servicio->pass){
                        if(count($numeros)){
                            $post['to'] = $numeros;
                            $post['text'] = "Estimado cliente, se le informa que su factura de internet ha sido generada. ".$empresa->slogan;
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
                                    $factura->response = $msj;
                                    $factura->save();
                                    $fail++;
                                }else{
                                    $factura->mensaje = 1;
                                    $factura->response = 'Mensaje enviado correctamente.';
                                    $factura->save();
                                    $succ++;
                                }
                            }
                        }
                    }
                }
            }

            if (file_exists("CrearFactura.txt")){
                $file = fopen("CrearFactura.txt", "a");
                fputs($file, "-----------------".PHP_EOL);
                fputs($file, "Fecha de Generación: ".date('Y-m-d').''. PHP_EOL);
                fputs($file, "Facturas Generadas: ".$i.''. PHP_EOL);
                fputs($file, "SMS Enviados: ".$succ.''. PHP_EOL);
                fputs($file, "SMS NO Enviados: ".$fail.''. PHP_EOL);
                fputs($file, "-----------------".PHP_EOL);
                fclose($file);
            }else{
                $file = fopen("CrearFactura.txt", "w");
                fputs($file, "-----------------".PHP_EOL);
                fputs($file, "Fecha de Generación: ".date('Y-m-d').''. PHP_EOL);
                fputs($file, "Facturas Generadas: ".$i.''. PHP_EOL);
                fputs($file, "SMS Enviados: ".$succ.''. PHP_EOL);
                fputs($file, "SMS NO Enviados: ".$fail.''. PHP_EOL);
                fputs($file, "-----------------".PHP_EOL);
                fclose($file);
            }
        }
    }

    public static function CortarFacturas(){
        $i=0;
        $fecha = date('Y-m-d');

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'cs.state')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('f.vencimiento', $fecha)->
            where('contactos.status',1)->
            where('cs.state','enabled')->
            take(15)->
            get();

        //dd($contactos);

        $empresa = Empresa::find(1);
        foreach ($contactos as $contacto) {
            $contrato = Contrato::where('client_id', $contacto->id)->first();

            $crm = CRM::where('cliente', $contacto->id)->whereIn('estado', [0, 3])->delete();
            $crm = new CRM();
            $crm->cliente = $contacto->id;
            $crm->factura = $contacto->factura;
            $crm->servidor = $contrato->server_configuration_id;
            $crm->grupo_corte = $contrato->grupo_corte;
            $crm->save();

            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($contrato) {
                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    $API->write('/ip/firewall/address-list/print', TRUE);
                    $ARRAYS = $API->read();
                    if($contrato->state == 'enabled'){
                        if($contrato->ip){
                            $API->comm("/ip/firewall/address-list/add", array(
                                "address" => $contrato->ip,
                                "comment" => $contrato->servicio,
                                "list" => 'morosos'
                                )
                            );

                            #ELIMINAMOS DE IP_AUTORIZADAS#
                            $API->write('/ip/firewall/address-list/print', false);
                            $API->write('?address='.$contrato->ip, false);
                            $API->write("?list=ips_autorizadas",false);
                            $API->write('=.proplist=.id');
                            $ARRAYS = $API->read();
                            if(count($ARRAYS)>0){
                                $API->write('/ip/firewall/address-list/remove', false);
                                $API->write('=.id='.$ARRAYS[0]['.id']);
                                $READ = $API->read();
                            }
                            #ELIMINAMOS DE IP_AUTORIZADAS#
                        }
                        $contrato->state = 'disabled';
                        $i++;
                    }
                    $API->disconnect();
                    $contrato->save();
                }
            }
        }
        if (file_exists("CorteFacturas.txt")){
            $file = fopen("CorteFacturas.txt", "a");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Corte: ".date('Y-m-d').''. PHP_EOL);
            fputs($file, "Contratos Deshabilitados: ".$i.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }else{
            $file = fopen("CorteFacturas.txt", "w");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Corte: ".date('Y-m-d').''. PHP_EOL);
            fputs($file, "Contratos Deshabilitados: ".$i.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }
    }

    public static function migrarCRM(){
        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->join('contracts as cs','cs.client_id','=','contactos.id')->select('contactos.id as cliente', 'f.id as factura', 'cs.grupo_corte', 'cs.server_configuration_id')->where('f.estatus',1)->where('f.fecha','>=',('2022-01-01'))->where('cs.state','disabled')->where('cs.status',1)->where('contactos.status',1)->groupBy('contactos.id')->get();
        //dd($contactos);
        foreach ($contactos as $contacto) {
            CRM::where('cliente', $contacto->cliente)->where('factura', $contacto->factura)->whereIn('estado', [0,3])->delete();
            $crm = new CRM();
            $crm->cliente = $contacto->cliente;
            $crm->factura = $contacto->factura;
            $crm->grupo_corte = $contacto->grupo_corte;
            $crm->servidor = $contacto->server_configuration_id;
            $crm->save();
        }
    }

    public static function monitorBlacklist(){
        $blacklists = Blacklist::all();
        $empresa    = Empresa::find(1);
        $api_key    = $empresa->api_key_hetrixtools;
        $contact    = $empresa->id_contacto_hetrixtools;
        $respon     = '';
        $datos      = [];

        if($api_key || $contact){
            foreach($blacklists as $blacklist) {
                $url = 'https://api.hetrixtools.com/v2/'.$api_key.'/blacklist-check/ipv4/'.$blacklist->ip.'/';

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));
                $result = curl_exec($curl);
                curl_close($curl);

                $response = json_decode($result, true);
                if($response['status'] == 'ERROR'){
                    $respon .= $blacklist->ip.' - '.$response['error_message'].'<br>';
                }else{
                    $blacklist->blacklisted_count = $response['blacklisted_count'];
                    $blacklist->estado = ($response['blacklisted_count'] == 0) ? 1:2;
                    $blacklist->response = '';
                    $blacklist->save();
                    $respon .= $blacklist->ip.' - '.$response['blacklisted_count'].'<br>';

                    if($blacklist->estado == 2){
                        $var = array(
                            'nombre' => $blacklist->nombre,
                            'ip' => $blacklist->ip,
                            'blacklisted_count' => $blacklist->blacklisted_count,
                            'estado' => $blacklist->estado,
                            'empresa' => $empresa->nombre,
                            'color' => $empresa->color
                        );

                        array_push($datos,$var);
                    }
                }
            }

            if(count($datos)>0){
                $correo = new BlacklistMailable($datos);
                $host = ServidorCorreo::where('estado', 1)->where('empresa', 1)->first();
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
                Mail::to($empresa->email)->send($correo);
            }
        }
    }

    public static function PagoOportuno(){
        $empresa = Empresa::find(1);
        $i=0;
        $fecha = date('Y-m-d');
        $numeros = [];
        $fail = 0;
        $succ = 0;

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            select('contactos.celular', 'f.vencimiento')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('f.pago_oportuno', $fecha)->
            where('contactos.status',1)->
            where('cs.status',1)->
            get();

        foreach ($contactos as $contacto) {
            $numero = str_replace('+','',$contacto->celular);
            $numero = str_replace(' ','',$numero);
            array_push($numeros, '57'.$numero);

            $vencimiento = $contacto->vencimiento;
        }

        $servicio = Integracion::where('empresa', 1)->where('tipo', 'SMS')->where('status', 1)->first();
        if($servicio){
            if($servicio->nombre == 'Hablame SMS'){
                if($servicio->api_key && $servicio->user && $servicio->pass){
                    $post['toNumber'] = $numeros;
                    $post['sms'] = "Estimado cliente, su fecha limite de pago es el ".date('d-m-Y', strtotime($vencimiento)).", recuerde pagar su factura y evite la suspension del servicio. ".$empresa->slogan;

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
            }else{
                if($servicio->user && $servicio->pass){
                    if(count($numeros)){
                        $post['to'] = $numeros;
                        $post['text'] = "Estimado cliente, su fecha limite de pago es el ".date('d-m-Y', strtotime($vencimiento)).", recuerde pagar su factura y evite la suspension del servicio. ".$empresa->slogan;
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

                        }else{
                            $response = json_decode($result, true);
                            if(isset($response['error'])){
                                $fail++;
                            }else{
                                $succ++;
                            }
                        }
                    }
                }
            }
        }

        if (file_exists("PagoOportuno.txt")){
            $file = fopen("PagoOportuno.txt", "a");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Notificación: ".date('d-m-Y').''. PHP_EOL);
            fputs($file, "SMS Enviados: ".$succ.''. PHP_EOL);
            fputs($file, "SMS NO Enviados: ".$fail.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }else{
            $file = fopen("PagoOportuno.txt", "w");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Notificación: ".date('d-m-Y').''. PHP_EOL);
            fputs($file, "SMS Enviados: ".$succ.''. PHP_EOL);
            fputs($file, "SMS NO Enviados: ".$fail.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }
    }

    public static function PagoVencimiento(){
        $empresa = Empresa::find(1);
        $i=0;
        $fecha = date('Y-m-d');
        $numeros = [];
        $fail = 0;
        $succ = 0;

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            select('contactos.celular')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('f.vencimiento', $fecha)->
            where('contactos.status',1)->
            where('cs.status',1)->
            get();

        foreach ($contactos as $contacto) {
            $numero = str_replace('+','',$contacto->celular);
            $numero = str_replace(' ','',$numero);
            array_push($numeros, '57'.$numero);
        }

        $servicio = Integracion::where('empresa', 1)->where('tipo', 'SMS')->where('status', 1)->first();
        if($servicio){
            if($servicio->nombre == 'Hablame SMS'){
                if($servicio->api_key && $servicio->user && $servicio->pass){
                    $post['toNumber'] = $numeros;
                    $post['sms'] = "Estimado cliente su servicio ha sido suspendido por falta de pago, por favor realice su pago para continuar disfrutando de su servicio. ".$empresa->slogan;

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
            }else{
                if($servicio->user && $servicio->pass){
                    if(count($numeros)){
                        $post['to'] = $numeros;
                        $post['text'] = "Estimado cliente su servicio ha sido suspendido por falta de pago, por favor realice su pago para continuar disfrutando de su servicio. ".$empresa->slogan;
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

                        }else{
                            $response = json_decode($result, true);
                            if(isset($response['error'])){
                                $fail++;
                            }else{
                                $succ++;
                            }
                        }
                    }
                }
            }
        }

        if (file_exists("PagoVencimiento.txt")){
            $file = fopen("PagoVencimiento.txt", "a");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Notificación: ".date('d-m-Y').''. PHP_EOL);
            fputs($file, "SMS Enviados: ".$succ.''. PHP_EOL);
            fputs($file, "SMS NO Enviados: ".$fail.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }else{
            $file = fopen("PagoVencimiento.txt", "w");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Notificación: ".date('d-m-Y').''. PHP_EOL);
            fputs($file, "SMS Enviados: ".$succ.''. PHP_EOL);
            fputs($file, "SMS NO Enviados: ".$fail.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }
    }
}
