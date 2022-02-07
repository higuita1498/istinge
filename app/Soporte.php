<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
 use App\Modulo; use App\Empresa; use App\User;
class Soporte extends Model
{
    protected $table = "soporte";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'asociada', 'empresa', 'modulo', 'imagen', 'error', 'titulo', 'estatus', 'created_at', 'updated_at', 'usuario'
    ];

    public function modulo(){
         return Modulo::where('id',$this->modulo)->first()->nombre;
    }
    public function estatus(){
           $reulst='Cerrado';
        if ($this->estatus==1) {
           $reulst='Pendiente';
        }
        else if ($this->estatus==2) {
           $reulst='Resuelto';
        }
         return $reulst;
    }

    public function empresa(){
        return Empresa::where('id',$this->empresa)->first();

    }

    public function usuario($array=false){
        if ($array) {            
            return $usuario= User::where('id',$this->usuario)->first();
        }
        $usuario= User::where('id',$this->usuario)->first();
        if ($usuario) {
            return $usuario->nombres;
        }
        return $this->usuario;

    }

}
