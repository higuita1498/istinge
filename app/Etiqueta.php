<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Empresa;


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


}
