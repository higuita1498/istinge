<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Model\Ingresos\Factura; 
use App\Contacto; 
use App\User; 
use DB;
use Auth;

class PromesaPago extends Model
{
    protected $table = "promesa_pago";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'factura', 'cliente', 'fecha', 'vencimiento', 'created_by', 'updated_by', 'created_at', 'updated_at'
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
    
    public function factura(){
        return Factura::find($this->factura);
    }
    
    public function cliente(){
        return Contacto::find($this->cliente);
    }
    
    public function usuario(){
        return User::find($this->created_by);
    }

}
