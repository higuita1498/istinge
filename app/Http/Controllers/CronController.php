<?php

namespace App\Http\Controllers;

use StdClass;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Mail;
use Config;
use ZipArchive;
use QrCode;
use File;
use DOMDocument;
use Barryvdh\DomPDF\Facade as PDF;
use App\Funcion;
use Illuminate\Support\Facades\Hash;
use Log;

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
use App\Model\Ingresos\FacturaRetencion;

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;


use App\Numeracion;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosFactura;
use App\Banco;
use App\Movimiento;

class CronController extends Controller
{
    public static function precisionAPI($valor, $id){
        $empresa = Empresa::find($id);
        return round($valor, $empresa->precision);
    }

    public function up_transaccion_($modulo, $id, $banco, $contacto, $tipo, $saldo, $fecha, $descripcion, $empresa){
        $movimiento=new Movimiento;
        $regis=Movimiento::where('modulo', $modulo)->where('id_modulo', $id)->first();
        if ($regis) {
            $movimiento=$regis;
        }
        $movimiento->empresa=$empresa;
        $movimiento->banco=$banco;
        $movimiento->contacto=$contacto;
        $movimiento->tipo=$tipo;
        $movimiento->saldo=$saldo;
        $movimiento->fecha=$fecha;
        $movimiento->modulo=$modulo;
        $movimiento->id_modulo=$id;
        $movimiento->descripcion=$id;
        $movimiento->save();
    }

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
                $contratos = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->join('empresas as e', 'e.id', '=', 'contracts.empresa')->select('contracts.id', 'contracts.public_id', 'c.id as cliente', 'contracts.state', 'contracts.fecha_corte', 'contracts.fecha_suspension', 'contracts.facturacion', 'contracts.plan_id', 'contracts.descuento', 'c.nombre', 'c.nit', 'c.celular', 'c.telefono1', 'e.terminos_cond', 'e.notas_fact', 'contracts.servicio_tv')->where('contracts.grupo_corte',$grupo_corte->id)->where('contracts.status',1)->where('contracts.state','enabled')->get();

                $num = Factura::where('empresa',1)->orderby('id','asc')->get()->last();
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

                    if(is_null($nro)){

                    }else{
                        if($contrato->fecha_suspension){
                            $fecha_suspension = $contrato->fecha_suspension;
                        }else{
                            $fecha_suspension = $grupo_corte->fecha_suspension;
                        }

                        $plazo=TerminosPago::where('dias', (((Carbon::now()->endOfMonth()->format('d')*1) - $grupo_corte->fecha_factura) + $grupo_corte->fecha_suspension))->first();

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
                            $item_reg->desc        = $contrato->descuento;
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
                            $item_reg->desc        = $contrato->descuento;
                            $item_reg->save();
                        }

                        $nro->save();
                        $i++;

                        $numero = str_replace('+','',$factura->cliente()->celular);
                        $numero = str_replace(' ','',$numero);

                        array_push($numeros, '57'.$numero);

                        ## ENVIO CORREO ##
                        // $host = ServidorCorreo::where('estado', 1)->where('empresa', $factura->empresa)->first();
                        // if($host){
                        //     $existing = config('mail');
                        //     $new =array_merge(
                        //         $existing, [
                        //             'host' => $host->servidor,
                        //             'port' => $host->puerto,
                        //             'encryption' => $host->seguridad,
                        //             'username' => $host->usuario,
                        //             'password' => $host->password,
                        //             'from' => [
                        //                 'address' => $host->address,
                        //                 'name' => $host->name
                        //             ],
                        //         ]
                        //     );
                        //     config(['mail'=>$new]);

