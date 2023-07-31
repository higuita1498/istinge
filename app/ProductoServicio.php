<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use stdClass;

class ProductoServicio extends Model
{
    protected $table = "producto_servicio";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'en_uso', 'codigo', 'nombre', 'inventario_id', 'costo_id', 'venta_id', 'devolucion_id', 'created_at', 'updated_at'
    ];

    public function inventario(){
        
        $inventario = Puc::where('id',$this->inventario_id)->first();

        if(!$inventario){
            $inventario = new stdClass;
            $inventario->nombre = "";
        }

        return $inventario;
    }

    public function costo(){

        $costo = Puc::where('id',$this->costo_id)->first();
        
        if(!$costo){
            $costo = new stdClass;
            $costo->nombre = "";
        }

        return $costo;
    }

    public function venta(){

        $venta = Puc::where('id',$this->venta_id)->first();

        if(!$venta){
            $venta = new stdClass;
            $venta->nombre = "";
        }

        return $venta;
    }

    public function devolucion(){

        $devolucion = Puc::where('id',$this->devolucion_id)->first();

        if(!$devolucion){
            $devolucion = new stdClass;
            $devolucion->nombre = "";
        }


        return $devolucion;
    }
}
