<?php

namespace  App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Model\Inventario\Inventario;  use App\Impuesto;  use App\CamposExtra; 
use DB; use Auth;
class ItemsFacturaRecurrente extends Model
{
    protected $table = "items_factura_recurrente";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'factura_recurrente', 'producto', 'ref', 'precio', 'descripcion', 'impuesto', 'id_impuesto', 'cant', 'desc', 'created_at', 'updated_at'
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
        return Inventario::where('id',$this->producto)->first();        
    }

    public function impuesto(){
        $impuesto= Impuesto::where('id',$this->id_impuesto)->first();
        if ($impuesto) {
            return $impuesto->nombre."(".$impuesto->porcentaje."%)";
        }
        return '';
        
    }

}
