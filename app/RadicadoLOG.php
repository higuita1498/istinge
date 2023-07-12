<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Auth;
use DB;

use App\Modulo;
use App\User;
use App\Contrato;
use App\Funcion;


class RadicadoLOG extends Model
{
    protected $table = "log_radicados";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'id_radicado', 'id_usuario', 'accion', 'created_at', 'updated_at'
    ];

    public function id_usuario(){
        return ($this->id_usuario) ? User::find($this->id_usuario)->nombres : 'Usuario APP';
    }
}
