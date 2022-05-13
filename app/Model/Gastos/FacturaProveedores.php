<?php

namespace App\Model\Gastos;
use App\TerminosPago;
use Illuminate\Database\Eloquent\Model;
use App\Contacto; use App\Impuesto; 
use App\Model\Gastos\ItemsFacturaProv;
use App\Model\Gastos\FacturaProveedoresRetenciones;
use App\Model\Gastos\GastosFactura;
use App\Funcion;
use Auth; use App\Model\Inventario\Bodega; 
use App\Retencion; 
use App\Model\Gastos\Ordenes_Compra; 
use App\Model\Gastos\NotaDeditoFactura;
use Carbon\Carbon;
use App\FormaPago;
use App\Puc;
use DB;


class FacturaProveedores extends Model
{
    protected $table = "factura_proveedores";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'orden_nro', 'empresa','codigo', 'tipo', 'proveedor', 'fecha_factura', 'vencimiento_factura', 'observaciones', 'observaciones_factura', 'estatus', 'notas', 'created_at', 'updated_at', 'bodega', 'term_cond'    
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

    public function orden(){
        return Ordenes_Compra::where('id',$this->id)->first();
    }

    public function plazo(){
        if($this->plazo){
            if ($this->plazo=='n') {
            return 'Vencimiento Manual';
            }else{
                return TerminosPago::where('id',$this->plazo)->first()->nombre;
            }    
        }else{
            return '';
        }
        
        
    }

    public function proveedor(){
        return Contacto::where('id',$this->proveedor)->first();
    }

    public function estatus($class=false){
        if ($this->tipo==1) {
            if ($class) {
                return 'success';
            }
            return 'Facturada';
        }

        
        if ($this->estatus==0) {
            if ($class) {
                return '';
            }
            return 'Facturada';
        }
        else if ($this->estatus==2) {
            if ($class) {
                return 'warning';
            }
            return 'Anulada';
        }
    }

    public function total(){
        $totales=array('totaltotal'=>0, 'total'=>0, 'ivas'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get(), 'reten'=>array(), 'totalreten'=>0);
        $totales["reten"]=Retencion::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $items=ItemsFacturaProv::where('factura',$this->id)->get();
        $result=0; $desc=0; $impuesto=0;
        foreach ($items as $item) {
            $result=$item->precio*$item->cant;
            $totales['subtotal']+=$result;

            //SACAR EL DESCUENTO 
            if ($item->desc>0) {
                $desc=($result*$item->desc)/100;
            }
            else{ $desc=0; }

            $totales['descuento']+=$desc;
            $result=$result-$desc;

            //SACAR EL IMPUESTO
            if ($item->impuesto>0) {
                foreach ($totales["imp"] as $key => $imp) {
                    if ($imp->id==$item->id_impuesto) {
                         $impuesto=($result*$imp->porcentaje)/100;
                        if (!isset($totales["imp"][$key]->total)) {
                            $totales["imp"][$key]->total=0;
                        }
                        $totales["imp"][$key]->total+=$impuesto;
                        if ($imp->tipo==1) {
                            $totales["ivas"]+=$impuesto;
                            
                        }
                    }
                }
            }
        }

         if (FacturaProveedoresRetenciones::where('factura',$this->id)->count()>0) {
                $items=FacturaProveedoresRetenciones::where('factura',$this->id)->get();

                foreach ($items as $item) {
                    foreach ($totales["reten"] as $key => $reten) {
                        if ($reten->id==$item->id_retencion) {
                            if (!isset($totales["reten"][$key]->total)) {
                                $totales["reten"][$key]->total=0;
                            }                        
                            $totales["reten"][$key]->total+=$item->valor;
                            $totales['totalreten']+=$item->valor;

                        }
                    }
                }
            }


        $totales['total']=$totales['subsub']=$totales['subtotal']-$totales['descuento'] - $this->retenido();
        $totales['subtotal2'] =  $totales['subsub']=$totales['subtotal']-$totales['descuento'];
        foreach ($totales["imp"] as $key => $imp) {
            $totales['total']+=$imp->total;
        }
        $totales['totaltotal']=$totales['total']-$totales['totalreten'];
        return (object) $totales;

    }

