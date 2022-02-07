<?php

namespace App\Model\Inventario;

use Illuminate\Database\Eloquent\Model;  use Auth; 
use App\Model\Inventario\ProductosBodega;
use App\Model\Inventario\TransferenciasBodegas;
class ProductosTransferencia extends Model
{
    protected $table = "productos_transferencia";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'transferencia', 'bodega_origen', 'bodega_destino', 'producto', 'nro', 'created_at', 'updated_at'
    ];

    public function producto(){
        return ProductosBodega::join('inventario as inv', 'inv.id', '=', 'productos_bodegas.producto')->select('productos_bodegas.*', 'inv.producto', 'inv.ref', 'inv.id as id_producto')-> where('productos_bodegas.empresa',Auth::user()->empresa)->where('productos_bodegas.bodega', $this->transferencia()->bodega_origen)->where('inv.id', $this->producto)->first();
    }
    public function transferencia(){
        return TransferenciasBodegas::find($this->transferencia);
    }


}
 