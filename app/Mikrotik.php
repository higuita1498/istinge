<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\User;
use App\Contrato;
use App\PlanesVelocidad;
use DB;

class Mikrotik extends Model
{
    protected $table = "mikrotik";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'ip', 'puerto_web', 'puerto_api', 'puerto_winbox', 'usuario', 'clave', 'status', 'segmento_ip', 'created_by', 'updated_by', 'created_at', 'updated_at', 'board', 'uptime', 'cpu', 'version', 'buildtime', 'freememory', 'totalmemory', 'cpucount', 'cpufrequency', 'cpuload', 'freehddspace', 'totalhddspace', 'writesectsincereboot', 'writesecttotal', 'architecturename', 'platform', 'interfaz', 'reglas', 'interfaz_lan', 'regla_ips_autorizadas'
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

    public function updated_by(){
        return User::where('id', $this->updated_by)->first();
    }
    
    public function created_by(){
        return User::where('id', $this->created_by)->first();
    }

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
            $status = 'Desconectada';
        }elseif($this->status == 1){
            $status = 'Conectada';
        }
        return $status;
    }

    public function uso(){
        $tmp        = 0;
        $tmp        += Contrato::where('server_configuration_id', $this->id)->where('state','enabled')->where('status',1)->count();
        $tmp        += PlanesVelocidad::where('mikrotik', $this->id)->where('status',1)->count();
        return $tmp;
    }

    public function amarre_mac($class = false){
        if($class){
            return ($this->amarre_mac == 0) ? 'danger' : 'success';
        }

        return ($this->amarre_mac == 0) ? 'Deshabilitado' : 'Habilitado';
    }
}