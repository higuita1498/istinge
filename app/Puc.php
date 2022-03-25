<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Puc extends Model
{
    protected $table = "puc";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'asociado', 'empresa', 'nro', 'nombre', 'codigo', 'estatus', 'descipcion', 'tercero', 'axi', 'id_grupo', 'id_tipo', 'id_balance', 'created_at', 'updated_at'
    ];

    public function hijos($array=false){
        if ($array) {
            return Puc::where('asociado', $this->nro)->whereIn('empresa', [1, Auth::user()->empresa] )->get();
        }
        return Puc::where('asociado', $this->nro)->whereIn('empresa', [1, Auth::user()->empresa] )->count();
    }

    public function asociado(){
        return Puc::where('nro', $this->asociado)->whereIn('empresa', [1, Auth::user()->empresa] )->first();
    }

    public function usado(){
        return Puc::where('asociado', $this->nro)->whereIn('empresa', [1, Auth::user()->empresa] )->count();

    }

    public function formasPago(){
        return $this->hasOne('App\FormaPago','cuenta_id');
    }
    
}
