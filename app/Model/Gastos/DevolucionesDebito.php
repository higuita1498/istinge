<?php

namespace App\Model\Gastos;

use Illuminate\Database\Eloquent\Model; use DB;
use App\Contacto; use App\Banco; use Auth;  
use App\Model\Ingresos\Ingreso;
class DevolucionesDebito extends Model
{
    protected $table = "notas_debito_devolucion_dinero";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nota', 'empresa', 'fecha', 'monto', 'cuenta', 'observaciones', 'estatus', 'created_at', 'updated_at'
    ];

    
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

    public function ingreso(){
        return Ingreso::where('nota_debito', $this->nota)->where('nro_devolucion', $this->id)->first();
    }


  



}