                        //     $empresa = Empresa::find($factura->empresa);
                        //     $emails  = $factura->cliente()->email;
                        //     $tipo    = 'Factura de venta original';
                        //     view()->share(['title' => 'Imprimir Factura']);
                        //     if ($factura) {
                        //         $items = ItemsFactura::where('factura',$factura->id)->get();
                        //         $itemscount=ItemsFactura::where('factura',$factura->id)->count();
                        //         $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
                        //         $resolucion = NumeracionFactura::where('empresa',$empresa->id)->latest()->first();
                        //         //---------------------------------------------//
                        //         if($factura->emitida == 1){
                        //             $impTotal = 0;
                        //             foreach ($factura->totalAPI($empresa->id)->imp as $totalImp){
                        //                 if(isset($totalImp->total)){
                        //                     $impTotal = $totalImp->total;
                        //                 }
                        //             }

                        //             $CUFEvr = $factura->info_cufeAPI($factura->id, $impTotal, $empresa->id);
                        //             $infoEmpresa = Empresa::find($empresa->id);
                        //             $data['Empresa'] = $infoEmpresa->toArray();
                        //             $infoCliente = Contacto::find($factura->cliente);
                        //             $data['Cliente'] = $infoCliente->toArray();
                        //             /*..............................
                        //             Construcción del código qr a la factura
                        //             ................................*/
                        //             $impuesto = 0;
                        //             foreach ($factura->totalAPI($empresa->id)->imp as $key => $imp) {
                        //                 if(isset($imp->total)){
                        //                     $impuesto = $imp->total;
                        //                 }
                        //             }

                        //             $codqr = "NumFac:" . $factura->codigo . "\n" .
                        //             "NitFac:"  . $data['Empresa']['nit']   . "\n" .
                        //             "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
                        //             "FecFac:" . Carbon::parse($factura->created_at)->format('Y-m-d') .  "\n" .
                        //             "HoraFactura" . Carbon::parse($factura->created_at)->format('H:i:s').'-05:00' . "\n" .
                        //             "ValorFactura:" .  number_format($factura->totalAPI($empresa->id)->subtotal, 2, '.', '') . "\n" .
                        //             "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
                        //             "ValorOtrosImpuestos:" .  0.00 . "\n" .
                        //             "ValorTotalFactura:" .  number_format($factura->totalAPI($empresa->id)->subtotal + $factura->impuestos_totalesFe(), 2, '.', '') . "\n" .
                        //             "CUFE:" . $CUFEvr;
                        //             /*..............................
                        //             Construcción del código qr a la factura
                        //             ................................*/
                        //             $pdf = PDF::loadView('pdf.electronicaAPI', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr','CUFEvr', 'empresa'))->stream();
                        //         }else{
                        //             $pdf = PDF::loadView('pdf.electronicaAPI', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion', 'empresa'))->stream();
                        //         }
                        //         //-----------------------------------------------//

                        //         $total = Funcion::ParsearAPI($factura->totalAPI($empresa->id)->total, $empresa->id);
                        //         $key = Hash::make(date("H:i:s"));
                        //         $toReplace = array('/', '$','.');
                        //         $key = str_replace($toReplace, "", $key);
                        //         $factura->nonkey = $key;
                        //         $factura->save();
                        //         $cliente = $factura->cliente()->nombre;
                        //         $tituloCorreo = $empresa->nombre.": Factura N° $factura->codigo";
                        //         $xmlPath = 'xml/empresa1/FV/FV-'.$factura->codigo.'.xml';
                        //     }

