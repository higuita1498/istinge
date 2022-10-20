<?php

namespace App\Model\Gastos;

use Illuminate\Database\Eloquent\Model;
use App\Model\Gastos\FacturaProveedores; 
use App\Model\Gastos\GastosRetenciones; 
use App\Model\Gastos\Gastos;


use App\Ingreso;  
class GastosFactura extends Model 
{
    protected $table = "gastos_factura";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gasto', 'factura', 'pagado', 'pago', 'created_at', 'updated_at'
    ];

    public function retencion(){
        return GastosRetenciones::where('gasto', $this->gasto)->where('factura', $this->factura)->sum('valor');
    }

    public function factura(){
         return FacturaProveedores::where('id',$this->factura)->first();
    }

    public function retenciones(){
        return GastosRetenciones::where('gasto', $this->gasto)->where('factura', $this->factura)->get();
    }

    public function gasto(){
        return Gastos::where('id',$this->gasto)->first();
    }

    public function pago(){
        $gasto = Gastos::find($this->gasto);
        $pago = $gasto->estatus == 2 ? 0 : $this->pago;

        return $pago+$this->retencion();
    }

    
    

     
    


    

    

    public function detalle(){
        $factura=FacturaProveedores::where('id',$this->factura)->first();
        if ($factura->codigo) {
            return 'Pago a la factura de proveedor número '.$factura->codigo;
        }
        return 'Pago a la factura de proveedor con fecha '.date('d-m-Y', strtotime($factura->fecha_factura));


    }
}
 