<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Inventario\Inventario;
class Retencion extends Model
{
    protected $table = "retenciones";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'nombre', 'porcentaje', 'tipo', 'descripcion', 'empresa'
    ];

    public function usado()
    {
        return ItemsFactura::where('id_impuesto',$this->id)->count()+Inventario::where('id_impuesto',$this->id)->count();
        
    }

    public function tipo()
    {
        $tipo='Otro tipo de retenci®Æn';
        if ($this->tipo==1) {
            $tipo='Retenci√≥n de IVA';
        }
        else if ($this->tipo==2) {
            $tipo='Retenci√≥n en la fuente';
        }
        else if ($this->tipo==3) {
            $tipo='Retenci√≥n de Industria y Comercio';
        }

        
        return $tipo;
    }

    
}
