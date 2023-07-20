<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Impuesto;
use App\Model\Inventario\Inventario;  
use DB; use Auth;
use App\ProductoCuenta;

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

    public function itemImpuestoSingular()
    {
        $text = '';
        $ivas =  array();

        $impuesto = Impuesto::where('id', $this->id_impuesto)->first();
        if ($impuesto) {
            array_push($ivas, ["imp0" => $impuesto->porcentaje]);
        }
       
        if ($impuesto) {
            return $ivas;
        }
        return '';
    }

    public function impuestoNombre(){
        $text = '';

       $impuesto = Impuesto::where('id', $this->id_impuesto)->first();
       if ($impuesto) {
           $text .= $impuesto->nombre;
       }

        if ($impuesto) {
           
           if($text == "Ninguno" || $text == "NINGUNO" || $text == "N/A"){
               $text = "IVA";
           }
           
           return $text;
       }else if($this->id_impuesto == 0){
        $text = "IVA";
        return $text;
       }
       return '';
   }

   public function itemCantImpuesto()
    {
        $imp = 0;
        $result = $this->precio * $this->cant;

        if ($this->desc>0) {
            $desc=($result*$this->desc)/100;
        } else {
            $desc=0;
        }

        $result = $result - $desc;

        if ($this->impuesto > 0) {
            $imp += (($result)*$this->impuesto)/100;
        }
        
        return $imp;
    }

    public function cuentasContable(){
        return ProductoCuenta::where('inventario_id',$this->producto)->get();
    }

    public function totalCompra(){
        
        if($this->inventario->tipo_producto == 1){
            $result=$this->inventario->costo_unidad*$this->cant;
            $result = round($result);
        }else{
            $result = 0;
        }
        return $result;
    }

    public function inventario(){
        return $this->belongsTo(Inventario::class,'producto');
    }

}
