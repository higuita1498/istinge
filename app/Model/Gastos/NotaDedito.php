<?php

namespace App\Model\Gastos;

use App\Retencion;
use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Contacto;
use App\Impuesto; 
use App\TerminosPago;  use DB; 
use App\NotaRetencion;

use App\Model\Gastos\NotaDeditoFactura;
use App\Model\Gastos\ItemsNotaDedito;
use App\Model\Gastos\DevolucionesDebito;

use App\Model\Inventario\Bodega; 
class NotaDedito extends Model
{
    protected $table = "notas_debito";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'empresa', 'codigo', 'proveedor', 'fecha', 'observaciones', 'estatus', 'bodega', 'created_at', 'updated_at','emitida','dian_response','fecha_expedicion',
    ];

    public function proveedor(){
        return Contacto::where('id',$this->proveedor)->first();
    }

    
    public function total(){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0, 'totalreten' => 0,'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get());
        $items=ItemsNotaDedito::where('nota',$this->id)->get();
        $totales["reten"]= Retencion::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
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
                        $totales["imp"][$key]->totalprod+= $item->total();
                    }
                }
            }

            if (NotaRetencion::where('notas',$this->id)->count()>0) {
                $items=NotaRetencion::where('notas',$this->id)->get();

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

        }
        $totales['total']=$totales['subsub']=$totales['subtotal']-$totales['descuento']- $this->retenido_factura();
        foreach ($totales["imp"] as $key => $imp) {
            $totales['total']+=$imp->total;
        }
        return (object) $totales;

    }
    
    public function factura_detalle(){
        
    }
    
    public function impuestos_totales(){
        $total=0;
        foreach ($this->total()->imp as $value) {
            if ($value->tipo==1) {
                $total+=$value->total;
            }
        }
        return  $total;
    }
    
    public function cliente(){
         return Contacto::where('id',$this->proveedor)->first();
    }

    public function por_aplicar(){
        $facturas=NotaDeditoFactura::where('nota',$this->id)->sum('pago');
        $devoluciones=DevolucionesDebito::where('nota',$this->id)->sum('monto');
       return $facturas;//-$facturas-$devoluciones;
    }

    
    public function bodega(){
        $bodega=Bodega::where('empresa',Auth::user()->empresa)->where('id', $this->bodega)->first();
        if (!$bodega) { return ''; }
        return $bodega->bodega;
    }
    public function retenido_factura(){
        $retenciones = FacturaProveedoresRetenciones::join('notas_factura_debito as nf','nf.factura','=','factura_proveedores_retenciones.factura')->where('nf.nota',$this->id)->get();
        $total=0;
        $id=array();
        foreach ($retenciones as $retencion) {
            $total+=(float)$retencion->valor;
            $id[]=$retencion->id_retencion;
        }

        return $total;
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
}   
