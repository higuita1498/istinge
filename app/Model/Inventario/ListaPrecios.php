<?php

namespace App\Model\Inventario;

use Illuminate\Database\Eloquent\Model; 
use App\Model\Inventario\ProductosPrecios; 
use Auth; 
class ListaPrecios extends Model
{
    protected $table = "lista_precios";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'nro', 'empresa', 'nombre', 'tipo', 'status', 'porcentaje',  'created_at', 'updated_at'
    ];

    public function status()
    {
        return $this->status==1?'Activo':'Inactiva';
    }

    public function uso(){
        return ProductosPrecios::where('empresa', Auth::user()->empresa)->where('lista', $this->id)->count();
    }

    public function principal(){
        return $this->nro==1?'Si':'No';
    }

    public function tipo(){
        return $this->tipo==1?'Porcentaje ('.$this->porcentaje.'%)':'Valor';
    }

    public function nombre(){
        return $this->nombre.($this->tipo==1?' ('.$this->porcentaje.'%)':'');
    }

}
