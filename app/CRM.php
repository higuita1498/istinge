<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contrato;
use App\Contacto;
use App\User;
use App\CRM;
use App\TipoIdentificacion;
use App\Mikrotik;
use Auth;
use DB;
use App\GrupoCorte;
use App\Model\Ingresos\Factura;
use App\Etiqueta;

class CRM extends Model
{
    protected $table = "crm";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cliente', 'factura', 'estado', 'llamada', 'informacion', 'promesa_pago', 'fecha_pago', 'tiempo', 'notificacion', 'fecha_corte', 'servidor', 'created_by', 'updated_by', 'created_at', 'updated_at'
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
    
    public function cliente(){
        return Contacto::find($this->cliente);
    }
    
    public function estado($class = false){
        if ($class) {
            if($this->estado == 0){
                return 'warning';
            }else if($this->estado == 1){
                return 'success';
            }else if($this->estado == 2 || $this->estado == 4 || $this->estado == 5 || $this->estado == 6){
                return 'danger';
            }else if($this->estado == 3){
                return 'info';
            }
        }
        
        if($this->estado == 0){
            return 'SIN GESTIONAR';
        }else if($this->estado == 1){
            return 'GESTIONADO';
        }else if($this->estado == 2){
            return 'PROMESA INCUMPLIDA';
        }else if($this->estado == 3){
            return 'GESTIONADO/SIN CONTESTAR';
        }else if($this->estado == 4){
            return 'RETIRADO';
        }else if($this->estado == 5){
            return 'RETIRADO TOTAL';
        }else if($this->estado == 6){
            return 'GESTIONADO/NRO EQUIVOCADO';
        }
    }

    public function etiqueta(){
        return $this->belongsTo(Etiqueta::class);
    }
    
    public function created_by(){
        if($this->created_by){
            $user = User::find($this->created_by);
            return $user->nombres;
        }
        return '';
    }
    
    public function updated_by(){
        if($this->updated_by){
            $user = User::find($this->updatedd_by);
            return $user->nombres;
        }
        return '';
    }
    
    public function updated_at(){
        if($this->updated_at){
            return date('d-m-Y h:i:s', strtotime($this->updated_at));
        }
    }
    
    public function factura($class=false){
        if($class){
            return ($this->estatus == 0) ? 'success' : 'danger';
        }
        return ($this->estatus == 0) ? 'PAGADA' : 'SIN PAGAR';
    }
    
    public function servidor(){
        if($this->servidor){
            $mikrotik = Mikrotik::find($this->servidor);
            return $mikrotik->nombre;
        }
        return '';
    }

    public function grupo_corte(){
        if($this->grupo_corte){
            $grupo_corte = GrupoCorte::find($this->grupo_corte);
            return $grupo_corte->nombre;
        }
        return '';
    }

    public function factura_detalle(){
        if($this->factura){
            return Factura::find($this->factura);
        }
    }
}
