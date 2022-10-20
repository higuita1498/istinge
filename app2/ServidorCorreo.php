<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Auth;

use App\Contrato;
use App\Nodo;
use App\Funcion;
use DB;
use App\User;

class ServidorCorreo extends Model
{
    protected $table = "servidores_correos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa', 'servidor', 'seguridad', 'puerto', 'password', 'estado', 'address', 'name', 'created_at', 'updated_at'
    ];
    
    protected $appends = ['session'];
    
    public function getSessionAttribute()
    {
        return $this->getAllPermissions(Auth::user()->id);
    }
    
    public function getAllPermissions($id){
        if(Auth::user()->rol>=2){
            if (DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->count() > 0 ) {
                $permisos = DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->get();
                foreach ($permisos as $key => $value) {
                    $_SESSION['permisos'][$permisos[$key]->id_permiso] = '1';
                }
                return $_SESSION['permisos'];
            }
            else return null;
        }
    }
    
    public function estado($class=false){
        if($class){
            return $this->estado == '1' ? 'success' : 'danger';
        }
        return $this->estado == '1' ? 'Habilitado' : 'Deshabilitado';
    }
}