<?php

namespace App;

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
        'empresa','id_tecnico', 'fecha', 'notas'
    ];

}
