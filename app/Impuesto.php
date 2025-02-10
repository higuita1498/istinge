<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Inventario\Inventario;
use App\Puc;
class Impuesto extends Model
{
    protected $table = "impuestos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'nombre', 'porcentaje', 'tipo', 'descripcion', 'empresa', 'estado','siigo_id'
    ];

    public function usado()
    {
        return ItemsFactura::where('id_impuesto',$this->id)->count()+Inventario::where('id_impuesto',$this->id)->count();

    }

    public function tipo()
    {
        $tipo='';
        if ($this->tipo==1) {
            $tipo='IVA';
        }
        else if ($this->tipo==2) {
            $tipo='ICO';
        }
        else if ($this->tipo==3) {
            $tipo='Otro';
        }
        return $tipo;
    }

    public function pucVenta(){
        return Puc::find($this->puc_venta);
    }

    public function pucCompra(){
        return Puc::find($this->puc_compra);
    }


}
