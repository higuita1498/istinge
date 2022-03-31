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

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;

class CronController extends Controller
{
    public static function CrearFactura(){
        $empresa = Empresa::find(1);
        if($empresa->factura_auto == 1){
            $i=0;
            $date = date('d');

            $grupos_corte = GrupoCorte::where('fecha_factura', $date)->where('status', 1)->get();

            foreach($grupos_corte as $grupo_corte){
                $contratos = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->join('planes_velocidad as p', 'p.id', '=', 'contracts.plan_id')->join('inventario as i', 'i.id', '=', 'p.item')->join('empresas as e', 'e.id', '=', 'i.empresa')->select('contracts.id','contracts.public_id','c.id as cliente','contracts.state','contracts.fecha_corte','contracts.fecha_suspension','contracts.facturacion','c.nombre','c.nit','c.celular','c.telefono1','p.name as plan', 'p.price','p.item','i.ref', 'i.id_impuesto', 'i.impuesto','e.terminos_cond','e.notas_fact')->where('contracts.grupo_corte',$grupo_corte->id)->where('contracts.status',1)->where('contracts.state','enabled')->get();

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
                    $factura->save();

                    $item_reg = new ItemsFactura;
                    $item_reg->factura     = $factura->id;
                    $item_reg->producto    = $contrato->item;
                    $item_reg->ref         = $contrato->ref;
                    $item_reg->precio      = $contrato->price;
                    $item_reg->descripcion = $contrato->plan;
                    $item_reg->id_impuesto = $contrato->id_impuesto;
                    $item_reg->impuesto    = $contrato->impuesto;
                    $item_reg->cant        = 1;
                    $item_reg->save();
                    $nro->save();
                    $i++;
                }
            }
            echo "Se han generado ".$i." facturas electrónicas";
        }
    }

    public static function CortarFacturas(){
        $i=0;
        $fecha = date('Y-m-d');

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'cs.state')->
            where('f.estatus',1)->
            where('f.tipo', 1)->
            where('f.vencimiento', $fecha)->
            where('contactos.status',1)->
            where('cs.state','enabled')->
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
                        $API->comm("/ip/firewall/address-list/add", array(
                            "address" => $contrato->ip,
                            "comment" => $contrato->servicio,
                            "list" => 'morosos'
                            )
                        );
                        $contrato->state = 'disabled';
                        $i++;
                    }
                    $API->disconnect();
                    $contrato->save();
                }
            }
        }
        echo "Se han suspendido ".$i." contratos";
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
}
