<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\User;
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
        'nombre', 'ip', 'puerto_web', 'puerto_api', 'usuario', 'clave', 'status', 'segmento_ip', 'created_by', 'updated_by', 'created_at', 'updated_at', 'board', 'uptime', 'cpu', 'version', 'buildtime', 'freememory', 'totalmemory', 'cpucount', 'cpufrequency', 'cpuload', 'freehddspace', 'totalhddspace', 'writesectsincereboot', 'writesecttotal', 'architecturename', 'platform', 'interfaz', 'reglas', 'interfaz_lan', 'regla_ips_autorizadas'
    ];

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
}