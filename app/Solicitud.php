<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
class Solicitud extends Model
{
    protected $table = "solicitudes";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre','cedula','nrouno', 'nrodos', 'email', 'direccion', 'plan', 'fecha', 'status'
    ];

    public function status($class = false){
        if($class){
            if($this->status == 0){
                $status = 'danger';
            }elseif($this->status == 1){
                $status = 'success';
            }
            return $status;
        }
        
        if($this->status == 0){
            $status = 'Atendido';
        }elseif($this->status == 1){
            $status = 'Por Atender';
        }
        return $status;
    }
}