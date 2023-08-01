<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Empresa;
use App\CRM;

class Etiqueta extends Model
{
    protected $table = "etiquetas";
    protected $primaryKey = 'id';
    //

    protected $fillable = [
        'nombre', 'color', 'empresa_id'
    ];

    public function empresa(){
        return $this->belongsTo(Empresa::class);
    }

    public function radicados(){
        return $this->hasMany(CRM::class);
    }

}
