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


class MovimientoLOG extends Model
{
    protected $table = "log_movimientos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'contrato', 'modulo', 'descripcion', 'created_by', 'created_at', 'updated_at'    
    ];

    public function modulo(){
        return Modulo::find($this->modulo);
    }
    public function created_by(){
        return ($this->created_by) ? User::find($this->created_by)->nombres : 'Usuario APP';
    }
}
