<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\User;
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
        'cliente','fecha','nombre', 'telefono', 'correo', 'direccion', 'contrato', 'desconocido', 'estatus', 'codigo','empresa','firma'
    ];

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
            $status = 'Resuelto';
        }elseif($this->estatus == 2){
            $status = 'Escalado / Pendiente';
        }elseif($this->estatus == 3){
            $status = 'Escalado / Resuelto';
        }
        return $status;
    }
    
    public function show_url(){
        return route('radicados.show', $this->id);
    }
}