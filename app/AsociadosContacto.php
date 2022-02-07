<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AsociadosContacto extends Model
{
    protected $table = "asociados_contactos";
    protected $primaryKey = 'contacto';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'telefono', 'celular', 'email', 'notificacion', 'contacto', 'created_at', 'updated_at'  
    ];

}
