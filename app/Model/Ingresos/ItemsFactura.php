<?php

namespace App\Model\Ingresos;

use App\Categoria;
use Illuminate\Database\Eloquent\Model;
use App\Model\Inventario\Inventario;  
use App\Impuesto;  use App\CamposExtra; 
use DB; use Auth;
class ItemsFactura extends Model
{
    protected $table = "items_factura";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'factura', 'producto', 'ref', 'precio', 'descripcion', 'impuesto', 'id_impuesto', 'cant', 'desc', 'created_at', 'updated_at', 'tipo_inventario'
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

    public function itemImpuesto(){
        if($this->impuesto > 0 ){
            $imp = ($this->precio*$this->impuesto)/100;
        }else{
            $imp = 0;
        }

        return $imp;

    }

    public function producto($largo=false){
        if ($this->tipo_inventario==1) {
            return Inventario::where('id',$this->producto)->first()->producto;
        }
        else{
            $nombre='';
            $producto=DB::table('inventario_volatil')->where('id', $this->producto)->first();
            if ($largo) {
                $meta=DB::table('inventario_volatil_meta')->where('id_producto', $producto->id)->get();
                $nombre=array(array('', $producto->producto));
                foreach ($meta as $value) {
                    $id=0;
                    $titulo=CamposExtra::where('empresa', Auth::user()->empresa)->where('id', $value->meta_key)->Orwhere('campo', $value->meta_key)->first();
                   
                    if ($titulo) {
                       $value->meta_key=$titulo->nombre;
                       $id=$titulo->id;
                    }
                    $nombre[]=array($value->meta_key, $value->meta_value, $id);
                }
                return $nombre;

            }
            return $producto->producto;
        }
         
        
    }

    public function impuesto($fe = false){
        $impuesto= Impuesto::where('id',$this->id_impuesto)->first();
        if ($impuesto) {
            return $impuesto->nombre."(".$impuesto->porcentaje."%)";
        }
        if($fe){
            return $impuesto->nombre;
        }
        return '';
        
    }

    public function productoTotal($largo=false)
    {
        if ($this->tipo_inventario == 1) {
            return Inventario::where('id', $this->producto)->first();
        }

        return null;

    }
    
    public function itemImpDescuento(){
       return $imp = $this->total() * $this->impuesto / 100;
    }
}
