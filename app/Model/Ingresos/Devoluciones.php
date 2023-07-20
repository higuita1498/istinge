<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model; 
use App\Contacto; use App\Banco; 
use App\Model\Gastos\Gastos;
use Auth;  use DB;

class Devoluciones extends Model
{
    protected $table = "notas_devolucion_dinero";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nota', 'empresa', 'fecha', 'monto', 'cuenta', 'observaciones', 'estatus', 'created_at', 'updated_at'
    ];

    
    public function cliente(){
        return Contacto::where('id',$this->cliente)->first();
    }

    public function detalle(){
        if ($this->tipo==1) {
            $ingresos=IngresosFactura::where('ingreso', $this->id)->get();
            $Factura='';
            foreach ($ingresos as $ingreso) {
                $Factura.=" ".$ingreso->factura()->codigo.",";
            }
            return 'Factura de Venta:'.substr($Factura, 0, -1);
        }
        
    }

    public function cuenta(){
        return Banco::where('id',$this->cuenta)->first();
    }

    public function gasto(){
        return Gastos::where('nota_credito', $this->nota)->where('nro_devolucion', $this->id)->first();
    }


  



}
