<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Model\Ingresos\Remision;
use  App\Model\Ingresos\IngresoR;

class IngresosRemision extends Model
{
    protected $table = "ingresosr_remisiones";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ingreso', 'remision', 'pagado', 'pago', 'created_at', 'updated_at'
    ];

    
    public function remision(){
         return Remision::where('id',$this->remision)->first();
    }
    
    public function getPagadoAttribute()
    {
        return $this->remision()->total()->total;
    }


    public function ingreso(){
        return IngresoR::where('id',$this->ingreso)->first();
    }
    
    public function total(){

        $result=$this->precio*$this->cant;
        //SACAR EL DESCUENTO
        if ($this->desc>0) {
            $desc=($result*$this->desc)/100;
        }
        else{ $desc=0; }

        return ($result-$desc);
    }

    public function totalImp(){
        $result = $this->total();

        if($this->impuesto > 0 ){
            $imp = ($result*$this->impuesto)/100;
        }else{
            $imp = 0;
        }

        return $result+$imp;
    }
    
    

    public function pago(){
        return $this->pago;
    }
}
 