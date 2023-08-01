<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Notificacion extends Model
{
    protected $table = "notificaciones";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tipo', 'mensaje', 'desde', 'hasta', 'status', 'created_at', 'updated_at'
    ];

    public function tipo(){
        return ($this->tipo == 0) ? 'NOTIFICACIÓN' : 'NOTICIA';
    }
    
    public function status($class = false){
        if($class){
            return ($this->status == 0) ? 'text-danger' : 'text-success';
        }
        return ($this->status == 0) ? 'VENCIDA' : 'ACTIVA';
    }
}