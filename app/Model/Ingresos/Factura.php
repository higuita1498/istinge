<?php

namespace App\Model\Ingresos;

use App\Empresa;
use App\Retencion;
use Illuminate\Database\Eloquent\Model;
use App\Contacto;
use App\Contrato;
use App\Impuesto; use App\Vendedor;
use App\Funcion; use Auth;
use App\TerminosPago;
use App\Banco;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\IngresosFactura;
use App\Model\Ingresos\NotaCreditoFactura;
use App\Model\Ingresos\IngresosRetenciones;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\Bodega;
use Carbon\Carbon;
use DB;
use App\GrupoCorte;
use App\Puc;
use App\PucMovimiento;
use App\FormaPago;
use stdClass;
use App\User;

class Factura extends Model
{
    protected $table = "factura";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'nro_remision','cot_nro', 'empresa','codigo',  'numeracion',
        'vendedor', 'tipo', 'cliente', 'fecha', 'vencimiento', 'observaciones',
        'estatus', 'notas', 'plazo', 'created_at', 'updated_at', 'term_cond',
        'facnotas' , 'lista_precios', 'bodega','emitida','dian_response',
        'nonkey', 'statusdian', 'observacionesdian', 'modificado','fecha_expedicion',
        'tipo_fac','tipo_operacion', 'promesa_pago', 'contrato_id', 'created_by','prorrateo_aplicado','facturacion_automatica'
    ];

    protected $appends = ['session'];

    public function getSessionAttribute(){
        return $this->getAllPermissions(Auth::user()->id);
    }

    public function getAllPermissions($id){
        if(Auth::user()->rol>=2){
            if (DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->count() > 0 ) {
                $permisos = DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->get();
                foreach ($permisos as $key => $value) {
                    $_SESSION['permisos'][$permisos[$key]->id_permiso] = '1';
                }
                return $_SESSION['permisos'];
            }
            else return null;
        }
    }
    
    public function parsear($valor){
        return number_format($valor, auth()->user()->empresa()->precision, auth()->user()->empresa()->sep_dec, (auth()->user()->empresa()->sep_dec == '.' ? ',' : '.'));
    }

    public function cliente(){
         return Contacto::where('id',$this->cliente)->first();
    }
    
    public function banco(){
         return Banco::where('id',$this->cuenta)->first();
    }
    
    public function contrato(){
        if($this->contrato_id){
        return $contrato = Contrato::where('id',$this->contrato_id)->first();
        }
    }

    public function contratoAsociado(){
        $contrato = Contrato::find($this->contrato_id);

        if($contrato){return $contrato;}
    }
    
    public function servidor(){
        $cliente = Contacto::where('id',$this->cliente)->first();
        $contrato = Contrato::where('client_id',$cliente->id)->first();
        if(!$contrato){
            return '';
        }
        return DB::table('mikrotik')->where('id',$contrato->server_configuration_id)->first();
    }

    public function estatus($class = false, $isId = false)
    {

        if (!isset($this->estatus)) {
            $factura = DB::table('factura')->select('estatus as estado')->where('id', $this->id)->first();
            if (!$factura) {
                return '';
            }
            $estatus = $factura->estado;
        } else {
            $estatus = $this->estatus;
        }


        if ($class) {
            if ($estatus == 2) {
                return 'warning';
            }
            return $estatus == 1 ? 'danger' : 'success';
        }

        if ($estatus == 2) {
            if ($isId) {
                return 2;
            } else {
                return 'Anulada';
            }
        }

        if ($this->notas_credito()) {
            $precioNotas = 0;

            foreach ($this->notas_credito() as $notas) {

                //Acumulado de total en notas creditos de la factura.
                $precioNotas += $notas->nota()->total()->total;
            }

            if ($precioNotas > 0 && $this->total()->total > $precioNotas && $estatus == 1 && $precioNotas + $this->pagado() < $this->total()->total) {
                if ($isId) {
                    return 3;
                } else {
                    return "Abierta con nota crédito";
                }
            } elseif ($this->total()->total == $precioNotas) {
                if ($isId) {
                    return 4;
                } else {
                    return "Cerrada con nota crédito";
                }
            } elseif ($precioNotas > 0 && $estatus == 1 && $precioNotas + $this->pagado() >= $this->total()->total) {
                if ($isId) {
                    return 3;
                } else {
                    return "Cerrada con nota crédito";
                }
            }
        }

        if ($isId) {
            return $estatus == 1 ? 1 : 0;
        } else {
            return $estatus == 1 ? 'Abierta' : 'Cerrada';
        }
    }


    public function total()
    {

        // Personalizado para a la hora de ver o descargar pdf que involucran totalidades de facturass... empresa importadora y gestor de partes
        $idEmpresas = [0];
        if(isset(Auth::user()->empresaObj)){
                if(Auth::user()->empresaObj->id == 1){
                if(Auth::user()->empresaObj->nit == 1128464945){   
                    $idEmpresas = [1, 160];  
                }else{
                    $idEmpresas = [optional(auth()->user())->empresa];
                }
            }else{  
                    $idEmpresas = [optional(auth()->user())->empresa];
            }
        }
        


        $totales = array(
            'total' => 0,
            'subtotal' => 0,
            'descuento' => 0,
            'subsub' => 0,
            'imp' => Impuesto::whereIn('empresa', $idEmpresas)
                ->orWhere('empresa', null)
                ->Where('estado', 1)
                ->get(),
            'totalreten' => 0,
            'valImpuesto' => 0,
            'reteIva' => 0,
            'reteFuente' => 0,
            'reteIca' => 0,
            'baseImponible' => 0
        );
        $items = ItemsFactura::where('factura', $this->id)->get();
        $totales["reten"] = Retencion::whereIn('empresa',  $idEmpresas)
            ->orWhere('empresa', null)
            ->Where('estado', 1)
            ->get();
        $result = 0;
        $desc = 0;
        $impuesto = 0;
        $totales["TaxExclusiveAmount"] = 0;


        if ($items) {
            foreach ($items as $item) {
                $result = $item->precio * $item->cant;
                $totales['subtotal'] += $result;

                //SACAR EL DESCUENTO
                if ($item->desc > 0) {
                    $desc = ($result * $item->desc) / 100;
                } else {
                    $desc = 0;
                }

                $totales['descuento'] += $desc;
                $result = $result - $desc;
                $totales['resul'] = $totales['subtotal'] - $totales['descuento'];

                //SACAR EL IMPUESTO

                if ($item->impuesto > 0) {
                    foreach ($totales["imp"] as $key => $imp) {
                        if ($imp->id == $item->id_impuesto) {
                            $impuesto = ($result * $imp->porcentaje) / 100;
                            if (!isset($totales["imp"][$key]->total)) {
                                $totales["imp"][$key]->total = 0;
                            }

                            $totales["imp"][$key]->total += $impuesto;
                            $totales["imp"][$key]->totalprod += $item->total();
                            $totales['baseImponible'] += $totales["imp"][$key]->totalprod - $desc;
                        }
                    }
                }
                //Facturacion electronica obtenemos el TaxExclusiveAmount (total sobre el cual se calculan los ivas de los items)
                if ($item->impuesto != null) {
                    $totales['TaxExclusiveAmount'] += ($item->precio * $item->cant) - $desc;
                }

                if ($item->impuesto_1 > 0) {
                    foreach ($totales["imp"] as $key => $imp) {
                        if ($imp->id == $item->id_impuesto_1) {
                            $impuesto = ($result * $imp->porcentaje) / 100;
                            if (!isset($totales["imp"][$key]->total)) {
                                $totales["imp"][$key]->total = 0;
                            }

                            $totales["imp"][$key]->total += $impuesto;
                            $totales["imp"][$key]->totalprod += $item->total();
                            $totales['baseImponible'] += $totales["imp"][$key]->totalprod - $desc;
                        }
                    }
                }
                if ($item->impuesto_1 != null) {
                    $totales['TaxExclusiveAmount'] += ($item->precio * $item->cant) - $desc;
                }

                if ($item->impuesto_2 > 0) {
                    foreach ($totales["imp"] as $key => $imp) {
                        if ($imp->id == $item->id_impuesto_2) {
                            $impuesto = ($result * $imp->porcentaje) / 100;
                            if (!isset($totales["imp"][$key]->total)) {
                                $totales["imp"][$key]->total = 0;
                            }

                            $totales["imp"][$key]->total += $impuesto;
                            $totales["imp"][$key]->totalprod += $item->total();
                            $totales['baseImponible'] += $totales["imp"][$key]->totalprod - $desc;
                        }
                    }
                }
                if ($item->impuesto_2 != null) {
                    $totales['TaxExclusiveAmount'] += ($item->precio * $item->cant) - $desc;
                }

                if ($item->impuesto_3 > 0) {
                    foreach ($totales["imp"] as $key => $imp) {
                        if ($imp->id == $item->id_impuesto_3) {
                            $impuesto = ($result * $imp->porcentaje) / 100;
                            if (!isset($totales["imp"][$key]->total)) {
                                $totales["imp"][$key]->total = 0;
                            }

                            $totales["imp"][$key]->total += $impuesto;
                            $totales["imp"][$key]->totalprod += $item->total();
                            $totales['baseImponible'] += $totales["imp"][$key]->totalprod - $desc;
                        }
                    }
                }
                if ($item->impuesto_3 != null) {
                    $totales['TaxExclusiveAmount'] += ($item->precio * $item->cant) - $desc;
                }

                if ($item->impuesto_4 > 0) {
                    foreach ($totales["imp"] as $key => $imp) {
                        if ($imp->id == $item->id_impuesto_4) {
                            $impuesto = ($result * $imp->porcentaje) / 100;
                            if (!isset($totales["imp"][$key]->total)) {
                                $totales["imp"][$key]->total = 0;
                            }

                            $totales["imp"][$key]->total += $impuesto;
                            $totales["imp"][$key]->totalprod += $item->total();
                            $totales['baseImponible'] += $totales["imp"][$key]->totalprod - $desc;
                        }
                    }
                }
                if ($item->impuesto_4 != null) {
                    $totales['TaxExclusiveAmount'] += ($item->precio * $item->cant) - $desc;
                }

                if ($item->impuesto_5 > 0) {
                    foreach ($totales["imp"] as $key => $imp) {
                        if ($imp->id == $item->id_impuesto_5) {
                            $impuesto = ($result * $imp->porcentaje) / 100;
                            if (!isset($totales["imp"][$key]->total)) {
                                $totales["imp"][$key]->total = 0;
                            }

                            $totales["imp"][$key]->total += $impuesto;
                            $totales["imp"][$key]->totalprod += $item->total();
                            $totales['baseImponible'] += $totales["imp"][$key]->totalprod - $desc;
                        }
                    }
                }
                if ($item->impuesto_5 != null) {
                    $totales['TaxExclusiveAmount'] += ($item->precio * $item->cant) - $desc;
                }

                if ($item->impuesto_6 > 0) {
                    foreach ($totales["imp"] as $key => $imp) {
                        if ($imp->id == $item->id_impuesto_6) {
                            $impuesto = ($result * $imp->porcentaje) / 100;
                            if (!isset($totales["imp"][$key]->total)) {
                                $totales["imp"][$key]->total = 0;
                            }

                            $totales["imp"][$key]->total += $impuesto;
                            $totales["imp"][$key]->totalprod += $item->total();
                            $totales['baseImponible'] += $totales["imp"][$key]->totalprod - $desc;
                        }
                    }
                }
                if ($item->impuesto_6 != null) {
                    $totales['TaxExclusiveAmount'] += ($item->precio * $item->cant) - $desc;
                }

                if ($item->impuesto_7 > 0) {
                    foreach ($totales["imp"] as $key => $imp) {
                        if ($imp->id == $item->id_impuesto_7) {
                            $impuesto = ($result * $imp->porcentaje) / 100;
                            if (!isset($totales["imp"][$key]->total)) {
                                $totales["imp"][$key]->total = 0;
                            }

                            $totales["imp"][$key]->total += $impuesto;
                            $totales["imp"][$key]->totalprod += $item->total();
                            $totales['baseImponible'] += $totales["imp"][$key]->totalprod - $desc;
                        }
                    }
                }
                if ($item->impuesto_7 != null) {
                    $totales['TaxExclusiveAmount'] += ($item->precio * $item->cant) - $desc;
                }
            }


            if (FacturaRetencion::where('factura', $this->id)->count() > 0) {
                $items = FacturaRetencion::select('factura_retenciones.*', 'retenciones.tipo as id_tipo')
                    ->join('retenciones', 'retenciones.id', '=', 'factura_retenciones.id_retencion')
                    ->where('factura', $this->id)->get();

                foreach ($items as $item) {
                    foreach ($totales["reten"] as $key => $reten) {
                        if ($reten->id == $item->id_retencion) {
                            if (!isset($totales["reten"][$key]->total)) {
                                $totales["reten"][$key]->total = 0;
                            }
                            $totales["reten"][$key]->total += $item->valor;
                            $totales['totalreten'] += $item->valor;

                            $tipo = $item->id_tipo;
                            switch ($tipo) {

                                case 1:
                                    $totales['reteIva'] += $item->valor;
                                    break;

                                case 2:
                                    $totales['reteFuente'] += $item->valor;
                                    break;

                                case 3:
                                    $totales['reteIca'] += $item->valor;
                                    break;
                            }
                        }
                    }
                }
            }


            //A este metodo se le quita el this->retenido() ya que este metodo se encarga de aplicar las retenciones a los ingresos también.
            $totales['total'] = $totales['subsub'] = $totales['subtotal'] - $totales['descuento'] - $this->retenido_factura();


            foreach ($totales["imp"] as $key => $imp) {
                $totales['total'] += Funcion::precision($imp->total);
                $totales['valImpuesto'] += Funcion::precision($imp->total);
            }

            //Agregamos una precisión sobre el valor total de la factura
            $totales['total'] = Funcion::precision($totales['total']);

            return (object) $totales;
        }
    }

    public function totalAPI($empresaId){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0, 'imp'=>Impuesto::where('empresa',$empresaId)->orWhere('empresa', null)->Where('estado', 1)->get(), 'totalreten'=>0);
        $items=ItemsFactura::where('factura',$this->id)->get();
        $totales["reten"]=Retencion::where('empresa',$empresaId)->orWhere('empresa', null)->Where('estado', 1)->get();
        $result=0; $desc=0; $impuesto=0;
        foreach ($items as $item) {
            $result=$item->precio*$item->cant;
            $totales['subtotal']+=$result;

            //SACAR EL DESCUENTO
            if ($item->desc>0) {
                $desc=($result*$item->desc)/100;
            }
            else{ $desc=0; }

            $totales['descuento']+=$desc;
            $result=$result-$desc;
            $totales['resul'] = $totales['subtotal'] - $totales['descuento'];

            //SACAR EL IMPUESTO
            if ($item->impuesto>0) {
                foreach ($totales["imp"] as $key => $imp) {
                    if ($imp->id==$item->id_impuesto) {
                        $impuesto=($result*$imp->porcentaje)/100;
                        if (!isset($totales["imp"][$key]->total)) {
                            $totales["imp"][$key]->total=0;
                        }
                        $totales["imp"][$key]->total+=$impuesto;
                    }
                }
            }
        }

        if (FacturaRetencion::where('factura',$this->id)->count()>0) {
            $items=FacturaRetencion::where('factura',$this->id)->get();

            foreach ($items as $item) {
                foreach ($totales["reten"] as $key => $reten) {
                    if ($reten->id==$item->id_retencion) {
                        if (!isset($totales["reten"][$key]->total)) {
                            $totales["reten"][$key]->total=0;
                        }
                        $totales["reten"][$key]->total+=$item->valor;
                        $totales['totalreten']+=$item->valor;

                    }
                }
            }
        }

        $totales['total']=$totales['subsub']=$totales['subtotal']-$totales['descuento'] - $this->retenido_factura()
            - $this->retenido();
        foreach ($totales["imp"] as $key => $imp) {
            $totales['total']+=$imp->total;
        }
        return (object) $totales;

    }

    public function plazo(){
        if ($this->plazo=='n') {
            return 'Vencimiento Manual';
        }
        return TerminosPago::where('id',$this->plazo)->first()->nombre;
    }

    public function vendedor(){
        if (!$this->vendedor) {
            return '';
        }
        return Vendedor::where('id',$this->vendedor)->first()->nombre;
    }

    public function pagado($sumarRetencion = true){
        $total=IngresosFactura::
        where('factura',$this->id)->
        whereRaw('(SELECT estatus FROM ingresos where id = ingresos_factura.ingreso) <> 2 ')->
        sum('pago');

        $totalAnticipo = PucMovimiento::
        where('tipo_comprobante',3)->
        where('recibocaja_id','!=',null)->
        where('documento_id',$this->id)->
        sum('debito');

        if ($sumarRetencion) {
            $total += $this->retenido(); //le sumamos la retención del ingreso (solamente), ya que no afecta la factura de venta
        }

        $total = $total + $totalAnticipo;

        //$total+=$this->retenido();
        return $total;
    }

    public function retenido($por_factura = false){
        $ingresos = IngresosFactura::where('factura', $this->id)->whereRaw('(SELECT estatus FROM ingresos where id = ingresos_factura.ingreso) <> 2 ')->get();
        $total = 0;
        foreach ($ingresos as $ingreso) {
            $total += (float)$ingreso->retencion();
        }
        if ($por_factura) {
            $total += $this->retenido_factura();
        }
        return $total;
    }

    public function retenido_factura(){
        $retenciones=FacturaRetencion::where('factura',$this->id)->get();
        $total=0;
        $id=array();
        foreach ($retenciones as $retencion) {
            $total+=(float)$retencion->valor;
            $id[]=$retencion->id_retencion;
        }

        return $total;
    }

    public function retenciones(){
        $ingresos=IngresosRetenciones::where('factura',$this->id)->get();
        $retencion=" ";
        foreach ($ingresos as $key => $ingreso) {
             $retencion .= " ".$ingreso->retencion()->nombre.' ('.$ingreso->retencion()->porcentaje.'%) '.Auth::user()->empresa()->moneda .Funcion::Parsear($ingreso->valor).($key<$ingresos->count()-1?",":'');
            
        }
        return $retencion;
    }

    public function porpagar(){
        $porpagar = Funcion::precision($this->total()->total);
        $pagado = $this->pagado();
        $total = abs($porpagar - $pagado - $this->devoluciones());
        if($pagado > $porpagar){
            return 0;
        }
        return $total;
    }

    public function porpagarAPI($empresa){
        $porpagar = Funcion::precisionAPI($this->totalAPI($empresa)->total, $empresa);
        $pagado = $this->pagado();
        $total = abs($porpagar - $pagado - $this->devoluciones());
        if($pagado > $porpagar){
            return 0;
        }
        return $total;
    }

    public function devoluciones(){
        return NotaCreditoFactura::where('factura',$this->id)->sum('pago');
    }

    //Obtenemos el valor pagado de la factura en la devolucion(nota credito), ya que se puede abonar y hacer varios pagos en diferentes notas creditos
    public function devolucionPagado()
    {
        if (NotaCreditoFactura::where('factura', $this->id)->count() > 0) {
            $notasC = NotaCreditoFactura::where('factura', $this->id)->get();

            $pagado = 0;

            foreach ($notasC as $notaC) {
                $pagado = $pagado + $notaC->nota()->total()->total;
            }

            return $pagado;
        } else {
            return 0;
        }
    }

    public function pagos($cont=false){
        if ($cont) {
            return IngresosFactura::where('factura',$this->id)->count();
        }
        return IngresosFactura::where('factura',$this->id)->get();
    }

    public function lista_precios(){
        $lista=ListaPrecios::where('empresa',Auth::user()->empresa)->where('id', $this->lista_precios)->first();
        if (!$lista) { return ''; }
        return $lista->nombre();
    }
    public function bodega(){
        $bodega=Bodega::where('empresa',Auth::user()->empresa)->where('id', $this->bodega)->first();
        if (!$bodega) { return ''; }
        return $bodega->bodega;
    }

    public function retenciones_previas(){
        $ingresos=IngresosFactura::where('factura',$this->id)->get();
        $total=0;
        $id=array();
        foreach ($ingresos as $ingreso) {
            $retenciones=$ingreso->retenciones();
            foreach ($retenciones as $retencion) {
                $id[]=$retencion->id_retencion;
            }
        }

        return json_encode($id);
    }

    public function retenciones_previas_actual($ingreso){
        $ingresos=IngresosFactura::where('factura',$this->id)->where('ingreso', '<>', $ingreso)->get();
        $total=0;
        $id=array();
        foreach ($ingresos as $ingreso) {
            $retenciones=$ingreso->retenciones();
            foreach ($retenciones as $retencion) {
                $id[]=$retencion->id_retencion;
            }
        }

        return json_encode($id);

    }

    public function impuestos_totales(){
        $total=0;
        foreach ($this->total()->imp as $value) {
            if ($value->tipo==1) {
                $total+=$value->total;
            }
        }
        return  $total;
    }

    public function impuestos_totalesFe(){
        $total=0;
        foreach ($this->totalAPI($this->empresa)->imp as $value) {
            if ($value->tipo==1) {
                $total+=$value->total;
            }
        }
        return  $total;
    }

    public function notas_credito($cont=false){
        $notas=NotaCreditoFactura::where('factura', $this->id);
        if ($cont) {
            return $notas->count();
        }

        return $notas->get();

    }

    public function info_cufe($id, $impTotal)
    {
        $factura = Factura::find($id);
        $technicalKey = "";

        if ($factura->technicalkey == null) {
            $technicalKey = Auth::user()->empresaObj->technicalkey;
        } else {
            $technicalKey = $factura->technicalkey;
        }

        //Validacion de desarrollo nuevo solamente para facturas nuevas desde el 15 de dic de 2021.
        if (Carbon::parse($factura->created_at)->format('Y-m-d') >= "2021-12-15") {
            if ($factura->tiempo_creacion) {
                $horaFac = $factura->tiempo_creacion;
            } else {
                $horaFac = $factura->created_at;
            }
        } else {
            $horaFac = $factura->created_at;
            $factura->fecha = $factura->created_at;
        }

        $totalIva = 0.00;
        $totalInc = 0.00;

        foreach ($factura->total()->imp as $key => $imp) {
            if (isset($imp->total) && $imp->tipo == 1) {
                $totalIva = $impTotal;
            } elseif (isset($imp->total) && $imp->tipo == 3) {
                $totalInc = $impTotal;
            }
        }


        $infoCufe = [
            'Numfac' => $factura->codigo,
            'FecFac' => Carbon::parse($factura->fecha)->format('Y-m-d'),
            'HorFac' => Carbon::parse($horaFac)->format('H:i:s') . '-05:00',
            'ValFac' => number_format($factura->total()->subtotal - $factura->total()->descuento, 2, '.', ''),
            'CodImp' => '01',
            'ValImp' => number_format($totalIva, 2, '.', ''),
            'CodImp2' => '04',
            'ValImp2' => number_format($totalInc, 2, '.', ''),
            'CodImp3' => '03',
            'ValImp3' => '0.00',
            'ValTot' => number_format($factura->total()->subtotal + $factura->impuestos_totales() - $factura->total()->descuento, 2, '.', ''),
            'NitFE'  => Auth::user()->empresaObj->nit,
            'NumAdq' => $factura->cliente()->nit,
            'ClvTec' => $technicalKey,
            'TipoAmb' => 1,
        ];

        $CUFE = $infoCufe['Numfac'] . $infoCufe['FecFac'] . $infoCufe['HorFac'] . $infoCufe['ValFac'] . $infoCufe['CodImp'] . $infoCufe['ValImp'] . $infoCufe['CodImp2'] . $infoCufe['ValImp2'] . $infoCufe['CodImp3'] . $infoCufe['ValImp3'] . $infoCufe['ValTot'] . $infoCufe['NitFE'] . $infoCufe['NumAdq'] . $infoCufe['ClvTec'] . $infoCufe['TipoAmb'];

        return hash('sha384', $CUFE);
    }

    public function info_cufeAPI($id, $impTotal, $empresa)
    {
        $factura = Factura::find($id);
        $infoCufe = [
            'Numfac' => $factura->codigo,
            'FecFac' => Carbon::parse($factura->created_at)->format('Y-m-d'),
            'HorFac' => Carbon::parse($factura->created_at)->format('H:i:s').'-05:00',
            'ValFac' => $factura->totalAPI($empresa)->subtotal.'.00',
            'CodImp' => '01',
            'ValImp' => $impTotal.'.00',
            'CodImp2'=> '04',
            'ValImp2'=> '0.00',
            'CodImp3'=> '03',
            'ValImp3'=> '0.00',
            'ValTot' => number_format($factura->totalAPI($empresa)->total, 2, '.', ''),
            'NitFE'  => Empresa::find($factura->empresa)->nit,
            'NumAdq' => $factura->cliente()->nit,
            'ClvTec' => 'fc8eac422eba16e22ffd8c6f94b3f40a6e38162c',
            'TipoAmb'=> 1,
        ];

        $CUFE = $infoCufe['Numfac'].$infoCufe['FecFac'].$infoCufe['HorFac'].$infoCufe['ValFac'].$infoCufe['CodImp'].$infoCufe['ValImp'].$infoCufe['CodImp2'].$infoCufe['ValImp2'].$infoCufe['CodImp3'].$infoCufe['ValImp3'].$infoCufe['ValTot'].$infoCufe['NitFE'].$infoCufe['NumAdq'].$infoCufe['ClvTec'].$infoCufe['TipoAmb'];

        return hash('sha384',$CUFE);
    }


