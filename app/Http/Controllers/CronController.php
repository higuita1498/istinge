<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

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

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;

class CronController extends Controller
{
    public static function CrearFactura(){
        $i=0;
        $fecha = date('Y-m-d');
        $fecha_corte = date('d', strtotime("+5 days", strtotime($fecha)));
        
        $grupos_corte = GrupoCorte::where('fecha_corte', $fecha_corte)->where('status', 1)->get();
        
        foreach($grupos_corte as $grupo_corte){
            $contratos = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->join('planes_velocidad as p', 'p.id', '=', 'contracts.plan_id')->join('inventario as i', 'i.id', '=', 'p.item')->join('empresas as e', 'e.id', '=', 'i.empresa')->select('contracts.id','contracts.public_id','c.id as cliente','contracts.state','contracts.fecha_corte','contracts.fecha_suspension','c.nombre','c.nit','c.celular','c.telefono1','p.name as plan', 'p.price','p.item','i.ref','e.terminos_cond','e.notas_fact')->where('contracts.grupo_corte',date($grupo_corte->id))->where('contracts.status',1)->where('contracts.state','enabled')->get();
            //dd($contratos);
            $num = Factura::where('empresa',1)->where('tipo',1)->orderby('nro','asc')->get()->last();
            if($num){
                $numero = $num->nro;
            }else{
                $numero = 0;
            }
            
            $qwerty = Carbon::now()->endOfMonth()->toDateString();
            $ultimo = explode("-", $qwerty);
        
            foreach ($contratos as $contrato) {
                $numero++;
                $nro=NumeracionFactura::where('empresa',1)->where('preferida',1)->where('estado',1)->first();
                if($ultimo[2] == 31 && date('d') == "25"){
                    $fecha_suspension = $grupo_corte->fecha_suspension + 1;
                }else{
                    $fecha_suspension = $grupo_corte->fecha_suspension;
                }
                
                $plazo=TerminosPago::where('dias',$fecha_suspension)->first();
                
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
                $factura->fecha         = date('Y-m-d', strtotime("+5 days", strtotime($fecha)));
                $factura->vencimiento   = date('Y-m-d', strtotime("+".$fecha_suspension." days", strtotime($factura->fecha)));
                $factura->suspension    = date('Y-m-d', strtotime("+".$fecha_suspension." days", strtotime($factura->fecha)));
                $factura->observaciones = 'Facturación Automática - Corte '.$fecha_corte;
                $factura->bodega        = 1;
                $factura->vendedor      = 1;
                $factura->save();
                
                $item_reg = new ItemsFactura;
                $item_reg->factura     = $factura->id;
                $item_reg->producto    = $contrato->item;
                $item_reg->ref         = $contrato->ref;
                $item_reg->precio      = $contrato->price;
                $item_reg->descripcion = $contrato->plan;
                $item_reg->id_impuesto = 2;
                $item_reg->impuesto    = 0;
                $item_reg->cant        = 1;
                $item_reg->save();
                $nro->save();
                $i++;
            }
        }
        echo "Se han generado ".$i." facturas electrónicas";
    }

    public static function CortarFacturas(){
        $i=0;
        $fecha = date('Y-m-d');

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->join('contracts as cs','cs.client_id','=','contactos.id')->select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.estatus', 'f.suspension', 'cs.state')->where('f.estatus',1)->where('f.tipo', 1)->where('f.vencimiento', $fecha)->where('contactos.status',1)->where('cs.state','enabled')->get();

        //dd($contactos);

        $empresa = Empresa::find(1);
        foreach ($contactos as $contacto) {
            $contrato = Contrato::where('client_id', $contacto->id)->first();
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
}
