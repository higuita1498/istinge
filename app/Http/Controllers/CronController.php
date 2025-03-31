<?php

namespace App\Http\Controllers;

use StdClass;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Funcion;
use Illuminate\Support\Facades\Hash;

use App\Model\Ingresos\Factura;
use App\NumeracionFactura;
use App\Model\Ingresos\ItemsFactura;
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
use App\Producto;
use Auth;
use App\Services\EmisionesService;

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;

use App\Numeracion;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosFactura;
use App\Banco;
use App\Instance;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Gastos\NotaDedito;
use App\Model\Ingresos\NotaCredito;
use App\Model\Nomina\Nomina;
use App\Movimiento;
use App\MovimientoLOG;
use App\Services\WapiService;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    public static function precisionAPI($valor, $id){
        $empresa = Empresa::find($id);
        return round($valor, $empresa->precision);
    }

    public static function up_transaccion_($modulo, $id, $banco, $contacto, $tipo, $saldo, $fecha, $descripcion, $generoSaldoFavor=null,$empresa=null){
        $movimiento=new Movimiento;
        $probableMovimiento = Movimiento::where('modulo', 7)->where('id_modulo', $id)->where('estatus',1)->first();

        //Caso1: Cuando cambiamos de un saldo a favor a un pago normal, necesitamos buscarlo por el modulo.
        $regis=Movimiento::where('modulo', $modulo)->where('id_modulo', $id)->where('estatus',1)->first();

        if(!$regis && $probableMovimiento && $generoSaldoFavor == null){
            $movimiento=$probableMovimiento;
        }

        if ($regis) {
            $movimiento=$regis;
        }

        //Caso1: Se esta pasando de un saldo a favor a un movimiento normal, se devuelve el dinero al cliente de saldo a favor.
        if($probableMovimiento && $probableMovimiento->tipo == 2 && $modulo != 7){
            $conta = Contacto::Find($probableMovimiento->contacto);
            $conta->saldo_favor = $conta->saldo_favor + $probableMovimiento->saldo;
            $conta->save();
        }

        if($modulo == 7){
            $banco = Banco::where('empresa',$empresa)->where('nombre','like','Saldos a favor')->first()->id;
        }

        $movimiento->empresa=$empresa;
        $movimiento->banco=$banco;
        $movimiento->contacto=$contacto;
        $movimiento->tipo=$tipo;
        $movimiento->saldo=$saldo;
        $movimiento->fecha=$fecha;
        $movimiento->modulo=$modulo;
        $movimiento->id_modulo=$id;
        $movimiento->descripcion=$id . " " . $descripcion;
        $movimiento->save();
    }

    public static function CrearFactura(){

        ini_set('max_execution_time', 500);
        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

        $empresa = Empresa::find(1);

        if($empresa->factura_auto == 1){
            $i=0;
            $date = getdate()['mday'] * 1;
            $numeros = [];
            $bulk = '';
            $horaActual = date('H:i');

            $grupos_corte = GrupoCorte::
            where('fecha_factura', $date)
            ->where('hora_creacion_factura','<=',$horaActual)
            ->where('status', 1)->get();

            $fecha = Carbon::now()->format('Y-m-d');

            foreach($grupos_corte as $grupo_corte){
                $contratos = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->
                join('empresas as e', 'e.id', '=', 'contracts.empresa')
                ->select('contracts.id', 'contracts.iva_factura', 'contracts.public_id', 'c.id as cliente',
                'contracts.state', 'contracts.fecha_corte', 'contracts.fecha_suspension', 'contracts.facturacion',
                'contracts.plan_id', 'contracts.descuento', 'c.nombre', 'c.nit', 'c.celular', 'c.telefono1',
                'c.saldo_favor','contracts.created_at','contracts.fact_primer_mes',
                'e.terminos_cond', 'e.notas_fact', 'contracts.servicio_tv', 'contracts.factura_individual','contracts.nro')
                ->where('contracts.grupo_corte',$grupo_corte->id)->
                where('contracts.status',1)->
                // whereIn('contracts.client_id',[645])->
                // where('c.saldo_favor','>',80000)->//rc
                where('contracts.state','enabled')
                ->get();

                $num = Factura::where('empresa',1)->orderby('id','asc')->get()->last();
                if($num){
                    $numero = $num->nro;
                }else{
                    $numero = 0;
                }

                //Calculo fecha pago oportuno.
                $y = Carbon::now()->format('Y');
                $m = Carbon::now()->format('m');
                $d = substr(str_repeat(0, 2).$grupo_corte->fecha_pago, - 2);
                if($d == 0){
                    $d = 30;
                }

                if($grupo_corte->fecha_factura > $grupo_corte->fecha_pago && $m!=12){
                    $m=$m+1;
                }

                if($m == 12 && $grupo_corte->fecha_factura > $grupo_corte->fecha_pago){
                    $y = $y+1;
                    $m = 01;
                }
                $date_pagooportuno = $y . "-" . $m . "-" . $d;
                //Fin calculo fecha de pago oportuno

                //calculo fecha suspension
                $y = Carbon::now()->format('Y');
                $m = Carbon::now()->format('m');
                $ds = substr(str_repeat(0, 2).$grupo_corte->fecha_suspension, - 2);
                $da = Carbon::now()->format('d')*1;
                 if($da > $grupo_corte->fecha_suspension && $m!=12){
                    $m=$m+1;
                }

                if($m == 12){
                    if($da > $grupo_corte->fecha_suspension){

                        if(Carbon::now()->format('m') != 11){
                            $m = 01;
                            $y = $y+1;
                        }
                    }
                }
                $date_suspension = $y . "-" . $m . "-" . $ds;
                //Fin calculo fecha suspension

                foreach ($contratos as $contrato) {

                    //validacion primer factura del contrato
                    $creacion_contrato = Carbon::parse($contrato->created_at);
                    $dia_creacion_contrato = $creacion_contrato->day;
                    $dia_creacion_factura = $grupo_corte->fecha_factura;

                    // Determinar el mes y año para la primera factura
                    if ($dia_creacion_contrato <= $dia_creacion_factura) {
                        // Si el contrato se creó antes o el mismo día del corte, la factura es en el mismo mes
                        $primer_fecha_factura = $creacion_contrato->copy()->day($dia_creacion_factura);
                        $primer_fecha_factura = Carbon::parse($primer_fecha_factura)->format("Y-m-d");
                    } else {
                        // Si el contrato se creó después del corte, la factura es en el siguiente mes
                        $primer_fecha_factura = $creacion_contrato->copy()->addMonth()->day($dia_creacion_factura);
                        $primer_fecha_factura = Carbon::parse($primer_fecha_factura)->format("Y-m-d");
                    }

                    //** Si no existe ninguna factura en esa tabla es por que es la primer fac y entra a la validacion*
                    if(!DB::table('facturas_contratos as fc')->where('contrato_nro',$contrato->nro)->first()){
                        if(isset($primer_fecha_factura) &&
                        Carbon::parse($fecha)->format("Y-m-d") == $primer_fecha_factura &&
                        $contrato->fact_primer_mes == 0){
                            continue; //este continue salta la actual iteracion
                        }
                    }
                    //Fin validacion primer factura del contrato

                    $ultimaFactura = DB::table('facturas_contratos')
                    ->join('factura', 'facturas_contratos.factura_id', '=', 'factura.id')
                    ->where('facturas_contratos.contrato_nro', $contrato->nro)
                    ->select('factura.*')
                    ->orderBy('factura.fecha', 'desc')
                    ->first();

                    $mesUltimaFactura = false;
                    $mesActualFactura = date('Y-m',strtotime($fecha));

                    if($ultimaFactura){

                        //Validamos que solo vamos a evaluar por created_at a las f. electronicas, por que las pudieron emitir despues.
                        if($ultimaFactura->tipo == 2){
                            $mesUltimaFactura = date('Y-m',strtotime($ultimaFactura->created_at));
                        }else{
                            $mesUltimaFactura = date('Y-m',strtotime($ultimaFactura->fecha));
                        }

                        //Validacion nueva: mirar si la ultima factura generada tiene la opcion de factura del mes actual.
                        if($mesActualFactura == $mesUltimaFactura){
                            if($ultimaFactura->factura_mes_manual == 1){
                                continue; //salte esta iteracion entonces por que es la factura del mes manual.
                            }
                        }
                    }

                    /* ** Validacion: si la actual es dif a la ultima fac pasa o sino
                    si son iguales y no tiene fact manual == 1(la ultima) y es manual y no automatica pasa */
                    if($mesActualFactura != $mesUltimaFactura ||
                       $mesActualFactura == $mesUltimaFactura && $ultimaFactura->factura_mes_manual == 0 && $ultimaFactura->facturacion_automatica == 0)
                    {
                        ## Verificamos que el cliente no posea la ultima factura automática abierta, de tenerla no se le genera la nueva factura
                        if(isset($ultimaFactura->fecha)){
                            $fac = $ultimaFactura;
                        }else{$fac=false;}

                        //Primer filtro de la validación, que la factura esté cerrada o que no exista una factura.
                        if(isset($fac->estatus) || !$fac || $empresa->cron_fact_abiertas == 1){

                            //Segundo filtro, que la fecha de vencimiento de la factura abierta sea mayor a la fecha actual
                            if(isset($fac->vencimiento) && $fac->vencimiento > $fecha ||
                            isset($fac->estatus) && $fac->estatus == 0 || !$fac ||
                            isset($fac->estatus) && $fac->estatus == 2 ||
                            $empresa->cron_fact_abiertas == 1
                            ){

                                if(!$fac || isset($fac) && $fecha != $fac->fecha){
                                    $numero=round(floatval($numero));+1;

                                    //Obtenemos el número depende del contrato que tenga asignado (con fact electrpinica o estandar).
                                    $nro = NumeracionFactura::tipoNumeracion($contrato);

                                    if(is_null($nro)){
                                    }else{ //aca empieza la verdadera creacion de la factura despues de pasar las validaciones.

                                    $hoy = $fecha;

                                    if(!DB::table('facturas_contratos')
                                    ->whereDate('created_at',$hoy)
                                    ->where('contrato_nro',$contrato->nro)->where('is_cron',1)->first())
                                    {

                                        if($contrato->fecha_suspension){
                                                $fecha_suspension = $contrato->fecha_suspension;
                                        }else{
                                                $fecha_suspension = $grupo_corte->fecha_suspension;
                                        }

                                        $plazo=TerminosPago::where('dias', Funcion::diffDates($date_suspension, Carbon::now())+1)->first();
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
                                            $nro = $nro->fresh();
                                            $inicio=$nro->inicio;
                                            $nro->inicio += 1;
                                            $nro->save();
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
                                        $factura->vencimiento   = $date_suspension;
                                        $factura->suspension    = $date_suspension;
                                        $factura->pago_oportuno = $date_pagooportuno;
                                        $factura->observaciones = 'Facturación Automática - Corte '.$grupo_corte->fecha_corte;
                                        $factura->bodega        = 1;
                                        $factura->vendedor      = 1;
                                        $factura->prorrateo_aplicado = 0;
                                        $factura->facturacion_automatica = 1;

                                        if($contrato){
                                            $factura->contrato_id = $contrato->id;
                                        }

                                        //validacion extra antes de guardar que no haya ningun mismo codigo.
                                        if(Factura::where('codigo',$factura->codigo)->count() <= 1){
                                            $factura->save();

                                        // *** Actualizacion importante contratos multiples en una sola factura **** //
                                        if($contrato->factura_individual == 0){
                                            $contratos_multiples = Contrato::where('client_id',$factura->cliente)->where('factura_individual', 0)->get();
                                        }else {
                                            $contratos_multiples = Contrato::where('nro',$contrato->nro)->where('client_id',$factura->cliente)->get();
                                        }

                                        foreach($contratos_multiples as $cm){

                                            $descuentoPesos = 0;
                                            ## Se carga el item a la factura (Plan de Internet) ##
                                            if($contrato->plan_id){
                                                $plan = PlanesVelocidad::find($cm->plan_id);
                                                $item = Inventario::find($plan->item);



                                                $item_reg = new ItemsFactura;
                                                $item_reg->factura     = $factura->id;
                                                $item_reg->producto    = $item->id;
                                                $item_reg->ref         = $item->ref;
                                                $item_reg->precio      = $item->precio;
                                                $item_reg->descripcion = $plan->name;
                                                $item_reg->id_impuesto = $item->id_impuesto;
                                                $item_reg->impuesto    = $item->impuesto;
                                                if($cm->iva_factura == 1){
                                                    $item_reg->id_impuesto = 1;
                                                    $item_reg->impuesto = 19;
                                                }
                                                $item_reg->cant        = 1;
                                                $item_reg->desc        = $cm->descuento;

                                                if($cm->descuento_pesos != null && $descuentoPesos == 0){
                                                    $item_reg->precio      = $item_reg->precio - $cm->descuento_pesos;
                                                    $descuentoPesos = 1;
                                                }
                                                $item_reg->save();
                                            }

                                            ## Se carga el item a la factura (Plan de Televisión) ##
                                            if($cm->servicio_tv){
                                                $item = Inventario::find($cm->servicio_tv);
                                                $item_reg = new ItemsFactura;
                                                $item_reg->factura     = $factura->id;
                                                $item_reg->producto    = $item->id;
                                                $item_reg->ref         = $item->ref;
                                                $item_reg->precio      = $item->precio;
                                                $item_reg->descripcion = $item->producto;
                                                $item_reg->id_impuesto = $item->id_impuesto;
                                                $item_reg->impuesto    = $item->impuesto;
                                                $item_reg->cant        = 1;
                                                $item_reg->desc        = $cm->descuento;
                                                if($cm->descuento_pesos != null && $descuentoPesos == 0){
                                                    $item_reg->precio      = $item_reg->precio - $cm->descuento_pesos;
                                                    $descuentoPesos = 1;
                                                }
                                                $item_reg->save();
                                            }

                                            ## Se carga el item de otro tipo de servicio ##
                                            if($cm->servicio_otro){
                                                $item = Inventario::find($cm->servicio_otro);
                                                $item_reg = new ItemsFactura;
                                                $item_reg->factura     = $factura->id;
                                                $item_reg->producto    = $item->id;
                                                $item_reg->ref         = $item->ref;
                                                $item_reg->precio      = $item->precio;
                                                $item_reg->descripcion = $item->producto;
                                                $item_reg->id_impuesto = $item->id_impuesto;
                                                $item_reg->impuesto    = $item->impuesto;
                                                $item_reg->cant        = 1;
                                                $item_reg->desc        = $cm->descuento;
                                                if($cm->descuento_pesos != null && $descuentoPesos == 0){
                                                    $item_reg->precio      = $item_reg->precio - $cm->descuento_pesos;
                                                    $descuentoPesos = 1;
                                                }

                                                if($cm->rd_item_vencimiento == 1){

                                                    if($cm->dt_item_hasta > now()){
                                                        $item_reg->save();
                                                    }
                                                }else{
                                                    $item_reg->save();
                                                }
                                            }

                                            ## REGISTRAMOS EL ITEM SI TIENE PAGO PENDIENTE DE ASIGNACIÓN DE PRODUCTO
                                            $asignacion = Producto::where('contrato', $cm->id)->where('venta', 1)->where('status', 2)->where('cuotas_pendientes', '>', 0)->get()->last();

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
                                                $item_reg->desc        = $cm->descuento;
                                                if($cm->descuento_pesos != null && $descuentoPesos == 0){
                                                    $item_reg->precio      = $item_reg->precio - $cm->descuento_pesos;
                                                    $descuentoPesos = 1;
                                                }
                                                $item_reg->save();
                                            }

                                            //guardamos en la tabla detalle para saber que esa factura tiene n contratos
                                            DB::table('facturas_contratos')->insert([
                                                'factura_id' => $factura->id,
                                                'contrato_nro' => $cm->nro,
                                                'created_by' => 0,
                                                'client_id' => $factura->cliente,
                                                'is_cron' => 1,
                                                'created_at' => Carbon::now()
                                            ]);
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
                                        $fechaVencimiento = Carbon::parse($date_suspension)->format('d-m-Y');

                                        $bulksms = $empresa->sms_factura_generada;
                                        $bulksms = str_replace("{cliente}", $nombreCliente, $bulksms);
                                        $bulksms = str_replace("{empresa}", $nombreEmpresa, $bulksms);
                                        $bulksms = str_replace("{factura}", $codigoFactura, $bulksms);
                                        $bulksms = str_replace("{valor}", $valorFactura, $bulksms);
                                        $bulksms = str_replace("{vencimiento}", $fechaVencimiento, $bulksms);

                                        $bulk .= '{"numero": "57'.$numero.'", "sms": "'.$bulksms.'"},';

                                        }else if($empresa->nombre == 'FIBRACONEXION S.A.S.' || $empresa->nit == '900822955' || $empresa->nombre == 'Almeidas Comunicaciones S.A.S' ||  $empresa->nit == '901044772' || $empresa->nombre == 'Telecomunicaciones Por Redes Pon Tele Pon S.A.S' ||  $empresa->nit == '901346829' ){
                                            $fullname = $factura->cliente()->nombre.' '.$factura->cliente()->apellidos();
                                            $bulksms = ''.trim($fullname).'. '.$empresa->nombre.' le informa que su factura de servicio de internet. Tiene como fecha de vencimiento: '.$date->format('d-m-Y').' Total a pagar '.$factura->totalAPI($empresa->id)->total;
                                            $bulk .= '{"numero": "57'.$numero.'", "sms": "'.$bulksms.'"},';
                                        }else{
                                            // Array con los nombres de los meses en español
                                            $meses = [
                                                1 => 'enero',
                                                2 => 'febrero',
                                                3 => 'marzo',
                                                4 => 'abril',
                                                5 => 'mayo',
                                                6 => 'junio',
                                                7 => 'julio',
                                                8 => 'agosto',
                                                9 => 'septiembre',
                                                10 => 'octubre',
                                                11 => 'noviembre',
                                                12 => 'diciembre',
                                            ];
                                            $numeroMes = date('n', strtotime($factura->fecha));
                                            $mes = ucfirst($meses[$numeroMes]);

                                            $bulksms = $empresa->nombre.' informa, su factura del mes de ' .$mes.  ' fue generada por un total de ' .$factura->total()->total .  ' en el contrato nro ' . $contrato->nro . ' . Cuenta para pago en Coopenessa convenio Telepon ' . $contrato->contrato_nro;
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

                                        /* Creacion de pagos automaticamente */
                                        if($contrato->saldo_favor >= $factura->totalAPI($empresa->id)->total && $empresa->aplicar_saldofavor == 1){
                                            self::pagoFacturaAutomatico($factura);
                                        }

                                    }// fin de validacion factura doble.

                                } //Validacion facturas_contratos

                            }
                            }//validacion que no se creen dos el mismo dia
                        }
                    } //Comentando factura abierta del mes pasado

                 }


                }// fin foreach contratos.
            }

            if(isset($nro)){
                $nro->inicio = $nro->inicio+1;
                $nro->save();
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
        }
    }

    //Pago automatico que se genera cuando el cliente tiene saldo a favor.
    public static function pagoFacturaAutomatico($factura){

            $empresa = $factura->empresa;
            $precio = $factura->totalAPI($empresa)->total;

            //obtencion de numeración de el recibo de caja.
            $nro = Numeracion::where('empresa', $empresa)->first();
            $caja = $nro->caja;

            while (true) {
                $numero = Ingreso::where('empresa', $empresa)->where('nro', $caja)->count();
                if ($numero == 0) {
                    break;
                }
                $caja++;
            }

            $request = new StdClass;
            $request->cuenta = Banco::where('empresa',$empresa)->where('nombre','like','Saldos a favor')->first()->id;
            $request->metodo_pago = 1;
            $request->notas = "Recibo de caja generado automáticamente por saldo a favor.";
            $request->observaciones = "Recibo de caja generado automáticamente por saldo a favor.";
            $request->tipo = 1;
            $request->fecha = Carbon::now()->format('Y-m-d');

            $ingreso = new Ingreso;
            $ingreso->nro = $caja;
            $ingreso->empresa = $empresa;
            $ingreso->cliente = $factura->cliente;
            $ingreso->cuenta = $request->cuenta;
            $ingreso->metodo_pago = $request->metodo_pago;
            $ingreso->notas = $request->notas;
            $ingreso->tipo = $request->tipo;
            $ingreso->fecha = $request->fecha;
            $ingreso->observaciones = mb_strtolower($request->observaciones);
            $ingreso->save();

            $items = new IngresosFactura;
            $items->ingreso = $ingreso->id;
            $items->factura = $factura->id;
            $items->pagado = $precio; //asi exista mas dinero del  pagado ese se debe usar.
            $items->puc_factura = $factura->cuenta_id;
            $items->pago = self::precisionAPI($precio, $empresa);
            $items->save();

            $factura->estatus = 0;
            $factura->save();

            //Descontamos el saldo a favor del cliente
            $contacto = Contacto::Find($factura->cliente);
            $contacto->saldo_favor-=$precio;
            $contacto->save();

            //No vamos a regisrtrar por el momento un movimiento del puc ya que no sabemos esta informacion.
            // $ingreso->puc_banco = $request->forma_pago; //cuenta de forma de pago genérico del ingreso. (en memoria)
            // PucMovimiento::ingreso($ingreso,1,2,$request);

            self::up_transaccion_(7, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 2, $precio, $ingreso->fecha, "Uso de saldo a favor automatico.",null,$empresa);
    }

    public static function CortarFacturas(){
        $i=0;
        $fecha = date('Y-m-d');

        if(request()->fechaCorte){
            $fecha = request()->fechaCorte;
        }
        $swGrupo = 1; //masivo
        $horaActual = date('H:i');

        $grupos_corte = DB::table('grupos_corte')
        ->where('status', 1)
        ->where('hora_suspension','<=',$horaActual)
        ->where('fecha_suspension','!=',0)
        ->get();

        if($grupos_corte->count() > 0){

            $grupos_corte_array = array();

            foreach($grupos_corte as $grupo){
                array_push($grupos_corte_array,$grupo->id);
            }

            //Estamos tomando la ultima factura siempre del cliente con el orderby y el groupby, despues analizamos si esta ultima ya vencio
            $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
                leftJoin('facturas_contratos as fcs', 'fcs.factura_id', '=', 'f.id')
                ->leftJoin('contracts as cs', function ($join) {
                    $join->on('cs.nro', '=', 'fcs.contrato_nro')
                         ->orOn('cs.id', '=', 'f.contrato_id');
                })->
                select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'cs.state', 'f.contrato_id')->
                where('f.estatus',1)->
                whereIn('f.tipo', [1,2])->
                where('contactos.status',1)->
                where('cs.state','enabled')->
                whereIn('cs.grupo_corte',$grupos_corte_array)->
                where('cs.fecha_suspension', null)->
                where('cs.server_configuration_id','!=',null)-> //se comenta por que tambien se peuden canclear planes de tv que no estan con servidor
                whereDate('f.vencimiento', '<=', now())->
                orderBy('f.id', 'desc')->
                take(40)->
                get();

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

                //ESto es lo que hay que refactorizar.
                $facturaContratos = DB::table('facturas_contratos')
                ->where('factura_id',$factura->id)->pluck('contrato_nro');

                if(!DB::table('facturas_contratos')
                ->where('factura_id',$factura->id)->first()){
                    $facturaContratos = Contrato::where('id',$factura->contrato_id)->pluck('nro');
                }

                $contratosId = Contrato::whereIn('nro',$facturaContratos)
                ->pluck('id');

                $ultimaFacturaRegistrada = Factura::
                where('cliente',$factura->cliente)
                ->where('estatus','<>',2)
                ->whereIn('contrato_id',$contratosId)
                ->orderBy('created_at', 'desc')
                ->value('id');

                //manera antigua de buscar el contrato.
                if(!$ultimaFacturaRegistrada){
                      $ultimaFacturaRegistrada = Factura::
                        where('cliente',$factura->cliente)
                        ->where('contrato_id',$factura->contrato_id)
                        ->orderBy('created_at', 'desc')
                        ->value('id');
                }

                if($factura->id == $ultimaFacturaRegistrada){

                    //1. debemos primero mirar si los contrsatos existen en la tabla detalle, si no hacemos el proceso antiguo
                    $contratos = Contrato::whereIn('nro',$facturaContratos)->get();
                    if(!$contratos){
                        if($factura->contrato_id != null){
                            $contratos = Contrato::where('id',$factura->contrato_id)->get();
                        }else{
                            $contratos = Contrato::where('id',$contacto->contrato_id)->get();
                        }
                    }

                    $promesaExtendida = DB::table('promesa_pago')->where('factura', $contacto->factura)->where('vencimiento', '>=', $fecha)->count();

                    //2. Debemos recorrer el o los contratos para que haga el disabled.
                    foreach($contratos as $contrato){
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

                                if(isset($contrato->server_configuration_id) && $factura->estatus != 0){

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
                        if (($contrato && $swGrupo == 1) ||
                        ($contrato && $swGrupo == 0 && $contrato->fecha_suspension == getdate()['mday'])) {

                        //segundo filtro de validacion, validando por rango de fechas
                        $diasHabilesNocobro = 0;
                        if($contrato->tipo_nosuspension == 1 &&  $contrato->fecha_desde_nosuspension <= $fecha && $contrato->fecha_hasta_nosuspension >= $fecha){
                            $diasHabilesNocobro = 1;
                        }

                        if($diasHabilesNocobro == 0){
                            if(isset($contrato->server_configuration_id) || $promesaExtendida == 0){

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
                                $contrato->observaciones = $contrato->observaciones. " - Contrato deshabilitado automaticamente";
                                $contrato->save();

                                $descripcion = '<i class="fas fa-check text-success"></i> <b>Cambio de Status</b> de habilitado a deshabilitado por cronjob<br>';
                                $movimiento = new MovimientoLOG();
                                $movimiento->contrato    = $contrato->id;
                                $movimiento->modulo      = 5;
                                $movimiento->descripcion = $descripcion;
                                $movimiento->created_by  = 1;
                                $movimiento->empresa     = $contrato->empresa;
                                $movimiento->save();
                            }
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

    public function cortarTelevision(){
        $i=0;
        $fecha = date('Y-m-d');
        $empresa = Empresa::find(1);

        if(request()->fechaCorte){
            $fecha = request()->fechaCorte;
        }
        $swGrupo = 1; //masivo
        $horaActual = date('H:i');

        $grupos_corte = DB::table('grupos_corte')
        ->where('status', 1)
        ->where('hora_suspension','<=',$horaActual)
        ->where('fecha_suspension','!=',0)
        ->where('id',1)
        ->get();

        if($grupos_corte->count() > 0 && $empresa->smartOLT != null){

            $grupos_corte_array = array();

            foreach($grupos_corte as $grupo){
                array_push($grupos_corte_array,$grupo->id);
            }

            $contactos = Contacto::join('factura as f', 'f.cliente', '=', 'contactos.id')
            ->join('contracts as cs', 'cs.id', '=', 'f.contrato_id')
            ->join('grupos_corte as gc', 'gc.id', '=', 'cs.grupo_corte') // Unimos con grupos_corte
            ->select(
                'contactos.id',
                'contactos.nombre',
                'contactos.nit',
                'f.id as factura',
                'f.estatus',
                'f.suspension',
                'cs.state',
                'f.contrato_id',
                'gc.prorroga_tv', // Seleccionamos prorroga_tv
                'gc.id as grupo_corte'
            )
            ->where('f.estatus', 1)
            ->whereIn('f.tipo', [1, 2])
            ->where('contactos.status', 1)
            // ->where('cs.state', 'enabled') // Solo si aplica
            ->whereIn('cs.grupo_corte', $grupos_corte_array)
            ->where('cs.fecha_suspension', null)
            ->where('cs.state_olt_catv', true)
            ->whereRaw("DATE_ADD(f.vencimiento, INTERVAL gc.prorroga_tv DAY) <= NOW()") // Agregamos la prórroga a la fecha de vencimiento
            ->orderBy('f.id', 'desc')
            ->take(45)
            ->get();

            if($contactos){
                foreach ($contactos as $contacto) {

                    $factura = Factura::find($contacto->factura);

                    //ESto es lo que hay que refactorizar.
                    $facturaContratos = DB::table('facturas_contratos')
                    ->where('factura_id',$factura->id)->pluck('contrato_nro');

                    if(!DB::table('facturas_contratos')
                    ->where('factura_id',$factura->id)->first()){
                        $facturaContratos = Contrato::where('id',$factura->contrato_id)->pluck('nro');
                    }

                    $contratosId = Contrato::whereIn('nro',$facturaContratos)
                    ->pluck('id');

                    $ultimaFacturaRegistrada = Factura::
                    where('cliente',$factura->cliente)
                    ->where('estatus','<>',2)
                    ->whereIn('contrato_id',$contratosId)
                    ->orderBy('created_at', 'desc')
                    ->value('id');

                    //manera antigua de buscar el contrato.
                    if(!$ultimaFacturaRegistrada){
                        $ultimaFacturaRegistrada = Factura::
                        where('cliente',$factura->cliente)
                        ->where('contrato_id',$factura->contrato_id)
                        ->orderBy('created_at', 'desc')
                        ->value('id');
                    }

                    if($factura->id == $ultimaFacturaRegistrada){

                    //1. debemos primero mirar si los contrsatos existen en la tabla detalle, si no hacemos el proceso antiguo
                    $contratos = Contrato::whereIn('nro',$facturaContratos)->get();
                    if(!$contratos){
                        if($factura->contrato_id != null){
                            $contratos = Contrato::where('id',$factura->contrato_id)->get();
                        }else{
                            $contratos = Contrato::where('id',$contacto->contrato_id)->get();
                        }
                    }

                    //2. Debemos recorrer el o los contratos para que haga el disabled.
                        foreach($contratos as $contrato){

                                if($contrato->olt_sn_mac != null){
                                    $curl = curl_init();

                                curl_setopt_array($curl, array(
                                CURLOPT_URL => $empresa->adminOLT.'/api/onu/disable_catv/'.$contrato->olt_sn_mac,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'POST',
                                CURLOPT_HTTPHEADER => array(
                                    'X-token: '.$empresa->smartOLT
                                ),
                                ));

                                $response = curl_exec($curl);
                                $response = json_decode($response);

                                curl_close($curl);

                                if(isset($response->status) && $response->status == true){
                                    $contrato->state_olt_catv = false;
                                    $contrato->save();
                                }

                            }
                        }
                    }
                }
            }
        }
    }

    public static function CortarPromesas(){
        $i=0;
        $fecha = date('Y-m-d');
        $hora = date('G:i');
        $hora_24 = date('H:i', strtotime($hora));

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            join('promesa_pago as p', 'p.factura', '=', 'f.id')->
            select('contactos.id','p.hora_pago')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('f.promesa_pago', $fecha)->
            where('contactos.status',1)->
            where('cs.state','enabled')->
            whereRaw('TIME_FORMAT(p.hora_pago, "%H:%i") < ?', [$hora_24])->
            get();

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

                    $this->up_transaccion_(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion,null, $empresa->id);

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

                    $this->up_transaccion_(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion,null, $empresa->id);

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

            $factura = Factura::where('codigo', substr($request->invoice_number, $empresa->caracter_combo_pay))->first();

            if (!$factura) {
                $factura = Factura::where('codigo', substr($request->invoice_number, 4))->first();
            }
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

                $this->up_transaccion_(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion, null, $empresa->id);

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

    //metodo para recibir la respuesta de la api de toppay
    public function eventosTopPay(Request $request){
        $empresa = Empresa::find(1);
        if($request->status == 'success'){

            $factura = Factura::where('codigo','LIKE', '%' . $request->reference . '%')->first();

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

                $banco = Banco::where('nombre', 'TOPPAY')->where('estatus', 1)->where('lectura', 1)->first();

                # REGISTRAMOS EL INGRESO
                $ingreso                = new Ingreso;
                $ingreso->nro           = $caja;
                $ingreso->empresa       = $empresa->id;
                $ingreso->cliente       = $factura->cliente;
                $ingreso->cuenta        = $banco->id;
                $ingreso->metodo_pago   = 9;
                $ingreso->tipo          = 1;
                $ingreso->fecha         = date('Y-m-d');
                $ingreso->observaciones = 'Pago topPay ID: '.$request->reference;
                $ingreso->save();

                # REGISTRAMOS EL INGRESO_FACTURA
                $precio = ($this->precisionAPI($request->amount, $empresa->id) > $factura->porpagarAPI($empresa->id)) ? $factura->porpagarAPI($empresa->id) : $this->precisionAPI($request->amount, $empresa->id);
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

                $this->up_transaccion_(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion,null, $empresa->id);

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
                   /* $servicio = Integracion::where('empresa', $empresa->id)->where('tipo', 'SMS')->where('status', 1)->first();
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
                    }*/
                }
                return response('success', 200);
            }
            return response('Factura ya pagada', 200);
        }else{
            return response('Error al pagar la Factura', 500);
        }

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
            $mensaje = Auth::user()->empresa()->nombre." Estimado cliente, se le informa que su factura de internet ha sido generada. ".$empresa->slogan;
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

    return $contratos = Contrato::join('facturas_contratos as fc','fc.contrato_nro','contracts.nro')
        ->join('factura as f','f.id','fc.factura_id')
        ->where('f.fecha', '2025-02-01')
        ->where('f.vencimiento', '>', date('Y-m-d'))
        ->where('contracts.state', 'disabled')
        ->select('contracts.*')
        ->get();


    //Habilitando contratos masivamente segun unas especificaciones
    foreach($contratos as $contrato){
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
    }

    return "okok";
    //Script para habilitar contratos por mk tambien segun unas especificaciones


        //Envio de facturas solo por correo por fecha unica.
        //  $fechaInvoice = Carbon::now()->format('Y-m').'-'.substr(str_repeat(0, 2)."15", - 2);
        //  $this->sendInvoices($fechaInvoice);
        //  return "ok";


        // SCRIPT PARA VER CONTRATOS DESHABILUTADOS CON SU ULTIMA FACTURA CERRADA //
        // $contratos = DB::table('contracts as cont')
        // ->where('state', 'disabled')
        // ->join('facturas_contratos', 'cont.nro', '=', 'facturas_contratos.contrato_nro')
        // ->leftJoin('factura as fac', function ($join) {
        //     $join->on('fac.id', '=', DB::raw('(SELECT factura_id FROM facturas_contratos WHERE facturas_contratos.contrato_nro = cont.nro ORDER BY id DESC LIMIT 1)'));
        // })
        // ->where(function ($query) {
        //     $query->whereNull('fac.estatus')->orWhere('fac.estatus', 0);
        // })
        // ->select('cont.*')
        // ->distinct()
        // ->get();

        // $contratos = Contrato::where('state','disabled')->get();
        // $i = 0;
        // foreach($contratos as $contrato){

        //     $facturaContratos = DB::table('facturas_contratos')
        //         ->where('contrato_nro',$contrato->nro)->orderBy('id','DESC')->first();

        //     if($facturaContratos){
        //         $ultFactura = Factura::Find($facturaContratos->factura_id);
        //         if($ultFactura->estatus == 0){
        //             $i = $i+1;
        //             echo $contrato->nro . "<br>";
        //             // return $contrato;
        //             // return $ultFactura;
        //         }
        //     }
        // }
        // return "Deshabilitaods mal hay: " . $i;
        // SCRIPT PARA VER CONTRATOS DESHABILUTADOS CON SU ULTIMA FACTURA CERRADA //

        //--------- Obtener facturas relacionadas con constratos de la manera nueva o antigua por varias validaciones ------- ///
        return $facturas = Factura::leftJoin('facturas_contratos as fc','fc.factura_id','factura.id')
        ->leftJoin('contracts as cs', function ($join) {
            $join->on('cs.nro', '=', 'fc.contrato_nro')
                    ->orOn('cs.id', '=', 'factura.contrato_id');
        })
        ->where('factura.estatus',2)
        ->where('factura.observaciones','LIKE','%Facturación Automática -%')->where('factura.fecha',"2024-12-01")
        //   ->where('cs.grupo_corte',2)
        ->select('factura.id')
        ->get();

        $eliminadas = 0;
        foreach($facturas as $f){

            if($f->pagado() == 0){
                DB::table('facturas_contratos')->where('factura_id',$f->id)->delete();
                $itemsFactura = ItemsFactura::where('factura',$f->id)->delete();
                DB::table('crm')->where('factura',$f->id)->delete();

                //Si queremos eliminar ingresos tambien si no comentar linea:
                // $if = DB::table('ingresos_factura')->where('factura',$f->id)->first();
                // if($if){
                //     DB::table('ingresos')->where('id',$if->ingreso)->delete();
                //     DB::table('ingresos_factura')->where('factura',$f->id)->delete();
                // }

                $eliminadas++;
                $f->delete();
            }
        }


        return "Se eliminaron un total de:" . $eliminadas . " facturas correctamente";

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

       /// ELIMINAR FACTURAS REPETIDAS EN UN MISMO MES PARA UN MISMO CONTRATO QUE NO ESTEN PAGAS ///
       return;
       $contratos = Contrato::where('status',1)->get();
       $eli = 0;
       foreach($contratos as $contrato){

           $mes = 12;
           $year = 2024;
           $dia = 16;

           $query_facturas = Factura::leftJoin('facturas_contratos as fc','fc.factura_id','factura.id')
            ->leftJoin('contracts as cs', function ($join) {
                   $join->on('cs.nro', '=', 'fc.contrato_nro')
                        ->orOn('cs.id', '=', 'factura.contrato_id');
               })
           ->where('fc.contrato_nro',$contrato->nro)
           ->whereYear('factura.fecha', $year)
           ->whereMonth('factura.fecha', $mes)
           ->whereDay('factura.fecha', $dia)
           ->orWhere('factura.contrato_id',$contrato->id)
           ->whereYear('factura.fecha', $year)
           ->whereMonth('factura.fecha', $mes)
           ->whereDay('factura.fecha', $dia)
           ->select('factura.*')
           ->groupBy('factura.codigo');


           $facturas = $query_facturas->get();

               if($facturas->count() > 1){

                   foreach($facturas as $f){

                       if($f->pagado() == 0){

                       $itemsFactura = ItemsFactura::where('factura',$f->id)->delete();
                       DB::table('facturas_contratos')->where('factura_id',$f->id)->delete();
                           DB::table('crm')->where('factura',$f->id)->delete();
                               $eli++;
                               $f->delete();
                       }else{
                           $facturas = $query_facturas->get();

                               if($facturas->count() > 1){
                                   DB::table('facturas_contratos')->where('factura_id',$f->id)->delete();
                                    $itemsFactura = ItemsFactura::where('factura',$f->id)->delete();
                                   DB::table('crm')->where('factura',$f->id)->delete();
                                           $eli++;
                                           $f->delete();
                               }
                       }
                   }
               }

               // return "ok";
       }

       return "se eliminaron " . $eli;

       /// FIN ELIMINAR FACTURAS REPETIDAS EN UN MISMO MES PARA UN MISMO CONTRATO QUE NO ESTEN PAGAS ///

    }

    public function envioFacturaWpp(WapiService $wapiService){

        if(getdate()['mday'] == 01){
            $dia = 1;
        }else $dia = getdate()['mday'];

        $empresa = Empresa::Find(1);

        $grupos_corte = GrupoCorte::where('status', 1)->where('fecha_factura',$dia)->get();

        if($grupos_corte->count() > 0){

            $grupos_corte_array = array();

            foreach($grupos_corte as $grupo){
                array_push($grupos_corte_array,$grupo->id);
            }

         $facturas = Factura::
            join('contracts as c','c.id','=','factura.contrato_id')
            ->where('factura.observaciones','LIKE','%Facturación Automática -%')->where('factura.fecha',date('Y-m-d'))
            ->where('factura.whatsapp',0)
            ->whereIn('c.grupo_corte',$grupos_corte_array)
            ->select('factura.*')
            ->limit(45)->get();


            foreach($facturas as $factura){

                view()->share(['title' => 'Imprimir Factura']);

                $facturaPDF = $this->getPdfFactura($factura->id);
                $facturabase64 = base64_encode($facturaPDF);
                $instance = Instance::where('company_id', $empresa->id)->first();

                if(is_null($instance) || empty($instance)){
                    Log::error('Instancia no está creada.');
                    return;
                }

                if($instance->status !== "PAIRED") {
                    Log::error('La instancia de whatsapp no está conectada, por favor conectese a whatsapp y vuelva a intentarlo.');
                    return;
                }

                $contacto = $factura->cliente();

                // envio de mensajes por whatsapp //
                $file = [
                    "mime" => "@file/pdf",
                    "data" => $facturabase64,
                ];

                $contact = [
                    "phone" =>  "57" . $contacto->celular,
                    "name" => $contacto->nombre . " " . $contacto->apellido1
                ];

                $nameEmpresa = $empresa->nombre;
                $estadoCuenta = $factura->estadoCuenta();

                $msg_deuda = "";
                $total = $factura->total()->total;
                if($estadoCuenta->saldoMesAnterior > 0){
                    $msg_deuda = "El total a deber es: " . Funcion::Parsear($estadoCuenta->saldoMesAnterior + $total);
                }

                $message = "$nameEmpresa Le informa que su factura ha sido generada bajo el número $factura->codigo por un monto de $$total pesos. " . $msg_deuda;

                $body = [
                    "contact" => $contact,
                    "body" => $message,
                    "file" => $file
                ];

                $response = (object) $wapiService->sendMessageMedia($instance->uuid_whatsapp, $instance->api_key, $body);
                if(isset($response->statusCode)) {
                    Log::error('No se pudo enviar el mensaje, por favor intente nuevamente.' . $contacto->nit);
                }

                if(isset($response->scalar)){
                $response = json_decode($response->scalar);
                }

                if(isset($response->status) && $response->status != "success") {
                    Log::error('No se pudo enviar el mensaje, por favor intente nuevamente. ' . $contacto->nit);
                    // break;
                }

                $archivo = public_path() . "/convertidor/" . $factura->codigo . ".pdf";
                if (file_exists($archivo)) {
                    unlink($archivo);
                }
                $factura->whatsapp = 1;
                $factura->save();
            }
            Log::info("Lote de facturas enviadas por whatsapp correctamente.");
        }
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

    public function validateEmisionApi(){

        $bearerToken = env('EMISION_TOKEN');
        $urlEmision = env('URL_EMISION_DIAN');
        $mesInicio = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();

        if($bearerToken != "" && $urlEmision != "")
        {
            $facturas = Factura::where('fecha','>=',$mesInicio)->where('fecha','<=',$finMes)
            ->where('emitida',1)
            ->where('tipo',2)
            ->where('dian_service',0);

            $pos = Factura::where('fecha','>=',$mesInicio)->where('fecha','<=',$finMes)
            ->where('emitida',1)
            ->where('tipo',6)
            ->where('dian_service',0);

            $documentoSoporte = FacturaProveedores::where('fecha','>=',$mesInicio)->where('fecha','<=',$finMes)
            ->where('emitida',1)
            ->where('dian_service',0);

            $notasCredito = NotaCredito::where('fecha','>=',$mesInicio)->where('fecha','<=',$finMes)
            ->where('emitida',1)
            ->where('dian_service',0);

            $notasDebito = NotaDedito::where('fecha','>=',$mesInicio)->where('fecha','<=',$finMes)
            ->where('emitida',1)
            ->where('dian_service',0);

            $nominas = Nomina::where('fecha_emision','>=',$mesInicio)->where('fecha_emision','<=',$finMes)
            ->where('emitida',1)
            ->where('dian_service',0);

            $data = [
                'facturas' => $facturas->count(),
                'pos' => $pos->count(),
                'documentosoporte' => $documentoSoporte->count(),
                'notascredito' => $notasCredito->count(),
                'notasdebito' => $notasDebito->count(),
                'nomina' => $nominas->count(),
            ];

            try {
                $emisionService = new EmisionesService();
                $response = $emisionService->sendEmisionsEmpresa($data);

                $response = json_decode($response);

                if(isset($response->status) && $response->status == 200){
                    $facturas->update(['dian_service'=> 1]);
                    $pos->update(['dian_service'=> 1]);
                    $documentoSoporte->update(['dian_service'=> 1]);
                    $notasCredito->update(['dian_service'=> 1]);
                    $notasDebito->update(['dian_service'=> 1]);
                    $nominas->update(['dian_service'=> 1]);
                }
                Log::info('Finalizado con exito el informe de emisiones del dia: ' . Carbon::now()->format('Y-m-d'));

            } catch (ClientException $e) {
                if($e->getResponse()->getStatusCode() === 404) {
                    Log::error('Hay un error en la importacion de la informacion: ' . Carbon::now()->format('Y-m-d'));
                    // return $e;
                }
            }
        }else{
            Log::error('No hay credenciales para registrar las emisiones: ' . Carbon::now()->format('Y-m-d'));
        }

        //REVISION RECONEXION GENERAL//.
        $empresa = Empresa::Find(1);
        if($empresa->reconexion_generica == 1 && $empresa->dias_reconexion_generica != null){
            $diasMas = $empresa->dias_reconexion_generica;

            $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.id','=','f.contrato_id')->
            select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'cs.state', 'f.contrato_id')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('contactos.status',1)->
            where('cs.fecha_suspension', null)->
            // where('f.id',191)->
            whereDate(DB::raw("DATE_ADD(f.vencimiento, INTERVAL $diasMas DAY)"), '<=', now())->
            orderBy('f.id', 'desc')->
            get();

            foreach ($contactos as $contacto) {

                $factura = Factura::find($contacto->factura);

                //ESto es lo que hay que refactorizar.
                $facturaContratos = DB::table('facturas_contratos')
                ->where('factura_id',$factura->id)->pluck('contrato_nro');

                if(!DB::table('facturas_contratos')
                ->where('factura_id',$factura->id)->first()){
                    $facturaContratos = Contrato::where('id',$factura->contrato_id)->pluck('nro');
                }

                $contratosId = Contrato::whereIn('nro',$facturaContratos)
                ->pluck('id');

                $ultimaFacturaRegistrada = Factura::
                where('cliente',$factura->cliente)
                ->where('estatus','<>',2)
                ->whereIn('contrato_id',$contratosId)
                ->orderBy('created_at', 'desc')
                ->value('id');

                //manera antigua de buscar el contrato.
                if(!$ultimaFacturaRegistrada){
                      $ultimaFacturaRegistrada = Factura::
                        where('cliente',$factura->cliente)
                        ->where('contrato_id',$factura->contrato_id)
                        ->orderBy('created_at', 'desc')
                        ->value('id');
                }

                if($factura->id == $ultimaFacturaRegistrada){
                    $itemReconexion = Inventario::where('type','RECONEXION')->first();
                    $itemExiste = ItemsFactura::where('factura',$factura->id)->where('ref','RECONEXION')->first();
                    if($itemReconexion && !$itemExiste){
                        $item = new ItemsFactura();
                        $item->factura     = $factura->id;
                        $item->producto    = $itemReconexion->id;
                        $item->ref         = $itemReconexion->ref;
                        $item->precio      = $itemReconexion->precio;
                        $item->descripcion = $itemReconexion->descripcion;
                        $item->id_impuesto = $itemReconexion->id_impuesto;
                        $item->impuesto    = $itemReconexion->impuesto;
                        $item->cant        = 1;
                        $item->desc        = $itemReconexion->descuento;
                        $item->save();
                    }
                }
            }
        }
        //Fin REVISION RECONEXION GENERAL//.
    }

    //Este metodo me permite validar que facturas se crearon con el mismo codigo y quedaron emitidas, la que tiene el
    //codigo 409 es la que no quedo emitida y debe cambiar de codigo.
    public function validarFacturasDobles(){

        $fecha_inicio = "2024-04-01";
        $fecha_fin = "2024-04-31";

        // Consulta para obtener facturas con el mismo código y dian_response = 409
         return $noObtener = Factura::
        where('fecha', '>=',$fecha_inicio)
        ->where('fecha', '<=',$fecha_fin)
        ->where('dian_response', 409)
        ->groupBy('codigo') // Agrupamos por el atributo código
        ->havingRaw('COUNT(codigo) > 1') // Condición para obtener facturas con el mismo código
        ->pluck('codigo');


        // Obtener los códigos duplicados sin filtrar por dian_response
        $duplicatedCodes = Factura::whereBetween('fecha', [$fecha_inicio, $fecha_fin])
            ->groupBy('codigo')
            ->havingRaw('COUNT(codigo) > 1')
            ->pluck('codigo');

        // Obtener las facturas que tienen el dian_response = 409 y cuyo código esté en los duplicados
        $facturas = Factura::whereIn('codigo', $duplicatedCodes)
            ->whereNotIn('codigo',$noObtener)
            ->where('dian_response', 409)
            ->whereBetween('fecha', [$fecha_inicio, $fecha_fin])
            ->get();

        // tipo 2 numeracion dian
        $nro=NumeracionFactura::where('empresa',1)->where('preferida',1)->where('estado',1)->where('tipo',2)->first();

        foreach($facturas as $factura){
            //Actualiza el nro de inicio para la numeracion seleccionada
            $inicio = $nro->inicio;

            // Validacion para que solo asigne numero consecutivo si no existe.
            while (Factura::where('codigo',$nro->prefijo.$inicio)->first()) {
                $nro->save();
                $inicio=$nro->inicio;
                $nro->inicio += 1;
            }

            $factura->codigo=$nro->prefijo.$inicio;
            $factura->emitida = 0;

            $nro->save();
            $factura->save();
        }

        return "correccion finalizada";
    }
}
