<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Auth;
use DB;

use App\Funcion;
use App\User;
use App\Contrato;
use App\Contacto;

class Producto extends Model
{
    protected $table = "productos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa', 'oficina', 'nro', 'tipo', 'producto', 'contrato', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];
    
    protected $appends = ['session'];
    
    public function getSessionAttribute(){
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
    
    public function tipo($class=false){
        return $this->tipo == 1 ? 'Asignación' : 'Devolución';
    }
    
    public function created_by(){
        return User::find($this->created_by);
    }
    
    public function updated_by(){
        return User::find($this->updated_by);
    }

    public function contrato(){
        return Contrato::find($this->contrato);
    }
}