                        //     Mail::send('emails.emailAPI', compact('factura', 'total', 'cliente', 'empresa'), function($message) use ($pdf, $emails,$tituloCorreo,$xmlPath){
                        //         $message->attachData($pdf, 'factura.pdf', ['mime' => 'application/pdf']);
                        //         if(file_exists($xmlPath)){
                        //             $message->attach($xmlPath, ['as' => 'factura.xml', 'mime' => 'text/plain']);
                        //         }
                        //         $message->to($emails)->subject($tituloCorreo);
                        //     });
                        // }
                        ## ENVIO CORREO ##
                    }
                }
            }

            $servicio = Integracion::where('empresa', 1)->where('tipo', 'SMS')->where('status', 1)->first();
            if($servicio){
                $mensaje = "Estimado cliente, se le informa que su factura de internet ha sido generada. ".$empresa->slogan;
                if($servicio->nombre == 'Hablame SMS'){
                    if($servicio->api_key && $servicio->user && $servicio->pass){
                        $post['toNumber'] = $numeros;
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
                    }
                }else{
                    if($servicio->user && $servicio->pass){
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
                        curl_setopt($ch, CURLOPT_HTTPHEADER,
                            array(
                                "Accept: application/json",
                                "Authorization: Basic ".base64_encode($login.":".$password)));
                        $result = curl_exec ($ch);
                        $err  = curl_error($ch);
                        curl_close($ch);
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
        $grupos_corte = GrupoCorte::where('fecha_suspension', date('d') * 1)->where('hora_suspension','<=', date('H:i'))->where('hora_suspension_limit','>=', date('H:i'))->where('status', 1)->count();

        if($grupos_corte > 0){
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
        }

        $servicio = Integracion::where('empresa', 1)->where('tipo', 'SMS')->where('status', 1)->first();
        if($servicio){
            $mensaje = "Estimado cliente, su fecha limite de pago es el ".date('d-m-Y').", recuerde pagar su factura y evite la suspension del servicio. ".$empresa->slogan;

            if($servicio->nombre == 'Hablame SMS'){
                if($servicio->api_key && $servicio->user && $servicio->pass){
                    $post['toNumber'] = $numeros;
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
                }
            }else{
                if($servicio->user && $servicio->pass){
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
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array(
                            "Accept: application/json",
                            "Authorization: Basic ".base64_encode($login.":".$password)));
                    $result = curl_exec ($ch);
                    $err  = curl_error($ch);
                    curl_close($ch);
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
            $mensaje = "Estimado cliente su servicio ha sido suspendido por falta de pago, por favor realice su pago para continuar disfrutando de su servicio. ".$empresa->slogan;
            if($servicio->nombre == 'Hablame SMS'){
                if($servicio->api_key && $servicio->user && $servicio->pass){
                    $post['toNumber'] = $numeros;
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
                }
            }else{
                if($servicio->user && $servicio->pass){
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
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                        array(
                            "Accept: application/json",
                            "Authorization: Basic ".base64_encode($login.":".$password)));
                    $result = curl_exec ($ch);
                    $err  = curl_error($ch);
                    curl_close($ch);
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

    public function eventosWompi(Request $request){
        $request = (object) $request->all();

        if($request->event == 'transaction.updated'){
            $timestamp = $request->timestamp;
            $request = (object) $request->data['transaction'];
            $servicio = Integracion::where('nombre', 'WOMPI')->where('tipo', 'PASARELA')->where('lectura', 1)->first();

            $cadena = $request->id.''.$request->status.''.$request->amount_in_cents.''.$timestamp.''.$servicio->api_event;
            $hash = hash("sha256", $cadena);

            if($request->status == 'APPROVED'){
                $factura = Factura::where('codigo', explode("-", $request->reference)[1])->first();
                if($factura->estatus == 1){
                    $empresa = Empresa::find($factura->empresa);
                    $nro = Numeracion::where('empresa', $empresa->id)->first();
                    $caja = $nro->caja;

                    while (true) {
                        $numero = Ingreso::where('empresa', $empresa->id)->where('nro', $caja)->count();
                        if ($numero == 0) {
                            break;
                        }
                        $caja++;
                    }

                    $banco = Banco::where('nombre', 'WOMPI')->where('estatus', 1)->where('lectura', 1)->first();

                    # REGISTRAMOS EL INGRESO
                    $ingreso                = new Ingreso;
                    $ingreso->nro           = $caja;
                    $ingreso->empresa       = $empresa->id;
                    $ingreso->cliente       = $factura->cliente;
                    $ingreso->cuenta        = $banco->id;
                    $ingreso->metodo_pago   = 9;
                    $ingreso->tipo          = 1;
                    $ingreso->fecha         = date('Y-m-d');
                    $ingreso->observaciones = 'Pago Wompi ID: '.$request->id;
                    $ingreso->save();

                    # REGISTRAMOS EL INGRESO_FACTURA
                    $precio         = $this->precisionAPI($request->amount_in_cents/100, $empresa->id);

                    $items          = new IngresosFactura;
                    $items->ingreso = $ingreso->id;
                    $items->factura = $factura->id;
                    $items->pagado  = $factura->pagado();
                    $items->pago    = $precio;

                    if ($precio == $this->precisionAPI($factura->porpagarAPI($empresa->id), $empresa->id)) {
                        $factura->estatus = 0;
                        $factura->save();

                        CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->delete();

                        $crms = CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->get();
                        foreach ($crms as $crm) {
                            $crm->delete();
                        }
                    }

                    $items->save();

                    # AUMENTAMOS LA NUMERACIÓN DE PAGOS
                    $nro->caja = $caja + 1;
                    $nro->save();

                    # REGISTRAMOS EL MOVIMIENTO
                    $ingreso = Ingreso::find($ingreso->id);

                    $this->up_transaccion_(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion, $empresa->id);

                    if($precio){
                        # EJECUTAMOS COMANDOS EN MIKROTIK
                        $cliente = Contacto::where('id', $factura->cliente)->first();
                        $contrato = Contrato::where('client_id', $cliente->id)->first();
                        $res = DB::table('contracts')->where('client_id', $cliente->id)->update(["state" => 'enabled']);

                        # API MK
                        if($contrato->server_configuration_id){
                            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

                            $API = new RouterosAPI();
                            $API->port = $mikrotik->puerto_api;

                            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                                $API->write('/ip/firewall/address-list/print', TRUE);
                                $ARRAYS = $API->read();

                                #ELIMINAMOS DE MOROSOS#
                                $API->write('/ip/firewall/address-list/print', false);
                                $API->write('?address='.$contrato->ip, false);
                                $API->write("?list=morosos",false);
                                $API->write('=.proplist=.id');
                                $ARRAYS = $API->read();

                                if(count($ARRAYS)>0){
                                    $API->write('/ip/firewall/address-list/remove', false);
                                    $API->write('=.id='.$ARRAYS[0]['.id']);
                                    $READ = $API->read();
                                }
                                #ELIMINAMOS DE MOROSOS#

                                #AGREGAMOS A IP_AUTORIZADAS#
                                $API->comm("/ip/firewall/address-list/add", array(
                                    "address" => $contrato->ip,
                                    "list" => 'ips_autorizadas'
                                    )
                                );
                                #AGREGAMOS A IP_AUTORIZADAS#

                                $API->disconnect();

                                $contrato->state = 'enabled';
                                $contrato->save();
                            }
                        }

                        # ENVÍO SMS
                        $servicio = Integracion::where('empresa', $empresa->id)->where('tipo', 'SMS')->where('status', 1)->first();
                        if($servicio){
                            $numero = str_replace('+','',$cliente->celular);
                            $numero = str_replace(' ','',$numero);
                            $mensaje = "Estimado Cliente, le informamos que hemos recibido el pago de su factura por valor de ".Funcion::ParsearAPI($precio, $empresa->id)." gracias por preferirnos. ".$empresa->slogan;
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
                                }
                            }else{
                                if($servicio->user && $servicio->pass){
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
                                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                                        array(
                                            "Accept: application/json",
                                            "Authorization: Basic ".base64_encode($login.":".$password)));
                                    $result = curl_exec ($ch);
                                    $err  = curl_error($ch);
                                    curl_close($ch);
                                }
                            }
                        }
                    }
                    return response('success', 200);
                }
                return response('false', 200);
            }else{
                return response('false', 200);
            }
        }
    }

    public function eventosPayu(Request $request){
        if($request->state_pol == 4){
            $timestamp = $request->timestamp;
            $payu = Integracion::where('nombre', 'PayU')->where('tipo', 'PASARELA')->where('lectura', 1)->first();

            $hash = md5($payu->api_key.'~'.$request->merchant_id.'~'.$request->reference_sale.'~'.$request->value.'~'.$request->currency.'~'.$request->state_pol);

            if($request->sign == $hash){
                $factura = Factura::where('codigo', substr($request->reference_sale, 4))->first();

                if($factura->estatus == 1){
                    $empresa = Empresa::find($factura->empresa);
                    $nro = Numeracion::where('empresa', $empresa->id)->first();
                    $caja = $nro->caja;

                    while (true) {
                        $numero = Ingreso::where('empresa', $empresa->id)->where('nro', $caja)->count();
                        if ($numero == 0) {
                            break;
                        }
                        $caja++;
                    }

                    $banco = Banco::where('nombre', 'PAYU')->where('estatus', 1)->where('lectura', 1)->first();

                    # REGISTRAMOS EL INGRESO
                    $ingreso                = new Ingreso;
                    $ingreso->nro           = $caja;
                    $ingreso->empresa       = $empresa->id;
                    $ingreso->cliente       = $factura->cliente;
                    $ingreso->cuenta        = $banco->id;
                    $ingreso->metodo_pago   = 9;
                    $ingreso->tipo          = 1;
                    $ingreso->fecha         = date('Y-m-d');
                    $ingreso->observaciones = 'Pago PayU ID: '.$request->transaction_id;
                    $ingreso->save();

                    # REGISTRAMOS EL INGRESO_FACTURA
                    $precio         = $this->precisionAPI($request->value, $empresa->id);

                    $items          = new IngresosFactura;
                    $items->ingreso = $ingreso->id;
                    $items->factura = $factura->id;
                    $items->pagado  = $factura->pagado();
                    $items->pago    = $precio;

                    if ($precio == $this->precisionAPI($factura->porpagarAPI($empresa->id), $empresa->id)) {
                        $factura->estatus = 0;
                        $factura->save();

                        CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->delete();

                        $crms = CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->get();
                        foreach ($crms as $crm) {
                            $crm->delete();
                        }
                    }

                    $items->save();

                    # AUMENTAMOS LA NUMERACIÓN DE PAGOS
                    $nro->caja = $caja + 1;
                    $nro->save();

                    # REGISTRAMOS EL MOVIMIENTO
                    $ingreso = Ingreso::find($ingreso->id);

                    $this->up_transaccion_(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion, $empresa->id);

                    if($precio){
                        # EJECUTAMOS COMANDOS EN MIKROTIK
                        $cliente = Contacto::where('id', $factura->cliente)->first();
                        $contrato = Contrato::where('client_id', $cliente->id)->first();
                        $res = DB::table('contracts')->where('client_id', $cliente->id)->update(["state" => 'enabled']);

                        # API MK
                        if($contrato->server_configuration_id){
                            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

                            $API = new RouterosAPI();
                            $API->port = $mikrotik->puerto_api;

                            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                                $API->write('/ip/firewall/address-list/print', TRUE);
                                $ARRAYS = $API->read();

                                #ELIMINAMOS DE MOROSOS#
                                $API->write('/ip/firewall/address-list/print', false);
                                $API->write('?address='.$contrato->ip, false);
                                $API->write("?list=morosos",false);
                                $API->write('=.proplist=.id');
                                $ARRAYS = $API->read();

                                if(count($ARRAYS)>0){
                                    $API->write('/ip/firewall/address-list/remove', false);
                                    $API->write('=.id='.$ARRAYS[0]['.id']);
                                    $READ = $API->read();
                                }
                                #ELIMINAMOS DE MOROSOS#

                                #AGREGAMOS A IP_AUTORIZADAS#
                                $API->comm("/ip/firewall/address-list/add", array(
                                    "address" => $contrato->ip,
                                    "list" => 'ips_autorizadas'
                                    )
                                );
                                #AGREGAMOS A IP_AUTORIZADAS#

                                $API->disconnect();

                                $contrato->state = 'enabled';
                                $contrato->save();
                            }
                        }

                        # ENVÍO SMS
                        $servicio = Integracion::where('empresa', $empresa->id)->where('tipo', 'SMS')->where('status', 1)->first();
                        if($servicio){
                            $numero = str_replace('+','',$cliente->celular);
                            $numero = str_replace(' ','',$numero);
                            $mensaje = "Estimado Cliente, le informamos que hemos recibido el pago de su factura por valor de ".Funcion::ParsearAPI($precio, $empresa->id)." gracias por preferirnos. ".$empresa->slogan;
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
                                }
                            }else{
                                if($servicio->user && $servicio->pass){
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
                                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                                        array(
                                            "Accept: application/json",
                                            "Authorization: Basic ".base64_encode($login.":".$password)));
                                    $result = curl_exec ($ch);
                                    $err  = curl_error($ch);
                                    curl_close($ch);
                                }
                            }
                        }
                    }
                    return abort(200);
                }
                return abort(400);
            }else{
                return abort(400);
            }
        }
        return abort(400);
    }

    public function eventosEpayco(Request $request){
        dd($request);
        /*En esta página se reciben las variables enviadas desde ePayco hacia el servidor.
        Antes de realizar cualquier movimiento en base de datos se deben comprobar algunos valores
        Es muy importante comprobar la firma enviada desde ePayco
        Ingresar  el valor de p_cust_id_cliente lo encuentras en la configuración de tu cuenta ePayco
        Ingresar  el valor de p_key lo encuentras en la configuración de tu cuenta ePayco
        */

        $p_cust_id_cliente = '';
        $p_key             = '';

        $x_ref_payco      = $_REQUEST['x_ref_payco'];
        $x_transaction_id = $_REQUEST['x_transaction_id'];
        $x_amount         = $_REQUEST['x_amount'];
        $x_currency_code  = $_REQUEST['x_currency_code'];
        $x_signature      = $_REQUEST['x_signature'];

        $signature = hash('sha256', $p_cust_id_cliente.'^'.$p_key.'^'.$x_ref_payco.'^'.$x_transaction_id.'^'.$x_amount.'^'.$x_currency_code);

        // obtener invoice y valor en el sistema del comercio
        $numOrder = '2531'; // Este valor es un ejemplo se debe reemplazar con el número de orden que tiene registrado en su sistema
        $valueOrder = '10000';  // Este valor es un ejemplo se debe reemplazar con el valor esperado de acuerdo al número de orden del sistema

        $x_response     = $_REQUEST['x_response'];
        $x_motivo       = $_REQUEST['x_response_reason_text'];
        $x_id_invoice   = $_REQUEST['x_id_invoice'];
        $x_autorizacion = $_REQUEST['x_approval_code'];

        // se valida que el número de orden y el valor coincidan con los valores recibidos
        if ($x_id_invoice === $numOrder && $x_amount === $valueOrder) {
            //Validamos la firma
            if ($x_signature == $signature) {
                /*Si la firma esta bien podemos verificar los estado de la transacción*/
                $x_cod_response = $_REQUEST['x_cod_response'];
                switch ((int) $x_cod_response) {
                    case 1:
                    # code transacción aceptada
                    //echo "transacción aceptada";
                    break;
                    case 2:
                    # code transacción rechazada
                    //echo "transacción rechazada";
                    break;
                    case 3:
                    # code transacción pendiente
                    //echo "transacción pendiente";
                    break;
                    case 4:
                    # code transacción fallida
                    //echo "transacción fallida";
                    break;
                }
            } else {
                die("Firma no válida");
            }
        } else {
            die("número de orden o valor pagado no coinciden");
        }
    }

    public function eventosCombopay(Request $request){
        if($request->transaction_state == 'payment_approved'){
            $factura = Factura::where('codigo', substr($request->invoice_number, 4))->first();

            if($factura->estatus == 1){
                $empresa = Empresa::find($factura->empresa);
                $nro = Numeracion::where('empresa', $empresa->id)->first();
                $caja = $nro->caja;

                while (true) {
                    $numero = Ingreso::where('empresa', $empresa->id)->where('nro', $caja)->count();
                    if ($numero == 0) {
                        break;
                    }
                    $caja++;
                }

                $banco = Banco::where('nombre', 'COMBOPAY')->where('estatus', 1)->where('lectura', 1)->first();

                # REGISTRAMOS EL INGRESO
                $ingreso                = new Ingreso;
                $ingreso->nro           = $caja;
                $ingreso->empresa       = $empresa->id;
                $ingreso->cliente       = $factura->cliente;
                $ingreso->cuenta        = $banco->id;
                $ingreso->metodo_pago   = 9;
                $ingreso->tipo          = 1;
                $ingreso->fecha         = date('Y-m-d');
                $ingreso->observaciones = 'Pago ComboPay ID: '.$request->ticket_id;
                $ingreso->save();

                # REGISTRAMOS EL INGRESO_FACTURA
                $precio         = $this->precisionAPI($request->transaction_value, $empresa->id);

                $items          = new IngresosFactura;
                $items->ingreso = $ingreso->id;
                $items->factura = $factura->id;
                $items->pagado  = $factura->pagado();
                $items->pago    = $precio;

                if ($precio == $this->precisionAPI($factura->porpagarAPI($empresa->id), $empresa->id)) {
                    $factura->estatus = 0;
                    $factura->save();

                    CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->delete();

                    $crms = CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->get();
                    foreach ($crms as $crm) {
                        $crm->delete();
                    }
                }

                $items->save();

                # AUMENTAMOS LA NUMERACIÓN DE PAGOS
                $nro->caja = $caja + 1;
                $nro->save();

                # REGISTRAMOS EL MOVIMIENTO
                $ingreso = Ingreso::find($ingreso->id);

                $this->up_transaccion_(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion, $empresa->id);

                if($precio){
                    # EJECUTAMOS COMANDOS EN MIKROTIK
                    $cliente = Contacto::where('id', $factura->cliente)->first();
                    $contrato = Contrato::where('client_id', $cliente->id)->first();
                    $res = DB::table('contracts')->where('client_id', $cliente->id)->update(["state" => 'enabled']);

                    # API MK
                    if($contrato->server_configuration_id){
                        $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

                        $API = new RouterosAPI();
                        $API->port = $mikrotik->puerto_api;

                        if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                            $API->write('/ip/firewall/address-list/print', TRUE);
                            $ARRAYS = $API->read();

                            #ELIMINAMOS DE MOROSOS#
                            $API->write('/ip/firewall/address-list/print', false);
                            $API->write('?address='.$contrato->ip, false);
                            $API->write("?list=morosos",false);
                            $API->write('=.proplist=.id');
                            $ARRAYS = $API->read();

                            if(count($ARRAYS)>0){
                                $API->write('/ip/firewall/address-list/remove', false);
                                $API->write('=.id='.$ARRAYS[0]['.id']);
                                $READ = $API->read();
                            }
                            #ELIMINAMOS DE MOROSOS#

                            #AGREGAMOS A IP_AUTORIZADAS#
                            $API->comm("/ip/firewall/address-list/add", array(
                                "address" => $contrato->ip,
                                "list" => 'ips_autorizadas'
                                )
                            );
                            #AGREGAMOS A IP_AUTORIZADAS#

                            $API->disconnect();

                            $contrato->state = 'enabled';
                            $contrato->save();
                        }
                    }

                    # ENVÍO SMS
                    $servicio = Integracion::where('empresa', $empresa->id)->where('tipo', 'SMS')->where('status', 1)->first();
                    if($servicio){
                        $numero = str_replace('+','',$cliente->celular);
                        $numero = str_replace(' ','',$numero);
                        $mensaje = "Estimado Cliente, le informamos que hemos recibido el pago de su factura por valor de ".Funcion::ParsearAPI($precio, $empresa->id)." gracias por preferirnos. ".$empresa->slogan;
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
                            }
                        }else{
                            if($servicio->user && $servicio->pass){
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
                                curl_setopt($ch, CURLOPT_HTTPHEADER,
                                    array(
                                        "Accept: application/json",
                                        "Authorization: Basic ".base64_encode($login.":".$password)));
                                $result = curl_exec ($ch);
                                $err  = curl_error($ch);
                                curl_close($ch);
                            }
                        }
                    }
                }
                return abort(200);
            }
            return abort(400);
        }
        return abort(400);
    }
}