<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Impuesto; 
use App\Model\Inventario\Inventario;  

class ItemsRemision extends Model
{
    protected $table = "items_remision";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'remision', 'producto', 'ref', 'precio', 'descripcion', 'impuesto', 'id_impuesto', 'cant', 'desc', 'created_at', 'updated_at'  
    ];

    public function total(){

        $result=$this->precio*$this->cant;
        //SACAR EL DESCUENTO
        if ($this->desc>0) {
            $desc=($result*$this->desc)/100;
        }
        else{ $desc=0; }

        return ($result);

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

    public function producto($name = true){
        $producto = Inventario::where('id',$this->producto)->first();
         return $name ? Inventario::where('id',$this->producto)->first()->producto  : Inventario::where('id',$this->producto)->first();
    }

    public function impuesto(){
        $impuesto= Impuesto::where('id',$this->id_impuesto)->first();
        if ($impuesto) {
            return $impuesto->nombre."(".$impuesto->porcentaje."%)";
        }
        return '';
        
    }

}
