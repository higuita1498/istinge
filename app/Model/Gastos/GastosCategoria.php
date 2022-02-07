<?php

namespace App\Model\Gastos;

use Illuminate\Database\Eloquent\Model;
use App\Categoria; use App\Impuesto; 
class GastosCategoria extends Model
{
    protected $table = "gastos_categoria";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gasto', 'categoria', 'valor', 'impuesto','id_impuesto',  'descripcion', 'cant', 'created_at', 'updated_at' 
    ];

    
    public function categoria($solo=false){
        if ($solo) {
         return Categoria::where('id',$this->categoria)->first()->nombre;
        }

        $categoria=Categoria::where('id',$this->categoria)->first();  
        
        $cat=$categoria->nombre;
       
        while (true) {
            if (!$categoria->asociado) {  $cat=$categoria->nombre.": ".$cat; break; }
           $categoria=Categoria::where('nro',$categoria->asociado)->first();
            $cat=$categoria->nombre." / ".$cat;

        }

        return $cat;
    }
    public function impuesto(){
        $impuesto= Impuesto::where('id',$this->id_impuesto)->first();
        if ($impuesto->porcentaje) {
            return $this->impuesto."%";
        }
        return '';
        
    }
    public function total(){
        return $this->valor*$this->cant;
    }

   public function pago(){
        return $this->total();
    } 

    public function detalle(){
        return $this->categoria(true);
    }

    public function detalleCat()
    {
        return Categoria::where('id',$this->categoria)->first();
    }

}
