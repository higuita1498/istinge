<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model; 
use App\Contacto; use App\Banco;
use App\Model\Ingresos\IngresosCategoria;
use App\Model\Ingresos\IngresosRetenciones; 
use App\Model\Ingresos\IngresosFactura; 
use App\Model\Gastos\NotaDedito; 
use App\Numeracion;  
use App\Retencion; 
use App\Impuesto; 
use App\Movimiento; 
use DB; use Auth;
use App\User;
use App\Puc;
class Ingreso extends Model
{
    protected $table = "ingresos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'empresa', 'cliente', 'cuenta', 'metodo_pago', 'fecha', 'observaciones', 'notas', 'tipo', 'estatus', 'created_at', 'updated_at', 'nota_debito', 'total_debito', 'nro_devolucion', 'created_by', 'updated_by'
    ];
    
    public function parsear($valor)
    {
        return number_format($valor, auth()->user()->empresa()->precision, auth()->user()->empresa()->sep_dec, (auth()->user()->empresa()->sep_dec == '.' ? ',' : '.'));
    }

    public function estatus($class=false){
        if ($class) {
            return $this->estatus==2?'warning':'';
        }
        if ($this->estatus==2) {
            return 'Anulado';
        }
    }
    
    public function deta(){
        return Factura::where('id', $this->factura)->first();
    }
    
    public function ingresofactura(){
        return IngresosFactura::where('ingreso', $this->id)->first();
    }
    
    public function factura(){
        return Factura::where('ingreso', $this->factura)->first();
    }
    
    public function tirilla(){
        $ingreso = IngresosFactura::where('ingreso', $this->id)->first();
        return Factura::find($ingreso->factura);
    }

    public function notas(){
        return NotaDedito::where('empresa',Auth::user()->empresa)->where('id', $this->nota_debito)->first();
    }


    public function cliente(){
         return Contacto::where('id',$this->cliente)->first();
    }

    public function detalle($pdf=false){
        if ($this->tipo==1) {
            $ingresos=IngresosFactura::where('ingreso', $this->id)->get();
            $Factura='';
            foreach ($ingresos as $ingreso) {
                $Factura.=" ".$ingreso->factura()->codigo.",";
            }
            return $pdf.'Factura de Venta:'.substr($Factura, 0, -1);
        }
        else if($this->tipo==2 || (!$pdf && $this->tipo==4 )){
            $gastos=IngresosCategoria::where('ingreso', $this->id)->select('categoria')->distinct()->get();
            $Factura='';
            foreach ($gastos as $gasto) {
                $Factura.=" ".($gasto->categoria(true)).",";
            }
            return $pdf.'Categorías:'.substr($Factura, 0, -1);
        } 
        else if($this->tipo==4){
            if ($pdf) {
               $mov1=Movimiento::where('modulo', 1)->where('id_modulo', $this->id)->first();
                $mov2=Movimiento::where('transferencia', $mov1->id)->first();
                return 'Transferencia bancaria de la cuenta '.$mov2->banco()->nombre.' a la cuenta '.$mov1->banco()->nombre;
            }            
        }
        else{
            $nota=NotaDedito::where('empresa',Auth::user()->empresa)->where('id', $this->nota_debito)->first();
            if ($pdf) {
                return $pdf.'Devolución en nota débito '.($nota->codigo?$nota->codigo:$nota->nro); die;
            }
            return 'Nota de Débito: '.($nota->codigo?$nota->codigo:$nota->nro); 
        }         
    }

    public function cuenta(){
        return Banco::where('id',$this->cuenta)->first();
    }

    public function pago(){
        if ($this->tipo==1) {
            $ingresos=IngresosFactura::where('ingreso',$this->id)->get();
            $total=0;
            foreach ($ingresos as $ingreso) {
                $total+=$ingreso->pago;
            }
            return $total;
        }
        elseif ($this->tipo==2 || $this->tipo==4) {
            return $this->total()->total;
        }
        else{
            return $this->total_debito;
        }
        

    }

    public function metodo_pago(){
       if ($this->metodo_pago) {
            return DB::table('metodos_pago')->where('id',$this->metodo_pago)->first()->metodo;
       }
    }

    public function total(){
        $totales=array('total'=>0, 'ivas'=>0, 'subtotal'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get(), 'reten'=>array());
        $totales["reten"]=Retencion::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        if ($this->tipo==1) {
            $facturas=IngresosFactura::where('ingreso', $this->id)->get();
            foreach ($facturas as $factura) {
                $totales['subtotal']+=$factura->pago;

                if ($factura->retenciones()) {
                    foreach ($totales["reten"] as $key => $reten) {
                        foreach ($factura->retenciones() as $retencion) {
                            if ($reten->id==$retencion->id_retencion) {
                                if (!isset($totales["reten"][$key]->total)) {
                                    $totales["reten"][$key]->total=0;
                                }      

                                $totales["reten"][$key]->total+=round($retencion->valor, 2);
                            }
                        }
                    }
                }
            }

             $totales['total']=$totales['subtotal'];
            foreach ($totales["reten"] as $key => $reten) {
                if ($totales["reten"][$key]->total>0) {
                    $totales['subtotal']+=$totales["reten"][$key]->total;
                }  
            }     


        }
        else{
            $items=IngresosCategoria::where('ingreso',$this->id)->get();
            $result=0; $desc=0; $impuesto=0;
            foreach ($items as $item) {
                $result=$item->valor*$item->cant;
                $totales['subtotal']+=round($result, 2);

                //SACAR EL IMPUESTO

                if ($item->impuesto>0) {
                    foreach ($totales["imp"] as $key => $imp) {
                        if ($imp->id==$item->id_impuesto) {
                             $impuesto=($result*$imp->porcentaje)/100;
                            if (!isset($totales["imp"][$key]->total)) {
                                $totales["imp"][$key]->total=0;
                            }
                            if ($imp->tipo==1) {
                                $totales["ivas"]+=$impuesto;
                            }
                            $totales["imp"][$key]->total+=$impuesto;
                        }
                    }
                }

            }
            $totales['total']=$totales['subtotal'];
            foreach ($totales["imp"] as $key => $imp) {
                $totales['total']+=$imp->total;
            }

             
            

            if (IngresosRetenciones::where('ingreso',$this->id)->count()>0) {
                $items=IngresosRetenciones::where('ingreso',$this->id)->get();

                foreach ($items as $item) {
                    foreach ($totales["reten"] as $key => $reten) {
                        if ($reten->id==$item->id_retencion) {
                            if (!isset($totales["reten"][$key]->total)) {
                                $totales["reten"][$key]->total=0;
                            }                        
                            $totales["reten"][$key]->total+=round($item->valor, 2);
                            $totales['total']-=round($item->valor, 2);

                        }
                    }
                }
            }
        }
        
        return (object) $totales;

    }

    public function created_by(){
        return User::find($this->created_by);
    }

    public function updated_by(){
        return User::find($this->updated_by);
    }

    public function ingresosCategorias(){
        return IngresosCategoria::where('ingreso',$this->id)->get();
    }
    

    public function ingresosFacturas(){
        return IngresosFactura::where('ingreso',$this->id)->get();
    }


    /* * * * Asociados a una categoria * * */
    public function ingresoAnticipo(){
        $anticipo = IngresosCategoria::join('anticipo as an','an.id','=','ingresos_categoria.anticipo')
        ->where('ingresos_categoria.ingreso',$this->id)->select('an.*')->first();

        if($anticipo){
            return $anticipo;
        }
    }
    
    public function ingresoPuc(){
        $puc = IngresosCategoria::join('puc as p','p.id','=','ingresos_categoria.categoria')
        ->where('ingresos_categoria.ingreso',$this->id)->select('p.*')->first();

        if($puc){
            return $puc;
        }
    }

    /* * * * Asociados a una(s) facturas * * */
    public function ingresoAnticipoFactura(){
        $anticipo = IngresosFactura::join('anticipo as an','an.id','=','ingresos_factura.anticipo')
        ->where('ingresos_factura.ingreso',$this->id)->select('an.*')->first();

        if($anticipo){
            return $anticipo;
        }
    }
    
    public function ingresoPucBanco(){
        $puc = IngresosFactura::join('puc as p','p.id','=','ingresos_factura.puc_banco')
        ->where('ingresos_factura.ingreso',$this->id)->select('p.*')->first();

        if($puc){
            return $puc;
        }
    }
    

}
