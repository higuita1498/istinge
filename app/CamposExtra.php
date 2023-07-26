<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB; use Auth;
class CamposExtra extends Model
{
    protected $table = "campos_extra_inventario";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa', 'campo', 'nombre', 'varchar', 'tipo', 'default', 'status', 'tabla', 'descripcion', 'autocompletar'
    ];

    public function tipo(){
        return $this->tipo==1?'Si':'No';
    }
    public function status(){
        return $this->status==1?'Activo':'Inactivo';
    }
    public function autocompletar(){
        return $this->autocompletar==1?'Activo':'Inactivo';
    }
    
    public function tabla(){
        if ($this->tabla==0) {
            return 'No Aparece';
        }
        return 'Posición '.($this->tabla);
    }

    public function usado(){
        return DB::table('inventario_meta')->where('empresa', Auth::user()->empresa)->where('meta_key', $this->campo)->count()+DB::table('inventario_volatil_meta')->where('empresa', Auth::user()->empresa)->where('meta_key', $this->campo)->count();
    }

    public function records(){
        $datos= DB::table('inventario_meta')->where('empresa', Auth::user()->empresa)->where('meta_key', $this->campo)->select('meta_value')->distinct()->get();
        $array=array();
        foreach ($datos as $key => $value) {
            if ($value->meta_value != null) {
            $array[]=$value->meta_value;
            }
        }
         return $array;
    }
    
}
