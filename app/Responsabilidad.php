<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Responsabilidad extends Model
{
    protected $table = "responsabilidades_facturacion";

    protected $fillable = ['codigo', 'responsabilidad'];


    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'empresa_responsabilidad', 'id_responsabilidad', 'id_empresa');
    }
}
