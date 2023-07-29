<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Numeracion extends Model
{
    protected $table = "numeraciones";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'caja','cajar', 'pago', 'credito', 'remision', 'cotizacion', 'orden', 'empresa'
    ];

}
