<?php

namespace App\Model\Gastos;
use Illuminate\Database\Eloquent\Model;

use App\Contacto; use App\Banco;
use App\Impuesto; use App\Retencion;
use App\Funcion;
use App\Movimiento;
use App\Model\Gastos\GastosFactura; 
use App\Model\Gastos\GastosCategoria; 
use App\Model\Gastos\GastosRetenciones; 
use App\Model\Gastos\FacturaProveedoresRetenciones;
use App\Model\Gastos\GastosRecurrentesCategoria;
use App\Model\Ingresos\NotaCredito; 
use App\Model\Ingresos\Devoluciones;
use Auth; use DB;
use App\User;
class Gastos extends Model
{
    protected $table = "gastos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'empresa', 'beneficiario', 'cuenta', 'metodo_pago', 'fecha', 'observaciones', 'notas', 'tipo', 'estatus', 'created_at', 'updated_at', 'nota_credito', 'total_credito', 'nro_devolucion', 'created_by', 'updated_by'
    ];

    protected $appends = ['session'];

    public function getSessionAttribute(){
        return $this->getAllPermissions(Auth::user()->id);
    }

    public function getAllPermissions($id){
        if(Auth::user()->rol>=2){
            if (DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->count() > 0 ) {
                $permisos = DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->get();
                foreach ($permisos as $key => $value) {
                    $_SESSION['permisos'][$permisos[$key]->id_permiso] = '1';
                }
                return $_SESSION['permisos'];
            }
            else return null;
        }
    }

    public function parsear($valor){
        return number_format($valor, auth()->user()->empresa()->precision, auth()->user()->empresa()->sep_dec, (auth()->user()->empresa()->sep_dec == '.' ? ',' : '.'));
    }

    public function estatus($class=false){
        if ($class) {
            return $this->estatus==2?'warning':'';
        }
        if ($this->estatus==2) {
            return 'Anulado';
        }
        return $this->estatus == 1 ? "Consolidado": "No consolidado";
    }

    public function beneficiario(){
         return Contacto::where('id',$this->beneficiario)->first();
    }

    public function metodo_pago(){
       if ($this->metodo_pago) {
            return DB::table('metodos_pago')->where('id',$this->metodo_pago)->first()->metodo;
       }
    }

    public function detalle($pdf=false){
        if ($this->tipo==1) {
            $gastos=GastosFactura::where('gasto', $this->id)->get();
            $Factura='';
            $i = 0;
            foreach ($gastos as $gasto) {
                $Factura.=" ".($gasto->factura()->nro).",";
                $i++;
                if($i > 2){
                    $Factura.=". . .";
                    break;
                }
            }
            return 'Facturas de Proveedor:'.substr($Factura, 0, -1);
        }
        else if($this->tipo==2 || $this->tipo==4 || $this->tipo==5){
            $gastos=GastosCategoria::where('gasto', $this->id)->select('categoria')->distinct()->get();
            $Factura='';
            foreach ($gastos as $gasto) {
                $Factura.=" ".($gasto->categoria(true)).",";
            }
            return 'Categorías:'.substr($Factura, 0, -1);
        }
        else if($this->tipo == 5)
        {
            $gastos=GastosRecurrentesCategoria::where('gasto_recurrente', $this->nro)->select('categoria')->distinct()->get();
            $Factura='';
            foreach ($gastos as $gasto) {
                $Factura.=" ".($gasto->categoria(true)).",";
            }
            return 'Categorías:'.substr($Factura, 0, -1);
        }
        else{
            if ($pdf) {
                return 'Devolución en nota crédito '.NotaCredito::where('empresa',Auth::user()->empresa)->where('id', $this->nota_credito)->first()->nro; die;
            }
            return 'Nota de Crédito: '.NotaCredito::where('empresa',Auth::user()->empresa)->where('id', $this->nota_credito)->first()->nro; 
        }        
    }

    public function cuenta(){
        return Banco::where('id',$this->cuenta)->first();
    }

    public function pago(){
        if ($this->tipo==1) {
            $gastos=GastosFactura::where('gasto',$this->id)->get();
            $total=0;
            foreach ($gastos as $gasto) {
                if($this->estatus == 2){
                    $tmp =  Movimiento::where('modulo', 3)->where('id_modulo', $this->id)->first();
                    if($tmp){
                        $total = Movimiento::where('modulo', 3)->where('id_modulo', $this->id)->first()->saldo;  
                        break;
                    }
                }
                $total+=$gasto->pago+(float)$gasto->retencion();
            }
            return $total;
        }else if($this->tipo==2 || $this->tipo==4 || $this->tipo==5){
            return $this->total()->total;
        }else if($this->tipo == 5){
            $gastosrecurrentes = GastosRecurrentes::where('empresa',Auth::user()->empresa)->where('id',$this->nro)->first();
            if ($gastosrecurrentes != null) {
                return   $gastosrecurrentes->total()->total;
            }else{
                return 0;
            }
        }else{
            return $this->total_credito;
        }
    }

    public function total(){
        $totales=array('total'=>0, 'ivas'=>0, 'subtotal'=>0, 'totalreten'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get(), 'reten'=>array());
        $totales["reten"]=Retencion::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        if ($this->tipo==1) {
            $facturas=GastosFactura::where('gasto', $this->id)->get();
            foreach ($facturas as $factura) {
                $totales['subtotal']+=$factura->pago;
                if ($factura->retenciones()) {
                    foreach ($totales["reten"] as $key => $reten) {
                        foreach ($factura->retenciones() as $retencion) {
                            if ($reten->id==$retencion->id_retencion) {
                                if (!isset($totales["reten"][$key]->total)) {
                                    $totales["reten"][$key]->total=0;
                                }  
                                $totales["totalreten"]+=$retencion->valor;             
                                $totales["reten"][$key]->total+=$retencion->valor;
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
            $items=GastosCategoria::where('gasto',$this->id)->get();
            $result=0; $desc=0; $impuesto=0;
            foreach ($items as $item) {
                $result=$item->valor*$item->cant;
                $totales['subtotal']+=$result;
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

            if (GastosRetenciones::where('gasto',$this->id)->count()>0) {
                $items=GastosRetenciones::where('gasto',$this->id)->get();
                foreach ($items as $item) {
                    foreach ($totales["reten"] as $key => $reten) {
                        if ($reten->id==$item->id_retencion) {
                            if (!isset($totales["reten"][$key]->total)) {
                                $totales["reten"][$key]->total=0;
                            }                        
                            $totales["reten"][$key]->total+=$item->valor;
                            $totales['total']-=$item->valor;
                        }
                    }
                }
            }
        }        
        return (object) $totales;
    }

    public function notas(){
        return NotaCredito::where('empresa',Auth::user()->empresa)->where('id', $this->nota_credito)->first();
    }

    public function retenciones_facturas($cont=false){
        $facturas=GastosFactura::where('gasto', $this->id)->get();
        $id=array();
        foreach ($facturas as $factura) {
            $id[]=$factura->factura;
        }
        if (!$id) {
            return count($id);
        }



        $retenciones='';
        $datos=FacturaProveedoresRetenciones::whereIn('factura', $id)->select(DB::raw('SUM(valor) AS valor'), 'retencion', DB::raw('(Select nombre from retenciones where id = factura_proveedores_retenciones.id_retencion) as nombre'));

        if ($cont) {
            return $datos->count();
        }

        $datos=$datos->groupBy('id_retencion')->get();
        foreach ($datos as $reten) {
            $retenciones.="*".$reten->nombre." (".$reten->retencion."%): ".Funcion::Parsear($reten->valor)." ";
        }
        return $retenciones;
    }

    public function created_by(){
        return User::find($this->created_by);
    }

    public function updated_by(){
        return User::find($this->updated_by);
    }

    /* * * * Asociados a una categoria * * */
    public function gastoAnticipo(){
        $anticipo = IngresosCategoria::join('anticipo as an','an.id','=','gastos_categoria.anticipo')
        ->where('gastos_categoria.gasto',$this->id)->select('an.*')->first();

        if($anticipo){
            return $anticipo;
        }
    }
    
    public function gastoPuc(){
        $puc = IngresosCategoria::join('puc as p','p.id','=','gastos_categoria.categoria')
        ->where('gastos_categoria.gasto',$this->id)->select('p.*')->first();

        if($puc){
            return $puc;
        }
    }

    /* * * * Asociados a una(s) facturas * * */
    public function gastoAnticipoFactura(){
        $anticipo = IngresosFactura::join('anticipo as an','an.id','=','gastos_factura.anticipo')
        ->where('gastos_factura.gasto',$this->id)->select('an.*')->first();

        if($anticipo){
            return $anticipo;
        }
    }
    
    public function gastoPucBanco(){
        $puc = IngresosFactura::join('puc as p','p.id','=','gastos_factura.puc_banco')
        ->where('gastos_factura.gasto',$this->id)->select('p.*')->first();

        if($puc){
            return $puc;
        }
    }
}
