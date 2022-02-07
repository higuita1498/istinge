<?php

namespace App\Model\Gastos;

use Illuminate\Database\Eloquent\Model;
use App\Model\Inventario\Inventario; use App\Impuesto;  
use DB; use Auth; use App\Categoria;  
class ItemsFacturaProv extends Model
{
    protected $table = "items_factura_proveedor";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'factura', 'producto', 'ref', 'precio', 'descripcion', 'impuesto', 'id_impuesto', 'cant', 'desc', 'created_at', 'updated_at', 'tipo_item'
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


    /**
     * Obtiene el monto total del item_factura con el impuesto aplicado
     * @return float|int
     */
    public function totalImp()
    {
        $result = $this->total();

        if($this->impuesto > 0 ){
            $imp = ($result*$this->impuesto)/100;
        }else{
            $imp = 0;
        }

        return $result+$imp;

    }

    public function producto($largo=false){
        if ($this->tipo_item==1) {
            return Inventario::where('id',$this->producto)->first()->producto;
        }
        else{
            
            if($largo){
                return Categoria::where('id',$this->producto)->first();
            }
            return Categoria::where('id',$this->producto)->first()->nombre;
        }
         
        
    }

    public function ref()
    {
        if ($this->tipo_item==1) {
            return Inventario::where('id',$this->producto)->first()->ref;
        }
        else{
            return "N/A";

        }

    }

    public function impuesto(){
        $impuesto= Impuesto::where('id',$this->id_impuesto)->first();
        if ($impuesto) {
            return $impuesto->nombre."(".$impuesto->porcentaje."%)";
        }
        return '';
        
    }

    public function productoTotal($largo=false)
    {
        if ($this->tipo_item == 1) {
            return Inventario::where('id', $this->producto)->first();
        }

        return null;

    }

}
