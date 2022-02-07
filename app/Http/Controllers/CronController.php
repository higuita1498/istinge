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
        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->join('contracts as cs','cs.client_id','=','contactos.UID')->select('contactos.UID','contactos.nombre','contactos.nit')->where('f.estatus',1)->where('f.suspension','=',date('Y-m-d'))->where('cs.state','enabled')->get();
        
        //dd($contactos);
        
        $empresa = Empresa::find(1);
        foreach ($contactos as $contacto) {
            $contrato = Contrato::where('client_id', $contacto->UID)->first();
            if ($contrato) {
                $res = DB::table('contracts')->where('client_id',$contacto->UID)->update(["state" => 'disabled']);
                $path = $contrato->contrato_id.'?state=disabled';

                /* * * API WISPRO * * */
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/contracts/".$path,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "PUT",
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: ".$empresa->wispro
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                    return "cURL Error #:" . $err;
                } else {
                    $i++;
                }
                /* * * API WISPRO * * */
            }
        }
        echo "Se han suspendido ".$i." contratos";
    }
    
    public static function EnviarSMS(){
        $facturas = Factura::where('fecha', '2021-08-30')->where('mensaje', 0)->take(50)->get();
        //dd($facturas);
        $errores=0;
        $enviados=0;
        
        foreach($facturas as $factura){
            $mensaje = "Top Link le informa que su factura ha sido generada bajo el Nro. ".$factura->codigo.", por un monto de $".$factura->parsear($factura->total()->total).". Ingrese a https://bit.ly/TopLinkPay y realice su pago";
            $numero = str_replace('+','',$factura->cliente()->celular);
            $numero = str_replace(' ','',$numero);
            $post['to'] = array($numero);
            $post['text'] = $mensaje;
            $post['from'] = "TopLink";
            $login ="jjtuiran2021";
            $password = '';
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
                //return back()->with('danger', $err);
                $errores++;
            }else{
                $response = json_decode($result, true);
                if(isset($response['error'])){
                    if($response['error']['code'] == 102){
                        $msj = "No hay destinatarios v��lidos (Cumpla con el formato de nro +5700000000000)";
                    }else if($response['error']['code'] == 103){
                        $msj = "Nombre de usuario o contrase�0�9a desconocidos";
                    }else if($response['error']['code'] == 104){
                        $msj = "Falta el mensaje de texto";
                    }else if($response['error']['code'] == 105){
                        $msj = "Mensaje de texto demasiado largo";
                    }else if($response['error']['code'] == 106){
                        $msj = "Falta el remitente";
                    }else if($response['error']['code'] == 107){
                        $msj = "Remitente demasiado largo";
                    }else if($response['error']['code'] == 108){
                        $msj = "No hay fecha y hora v��lida para enviar";
                    }else if($response['error']['code'] == 109){
                        $msj = "URL de notificaci��n incorrecta";
                    }else if($response['error']['code'] == 110){
                        $msj = "Se super�� el n��mero m��ximo de piezas permitido o n��mero incorrecto de piezas";
                    }else if($response['error']['code'] == 111){
                        $msj = "Cr��dito/Saldo insuficiente";
                    }else if($response['error']['code'] == 112){
                        $msj = "Direcci��n IP no permitida";
                    }else if($response['error']['code'] == 113){
                        $msj = "Codificaci��n no v��lida";
                    }else{
                        $msj = $response['error']['description'];
                    }
    				$factura->response = $msj;
                    $factura->save();
                    $errores++;
                    //return back()->with('danger', 'Env��o Fallido: '.$msj);
                }else{
                    $factura->mensaje = 1;
    				$factura->response = 'Mensaje enviado correctamente.';
                    $factura->save();
                    $enviados++;
                    //return back()->with('success', 'Mensaje enviado correctamente.');
                }
            }
            
        }
        
        return 'SMS Enviados: '.$enviados.' | SMS No Enviados: '.$errores;
    }
}
