<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\categoria; use App\Retencion; 
use App\Model\Ingresos\Ingreso;

class IngresosRetenciones extends Model
{
    protected $table = "ingresos_retenciones";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ingreso', 'factura', 'valor','retencion',  'id_retencion', 'created_at', 'updated_at' 
    ];

   
    public function retencion(){
        return Retencion::where('id',$this->id_retencion)->first();
    }
  

}
