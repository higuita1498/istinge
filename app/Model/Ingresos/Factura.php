<?php

namespace App\Model\Ingresos;

use App\Empresa;
use App\Retencion;
use Illuminate\Database\Eloquent\Model;
use App\Contacto;
use App\Contrato;
use App\Impuesto; use App\Vendedor;
use App\Funcion; use Auth;
use App\TerminosPago;
use App\Banco;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\IngresosFactura;
use App\Model\Ingresos\NotaCreditoFactura;
use App\Model\Ingresos\IngresosRetenciones;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\Bodega;
use Carbon\Carbon;
use DB;
use App\GrupoCorte;
class Factura extends Model
{
    protected $table = "factura";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'nro_remision','cot_nro', 'empresa','codigo',  'numeracion',
        'vendedor', 'tipo', 'cliente', 'fecha', 'vencimiento', 'observaciones',
        'estatus', 'notas', 'plazo', 'created_at', 'updated_at', 'term_cond',
        'facnotas' , 'lista_precios', 'bodega','emitida','dian_response',
        'nonkey', 'statusdian', 'observacionesdian', 'modificado','fecha_expedicion',
        'tipo_fac','tipo_operacion', 'promesa_pago', 'contrato_id'
    ];
    
    public function parsear($valor)
    {
        return number_format($valor, auth()->user()->empresa()->precision, auth()->user()->empresa()->sep_dec, (auth()->user()->empresa()->sep_dec == '.' ? ',' : '.'));
    }

    public function cliente(){
         return Contacto::where('id',$this->cliente)->first();
    }
    
    public function banco(){
         return Banco::where('id',$this->cuenta)->first();
    }
    
    public function contrato(){
        $cliente = Contacto::where('id',$this->cliente)->first();
        return Contrato::where('client_id',$cliente->UID)->first();
    }
    
    public function servidor(){
        $cliente = Contacto::where('id',$this->cliente)->first();
        $contrato = Contrato::where('client_id',$cliente->UID)->first();
        return DB::table('servidores')->where('id',$contrato->server_configuration_id)->first();
    }

    public function estatus($class=false){
        if ($class) {
            if ($this->estatus==2) {
               return 'warning';
            }
            return $this->estatus==1?'danger':'success';
        }

        if ($this->estatus==2) {
            $mensaje = 'Anulada';
        }
        if ($this->estatus==3) {
            $mensaje = 'Reconexión';
        }
        $mensaje = $this->estatus==1?'Abierta':'Cerrada';

        if(isset($this->tipo) && $this->tipo == 2){
            if($this->emitida == 1){
                $mensaje.="-emitida";
            }else{
                $mensaje.="-no emitida";
            }
        }

        return $mensaje;
    }



    public function total(){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get(), 'totalreten'=>0);
        $items=ItemsFactura::where('factura',$this->id)->get();
        $totales["reten"]=Retencion::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $result=0; $desc=0; $impuesto=0;
        $totales["TaxExclusiveAmount"] = 0;

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
            $totales['resul'] = $totales['subtotal'] - $totales['descuento'];

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
            //Facturacion electronica obtenemos el TaxExclusiveAmount (total sobre el cual se calculan los ivas de los items)
            if ($item->impuesto != null) {
                $totales['TaxExclusiveAmount'] += ($item->precio * $item->cant) - $desc;
            }
        }

        if (FacturaRetencion::where('factura',$this->id)->count()>0) {
            $items=FacturaRetencion::where('factura',$this->id)->get();

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

        $totales['total']=$totales['subsub']=$totales['subtotal']-$totales['descuento'] - $this->retenido_factura()
            - $this->retenido();
        foreach ($totales["imp"] as $key => $imp) {
            $totales['total']+=$imp->total;
        }
        return (object) $totales;

    }

    public function totalAPI($empresaId){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0, 'imp'=>Impuesto::where('empresa',$empresaId)->orWhere('empresa', null)->Where('estado', 1)->get(), 'totalreten'=>0);
        $items=ItemsFactura::where('factura',$this->id)->get();
        $totales["reten"]=Retencion::where('empresa',$empresaId)->orWhere('empresa', null)->Where('estado', 1)->get();
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
            $totales['resul'] = $totales['subtotal'] - $totales['descuento'];

            //SACAR EL IMPUESTO
            if ($item->impuesto>0) {
                foreach ($totales["imp"] as $key => $imp) {
                    if ($imp->id==$item->id_impuesto) {
                        $impuesto=($result*$imp->porcentaje)/100;
                        if (!isset($totales["imp"][$key]->total)) {
                            $totales["imp"][$key]->total=0;
                        }
                        $totales["imp"][$key]->total+=$impuesto;
                    }
                }
            }
        }

        if (FacturaRetencion::where('factura',$this->id)->count()>0) {
            $items=FacturaRetencion::where('factura',$this->id)->get();

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

        $totales['total']=$totales['subsub']=$totales['subtotal']-$totales['descuento'] - $this->retenido_factura()
            - $this->retenido();
        foreach ($totales["imp"] as $key => $imp) {
            $totales['total']+=$imp->total;
        }
        return (object) $totales;

    }

    public function plazo(){
        if ($this->plazo=='n') {
            return 'Vencimiento Manual';
        }
        return TerminosPago::where('id',$this->plazo)->first()->nombre;
    }

    public function vendedor(){
        if (!$this->vendedor) {
            return '';
        }
        return Vendedor::where('id',$this->vendedor)->first()->nombre;
    }

    public function pagado(){
        $total=IngresosFactura::where('factura',$this->id)->whereRaw('(SELECT estatus FROM ingresos where id = ingresos_factura.ingreso) <> 2 ')->sum('pago');
        //$total+=$this->retenido();
        return $total;
    }

    public function retenido($por_factura = false){
        $ingresos=IngresosFactura::where('factura',$this->id)->whereRaw('(SELECT estatus FROM ingresos where id = ingresos_factura.ingreso) <> 2 ')->get();
        $total=0;
        foreach ($ingresos as $ingreso) {
            $total+=(float)$ingreso->retencion();
        }
        if($por_factura)
        {
            $total += $this->retenido_factura();
        }
        return $total;
    }

    public function retenido_factura(){
        $retenciones=FacturaRetencion::where('factura',$this->id)->get();
        $total=0;
        $id=array();
        foreach ($retenciones as $retencion) {
            $total+=(float)$retencion->valor;
            $id[]=$retencion->id_retencion;
        }

        return $total;
    }

    public function retenciones(){
        $ingresos=IngresosRetenciones::where('factura',$this->id)->get();
        $retencion=" ";
        foreach ($ingresos as $key => $ingreso) {
             $retencion .= " ".$ingreso->retencion()->nombre.' ('.$ingreso->retencion()->porcentaje.'%) '.Auth::user()->empresa()->moneda .Funcion::Parsear($ingreso->valor).($key<$ingresos->count()-1?",":'');

        }
        return $retencion;
    }

    public function porpagar(){
         $porpagar = Funcion::precision($this->total()->total);
        return abs($porpagar - $this->pagado() - $this->devoluciones());
        //return abs($this->total()->total - $this->pagado() - $this->devoluciones() );
    }

    public function devoluciones(){
        return NotaCreditoFactura::where('factura',$this->id)->sum('pago');

    }

    public function pagos($cont=false){
        if ($cont) {
            return IngresosFactura::where('factura',$this->id)->count();
        }
        return IngresosFactura::where('factura',$this->id)->get();
    }

    public function lista_precios(){
        $lista=ListaPrecios::where('empresa',Auth::user()->empresa)->where('id', $this->lista_precios)->first();
        if (!$lista) { return ''; }
        return $lista->nombre();
    }
    public function bodega(){
        $bodega=Bodega::where('empresa',Auth::user()->empresa)->where('id', $this->bodega)->first();
        if (!$bodega) { return ''; }
        return $bodega->bodega;
    }

    public function retenciones_previas(){
        $ingresos=IngresosFactura::where('factura',$this->id)->get();
        $total=0;
        $id=array();
        foreach ($ingresos as $ingreso) {
            $retenciones=$ingreso->retenciones();
            foreach ($retenciones as $retencion) {
                $id[]=$retencion->id_retencion;
            }
        }

        return json_encode($id);
    }

    public function retenciones_previas_actual($ingreso){
        $ingresos=IngresosFactura::where('factura',$this->id)->where('ingreso', '<>', $ingreso)->get();
        $total=0;
        $id=array();
        foreach ($ingresos as $ingreso) {
            $retenciones=$ingreso->retenciones();
            foreach ($retenciones as $retencion) {
                $id[]=$retencion->id_retencion;
            }
        }

        return json_encode($id);

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

    public function impuestos_totalesFe(){
        $total=0;
        foreach ($this->totalAPI($this->empresa)->imp as $value) {
            if ($value->tipo==1) {
                $total+=$value->total;
            }
        }
        return  $total;
    }

    public function notas_credito($cont=false){
        $notas=NotaCreditoFactura::where('factura', $this->id);
        if ($cont) {
            return $notas->count();
        }

        return $notas->get();

    }

    public function info_cufe($id, $impTotal)
{
    $factura = Factura::find($id);
   $infoCufe = [
      'Numfac' => $factura->codigo,
      'FecFac' => Carbon::parse($factura->created_at)->format('Y-m-d'),
      'HorFac' => Carbon::parse($factura->created_at)->format('H:i:s').'-05:00',
      'ValFac' => number_format($factura->total()->subtotal - $factura->total()->descuento,2,'.',''),
      'CodImp' => '01',
      'ValImp' => number_format($impTotal,2, '.',''),
      'CodImp2'=> '04',
      'ValImp2'=> '0.00',
      'CodImp3'=> '03',
      'ValImp3'=> '0.00',
      'ValTot' => number_format($factura->total()->subtotal + $factura->impuestos_totales() - $factura->total()->descuento, 2, '.', ''),
      'NitFE'  => Auth::user()->empresa()->nit,
      'NumAdq' => $factura->cliente()->nit,
      'ClvTec' => Auth::user()->empresa()->technicalkey,
      'TipoAmb'=> 1,
  ];

   $CUFE = $infoCufe['Numfac'].$infoCufe['FecFac'].$infoCufe['HorFac'].$infoCufe['ValFac'].$infoCufe['CodImp'].$infoCufe['ValImp'].$infoCufe['CodImp2'].$infoCufe['ValImp2'].$infoCufe['CodImp3'].$infoCufe['ValImp3'].$infoCufe['ValTot'].$infoCufe['NitFE'].$infoCufe['NumAdq'].$infoCufe['ClvTec'].$infoCufe['TipoAmb'];

     return hash('sha384',$CUFE);
}

    public function info_cufeAPI($id, $impTotal, $empresa)
    {
        $factura = Factura::find($id);
        $infoCufe = [
            'Numfac' => $factura->codigo,
            'FecFac' => Carbon::parse($factura->created_at)->format('Y-m-d'),
            'HorFac' => Carbon::parse($factura->created_at)->format('H:i:s').'-05:00',
            'ValFac' => $factura->totalAPI($empresa)->subtotal.'.00',
            'CodImp' => '01',
            'ValImp' => $impTotal.'.00',
            'CodImp2'=> '04',
            'ValImp2'=> '0.00',
            'CodImp3'=> '03',
            'ValImp3'=> '0.00',
            'ValTot' => number_format($factura->totalAPI($empresa)->total, 2, '.', ''),
            'NitFE'  => Empresa::find($factura->empresa)->nit,
            'NumAdq' => $factura->cliente()->nit,
            'ClvTec' => 'fc8eac422eba16e22ffd8c6f94b3f40a6e38162c',
            'TipoAmb'=> 1,
        ];

        $CUFE = $infoCufe['Numfac'].$infoCufe['FecFac'].$infoCufe['HorFac'].$infoCufe['ValFac'].$infoCufe['CodImp'].$infoCufe['ValImp'].$infoCufe['CodImp2'].$infoCufe['ValImp2'].$infoCufe['CodImp3'].$infoCufe['ValImp3'].$infoCufe['ValTot'].$infoCufe['NitFE'].$infoCufe['NumAdq'].$infoCufe['ClvTec'].$infoCufe['TipoAmb'];

        return hash('sha384',$CUFE);
    }


public function forma_pago()
{
    $terminos=TerminosPago::find($this->plazo);

    if ($terminos) {
        if ($terminos->dias > 0) {
            //cbc:PaymentMeans/ID  2 = Crédito
            $formapago = 2;
        }
        elseif($terminos->dias == 0)
        {
            //cbc:PaymentMeans/ID  1 = De Contado
            $formapago = 1;
        }
    }else
    {
        //-- Si no hay un plazo es por que se escogio manual, y obviamente se va a escoger un fecha futura entonces la forma de pago será a credito
        //cbc:PaymentMeans/ID  2 = Crédito
        $formapago = 2;
    }
    return $formapago;
}

    public function getTypeNameAttribute()
    {
        switch ($this->tipo){
            case 1:
                return 'Factura de venta';
            case 2:
                return 'Cotización';
            case 3:
             return 'Cuenta de cobro';
            default:
                return 'Cotización';
        }
    }
    
    public function getDateAttribute()
    {
        return [
            'primera' => Factura::where('empresa', Auth::user()->empresa)->whereNotNull('fecha')
                ->where('tipo','<>',2)
                ->where('estatus','<>',2)
                ->get()
                ->first()->fecha,
            'ultima' => Factura::where('empresa', Auth::user()->empresa)
                ->where('tipo','<>',2)
                ->where('estatus','<>',2)
                ->get()
                ->last()->fecha
        ];
    }
    
    public function deta(){
        return Factura::where('id', $this->factura)->first();
    }
    
    public function estadoCuenta(){
        
        $estadoCuenta = array('saldoMesAnterior' => 0, 'saldoMesActual' => 0, 'equipoCuota' => 0, 'servicioAdicional' => 0, 'total' => 0);
        
        $fechaActual = date("Y-m-d", strtotime(Carbon::now()));
        $saldoMesAnterior=0;
        $saldoMesActual=0;
        
        /*>>>>>>>>>>>>>>>>>>>>>>>>>> Saldo mes Anterior <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
        
        //traemos todas las facturas que el vencimiento haya pasado la fecha actual.
        $facturasVencidas = Factura::where('cliente',$this->cliente)->where('vencimiento','<',$fechaActual)->get();
          
        //sumamos todo lo que deba el cliente despues de la fecha de vencimiento
        foreach($facturasVencidas as $vencida){
            $saldoMesAnterior+=$vencida->porpagar();
        }
        
        $estadoCuenta['saldoMesAnterior'] = $saldoMesAnterior;
        
        /*>>>>>>>>>>>>>>>>>>>>>>>>>> Saldo mes Actual <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
        
        $facturasActuales = Factura::where('cliente',$this->cliente)->where('vencimiento','>',$fechaActual)->get();
        
        //sumamos todo lo que deba el cliente despues de la fecha de vencimiento
        foreach($facturasActuales as $actual){
            $saldoMesActual+=$actual->porpagar();
        }
        
        $estadoCuenta['saldoMesActual'] = $saldoMesActual;
        
        return (object) $estadoCuenta;
    }

    /**
     * Retorna si un cliente puede crear factura electrónica o no.
     *
     * @var array
     */
    public static function booleanFacturaElectronica($clienteId){
    //Validamos que la persona tenga un contrato de lo contrario no podremos crear una factura electrónica.
    $contratoPersona = Contrato::where('client_id',$clienteId)->first();

    if($contratoPersona){

        /* 
        Vamos a evaluar la fecha de corte tomando el ultimo contrato y contando los dias de corte que tenga el contrato.
        para saber si se puede generar un factura electrónica de ese cliente.
        */
        $diasCorte = GrupoCorte::join('contracts as c','c.grupo_corte','=','grupos_corte.id')
        ->where('c.client_id',$clienteId)
        ->select('grupos_corte.*')
        ->first();

        //Obtenemos la ultima factura generada para ese cliente (si es que tiene).
        $fechaActual = Carbon::now()->format('Y-m');
        $lastFacturaFecha = false;
        $fechaPermitida = true;
        if(Factura::where('cliente', $clienteId)->orderby('id','desc')->first()){
            
            $lastFacturaFecha = Factura::where('cliente', $clienteId)->orderby('id','desc')->first()->fecha;
            $lastFacturaFecha = Carbon::parse($lastFacturaFecha)->format('Y-m');
        
            if($lastFacturaFecha != $fechaActual){
                return response()->json(true);
            }else{
                return response()->json(false);
            }
        
        //si no ingresa a este apartado ya que no cuenta con ninguna factura creada pero si contrato.
        }else{
            return response()->json(true);
        }
        
        }
        else{
            return response()->json(false);
        }
    }

    public function redondeo($total)
    {
        $decimal = explode(".", $total);
        if (isset($decimal[1]) && $decimal[1] > 50) {
            $total = round($total);
        }
        return $total;
    }

    public function isItemSinIva()
    {

        $items = ItemsFactura::where('factura', $this->id)->get();

        foreach ($items as $item) {
            if ($item->id_impuesto == 0) {
                return true;
            }
        }


        return false;
    }

    public function itemImpuestoSingular()
    {
        $text = '';
        $ivas =  array();

        $impuesto = Impuesto::where('id', $this->id_impuesto)->first();
        if ($impuesto) {
            array_push($ivas, ["imp0" => $impuesto->porcentaje]);
        }
        
        if ($impuesto) {
            return $ivas;
        }
        return '';
    }

    public function periodoCobrado(){
        
        $grupo = Contrato::join('grupos_corte as gc', 'gc.id', '=', 'contracts.grupo_corte')->
        where('client_id',$this->cliente)
        ->select('gc.*')->first();
        
        $mesInicioCorte = $mesFinCorte = Carbon::parse($this->fecha)->format('m');
        $yearInicioCorte = $yearFinCorte = Carbon::parse($this->fecha)->format('Y');
        
        //Calculos para los inicios de corte
        if($mesInicioCorte == 1){
            $mesInicioCorte = 12;
            $yearInicioCorte = $yearInicioCorte - 1;
        }else{
            $mesInicioCorte = $mesInicioCorte - 1;
        }
        
        //Calculos para los finales de corte
        if($mesFinCorte == 12){
            $mesFinCorte = 1;
            $yearFinCorte = $yearFinCorte + 1;
        }else{
            $mesFinCorte = $mesFinCorte + 1;
        }
        
        /* 
            validamos que si la fecha de corte es mas grande que el ultimo dia del mes anterior
            (caso con los meses que tiene 28, 29 días y la fec. corte es el 30)
            entonces la fecha de corte pasa a ser el ultimo día del mes.
        */
        $diaValidar = "1-".$mesInicioCorte."-".$yearInicioCorte;
        $diaValidar = Carbon::parse($diaValidar)->endOfMonth()->format('d');
        
        $diaFinValidar = "1-".$mesFinCorte."-".$yearFinCorte;
        $diaFinValidar = Carbon::parse($diaFinValidar)->endOfMonth()->format('d');
        
        $diaInicioCorte = $diaFinCorte = $grupo->fecha_corte;
        
        if($grupo->fecha_corte > $diaValidar){
            $diaInicioCorte = $diaValidar;
        }
        
        if($grupo->fecha_corte > $diaFinValidar){
             $diaFinCorte = $diaFinValidar;
        }
        
        //construimos el inicio del corte tomando la fecha de la factura (mes y año) y el grupo de corte (el dia)
        $fechaInicio = $inicioCorte = $diaInicioCorte . "-" . $mesInicioCorte . "-" . $yearInicioCorte;
        $inicioCorte = Carbon::parse($inicioCorte)->addDay()->toFormattedDateString();
        
        //obtenemos el mes de la factura actual
        $mesFactura = Carbon::parse($this->fecha)->format('m-Y');
        
        $fechaFin = $finCorte = $diaFinCorte . "-" . $mesFactura;
        $finCorte = Carbon::parse($finCorte)->toFormattedDateString();
        
        
        //Construimos una fecha con el grupo de corte y mes y año de la factura, tambien formateamos la fecha de la factura completamente
        $fechaFactura = Carbon::parse($this->fecha);
        $inicio = $grupo->fecha_corte . "-" . $mesFactura;
        $inicio = Carbon::parse($inicio);
        
        $diasCobrados = 0;
        $mensaje = "Periodo cobrado del " . $inicioCorte . " Al " . $finCorte;
        
        //Primero analizamos si el contrato es la primer factura que vamos a generar
        if($this->contrato_id != null){
            
            $factura = Factura::where('empresa',$this->empresa)->where('contrato_id',$this->contrato_id)->orderBy('id','ASC')->first();
            
            //De esta manera nos aseguramos que se esté hablando de la misma y primer factura y entonces cobraremos los primeros dias de uso
            if($factura->id == $this->id){
                $diasCobrados = $fechaFactura->diffInDays($inicio);
                $mensaje.= " total días cobrados: " . $diasCobrados; 
            }else{
                $fechaInicio = Carbon::parse($fechaInicio);
                $fechaFin    = Carbon::parse($fechaFin);
                $diasCobrados = $fechaInicio->diffInDays($fechaFin);
                $mensaje.= " total días cobrados: " . $diasCobrados; 
            }
        }
        
        return $mensaje;
    }

}
