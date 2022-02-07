<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Categoria; use App\Impuesto; 

class IngresosCategoria extends Model
{
    protected $table = "ingresos_categoria";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ingreso', 'categoria', 'valor', 'impuesto','id_impuesto',  'descripcion', 'cant', 'created_at', 'updated_at' 
    ];

    
    public function categoria($name=false){
        $categoria = Categoria::where('id',$this->categoria)->first();
        return $name ? Categoria::where('id',$this->categoria)->first()->nombre
            : Categoria::where('id',$this->categoria)->first();
    }

    public function impuesto(){
        $impuesto= Impuesto::where('id',$this->id_impuesto)->first();
        if ($impuesto->porcentaje) {
            return $impuesto->nombre."(".$impuesto->porcentaje."%)";
        }
        return '';
        
    }
    public function detalle(){
        return $this->categoria();
    }
    public function total(){
        return $this->valor*$this->cant;
    }

   public function pago(){
        return $this->total();
    } 

}
