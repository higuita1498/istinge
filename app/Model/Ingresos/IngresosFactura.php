<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosRetenciones;

class IngresosFactura extends Model
{
    protected $table = "ingresos_factura";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ingreso', 'factura', 'pagado', 'pago', 'created_at', 'updated_at'
    ];


    public function factura(){
         return Factura::where('id',$this->factura)->first();
    }


    public function getPagadoAttribute()
    {
        return $this->factura()->total()->total;
    }


    public function ingreso(){
        return Ingreso::where('id',$this->ingreso)->first();
    }

    public function ingresoRelation()
    {
        return $this->belongsTo(Ingreso::class, 'ingreso'); // o 'ingreso_id' si ese es el nombre real de la columna
    }

    public function retencion(){
        return IngresosRetenciones::where('ingreso', $this->ingreso)->where('factura', $this->factura)->sum('valor');
    }
    public function retenciones(){
        return IngresosRetenciones::where('ingreso', $this->ingreso)->where('factura', $this->factura)->get();

    }

    public function pago(){
        return $this->pago;
    }

    public function detalle(){
        $factura=Factura::where('id',$this->factura)->first();
        return 'Factura de Venta: '.$factura->codigo;
    }

    public function fecha($fecha)
    {
        return date('Y-m-d', strtotime($this->created_at)) == $fecha;
}

    /**
     * Devuelve la factura del item relacionado
     */
    public function itemFactura()
    {
        return ItemsFactura::where('factura', $this->factura)->get();
    }

}
