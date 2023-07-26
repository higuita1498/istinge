<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Model\Ingresos\Factura; 
use App\Model\Ingresos\NotaCredito; 

class NotaCreditoFactura extends Model 
{
    protected $table = "notas_factura";
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
         return Factura::where('id',$this->factura)->first();
    }

    public function nota(){
        return NotaCredito::where('id',$this->nota)->first();
    }

    public function pago(){
        return $this->pago+$this->retencion();
    }

}
 