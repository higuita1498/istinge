<?php

namespace App\Model\Inventario;

use Illuminate\Database\Eloquent\Model; 
use App\Model\Inventario\Bodega; 
use App\Model\Inventario\Inventario; use Auth; 
class AjusteInventario extends Model
{
    protected $table = "ajuste_inventario";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'nro', 'bodega', 'ajuste', 'producto', 'cant', 'fecha', 'observaciones', 'costo_unitario', 'created_at', 'updated_at'
    ];
    public function bodega(){
        return Bodega::where('empresa', Auth::user()->empresa)->where('id', $this->bodega)->first();
    }

    public function producto(){
        return Inventario::where('id', $this->producto)->first(); 
    }

    public function ajuste(){
        return $this->ajuste==1?'Incremento':'Disminución';
    }

}
 