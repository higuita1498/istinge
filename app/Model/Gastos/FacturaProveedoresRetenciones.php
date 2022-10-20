<?php

namespace App\Model\Gastos;

use Illuminate\Database\Eloquent\Model;
use App\categoria; use App\Ingreso; use App\Retencion; 
class FacturaProveedoresRetenciones extends Model
{
    protected $table = "factura_proveedores_retenciones";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'factura', 'valor','retencion',  'id_retencion', 'created_at', 'updated_at' 
    ];

   
    public function retencion(){
        return Retencion::where('id',$this->id_retencion)->first();
    }
    
    public function tipo()
    {
        if ($this->retencion()->tipo == 2){
            return "RETENCIÓN FTE";
        }elseif ($this->retencion()->tipo == 1){
            return "RETENCIÓN IVA";
        }else{
            return $this->retencion()->tipo == 3 ? "RETENCIÓN ICO" : "RETENCIÓN";
        }
    }
  

}
