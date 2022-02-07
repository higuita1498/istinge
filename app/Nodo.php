<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Auth;

use App\Contrato;
use App\Funcion;
use App\User;
use DB;

class Nodo extends Model
{
    protected $table = "nodos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'nombre', 'descripcion', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];
    
    protected $appends = ['uso', 'session'];

    public function getUsoAttribute()
    {
        return $this->uso();
    }
    
    public function getSessionAttribute()
    {
        return $this->getAllPermissions(Auth::user()->id);
    }
    
    public function getAllPermissions($id)
    {
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
    
    public function status($class=false){
        if($class){
            return $this->status == '1' ? 'success' : 'danger';
        }
        return $this->status == '1' ? 'Habilitado' : 'Deshabilitado';
    }

    public function uso(){
        $cont=0;
        $cont+=Contrato::where('nodo', $this->id)->count();
        return $cont;
    }
    
    public function created_by(){
        return User::find($this->created_by);
    }
    
    public function updated_by(){
        return User::find($this->updated_by);
    }
    
    public function nodos(){
        return DB::table('nodos')->where('status', 1)->get();
    }
}