<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductoCuenta extends Model
{
    protected $table = "producto_cuentas";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cuenta_id', 'inventario_id', 'created_at', 'updated_at', 'tipo'
    ];

}
