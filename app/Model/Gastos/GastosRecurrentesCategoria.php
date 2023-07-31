<?php

namespace App\Model\Gastos;
use Illuminate\Database\Eloquent\Model;
use App\Categoria; use App\Impuesto;
class GastosRecurrentesCategoria extends Model
{
    protected $table = "gastos_recurrentes_categoria";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gasto_recurrente', 'categoria', 'valor', 'impuesto','id_impuesto',  'descripcion', 'cant', 'created_at', 'updated_at'
    ];


    public function categoria($solo=false){
        return Categoria::where('id',$this->categoria)->first()->nombre;
    }
    public function impuesto(){
        $impuesto= Impuesto::where('id',$this->id_impuesto)->first();
        if (isset($impuesto->porcentaje)) {
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

}
