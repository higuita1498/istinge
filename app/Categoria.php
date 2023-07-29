<?php

namespace App;

use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosCategoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Categoria extends Model
{
    protected $table = "categorias";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'asociado', 'empresa', 'nro', 'nombre', 'codigo', 'descripcion', 'estatus'
    ];

    public function hijos($array=false){
        if ($array) {
            return Categoria::where('asociado', $this->nro)->whereIn('empresa', [1, Auth::user()->empresa] )->get();
        }
        return Categoria::where('asociado', $this->nro)->whereIn('empresa', [1, Auth::user()->empresa] )->count();
    }

    public function asociado(){
        return Categoria::where('nro', $this->asociado)->whereIn('empresa', [1, Auth::user()->empresa] )->first();
    }

    public function usado(){
        return Categoria::where('asociado', $this->nro)->whereIn('empresa', [1, Auth::user()->empresa] )->count();

    }

    public function catUsadaEnPago(){
        return GastosCategoria::where('categoria',$this->id)->count();
    }

}
