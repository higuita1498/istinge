<?php

namespace App\Model\Ingresos;

use App\AsignarMaterial;
use App\Categoria;
use Illuminate\Database\Eloquent\Model;
use App\Model\Inventario\Inventario;
use App\Impuesto;  use App\CamposExtra;
use DB; use Auth;
use App\ProductoCuenta;
class ItemsAsignarMaterial extends Model
{
    protected $table = "items_asignar_materials";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_asignacion_material', 'id_material', 'cantidad','created_at', 'updated_at'
    ];

    public function asignacion()
    {
        return $this->belongsTo(AsignarMaterial::class, 'id_asignacion_material');
    }

    public function material()
    {
        return $this->belongsTo(Inventario::class, 'id_material','id');
    }

}
