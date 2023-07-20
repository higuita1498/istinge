<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Model\Ingresos\Factura;
class TerminosPago extends Model
{
    protected $table = "terminos_pago";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'nombre', 'dias', 'empresa'
    ];

    public function usado()
    {
        return Factura::where('plazo',$this->id)->count();
        
    }
}
