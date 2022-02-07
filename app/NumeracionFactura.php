<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Model\Ingresos\Factura;
class NumeracionFactura extends Model
{
    protected $table = "numeraciones_facturas";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'prefijo', 'inicio', 'final', 'desde', 'hasta', 'preferida', 'nroresolucion', 'resolucion', 'empresa', 'estado','inicioverdadero'
    ];

    public function preferida(){
            return $this->preferida==1?'Si':'No';
    }
    public function estado(){
        $estado=$this->estado==1?'Activo':'Inactivo';
        if ($this->hasta) {
            if ($this->hasta<date('Y-m-d')) {
                $estado.=' Vencida ';
            }
        }
        
        if ($this->inicio==$this->final) {
            $estado.='Finalizada ';
        }

        return $estado;
    }
     public function usado()
    {
        return Factura::where('numeracion',$this->id)->count();
        
    }
} 
