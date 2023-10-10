<?php

namespace App\Model\Gastos;

use Illuminate\Database\Eloquent\Model;
use App\Model\Inventario\Inventario; use App\Impuesto;  
use DB; use Auth; use App\Categoria;  
use App\ProductoCuenta;
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
            if(Categoria::where('id',$this->producto)->first()){
                return Categoria::where('id',$this->producto)->first()->nombre;
            }else{
                return Inventario::where('id',$this->producto)->first()->producto;
            }
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

    public function inventario(){
        return $this->belongsTo(Inventario::class,'producto');
    }

    public function cuentasContable(){
        return ProductoCuenta::where('inventario_id',$this->producto)->get();
    }

    public function productoIva()
    {
        if (isset($this->tipo_inventario) && $this->tipo_inventario==1) {
            return Inventario::where('id', $this->producto)->first()->diaiva;
        } else {
            return 0;
        }
    }

    public function itemImpDescuento()
    {
        $text = '';
        $ivas =  array();
        $totalIva = 0;
        $precioItem = $this->precio * $this->cant;

        if($this->impuesto){
            $totalIva+= ($precioItem * $this->impuesto) / 100;
        }
        if($this->impuesto_1){
            $totalIva+= ($precioItem * $this->impuesto_1) / 100;
        }
        if($this->impuesto_2 > 0){
            $totalIva+= ($precioItem * $this->impuesto_2) / 100;
        }
        if($this->impuesto_3){
            $totalIva+= ($precioItem * $this->impuesto_3) / 100;
        }
        if($this->impuesto_4){
            $totalIva+= ($precioItem * $this->impuesto_4) / 100;
        }
        if($this->impuesto_5){
            $totalIva+= ($precioItem * $this->impuesto_5) / 100;
        }
        if($this->impuesto_6){
            $totalIva+= ($precioItem * $this->impuesto_6) / 100;
        }
        if($this->impuesto_7){
            $totalIva+= ($precioItem * $this->impuesto_7) / 100;
        }

        if($this->desc){
            $totalIva = $totalIva - ($this->desc * $totalIva / 100);
        }
        
        return $totalIva;
    }

    public function totalImpSingular()
    {
        $result = $this->total();
        $imp = 0;
        $total = array();

        if ($this->impuesto > 0) {
            array_push($total, ["imp0"=>($result*$this->impuesto)/100]);
        }

        if ($this->impuesto_1 > 0) {
            array_push($total, ["imp1"=>($result*$this->impuesto_1)/100]);
        }

        if ($this->impuesto_2 > 0) {
            array_push($total, ["imp2"=>($result*$this->impuesto_2)/100]);
        }

        if ($this->impuesto_3 > 0) {
            array_push($total, ["imp3"=>($result*$this->impuesto_3)/100]);
        }

        if ($this->impuesto_4 > 0) {
            array_push($total, ["imp4"=>($result*$this->impuesto_4)/100]);
        }

        if ($this->impuesto_5 > 0) {
            array_push($total, ["imp5"=>($result*$this->impuesto_5)/100]);
        }

        if ($this->impuesto_6 > 0) {
            array_push($total, ["imp6"=>($result*$this->impuesto_6)/100]);
        }

        if ($this->impuesto_7 > 0) {
            array_push($total, ["imp7"=>($result*$this->impuesto_7)/100]);
        }

        return $total;
    }

    public function impuestoNombre($fe = false)
    {
        if ($fe) {
            $impuesto= Impuesto::where('id', $this->id_impuesto)->first();
            return $impuesto->nombre;
        }

        $text = '';

        $impuesto = Impuesto::where('id', $this->id_impuesto)->first();
        if ($impuesto) {
            $text .= $impuesto->nombre;
        }
        $impuesto_1 = Impuesto::where('id', $this->id_impuesto_1)->first();
        if ($impuesto_1) {
            $text .= $impuesto_1->nombre;
        }
        $impuesto_2 = Impuesto::where('id', $this->id_impuesto_2)->first();
        if ($impuesto_2) {
            $text .= $impuesto_2->nombre;
        }
        $impuesto_3 = Impuesto::where('id', $this->id_impuesto_3)->first();
        if ($impuesto_3) {
            $text .= $impuesto_3->nombre;
        }
        $impuesto_4 = Impuesto::where('id', $this->id_impuesto_4)->first();
        if ($impuesto_4) {
            $text .= $impuesto_4->nombre;
        }
        $impuesto_5 = Impuesto::where('id', $this->id_impuesto_5)->first();
        if ($impuesto_5) {
            $text .= $impuesto_5->nombre;
        }
        $impuesto_6 = Impuesto::where('id', $this->id_impuesto_6)->first();
        if ($impuesto_6) {
            $text .= $impuesto_6->nombre;
        }
        $impuesto_7 = Impuesto::where('id', $this->id_impuesto_7)->first();
        if ($impuesto_7) {
            $text .= $impuesto_7->nombre;
        }
        if ($impuesto) {
            
            if($text == "Ninguno" || $text == "NINGUNO"){
                $text = "IVA";
            }
            
            return $text;
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
        $impuesto_1 = Impuesto::where('id', $this->id_impuesto_1)->first();
        if ($impuesto_1) {
            array_push($ivas, ["imp1" =>$impuesto_1->porcentaje]);
        }
        $impuesto_2 = Impuesto::where('id', $this->id_impuesto_2)->first();
        if ($impuesto_2) {
            array_push($ivas, ["imp2" => $impuesto_2->porcentaje]);
        }
        $impuesto_3 = Impuesto::where('id', $this->id_impuesto_3)->first();
        if ($impuesto_3) {
            array_push($ivas, ["imp3" =>$impuesto_3->porcentaje]);
        }
        $impuesto_4 = Impuesto::where('id', $this->id_impuesto_4)->first();
        if ($impuesto_4) {
            array_push($ivas, ["imp4" =>$impuesto_4->porcentaje]);
        }
        $impuesto_5 = Impuesto::where('id', $this->id_impuesto_5)->first();
        if ($impuesto_5) {
            array_push($ivas, ["imp5" =>$impuesto_5->porcentaje]);
        }
        $impuesto_6 = Impuesto::where('id', $this->id_impuesto_6)->first();
        if ($impuesto_6) {
            array_push($ivas, ["imp6" =>$impuesto_6->porcentaje]);
        }
        $impuesto_7 = Impuesto::where('id', $this->id_impuesto_7)->first();
        if ($impuesto_7) {
            array_push($ivas, ["imp7" =>$impuesto_7->porcentaje]);
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
        $impuesto_1 = Impuesto::where('id', $this->id_impuesto_1)->first();
        if ($impuesto_1) {
            array_push($ivas, ["imp1" =>$impuesto_1->nombre]);
        }
        $impuesto_2 = Impuesto::where('id', $this->id_impuesto_2)->first();
        if ($impuesto_2) {
            array_push($ivas, ["imp2" =>$impuesto_2->nombre]);
        }
        $impuesto_3 = Impuesto::where('id', $this->id_impuesto_3)->first();
        if ($impuesto_3) {
            array_push($ivas, ["imp3" =>$impuesto_3->nombre]);
        }
        $impuesto_4 = Impuesto::where('id', $this->id_impuesto_4)->first();
        if ($impuesto_4) {
            array_push($ivas, ["imp4" =>$impuesto_4->nombre]);
        }
        $impuesto_5 = Impuesto::where('id', $this->id_impuesto_5)->first();
        if ($impuesto_5) {
            array_push($ivas, ["imp5" =>$impuesto_5->nombre]);
        }
        $impuesto_6 = Impuesto::where('id', $this->id_impuesto_6)->first();
        if ($impuesto_6) {
            array_push($ivas, ["imp6" =>$impuesto_6->nombre]);
        }
        $impuesto_7 = Impuesto::where('id', $this->id_impuesto_7)->first();
        if ($impuesto_7) {
            array_push($ivas, ["imp7" =>$impuesto_7->nombre]);
        }
        if ($impuesto) {
            return $ivas;
        }
        return '';
    }

    public function itemImpuesto()
    {
        if ($this->impuesto > 0) {
            $imp = ($this->precio*$this->impuesto)/100;
        } else {
            $imp = 0;
        }

        return $imp;
    }

}
