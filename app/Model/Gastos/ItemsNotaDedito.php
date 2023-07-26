<?php

namespace App\Model\Gastos;

use Illuminate\Database\Eloquent\Model;
use App\Model\Inventario\Inventario; use App\Impuesto;
use DB; use Auth; use App\Categoria; 
class ItemsNotaDedito extends Model
{
    protected $table = "items_notas_debito";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nota', 'producto', 'ref', 'precio', 'descripcion', 'impuesto', 'id_impuesto', 'cant', 'desc', 'tipo_item', 'created_at', 'updated_at', 'tipo_item'
    ]; 

    public function total(){

        $result=$this->precio*$this->cant;
        //SACAR EL DESCUENTO
        if ($this->desc>0) {
            $desc=($result*$this->desc)/100; 
        }
        else{ $desc=0; }

        return $result-$desc;

    }

    public function producto($largo=false){
        if ($this->tipo_item==1) {
            return Inventario::where('id',$this->producto)->first()->producto;
        }
        else{
            return Categoria::where('id',$this->producto)->first()->nombre;

        }
        
    }

    public function impuesto(){
        $impuesto= Impuesto::where('id',$this->id_impuesto)->first();
        if ($impuesto) {
            return $impuesto->nombre."(".$impuesto->porcentaje."%)";
        }
        return '';
        
    }
    
    public function itemImpuesto(){
        if($this->impuesto > 0 ){
            $imp = ($this->precio*$this->impuesto)/100;
        }else{
            $imp = 0;
        }
        return $imp;
    }
    
    public function itemImpDescuento(){
       return $imp = $this->total() * $this->impuesto / 100;
    }

}
