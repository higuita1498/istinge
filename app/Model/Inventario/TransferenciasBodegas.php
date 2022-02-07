<?php

namespace App\Model\Inventario;

use Illuminate\Database\Eloquent\Model; 
use App\Model\Inventario\Bodega; use Auth; 
class TransferenciasBodegas extends Model
{
    protected $table = "transferencias_bodegas";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'empresa', 'bodega_origen', 'nro', 'bodega_destino', 'fecha', 'observaciones', 'created_at', 'updated_at'
    ];
    public function bodega($tipo='origen'){
        $tipo='bodega_'.$tipo;
        return Bodega::where('empresa', Auth::user()->empresa)->where('id', $this->$tipo)->first();
    }

}
 