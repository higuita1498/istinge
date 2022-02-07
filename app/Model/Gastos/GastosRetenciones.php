<?php

namespace App\Model\Gastos;

use Illuminate\Database\Eloquent\Model;
use App\categoria; use App\Ingreso; use App\Retencion; 
class GastosRetenciones extends Model
{
    protected $table = "gastos_retenciones";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gasto', 'factura', 'valor','retencion',  'id_retencion', 'created_at', 'updated_at' 
    ];

   
    public function retencion(){
        return Retencion::where('id',$this->id_retencion)->first();
    }
  

}
