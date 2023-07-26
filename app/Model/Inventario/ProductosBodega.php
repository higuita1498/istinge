<?php

namespace App\Model\Inventario;

use Illuminate\Database\Eloquent\Model; 
use App\Model\Inventario\Bodega; use Auth; 
use App\Model\Inventario\ProductosTransferencia; 
class ProductosBodega extends Model
{
    protected $table = "productos_bodegas";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'empresa', 'bodega', 'producto', 'nro', 'inicial', 'created_at', 'updated_at'
    ];
    public function bodega(){
        return Bodega::where('empresa', Auth::user()->empresa)->where('id', $this->bodega)->first();
    }

    public function transferencias(){
        return ProductosTransferencia::where('bodega_destino', $this->bodega)->orwhere('bodega_origen', $this->bodega)->where('producto', $this->producto)->count(); 
    }

}
 