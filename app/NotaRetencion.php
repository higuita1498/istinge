<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaRetencion extends Model
{
    protected $table = "notas_retenciones";
    protected $primaryKey = 'id';

    protected $fillable = [
        'notas', 'valor', 'retencion', 'id_retencion'
    ];

    public $timestamps = false;

    public function retencion()
    {
        return Retencion::find($this->id_retencion);
    }
    
    public function getNombreAttribute()
    {
        return (Retencion::find($this->id_retencion))->nombre;
    }
    
    public function getPorcentajeAttribute()
    {
        return (Retencion::find($this->id_retencion))->porcentaje;
    }
    
}
