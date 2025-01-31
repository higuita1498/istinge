<?php

namespace App\Http\Controllers;

use App\User;
use App\Categoria;
use App\Banco;
use App\Contacto;
use App\GrupoCorte;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosCategoria;
use App\Model\Gastos\ItemsFacturaProv;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\FacturaRetencion;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosCategoria;
use App\Model\Ingresos\IngresosFactura;
use App\Model\Ingresos\IngresosRemision;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\ItemsRemision;
use App\Model\Ingresos\Remision;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\ProductosBodega;
use App\Movimiento;
use App\Radicado;
use App\Vendedor;
use App\Model\Ingresos\NotaCredito;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request; use Carbon\Carbon;
use App\NumeracionFactura;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use App\Mikrotik;
use App\PucMovimiento;
use App\Servidor;
use App\FormaPago;
use App\Contrato;
use App\Empresa;
use Illuminate\Support\Facades\Auth as Auth;
use Illuminate\Support\Facades\DB as DB;

include_once(app_path() .'/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Shared_ZipArchive;

class ReportesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'reportes', 'title' => 'Reportes', 'icon' =>'fas fa-chart-line']);
    }


    public function index()
    {
        $this->getAllPermissions(Auth::user()->id);
        return view('reportes.index');
    }

    public function ventas(Request $request){

        $this->getAllPermissions(Auth::user()->id);
        DB::enableQueryLog();
        if ($request->nro == 'remisiones'){
            return $this->remisiones($request);
        }else{

            $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
            $cajas = Banco::where('estatus',1)->get();
            $cajasUsuario = auth()->user()->cuentas();

            if(Auth::user()->rol > 1 && auth()->user()->rol == 8){
                $cajas = Banco::whereIn('id', $cajasUsuario)->get();
            }

            view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Facturas Pagadas', 'icon' =>'fas fa-chart-line']);
            $campos=array( '','nombrecliente', 'factura.fecha', 'factura.vencimiento', 'nro', 'nro', 'nro', 'nro');
            if (!$request->orderby) {
                $request->orderby=1; $request->order=1;
            }
            $orderby=$campos[$request->orderby];
            $order=$request->order==1?'DESC':'ASC';

            $facturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
                ->leftjoin('contracts', 'contracts.id', '=', 'factura.contrato_id')
                ->join('items_factura as if', 'factura.id', '=', 'if.factura')
                ->join('ingresos_factura as ig', 'factura.id', '=', 'ig.factura')
                ->join('ingresos as i', 'ig.ingreso', '=', 'i.id')
                ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                    'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa',
                    'i.fecha as pagada','i.cuenta','ig.puc_banco')
                ->whereIn('factura.tipo', [1,2])
                ->where('factura.empresa',Auth::user()->empresa)
                ->where('factura.estatus',0)
                ->groupBy('factura.id');
            $example = $facturas->get()->last();

            $dates = $this->setDateRequest($request);

            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $facturas=$facturas->where('i.fecha','>=', $dates['inicio'])->where('i.fecha','<=', $dates['fin']);
            }
            if($request->caja){
                $facturas=$facturas->where('i.cuenta',$request->caja);
            }else{
                if(Auth::user()->rol > 1 && auth()->user()->rol == 8){
                    $facturas=$facturas->whereIn('i.cuenta', $cajasUsuario);
                }
            }
            if($request->grupo){
                $facturas=$facturas->where('contracts.grupo_corte', $request->grupo);
            }

            if($request->formapago){
                $facturas=$facturas->where('ig.puc_banco', $request->formapago);
            }

            $ides=array();
            $facturas=$facturas->OrderBy($orderby, $order)->get();

            foreach ($facturas as $factura) {
                $ides[]=$factura->id;
            }

            foreach ($facturas as $invoice) {
                $invoice->subtotal = $invoice->total()->subsub;
                $invoice->iva = $invoice->impuestos_totales();
                $invoice->retenido = $factura->retenido(true);
                $invoice->total = $invoice->total()->total - $invoice->devoluciones();
            }
            if($request->orderby == 4 || $request->orderby == 5  || $request->orderby == 6 || $request->orderby == 7 ){
                switch ($request->orderby){
                    case 4:
                        $facturas = $request->order  ? $facturas->sortBy('subtotal') : $facturas = $facturas->sortByDesc('subtotal');
                        break;
                    case 5:
                        $facturas = $request->order ? $facturas->sortBy('iva') : $facturas = $facturas->sortByDesc('iva');
                        break;
                    case 6:
                        $facturas = $request->order ? $facturas->sortBy('retenido') : $facturas = $facturas->sortByDesc('retenido');
                        break;
                    case 7:
                        $facturas = $request->order ? $facturas->sortBy('total') : $facturas = $facturas->sortByDesc('total');
                        break;
                }
            }
            $facturas = $this->paginate($facturas, 15, $request->page, $request);


            $subtotal=$total=0;
            if ($ides) {
                $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
                $subtotal=$this->precision($result->total-$result->descuento);
                $total=$this->precision((float)$subtotal+$result->impuesto);
            }


            $gruposCorte = GrupoCorte::where('empresa', Auth::user()->empresa)->get();

            //tomamos las formas de pago cuando no es un recibo de caja por anticipo
            $formasPago = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();

            return view('reportes.ventas.index')->with(compact('facturas', 'numeraciones', 'subtotal', 'total', 'request', 'example','cajas', 'gruposCorte','formasPago'));

        }


    }

    public function ventasExport($actual, $minus){
        $facturas = Factura::where('empresa',Auth::user()->empresa)->where('tipo','!=',2);
        $dates = $this->setDate($actual, $minus);

        $facturas=$facturas->where('fecha','>=', $dates['inicio'])->where('fecha','<=', $dates['fin']);
        $ides=array();
        $factures=$facturas->get();

        foreach ($factures as $factura) {
            $ides[]=$factura->id;
        }

        $subtotal=$total=0;
        if ($ides) {
            $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
            $subtotal=$this->precision($result->total-$result->descuento);
            $total=$this->precision((float)$subtotal+$result->impuesto);
        }
        return $total;

    }

    private function remisiones(&$request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
        $dates = $this->setDateRequest($request);
        view()->share(['seccion' => 'reportes', 'title' => '', 'icon' =>'']);

        //Código base tomado de RemisionesController@index
        $campos=array('', 'remisiones.id', 'nombrecliente', 'remisiones.fecha', 'remisiones.vencimiento', 'total', 'pagado', 'porpagar', 'remisiones.estatus');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $facturas=Remision::join('contactos as c', 'remisiones.cliente', '=', 'c.id')
            ->join('items_remision as if', 'remisiones.id', '=', 'if.remision')
            ->select('remisiones.id', 'remisiones.nro', DB::raw('c.nombre as nombrecliente'), 'remisiones.cliente',
                'remisiones.fecha', 'remisiones.vencimiento', 'remisiones.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) as pagado'),
                DB::raw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) )    as porpagar'))
            ->where('remisiones.empresa',Auth::user()->empresa)
            ->where('fecha','>=', $dates['inicio'])
            ->where('fecha','<=', $dates['fin'])
            ->whereIn('estatus', [0, 1]);
        $appends=array(
            'fechas'    =>$request->fechas,
            'nro'       => $request->nro,
            'fecha'=>$request->fecha,
            'hasta'=>$request->hasta,
            'orderby'   => $request->orderby,
            'order'     => $request->order);
        $facturas=$facturas->groupBy('if.remision');
        $example = $facturas->get()->last();
        $facturas=$facturas->OrderBy($orderby, $order)->paginate(50)->appends($appends);

        $totales = $this->totalRemisiones($dates);

        return view('reportes.ventas.indexRemisiones')->with(compact('facturas', 'numeraciones', 'request', 'example'))
            ->with('total', $totales['total'])
            ->with('subtotal', $totales['subtotal']);

    }

    private function totalRemisiones($dates)
    {
        $facturas=Remision::join('contactos as c', 'remisiones.cliente', '=', 'c.id')
            ->join('items_remision as if', 'remisiones.id', '=', 'if.remision')
            ->select('remisiones.id', 'remisiones.nro', DB::raw('c.nombre as nombrecliente'), 'remisiones.cliente',
                'remisiones.fecha', 'remisiones.vencimiento', 'remisiones.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) as pagado'),
                DB::raw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) )    as porpagar'))
            ->where('remisiones.empresa',Auth::user()->empresa)
            ->where('fecha','>=', $dates['inicio'])
            ->where('fecha','<=', $dates['fin'])
            ->whereIn('estatus', [0, 1])
            ->groupBy('if.remision')
            ->get();
        $totales = array(
            'total' => 0,
            'subtotal' => 0,
        );

        foreach ($facturas as $factura) {
            $totales['total']+= $factura->total()->total;
            $totales['subtotal']+= $factura->total()->subsub;
        }

        return $totales;
    }

    public function ventasItem(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);

        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }
        else{

            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $inicio = date('Y-m-d', strtotime($request->fecha));
                $fin    = date('Y-m-d', strtotime($request->hasta));
            }else{
                $inicio = Carbon::now()->subYear('4')->format('Y-m-d');
                $fin    = Carbon::now()->format('Y-m-d');
            }


        }

        $user = Auth::user()->empresa;
        //Se cuenta cuantas veces se repiten las facturas con un mismo producto
        $sqlRepeticiones =
            "SELECT id, producto FROM items_factura WHERE items_factura.factura IN
	            (
		            SELECT id FROM factura
			            WHERE factura.fecha >= '$inicio'
				            AND factura.fecha <= '$fin'
				            AND factura.empresa = '$user'
				            AND factura.tipo != 2
	            )";

        $repeticones = DB::select($sqlRepeticiones);
        $totales = array();
        foreach ($repeticones as $repeticone){

            $item = ItemsFactura::where('id', $repeticone->id)->first();

            if(!isset($totales[$repeticone->producto])){
                $totales[$repeticone->producto]['rep'] = 1;
                $totales[$repeticone->producto]['subtotal'] = $item->total();
                $totales[$repeticone->producto]['total'] = $item->totalImp();
                echo $item->porcentaje;

            }else{
                $totales[$repeticone->producto]['rep']+= 1;
                $totales[$repeticone->producto]['subtotal'] += $item->total();
                $totales[$repeticone->producto]['total'] += $item->totalImp();
            }
        }


        //Subconsulta para obtener todos los productos según su item factura
        $productos = DB::table('inventario')
            ->select('id', 'producto', 'ref', 'precio', DB::raw('precio+(precio*(impuesto/100)) as total'))
            ->whereIn('id', function ($query) use ($inicio, $fin, $user){
                $query->select('producto')
                    ->from(with(new ItemsFactura)->getTable())
                    ->whereIn('factura', function ($sql) use ($inicio, $fin, $user){
                        $sql->select('id')
                            ->from(with(new Factura)->getTable())
                            ->where('fecha', ">=", $inicio)
                            ->where('fecha', "<=", $fin)
                            ->where('empresa', $user)
                            ->where('tipo','!=', 2);
                    });
            })->paginate(50)
            ->appends(['fechas'=>$request->fechas, 'nro'=>$request->nro, 'fecha'=>$request->fecha,
                'hasta'=>$request->hasta]);
        //Subconsulta para determinar todos los precios de los productos
        $productosTotal = DB::table('inventario')
            ->select('precio', DB::raw('precio+(precio*(impuesto/100)) as total'))
            ->whereIn('id', function ($query) use ($inicio, $fin, $user){
                $query->select('producto')
                    ->from(with(new ItemsFactura)->getTable())
                    ->whereIn('factura', function ($sql) use ($inicio, $fin, $user){
                        $sql->select('id')
                            ->from(with(new Factura)->getTable())
                            ->where('fecha', ">=", $inicio)
                            ->where('fecha', "<=", $fin)
                            ->where('empresa', $user)
                            ->where('tipo', '<>', 2);
                    });
            })->get();
        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();
        //Se agregan las veces que se repiten los productos listados en el array actual
        $i = 0;
        $total = 0;
        $subtotal = 0;
        foreach ( $productos as $producto ){
            $producto->rep = $totales[$producto->id]['rep'];
            $producto->precio = $totales[$producto->id]['subtotal'];
            $producto->total = $totales[$producto->id]['total'];
        }
        foreach ($productosTotal as $productoTotal){
            $total      += $productoTotal->total;
            $subtotal   += $productoTotal->precio;
        }

        return view('reportes.ventasItem.index')->with(compact('productos', 'subtotal', 'total', 'request', 'example'));

    }

    public function comprasProveedor(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        //Pendiente, sustituir esto
        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }
        else{
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $inicio = date('Y-m-d', strtotime($request->fecha));
                $fin    = date('Y-m-d', strtotime($request->hasta));
            }else{
                $inicio = Carbon::now()->subYear('10')->format('Y-m-d');
                $fin    = Carbon::now()->format('Y-m-d');
            }
        }

        $user = Auth::user()->empresa;

        $sqlNroFacturaProveedores = "SELECT factura_proveedores.id as factura, contactos.id, contactos.nombre FROM factura_proveedores
	                                INNER JOIN  contactos ON factura_proveedores.proveedor = contactos.id
                                    WHERE factura_proveedores.fecha_factura >= '$inicio'
                                    AND factura_proveedores.fecha_factura <= '$fin'
                                    AND factura_proveedores.empresa = '$user'
                                    AND factura_proveedores.tipo = 1";

        // dd($sqlNroFacturaProveedores);


        $datoFacturas = DB::table('items_factura_proveedor')
            ->select('id', DB::raw('SUM(precio) as precio'), 'factura', DB::raw('COUNT(factura)'),
                DB::raw('SUM(precio)+(SUM(precio)*(impuesto/100)) as total'))
            ->whereIn('factura', function ($query) use ($inicio, $fin, $user){
                $query->select('id')
                    ->from(with(new FacturaProveedores)->getTable())
                    ->where('fecha_factura', ">=", $inicio)
                    ->where('fecha_factura', "<=", $fin)
                    ->where('empresa', $user)
                    ->where('tipo', 1)
                    ->whereIn('proveedor', function ($sql) use ($inicio, $fin, $user){
                        $sql->select('id')
                            ->from(with(new Contacto)->getTable())
                            ->whereIn('id', function ($sqlQuery) use ($inicio, $fin, $user){
                                $sqlQuery->select('proveedor')
                                    ->from(with(new Contacto)->getTable())
                                    ->where('fecha_factura', ">=", $inicio)
                                    ->where('fecha_factura', "<=", $fin)
                                    ->where('empresa', $user)
                                    ->where('tipo','=', 1);
                            });
                    });
            })
            ->groupby('factura')
            ->paginate(1000000)
            ->appends(['fechas'=>$request->fechas, 'nro'=>$request->nro, 'fecha'=>$request->fecha,
                'hasta'=>$request->hasta]);

        $nroFacturas = DB::select($sqlNroFacturaProveedores);

        $i = 0;
        $proveedores= array();
        $subtotal = 0;
        $total= 0;
        foreach ($datoFacturas as $datoFactura){

            if(!isset($clientes[$nroFacturas[$i]->id])){
                $proveedores[$nroFacturas[$i]->id]['nombre'] = $nroFacturas[$i]->nombre;
                $proveedores[$nroFacturas[$i]->id]['id'] = $nroFacturas[$i]->id;
                $proveedores[$nroFacturas[$i]->id]['subtotal'] = $datoFactura->precio;
                $proveedores[$nroFacturas[$i]->id]['total'] = $datoFactura->total;
                $proveedores[$nroFacturas[$i]->id]['rep'] = 1;
                $subtotal += $datoFactura->precio;
                $total += $datoFactura->total;

            }else{

                $proveedores[$nroFacturas[$i]->id]['subtotal']+= $datoFactura->precio;
                $proveedores[$nroFacturas[$i]->id]['total'] += $datoFactura->total;
                $proveedores[$nroFacturas[$i]->id]['rep']+=1;
                $subtotal+= $datoFactura->precio;
                $total+= $datoFactura->total;

            }

            $i++;
        }

        $example = FacturaProveedores::where('empresa', Auth::user()->empresa)->get()->last();
        view()->share(['title' => 'Compras por Proveedor', 'subseccion' => 'reportes']);

        $clientes = $this->orderMultiDimensionalArray($proveedores, 'rep', true);

        return view ('reportes.comprasProveedores.index', compact('proveedores', 'request', 'total', 'subtotal', 'example'));


    }

    public function ventasCliente(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        //Pendiente, sustituir esto
        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }
        else{
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $inicio = date('Y-m-d', strtotime($request->fecha));
                $fin    = date('Y-m-d', strtotime($request->hasta));
            }else{
                $inicio = Carbon::now()->subYear('10')->format('Y-m-d');
                $fin    = Carbon::now()->format('Y-m-d');
            }
        }

        $user = Auth::user()->empresa;

        $sqlNroFacturaCliente = "SELECT factura.id as factura, contactos.id, contactos.nombre FROM factura
	                                INNER JOIN  contactos ON factura.cliente = contactos.id
                                    WHERE factura.fecha >= '$inicio'
                                    AND factura.fecha <= '$fin'
                                    AND factura.empresa = '$user'
                                    AND factura.tipo != 2";


        $datoFacturas = DB::table('items_factura')
            ->select('id', DB::raw('SUM(precio) as precio'), 'factura', DB::raw('COUNT(factura)'),
                DB::raw('SUM(precio)+(SUM(precio)*(impuesto/100)) as total'))
            ->whereIn('factura', function ($query) use ($inicio, $fin, $user){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('fecha', ">=", $inicio)
                    ->where('fecha', "<=", $fin)
                    ->where('empresa', $user)
                    ->where('tipo', 1)
                    ->whereIn('cliente', function ($sql) use ($inicio, $fin, $user){
                        $sql->select('id')
                            ->from(with(new Contacto)->getTable())
                            ->whereIn('id', function ($sqlQuery) use ($inicio, $fin, $user){
                                $sqlQuery->select('cliente')
                                    ->from(with(new Contacto)->getTable())
                                    ->where('fecha', ">=", $inicio)
                                    ->where('fecha', "<=", $fin)
                                    ->where('empresa', $user)
                                    ->where('tipo','!=', 2);
                            });
                    });
            })
            ->groupby('factura')->get();


        $nroFacturas = DB::select($sqlNroFacturaCliente);

        $i = 0;
        $clientes= array();
        $subtotal = 0;
        $total= 0;
        foreach ($datoFacturas as $datoFactura){

            if(!isset($clientes[$nroFacturas[$i]->id])){
                $clientes[$nroFacturas[$i]->id]['nombre'] = $nroFacturas[$i]->nombre;
                $clientes[$nroFacturas[$i]->id]['id'] = $nroFacturas[$i]->id;
                $clientes[$nroFacturas[$i]->id]['subtotal'] = $datoFactura->precio;
                $clientes[$nroFacturas[$i]->id]['total'] = $datoFactura->total;
                $clientes[$nroFacturas[$i]->id]['rep'] = 1;
                $subtotal += $datoFactura->precio;
                $total += $datoFactura->total;

            }else{

                $clientes[$nroFacturas[$i]->id]['subtotal']+= $datoFactura->precio;
                $clientes[$nroFacturas[$i]->id]['total'] += $datoFactura->total;
                $clientes[$nroFacturas[$i]->id]['rep']+=1;
                $subtotal+= $datoFactura->precio;
                $total+= $datoFactura->total;

            }

            $i++;
        }

        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();

        view()->share(['title' => 'Ventas por Cliente', 'subseccion' => 'reportes']);


        $clientes = $this->orderMultiDimensionalArray($clientes, 'rep', true);

        return view ('reportes.ventasCliente.index', compact('clientes', 'request', 'total', 'subtotal', 'example'));


    }

    public function remisionesCliente(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        //Pendiente, sustituir esto
        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }
        else{
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $inicio = date('Y-m-d', strtotime($request->fecha));
                $fin    = date('Y-m-d', strtotime($request->hasta));
            }else{
                $inicio = Carbon::now()->subYear('10')->format('Y-m-d');
                $fin    = Carbon::now()->format('Y-m-d');
            }

        }

        $user = Auth::user()->empresa;

        $sqlNroFacturaCliente = "SELECT remisiones.id as remision, contactos.id, contactos.nombre FROM remisiones
	                                INNER JOIN  contactos ON remisiones.cliente = contactos.id
                                    WHERE remisiones.fecha >= '$inicio'
                                    AND remisiones.fecha <= '$fin'
                                    AND remisiones.empresa = '$user'
                                    AND remisiones.estatus IN (0, 1)
                                    AND remisiones.documento = 1";


        $datoRemisiones = DB::table('items_remision')
            ->select('id', DB::raw('SUM(precio) as precio'), 'remision', DB::raw('COUNT(remision)'),
                DB::raw('SUM(precio)+(SUM(precio)*(impuesto/100)) as total'))
            ->whereIn('remision', function ($query) use ($inicio, $fin, $user){
                $query->select('id')
                    ->from(with(new Remision)->getTable())
                    ->where('fecha', ">=", $inicio)
                    ->where('fecha', "<=", $fin)
                    ->where('empresa', $user)
                    ->whereIn('estatus', [0, 1])
                    ->where('documento', 1)
                    ->whereIn('cliente', function ($sql) use ($inicio, $fin, $user){
                        $sql->select('id')
                            ->from(with(new Contacto)->getTable())
                            ->whereIn('id', function ($sqlQuery) use ($inicio, $fin, $user){
                                $sqlQuery->select('cliente')
                                    ->from(with(new Contacto)->getTable())
                                    ->where('fecha', ">=", $inicio)
                                    ->where('fecha', "<=", $fin)
                                    ->where('empresa', $user)
                                    ->where('documento','=', 1);
                            });
                    });
            })
            ->groupby('remision')
            ->get();
        $example = Remision::where('empresa', Auth::user()->empresa)->get()->last();
        $nroRemisiones = DB::select($sqlNroFacturaCliente);


        $i = 0;
        $clientes= array();
        $subtotal = 0;
        $total= 0;
        foreach ($datoRemisiones as $datoRemision){

            if(!isset($clientes[$nroRemisiones[$i]->id])){
                $clientes[$nroRemisiones[$i]->id]['nombre'] = $nroRemisiones[$i]->nombre;
                $clientes[$nroRemisiones[$i]->id]['id'] = $nroRemisiones[$i]->id;
                $clientes[$nroRemisiones[$i]->id]['subtotal'] = $datoRemision->precio;
                $clientes[$nroRemisiones[$i]->id]['total'] = $datoRemision->total;
                $clientes[$nroRemisiones[$i]->id]['rep'] = 1;
                $subtotal += $datoRemision->precio;
                $total += $datoRemision->total;

            }else{

                $clientes[$nroRemisiones[$i]->id]['subtotal']+= $datoRemision->precio;
                $clientes[$nroRemisiones[$i]->id]['total'] += $datoRemision->total;
                $clientes[$nroRemisiones[$i]->id]['rep']+=1;
                $subtotal+= $datoRemision->precio;
                $total+= $datoRemision->total;

            }

            $i++;
        }



        view()->share(['title' => 'Remisiones por Cliente', 'subseccion' => 'reportes']);

        $clientes = $this->orderMultiDimensionalArray($clientes, 'rep', true);

        return view ('reportes.remisionesCliente.index', compact('clientes', 'request', 'total', 'subtotal', 'example'));


    }


    public function cuentasCobrar(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);

        $dates = $this->setDateRequest($request);
        if($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);

        if ($request->nro == 'remisiones'){
            return $this->remisionesCobrar($request);
        }


        //Código base tomado de FacturasController@index

        $busqueda=false;
        $campos=array( '','nombrecliente', 'factura.fecha', 'factura.vencimiento', 'total', 'pagado', 'porpagar');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $facturas=Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'),
                DB::raw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) - (Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id) )    as porpagar'))
            ->where('factura.empresa',Auth::user()->empresa)
            ->where('factura.estatus', 1);

        //Filtrado por fecha
        if($request->fecha){
            $appends['fecha']=$request->fecha;
            $facturas=$facturas->where('factura.fecha', ">=", $dates['inicio']);
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
            $facturas=$facturas->where('factura.fecha', "<=", $dates['fin']);
        }

        $facturas=$facturas->groupBy('if.factura');

        $facturasTotal = $facturas->get();
        $facturas=$facturas->OrderBy($orderby, $order)->get();
        foreach ($facturas as $factura) {
            $factura->total = $factura->total()->total;
            $factura->pagado = $factura->pagado();
            $factura->porpagar = $factura->porpagar();
        }
        if($request->orderby == 4 || $request->orderby == 5  || $request->orderby == 6 ){
            switch ($request->orderby){
                case 4:
                    $facturas = $request->order  ? $facturas->sortBy('total') : $facturas = $facturas->sortByDesc('total');
                    break;
                case 5:
                    $facturas = $request->order ? $facturas->sortBy('pagado') : $facturas = $facturas->sortByDesc('pagado');
                    break;
                case 6:
                    $facturas = $request->order ? $facturas->sortBy('porpagar') : $facturas = $facturas->sortByDesc('porpagar');
                    break;
            }
        }


        view()->share(['title' => 'Cuentas por Cobrar', 'subseccion' => 'reportes']);


        //Se determina el gran total
        $totalPagar = 0;
        foreach ($facturasTotal as $factura){
            $totalPagar += $factura->porPagar();
        }
        $facturas = $this->paginate($facturas, 30, $request->page, $request);



        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();
        return view('reportes.cuentasCobrar.index')->with(compact('facturas', 'request', 'busqueda', 'totalPagar', 'example'));

    }

    public function remisionesCobrar(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);
        if($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);
        $remisiones = Remision::where('empresa', Auth::user()->empresa)
            ->where('fecha', '<=', $dates['fin'])
            ->where('fecha', '>=', $dates['inicio'])
            ->whereIn('estatus', [0, 1])
            ->get();
        $totalPagar = 0;
        $remisionesCobrar = array();
        foreach ($remisiones as $remision){
            if($remision->porpagar() > 0 ){
                $remision->clienteNombre = $remision->cliente()->nombre;
                $remision->clienteId = $remision->cliente()->id;
                $remisionesCobrar[] = $remision;
                $totalPagar += $remision->porPagar();
            }
        }

        if(count($remisiones) > 0){
            $remisionesCobrar = $this->orderMultiDimensionalArray($remisionesCobrar, 'nro', true);
        }
        $example = Remision::where('empresa', Auth::user()->empresa)->get()->last();
        view()->share(['title' => 'Cuentas por Cobrar / Remisones', 'subseccion' => 'reportes']);

        return view('reportes.cuentasCobrar.indexRemisiones')
            ->with('facturas', $remisionesCobrar)
            ->with('request', $request)
            //->with('example', $example)
            ->with('totalPagar', $totalPagar);
    }

    public function cuentasPagar(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);

        $dates = $this->setDateRequest($request);
        if($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);
        //Código base tomado de FacturaspController@index

        $campos=array('', 'factura_proveedores.nro', 'factura_proveedores.codigo', 'nombrecliente', 'factura_proveedores.fecha_factura','factura_proveedores.vencimiento_factura',  'total',  'total',  'total');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        $facturas=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro', DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura', 'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar '))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)->where('factura_proveedores.tipo',1)
            ->where('factura_proveedores.fecha_factura', ">=", $dates['inicio'])
            ->where('factura_proveedores.fecha_factura', "<=", $dates['fin'])
            ->groupBy('if.factura')->get();

        //usado para poder sacar el gran total a mostrar dentro reportes -> cuentas pagar

        $totales=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro', DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura', 'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar '))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)->where('factura_proveedores.tipo',1)
            ->where('factura_proveedores.fecha_factura', ">=", $dates['inicio'])
            ->where('factura_proveedores.fecha_factura', "<=", $dates['fin'])
            ->groupBy('if.factura')->OrderBy($orderby, $order)->get();

        //Se determina el gran total
        $totalPagar = 0;
        foreach ($totales as $total){
            $totalPagar += $total->porPagar();
        }
        $example = FacturaProveedores::where('empresa', Auth::user()->empresa)->get()->last();

        view()->share(['title' => 'Cuentas por Pagar', 'subseccion' => 'reportes']);

        return view('reportes.cuentasPagar.index')->with(compact('facturas', 'request', 'totalPagar', 'example'));

    }


    public function compras(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);

        $dates = $this->setDateRequest($request);
        if($request->fecha == 8)
            $dates = $this->setDateRequest($request, true);

        //Código base tomado de FacturaspController@index

        $campos=array('', 'factura_proveedores.nro', 'factura_proveedores.codigo', 'nombrecliente', 'factura_proveedores.fecha_factura','factura_proveedores.vencimiento_factura',  'total',  'total',  'total');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        $facturas=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro', DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura', 'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar'))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)
            ->where('factura_proveedores.fecha_factura', ">=", $dates['inicio'])
            ->where('factura_proveedores.fecha_factura', "<=", $dates['fin'])
            ->where('factura_proveedores.tipo',1)->groupBy('if.factura')
            ->where('factura_proveedores.estatus','!=','3')
            ->OrderBy($orderby, $order)->paginate(10000000)
            ->appends(['orderby'=>$request->orderby, 'order'=>$request->order]);

        //Consulta usada para obtener el gran total
        $totalFacturas = FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro', DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura', 'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar'))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)
            ->where('factura_proveedores.fecha_factura', ">=", $dates['inicio'])
            ->where('factura_proveedores.fecha_factura', "<=", $dates['fin'])
            ->where('factura_proveedores.estatus','!=','3')
            ->where('factura_proveedores.tipo',1)->groupBy('if.factura')->get();

        //Se determina el gran total
        $totalPagar = 0;
        foreach ($totalFacturas as $totalFactura){
            $totalPagar += $totalFactura->total()->total;
        }
        $example = FacturaProveedores::where('empresa', Auth::user()->empresa)->get()->last();
        view()->share(['title' => 'Compras', 'subseccion' => 'reportes']);
        return view('reportes.compras.index')->with(compact('facturas', 'request', 'totalPagar', 'example'));

    }

    public function comprasExport($actual, $minus){

        $dates = $this->setDate($actual, $minus);
        $campos=array('', 'factura_proveedores.nro', 'factura_proveedores.codigo', 'nombrecliente', 'factura_proveedores.fecha_factura','factura_proveedores.vencimiento_factura',  'total',  'total',  'total');
        $facturas=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro', DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura', 'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar'))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)
            ->where('factura_proveedores.fecha_factura', ">=", $dates['inicio'])
            ->where('factura_proveedores.fecha_factura', "<=", $dates['fin'])
            ->where('factura_proveedores.tipo',1)->groupBy('if.factura')->paginate(1000000);

        //Se determina el gran total
        $totalPagar = 0;
        foreach ($facturas as $factura){
            $totalPagar += $factura->total()->total;
        }

        return $totalPagar;
    }


    //Muestra y carga la lista de clientes en la respectiva vista
    public function estadoCliente()
    {
        $this->getAllPermissions(Auth::user()->id);
        //Se buscan los clientes pertenecientes a esa empresa
        $clients = Contacto::where('empresa', Auth::user()->empresa)
            ->whereIn('tipo_contacto', ['0', '2'])
            ->orderBy('id','DESC')
            ->get();

        view()->share(['title' => 'Estado de Cuenta Cliente', 'subseccion' => 'reportes']);


        return view ('reportes.estadoCliente.index')
            ->with('clientes', $clients);

    }

    public function estadoClienteShow(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);
        if($request->input('fechas') == 8)
            $dates = $this->setDateRequest($request, true);

        $client = Contacto::find($request->client);

        //Se obtienen las facturas de los clientes

        $facturas=Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->select('factura.id', 'factura.codigo', 'factura.tipo', 'factura.nro', DB::raw('c.nombre as nombrecliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'),
                DB::raw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) - (Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id) )    as porpagar'))
            ->where('factura.empresa',Auth::user()->empresa)
            ->where('factura.fecha', ">=", $dates['inicio'])
            ->where('factura.fecha', "<=", $dates['fin'])
            ->where('factura.cliente', $client->id)
            ->groupBy('if.factura');


        $appends = array();

        //Filtrado por fecha
        if($request->fecha){
            $appends['fecha']=$request->fecha;
            $facturas=$facturas->where('factura.fecha', ">=", date('Y-m-d', strtotime($request->fecha)));
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
            $facturas=$facturas->where('factura.fecha', "<=", date('Y-m-d', strtotime($request->hasta)));
        }

        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();

        $total = array(
            'total'     => 0,
            'pagado'    => 0,
            'porPagar'  => 0,

        );
        $facturasTotales = $facturas->get();
        foreach ($facturasTotales as $facturaTotal){
            $total['total']     += $facturaTotal->total()->total;
            $total['pagado']    += $facturaTotal->pagado();
            $total['porPagar']  += $facturaTotal->porPagar();
        }

        $facturas=$facturas->paginate(25)->appends($appends);


        view()->share(['title' => 'Estado de Cuenta Cliente', 'subseccion' => 'reportes']);

        return view('reportes.estadoCliente.show')
            ->with('request', $request)
            //->with('example', $example)
            ->with('clienteFacturas', $facturas)
            ->with('totales', $total);

    }

    public function ventasVendedor(Request $request)
    {

        $dates          = $this->setDateRequest($request);
        if($request->input('fechas') == 8)
            $dates      = $this->setDateRequest($request, true);

        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();

        $totales = array(
            'pagado'    => 0,
            'subtotal'  => 0,
            'total'     => 0,

            'pagadoR'   => 0,
            'subtotalR' => 0,
            'totalR'    => 0
        );




        foreach ($vendedores as $vendedore){
            $totales['pagado'] += $vendedore->pagosFecha($dates['inicio'], $dates['fin']);
            $totales['subtotal'] += $vendedore->montoTotal($dates['inicio'], $dates['fin'])['subtotal'];
            $totales['total'] += $vendedore->montoTotal($dates['inicio'], $dates['fin'])['total'];

            $totales['pagadoR'] += $vendedore->pagosFechaR($dates['inicio'], $dates['fin']);
            $totales['subtotalR'] += $vendedore->montoTotalR($dates['inicio'], $dates['fin'])['subtotalR'];
            $totales['totalR'] += $vendedore->montoTotalR($dates['inicio'], $dates['fin'])['totalR'];
        }

        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();


        view()->share(['title' => 'Ventas por Vendedor', 'subseccion' => 'reportes']);

        return view('reportes.ventasVendedor.index')
            ->with('vendedores', $vendedores)
            ->with('request', $request)
            //->with('example', $example)
            ->with('dates', $dates)
            ->with('totales', $totales);
    }

    public function rentabilidadItem(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);
        $items = Inventario::where('empresa', Auth::user()->empresa)
            ->where('tipo_producto', 1)
            ->get();
        $totales = array(
            'totalVendidos'         => 0,
            'costosTotales'         => 0,
            'rentabilidadtotal'     => 0,
        );

        foreach ($items as $item){
            $facturas = Factura::where('empresa', Auth::user()->empresa)
                ->whereNull('cot_nro');
            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $facturas=$facturas->where('fecha','>=', $dates['inicio'])->where('fecha','<=', $dates['fin']);
            }
            $facturas = $facturas->get();

            $item->totalVendido = 0;
            $item->vendidos = 0;

            if(!count($facturas) == 0){
                foreach ($facturas as $factura){
                    $itemFacturas = ItemsFactura::where('factura', $factura->id)
                        ->where('producto', $item->id)
                        ->get();

                    if(!count($itemFacturas) == 0){
                        foreach ($itemFacturas as $itemFactura){
                            $item->totalVendido+= $itemFactura->totalImp();
                            $item->vendidos++;
                        }
                        ;
                    }

                }
            }

            $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();

            if($item->totalVendido == 0){
                $item->costoTotal   = 0;
                $item->rentabilidad = 0;
                $item->porcentaje   = 0;

            }else{
                $item->costoTotal   = $item->vendidos * $item->costo_unidad;
                $item->rentabilidad = $item->totalVendido - $item->costoTotal;
                $item->porcentaje   = ($item->rentabilidad/$item->totalVendido)*100;

            }

            $totales['totalVendidos']        += $item->totalVendido;
            $totales['rentabilidadtotal']    += $item->rentabilidad;
            $totales['costosTotales']        += $item->costoTotal;


        }
        $items = $this->orderMultiDimensionalArray($items, 'totalVendido', true);

        view()->share(['title' => 'Rentabilidad de Items', 'subseccion' => 'reportes']);

        return view('reportes.rentabilidadItem.index')
            ->with('productos', $items)
            //->with('example', $example)
            ->with('request', $request)
            ->with('totales', $totales);

    }

    public function transacciones(Request $request)
    {
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Transacciones', 'icon' =>'fas fa-chart-line']);
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);
        if($request->fecha == 8)
            $dates = $this->setDateRequest($request, true);

        //Código base tomado de datatable_movimientos

        $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.empresa',Auth::user()->empresa);

        $movimientosTodos = Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.empresa',Auth::user()->empresa);
        $example = Movimiento::where('empresa', Auth::user()->empresa)->get()->last();

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->paginate(25)->appends($appends);
        $movimientosTodos = $movimientosTodos->get();

        $totales = array(
            'salida'    => 0,
            'entrada'   => 0
        );

        foreach ($movimientosTodos as $movimiento){
            $totales['salida']  += $movimiento->tipo==2?$movimiento->saldo:0;
            $totales['entrada']  += $movimiento->tipo==1?$movimiento->saldo:0;
        }

        return view('reportes.transacciones.index')
            ->with('movimientos', $movimientos)
            ->with('request', $request)
            //->with('example', $example)
            ->with('totales', $totales);

    }


    /**
     * Establece fecha dentro del request enviado
     * @param $request
     * @return array
     */
    private function setDateRequest(&$request, $all = false)
    {

        //Si no hay fecha establecida dentro de la url se establece desde el 1 al 30/31 del mes actual
        if (!$request->fecha) {
            $month = date('m');
            $year = date('Y');
            $day = date("d", mktime(0,0,0, $month+1, 0, $year));
            $fin= date('Y-m-d', mktime(0,0,0, $month, $day, $year));
            $request->hasta=date('d-m-Y', mktime(0,0,0, $month, $day, $year));
            $month = date('m');
            $year = date('Y');
            $inicio=  date('Y-m-d', mktime(0,0,0, $month, 1, $year));
            $request->fecha=date('d-m-Y', mktime(0,0,0, $month, 1, $year));
        }else{
            $inicio= date('Y-m-d', strtotime($request->fecha));
            $fin= date('Y-m-d', strtotime($request->hasta));
        }

        if($all){
            $inicio = Carbon::now()->subYear('10')->format('Y-m-d');
            $fin    = Carbon::now()->format('Y-m-d');
        }

        return array(
            'inicio'    => $inicio,
            'fin'       => $fin
        );
    }

    private function setDate($actual, $minus)
    {

        $actualDate = Carbon::now();
        if ($actual){
            return array(
                'inicio'    => $actualDate->firstOfMonth(),
                'fin'       => $actualDate->lastOfMonth()
            );
        }
        $actualDate->subMonth($minus);
        return array(
            'inicio'    => $actualDate->firstOfMonth(),
            'fin'       => $actualDate->lastOfMonth()
        );

    }

    public function valorActual(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $bodega = Bodega::where('empresa',Auth::user()->empresa)
            ->where('status', 1)
            ->where('id', $request->bodega)->first();
        $bodegas = Bodega::where('empresa',Auth::user()->empresa)
            ->where('status', 1)->get();
        if (!$bodega) {
            $bodega = Bodega::where('empresa',Auth::user()->empresa)
                ->where('status', 1)
                ->first();
        }
        if(!$request->bodega || $request->bodega == "all"){
            $request->bodega = 'all';
            $productos = Inventario::select('*')
                ->whereIn('id', function ($query){
                    $query->select('producto')
                        ->from(with(new ProductosBodega)->getTable())
                        ->where('empresa', Auth::user()->empresa);
                })->get();

        }else{
            $productos = Inventario::select('*')
                ->whereIn('id', function ($query) use ($bodega){
                    $query->select('producto')
                        ->from(with(new ProductosBodega)->getTable())
                        ->where('bodega', $bodega->id)
                        ->where('empresa', Auth::user()->empresa);
                })->get();

        }

        $total = 0;
        foreach ($productos as $producto){
            $producto->precio = $this->precision($producto->precio);
            $producto->costo_unidad=$this->precision($producto->costo_unidad);
            $producto->inventario = $request->bodega != "all" ? $producto->inventarioBodega($bodega->id) : $producto->inventario();
            $producto->total = $producto->costo_unidad * $producto->inventario;
            $total += $producto->costo_unidad * $producto->inventario;
        }


        view()->share(['title' => 'Valor Actual del Inventario', 'subseccion' => 'reportes']);

        return view('reportes.valorActual.index')
            ->with('request', $request)
            ->with('productos', $productos)
            ->with('total', $total)
            ->with('actualBodega', $bodega)
            ->with('bodegas', $bodegas);

    }

    public function ingresosEgresos(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);
        if($request->fecha == 8)
            $dates = $this->setDateRequest($request, true);

        $gastos     = $this->egresosTEST($dates);
        $ingresos   = $this->ingresosTEST($dates);
        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();

        view()->share(['title' => 'Ingresos y Egresos', 'subseccion' => 'reportes']);

        return view ('reportes.ingresoEgreso.index')
            ->with('request', $request)
            ->with('gastos', $gastos)
            //->with('example', $example)
            ->with('ingresos', $ingresos);

    }

    public function ingresosEgresosExport($actual, $minus)
    {
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDate();

        $gastos     = $this->egresosTEST($dates);
        $ingresos   = $this->ingresosTEST($dates);

        return array(

            'gastos'    => $gastos,
            'ingresos'  => $ingresos

        );

    }


    public function gastosExport($actual, $minus){

        $dates = $this->setDate($actual, $minus);
        return $this->egresosTEST($dates);
    }

    private function egresos($dates, $noData = false)
    {
        $this->getAllPermissions(Auth::user()->id);
        //Se obtienen todas las facturas de proveedores dentro de la fecha correspondinete
        $itemsFacturas = ItemsFacturaProv::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new FacturaProveedores)->getTable())
                    ->where('fecha_factura', '<=', $dates['fin'])
                    ->where('fecha_factura', '>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();
        $gastosItem = GastosCategoria::select('*')
            ->whereIn('gasto', function ($query) use ($dates){
                $query->select('id')
                    ->from(with(new Gastos)->getTable())
                    ->where('fecha' ,'<=', $dates['fin'])
                    ->where('fecha' ,'>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();
        $categoriaGasto = array();
        $categoriaGasto ['gasto'] = 0;
        //Se filtra por tipo de item y se agrupan su total por categoria
        foreach ($itemsFacturas as $itemsFactura){
            if($itemsFactura->tipo_item == 1) {
                $categoria = $itemsFactura->productoTotal()->categoriaId();
            }
            else{
                $categoria = $itemsFactura->producto(true);
            }
            if(!isset($categoriaGasto[$categoria->id])){
                $categoriaGasto[$categoria->id]['nombre']        = $categoria->nombre;
                $categoriaGasto[$categoria->id]['descripcion']   = $categoria->descripcion;
                $categoriaGasto[$categoria->id]['total']         = $itemsFactura->totalImp();
                $categoriaGasto[$categoria->id]['id']         = $categoria->id;
                $categoriaGasto ['gasto']                        += $itemsFactura->totalImp();
            }else{
                $categoriaGasto[$categoria->id]['total'] += $itemsFactura->totalImp();
                $categoriaGasto ['gasto']                += $itemsFactura->totalImp();
            }

        }
        if(count($gastosItem) > 0 )
        {
            foreach ($gastosItem as $gastoItem)
            {
                if(!isset($categoriaGasto[$gastoItem->categoria])){
                    $categoriaGasto[$gastoItem->categoria]['nombre']        = $gastoItem->categoria(true);
                    $categoriaGasto[$gastoItem->categoria]['descripcion']   = $gastoItem->detalleCat()->descripcion;
                    $categoriaGasto[$gastoItem->categoria]['total']         = $gastoItem->pago();
                    $categoriaGasto ['gasto']                               += $gastoItem->pago();
                }else{
                    $categoriaGasto[$gastoItem->categoria]['total'] += $gastoItem->pago();
                    $categoriaGasto ['gasto']                += $gastoItem->pago();
                }
            }
        }
        return $categoriaGasto;
    }

    private function ingresos($dates)
    {
        //Se obtienen todas las facturas dentro de la fecha correspondinete
        $itemsFacturas = ItemsFactura::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();
        $ingresosItem = IngresosCategoria::select('*')
            ->whereIn('ingreso', function ($query) use ($dates){
                $query->select('id')
                    ->from(with(new Ingreso)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('empresa', Auth::user()->empresa);
            })->get();

        $categoriaGanancia = array();
        $categoriaGanancia ['ingresos'] = 0;
        //Se filtra por tipo de item y se agrupan su total por categoria
        foreach ($itemsFacturas as $itemsFactura){
            if($itemsFactura->tipo_inventario == 1){
                $categoria = $itemsFactura->productoTotal()->categoriaId();
                if($categoria){
                    if(!isset($categoriaGanancia[$categoria->id])){
                        $categoriaGanancia[$categoria->id]['nombre']        = $categoria->nombre;
                        $categoriaGanancia[$categoria->id]['descripcion']   = $categoria->descripcion;
                        $categoriaGanancia[$categoria->id]['total']         = $itemsFactura->totalImp();
                        $categoriaGanancia ['ingresos']                     += $itemsFactura->totalImp();
                    }else{
                        $categoriaGanancia[$categoria->id]['total'] += $itemsFactura->totalImp();
                        $categoriaGanancia ['ingresos']             += $itemsFactura->totalImp();
                    }
                }
            }
        }

        if (count($ingresosItem) > 0)
        {
            foreach ($ingresosItem as $ingresoItem)
            {
                if(!isset($categoriaGanancia[$ingresoItem->categoria])){
                    $categoriaGanancia[$ingresoItem->categoria]['nombre']        = $ingresoItem->categoria(true);
                    $categoriaGanancia[$ingresoItem->categoria]['descripcion']   = $ingresoItem->categoria()->descripcion;
                    $categoriaGanancia[$ingresoItem->categoria]['total']         = $ingresoItem->pago();
                    $categoriaGanancia ['ingresos']                              += $ingresoItem->pago();
                }else{
                    $categoriaGanancia[$ingresoItem->categoria]['total'] += $ingresoItem->pago();
                    $categoriaGanancia ['ingresos']                += $ingresoItem->pago();
                }
            }
        }
        return $categoriaGanancia;
    }

    private function egresosTEST($dates, $noData = false)
    {
        $this->getAllPermissions(Auth::user()->id);

        $proveedorFacturas = FacturaProveedores::where('fecha_factura', '<=', $dates['fin'])
            ->where('fecha_factura', '>=', $dates['inicio'])
            ->where('empresa', Auth::user()->empresa)
            ->get();
        $categoriaGasto = 0;
        foreach ($proveedorFacturas as $proveedorFactura){
            $categoriaGasto += $proveedorFactura->porpagar();
        }
        return $categoriaGasto;
    }

    private function ingresosTEST($dates)
    {
        //Se obtienen todas las facturas dentro de la fecha correspondinete
        $facturas = Factura::where('fecha', '<=', $dates['fin'])
            ->where('fecha', '>=', $dates['inicio'])
            ->where('empresa', Auth::user()->empresa)
            ->get();
        $categoriaGanancia = 0;
        foreach ($facturas as $factura) {
            $categoriaGanancia += $factura->pagado();
        }
        return $categoriaGanancia;
    }


    public function categorias(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $dates      = $this->setDateRequest($request);
        if($request->fecha == 8)
            $dates = $this->setDateRequest($request, true);
        $categorias = Categoria::where('empresa',Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();

        //Se obtiene el inventario según la categoría seleccionada
        if($request->categoria)
        {
            $inventario = Inventario::where('empresa', Auth::user()->empresa)
                ->where('categoria', $request->categoria)
                ->get();
        }
        else
        {
            $inventario = Inventario::where('empresa', Auth::user()->empresa)
                ->where('categoria', Auth::user()->empresa()->categoria_default)
                ->get();
            $request->categoria = Categoria::where('empresa', Auth::user()->empresa)->where('nombre', 'Activos')
                ->get()->first()->id;
        }

        $ingresos           = $this->ingresos($dates);
        $egresos            = $this->egresos($dates);
        $categoriadata      = Categoria::find($request->categoria);
        $cantidadInventario = $inventario->count();
        $codigo             = $categoriadata == '' ? '...' : Categoria::find($request->categoria)->codigo;
        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();

        if(!isset($ingresos[$request->categoria]))
        {
            $ingresos[$request->categoria]['total']  = 0;

        }
        if(!isset($egresos[$request->categoria]))
        {
            $egresos[$request->categoria]['total'] = 0;

        }

        view()->share(['title' => 'Reporte de Categoría', 'subseccion' => 'reportes']);
        return view('reportes.categorias.index')->with('request', $request)
            ->with('ingresos', $ingresos)
            ->with('egresos', $egresos)
            ->with('codigo', $codigo)
            //->with('example', $example)
            ->with('inventario', $inventario)
            ->with('categoriaData', $categoriadata)
            ->with('cantidad', $cantidadInventario)
            ->with('categorias', $categorias);
    }

    function orderMultiDimensionalArray ($toOrderArray, $field, $inverse = false) {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
            $position[$key]  = $row[$field];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        }
        else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    public function exportar_ventas(){

    }

    /*
     * TODOS LOS PAGOS REFERENTES A LA CATEGORIA
     * */

    public function getPagosCategorias(Request $request){


        $this->getAllPermissions(Auth::user()->id);
        $dates      = $this->setDateRequest($request);
        if($request->fecha == 8)
            $dates = $this->setDateRequest($request, true);
        $categorias = Categoria::where('empresa',Auth::user()->empresa)
            ->where('estatus', 1)->whereNull('asociado')->get();

        //Se obtiene el inventario según la categoría seleccionada
        if($request->categoria){

            $pagos = Gastos::where('empresa', Auth::user()->empresa)
                ->join('gastos_categoria','gasto','=','gastos.id')
                ->where('categoria', $request->categoria)
                ->get();

        }else{
            $pagos = Gastos::where('empresa', Auth::user()->empresa)
                ->join('gastos_categoria','gasto','=','gastos.id')
                ->where('categoria', Auth::user()->empresa()->categoria_default)
                ->get();
            $request->categoria = Categoria::where('empresa', Auth::user()->empresa)->where('nombre', 'Activos')
                ->get()->first()->id;
        }

        $ingresos           = $this->ingresos($dates);
        $egresos            = $this->egresos($dates);
        $categoriadata      = Categoria::find($request->categoria);
        $cantidadPagos      = $pagos->count();
        $codigo             = $categoriadata == '' ? '...' : Categoria::find($request->categoria)->codigo;

        if(!isset($ingresos[$request->categoria]))
        {
            $ingresos[$request->categoria]['total']  = 0;

        }
        if(!isset($egresos[$request->categoria]))
        {
            $egresos[$request->categoria]['total'] = 0;

        }


        view()->share(['title' => 'Reporte de Pagos por Categoría', 'subseccion' => 'reportes']);

        return view('reportes.categoriasp.index')
            ->with(compact(
                'request','ingresos','egresos','codigo',
                'pagos','categoriaData','categorias','cantidadPagos','categoriadata'));
    }


    public function getReporteDiario(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $user = Auth::user()->empresa;

        if (!$request->fecha) {
            $fecha = date('Y-m-d');
        }else{
            $fecha = date('Y-m-d',strtotime($request->fecha));
        }

        //Se cuenta cuantas veces se repiten las facturas con un mismo producto
        $sqlRepeticiones =
            "SELECT id, producto  FROM items_factura WHERE items_factura.factura IN
                    (
                        SELECT id FROM factura
                            WHERE factura.fecha >= '$fecha'
                                AND factura.fecha <= '$fecha'
                                AND factura.empresa = '$user'
                                AND factura.tipo != 2
                            ORDER BY factura.id DESC
                    )";

        $repeticones = DB::select($sqlRepeticiones);
        $totales = array();
        foreach ($repeticones as $repeticone){
            $item = ItemsFactura::where('id', $repeticone->id)->first();
            if(!isset($totales[$repeticone->producto])){
                $totales[$repeticone->producto]['rep'] = 1;
                $totales[$repeticone->producto]['subtotal'] = $item->total();
                $totales[$repeticone->producto]['total'] = $item->totalImp();
                echo $item->porcentaje;

            }else{
                $totales[$repeticone->producto]['rep']+= 1;
                $totales[$repeticone->producto]['subtotal'] += $item->total();
                $totales[$repeticone->producto]['total'] += $item->totalImp();
            }

        }


        //Subconsulta para obtener todos los productos según su item factura
        $productos = DB::table('inventario')
            ->select('id', 'producto', 'ref', 'precio', DB::raw('precio+(precio*(impuesto/100)) as total'))
            ->whereIn('id', function ($query) use ($fecha, $user){
                $query->select('producto')
                    ->from(with(new ItemsFactura)->getTable())
                    ->whereIn('factura', function ($sql) use ($fecha,  $user){
                        $sql->select('id')
                            ->from(with(new Factura)->getTable())
                            ->where('fecha', ">=", $fecha)
                            ->where('fecha', "<=", $fecha)
                            ->where('empresa', $user)
                            ->where('tipo','!=', 2);
                    });
            })->paginate(50);



        //Se agregan las veces que se repiten y se determina el gran total
        $i = 0;
        $total = 0;
        $subtotal = 0;
        foreach ( $productos as $producto ){
            $producto->rep = $totales[$producto->id]['rep'];
            $producto->precio = $totales[$producto->id]['subtotal'];
            $producto->total = $totales[$producto->id]['total'];
            $total += $producto->total;
            $subtotal += $producto->precio;
            $i++;
        }


        return view('reportes.reporteDiario.index')->with(compact('request','productos', 'subtotal', 'total'));

    }

    public function getReporteContactos(Request $request){
        $this->getAllPermissions(Auth::user()->id);

        $dates  = $this->setDateRequest($request);

        $contactos=Contacto::join('tipos_empresa as te', 'te.id', '=', 'contactos.tipo_empresa')
            ->select('contactos.*', 'te.nombre as tipo_emp')
            ->where('contactos.empresa', Auth::user()->empresa)
            ->where('contactos.created_at','>=', $dates['inicio'].' 00:00:00')
            ->where('contactos.created_at','<=', $dates['fin'].' 00:00:00')
            ->orderBy('contactos.id','DESC');


        if($request->fecha){
            $appends['fecha']=$request->fecha;
            $contactos = $contactos->where('contactos.created_at', ">=", date('Y-m-d', strtotime($request->fecha)));
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
            $contactos=$contactos->where('contactos.created_at', "<=", date('Y-m-d', strtotime($request->hasta)));
        }

        $contactos=$contactos->get();



        return view('reportes.contactos.index')->with(compact('request','contactos'));
    }

    public function getReporteReteIva(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $dates  = $this->setDateRequest($request);
        if($request->fecha == 8)
            $dates = $this->setDateRequest($request, true);

        $itemsFacturas = ItemsFactura::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('tipo', '!=', 2)
                    ->where('empresa', Auth::user()->empresa);
            })->get();

        $retencionItems = FacturaRetencion::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('tipo', '!=', 2)
                    ->where('empresa', Auth::user()->empresa);
            })->get();

        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();
        $totales = array(
            'totalIva'  => 0,
            'totalPrecio'  => 0,
            'totalValorRetenido'  => 0
        );
        // dd($itemsFacturas);
        foreach($itemsFacturas as $iva){
            if($iva->impuesto > 0 && $iva->impuesto == 19){
                $totales['totalIva'] += ($iva->precio * $iva->impuesto)/100;

            }
        }

        foreach ($retencionItems as $retenciones){
            $totales['totalValorRetenido'] += $retenciones->valor;
        }

        return view('reportes.reteiva.index')->with(compact('request','totales', 'example'));
    }

    /**
     * Pagina los objetos tipo Collection, conservando los parametros get de la URL.
     * - items: Collection a paginar
     * - perPage: Elementos mostrados por pagina.
     * - page: Pagina actual
     * - request: Request actual
     * - pageName: Nombre de la variable de conteo
     * @param $items
     * @param int $perPage
     * @param null $page
     * @param $request
     * @param string $pageName
     * @return LengthAwarePaginator
     */
    private function paginate($items, $perPage = 15, $page = null, $request, $pageName = 'page')
    {
        $page = $page ?: (Paginator::resolveCurrentPage($pageName) ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        $params = !array_key_exists(1, explode('?', $request->fullUrl())) ? ''
            : (explode('?', $request->fullUrl()))[1];
        return (new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page,
            [ 'path'=> Paginator::resolveCurrentPath(), 'pageName' => $pageName ]
        ))->withPath($request->segment(3)."?$params");
    }

    public function getReporteRemisionesFacturas(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $dates  = $this->setDateRequest($request);
        if($request->fecha == 8)
            $dates = $this->setDateRequest($request, true);

        $itemsFacturas = ItemsFactura::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('tipo', '!=', 2)
                    ->where('empresa', Auth::user()->empresa);
            })->get();

        $itemsRemisiones = ItemsRemision::select('*')
            ->whereIn('remision', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Remision)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('documento', '=', 1)
                    ->whereIn('estatus', [0, 1])
                    ->where('empresa', Auth::user()->empresa);
            })->get();

        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();
        $totales = array(
            'totalFactura'  => 0,
            'totalRemision'  => 0
        );
        // dd($itemsFacturas);
        foreach($itemsFacturas as $factura){
            $totales['totalFactura'] += $factura->precio;
        }

        foreach($itemsRemisiones as $remision){
            $totales['totalRemision'] += $remision->precio;
        }


        return view('reportes.facturaremision.index')->with(compact('request','totales', 'example'));
    }

    public function getTotalPagos(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $dates  = $this->setDateRequest($request);
        if($request->fecha == 8)
            $dates = $this->setDateRequest($request, true);

        $ingresosFacturas = IngresosFactura::select('*')
            ->whereIn('factura', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Ingreso)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('tipo', '=', 1)
                    ->where('empresa', Auth::user()->empresa);
            })->get();

        $ingresosRemisiones = IngresosRemision::select('*')
            ->whereIn('remision', function($query) use ($dates){
                $query->select('id')
                    ->from(with(new Factura)->getTable())
                    ->where('fecha', '<=', $dates['fin'])
                    ->where('fecha', '>=', $dates['inicio'])
                    ->where('estatus', '=', 1)
                    ->where('empresa', Auth::user()->empresa);
            })->get();

        $totales = array(
            'totalFactura'  => 0,
            'totalRemision'  => 0
        );
        // dd($itemsFacturas);
        foreach($ingresosFacturas as $ingresosFactura){
            $totales['totalFactura'] += $ingresosFactura->pago;
        }
        foreach($ingresosRemisiones as $ingresosRemisione){
            $totales['totalRemision'] += $ingresosRemisione->pago;
        }
        $example = Factura::where('empresa', Auth::user()->empresa)->get()->last();

        return view('reportes.pagosRemisionesFacturas.index')->with(compact('request','totales', 'example'));

    }

    /**
     * Ordena a los objetos tipo Collection
     * @param $invoices
     * @param $request
     */
    private function sortCollection(&$invoices, $request)
    {
        foreach ($invoices as $invoice) {
            $invoice->total = $invoice->total()->total;
            $invoice->pagado = $invoice->pagado();
            $invoice->porpagar = $invoice->porpagar();
        }
        if($request->orderby == 4 || $request->orderby == 5  || $request->orderby == 6 ){
            switch ($request->orderby){
                case 4:
                    $invoices = $request->order  ? $invoices->sortBy('total') : $invoices = $invoices->sortByDesc('total');
                    break;
                case 5:
                    $invoices = $request->order ? $invoices->sortBy('pagado') : $invoices = $invoices->sortByDesc('pagado');
                    break;
                case 6:
                    $invoices = $request->order ? $invoices->sortBy('porpagar') : $invoices = $invoices->sortByDesc('porpagar');
                    break;
            }
        }

    }

    public function cajas(Request $request) {
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Cajas', 'icon' =>'fas fa-chart-line']);
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);
        $empresa =Auth::user()->empresa;
        $example = Movimiento::where('empresa', $empresa)->get()->last();

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }

        if(!isset($request->servidor) ||  $request->servidor == 0){

            $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
                ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
                ->where('fecha', '>=', $dates['inicio'])
                ->where('fecha', '<=', $dates['fin'])
                ->where('movimientos.empresa',$empresa);

            $movimientosTodos = Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
                ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
                ->where('fecha', '>=', $dates['inicio'])
                ->where('fecha', '<=', $dates['fin'])
                ->where('movimientos.empresa',$empresa);

        }
        elseif($request->servidor){

            $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->leftjoin('ingresos_factura as if','if.ingreso','movimientos.id_modulo')
            ->leftjoin('factura as f','f.id','if.factura')
            ->leftjoin('contracts as co','co.id','f.contrato_id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('movimientos.fecha', '>=', $dates['inicio'])
            ->where('movimientos.fecha', '<=', $dates['fin'])
            ->where('movimientos.modulo',1)
            ->where('co.server_configuration_id',$request->servidor)
            ->where('movimientos.empresa',$empresa);

             $movimientosTodos = Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->leftjoin('ingresos_factura as if','if.ingreso','movimientos.id_modulo')
            ->leftjoin('factura as f','f.id','if.factura')
            ->leftjoin('contracts as co','co.id','f.contrato_id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('movimientos.fecha', '>=', $dates['inicio'])
            ->where('movimientos.fecha', '<=', $dates['fin'])
            ->where('movimientos.modulo',1)
            ->where('co.server_configuration_id',$request->servidor)
            ->where('movimientos.empresa',$empresa);

        }

        if($request->caja){
            //$banco = Banco::where('empresa',$empresa)->where('nro', $request->caja)->first();dd($request->caja);
            $movimientos->where('banco',$request->caja);
            $movimientosTodos->where('banco',$request->caja);
        }
        if($request->tipo>0){
            $movimientos->where('movimientos.tipo',$request->tipo);
            $movimientosTodos->where('movimientos.tipo',$request->tipo);
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->paginate(25)->appends($appends);
        $movimientosTodos = $movimientosTodos->get();

        $totales = array(
            'salida'    => 0,
            'entrada'   => 0
        );

        foreach ($movimientosTodos as $movimiento){
            $totales['salida']  += $movimiento->tipo==2?$movimiento->saldo:0;
            $totales['entrada']  += $movimiento->tipo==1?$movimiento->saldo:0;
        }

        $cajas = Banco::where('estatus',1)->get();

        $servidores = Mikrotik::where('status', 1)->where('empresa', $empresa)->get();

        return view('reportes.cajas.index')
            ->with('movimientos', $movimientos)
            ->with('request', $request)
            ->with('example', $example)
            ->with('totales', $totales)
            ->with('servidores', $servidores)
            ->with('cajas', $cajas);
    }

    public function instalacion(Request $request) {
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Contratos con Instalación', 'icon' =>'fas fa-chart-line']);
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);
        if($request->fecha == 8)
            $dates = $this->setDateRequest($request, true);

        //Código base tomado de datatable_movimientos

        $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.descripcion','Pago de Instalación de Servicio')
            ->where('movimientos.empresa',Auth::user()->empresa);

        $movimientosTodos = Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.descripcion','Pago de Instalación de Servicio')
            ->where('movimientos.empresa',Auth::user()->empresa);
        $example = Movimiento::where('empresa', Auth::user()->empresa)->get()->last();

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->paginate(25)->appends($appends);
        $movimientosTodos = $movimientosTodos->get();

        $totales = array(
            'salida'    => 0,
            'entrada'   => 0
        );

        foreach ($movimientosTodos as $movimiento){
            $totales['salida']  += $movimiento->tipo==2?$movimiento->saldo:0;
            $totales['entrada']  += $movimiento->tipo==1?$movimiento->saldo:0;
        }

        $cajas = Banco::where('estatus',1)->get();

        return view('reportes.instalacion.index')
            ->with('movimientos', $movimientos)
            ->with('request', $request)
            ->with('example', $example)
            ->with('totales', $totales)
            ->with('cajas', $cajas);
    }

    //repórtes para crc reporte 1.2
    public function reporte_1_2(Request $request) {
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de CRC 1.2', 'icon' =>'fas fa-chart-line']);
        $this->getAllPermissions(Auth::user()->id);
        // Obtener el trimestre actual
        $trimestreActual = Carbon::now()->quarter;

        // Obtener el año actual
        $anioActual = Carbon::now()->year;

        // Obtener la fecha de inicio del trimestre actual
        $inicioTrimestre = Carbon::now()->startOfQuarter()->toDateString();

        // Obtener la fecha de fin del trimestre actual
        $finTrimestre = Carbon::now()->endOfQuarter()->toDateString();


        // Obtener los contratos del trimestre actual
        // $contratos = Contrato::join('contactos', 'contracts.client_id', '=', 'contactos.id')
        // ->join('planes_velocidad', 'contracts.plan_id', '=', 'planes_velocidad.id')
        // ->whereYear('contracts.created_at', $anioActual)
        // ->whereRaw('DATE(contracts.created_at) BETWEEN ? AND ?', [$inicioTrimestre, $finTrimestre])
        // ->paginate(25);

        $contratos = Contrato::join('contactos', 'contracts.client_id', '=', 'contactos.id')
        ->join('planes_velocidad', 'contracts.plan_id', '=', 'planes_velocidad.id')
        ->leftJoin(DB::raw('(SELECT plan_id, COUNT(id) as cantidad_suscriptores FROM contracts GROUP BY plan_id) as plan_counts'),
                   'contracts.plan_id', '=', 'plan_counts.plan_id')
        ->whereYear('contracts.created_at', $anioActual)
        ->whereRaw('DATE(contracts.created_at) BETWEEN ? AND ?', [$inicioTrimestre, $finTrimestre])
        ->select(
            'contracts.*',
            'planes_velocidad.name as name',
            'planes_velocidad.price as price',
            'plan_counts.cantidad_suscriptores'
        )
        ->paginate(25);


        // return $contratos;
            return view('reportes.mintic.index')
                ->with('contratos', $contratos)
                ->with('trimestre', $trimestreActual);
    }

    public function facturasImpagas(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        DB::enableQueryLog();
        if ($request->nro == 'remisiones'){
            return $this->remisiones($request);
        }else{

            $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
            view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Facturas Impagas', 'icon' =>'fas fa-chart-line']);
            $campos=array( '','nombrecliente', 'factura.fecha', 'factura.vencimiento', 'nro', 'nro', 'nro', 'nro');
            if (!$request->orderby) {
                $request->orderby=1; $request->order=1;
            }
            $orderby=$campos[$request->orderby];
            $order=$request->order==1?'DESC':'ASC';

            $facturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
                ->join('contracts', 'factura.contrato_id', '=', 'contracts.id')
                ->leftjoin('mikrotik', 'mikrotik.id', '=', 'contracts.server_configuration_id')
                ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                    'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa','c.status')
                // ->where('factura.tipo','<>',2)
                ->where('factura.empresa',Auth::user()->empresa)
                ->where('factura.estatus',1)
                ->where('c.status',1)
                ->groupBy('factura.id');
            $example = $facturas->get()->last();

            $dates = $this->setDateRequest($request);

            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $facturas=$facturas->where('factura.fecha','>=', $dates['inicio'])->where('factura.fecha','<=', $dates['fin']);
            }
            if($request->servidor){
                $facturas=$facturas->where('mikrotik.id', $request->servidor);
            }
            if($request->grupo){
                $facturas=$facturas->where('contracts.grupo_corte', $request->grupo);
            }
            if($request->nro && $request->nro != 0){
                $facturas=$facturas->where('factura.numeracion', $request->nro);
            }
            $ides=array();
            $facturas=$facturas->OrderBy($orderby, $order)->get();

            foreach ($facturas as $factura) {
                $ides[]=$factura->id;
            }

            foreach ($facturas as $invoice) {
                $invoice->subtotal = $invoice->total()->subsub;
                $invoice->iva = $invoice->impuestos_totales();
                $invoice->retenido = $factura->retenido(true);
                $invoice->total = $invoice->total()->total - $invoice->devoluciones();
            }
            if($request->orderby == 4 || $request->orderby == 5  || $request->orderby == 6 || $request->orderby == 7 ){
                switch ($request->orderby){
                    case 4:
                        $facturas = $request->order  ? $facturas->sortBy('subtotal') : $facturas = $facturas->sortByDesc('subtotal');
                        break;
                    case 5:
                        $facturas = $request->order ? $facturas->sortBy('iva') : $facturas = $facturas->sortByDesc('iva');
                        break;
                    case 6:
                        $facturas = $request->order ? $facturas->sortBy('retenido') : $facturas = $facturas->sortByDesc('retenido');
                        break;
                    case 7:
                        $facturas = $request->order ? $facturas->sortBy('total') : $facturas = $facturas->sortByDesc('total');
                        break;
                }
            }
            $facturas = $this->paginate($facturas, 15, $request->page, $request);


            $subtotal=$total=0;
            if ($ides) {
                $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
                $subtotal=$this->precision($result->total-$result->descuento);
                $total=$this->precision((float)$subtotal+$result->impuesto);
            }

            $mikrotiks = Mikrotik::all();
            $gruposCorte = GrupoCorte::where('empresa', Auth::user()->empresa)->get();

            return view('reportes.facturasImpagas.index')->with(compact('facturas', 'numeraciones', 'subtotal', 'total', 'request', 'example', 'mikrotiks', 'gruposCorte'));

        }
    }

    public function radicados(Request $request) {
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Radicados', 'icon' =>'far fa-life-ring']);
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);

        $movimientos = Radicado::where('id', '>', 0)->where('fecha', '>=', $dates['inicio'])->where('fecha', '<=', $dates['fin']);

        $movimientosTodos = Radicado::where('id', '>', 0)->where('fecha', '>=', $dates['inicio'])->where('fecha', '<=', $dates['fin']);
        $example = Radicado::where('empresa', Auth::user()->empresa)->get()->last();

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }
        if($request->tecnico){
            $movimientos->where('radicados.tecnico',$request->tecnico);
            $movimientosTodos->where('radicados.tecnico',$request->tecnico);
        }
        if($request->servicio){
            $movimientos->where('radicados.servicio',$request->servicio);
            $movimientosTodos->where('radicados.servicio',$request->servicio);
        }
        if($request->estatus){
            $movimientos->where('radicados.estatus',$request->estatus);
            $movimientosTodos->where('radicados.estatus',$request->estatus);
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->paginate(25)->appends($appends);
        $movimientosTodos = $movimientosTodos->get();
        $tecnicosRoles = DB::table('roles1')->where('rol', 'Técnico')->first();

        // $tecnicos = User::whereIn('rol',[43,46])->get();
        $tecnicos = User::where('rol',$tecnicosRoles->id)->get();

        $servicios = DB::table('servicios')->where('estatus',1)->get();

        return view('reportes.radicados.index')
            ->with('movimientos', $movimientos)
            ->with('request', $request)
            ->with('example', $example)
            ->with('tecnicos', $tecnicos)
            ->with('servicios', $servicios)
            ->with('movimientosTodos', $movimientosTodos);
    }

    public function recargas(Request $request) {
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Recargas', 'icon' =>'fas fa-dollar-sign']);
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);
        $totales = 0;

        $movimientos = DB::table('recargas_usuarios')->join('usuarios as u', 'u.id', '=', 'recargas_usuarios.usuario')->select('recargas_usuarios.id', 'recargas_usuarios.recarga','recargas_usuarios.fecha','u.nombres')->where('fecha', '>=', $dates['inicio'])->where('fecha', '<=', $dates['fin']);

        $movimientosTodos = DB::table('recargas_usuarios')->join('usuarios as u', 'u.id', '=', 'recargas_usuarios.usuario')->select('recargas_usuarios.id', 'recargas_usuarios.recarga','recargas_usuarios.fecha','u.nombres')->where('fecha', '>=', $dates['inicio'])->where('fecha', '<=', $dates['fin']);
        $example = DB::table('recargas_usuarios')->join('usuarios as u', 'u.id', '=', 'recargas_usuarios.usuario')->select('recargas_usuarios.id', 'recargas_usuarios.recarga','recargas_usuarios.fecha','u.nombres')->where('fecha', '>=', $dates['inicio'])->where('fecha', '<=', $dates['fin'])->get()->last();

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }
        if($request->usuario){
            $movimientos->where('recargas_usuarios.usuario',$request->usuario);
            $movimientosTodos->where('recargas_usuarios.usuario',$request->usuario);
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->paginate(25)->appends($appends);
        $movimientosTodos = $movimientosTodos->get();

        foreach ($movimientosTodos as $movimiento){
            $totales  += $movimiento->recarga;
        }

        $usuarios = User::where('user_status',1)->get();

        return view('reportes.recargas.index')
            ->with('movimientos', $movimientos)
            ->with('request', $request)
            ->with('example', $example)
            ->with('usuarios', $usuarios)
            ->with('movimientosTodos', $movimientosTodos)
            ->with('totales', $totales);
    }

    public function puntoVenta(Request $request) {
        view()->share(['seccion' => 'reportes', 'title' => 'Punto de Ventas (Ganancias)', 'icon' =>'fas fa-store-alt']);
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);

        //Código base tomado de datatable_movimientos

        $cajas = Banco::where('estatus',1)->where('tipo_cta',4)->get();
        $puntos = [];

        foreach($cajas as $caja){
            array_push($puntos, $caja->id);
        }

        $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.empresa',Auth::user()->empresa)
            ->whereIn('movimientos.banco', $puntos)
            ->groupBy('movimientos.fecha')
            ->groupBy('movimientos.banco');
        $example = Movimiento::where('empresa', Auth::user()->empresa)->get()->last();

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }
        if($request->caja){
            $movimientos->where('banco',$request->caja);
        }
        if($request->tipo>0){
            $movimientos->where('movimientos.tipo',$request->tipo);
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->paginate(25)->appends($appends);

        return view('reportes.punto_venta.index')
            ->with('movimientos', $movimientos)
            ->with('request', $request)
            ->with('example', $example)
            ->with('cajas', $cajas);
    }

    public function puntoVentaRecaudo(Request $request) {
        view()->share(['seccion' => 'reportes', 'title' => 'Punto de Ventas (Recaudos)', 'icon' =>'fas fa-store-alt']);
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);

        //Código base tomado de datatable_movimientos

        $cajas = Banco::where('estatus',1)->where('tipo_cta',4)->get();
        $puntos = [];

        foreach($cajas as $caja){
            array_push($puntos, $caja->id);
        }

        $movimientos= Movimiento::leftjoin('contactos as c', 'movimientos.contacto', '=', 'c.id')
            ->select('movimientos.*', DB::raw('if(movimientos.contacto,c.nombre,"") as nombrecliente'))
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('movimientos.empresa',Auth::user()->empresa)
            ->whereIn('movimientos.banco', $puntos)
            ->groupBy('movimientos.fecha')
            ->groupBy('movimientos.banco');
        $example = Movimiento::where('empresa', Auth::user()->empresa)->get()->last();

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }
        if($request->caja){
            $movimientos->where('banco',$request->caja);
        }
        if($request->tipo>0){
            $movimientos->where('movimientos.tipo',$request->tipo);
        }

        $movimientos=  $movimientos->orderBy('fecha', 'DESC')->paginate(25)->appends($appends);

        $cajas = Banco::where('estatus',1)->where('tipo_cta',4)->get();

        return view('reportes.punto_venta_recaudo.index')
            ->with('movimientos', $movimientos)
            ->with('request', $request)
            ->with('example', $example)
            ->with('cajas', $cajas);
    }

    public function planes(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        DB::enableQueryLog();


        $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
        $cajas = Banco::where('estatus',1)->get();
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Planes Facturados', 'icon' =>'fas fa-chart-line']);
        $campos=array( '','nombrecliente', 'factura.fecha', 'factura.vencimiento', 'nro', 'nro', 'nro', 'nro');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $facturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->join('ingresos_factura as ig', 'factura.id', '=', 'ig.factura')
            ->join('ingresos as i', 'ig.ingreso', '=', 'i.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'i.fecha as pagada','i.cuenta')
            ->whereIn('factura.tipo', [1,2])
            ->where('factura.empresa',Auth::user()->empresa)
            ->where('factura.estatus',0)
            ->groupBy('factura.id');
        $example = $facturas->get()->last();

        $dates = $this->setDateRequest($request);

        if($request->input('fechas') != 8 || (!$request->has('fechas'))){
            $facturas=$facturas->where('i.fecha','>=', $dates['inicio'])->where('i.fecha','<=', $dates['fin']);
        }
        if($request->caja){
            $facturas=$facturas->where('i.cuenta',$request->caja);
        }
        $ides=array();
        $facturas=$facturas->OrderBy($orderby, $order)->get();

        foreach ($facturas as $factura) {
            $ides[]=$factura->id;
        }

        foreach ($facturas as $invoice) {
            $invoice->subtotal = $invoice->total()->subsub;
            $invoice->iva = $invoice->impuestos_totales();
            $invoice->retenido = $factura->retenido(true);
            $invoice->total = $invoice->total()->total - $invoice->devoluciones();
        }
        if($request->orderby == 4 || $request->orderby == 5  || $request->orderby == 6 || $request->orderby == 7 ){
            switch ($request->orderby){
                case 4:
                    $facturas = $request->order  ? $facturas->sortBy('subtotal') : $facturas = $facturas->sortByDesc('subtotal');
                    break;
                case 5:
                    $facturas = $request->order ? $facturas->sortBy('iva') : $facturas = $facturas->sortByDesc('iva');
                    break;
                case 6:
                    $facturas = $request->order ? $facturas->sortBy('retenido') : $facturas = $facturas->sortByDesc('retenido');
                    break;
                case 7:
                    $facturas = $request->order ? $facturas->sortBy('total') : $facturas = $facturas->sortByDesc('total');
                    break;
            }
        }
        $facturas = $this->paginate($facturas, 15, $request->page, $request);


        $subtotal=$total=0;
        if ($ides) {
            $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
            $subtotal=$this->precision($result->total-$result->descuento);
            $total=$this->precision((float)$subtotal+$result->impuesto);
        }
        return view('reportes.ventas.planes')->with(compact('facturas', 'numeraciones', 'subtotal', 'total', 'request', 'example','cajas'));
    }

    function ivas(Request $request){
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Ivas', 'icon' =>'fas fa-chart-line']);
        $this->getAllPermissions(Auth::user()->id);
        $dates = $this->setDateRequest($request);

        $empresa = Auth::user()->empresa;

        if(!isset($request->documento)){
            $request->documento = 2;
        }

        if($request->fecha){
            $appends['fecha']=$request->fecha;
        }
        if($request->fecha){
            $appends['hasta']=$request->hasta;
        }

        if($request->documento == 1){
            $documentos = Factura::leftjoin('contactos as c', 'cliente', '=', 'c.id')
            ->select('factura.*', 'c.nombre', 'factura.codigo as nro')
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('tipo',2)
            ->where('factura.empresa',$empresa);
        }elseif($request->documento == 2){
            $documentos = NotaCredito::leftjoin('contactos as c', 'cliente', '=', 'c.id')
            ->select('notas_credito.*', 'c.nombre')
            ->where('fecha', '>=', $dates['inicio'])
            ->where('fecha', '<=', $dates['fin'])
            ->where('notas_credito.empresa',$empresa);
        }

        //obtenemos el total del iva
        $totalIva = 0;
        $documentosGet = $documentos->get();
        foreach($documentosGet as $doc){
            $totalIva+=$doc->impuestos_totales();
        }

        $documentos =  $documentos->orderBy('fecha', 'DESC')->paginate(25)->appends($appends);

        return view('reportes.ivas.index')
            ->with('documentos', $documentos)
            ->with('request', $request)
            ->with('totalIva', $totalIva)
            ;
    }

    public function facturasElectronicas(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        DB::enableQueryLog();
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Facturas Electrónicas', 'icon' =>'fas fa-chart-line']);

        $campos=array( '','nombrecliente', 'factura.fecha', 'factura.vencimiento', 'nro', 'nro', 'nro', 'nro');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $facturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'factura.emitida'
                )
            ->where('factura.tipo',2)
            ->where('factura.empresa',Auth::user()->empresa)
            ->where('emitida',$request->tipo)
            ->groupBy('factura.id');

        $example = $facturas->get()->last();

        $dates = $this->setDateRequest($request);

        if($request->input('fechas') != 8 || (!$request->has('fechas'))){
            $facturas=$facturas->where('factura.fecha','>=', $dates['inicio'])->where('factura.fecha','<=', $dates['fin']);
        }
        $ides=array();
        $facturas=$facturas->OrderBy($orderby, $order)->get();

        foreach ($facturas as $factura) {
            $ides[]=$factura->id;
        }

        foreach ($facturas as $invoice) {
            $invoice->subtotal = $invoice->total()->subsub;
            $invoice->iva = $invoice->impuestos_totales();
            $invoice->retenido = $factura->retenido(true);
            $invoice->total = $invoice->total()->total - $invoice->devoluciones();
        }
        if($request->orderby == 4 || $request->orderby == 5  || $request->orderby == 6 || $request->orderby == 7 ){
            switch ($request->orderby){
                case 4:
                    $facturas = $request->order  ? $facturas->sortBy('subtotal') : $facturas = $facturas->sortByDesc('subtotal');
                    break;
                case 5:
                    $facturas = $request->order ? $facturas->sortBy('iva') : $facturas = $facturas->sortByDesc('iva');
                    break;
                case 6:
                    $facturas = $request->order ? $facturas->sortBy('retenido') : $facturas = $facturas->sortByDesc('retenido');
                    break;
                case 7:
                    $facturas = $request->order ? $facturas->sortBy('total') : $facturas = $facturas->sortByDesc('total');
                    break;
            }
        }
        $facturas = $this->paginate($facturas, 15, $request->page, $request);

        $subtotal=$total=0;
        if ($ides) {
            $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
            $subtotal=$this->precision($result->total-$result->descuento);
            $total=$this->precision((float)$subtotal+$result->impuesto);
        }
        return view('reportes.facturasElectronicas.index')->with(compact('facturas', 'subtotal', 'total', 'request', 'example'));
    }

    public function facturasEstandar(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        DB::enableQueryLog();
        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Facturas Estándar', 'icon' =>'fas fa-chart-line']);

        $campos=array( '','nombrecliente', 'factura.fecha', 'factura.vencimiento', 'nro', 'nro', 'nro', 'nro');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $facturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->select('factura.id', 'factura.codigo', 'factura.nro','factura.cot_nro', DB::raw('c.nombre as nombrecliente'),
                'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.empresa', 'factura.emitida'
                )
            ->where('factura.tipo',1)
            ->where('factura.empresa',Auth::user()->empresa)
            ->groupBy('factura.id');

        $example = $facturas->get()->last();

        $dates = $this->setDateRequest($request);

        if($request->input('fechas') != 8 || (!$request->has('fechas'))){
            $facturas=$facturas->where('factura.fecha','>=', $dates['inicio'])->where('factura.fecha','<=', $dates['fin']);
        }
        $ides=array();
        $facturas=$facturas->OrderBy($orderby, $order)->get();

        foreach ($facturas as $factura) {
            $ides[]=$factura->id;
        }

        foreach ($facturas as $invoice) {
            $invoice->subtotal = $invoice->total()->subsub;
            $invoice->iva = $invoice->impuestos_totales();
            $invoice->retenido = $factura->retenido(true);
            $invoice->total = $invoice->total()->total - $invoice->devoluciones();
        }
        if($request->orderby == 4 || $request->orderby == 5  || $request->orderby == 6 || $request->orderby == 7 ){
            switch ($request->orderby){
                case 4:
                    $facturas = $request->order  ? $facturas->sortBy('subtotal') : $facturas = $facturas->sortByDesc('subtotal');
                    break;
                case 5:
                    $facturas = $request->order ? $facturas->sortBy('iva') : $facturas = $facturas->sortByDesc('iva');
                    break;
                case 6:
                    $facturas = $request->order ? $facturas->sortBy('retenido') : $facturas = $facturas->sortByDesc('retenido');
                    break;
                case 7:
                    $facturas = $request->order ? $facturas->sortBy('total') : $facturas = $facturas->sortByDesc('total');
                    break;
            }
        }
        $facturas = $this->paginate($facturas, 15, $request->page, $request);

        $subtotal=$total=0;
        if ($ides) {
            $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
            $subtotal=$this->precision($result->total-$result->descuento);
            $total=$this->precision((float)$subtotal+$result->impuesto);
        }
        $grupos_corte = GrupoCorte::where('status',1)->get();
        return view('reportes.facturasEstandar.index')->with(compact('facturas', 'subtotal', 'total', 'request', 'example','grupos_corte'));
    }

    public function saldosFavor(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $total = 0;

        $saldos_favor = Contacto::where("saldo_favor",">",0);



        $saldos_favor= $saldos_favor->OrderBy("nit", "asc")->get();

        foreach ($saldos_favor as $saldo){
            $total += $saldo->saldo_favor;
        }

        $saldos_favor = $this->paginate($saldos_favor, 15, $request->page, $request);

        return view('reportes.saldofavor.index')->with(compact('saldos_favor',
            'total'));
    }

    public function notasCredito(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        DB::enableQueryLog();

        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Notas Crédito', 'icon' => '']);

        $campos = array('', 'f.codigo', 'c.nombre', 'notas_credito.fecha', 'nro', 'nro', 'nro', 'nro', 'nro');

        //Si no existe un request entrante vamos a ordenar por la fecha de la factura descendentemente
        if (!$request->orderby) {
            $request->orderby = 3;
            $request->order = 1;
        }

        $orderby = $campos[$request->orderby];
        $order = $request->order == 1 ? 'DESC' : 'ASC';

        //Si no existe un request con fecha vamos a tomas el mes actual como rangos
        if($request->fechas != 8){
            if (!$request->fecha) {
                $arrayDate = $this->setDateRequest($request);
                $desde = $arrayDate['inicio'];
                $hasta = $arrayDate['fin'];
            } else {
                $desde = Carbon::parse($request->fecha)->format('Y-m-d');
                $hasta = Carbon::parse($request->hasta)->format('Y-m-d');
            }
        }else{
            $desde = '2000-01-01';
            $hasta = now()->format('Y-m-d');
        }

        $notasc = NotaCredito::join('contactos as c', 'notas_credito.cliente', '=', 'c.id')
            ->join('notas_factura as nf', 'nf.nota', '=', 'notas_credito.id')
            ->join('factura as f', 'f.id', '=', 'nf.factura')
            ->where('notas_credito.empresa', auth()->user()->empresa)
            ->where('notas_credito.fecha', '>=', $desde)->where('notas_credito.fecha', '<=', $hasta)
            ->where('notas_credito.emitida',$request->tipo)
            ->select(
                'notas_credito.id as id',
                'notas_credito.nro',
                'c.id as cliente',
                'c.nombre',
                'notas_credito.fecha',
                'f.id as fid',
                'f.codigo',
                'f.nro as fnro',
                'notas_credito.emitida'
            )
            ->orderBy($orderby, $order)->groupBy('notas_credito.id')->get();


        //variable la cual identifica lo que se debe descontar por devoluciones pagadas.
        $devoluciones = 0;
        $retenciones = 0;
        $subtotal = 0;
        $total = 0;
        $iva = 0;
        $saldosR = 0;

        foreach ($notasc as $nota) {
            $subtotal = $subtotal + $nota->subtotal = ($nota->total()->subtotal - $nota->total()->descuento);
            $iva = $iva + $nota->iva = $nota->impuestos_totales();
            $retenciones = $retenciones + $nota->retenido = $nota->retenido_factura();
            $total = $total + $nota->total = $nota->total()->total;
            $saldosR = $saldosR + $nota->saldoRestante = $nota->por_aplicar();
        }


        if (
            $request->orderby == 4 || $request->orderby == 5 || $request->orderby == 6 || $request->orderby == 7 || $request->orderby == 8
        ) {
            switch ($request->orderby) {

                case 4:
                    $notasc = $request->order ? $notasc->sortBy('subtotal') : $notasc = $notasc->sortByDesc('subtotal');
                    break;
                case 5:
                    $notasc = $request->order ? $notasc->sortBy('iva') : $notasc = $notasc->sortByDesc('iva');
                    break;
                case 6:
                    $notasc = $request->order ? $notasc->sortBy('retenido') : $notasc = $notasc->sortByDesc('retenido');
                    break;
                case 7:
                    $notasc = $request->order ? $notasc->sortBy('total') : $notasc = $notasc->sortByDesc('total');
                    break;
            }
        }
        $notasc = $this->paginate($notasc, 15, $request->page, $request);
        $notas = $notasc;

        return view('reportes.notascredito.index')->with(compact('notas',
        'subtotal',
        'total',
        'request',
        'iva',
        'retenciones',
        'saldosR'));
    }


    public function balance(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        DB::enableQueryLog();

        view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Balance', 'icon' => '']);

        $campos = array('', 'p.nombre', 'p.codigo', 'puc_movimiento.debito', 'puc_movimiento.credito', 'totalfinal');

        //Si no existe un request entrante vamos a ordenar por la fecha de la factura descendentemente
        if (!$request->orderby) {
            $request->orderby = 3;
            $request->order = 1;
        }

        $orderby = $campos[$request->orderby];
        $order = $request->order == 1 ? 'DESC' : 'ASC';

        //Si no existe un request con fecha vamos a tomas el mes actual como rangos
        if($request->fechas != 8){
            if (!$request->fecha) {
                $arrayDate = $this->setDateRequest($request);
                $desde = $arrayDate['inicio'];
                $hasta = $arrayDate['fin'];
            } else {
                $desde = Carbon::parse($request->fecha)->format('Y-m-d');
                $hasta = Carbon::parse($request->hasta)->format('Y-m-d');
            }
        }else{
            $desde = '2000-01-01';
            $hasta = now()->format('Y-m-d');
        }

        $movimientosContables = PucMovimiento::join('puc as p','p.id','puc_movimiento.cuenta_id')
            ->select('puc_movimiento.*','p.nombre as cuentacontable',
            DB::raw("SUM((`debito`)) as totaldebito"),
            DB::raw("SUM((`credito`)) as totalcredito"),
            DB::raw("ABS(SUM((`credito`)) -  SUM((`debito`))) as totalfinal"))
            ->orderBy($orderby, $order)
            ->groupBy('cuenta_id')
            ->get();

        // if (
        //     $request->orderby == 4 || $request->orderby == 5 || $request->orderby == 6 || $request->orderby == 7 || $request->orderby == 8
        // ) {
        //     switch ($request->orderby) {

        //         case 4:
        //             $notasc = $request->order ? $notasc->sortBy('subtotal') : $notasc = $notasc->sortByDesc('subtotal');
        //             break;
        //         case 5:
        //             $notasc = $request->order ? $notasc->sortBy('iva') : $notasc = $notasc->sortByDesc('iva');
        //             break;
        //         case 6:
        //             $notasc = $request->order ? $notasc->sortBy('retenido') : $notasc = $notasc->sortByDesc('retenido');
        //             break;
        //         case 7:
        //             $notasc = $request->order ? $notasc->sortBy('total') : $notasc = $notasc->sortByDesc('total');
        //             break;
        //     }
        // }
        $movimientosContables = $this->paginate($movimientosContables, 15, $request->page, $request);
        // $notas = $notasc;

        return view('reportes.balance.index')->with(compact('movimientosContables',
        'request'));
    }

    public function reporte_1_2_mostrar(Request $request) {

        $this->getAllPermissions(Auth::user()->id);
          // Obtener el año y el trimestre de la solicitud
          if(!$request->input('anio')){
            $trimestreActual = Carbon::now()->quarter;
            // Obtener el año actual
            $anioActual = Carbon::now()->year;

        }else{
            // Obtener el año y el trimestre de la solicitud
            $anioActual = $request->input('anio');
            $trimestreActual = $request->input('trimestre');
        }

            switch ($trimestreActual) {
                case 1:
                    $inicioTrimestre = Carbon::create(date('Y'), 1, 1)->startOfDay()->toDateString();
                    $finTrimestre = Carbon::create(date('Y'), 3, 31)->endOfDay()->toDateString();
                    break;
                case 2:
                    $inicioTrimestre = Carbon::create(date('Y'), 4, 1)->startOfDay()->toDateString();
                    $finTrimestre = Carbon::create(date('Y'), 6, 30)->endOfDay()->toDateString();
                    break;
                case 3:
                    $inicioTrimestre = Carbon::create(date('Y'), 7, 1)->startOfDay()->toDateString();
                    $finTrimestre = Carbon::create(date('Y'), 9, 30)->endOfDay()->toDateString();
                    break;
                case 4:
                    $inicioTrimestre = Carbon::create(date('Y'), 10, 1)->startOfDay()->toDateString();
                    $finTrimestre = Carbon::create(date('Y'), 12, 31)->endOfDay()->toDateString();
                    break;
                default:
                    throw new Exception("Trimestre inválido. Debe ser un número entre 1 y 4.");
            }

            // Obtener la fecha de inicio del trimestre actual
            $inicioTrimestre = Carbon::now()->startOfQuarter()->toDateString();

            // Obtener la fecha de fin del trimestre actual
            $finTrimestre = Carbon::now()->endOfQuarter()->toDateString();

            // Obtener los contratos del año y trimestre especificados
            $contratos = Contrato::join('contactos', 'contracts.client_id', '=', 'contactos.id')
            ->join('planes_velocidad', 'contracts.plan_id', '=', 'planes_velocidad.id')
            ->leftJoin(DB::raw('(SELECT plan_id, COUNT(id) as cantidad_suscriptores FROM contracts GROUP BY plan_id) as plan_counts'),
                    'contracts.plan_id', '=', 'plan_counts.plan_id')
            ->whereYear('contracts.created_at', $anioActual)
            ->whereRaw('DATE(contracts.created_at) BETWEEN ? AND ?', [$inicioTrimestre, $finTrimestre])
            ->select(
                'contracts.*',
                'planes_velocidad.name as name',
                'planes_velocidad.price as price',
                'plan_counts.cantidad_suscriptores'
            )
            ->paginate(25);

            // Retornar los contratos y el trimestre como JSON
            return view('reportes.mintic.index')
            ->with('contratos', $contratos)
            ->with('request',$request)
            ->with('trimestre', $trimestreActual);
    }

    public function exportarExcel()
    {
        return Excel::download(new ContratosExport, 'contratos.xlsx');
    }

    public function generarExcel(Request $request)
    {
        // Definir las fechas de inicio y fin del trimestre en función del trimestre proporcionado
        if ($request->trimestre == 1) {
            $inicioTrimestre = Carbon::now()->startOfYear();
            $finTrimestre = Carbon::now()->startOfYear()->addMonths(3)->subDay();
        } else if ($request->trimestre == 2) {
            $inicioTrimestre = Carbon::now()->startOfYear()->addMonths(3);
            $finTrimestre = Carbon::now()->startOfYear()->addMonths(6)->subDay();
        } else if ($request->trimestre == 3) {
            $inicioTrimestre = Carbon::now()->startOfYear()->addMonths(6);
            $finTrimestre = Carbon::now()->startOfYear()->addMonths(9)->subDay();
        } else if ($request->trimestre == 4) {
            $inicioTrimestre = Carbon::now()->startOfYear()->addMonths(9);
            $finTrimestre = Carbon::now()->endOfYear();
        }

        // Obtener los contratos del trimestre actual
        $contratos = Contrato::join('contactos', 'contracts.client_id', '=', 'contactos.id')
            ->join('planes_velocidad', 'contracts.plan_id', '=', 'planes_velocidad.id')
            ->whereYear('contracts.created_at', $request->anio)
            ->whereRaw('DATE(contracts.created_at) BETWEEN ? AND ?', [$inicioTrimestre, $finTrimestre])
            ->paginate(25);

        // Crear un nuevo objeto PHPExcel
        $objPHPExcel = new PHPExcel();

        // Agregar encabezados de columna
        $encabezados = ['Fecha', 'Trimestre', 'Id municipio', 'Id segemento planes', 'Cantidad de suscriptores', 'Nombre del plan', 'Valor plan iva', 'Valor plan', 'Id modalidad plan', 'Fecha inicio', 'Fecha fin', 'Id tipo plan', 'Tiene telefonia fija', 'Tarifa telefonia fija', 'Cantidad minutos', 'Valor minuto inlcuido telefonia', 'Valor minuto adicional telefonia', 'Tiene internet fijo', 'Nombre Plan Int FI', 'Tarifa Mensual Internet', 'Velocidad Ofrecida Bajada', 'Velocidad Ofrecida Subida', 'Id Tecnologia', 'Canales Premium TV', 'Canales HD TV', 'Video Demanda', 'Costo Deco Adición', 'Otras Caracteristicas'];
        $objPHPExcel->getActiveSheet()->fromArray($encabezados, null, 'A1');

        // Obtener los datos de los contratos y almacenarlos en un array asociativo
        $datos = [];
        foreach ($contratos as $contrato) {
            $fila = [
                $contrato->created_at ?? '0',
                $request->trimestre ?? '0',
                $contrato->fk_idmunicipio ?? '0',
                $contrato->id_segmento_planes ?? '0',
                $contrato->cantidad_suscriptores ?? '0',
                $contrato->name ?? '0',
                $contrato->price ?? '0',
                $contrato->price ?? '0',
                $contrato->id_modalidad_plan ?? '0',
                $contrato->created_at ?? '0',
                $contrato->created_at ?? '0',
                $contrato->type ?? '0',
                $contrato->tiene_telefonia_fija ?? '0',
                $contrato->tarifa_telefonia_fija ?? '0',
                $contrato->cantidad_minutos ?? '0',
                $contrato->valor_minuto_incluido_telefonia ?? '0',
                $contrato->valor_minuto_adicional_telefonia ?? '0',
                $contrato->tiene_internet_fijo ?? '0',
                $contrato->nombre_plan_int_fi ?? '0',
                $contrato->tarifa_mensual_internet ?? '0',
                $contrato->burst_limit_bajada ?? '0',
                $contrato->burst_limit_subida ?? '0',
                $contrato->id_tecnologia ?? '0',
                $contrato->canales_premium_tv ?? '0',
                $contrato->canales_hd_tv ?? '0',
                $contrato->video_demanda ?? '0',
                $contrato->costo_deco_adicion ?? '0',
                $contrato->otras_caracteristicas ?? '0',
            ];
            $datos[] = $fila;
        }

        // Agregar los datos de contratos al archivo Excel
        $objPHPExcel->getActiveSheet()->fromArray($datos, null, 'A2');

        // Crear el archivo Excel
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=Reporte_Contratos.xlsx");
        header("Cache-Control: max-age=0");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    public function exogena(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        DB::enableQueryLog();
        if ($request->nro == 'remisiones'){
            return $this->remisiones($request);
        }else{

            $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
            $cajas = Banco::where('estatus',1)->get();
            $cajasUsuario = auth()->user()->cuentas();

            if(Auth::user()->rol > 1 && auth()->user()->rol == 8){
                $cajas = Banco::whereIn('id', $cajasUsuario)->get();
            }

            view()->share(['seccion' => 'reportes', 'title' => 'Reporte de Exógena', 'icon' =>'fas fa-chart-line']);
            $campos=array( '','nombre', 'f.fecha', 'f.vencimiento', 'nro', 'nro', 'nro', 'nro');
            if (!$request->orderby) {
                $request->orderby=1; $request->order=1;
            }
            $orderby=$campos[$request->orderby];
            $order=$request->order==1?'DESC':'ASC';

            $contactos = Contacto::join('factura as f', 'f.cliente', '=', 'contactos.id')
            ->join('ingresos_factura as ig', 'f.id', '=', 'ig.factura')
            ->whereIn('f.tipo', [2])
            // ->where('contactos.id',1518)
            ->select('contactos.id as idContacto','contactos.nombre','contactos.apellido1','contactos.apellido2','contactos.nit',
            'contactos.tip_iden')
            ->selectRaw('SUM(ig.pago) as ingresosBrutos')
            ->groupBy('contactos.id')
            ;

            $dates = $this->setDateRequest($request);

            if($request->input('fechas') != 8 || (!$request->has('fechas'))){
                $contactos=$contactos->where('f.fecha','>=', $dates['inicio'])->where('f.fecha','<=', $dates['fin']);
            }

            $ides=array();
            $contactos=$contactos->OrderBy($orderby, $order)->get();

            // foreach ($facturas as $factura) {
            //     $ides[]=$factura->id;
            // }

            if($request->orderby == 4 || $request->orderby == 5  || $request->orderby == 6 || $request->orderby == 7 ){
                switch ($request->orderby){
                    case 4:
                        $contactos = $request->order  ? $contactos->sortBy('subtotal') : $contactos = $contactos->sortByDesc('subtotal');
                        break;
                    case 5:
                        $contactos = $request->order ? $contactos->sortBy('iva') : $contactos = $contactos->sortByDesc('iva');
                        break;
                    case 6:
                        $contactos = $request->order ? $contactos->sortBy('retenido') : $contactos = $contactos->sortByDesc('retenido');
                        break;
                    case 7:
                        $contactos = $request->order ? $contactos->sortBy('total') : $contactos = $contactos->sortByDesc('total');
                        break;
                }
            }
            $contactos = $this->paginate($contactos, 15, $request->page, $request);

            $subtotal=$total=0;
            // if ($ides) {
            //     $result=DB::table('items_factura')->whereIn('factura', $ides)->select(DB::raw("SUM((`cant`*`precio`)) as 'total', SUM((precio*(`desc`/100)*`cant`)+0)  as 'descuento', SUM((precio-(precio*(if(`desc`,`desc`,0)/100)))*(`impuesto`/100)*cant) as 'impuesto'  "))->first();
            //     $subtotal=$this->precision($result->total-$result->descuento);
            //     $total=$this->precision((float)$subtotal+$result->impuesto);
            // }

            $empresa = Empresa::Find(1);

            return view('reportes.exogena.index')->with(compact('contactos', 'request','empresa'));

        }

    }

    public function contratoPeriodo(Request $request){

        $this->getAllPermissions(Auth::user()->id);
        DB::statement("SET lc_time_names = 'es_ES'");

        view()->share(['seccion' => 'reportes', 'title' => 'Reporte Periodo Contratos', 'icon' =>'fas fa-chart-line']);
        $campos=array( 'nro','fac.id', 'f.fecha', 'f.vencimiento', 'nro', 'nro', 'nro', 'nro');

        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $contratos = Contrato::
        join('facturas_contratos', 'contracts.nro', '=', 'facturas_contratos.contrato_nro')
        ->leftJoin('factura as fac', function ($join) {
            $join->on('fac.id', '=', DB::raw('(SELECT factura_id FROM facturas_contratos WHERE facturas_contratos.contrato_nro = contracts.nro ORDER BY id DESC LIMIT 1)'));
        })
        ->select('contracts.*','fac.codigo','fac.id as factura_id',DB::raw("DATE_FORMAT(fac.fecha, '%M') as mes_factura"),
        DB::raw("DATE_FORMAT(fac.fecha, '%Y%m%d') as fecha_concatenada"))
        ->groupBy('contracts.id');
        // ->get();

        if($request->month && $request->year){
            $contratos=$contratos->whereMonth('fac.fecha','=', $request->month)->whereYear('fac.fecha','=', $request->year);
        }else{
            $request->month = date('m');
            $request->year = date('Y');
            $contratos=$contratos->whereMonth('fac.fecha','=', $request->month)->whereYear('fac.fecha','=', $request->year);
        }

        $contratos=$contratos->OrderBy($orderby, $order)->get();

        $contratos = $this->paginate($contratos, 15, $request->page, $request);

        return view('reportes.contratos.periodo')->with(compact('contratos', 'request'));
    }

}
