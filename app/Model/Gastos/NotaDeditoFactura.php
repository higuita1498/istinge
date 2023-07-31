<?php

namespace App\Model\Gastos;

use Illuminate\Database\Eloquent\Model;
use App\Model\Gastos\FacturaProveedores; use App\Model\Gastos\NotaDedito; 
class NotaDeditoFactura extends Model 
{
    protected $table = "notas_factura_debito";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nota', 'factura', 'pago', 'created_at', 'updated_at'
    ];

    
    public function factura(){
         return FacturaProveedores::where('id',$this->factura)->first();
    }

    public function nota(){
        return NotaDedito::where('id',$this->nota)->first();
    }

    public function pago(){
        return $this->pago+$this->retencion();
    }

}
 