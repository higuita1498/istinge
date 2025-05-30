<?php

namespace App;

use App\Model\Ingresos\ItemsAsignarMaterial;
use App\Model\Inventario\Inventario;
use Illuminate\Database\Eloquent\Model;

class AsignarMaterial extends Model
{
    protected $table = "asignacion_materials";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa',
        'referencia',
        'id_tecnico',
        'notas',
        'fecha',
        'created_at',
        'updated_at'
    ];

    public function items()
    {
        return $this->hasMany(ItemsAsignarMaterial::class, 'id_asignacion_material');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'id_tecnico', 'id');
    }

    public function materiales()
    {
        return $this->belongsToMany(Inventario::class, 'items_asignar_materials', 'id_asignacion_material', 'id_material')
            ->withPivot('cantidad');
    }
}
