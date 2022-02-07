<?php

namespace App\Model\Inventario;

use Illuminate\Database\Eloquent\Model; 
use App\Model\Inventario\ProductosBodega; 
use Auth; 
class Bodega extends Model
{
    protected $table = "bodegas";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'empresa', 'bodega', 'direccion', 'observaciones', 'principal', 'status', 'created_at', 'updated_at'
    ];

    public function status()
    {
        return $this->status==1?'Activo':'Inactiva';
    }

    public function uso(){
        return ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $this->id)->count();
    }

}
