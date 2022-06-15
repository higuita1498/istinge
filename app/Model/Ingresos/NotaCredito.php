<?php

namespace App\Model\Ingresos;

use App\NotaRetencion;
use App\Retencion;
use Illuminate\Database\Eloquent\Model;
use App\Contacto; use App\Impuesto;
use App\Vendedor; use App\TerminosPago; 
use App\Model\Ingresos\ItemsFactura;  
use App\Model\Ingresos\IngresosFactura;
use App\Model\Ingresos\Devoluciones;
use App\Model\Inventario\ListaPrecios; 
use App\Model\Inventario\Bodega;
use Auth;  use DB; 
  
class NotaCredito extends Model
{
    protected $table = "notas_credito";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'empresa', 'tipo', 'cliente', 'fecha', 'observaciones', 'estatus', 'notas', 'lista_precios', 'bodega', 'created_at', 'updated_at','emitida','dian_response','fecha_expedicion',
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
    public function total(){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0,'totalreten'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get());
        $items=ItemsNotaCredito::where('nota',$this->id)->get(); 
        $result=0; $desc=0; $impuesto=0;
        $totales["reten"]= Retencion::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        
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

            //SACAR EL IMPUESTO

            if ($item->impuesto>0) {
                foreach ($totales["imp"] as $key => $imp) {
                    if ($imp->id==$item->id_impuesto) {
                         $impuesto=($result*$imp->porcentaje)/100;
                        if (!isset($totales["imp"][$key]->total)) {
                            $totales["imp"][$key]->total=0;
                        }
                        $totales["imp"][$key]->total+=$impuesto;
                        $totales["imp"][$key]->totalprod+= $item->total();
                    }
                }
            }
            

        }
        $totales['total']=$totales['subsub']=$totales['subtotal']-$totales['descuento'] - $this->retenido_factura();
        $totales['total'] = $totales['total'] - $totales['totalreten'];
        #$totales['totalizado']=$totales['subsub']=$totales['subtotal']-$totales['descuento'] - $this->retenido_factura()
        
        foreach ($totales["imp"] as $key => $imp) {
            $totales['total']+=$imp->total;
           # $totales['totalizado']+=$imp->total;
        }
        
        return (object) $totales;

    }
    
    public function totalAPI($empresaID){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0,'totalreten'=>0, 'imp'=>Impuesto::where('empresa',$empresaID)->orWhere('empresa', null)->Where('estado', 1)->get());
        $items=ItemsNotaCredito::where('nota',$this->id)->get();
        $result=0; $desc=0; $impuesto=0;
        $totales["reten"]= Retencion::where('empresa',$empresaID)->orWhere('empresa', null)->Where('estado', 1)->get();

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

            //SACAR EL IMPUESTO

            if ($item->impuesto>0) {
                foreach ($totales["imp"] as $key => $imp) {
                    if ($imp->id==$item->id_impuesto) {
                        $impuesto=($result*$imp->porcentaje)/100;
                        if (!isset($totales["imp"][$key]->total)) {
                            $totales["imp"][$key]->total=0;
                        }
                        $totales["imp"][$key]->total+=$impuesto;
                        $totales["imp"][$key]->totalprod+= $item->total();
                    }
                }
            }

            if (NotaRetencion::where('notas',$this->id)->count()>0) {
                $items=NotaRetencion::where('notas',$this->id)->get();

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

        }
        $totales['total']=$totales['subsub']=$totales['subtotal']-$totales['descuento'] - $this->retenido_factura();
        #$totales['totalizado']=$totales['subsub']=$totales['subtotal']-$totales['descuento'] - $this->retenido_factura()
        ;
        foreach ($totales["imp"] as $key => $imp) {
            $totales['total']+=$imp->total;
            # $totales['totalizado']+=$imp->total;
        }

        return (object) $totales;

    }

    public function pagado(){
        $total=IngresosFactura::where('factura',$this->id)->whereRaw('(SELECT estatus FROM ingresos where id = ingresos_factura.ingreso) <> 2 ')->sum('pago');
        //$total+=$this->retenido();
        return $total;
    }

    public function tipo(){
        if ($this->tipo) {
           return DB::table('tipos_nota_credito')->where('id', $this->tipo)->first()->tipo;
        }
    }
    
    public function factura_detalle(){
        $facturas=NotaCreditoFactura::where('nota',$this->id)->get();
        $factura="";
        foreach ($facturas as $key => $value) {
            $factura.=$value->factura()->codigo.",";
        }

        return substr($factura, 0, -1);
        
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

    public function por_aplicar(){
        $facturas=NotaCreditoFactura::where('nota',$this->id)->sum('pago');
        $devoluciones=Devoluciones::where('nota',$this->id)->sum('monto');
        #return $facturas-$devoluciones;
        return  $facturas + $devoluciones;
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

    public function retenido($por_factura = false){
        $ingresos=IngresosFactura::where('factura',$this->id)->whereRaw('(SELECT estatus FROM ingresos where id = ingresos_factura.ingreso) <> 2 ')->get();
        $total=0;
        foreach ($ingresos as $ingreso) {
            $total+=(float)$ingreso->retencion();
        }
        if($por_factura)
        {
            $total += $this->retenido_factura();
        }
        return $total;
    }

    public function retenido_factura(){
        $retenciones = NotaRetencion::where('notas', $this->id)->get();
        $total=0;
        $id=array();
        foreach ($retenciones as $retencion) {
            $total+=(float)$retencion->valor;
            $id[]=$retencion->id_retencion;
        }

        return $total;
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

        $items = ItemsNotaCredito::where('nota', $this->id)->get();


        foreach ($items as $item) {
            if ($item->id_impuesto == 0) {
                return true;
            }
        }

        return false;
    }

    public function modelDetalle()
    {
        return NotaCreditoFactura::where('nota', $this->id)->first();
    }

    public function emitida($class = false){
        if($class){
            return ($this->emitida == 0) ? 'danger' : 'success';
        }
        return ($this->emitida == 0) ? 'No Emitida' : 'Emitida';
    }
}   
