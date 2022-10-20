<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\User;
class PQRS extends Model
{
    protected $table = "pqrs";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'solicitud', 'nombres', 'email', 'telefono', 'direccion', 'mensaje', 'fecha', 'respuesta', 'fecha_resp', 'estatus', 'updated_by', 'created_at', 'updated_at'
    ];

    public function updated_by(){
        return User::where('id', $this->updated_by)->first();
    }

    public function estatus($class = false){
        if($class){
            if($this->estatus == 0){
                $status = 'danger';
            }elseif($this->estatus == 1){
                $status = 'success';
            }
            return $status;
        }
        
        if($this->estatus == 0){
            $status = 'Atendido';
        }elseif($this->estatus == 1){
            $status = 'Por Atender';
        }
        return $status;
    }
}