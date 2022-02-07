<?php

namespace App\Model\Gastos;
use Illuminate\Database\Eloquent\Model;
use App\Contacto; use App\Impuesto; 
use App\Model\Gastos\ItemsFacturaProv;
use Auth; use App\Model\Inventario\Bodega; 
use App\Model\Gastos\FacturaProveedores; 
use App\Vendedor;

class Ordenes_Compra extends Model
{
    protected $table = "factura_proveedores";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'orden_nro', 'empresa','codigo', 'tipo', 'proveedor', 'fecha', 'vencimiento', 'observaciones', 'estatus', 'notas', 'created_at', 'updated_at', 'bodega', 'term_cond'    
    ];

    public function factura(){
        return FacturaProveedores::where('id',$this->id)->first();

    }
    public function proveedor(){
        return Contacto::where('id',$this->proveedor)->first();
    }

    public function estatus($class=false){
        if ($this->tipo==1) {
            if ($class) {
                return 'success';
            }
            return 'Facturada';
        }

        
        if ($this->estatus==0) {
            if ($class) {
                return '';
            }
            return 'Facturada';
        }
        else if ($this->estatus==2) {
            if ($class) {
                return 'warning';
            }
            return 'Anulada';
        }
    }

    public function total(){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get());
        $items=ItemsFacturaProv::where('factura',$this->id)->get();
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



    public function bodega(){
        $bodega=Bodega::where('empresa',Auth::user()->empresa)->where('id', $this->bodega)->first();
        if (!$bodega) { return ''; }
        return $bodega->bodega;
    }
    
    public function getCompradorNameAttribute()
    {
        if (!$this->comprador) {
            return '';
        }
        return Vendedor::where('id',$this->comprador)->first()->nombre;
    }


}
