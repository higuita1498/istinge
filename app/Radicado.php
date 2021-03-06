<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Carbon\Carbon;
use DB;

use App\User;
use App\Oficina;
use App\Contacto;

class Radicado extends Model
{
    protected $table = "radicados";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cliente','fecha','nombre', 'telefono', 'correo', 'direccion', 'contrato', 'desconocido', 'estatus', 'codigo','empresa','firma','prioridad'
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

    public function tecnico(){
        return User::where('id',$this->tecnico)->first();
    }
    
    public function tecnico_reporte(){
        $tecnico = User::where('id',$this->tecnico)->first();
        if($tecnico){
            return $tecnico->nombres;
        }
        return 'No asociado';
    }

    public function responsable(){
        return User::where('id',$this->responsable)->first();
    }

    public function servicio(){
        return Servicio::where('id',$this->servicio)->first();
    }

    public function estatus($class = false){
        if($class){
            if($this->estatus == 0 || $this->estatus == 2){
                return 'danger';
            }elseif($this->estatus == 1 || $this->estatus == 3){
                return 'success';
            }
        }
        
        if($this->estatus == 0){
            $status = 'Pendiente';
        }elseif($this->estatus == 1){
            $status = 'Solventado';
        }elseif($this->estatus == 2){
            $status = 'Escalado / Pendiente';
        }elseif($this->estatus == 3){
            $status = 'Escalado / Solventado';
        }
        return $status;
    }
    
    public function show_url(){
        return route('radicados.show', $this->id);
    }

    public function nro_radicados(){
        $temp = 0;
        $temp += Radicado::where('cliente', $this->cliente)->count();
        return $temp;
    }

    public function creado(){
        return ($this->creado == 1) ? 'NetworkSoft' : 'APP';
    }

    public function prioridad($class = false){
        if($class){
            if($this->prioridad == 1){
                return 'info';
            }elseif($this->prioridad == 2){
                return 'warning';
            }elseif($this->prioridad == 3){
                return 'danger';
            }
        }

        if($this->prioridad == 1){
            return 'Baja';
        }elseif($this->prioridad == 2){
            return 'Media';
        }elseif($this->prioridad == 3){
            return 'Alta';
        }
    }

    public function oficina(){
        return Oficina::find($this->oficina);
    }

    public function duracion(){
        $inicio = Carbon::parse($this->tiempo_ini);
        $cierre = Carbon::parse($this->tiempo_fin);
        $duracion = $inicio->diffInMinutes($cierre);
        return $duracion.' minutos';
    }

    public function cliente(){
        return Contacto::find($this->cliente);
    }
}