    public function retenido_factura(){
        $retenciones = FacturaProveedoresRetenciones::where('factura',$this->id)->get();
        $total=0;
        $id=array();
        foreach ($retenciones as $retencion) {
            $total+=(float)$retencion->valor;
            $id[]=$retencion->id_retencion;
        }

        return $total;
    }



    public function bodega(){
        $bodega=Bodega::where('empresa',Auth::user()->empresa)->where('id', $this->bodega)->first();
        if (!$bodega) { return ''; }
        return $bodega->bodega;
    }

    public function pagado(){
        $pagos=GastosFactura::where('factura',$this->id)->get();
        $total = 0;
        foreach ($pagos as $pago){
            $gasto = Gastos::find($pago->gasto);
            if($gasto){
                $total += $gasto->estatus == 2 ? 0 : $pago->pago;
            }
        }
        /*$total+=$this->retenido();*/
        return $total;
    }     

    public function retenido($por_id=false){
        $retenciones=FacturaProveedoresRetenciones::where('factura',$this->id)->get();
        $total=0;
        $id=array();
        foreach ($retenciones as $retencion) {
            $total+=(float)$retencion->valor;
            $id[]=$retencion->id_retencion;
        }
        if ($por_id) { return $id; }

        return $total;
    }

    public function porpagar(){
         $porpagar = Funcion::precision($this->total()->total);
        return abs($porpagar  - $this->pagado() - $this->devoluciones());
    } 
    
    public function gastos(){
        return GastosFactura::where('factura', $this->id)->get();
    }

    public function retencions_previas(){
        return json_encode($this->retenido(true));
    }

    public function impuestos_totales(){
        return $this->total()->ivas;
    }

    public function devoluciones(){
        return NotaDeditoFactura::where('factura',$this->id)->sum('pago');
       
    }

    public function notas_debito($cont=false){
        $notas=NotaDeditoFactura::where('factura', $this->id);
        if ($cont) {
            return $notas->count();
        }

        return $notas->get();

    }
    
    public function cliente(){
         return Contacto::where('id',$this->proveedor)->first();
    }
    
    
     public function info_cufe($id, $impTotal)
    {
            $factura = FacturaProveedores::find($id);
           $infoCufe = [
              'Numfac' => $factura->codigo,
              'FecFac' => Carbon::parse($factura->created_at)->format('Y-m-d'),
              'HorFac' => Carbon::parse($factura->created_at)->format('H:i:s').'-05:00',
              'ValFac' => $factura->total()->subtotal.'.00',
              'CodImp' => '01',
              'ValImp' => $impTotal.'.00',
              'CodImp2'=> '04',
              'ValImp2'=> '0.00',
              'CodImp3'=> '03',
              'ValImp3'=> '0.00',
              'ValTot' => number_format($factura->total()->subtotal + $factura->impuestos_totales(), 2, '.', ''),
              'NitFE'  => Auth::user()->empresa()->nit,
              'NumAdq' => $factura->cliente()->nit,
              'ClvTec' => 'fc8eac422eba16e22ffd8c6f94b3f40a6e38162c',
              'TipoAmb'=> 2,
          ];
    
      $CUFE = $infoCufe['Numfac'].$infoCufe['FecFac'].$infoCufe['HorFac'].$infoCufe['ValFac'].$infoCufe['CodImp'].$infoCufe['ValImp'].$infoCufe['CodImp2'].$infoCufe['ValImp2'].$infoCufe['CodImp3'].$infoCufe['ValImp3'].$infoCufe['ValTot'].$infoCufe['NitFE'].$infoCufe['NumAdq'].$infoCufe['ClvTec'].$infoCufe['TipoAmb'];
    
         return hash('sha384',$CUFE);
    }
    
    public function getDateAttribute()
    {
        return [
            'primera' => FacturaProveedores::where('empresa', Auth::user()->empresa)->whereNotNull('fecha')
                ->get()
                ->first()->fecha,
            'ultima' => FacturaProveedores::where('empresa', Auth::user()->empresa)
                ->get()
                ->last()->fecha
        ];
    }

    public function formaPago(){
        $forma = FormaPago::find($this->cuenta_id);

        if($forma){
            return Puc::find($forma->cuenta_id); 
        }
    }

    public function itemsFactura()
    {
        return $this->hasMany(ItemsFacturaProv::class,'factura');
    }

}
