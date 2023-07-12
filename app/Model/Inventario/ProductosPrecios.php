<?php

namespace App\Model\Inventario;

use Illuminate\Database\Eloquent\Model;
use App\Model\Inventario\ListaPrecios; use Auth; 
class ProductosPrecios extends Model
{
    protected $table = "productos_precios";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'empresa', 'lista', 'producto', 'precio', 'created_at', 'updated_at'
    ];

    public function lista(){
        return ListaPrecios::where('empresa', Auth::user()->empresa)->where('id', $this->lista)->first();
    }

}
