<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Impuesto;
use App\Model\Inventario\Inventario;  
use DB; use Auth;

class ItemsNotaCredito extends Model
{
    protected $table = "items_notas";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nota', 'producto', 'ref', 'precio', 'descripcion', 'impuesto', 'id_impuesto', 'cant', 'desc', 'created_at', 'updated_at'
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
        return Inventario::where('id',$this->producto)->first()->producto;
        
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

    public function totalImpSingular()
    {
        $result = $this->total();
        $imp = 0;
        $total = array();

        if ($this->impuesto > 0) {
            array_push($total, ["imp0"=>($result*$this->impuesto)/100]);
        }

        return $total;
    }

    public function impuestoSingular()
    {
        $text = '';
        $ivas =  array();

        $impuesto = Impuesto::where('id', $this->id_impuesto)->first();
        if ($impuesto) {
            array_push($ivas, ["imp0" => $impuesto->nombre."(".$impuesto->porcentaje."%)"]);
        }
    
        if ($impuesto) {
            return $ivas;
        }
        return '';
    }

    public function impuestoSingularNombre()
    {
        $text = '';
        $ivas =  array();

        $impuesto = Impuesto::where('id', $this->id_impuesto)->first();
        if ($impuesto) {
            array_push($ivas, ["imp0" => $impuesto->nombre]);
        }
      
        if ($impuesto) {
            return $ivas;
        }
        return '';
    }


}
