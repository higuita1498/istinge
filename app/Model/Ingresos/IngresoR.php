<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model; 
use App\Contacto; use App\Retencion; 
use App\Banco; use App\Impuesto; 
use App\Model\Ingresos\IngresosRemision; 
use App\Model\Ingresos\IngresosCategoria; 
use App\Model\Ingresos\IngresosRetenciones; 
use Auth; use DB;

class IngresoR extends Model
{
    protected $table = "ingresosr";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'empresa', 'cliente', 'cuenta', 'metodo_pago', 'fecha', 'observaciones', 'notas', 'tipo', 'estatus', 'created_at', 'updated_at'
    ];

    
    public function cliente(){
         return Contacto::where('id',$this->cliente)->first();
    }

    public function detalle(){
        $ingresos=IngresosRemision::where('ingreso', $this->id)->get();
        $Factura='';
        foreach ($ingresos as $ingreso) {
            $Factura.=" ".$ingreso->remision()->nro.",";
        }
        return 'Remisión:'.substr($Factura, 0, -1);
        
    }

    public function cuenta(){
        return Banco::where('id',$this->cuenta)->first();
    }

    public function pago(){
        return IngresosRemision::where('ingreso', $this->id)->sum('pago');
        

    }

    public function metodo_pago(){
       if ($this->metodo_pago) {
            return DB::table('metodos_pago')->where('id',$this->metodo_pago)->first()->metodo;
       }
    }

    public function total(){
        $totales=array('total'=>0, 'subtotal'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get(), 'reten'=>array());
        $totales["reten"]=Retencion::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $facturas=IngresosRemision::where('ingreso', $this->id)->get();
        foreach ($facturas as $factura) {
            $totales['subtotal']+=$factura->pago;
        }
         $totales['total']=$totales['subtotal'];
        foreach ($totales["reten"] as $key => $reten) {
            if ($totales["reten"][$key]->total>0) {
                $totales['total']-=$totales["reten"][$key]->total;
            }  
        }            
        
        
        return (object) $totales;

    }

  
    public function estatus($class=false){
        if ($class) {
            return $this->estatus==2?'warning':'';
        }
        if ($this->estatus==2) {
            return 'Anulado';
        }
    }


}