public function forma_pago()
{
    $terminos=TerminosPago::find($this->plazo);

    if ($terminos) {
        if ($terminos->dias > 0) {
            //cbc:PaymentMeans/ID  2 = Crédito
            $formapago = 2;
        }
        elseif($terminos->dias == 0)
        {
            //cbc:PaymentMeans/ID  1 = De Contado
            $formapago = 1;
        }
    }else
    {
        //-- Si no hay un plazo es por que se escogio manual, y obviamente se va a escoger un fecha futura entonces la forma de pago será a credito
        //cbc:PaymentMeans/ID  2 = Crédito
        $formapago = 2;
    }
    return $formapago;
}

    public function itemsFactura()
    {
        return $this->hasMany(ItemsFactura::class,'factura','id');
    }

    public function getTypeNameAttribute()
    {
        switch ($this->tipo){
            case 1:
                return 'Factura de venta';
            case 2:
                return 'Cotización';
            case 3:
             return 'Cuenta de cobro';
            default:
                return 'Cotización';
        }
    }
    
    public function getDateAttribute()
    {
        return [
            'primera' => Factura::where('empresa', Auth::user()->empresa)->whereNotNull('fecha')
                ->where('tipo','<>',2)
                ->where('estatus','<>',2)
                ->get()
                ->first()->fecha,
            'ultima' => Factura::where('empresa', Auth::user()->empresa)
                ->where('tipo','<>',2)
                ->where('estatus','<>',2)
                ->get()
                ->last()->fecha
        ];
    }
    
    public function deta(){
        return Factura::where('id', $this->factura)->first();
    }
    
    public function estadoCuenta(){
        
        $estadoCuenta = array('saldoMesAnterior' => 0, 'saldoMesActual' => 0, 'equipoCuota' => 0, 'servicioAdicional' => 0, 'total' => 0);
        
        $fechaActual = date("Y-m-d", strtotime(Carbon::now()));
        $saldoMesAnterior=0;
        $saldoMesActual=0;
        
        /*>>>>>>>>>>>>>>>>>>>>>>>>>> Saldo mes Anterior <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
        
        //traemos todas las facturas que el vencimiento haya pasado la fecha actual.
        $facturasVencidas = Factura::where('cliente',$this->cliente)->where('vencimiento','<',$fechaActual)->where('estatus','!=',2)->get();
          
        //sumamos todo lo que deba el cliente despues de la fecha de vencimiento
        foreach($facturasVencidas as $vencida){
            $saldoMesAnterior+=$vencida->porpagar();
        }
        
        $estadoCuenta['saldoMesAnterior'] = $saldoMesAnterior;
        
        /*>>>>>>>>>>>>>>>>>>>>>>>>>> Saldo mes Actual <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
        
        $facturasActuales = Factura::where('cliente',$this->cliente)->where('vencimiento','>',$fechaActual)->where('estatus','!=',2)->get();
        
        //sumamos todo lo que deba el cliente despues de la fecha de vencimiento
        foreach($facturasActuales as $actual){
            $saldoMesActual+=$actual->porpagar();
        }
        
        $estadoCuenta['saldoMesActual'] = $saldoMesActual;
        
        return (object) $estadoCuenta;
    }

    public function estadoCuentaAPI($empresa){

        $estadoCuenta = array('saldoMesAnterior' => 0, 'saldoMesActual' => 0, 'equipoCuota' => 0, 'servicioAdicional' => 0, 'total' => 0);

        $fechaActual = date("Y-m-d", strtotime(Carbon::now()));
        $saldoMesAnterior=0;
        $saldoMesActual=0;

        /*>>>>>>>>>>>>>>>>>>>>>>>>>> Saldo mes Anterior <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/

        //traemos todas las facturas que el vencimiento haya pasado la fecha actual.
        $facturasVencidas = Factura::where('cliente',$this->cliente)->where('vencimiento','<',$fechaActual)->where('estatus','!=',2)->get();

        //sumamos todo lo que deba el cliente despues de la fecha de vencimiento
        foreach($facturasVencidas as $vencida){
            $saldoMesAnterior+=$vencida->porpagarAPI($empresa);
        }

        $estadoCuenta['saldoMesAnterior'] = $saldoMesAnterior;

        /*>>>>>>>>>>>>>>>>>>>>>>>>>> Saldo mes Actual <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/

        $facturasActuales = Factura::where('cliente',$this->cliente)->where('vencimiento','>',$fechaActual)->where('estatus','!=',2)->get();

        //sumamos todo lo que deba el cliente despues de la fecha de vencimiento
        foreach($facturasActuales as $actual){
            $saldoMesActual+=$actual->porpagarAPI($empresa);
        }

        $estadoCuenta['saldoMesActual'] = $saldoMesActual;

        return (object) $estadoCuenta;
    }

    /**
     * Retorna si un cliente puede crear factura electrónica o no.
     *
     * @var array
     */
    public static function booleanFacturaElectronica($clienteId){
    //Validamos que la persona tenga un contrato de lo contrario no podremos crear una factura electrónica.
    $contratoPersona = Contrato::where('client_id',$clienteId)->first();

    if($contratoPersona){

        /* 
        Vamos a evaluar la fecha de corte tomando el ultimo contrato y contando los dias de corte que tenga el contrato.
        para saber si se puede generar un factura electrónica de ese cliente.
        */
        $diasCorte = GrupoCorte::join('contracts as c','c.grupo_corte','=','grupos_corte.id')
        ->where('c.client_id',$clienteId)
        ->select('grupos_corte.*')
        ->first();

        //Obtenemos la ultima factura generada para ese cliente (si es que tiene).
        $fechaActual = Carbon::now()->format('Y-m');
        $lastFacturaFecha = false;
        $fechaPermitida = true;
        if(Factura::where('cliente', $clienteId)->orderby('id','desc')->first()){
            
            $lastFacturaFecha = Factura::where('cliente', $clienteId)->orderby('id','desc')->first()->fecha;
            $lastFacturaFecha = Carbon::parse($lastFacturaFecha)->format('Y-m');
        
            if($lastFacturaFecha != $fechaActual){
                return response()->json(true);
            }else{
                return response()->json(false);
            }
        
        //si no ingresa a este apartado ya que no cuenta con ninguna factura creada pero si contrato.
        }else{
            return response()->json(true);
        }
        
        }
        else{
            return response()->json(false);
        }
    }

    public function redondeo($total)
    {
        $decimal = explode(".", $total);
        if (isset($decimal[1]) && $decimal[1] > 50) {
            $total = round($total);
        }
        return $total;
    }

    public function isItemSinIva()
    {

        $items = ItemsFactura::where('factura', $this->id)->get();

        foreach ($items as $item) {
            if ($item->id_impuesto == 0 || $item->impuesto == 0) {
                return true;
            }
        }


        return false;
    }

    public function itemImpuestoSingular()
    {
        $text = '';
        $ivas =  array();

        $impuesto = Impuesto::where('id', $this->id_impuesto)->first();
        if ($impuesto) {
            array_push($ivas, ["imp0" => $impuesto->porcentaje]);
        }
        
        if ($impuesto) {
            return $ivas;
        }
        return '';
    }

    public function periodoCobrado($tirilla=false){

        $grupo = Contrato::join('grupos_corte as gc', 'gc.id', '=', 'contracts.grupo_corte')->
        where('contracts.id',$this->contrato_id)
        ->select('gc.*')->first();

        if(!$grupo){
            $grupo = Contrato::join('grupos_corte as gc', 'gc.id', '=', 'contracts.grupo_corte')->
            where('client_id',$this->cliente)
            ->select('gc.*')->first();
        }
        
        $empresa = Empresa::find($this->empresa);
        
        
        if($grupo){
            $empresa = Empresa::find($this->empresa);
            $mesInicioCorte = $mesFinCorte = Carbon::parse($this->fecha)->format('m');
            $yearInicioCorte = $yearFinCorte = Carbon::parse($this->fecha)->format('Y');
        
            //Calculos para los inicios de corte
            if($mesInicioCorte == 1){
                $mesInicioCorte = 12;
                $yearInicioCorte = $yearInicioCorte - 1;
            }else{
                $mesInicioCorte = $mesInicioCorte - 1;
            }

            //Calculos para los finales de corte
            if($mesFinCorte == 12){
                $mesFinCorte = 1;
                $yearFinCorte = $yearFinCorte + 1;
            }else{
                $mesFinCorte = $mesFinCorte + 1;
            }
        
            /*
                validamos que si la fecha de corte es mas grande que el ultimo dia del mes anterior
                (caso con los meses que tiene 28, 29 días y la fec. corte es el 30)
                entonces la fecha de corte pasa a ser el ultimo día del mes.
            */
            $diaValidar = "1-".$mesInicioCorte."-".$yearInicioCorte;
            $diaValidar = Carbon::parse($diaValidar)->endOfMonth()->format('d');

            $diaFinValidar = "1-".$mesFinCorte."-".$yearFinCorte;
            $diaFinValidar = Carbon::parse($diaFinValidar)->endOfMonth()->format('d');
            
            $diaInicioCorte = $diaFinCorte = $grupo->fecha_corte;
        
            
            if($grupo->fecha_corte > $diaValidar){
                $diaInicioCorte = $diaValidar;
            }

            if($grupo->fecha_corte > $diaFinValidar){
                 $diaFinCorte = $diaFinValidar;
            }

            //construimos el inicio del corte tomando la fecha de la factura (mes y año) y el grupo de corte (el dia)
            $fechaInicio = $inicioCorte = $diaInicioCorte . "-" . $mesInicioCorte . "-" . $yearInicioCorte;
            
            //obtenemos el mes y año de la factura actual
            $mesYearFactura = Carbon::parse($this->fecha)->format('m-Y');
            
            $validateFin = "01-".$mesYearFactura;
            $validateFin= Carbon::parse($validateFin)->endOfMonth()->format('d');
            
            if($validateFin < $diaFinCorte && $diaFinCorte == 30 || $validateFin < $diaFinCorte && $diaFinCorte == 31){
                $diaFinCorte = $validateFin;
            }
            
            //fecha fin corte es la combiancion del grupo de corte, osea la fecha_corte y mes factura es el mes año de la factura
            $fechaFin = $finCorte = $diaFinCorte . "-" . $mesYearFactura;

            //Construimos una fecha con el grupo de corte y mes y año de la factura, tambien formateamos la fecha de la factura completamente
            $fechaFactura = Carbon::parse($this->fecha);
            $inicio = $grupo->fecha_corte . "-" . $mesYearFactura;
            $inicio = Carbon::parse($inicio);

            $diasCobrados = 0;

            $fechaInicio = Carbon::parse($fechaInicio);
            //sumamos un dia ya que el corte es un 30, empezaria desde el siguiente dia
            $inicioCorte = $fechaInicio->addDay();
            
            if(Carbon::parse($fechaInicio)->format('d') == 31){
                $inicioCorte = $fechaInicio->addDay();
            }
            
            $fechaFin    = Carbon::parse($fechaFin);
            
            /* Validacion de mes anticipado o mes vencido */
            $diaFac = Carbon::parse($this->fecha)->format('d');
            
            //si este caso ocurre es por que tengo que cobrar el mes pasado
            
            // if($diaFac < $grupo->fecha_factura && $empresa->periodo_facturacion == 2){
            //     $finCorte = Carbon::parse($finCorte)->subMonth();
            //     $inicioCorte =  $inicioCorte->subMonth();
            // }
            //se comenta por que etsaba creando conflicto
            
            /* Validacion de mes anticipado o mes vencido */
            $finCorte = Carbon::parse($finCorte)->toFormattedDateString();
            $inicioCorte = Carbon::parse($inicioCorte)->toFormattedDateString();
            
            $mensaje = ($tirilla) ? $inicioCorte." - ".$finCorte : "Periodo cobrado del " . $inicioCorte . " Al " . $finCorte;
            

            //Primero analizamos si es la primer factura del contrato que vamos a generar
            if($this->contrato_id != null){

                $factura = Factura::where('empresa',$this->empresa)->where('contrato_id',$this->contrato_id)->orderBy('id','ASC')->first();

                /*
                De esta manera nos aseguramos que se esté hablando de la misma y primer factura y entonces cobraremos
                los primeros dias de uso dependiendo de la creacion del contrato
                también debemos tener la opción de prorrateo activa en el menú de configuración.
                */
      
                if($factura->id == $this->id && $empresa->prorrateo == 1){
          
                    //Buscamos el contrato al que esta asociada la factura
                    $contrato = Contrato::find($this->contrato_id);

                    $yearContrato = Carbon::parse($contrato->created_at)->format('Y');
                    $mesContrato = Carbon::parse($contrato->created_at)->format('m');
                    $diaContrato = Carbon::parse($contrato->created_at)->format('d');

                    $fechaContrato = $yearContrato . "-" . $mesContrato . "-" . $diaContrato;
                    $fechaContrato = Carbon::parse($fechaContrato);

                     /*
                        para calcular la fecha fin tenemos que tener en cuenta que los cortes se pueden generar los 15
                        y el contrato el 25, entonces es mayor el contrato y se tiene que tomar la fecha fin del siguiente mes
                    */
                    if($diaContrato > $grupo->fecha_corte){

                        if(($mesContrato+1) == 13){
                            $fechaFin = (intval($yearContrato) + 1) . "-" . "01" . "-" . $grupo->fecha_corte;
                        }else{
                            $fechaFin = $yearContrato . "-" . ($mesContrato+1) . "-" .  $grupo->fecha_corte;
                        }

                    }else{
                        $fechaFin = $yearContrato . "-" . $mesContrato . "-" .  $grupo->fecha_corte;
                    }
                
                    $diasCobrados = $fechaContrato->diffInDays($fechaFin);

                    /*
                        si la fecha no está entre el rango de la creacion del contrato y la fecha de corte entonces cojemos esos dias de
                        entre: creacion contrato difff fecha corte + el siguiente dia de la fecha de corte hasta la fecha de la factura
                        
                        si entra al if entonces ya tenemos la suma de los dias hasta la fecha de corte ahora sumamos los otros días del siguiente
                        dia 
                    */
                    if($diaFac < $diaContrato && $diaFac < $grupo->fecha_corte && $grupo->fecha_corte > $diaContrato){
                            $fechaInicioNuevoCorte = Carbon::parse($fechaFin)->addDay();
                            $diasFacturaNuevo = $fechaInicioNuevoCorte->diffInDays($this->fecha);
                            $diasCobrados+=$diasFacturaNuevo;
                    }

                    if($diasCobrados == 0){return 30;}
                    if($diasCobrados > 30){$diasCobrados=30;}
                    $mensaje.= ($tirilla) ? "" : " total días cobrados: " . $diasCobrados;
                }else{
                    //Si no se trata de la primer factura del contrato entonces hacemos el calculo con el grupo de corte normal (periodo completo)
                    $diasCobrados = $fechaInicio->diffInDays($fechaFin);
                    if($diasCobrados == 0){return 30;}
                    if($diasCobrados >= 27){$diasCobrados=30;}
                    $mensaje.= ($tirilla) ? "" : " total días cobrados: " . $diasCobrados;
                }
            }
            return $mensaje;
        }
    }
    
public function diasCobradosProrrateo(){

        $grupo = Contrato::join('grupos_corte as gc', 'gc.id', '=', 'contracts.grupo_corte')->
    where('contracts.id',$this->contrato_id)
    ->select('gc.*')->first();

    if(!$grupo){
        $grupo = Contrato::join('grupos_corte as gc', 'gc.id', '=', 'contracts.grupo_corte')->
        where('client_id',$this->cliente)
        ->select('gc.*')->first();
    }
    
    $empresa = Empresa::find($this->empresa);
    
    
    if($grupo){
        $empresa = Empresa::find($this->empresa);
        $mesInicioCorte = $mesFinCorte = Carbon::parse($this->fecha)->format('m');
        $yearInicioCorte = $yearFinCorte = Carbon::parse($this->fecha)->format('Y');
    
        //Calculos para los inicios de corte
        if($mesInicioCorte == 1){
            $mesInicioCorte = 12;
            $yearInicioCorte = $yearInicioCorte - 1;
        }else{
            $mesInicioCorte = $mesInicioCorte - 1;
        }

        //Calculos para los finales de corte
        if($mesFinCorte == 12){
            $mesFinCorte = 1;
            $yearFinCorte = $yearFinCorte + 1;
        }else{
            $mesFinCorte = $mesFinCorte + 1;
        }
    
        /*
            validamos que si la fecha de corte es mas grande que el ultimo dia del mes anterior
            (caso con los meses que tiene 28, 29 días y la fec. corte es el 30)
            entonces la fecha de corte pasa a ser el ultimo día del mes.
        */
        $diaValidar = "1-".$mesInicioCorte."-".$yearInicioCorte;
        $diaValidar = Carbon::parse($diaValidar)->endOfMonth()->format('d');

        $diaFinValidar = "1-".$mesFinCorte."-".$yearFinCorte;
        $diaFinValidar = Carbon::parse($diaFinValidar)->endOfMonth()->format('d');
        
        $diaInicioCorte = $diaFinCorte = $grupo->fecha_corte;
    
        
        if($grupo->fecha_corte > $diaValidar){
            $diaInicioCorte = $diaValidar;
        }

        if($grupo->fecha_corte > $diaFinValidar){
            $diaFinCorte = $diaFinValidar;
        }

        //construimos el inicio del corte tomando la fecha de la factura (mes y año) y el grupo de corte (el dia)
        $fechaInicio = $inicioCorte = $diaInicioCorte . "-" . $mesInicioCorte . "-" . $yearInicioCorte;
        
        //obtenemos el mes y año de la factura actual
        $mesYearFactura = Carbon::parse($this->fecha)->format('m-Y');
        
        $validateFin = "01-".$mesYearFactura;
        $validateFin= Carbon::parse($validateFin)->endOfMonth()->format('d');
        
        if($validateFin < $diaFinCorte && $diaFinCorte == 30 || $validateFin < $diaFinCorte && $diaFinCorte == 31){
            $diaFinCorte = $validateFin;
        }
        
        //fecha fin corte es la combiancion del grupo de corte, osea la fecha_corte y mes factura es el mes año de la factura
        $fechaFin = $finCorte = $diaFinCorte . "-" . $mesYearFactura;

        //Construimos una fecha con el grupo de corte y mes y año de la factura, tambien formateamos la fecha de la factura completamente
        $fechaFactura = Carbon::parse($this->fecha);
        $inicio = $grupo->fecha_corte . "-" . $mesYearFactura;
        $inicio = Carbon::parse($inicio);

        $diasCobrados = 0;

        $fechaInicio = Carbon::parse($fechaInicio);
        //sumamos un dia ya que el corte es un 30, empezaria desde el siguiente dia
        $inicioCorte = $fechaInicio->addDay();
        
        if(Carbon::parse($fechaInicio)->format('d') == 31){
            $inicioCorte = $fechaInicio->addDay();
        }
        
        $fechaFin    = Carbon::parse($fechaFin);
        
        /* Validacion de mes anticipado o mes vencido */
        $diaFac = Carbon::parse($this->fecha)->format('d');
        
        //si este caso ocurre es por que tengo que cobrar el mes pasado
        
        // if($diaFac < $grupo->fecha_factura && $empresa->periodo_facturacion == 2){
        //     $finCorte = Carbon::parse($finCorte)->subMonth();
        //     $inicioCorte =  $inicioCorte->subMonth();
        // }
        //se comenta por que etsaba creando conflicto
        
        /* Validacion de mes anticipado o mes vencido */
        $finCorte = Carbon::parse($finCorte)->toFormattedDateString();
        $inicioCorte = Carbon::parse($inicioCorte)->toFormattedDateString();
        
        $mensaje = "";
        

        //Primero analizamos si es la primer factura del contrato que vamos a generar
        if($this->contrato_id != null){

            $factura = Factura::where('empresa',$this->empresa)->where('contrato_id',$this->contrato_id)->orderBy('id','ASC')->first();

            /*
            De esta manera nos aseguramos que se esté hablando de la misma y primer factura y entonces cobraremos
            los primeros dias de uso dependiendo de la creacion del contrato
            también debemos tener la opción de prorrateo activa en el menú de configuración.
            */

            if($factura->id == $this->id && $empresa->prorrateo == 1){
    
                //Buscamos el contrato al que esta asociada la factura
                $contrato = Contrato::find($this->contrato_id);

                $yearContrato = Carbon::parse($contrato->created_at)->format('Y');
                $mesContrato = Carbon::parse($contrato->created_at)->format('m');
                $diaContrato = Carbon::parse($contrato->created_at)->format('d');

                $fechaContrato = $yearContrato . "-" . $mesContrato . "-" . $diaContrato;
                $fechaContrato = Carbon::parse($fechaContrato);

                /*
                    para calcular la fecha fin tenemos que tener en cuenta que los cortes se pueden generar los 15
                    y el contrato el 25, entonces es mayor el contrato y se tiene que tomar la fecha fin del siguiente mes
                */
                if($diaContrato > $grupo->fecha_corte){

                    if(($mesContrato+1) == 13){
                        $fechaFin = (intval($yearContrato) + 1) . "-" . "01" . "-" . $grupo->fecha_corte;
                    }else{
                        $fechaFin = $yearContrato . "-" . ($mesContrato+1) . "-" .  $grupo->fecha_corte;
                    }

                }else{
                    $fechaFin = $yearContrato . "-" . $mesContrato . "-" .  $grupo->fecha_corte;
                }
            
                $diasCobrados = $fechaContrato->diffInDays($fechaFin);

                /*
                    si la fecha no está entre el rango de la creacion del contrato y la fecha de corte entonces cojemos esos dias de
                    entre: creacion contrato difff fecha corte + el siguiente dia de la fecha de corte hasta la fecha de la factura
                    
                    si entra al if entonces ya tenemos la suma de los dias hasta la fecha de corte ahora sumamos los otros días del siguiente
                    dia 
                */
                if($diaFac < $diaContrato && $diaFac < $grupo->fecha_corte && $grupo->fecha_corte > $diaContrato){
                        $fechaInicioNuevoCorte = Carbon::parse($fechaFin)->addDay();
                        $diasFacturaNuevo = $fechaInicioNuevoCorte->diffInDays($this->fecha);
                        $diasCobrados+=$diasFacturaNuevo;
                }

                if($diasCobrados == 0){return 30;}
                if($diasCobrados > 30){$diasCobrados=30;}
                $diasCobrados=$diasCobrados;
            }else{
                //Si no se trata de la primer factura del contrato entonces hacemos el calculo con el grupo de corte normal (periodo completo)
                $diasCobrados = $fechaInicio->diffInDays($fechaFin);
                if($diasCobrados == 0){return 30;}
                if($diasCobrados >= 27){$diasCobrados=30;}
                $diasCobrados=$diasCobrados;
            }
        }
        return $diasCobrados;
    }
}

    public function numeracionFactura(){
        return $this->belongsTo('App\NumeracionFactura','numeracion');
    }

    //metodo que asigna al request (guardar o editar) de una factura
    public function formaPagoRequest($cuenta_id,$idIngreso=null){

        if($idIngreso == null){
            $forma = FormaPago::find($cuenta_id);
    
            if($forma){
                return Puc::find($forma->cuenta_id); 
            }
        //si es igual a cero es por que se trata de un anticipo.
        }else{
            //buscamos la cuenta contable que tiene asociada el ingreso
            $pm= PucMovimiento::where('documento_id',$idIngreso)->where('tipo_comprobante',1)->where('enlace_a',5)->first();

            if($pm){
                return Puc::find($pm->cuenta_id);
            }
        }
    }

    //metodo que busca la forma de pago de una factura 
    public function formaPago(){
        $forma = FormaPago::find($this->cuenta_id);

        if($forma){
            return Puc::find($forma->cuenta_id); 
        }    
    }

    public function contract(){
        
        $contrato = Contrato::find($this->contrato_id);
        if($contrato){
            return $contrato;
        }else {
            $contrato = new stdClass;
            $contrato->contrato_permanencia =false;
            $contrato->server_configuration_id =false;
            return $contrato;
        };
    }

    public function recibosAnticipo($edit = 0){
        //obtenemos los ingresos que tiene un anticpo vigente.
        $ingresosArray=array();
        $ingresosAsociados = array();
        if(count($this->pagos()) > 0){
            foreach ($this->pagos() as $id) {
                $ingresosAsociados[]=$id->ingreso;
            }
        }
        // dd($this->id);
        if($edit){
            $asientosUsados = PucMovimiento::where('enlace_a',4)->whereIn('documento_id',$ingresosAsociados)->get();

            foreach ($asientosUsados as $id) {
                if($id->recibocaja_id != null){
                    $ingresosArray[]=$id->recibocaja_id;
                }
            }
        }
        
        if(count($ingresosArray) > 0){
            $ingresos = Ingreso::where('cliente',$this->cliente)
            ->where('anticipo',1)
            ->where('valor_anticipo','>',0)
            ->orWhereIn('id',$ingresosArray)
            ->get();
        }else{
            $ingresos = Ingreso::where('cliente',$this->cliente)
            ->where('anticipo',1)
            ->where('valor_anticipo','>',0)
            ->get();
        }
       
        return $ingresos;
    }

    public function created_by(){
        return User::find($this->created_by);
    }

    //Método que retorna el saldo a favor usado en la forma de pago (cuando agregamos anticipos a la forma de pago)
    public function saldoFavorUsado(){
        return PucMovimiento::where('documento_id',$this->id)
        ->where('recibocaja_id','!=',null)
        ->sum('debito');
    }

}
