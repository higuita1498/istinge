<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contacto; use App\Impuesto;
use App\TerminosPago; use App\Vendedor;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\IngresosFactura;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\Bodega;
use Auth; use DB;
class Cotizacion extends Model
{
    protected $table = "factura";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'cot_nro', 'empresa','codigo',  'numeracion', 'vendedor', 'tipo', 'cliente', 'fecha', 'vencimiento', 'observaciones', 'estatus', 'notas', 'plazo', 'created_at', 'updated_at', 'term_cond', 'facnotas', 'lista_precios', 'bodega'
    ];

    public function cliente(){
        if ($this->cliente) {
            return Contacto::where('id',$this->cliente)->first();
        }
        else{
            return DB::table('factura_contacto')->where('factura',$this->id)->first();
        }

    }

    public function contactos_email($email){
        $clientes=Contacto::where('empresa', Auth::user()->empresa)->where('email', $email)->get();
        $cadena='';
        foreach ($clientes as $key => $cliente) {
            $cadena.=" ".$cliente->nombre.',';
        }
        return substr($cadena, 0, -1);
    }
    public function estatus($class=false, $estatus=null, $open=null){
        if (!$estatus) {
            $estatus=$this->estatus;
        }
        if ($class) {
            return $estatus==2?'danger':'success';
        }
        if ($estatus==2) {
            return 'Por Cotizar';
        }
        if ($open){
            return $this->estatus == 2 ? true : false;
        }
    }

    public static function estatus_static($class=false, $estatus=null){
        if (!$estatus) {
            $estatus=$this->estatus;
        }
        if ($class) {
            return $estatus==2?'danger':'';
        }
        if ($estatus==2) {
            return 'Por Cotizar';
        }
    }

    public  function total($id=null){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get());
        if ($id) {
             $items=ItemsFactura::where('factura',$id)->get();
        }
        else{

            $items=ItemsFactura::where('factura',$this->id)->get();
        }
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
        $totales['total']=$totales['subsub']=$totales['subtotal']-$totales['descuento'];
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

    public function pagado(){
        $total=IngresosFactura::where('factura',$this->id)->sum('pago');
        $total+=$this->retenido();
        return $total;
    }
    public function retenido(){
        $ingresos=IngresosFactura::where('factura',$this->id)->get();
        $total=0;
        foreach ($ingresos as $ingreso) {
            $total+=(float)$ingreso->retencion();
        }
        return $total;
    }


    public function porpagar(){
        return abs($this->total()->total - $this->pagado());
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
}
