<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Contacto; use  App\Model\Ingresos\ItemsFacturaRecurrente; 
use App\Impuesto; use App\Vendedor;
use App\Funcion; use Auth; 
use App\TerminosPago;  
use App\IngresosFactura; 
use App\NotaCreditoFactura; 
use App\IngresosRetenciones;
use App\Model\Inventario\ListaPrecios; 
use App\Model\Inventario\Bodega; 
class FacturaRecurrente extends Model
{
    protected $table = "facturas_recurrentes";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */ 
    protected $fillable = [
        'nro', 'empresa',  'numeracion', 'cliente', 'fecha', 'vencimiento', 'frecuencia', 'observaciones', 'proxima', 'notas', 'plazo', 'created_at', 'updated_at', 'term_cond', 'lista_precios', 'bodega' 
    ];

    public function cliente(){
         return Contacto::where('id',$this->cliente)->first();
    }

    public function total(){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get());
        $items=ItemsFacturaRecurrente::where('factura_recurrente',$this->id)->get();
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

    public function plazo($dias=false){
        if ($dias) {
            return TerminosPago::where('id',$this->plazo)->first()->dias;
        }
        if (!$this->plazo) { return ''; }
        if ($this->plazo=='n') {
            return 'Vencimiento Manual';
        }
        return TerminosPago::where('id',$this->plazo)->first()->nombre;
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
