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
use App\Plantilla;
use App\Producto;
use Auth;

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
        ini_set('max_execution_time', 500);
        $empresa = Empresa::find(1);

        if($empresa->factura_auto == 1){
            $i=0;
            $date = date('d') * 1;
            $numeros = [];
            $bulk = '';
            $fail = 0;
            $succ = 0;

            $grupos_corte = GrupoCorte::where('fecha_factura', $date)->where('status', 1)->get();

            $fecha = Carbon::now()->format('Y-m-d');

            foreach($grupos_corte as $grupo_corte){

                $contratos = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->
                join('empresas as e', 'e.id', '=', 'contracts.empresa')->select('contracts.id', 'contracts.public_id', 'c.id as cliente', 'contracts.state', 'contracts.fecha_corte', 'contracts.fecha_suspension', 'contracts.facturacion', 'contracts.plan_id', 'contracts.descuento', 'c.nombre', 'c.nit', 'c.celular', 'c.telefono1', 'e.terminos_cond', 'e.notas_fact', 'contracts.servicio_tv')->where('contracts.grupo_corte',$grupo_corte->id)->
                where('contracts.status',1)->
                // whereIn('contracts.id',[992,1612])->
                where('contracts.state','enabled')->get();
                
                // return $contratos;
                $num = Factura::where('empresa',1)->orderby('id','asc')->get()->last();
                if($num){
                    $numero = $num->nro;
                }else{
                    $numero = 0;
                }

                if(Carbon::now()->format('d')*1 > $grupo_corte->fecha_suspension){
                    $date = Carbon::create(Carbon::now()->addMonth()->format('Y'), Carbon::now()->addMonth()->format('m'), $grupo_corte->fecha_suspension,0);
                }else{
                    $date = Carbon::create(Carbon::now()->addDays(15)->format('Y'), Carbon::now()->format('m'), $grupo_corte->fecha_suspension, 0);
                }
                
                foreach ($contratos as $contrato) {
            
                    if(DB::table('factura')->where('contrato_id',$contrato->id)->where('fecha',$fecha)->count() == 0){
                    
                    ## Verificamos que el cliente no posea la ultima factura automática abierta, de tenerla no se le genera la nueva factura
                    $fac = Factura::where('cliente', $contrato->cliente)
                    // ->where('facturacion_automatica', 1)
                    ->where('contrato_id',$contrato->id)
                    ->get()->last();
                    
                    // return $fac;
                    
                    //Primer filtro de la validación, que la factura esté cerrada o que no exista una factura.
                    if(isset($fac->estatus) || !$fac){
                        //Segundo filtro, que la fecha de vencimiento de la factura abierta sea mayor a la fecha actual
                        if(isset($fac->vencimiento) && $fac->vencimiento > $fecha || isset($fac->estatus) && $fac->estatus == 0 || !$fac || isset($fac->estatus) && $fac->estatus == 2){
                            if(!$fac || isset($fac) && $fecha != $fac->fecha){
                            $numero=round($numero)+1;
                        
                            //Obtenemos el número depende del contrato que tenga asignado (con fact electrpinica o estandar).
                            $nro = NumeracionFactura::tipoNumeracion($contrato);
    
                            if(is_null($nro)){
                            }else{
                                if($contrato->fecha_suspension){
                                    $fecha_suspension = $contrato->fecha_suspension;
                                }else{
                                    $fecha_suspension = $grupo_corte->fecha_suspension;
                                }
    
                                //$plazo=TerminosPago::where('dias', (((Carbon::now()->endOfMonth()->format('d')*1) - $grupo_corte->fecha_factura) + $grupo_corte->fecha_suspension))->first();
                                $plazo=TerminosPago::where('dias', Funcion::diffDates($date, Carbon::now())+1)->first();
    
                                $tipo = 1; //1= normal, 2=Electrónica.
    
                                $electronica = Factura::booleanFacturaElectronica($contrato->cliente);
                                
                                if($contrato->facturacion == 3 && !$electronica){
                                    $tipo = 1;
                                    // return redirect('empresa/facturas')->with('success', "La Factura Electrónica no pudo ser creada por que no ha pasado el tiempo suficiente desde la ultima factura");
                                }elseif($contrato->facturacion == 3 && $electronica){
                                    $tipo = 2;
                                }
                                
                                $inicio = $nro->inicio;

                                // Validacion para que solo asigne numero consecutivo si no existe.
                                while (Factura::where('codigo',$nro->prefijo.$inicio)->first()) {
                                    $nro->save();
                                    $inicio=$nro->inicio;
                                    $nro->inicio += 1;
                                }
                                
                                $factura = new Factura;
                                $factura->nro           = $numero;
                                $factura->codigo        = $nro->prefijo.$inicio;
                                $factura->numeracion    = $nro->id;
                                $factura->plazo         = isset($plazo->id) ? $plazo->id : '';
                                $factura->term_cond     = $contrato->terminos_cond;
                                $factura->facnotas      = $contrato->notas_fact;
                                $factura->empresa       = 1;
                                $factura->cliente       = $contrato->cliente;
                                $factura->fecha         = $fecha;
                                $factura->tipo          = $tipo;
                                $factura->vencimiento   = $date->format('Y-m-d');
                                $factura->suspension    = $date->format('Y-m-d');
                                $factura->pago_oportuno = Carbon::now()->format('Y-m').'-'.substr(str_repeat(0, 2).$grupo_corte->fecha_pago, - 2);
                                $factura->observaciones = 'Facturación Automática - Corte '.$grupo_corte->fecha_corte;
                                $factura->bodega        = 1;
                                $factura->vendedor      = 1;
                                $factura->prorrateo_aplicado = 0;
                                $factura->facturacion_automatica = 1;
    
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
    
                                ## REGISTRAMOS EL ITEM SI TIENE PAGO PENDIENTE DE ASIGNACIÓN DE PRODUCTO
    
                                $asignacion = Producto::where('contrato', $contrato->id)->where('venta', 1)->where('status', 2)->where('cuotas_pendientes', '>', 0)->get()->last();
    
                                if($asignacion){
                                    $item = Inventario::find($asignacion->producto);
                                    $item_reg = new ItemsFactura;
                                    $item_reg->factura     = $factura->id;
                                    $item_reg->producto    = $item->id;
                                    $item_reg->ref         = $item->ref;
                                    $item_reg->precio      = ($asignacion->precio/$asignacion->cuotas);
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
    
                                if($empresa->sms_factura_generada){
    
                                    $nombreCliente = $factura->cliente()->nombre.' '.$factura->cliente()->apellidos();
                                    $nombreEmpresa = $empresa->nombre;
                                    $codigoFactura = $factura->codigo ?? $factura->nro;
                                    $valorFactura =  $factura->totalAPI($empresa->id)->total;
                                    $fechaVencimiento = $date->format('d-m-Y');
    
                                    $bulksms = $empresa->sms_factura_generada;
                                    $bulksms = str_replace("{cliente}", $nombreCliente, $bulksms);
                                    $bulksms = str_replace("{empresa}", $nombreEmpresa, $bulksms);
                                    $bulksms = str_replace("{factura}", $codigoFactura, $bulksms);
                                    $bulksms = str_replace("{valor}", $valorFactura, $bulksms);
                                    $bulksms = str_replace("{vencimiento}", $fechaVencimiento, $bulksms);
                                    
                                    $bulk .= '{"numero": "57'.$numero.'", "sms": "'.$bulksms.'"},';
    
                                }else if($empresa->nombre == 'FIBRACONEXION S.A.S.' || $empresa->nit == '900822955' || $empresa->nombre == 'Almeidas Comunicaciones S.A.S' ||  $empresa->nit == '901044772'){
                                    $fullname = $factura->cliente()->nombre.' '.$factura->cliente()->apellidos();
                                    $bulksms = ''.trim($fullname).'. '.$empresa->nombre.' le informa que su factura de servicio de internet. Tiene como fecha de vencimiento: '.$date->format('d-m-Y').' Total a pagar '.$factura->totalAPI($empresa->id)->total;
                                    $bulk .= '{"numero": "57'.$numero.'", "sms": "'.$bulksms.'"},';
                                }else{
                                    $bulksms = 'Hola, '.$empresa->nombre.' le informa que su factura de internet ha sido generada. '.$empresa->slogan;
                                    $bulk .= '{"numero": "57'.$numero.'", "sms": "'.$bulksms.'"},';
                                }
    
                                //>>>>Posible aplicación de Prorrateo al total<<<<//
                                if($empresa->prorrateo == 1){
                                    $dias = $factura->diasCobradosProrrateo();
                                    //si es diferente de 30 es por que se cobraron menos dias y hay prorrateo
                                    if($dias != 30){
    
                                            DB::table('factura')->where('id',$factura->id)->update([
                                             'prorrateo_aplicado' => 1
                                            ]);
                                            //si no se nombra la variable en la primer guardada se genera una copia
    
                                        foreach($factura->itemsFactura as $item){
                                            //dividimos el precio del item en 30 para saber cuanto vamos a cobrar en total restando los dias
                                            $precioItemProrrateo = round($item->precio * $dias / 30, $empresa->precision);
                                            DB::table('items_factura')->where('id',$item->id)->update([
                                                'precio' => $precioItemProrrateo
                                                ]);
                                        }
                                    }
                                }
                                //>>>>Fin posible aplicación prorrateo al total<<<<//
                            }
                            }//validacion que no se creen dos el mismo dia
                        }
                    } //Comentando factura abierta del mes pasado
                    }
                    
                }
            }


             /* Enviar correo funcional */
             foreach($grupos_corte as $grupo_corte){
                $fechaInvoice = Carbon::now()->format('Y-m').'-'.substr(str_repeat(0, 2).$grupo_corte->fecha_factura, - 2);
                self::sendInvoices($fechaInvoice);
            }

            ## ENVIO SMS ##
            if($empresa->factura_sms_auto){
                $servicio = Integracion::where('empresa', 1)->where('tipo', 'SMS')->where('status', 1)->first();
                if($servicio){
                    if(isset($bulksms) && $bulksms != ''){
                        $mensaje = $bulksms;
                    }else{
                        $mensaje = 'Hola, '.$empresa->nombre.' le informa que su factura de internet ha sido generada. '.$empresa->slogan;
                    }
                    if($servicio->nombre == 'Hablame SMS'){
                        if($servicio->api_key && $servicio->user && $servicio->pass){
                            $curl = curl_init();
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

                            $response = curl_exec($curl);
                            $err = curl_error($curl);
                            curl_close($curl);
                        }
                    }elseif($servicio->nombre == 'SmsEasySms'){
                        if($servicio->user && $servicio->pass){
                            $post['to'] = $numeros;
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
                            $post['to'] = $numeros;
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

            ## ENVIO SMS ##

            ## ENVIO CORREO ##

            $facturas = Factura::where('facturacion_automatica', 1)->where('fecha', date('Y-m-d'))->where('correo_sendinblue', 0)->get();
            foreach ($facturas as $factura) {
                $empresa = Empresa::find($factura->empresa);
                $emails  = $factura->cliente()->email;
                $tipo    = 'Factura de venta original';
                view()->share(['title' => 'Imprimir Factura']);
                if ($factura) {
                    $items = ItemsFactura::where('factura',$factura->id)->get();
                    $itemscount=ItemsFactura::where('factura',$factura->id)->count();
                    $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
                    $resolucion = NumeracionFactura::where('empresa',$empresa->id)->latest()->first();
                    //---------------------------------------------//
                    if($factura->emitida == 1){
                        $impTotal = 0;
                        foreach ($factura->totalAPI($empresa->id)->imp as $totalImp){
                            if(isset($totalImp->total)){
                                $impTotal = $totalImp->total;
                            }
                        }

                        $CUFEvr = $factura->info_cufeAPI($factura->id, $impTotal, $empresa->id);
                        $infoEmpresa = Empresa::find($empresa->id);
                        $data['Empresa'] = $infoEmpresa->toArray();
                        $infoCliente = Contacto::find($factura->cliente);
                        $data['Cliente'] = $infoCliente->toArray();
                        /*..............................
                        Construcción del código qr a la factura
                        ................................*/
                        $impuesto = 0;
                        foreach ($factura->totalAPI($empresa->id)->imp as $key => $imp) {
                            if(isset($imp->total)){
                                $impuesto = $imp->total;
                            }
                        }

                        $codqr = "NumFac:" . $factura->codigo . "\n" .
                        "NitFac:"  . $data['Empresa']['nit']   . "\n" .
                        "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
                        "FecFac:" . Carbon::parse($factura->created_at)->format('Y-m-d') .  "\n" .
                        "HoraFactura" . Carbon::parse($factura->created_at)->format('H:i:s').'-05:00' . "\n" .
                        "ValorFactura:" .  number_format($factura->totalAPI($empresa->id)->subtotal, 2, '.', '') . "\n" .
                        "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
                        "ValorOtrosImpuestos:" .  0.00 . "\n" .
                        "ValorTotalFactura:" .  number_format($factura->totalAPI($empresa->id)->subtotal + $factura->impuestos_totalesFe(), 2, '.', '') . "\n" .
                        "CUFE:" . $CUFEvr;
                        /*..............................
                        Construcción del código qr a la factura
                        ................................*/
                        $pdf = PDF::loadView('pdf.electronicaAPI', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr','CUFEvr', 'empresa'))->save(public_path() . "/convertidor/" . $factura->codigo . ".pdf")->stream();
                    }else{
                        $pdf = PDF::loadView('pdf.electronicaAPI', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion', 'empresa'))->save(public_path() . "/convertidor/" . $factura->codigo . ".pdf")->stream();
                    }
                     //-----------------------------------------------//

                    $total = Funcion::ParsearAPI($factura->totalAPI($empresa->id)->total, $empresa->id);
                    $key = Hash::make(date("H:i:s"));
                    $toReplace = array('/', '$','.');
                    $key = str_replace($toReplace, "", $key);
                    $factura->nonkey = $key;
                    $factura->save();
                    $cliente = $factura->cliente()->nombre;
                    $tituloCorreo = $empresa->nombre.": Factura N° $factura->codigo";
                    $xmlPath = 'xml/empresa1/FV/FV-'.$factura->codigo.'.xml';
                }
                // envio de mensajes por whatsapp // 
                $plantilla = Plantilla::where('empresa', Auth::user()->empresa)->where('clasificacion', 'Facturacion')->where('tipo', 2)->where('status', 1)->get()->last();

                if($plantilla){
                    $mensaje = str_replace('{{ $company }}', Auth::user()->empresa()->nombre, $plantilla->contenido);
                    $mensaje = str_replace('{{ $name }}', ucfirst($factura->cliente()->nombre), $mensaje);
                    $mensaje = str_replace('{{ $factura->codigo }}', $factura->codigo, $mensaje);
                    $mensaje = str_replace('{{ $factura->parsear($factura->total()->total) }}', $factura->parsear($factura->total()->total), $mensaje);
                }else{
                    $mensaje = Auth::user()->empresa()->nombre.", le informa que su factura ha sido generada bajo el Nro. ".$factura->codigo.", por un monto de $".$factura->parsear($factura->total()->total);
                }

                $numero = str_replace('+','',$factura->cliente()->celular);
                $numero = str_replace(' ','',$numero);
                $numero = (substr($numero, 0, 2) == 57) ? $numero : '57'.$numero;


                $fields = [
                    "action"=>"sendFile",
                    "id"=>$numero."@c.us",
                    "file"=>public_path() . "/convertidor/" . $factura->codigo . ".pdf", // debe existir el archivo en la ubicacion que se indica aqui
                    "mime"=>"application/pdf",
                    "namefile"=>$factura->codigo,
                    "mensaje"=>$mensaje,
                    "cron"=>"true"
                ];

                $request = new Request();
                $request->merge($fields); 
                $controller = new CRMController();

                $instancia = DB::table("instancia")
                                        ->first();
                $response;
                if(!is_null($instancia) && !empty($instancia)){
                    if($instancia->status == "1"){
                        
                        $response = $controller->whatsappActions($request); //ENVIA EL MENSAJE
                        if($response->salida != 'error'){ 
                            $factura->whatsapp = 1;
                            $factura->correo_sendinblue = 1;
                            $factura->save();
                        }
                        
                    }else{
                        $factura->correo_sendinblue = 0;
                        $factura->response_sendinblue = $response;
                        $factura->save();
                    }
                }else{
                    $factura->correo_sendinblue = 0;
                        
        
                    $factura->response_sendinblue = $response;
                    $factura->save();
                }


            

            

            // fin de envio de mensajes por whatsapp
                unlink(public_path() . "/convertidor/" . $factura->codigo . ".pdf");
            }

            ## ENVIO CORREO ##

            

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

        if(request()->fechaCorte){
            $fecha = request()->fechaCorte;
        }

        $swGrupo = 1; //masivo
        // $grupos_corte = GrupoCorte::where('fecha_suspension', date('d') * 1)->where('hora_suspension','<=', date('H:i'))->where('hora_suspension_limit','>=', date('H:i'))->where('status', 1)->count();
        $grupos_corte = GrupoCorte::where('hora_suspension','<=', date('H:i'))->where('hora_suspension_limit','>=', date('H:i'))->where('status', 1)->where('fecha_suspension','!=',0)->get();
    
        if($grupos_corte->count() > 0){

            $grupos_corte_array = array();
            
            foreach($grupos_corte as $grupo){
                array_push($grupos_corte_array,$grupo->id);
            }
            
            //Estamos tomando la ultima factura siempre del cliente con el orderby y el groupby, despues analizamos si esta ultima ya vencio
            $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
                join('contracts as cs','cs.id','=','f.contrato_id')->
                select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'cs.state', 'f.contrato_id')->
                where('f.estatus',1)->
                whereIn('f.tipo', [1,2])->
                where('contactos.status',1)->
                where('cs.state','enabled')->
                whereIn('cs.grupo_corte',$grupos_corte_array)->
                where('cs.fecha_suspension', null)->
                where('cs.server_configuration_id','!=',null)->
                whereDate('f.vencimiento', '<=', now())->
                orderBy('f.id', 'desc')->
                take(20)->
                get(); 
                $swGrupo = 1; //masivo
                
        }else{
            $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'cs.state', 'f.contrato_id')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('contactos.status',1)->
            where('cs.state','enabled')->
            where('cs.fecha_suspension','!=', null)->
            take(20)->
            get(); 
            $swGrupo = 0; // personalizado
        }

            if($contactos){
            $empresa = Empresa::find(1);
            foreach ($contactos as $contacto) {
                
                $factura = Factura::find($contacto->factura);
                
                $ultimaFacturaRegistrada = Factura::
                where('cliente',$factura->cliente)
                ->where('contrato_id',$factura->contrato_id)
                ->orderBy('created_at', 'desc')
                ->value('id');
                                
                if($factura->id == $ultimaFacturaRegistrada){
 
                    if($factura->contrato_id != null){
                        $contrato = Contrato::find($factura->contrato_id);
                    }else{
                        $contrato = Contrato::find($contacto->contrato_id);
                    }
                    
                    $promesaExtendida = DB::table('promesa_pago')->where('factura', $contacto->factura)->where('vencimiento', '>=', $fecha)->count();
    
    
                    $crm = CRM::where('cliente', $contacto->id)->whereIn('estado', [0, 3])->delete();
                    $crm = new CRM();
                    $crm->cliente = $contacto->id;
                    $crm->factura = $contacto->factura;
                    $crm->estado = 0;
                    $crm->servidor = isset($contrato->server_configuration_id) ? $contrato->server_configuration_id : '';
                    $crm->grupo_corte = isset($contrato->grupo_corte) ? $contrato->grupo_corte : '';
                    $crm->save();
    
    
                    if($promesaExtendida > 0){
    
                                if($contrato->state != 'enabled'){
    
                                            if(isset($contrato->server_configuration_id)){
    
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
                                                
                                                $contrato->state = 'enabled';
    
                                                $contrato->update();
                                                $API->disconnect();
                                                }
                                    }
                                }
    
                        continue;
                    }
    
                
    
                    //por aca entra cuando estamos deshbilitando de un grupo de corte sus contratos.
                    if (($contrato && $swGrupo == 1) || ($contrato && $swGrupo == 0 && $contrato->fecha_suspension == date('d'))) {
    
                        //segundo filtro de validacion, validando por rango de fechas
                        $diasHabilesNocobro = 0;
                        if($contrato->tipo_nosuspension == 1 &&  $contrato->fecha_desde_nosuspension <= $fecha && $contrato->fecha_hasta_nosuspension >= $fecha){
                            $diasHabilesNocobro = 1;
                        }
                        
                        if($diasHabilesNocobro == 0){
                        if(isset($contrato->server_configuration_id)){
    
                            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();
                            $API = new RouterosAPI();
                            $API->port = $mikrotik->puerto_api;
    
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
                                    $i++;
                                }
                                $API->disconnect();
                            }
                            $contrato->state = 'disabled';
                            $contrato->save();
                        }
                    }
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

            if(request()->fechaCorte){
                return back();
            }

        }
    }

    public static function CortarPromesas(){
        $i=0;
        $fecha = date('Y-m-d');
        $hora = date('G:i');

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            join('promesa_pago as p', 'p.factura', '=', 'f.id')->
            select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'cs.state')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('f.promesa_pago', $fecha)->
            where('contactos.status',1)->
            where('cs.state','enabled')->
            where('p.hora_pago', $hora)->
            get();

        //dd($contactos);

        $empresa = Empresa::find(1);
        foreach ($contactos as $contacto) {
            $contrato = Contrato::where('client_id', $contacto->id)->first();

            //$crm = CRM::where('cliente', $contacto->id)->whereIn('estado', [0, 3])->delete();
            /*$crm = new CRM();
            $crm->cliente = $contacto->id;
            $crm->factura = $contacto->factura;
            $crm->servidor = $contrato->server_configuration_id;
            $crm->grupo_corte = $contrato->grupo_corte;
            $crm->save();*/

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

        if (file_exists("CortePromesas.txt")){
            $file = fopen("CortePromesas.txt", "a");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Promesa: ".date('Y-m-d').''. PHP_EOL);
            fputs($file, "Contratos Deshabilitados: ".$i.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }else{
            $file = fopen("CortePromesas.txt", "w");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Promesa: ".date('Y-m-d').''. PHP_EOL);
            fputs($file, "Contratos Deshabilitados: ".$i.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }
    }

    public static function CortarCRM(){
        $fecha = date('d-m-Y');
        $hora = date('G:i');
        $i = 0;
        $notificaciones = CRM::join('factura as f','f.id','=','crm.factura')->where('f.estatus',1)->where('crm.fecha_pago', $fecha)->where('crm.hora_pago', $hora)->select('f.id as factura', 'f.cliente', 'f.estatus', 'crm.id', 'crm.estado', 'crm.fecha_pago')->get();

        foreach($notificaciones as $notificacion){
            $notificacion->estado = 2;
            $notificacion->notificacion = 1;
            $notificacion->save();
            $i++;
        }

        if (file_exists("CortarCRM.txt")){
                $file = fopen("CortarCRM.txt", "a");
                fputs($file, "-----------------".PHP_EOL);
                fputs($file, "Fecha de Corte: ".date('Y-m-d').''. PHP_EOL);
                fputs($file, "CRM: ".$i.''. PHP_EOL);
                fputs($file, "-----------------".PHP_EOL);
                fclose($file);
            }else{
                $file = fopen("CortarCRM.txt", "w");
                fputs($file, "-----------------".PHP_EOL);
                fputs($file, "Fecha de Corte: ".date('Y-m-d').''. PHP_EOL);
                fputs($file, "CRM: ".$i.''. PHP_EOL);
                fputs($file, "-----------------".PHP_EOL);
                fclose($file);
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
                // Mail::to($empresa->email)->send($correo);
            }
        }
    }

    public static function PagoOportuno(){
        $empresa = Empresa::find(1);
        $i=0;
        $fecha = date('Y-m-d');
        $numeros = [];
        $bulk = '';
        $fail = 0;
        $succ = 0;

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            select('contactos.celular', 'f.vencimiento', 'contactos.id as idContacto')->
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

            if($empresa->sms_factura_generada){

                $facturaDetalle = Factura::where('cliente', $contacto->idContacto)->whereIn('tipo', [1,2])->where('pago_oportuno', $fecha)->get();

                foreach($facturaDetalle as $fd){

                        $nombreCliente = trim($fd->cliente()->nombre.' '.$fd->cliente()->apellidos());
                        $nombreEmpresa = $empresa->nombre;
                        $codigoFactura = $fd->codigo ?? $fd->nro;
                        $valorFactura =  $fd->totalAPI($empresa->id)->total;
                        $fechaVencimiento = date('d-m-Y', strtotime($fd->vencimiento));

                        $bulksms = $empresa->sms_factura_generada;
                        $bulksms = str_replace("{cliente}", $nombreCliente, $bulksms);
                        $bulksms = str_replace("{empresa}", $nombreEmpresa, $bulksms);
                        $bulksms = str_replace("{factura}", $codigoFactura, $bulksms);
                        $bulksms = str_replace("{valor}", $valorFactura, $bulksms);
                        $bulksms = str_replace("{vencimiento}", $fechaVencimiento, $bulksms);
                        
                        $bulk .= '{"numero": "57'.$numero.'", "sms": "'.$bulksms.'"},';
                }

            }else if($empresa->nombre == 'FIBRACONEXION S.A.S.' || $empresa->nit == '900822955' || $empresa->nombre == 'Almeidas Comunicaciones S.A.S' ||  $empresa->nit == '901044772'){
                $facturaDetalle = Factura::where('cliente', $contacto->idContacto)->whereIn('tipo', [1,2])->where('pago_oportuno', $fecha)->get();
                foreach($facturaDetalle as $fd){
                    $fullname = $fd->cliente()->nombre.' '.$fd->cliente()->apellidos();
                    $bulk .= '{"numero": "57'.$numero.'", "sms": "'.trim($fullname).'. '.$empresa->nombre.' le informa que su factura de servicio de internet. Tiene como fecha de vencimiento: '.date('d-m-Y', strtotime($fd->vencimiento)).' Total a pagar '.$fd->totalAPI($empresa->id)->total.'"},';
                }
            }else{
                $bulk .= '{"numero": "57'.$numero.'", "sms": "Estimado cliente, se le informa que su factura de internet ha sido generada. '.$empresa->slogan.'"},';
            }
        }

        $servicio = Integracion::where('empresa', 1)->where('tipo', 'SMS')->where('status', 1)->first();
        if($servicio){
            $mensaje = "Estimado cliente, su fecha limite de pago es el ".date('d-m-Y').", recuerde pagar su factura y evite la suspension del servicio. ".$empresa->slogan;

            if($servicio->nombre == 'Hablame SMS'){
                if($servicio->api_key && $servicio->user && $servicio->pass){
                    $curl = curl_init();
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

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                }
            }elseif($servicio->nombre == 'SmsEasySms'){
                if($servicio->user && $servicio->pass){
                    $post['to'] = $numeros;
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
                    $post['to'] = $numeros;
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
        $bulk = '';
        $fail = 0;
        $succ = 0;

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            select('contactos.celular', 'contactos.id as idContacto')->
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
            if($empresa->sms_factura_generada){
                $facturaDetalle = Factura::where('cliente', $contacto->idContacto)->whereIn('tipo', [1,2])->where('vencimiento', $fecha)->get();
                foreach($facturaDetalle as $fd){

                    $nombreCliente = trim($fd->cliente()->nombre.' '.$fd->cliente()->apellidos());
                    $nombreEmpresa = $empresa->nombre;
                    $codigoFactura = $fd->codigo ?? $fd->nro;
                    $valorFactura =  $fd->totalAPI($empresa->id)->total;
                    $fechaVencimiento = date('d-m-Y', strtotime($fd->vencimiento));

                    $bulksms = $empresa->sms_factura_generada;
                    $bulksms = str_replace("{cliente}", $nombreCliente, $bulksms);
                    $bulksms = str_replace("{empresa}", $nombreEmpresa, $bulksms);
                    $bulksms = str_replace("{factura}", $codigoFactura, $bulksms);
                    $bulksms = str_replace("{valor}", $valorFactura, $bulksms);
                    $bulksms = str_replace("{vencimiento}", $fechaVencimiento, $bulksms);
                    
                    $bulk .= '{"numero": "57'.$numero.'", "sms": "'.$bulksms.'"},';
                }
            }else if($empresa->nombre == 'FIBRACONEXION S.A.S.' || $empresa->nit == '900822955' || $empresa->nombre == 'Almeidas Comunicaciones S.A.S' ||  $empresa->nit == '901044772'){
                $facturaDetalle = Factura::where('cliente', $contacto->idContacto)->whereIn('tipo', [1,2])->where('vencimiento', $fecha)->get();
                foreach($facturaDetalle as $fd){
                    $fullname = $fd->cliente()->nombre.' '.$fd->cliente()->apellidos();
                    $bulk .= '{"numero": "57'.$numero.'", "sms": "'.trim($fullname).'. '.$empresa->nombre.' le informa que su factura de servicio de internet. Tiene como fecha de vencimiento: '.date('d-m-Y', strtotime($fd->vencimiento)).' Total a pagar '.$fd->totalAPI($empresa->id)->total.'"},';
                }
            }else{
                    $bulk .= '{"numero": "57'.$numero.'", "sms": "Estimado cliente, se le informa que su factura de internet ha sido generada. '.$empresa->slogan.'"},';
            }
        }

        $servicio = Integracion::where('empresa', 1)->where('tipo', 'SMS')->where('status', 1)->first();
        if($servicio){
            $mensaje = "Estimado cliente su servicio ha sido suspendido por falta de pago, por favor realice su pago para continuar disfrutando de su servicio. ".$empresa->slogan;
            if($servicio->nombre == 'Hablame SMS'){
                if($servicio->api_key && $servicio->user && $servicio->pass){
                    $curl = curl_init();
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

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);
                }
            }elseif($servicio->nombre == 'SmsEasySms'){
                if($servicio->user && $servicio->pass){
                    $post['to'] = $numeros;
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
                    $post['to'] = $numeros;
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
        $empresa = Empresa::find(1);
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

                    if ($precio >= $this->precisionAPI($factura->porpagarAPI($empresa->id), $empresa->id)) {
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

                    if($factura->estatus == 0){
                        # EJECUTAMOS COMANDOS EN MIKROTIK
                        $cliente = Contacto::where('id', $factura->cliente)->first();
                        $contrato = Contrato::where('client_id', $cliente->id)->first();
                        $res = DB::table('contracts')->where('client_id', $cliente->id)->update(["state" => 'enabled']);

                        $asignacion = Producto::where('contrato', $contrato->id)->where('venta', 1)->where('status', 2)->where('cuotas_pendientes', '>', 0)->get()->last();

                        if ($asignacion) {
                            $cuotas_pendientes = $asignacion->cuotas_pendientes -= 1;
                            $asignacion->cuotas_pendientes = $cuotas_pendientes;
                            if ($cuotas_pendientes == 0) {
                                $asignacion->status = 1;
                            }
                            $asignacion->save();
                        }

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

                            if($empresa->sms_pago && isset($factura)){
                                $nombreCliente = $factura->cliente()->nombre.' '.$factura->cliente()->apellidos();
                                $nombreEmpresa = $empresa->nombre;
                                $codigoFactura = $factura->codigo ?? $factura->nro;
                                $valorFactura =  $factura->totalAPI($empresa->id)->total;
                                $fechaVencimiento = date('d-m-Y', strtotime($factura->vencimiento));
                                $pagoRecibido = Funcion::ParsearAPI($precio, $empresa->id);

                                $bulksms = $empresa->sms_pago;
                                $bulksms = str_replace("{cliente}", $nombreCliente, $bulksms);
                                $bulksms = str_replace("{empresa}", $nombreEmpresa, $bulksms);
                                $bulksms = str_replace("{factura}", $codigoFactura, $bulksms);
                                $bulksms = str_replace("{valor}", $valorFactura, $bulksms);
                                $bulksms = str_replace("{pagado}", $pagoRecibido, $bulksms);
                                $bulksms = str_replace("{vencimiento}", $fechaVencimiento, $bulksms);

                                $mensaje =  $bulksms;
                            }else{
                                $mensaje = "Estimado Cliente, le informamos que hemos recibido el pago de su factura por valor de ".Funcion::ParsearAPI($precio, $empresa->id)." gracias por preferirnos. ".$empresa->slogan;
                            }

                            if($servicio->nombre == 'Hablame SMS'){
                                if($servicio->api_key && $servicio->user && $servicio->pass){
                                    $post['numero'] = $numero;
                                    $post['sms'] = $mensaje;

                                    $curl = curl_init();
                                    curl_setopt_array($curl, array(
                                        CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/marketing/bulk',
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
        $empresa = Empresa::find(1);
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

                    if ($precio >= $this->precisionAPI($factura->porpagarAPI($empresa->id), $empresa->id)) {
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

                    if($factura->estatus == 0){
                        # EJECUTAMOS COMANDOS EN MIKROTIK
                        $cliente = Contacto::where('id', $factura->cliente)->first();
                        $contrato = Contrato::where('client_id', $cliente->id)->first();
                        $res = DB::table('contracts')->where('client_id', $cliente->id)->update(["state" => 'enabled']);

                        $asignacion = Producto::where('contrato', $contrato->id)->where('venta', 1)->where('status', 2)->where('cuotas_pendientes', '>', 0)->get()->last();

                        if ($asignacion) {
                            $cuotas_pendientes = $asignacion->cuotas_pendientes -= 1;
                            $asignacion->cuotas_pendientes = $cuotas_pendientes;
                            if ($cuotas_pendientes == 0) {
                                $asignacion->status = 1;
                            }
                            $asignacion->save();
                        }

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

                            if($empresa->sms_pago && isset($factura)){
                                $nombreCliente = $factura->cliente()->nombre.' '.$factura->cliente()->apellidos();
                                $nombreEmpresa = $empresa->nombre;
                                $codigoFactura = $factura->codigo ?? $factura->nro;
                                $valorFactura =  $factura->totalAPI($empresa->id)->total;
                                $fechaVencimiento = date('d-m-Y', strtotime($factura->vencimiento));
                                $pagoRecibido = Funcion::ParsearAPI($precio, $empresa->id);

                                $bulksms = $empresa->sms_pago;
                                $bulksms = str_replace("{cliente}", $nombreCliente, $bulksms);
                                $bulksms = str_replace("{empresa}", $nombreEmpresa, $bulksms);
                                $bulksms = str_replace("{factura}", $codigoFactura, $bulksms);
                                $bulksms = str_replace("{valor}", $valorFactura, $bulksms);
                                $bulksms = str_replace("{pagado}", $pagoRecibido, $bulksms);
                                $bulksms = str_replace("{vencimiento}", $fechaVencimiento, $bulksms);

                                $mensaje =  $bulksms;
                            }else{
                                 $mensaje = "Estimado Cliente, le informamos que hemos recibido el pago de su factura por valor de ".Funcion::ParsearAPI($precio, $empresa->id)." gracias por preferirnos. ".$empresa->slogan;
                            }
                            if($servicio->nombre == 'Hablame SMS'){
                                if($servicio->api_key && $servicio->user && $servicio->pass){
                                    $post['numero'] = $numero;
                                    $post['sms'] = $mensaje;

                                    $curl = curl_init();
                                    curl_setopt_array($curl, array(
                                        CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/marketing/bulk',
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
        $empresa = Empresa::find(1);
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
                $precio = ($this->precisionAPI($request->transaction_value, $empresa->id) > $factura->porpagarAPI($empresa->id)) ? $factura->porpagarAPI($empresa->id) : $this->precisionAPI($request->transaction_value, $empresa->id);
                //$precio         = $this->precisionAPI($request->transaction_value, $empresa->id);
                //$precio         = $this->precisionAPI($factura->totalAPI($empresa->id)->total, $empresa->id);

                $items          = new IngresosFactura;
                $items->ingreso = $ingreso->id;
                $items->factura = $factura->id;
                $items->pagado  = $factura->pagado();
                $items->pago    = $precio;

                if ($precio >= $this->precisionAPI($factura->porpagarAPI($empresa->id), $empresa->id)) {
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

                if($factura->estatus == 0){
                    # EJECUTAMOS COMANDOS EN MIKROTIK
                    $cliente = Contacto::where('id', $factura->cliente)->first();
                    $contrato = Contrato::where('client_id', $cliente->id)->first();
                    $res = DB::table('contracts')->where('client_id', $cliente->id)->update(["state" => 'enabled']);

                    $asignacion = Producto::where('contrato', $contrato->id)->where('venta', 1)->where('status', 2)->where('cuotas_pendientes', '>', 0)->get()->last();

                    if ($asignacion) {
                        $cuotas_pendientes = $asignacion->cuotas_pendientes -= 1;
                        $asignacion->cuotas_pendientes = $cuotas_pendientes;
                        if ($cuotas_pendientes == 0) {
                            $asignacion->status = 1;
                        }
                        $asignacion->save();
                    }

                    # API MK
                    if($contrato){
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
                    }

                    # ENVÍO SMS
                    $servicio = Integracion::where('empresa', $empresa->id)->where('tipo', 'SMS')->where('status', 1)->first();
                    if($servicio){
                        $numero = str_replace('+','',$cliente->celular);
                        $numero = str_replace(' ','',$numero);

                        if($empresa->sms_pago && isset($factura)){
                            $nombreCliente = $factura->cliente()->nombre.' '.$factura->cliente()->apellidos();
                            $nombreEmpresa = $empresa->nombre;
                            $codigoFactura = $factura->codigo ?? $factura->nro;
                            $valorFactura =  $factura->totalAPI($empresa->id)->total;
                            $fechaVencimiento = date('d-m-Y', strtotime($factura->vencimiento));
                            $pagoRecibido = Funcion::ParsearAPI($precio, $empresa->id);

                            $bulksms = $empresa->sms_pago;
                            $bulksms = str_replace("{cliente}", $nombreCliente, $bulksms);
                            $bulksms = str_replace("{empresa}", $nombreEmpresa, $bulksms);
                            $bulksms = str_replace("{factura}", $codigoFactura, $bulksms);
                            $bulksms = str_replace("{valor}", $valorFactura, $bulksms);
                            $bulksms = str_replace("{pagado}", $pagoRecibido, $bulksms);
                            $bulksms = str_replace("{vencimiento}", $fechaVencimiento, $bulksms);

                            $mensaje =  $bulksms;
                        }else{
                            $mensaje = "Estimado Cliente, le informamos que hemos recibido el pago de su factura por valor de ".Funcion::ParsearAPI($precio, $empresa->id)." gracias por preferirnos. ".$empresa->slogan;
                        }

                        if($servicio->nombre == 'Hablame SMS'){
                            if($servicio->api_key && $servicio->user && $servicio->pass){
                                $post['numero'] = $numero;
                                $post['sms'] = $mensaje;

                                $curl = curl_init();
                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/marketing/bulk',
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
            return response('Factura ya pagada', 200);
        }
        return response('Factura ya pagada', 200);
    }

    public static function SMSFacturas($fecha){
        $numeros = [];
        $bulk = '';
        $empresa = Empresa::find(1);
        $facturas = Factura::where('fecha', $fecha)->where('estatus', 1)->get();

        foreach ($facturas as $factura) {
            if($factura->cliente()->celular){
                $numero = str_replace('+','',$factura->cliente()->celular);
                $numero = str_replace(' ','',$numero);
                array_push($numeros, '57'.$numero);

                if($empresa->sms_factura_generada){

                    $nombreCliente = trim($factura->cliente()->nombre.' '.$factura->cliente()->apellidos());
                    $nombreEmpresa = $empresa->nombre;
                    $codigoFactura = $factura->codigo ?? $factura->nro;
                    $valorFactura =  $factura->totalAPI($empresa->id)->total;
                    $fechaVencimiento = date('d-m-Y', strtotime($factura->vencimiento));

                    $bulksms = $empresa->sms_factura_generada;
                    $bulksms = str_replace("{cliente}", $nombreCliente, $bulksms);
                    $bulksms = str_replace("{empresa}", $nombreEmpresa, $bulksms);
                    $bulksms = str_replace("{factura}", $codigoFactura, $bulksms);
                    $bulksms = str_replace("{valor}", $valorFactura, $bulksms);
                    $bulksms = str_replace("{vencimiento}", $fechaVencimiento, $bulksms);
                    
                    $bulk .= '{"numero": "57'.$numero.'", "sms": "'.$bulksms.'"},';

                }else if($empresa->nombre == 'FIBRACONEXION S.A.S.' || $empresa->nit == '900822955' || $empresa->nombre == 'Almeidas Comunicaciones S.A.S' ||  $empresa->nit == '901044772'){
                    $fullname = $factura->cliente()->nombre.' '.$factura->cliente()->apellidos();
                    $bulk .= '{"numero": "57'.$numero.'", "sms": "'.trim($fullname).'. '.$empresa->nombre.' le informa que su factura de servicio de internet. Tiene como fecha de vencimiento: '.date('d-m-Y', strtotime($factura->vencimiento)).' Total a pagar '.$factura->totalAPI($empresa->id)->total.'"},';
                }else{
                    $bulk .= '{"numero": "57'.$numero.'", "sms": "Hola, '.$empresa->nombre.' le informa que su factura de internet ha sido generada. '.$empresa->slogan.'"},';
                }
            }
        }

        $servicio = Integracion::where('empresa', 1)->where('tipo', 'SMS')->where('status', 1)->first();
        if($servicio){
            $mensaje = "Estimado cliente, se le informa que su factura de internet ha sido generada. ".$empresa->slogan;
            if($servicio->nombre == 'Hablame SMS'){
                if($servicio->api_key && $servicio->user && $servicio->pass){
                    $curl = curl_init();
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

                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);

                    isset($response) ? dd($response) : dd($err);
                }
            }elseif($servicio->nombre == 'SmsEasySms'){
                if($servicio->user && $servicio->pass){
                    $post['to'] = $numeros;
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
                    $post['to'] = $numeros;
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

    public static function DeshabilitarContratosMK($mk){
        $i=0;
        $mikrotik = Mikrotik::find($mk);
        $empresa = Empresa::find(1);

        if($mikrotik){
            $contratos = Contrato::where('server_configuration_id', $mikrotik->id)->where('state', 'disabled')->where('status', 1)->where('disabled', 0)->take(25)->get();

            //dd($contratos);

            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                foreach ($contratos as $contrato) {
                    if($contrato->state == 'disabled'){
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
                            $i++;
                            $contrato->disabled = 1;
                            $contrato->save();
                        }
                    }
                }
            }
            $API->disconnect();

            dd(Contrato::where('server_configuration_id', $mikrotik->id)->where('state', 'disabled')->where('status', 1)->where('disabled', 0)->count());
        }
    }

    public function deleteFactura(){ 
        
        //--------- enviar facturas por wpp segun una fecha ------- ///
        
            // $facturas = Factura::
            // join('contracts as c','c.id','=','factura.contrato_id')
            // ->where('factura.observaciones','LIKE','%Facturación Automática -%')->where('factura.fecha',"2023-07-24")
            // ->where('c.grupo_corte',1)
            // ->select('factura.*')
            // ->limit(1)->get();
            
            // foreach($facturas as $factura){
                
            //     view()->share(['title' => 'Imprimir Factura']);
            //     $empresa = Empresa::find($factura->empresa);
            //     $items = ItemsFactura::where('factura',$factura->id)->get();
            //     $itemscount=ItemsFactura::where('factura',$factura->id)->count();
            //     $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
            //     $resolucion = NumeracionFactura::where('empresa',$factura->empresa)->latest()->first();
                
            //     $tipo = $factura->tipo;
                    
            //     if($factura->emitida == 1){
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
            //             $pdf = PDF::loadView('pdf.electronicaAPI', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr','CUFEvr', 'empresa'))->save(public_path() . "/convertidor/" . $factura->codigo . ".pdf")->stream();
            //         }else{
            //             $pdf = PDF::loadView('pdf.electronicaAPI', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion', 'empresa'))->save(public_path() . "/convertidor/" . $factura->codigo . ".pdf")->stream();
            //         }
                
            //     // envio de mensajes por whatsapp // 
            //     $plantilla = Plantilla::where('empresa', Auth::user()->empresa)->where('clasificacion', 'Facturacion')->where('tipo', 2)->where('status', 1)->get()->last();

            //     if($plantilla){
            //         $mensaje = str_replace('{{ $company }}', Auth::user()->empresa()->nombre, $plantilla->contenido);
            //         $mensaje = str_replace('{{ $name }}', ucfirst($factura->cliente()->nombre), $mensaje);
            //         $mensaje = str_replace('{{ $factura->codigo }}', $factura->codigo, $mensaje);
            //         $mensaje = str_replace('{{ $factura->parsear($factura->total()->total) }}', $factura->parsear($factura->total()->total), $mensaje);
            //     }else{
            //         $mensaje = Auth::user()->empresa()->nombre.", le informa que su factura ha sido generada bajo el Nro. ".$factura->codigo.", por un monto de $".$factura->parsear($factura->total()->total);
            //     }

            //     $numero = str_replace('+','',$factura->cliente()->celular);
            //     $numero = str_replace(' ','',$numero);
            //     $numero = (substr($numero, 0, 2) == 57) ? $numero : '57'.$numero;
                
                
            //     // return $mensaje;

            //     $fields = [
            //         "action"=>"sendFile",
            //         "id"=> $numero."@c.us",
            //         "file"=>public_path() . "/convertidor/" . $factura->codigo . ".pdf", // debe existir el archivo en la ubicacion que se indica aqui
            //         "mime"=>"application/pdf",
            //         "namefile"=>$factura->codigo ,
            //         "message"=>$mensaje,
            //         "cron"=>"true"
            //     ];

            //     $request = new Request();
            //     $request->merge($fields); 
            //     $controller = new CRMController();

            //     $instancia = DB::table("instancia")
            //                             ->first();
            //     $response;
            //     if(!is_null($instancia) && !empty($instancia)){
            //         if($instancia->status == "1"){
            //             $response = $controller->whatsappActions($request); //ENVIA EL MENSAJE
                        
            //             $factura->correo_sendinblue = 1;
                        
        
            //             $factura->response_sendinblue = $response;
            //             $factura->save();
            //         }else{
            //             $factura->correo_sendinblue = 0;
                        
        
            //             $factura->response_sendinblue = $response;
            //             $factura->save();
            //         }
            //     }else{
            //         $factura->correo_sendinblue = 0;
                        
        
            //         $factura->response_sendinblue = $response;
            //         $factura->save();
            //     }

            //     unlink(public_path() . "/convertidor/" . $factura->codigo . ".pdf");
            // }
        
        // --------- fin enviar fcturas por wpp segun fecha ---------- //
        
        
        
        //facturas creadas automaticamente cancelamos sus contratos o eliminamos
        // $facturas = Factura::
        // join('contracts as c','c.id','=','factura.contrato_id')
        // ->where('factura.observaciones','LIKE','%Facturación Automática -%')->where('factura.fecha',"2023-05-02")
        // ->where('c.grupo_corte',2)
        // ->select('factura.*')
        // ->get();  

        //HABILITAR CONTRATOS DESHABILITADOS ERRONEAMENTE//
        // $contratos = Contrato::where('grupo_corte',1)->where('state','disabled')->where('updated_at','>=','2022-10-06 00:00:00')->where('updated_at','<=','2022-10-06 06:00:00')->get();
        // $i = 0;
        // foreach($contratos as $contrato){
        //         $contrato->state = "enabled";
        //         $contrato->status = 1;
        //         $contrato->save();
        //         $i++;
        // }
        // return "Se habilitaron " . $i . " contratos";
        //HABILITAR CONTRATOS DESHABILITADOS ERRONEAMENTE//
        
        // //habilitar contratos por factura
        // foreach($facturas as $factura){
        //     $contrato = Contrato::where('id',$factura->contrato_id)->first();
        //     if($contrato){
        //         $contrato->state = "enabled";
        //         $contrato->status = 1;
        //         $contrato->save();
        //     }
          
        // }
        
        // return "ok contratos habilitados";
        
        // $eliminadas = 0;
        // foreach($facturas as $f){
            
        //     if($f->pagado() == 0){
        //     $itemsFactura = ItemsFactura::where('factura',$f->id)->delete();
        //     $eliminadas++;
        //     $f->delete();
        //     }
        // }   
        
        
        // return "Se eliminaron un total de:" . $eliminadas . " facturas correctamente";
        
        //comprobar en bd
        //SELECT factura.* FROM `factura` WHERE factura.observaciones LIKE "%Facturación Automática - Corte%" AND factura.fecha = "2022-08-25"
        
        // SOPORTE AGREGAR ITEMS A FACTURAS SIN ITEMS MASIVAMENTE  POR UN GRUPO DE CORTE//
        // $facturas = Factura::join('contracts as c','c.id','=','factura.contrato_id')
        // ->select('factura.*','c.grupo_corte','c.plan_id','c.servicio_tv','c.descuento')
        // ->where('factura.fecha','2022-12-20')
        // ->get();
        
        // $cont = 0;
        // foreach($facturas as $factura){
            
            
            //#SOPORTE FECHA DE VENCIMIENTO MAL INGRESADA CAMBIO MASIVO //
            // if(Carbon::parse($factura->vencimiento)->format('Y') == "2022"){
            // $cont=$cont+1;
            //  $dia = Carbon::parse($factura->vencimiento)->format('d');
            //  $mes = Carbon::parse($factura->vencimiento)->format('m');
            //  $year = "2023";
            //  $fechaCompleta = $year . "-" . $mes . "-" . $dia;
            //  $factura->vencimiento = $fechaCompleta;
            //  $factura->suspension = $fechaCompleta;
            //  $factura->save();
            // }
            //#SOPORTE FECHA DE VENCIMIENTO MAL INGRESADA CAMBIO MASIVO //
            
            // if($factura->total()->total == 0){
            //     $cont=$cont+1;
            //     if(!DB::table('items_factura')->where('factura',$factura->id)->first()){
            //         $factura->estatus = 1;
            //         $factura->save();
            //         if($factura->plan_id){
            //                     $plan = PlanesVelocidad::find($factura->plan_id);
            //                     $item = Inventario::find($plan->item);

            //                     $item_reg = new ItemsFactura;
            //                     $item_reg->factura     = $factura->id;
            //                     $item_reg->producto    = $item->id;
            //                     $item_reg->ref         = $item->ref;
            //                     $item_reg->precio      = $item->precio;
            //                     $item_reg->descripcion = $plan->name;
            //                     $item_reg->id_impuesto = $item->id_impuesto;
            //                     $item_reg->impuesto    = $item->impuesto;
            //                     $item_reg->cant        = 1;
            //                     $item_reg->desc        = $factura->descuento;
            //                     $item_reg->save();
            //                 }

            //         //         ## Se carga el item a la factura (Plan de Televisión) ##

            //                 if($factura->servicio_tv){
            //                     $item = Inventario::find($factura->servicio_tv);
            //                     $item_reg = new ItemsFactura;
            //                     $item_reg->factura     = $factura->id;
            //                     $item_reg->producto    = $item->id;
            //                     $item_reg->ref         = $item->ref;
            //                     $item_reg->precio      = $item->precio;
            //                     $item_reg->descripcion = $item->producto;
            //                     $item_reg->id_impuesto = $item->id_impuesto;
            //                     $item_reg->impuesto    = $item->impuesto;
            //                     $item_reg->cant        = 1;
            //                     $item_reg->desc        = $factura->descuento;
            //                     $item_reg->save();
            //                 }
            //     }
            // }
        // }
        // return "ok productos actualizados" . $cont;
        //END SOPORTE AGREGAR ITEMS A FACTURAS SIN ITEMS MASIVAMENTE  POR UN GRUPO DE CORTE//

    }


    public function aplicateProrrateo(){

        $facturas = Factura::where('observaciones','LIKE','%Facturación Automática - Corte%')
        ->where('fecha',"2022-09-01")
        ->where('estatus',1)->get();

        if(Auth::user()->empresaObj->prorrateo == 1){

            foreach($facturas as $factura){
                $dias = $factura->diasCobradosProrrateo();
                //si es diferente de 30 es por que se cobraron menos dias y hay prorrateo
                if($dias != 30){
                    if(isset($factura->prorrateo_aplicado)){
                        $factura->prorrateo_aplicado = 1;
                        $factura->save();
                    }
        
                    foreach($factura->itemsFactura as $item){
                          
                        //dividimos el precio del item en 30 para saber cuanto vamos a cobrar en total restando los dias
                        $precioItemProrrateo = $this->precision($item->precio * $dias / 30); 
                        $item->precio = $precioItemProrrateo;
                        $item->save();

                    }
                }
            }

        }
    }

    public static function disabledAndCRM($ip){
        $i=0;$j=0;$anuladas=0;$ingreso=0;

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'cs.state', 'cs.id as contrato_id', 'f.contrato_id')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('contactos.status',1)->
            where('cs.ip',$ip)->
            get();

        if ($contactos) {
            foreach($contactos as $item){
                $contrato = Contrato::find($item->contrato_id);
                $contrato->state = 'disabled';
                $contrato->save();

                if($j==0){
                    $crm = CRM::where('cliente', $item->id)->whereIn('estado', [0, 3])->delete();
                    $crm = new CRM();
                    $crm->cliente = $item->id;
                    $crm->factura = $item->factura;
                    $crm->servidor = isset($contrato->server_configuration_id) ? $contrato->server_configuration_id : '';
                    $crm->grupo_corte = isset($contrato->grupo_corte) ? $contrato->grupo_corte : '';
                    $crm->save();
                    $ingreso++;
                    $j++;
                }else{
                    $factura = Factura::find($item->factura);
                    $factura->estatus = 2;
                    $factura->save();
                    $anuladas++;
                }
            }
        }
        return 'Anuladas: '.$anuladas.' - Ingresados a CRM: '.$ingreso;
    }

    public static function sendInvoices($date){
        $facturas = Factura::where('facturacion_automatica', 1)->where('fecha', $date)->where('correo_sendinblue', 0)->get();
        //dd($facturas);
        foreach ($facturas as $factura) {
            $empresa = Empresa::find($factura->empresa);
            $emails  = $factura->cliente()->email;
            $tipo    = 'Factura de venta original';
            view()->share(['title' => 'Imprimir Factura']);
            if ($factura) {
                $items = ItemsFactura::where('factura',$factura->id)->get();
                $itemscount=ItemsFactura::where('factura',$factura->id)->count();
                $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
                $resolucion = NumeracionFactura::where('empresa',$empresa->id)->latest()->first();
                //---------------------------------------------//
                if($factura->emitida == 1){
                    $impTotal = 0;
                    foreach ($factura->totalAPI($empresa->id)->imp as $totalImp){
                        if(isset($totalImp->total)){
                            $impTotal = $totalImp->total;
                        }
                    }

                    $CUFEvr = $factura->info_cufeAPI($factura->id, $impTotal, $empresa->id);
                    $infoEmpresa = Empresa::find($empresa->id);
                    $data['Empresa'] = $infoEmpresa->toArray();
                    $infoCliente = Contacto::find($factura->cliente);
                    $data['Cliente'] = $infoCliente->toArray();
                    /*..............................
                    Construcción del código qr a la factura
                    ................................*/
                    $impuesto = 0;
                    foreach ($factura->totalAPI($empresa->id)->imp as $key => $imp) {
                        if(isset($imp->total)){
                            $impuesto = $imp->total;
                        }
                    }

                    $codqr = "NumFac:" . $factura->codigo . "\n" .
                    "NitFac:"  . $data['Empresa']['nit']   . "\n" .
                    "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
                    "FecFac:" . Carbon::parse($factura->created_at)->format('Y-m-d') .  "\n" .
                    "HoraFactura" . Carbon::parse($factura->created_at)->format('H:i:s').'-05:00' . "\n" .
                    "ValorFactura:" .  number_format($factura->totalAPI($empresa->id)->subtotal, 2, '.', '') . "\n" .
                    "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
                    "ValorOtrosImpuestos:" .  0.00 . "\n" .
                    "ValorTotalFactura:" .  number_format($factura->totalAPI($empresa->id)->subtotal + $factura->impuestos_totalesFe(), 2, '.', '') . "\n" .
                    "CUFE:" . $CUFEvr;
                    /*..............................
                    Construcción del código qr a la factura
                    ................................*/
                    //$pdf = PDF::loadView('pdf.electronicaAPI', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr','CUFEvr', 'empresa'))->save(public_path() . "/convertidor/" . $factura->codigo . ".pdf")->stream();
                }else{
                    //$pdf = PDF::loadView('pdf.electronicaAPI', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion', 'empresa'))->save(public_path() . "/convertidor/" . $factura->codigo . ".pdf")->stream();
                }
                //-----------------------------------------------//

                $total = Funcion::ParsearAPI($factura->totalAPI($empresa->id)->total, $empresa->id);
                $key = Hash::make(date("H:i:s"));
                $toReplace = array('/', '$','.');
                $key = str_replace($toReplace, "", $key);
                $factura->nonkey = $key;
                $factura->save();
                $cliente = $factura->cliente()->nombre;
                $tituloCorreo = $empresa->nombre.": Factura N° $factura->codigo";
                $xmlPath = 'xml/empresa1/FV/FV-'.$factura->codigo.'.xml';
            }

            $html = view('emails.emailSendInBlue', [
                'factura' => $factura,
                'total'   => $total,
                'cliente' => $cliente,
                'empresa' => $empresa,
            ]);

            $fields = [
                'to' => [
                    [
                        'email' => $emails,
                        'name' => $cliente.' '.$factura->cliente()->apellidos()
                    ]
                ],
                'sender' => [
                    'name' => $empresa->nombre,
                    'email' => $empresa->email
                ],
                'subject' => $tituloCorreo,
                'htmlContent' => '<html>'.$html.'</html>',
                
            ];

            $fields = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sendinblue.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'api-key: '.$empresa->api_key_mail.'', 'content-type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);

            $response = json_decode($response, true);

            if(isset($response['messageId'])){
                $factura->correo_sendinblue = 1;
            }
            
            $factura->response_sendinblue = $response;
            $factura->save();
            //unlink(public_path() . "/convertidor/" . $factura->codigo . ".pdf");
        }

        return $facturas;
    }
}