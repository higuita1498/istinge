<?php

namespace App\Http\Controllers;
use App\CamposExtra;
use App\Model\Ingresos\IngresosRetenciones;
use http\Url;
use Illuminate\Http\Request;
use App\Empresa; use App\Contacto; use App\TipoIdentificacion;
use App\Impuesto; use App\NumeracionFactura;
use App\TerminosPago; use App\Funcion; use App\Vendedor;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\FacturaRetencion;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\ProductosBodega;
use App\Model\Ingresos\Remision;
use App\Model\Ingresos\ItemsRemision;
use App\Model\Ingresos\IngresosFactura;
use Illuminate\Support\Facades\Hash;
use Session;
use Response;
use Carbon\Carbon;
use Validator; use Illuminate\Validation\Rule; use QrCode; use File;
use App\PromesaPago;
include_once(app_path() . '/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use DOMDocument;
use App\TipoEmpresa; use App\Categoria;
use App\Retencion;
use Auth; use Mail; use bcrypt; use DB;
use Barryvdh\DomPDF\Facade as PDF;
use App\Contrato;
use App\GrupoCorte;
use App\Mikrotik;
include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;
use App\Descuento;
use App\Campos;
use Config;
use App\ServidorCorreo;
use App\FormaPago;
use ZipArchive;
use App\Integracion;
use App\PucMovimiento; use App\Puc;
use App\Plantilla;

class FacturasController extends Controller{

    protected $url;

    public function __construct(){
        $this->middleware('auth');
        view()->share(['seccion' => 'facturas', 'title' => 'Factura de Venta', 'icon' =>'fas fa-plus', 'subseccion' => 'venta']);
    }

    public function indexold(Request $request){
        $this->getAllPermissions(Auth::user()->id);

        $busqueda=false;
        $campos=array('factura.nro', 'factura.id', 'nombrecliente', 'factura.fecha', 'factura.vencimiento', 'total', 'pagado', 'porpagar', 'factura.estatus','contrato.fecha_corte', 'factura.correo');
        if (!$request->orderby) {
          $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        $facturas=Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
        ->join('items_factura as if', 'factura.id', '=', 'if.factura')
        ->leftJoin('contracts as cs', 'c.id', '=', 'cs.client_id')
        ->leftJoin('vendedores as v', 'factura.vendedor', '=', 'v.id')
        ->select('factura.id', 'factura.correo', 'factura.codigo', 'factura.nro', DB::raw('c.nombre as nombrecliente'), DB::raw('c.email as emailcliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.vendedor','factura.emitida', DB::raw('v.nombre as nombrevendedor'),
          DB::raw('SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
          DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'),
          DB::raw('(SUM(
              (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+
              (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -
              ((Select SUM(pago) from ingresos_factura where factura=factura.id) +
              (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) -
              (Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id) )    as porpagar'))
        ->where('factura.empresa',Auth::user()->empresa)->
        where('tipo','!=',5)->
        //where('factura.fecha','>=', '2021-03-01')->
        where('lectura',1);

        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);

        /*
         * Codigo buscador total, en desarrollo
         */
        if($request->has('search')){
            $busqueda              = true;
            $search                = mb_strtolower($request->input('search'));
            $filter                = '';
            switch ($request->input('search')){
                case (preg_match('/-fven/', $search) ? true : false):
                    $filter        = "ven";
                    break;
                case (preg_match('/-fvto/', $search) ? true : false):
                    $filter        = "vto";
                    break;
                case (preg_match('/-fiva/', $search) ? true : false):
                    $filter        = "iva";
                    break;
                case (preg_match('/-fpgo/', $search) ? true : false):
                    $filter        = "pgo";
                    break;
                case (preg_match('/-fppr/', $search) ? true : false):
                    $filter        = "ppr";
                    break;
            }

            if($filter)
                $search            = str_replace(' -f'.$filter, '', $search);
            if(is_numeric($request->input('search'))){
                if($filter != ''){

                    // En construcción

                }else{
                    $facturas          = $facturas->havingRaw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+
                    (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) > ?',
                        [$search]);
                }

            }else{

                if (preg_match('/[A-Za-z]/', $search) && preg_match('/[0-9]/', $search)){
                    $facturas      = $facturas->where('factura.codigo', 'like', '%' .$search.'%');
                }else{
                    if (strcmp($search, 'abierta') == 0 || strcmp($search, 'cerrada') == 0 || strcmp($search, 'anulada') == 0){
                        $facturas  = $facturas->whereIn('factura.estatus', $search);
                    }elseif (date('d-m-Y', strtotime($search)) == $search){

                        if(preg_match('/-vto/i', $search)){
                            dd("d");
                            $facturas  = $facturas->where('factura.vencimiento', date('Y-m-d', strtotime($search)));
                        }else{
                            $facturas  = $facturas->where('factura.fecha', date('Y-m-d', strtotime($search)));
                        }

                    }
                    else{
                        $facturas  = $facturas->where('c.nombre', 'like', '%' .$search.'%');
                    }
                }

            }
        }
        /*
         *
         */

        if ($request->name_1) {
          $busqueda=true; $appends['name_1']=$request->name_1; $facturas=$facturas->where('factura.codigo', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
          $busqueda=true; $appends['name_2']=$request->name_2; $facturas=$facturas->where('c.nombre', 'like', '%' .$request->name_2.'%');
        }
        if ($request->name_3) {
          $busqueda=true; $appends['name_3']=$request->name_3; $facturas=$facturas->where('factura.fecha', date('Y-m-d', strtotime($request->name_3)));
        }
        if ($request->name_4) {
          $busqueda=true; $appends['name_4']=$request->name_4; $facturas=$facturas->where('factura.vencimiento', date('Y-m-d', strtotime($request->name_4)));
        }
        if ($request->name_8) {
          $busqueda=true; $appends['name_8']=$request->name_8; $facturas=$facturas->whereIn('factura.estatus', $request->name_8);
        }

        if ($request->name_6) {
          $busqueda=true; $appends['name_6']=$request->name_6; $appends['name_6_simb']=$request->name_6_simb; $facturas=$facturas->whereRaw(DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) '.$request->name_6_simb.' ?'), [$request->name_6]);
        }


        if ($request->name_9) {
          $busqueda=true; $appends['name_9']=$request->name_9; $facturas=$facturas->where('v.nombre', 'like', '%' .$request->name_9.'%');
        }


          if ($request->name_7) {
              $tmpFacturas = $facturas->groupBy('if.factura');
              $tmpFacturas = $tmpFacturas->get();
              foreach ($tmpFacturas as $tmpFactura){
                  if ($request->name_7_simb == '>'){
                      if($tmpFactura->porpagar() > $request->name_7){
                          $tmpArry[] = $tmpFactura->id;
                      }
                  }elseif($request->name_7_simb == '<'){
                      if($tmpFactura->porpagar() < $request->name_7){
                          $tmpArry[] = $tmpFactura->id;
                      }
                  }else{
                      if($tmpFactura->porpagar() == $request->name_7){
                          $tmpArry[] = $tmpFactura->id;
                      }
                  }
              }
              $facturas = $facturas->whereIn('factura.id', $tmpArry);

              $appends['name_7']=$request->name_7;
              $appends['name_7_simb']=$request->name_7_simb;

              $busqueda=true;
          }

        if ($request->name_10) {
          $busqueda = true; $appends['name_10'] = $request->name_10; $facturas = $facturas->where('cs.fecha_corte', $request->name_10);
        }

        if ($request->name_11) {
          $busqueda=true; $appends['name_11']=$request->name_11; $facturas=$facturas->where('c.nit', 'like', '%' .$request->name_11.'%');
        }

        if ($request->name_12) {
          $busqueda=true; $appends['name_12']=$request->name_12; $facturas=$facturas->where('c.direccion', 'like', '%' .$request->name_12.'%');
        }

        if ($request->name_13) {
          $busqueda = true; $appends['name_13'] = $request->name_13; $facturas = $facturas->where('cs.server_configuration_id', $request->name_13);
        }

        if ($request->name_14) {
          $busqueda = true; $appends['name_14'] = $request->name_14; $facturas = $facturas->where('cs.ip', $request->name_14);
        }

        if ($request->name_15) {
          $busqueda = true; $appends['name_15'] = $request->name_15; $facturas = $facturas->where('cs.mac_address', $request->name_15);
        }

        $facturas=$facturas->groupBy('if.factura');


        if ($request->name_5) {
          $busqueda=true;
          $appends['name_5']=$request->name_5;
          $appends['name_5_simb']=$request->name_5_simb;
          $facturas=$facturas->havingRaw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+
          (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) )'
              .$request->name_5_simb.' ?', [$request->name_5]);
        }

        if ($busqueda==false) {
          $facturas=$facturas->where('factura.estatus', 1);
        }

        if(Auth::user()->id == 29){
            $facturas=$facturas->where('factura.estatus', 1);
        }

        $facturas=$facturas->OrderBy($orderby, $order)->paginate(15)->appends($appends);

        $clientes = Contacto::join('factura AS F','F.cliente','=','contactos.id')->select('contactos.id', 'contactos.nombre', 'contactos.nit')->groupBy('F.cliente')->orderBy('contactos.nombre','ASC')->get();

        view()->share(['title' => 'Facturas de Venta', 'subseccion' => 'venta']);
        return view('facturas.index')->with(compact('facturas', 'request', 'busqueda','clientes'));
    }
  
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $empresaActual = auth()->user()->empresa;

        $clientes = Contacto::join('factura as f', 'contactos.id', '=', 'f.cliente')->where('contactos.status', 1)->groupBy('f.cliente')->select('contactos.*')->orderBy('contactos.nombre','asc')->get();

        view()->share(['title' => 'Facturas de Venta', 'subseccion' => 'venta', 'precice' => true]);
        $tipo = false;
        $tabla = Campos::where('modulo', 4)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
        $municipios = DB::table('municipios')->orderBy('nombre', 'asc')->get();

        return view('facturas.indexnew', compact('clientes','tipo','tabla','municipios'));
    }

    public function indexNew(Request $request, $tipo){
        $this->getAllPermissions(Auth::user()->id);
        $empresaActual = auth()->user()->empresa;

        $clientes = Contacto::join('factura as f', 'contactos.id', '=', 'f.cliente')->where('contactos.status', 1)->groupBy('f.cliente')->select('contactos.*')->orderBy('contactos.nombre','asc')->get();
        $municipios = DB::table('municipios')->orderBy('nombre', 'asc')->get();

        view()->share(['title' => 'Facturas de Venta', 'subseccion' => 'venta', 'precice' => true]);
        $tipo = ($tipo == 'cerradas') ? 'A' : 1;
        $tabla = Campos::where('modulo', 4)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();

        return view('facturas.indexnew', compact('clientes','tipo','tabla','municipios'));
    }

    /*
    * Tabla principal de facturación electrónica.
    */
    public function index_electronica(){
        $this->getAllPermissions(Auth::user()->id);
        $empresaActual = auth()->user()->empresa;

        $clientes = Contacto::join('factura as f', 'contactos.id', '=', 'f.cliente')->where('contactos.status', 1)->groupBy('f.cliente')->select('contactos.*')->orderBy('contactos.nombre','asc')->get();
        $municipios = DB::table('municipios')->orderBy('nombre', 'asc')->get();

        view()->share(['title' => 'Facturas de Venta Electrónica', 'subseccion' => 'venta-electronica']);
        return view('facturas-electronica.index', compact('clientes', 'municipios'));
    }

    /*
    * Método que obtiene una colección de facturas por medio de oracle Datatable.
    */
    public function facturas_electronica(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $identificadorEmpresa = auth()->user()->empresa;
        $moneda = auth()->user()->empresa()->moneda;

        $facturas = Factura::query()
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->leftJoin('contracts as cs', 'c.id', '=', 'cs.client_id')
            ->leftJoin('vendedores as v', 'factura.vendedor', '=', 'v.id')
            ->select('factura.tipo','factura.promesa_pago','factura.id', 'factura.correo', 'factura.mensaje', 'factura.codigo', 'factura.nro', DB::raw('c.nombre as nombrecliente'), DB::raw('c.apellido1 as ape1cliente'), DB::raw('c.apellido2 as ape2cliente'), DB::raw('c.email as emailcliente'), DB::raw('c.celular as celularcliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.vendedor','factura.emitida', DB::raw('v.nombre as nombrevendedor'),DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'), DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'),         DB::raw('(SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant) + (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) - ((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) - (Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id)) as porpagar'))
            ->groupBy('factura.id');

        if ($request->filtro == true) {

            if($request->codigo){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.codigo', 'like', "%{$request->codigo}%");
                });
            }
            if($request->cliente){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.cliente', $request->cliente);
                });
            }
            if($request->corte){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('cs.fecha_corte', $request->corte);
                });
            }
            if($request->creacion){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.fecha', $request->creacion);
                });
            }
            if($request->vencimiento){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.vencimiento', $request->vencimiento);
                });
            }
            if($request->estado){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.estatus', $request->estado);
                });
            }
            if($request->correo){
                $correo = ($request->correo == 'A') ? 0 : $request->correo; 
                $facturas->where(function ($query) use ($request, $correo) {
                    $query->orWhere('factura.correo', $correo);
                });
            }
            if($request->municipio){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('c.fk_idmunicipio', $request->municipio);
                });
            }
            if($request->emision){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.emitida', $request->emision);
                });
            }
        }
        
        // if(auth()->user()->rol == 8){
        //     $facturas=$facturas->where('factura.estatus', 1);
        // }

        $facturas->where('factura.empresa', $identificadorEmpresa);
        $facturas->where('factura.tipo', 2)->where('factura.lectura',1);

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $facturas->where('cs.oficina', auth()->user()->oficina);
            }
        }

        return datatables()->eloquent($facturas)
        ->editColumn('codigo', function (Factura $factura) {
            return $factura->id ? "<a href=" . route('facturas.show', $factura->id) . ">$factura->codigo</a>" : "";
        })
        ->editColumn('cliente', function (Factura $factura) {
            return  $factura->cliente ? "<a href=" . route('contactos.show', $factura->cliente) . ">{$factura->nombrecliente} {$factura->ape1cliente} {$factura->ape2cliente}</a>" : "";
        })
        ->editColumn('fecha', function (Factura $factura) {
            return date('d-m-Y', strtotime($factura->fecha));
        })
        ->editColumn('vencimiento', function (Factura $factura) {
            return (date('Y-m-d') > $factura->vencimiento && $factura->estatus == 1) ? '<span class="text-danger">' . date('d-m-Y', strtotime($factura->vencimiento)) . '</span>' : date('d-m-Y', strtotime($factura->vencimiento));
        })
        ->addColumn('total', function (Factura $factura) use ($moneda) {
            return "{$moneda} {$factura->parsear($factura->total()->total)}";
        })
        ->addColumn('impuesto', function (Factura $factura) use ($moneda) {
            return "{$moneda} {$factura->parsear($factura->impuestos_totales())}";
        })
        ->addColumn('pagado', function (Factura $factura) use ($moneda) {
            return "{$moneda} {$factura->parsear($factura->pagado)}";
        })
        ->addColumn('pendiente', function (Factura $factura) use ($moneda) {
            return "{$moneda} {$factura->parsear($factura->porpagar)}";
        })
        ->addColumn('estado', function (Factura $factura) {
            return   '<span class="text-' . $factura->estatus(true) . '">' . $factura->estatus(). '</span>';
        })
        ->addColumn('acciones', $modoLectura ?  "" : "facturas.acciones-facturas")
        ->rawColumns(['codigo', 'cliente', 'estado', 'acciones', 'vencimiento'])
        ->toJson();
    }

    public function facturas(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $identificadorEmpresa = auth()->user()->empresa;
        $moneda = auth()->user()->empresa()->moneda;

        $facturas = Factura::query()
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->leftJoin('contracts as cs', 'c.id', '=', 'cs.client_id')
            ->leftJoin('vendedores as v', 'factura.vendedor', '=', 'v.id')
            ->select('factura.tipo','factura.promesa_pago','factura.id', 'factura.correo', 'factura.mensaje', 'factura.codigo', 'factura.nro', DB::raw('c.nombre as nombrecliente'), DB::raw('c.apellido1 as ape1cliente'), DB::raw('c.apellido2 as ape2cliente'), DB::raw('c.email as emailcliente'), DB::raw('c.celular as celularcliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.vendedor','factura.emitida', DB::raw('v.nombre as nombrevendedor'),DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'), DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'),         DB::raw('(SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant) + (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) - ((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) - (Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id)) as porpagar'))
            ->groupBy('factura.id');

        if ($request->filtro == true) {
            if($request->codigo){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.codigo', 'like', "%{$request->codigo}%");
                });
            }
            if($request->cliente){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.cliente', $request->cliente);
                });
            }
            if($request->corte){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('cs.fecha_corte', $request->corte);
                });
            }
            if($request->creacion){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.fecha', $request->creacion);
                });
            }
            if($request->vencimiento){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.vencimiento', $request->vencimiento);
                });
            }
            if($request->estado){
                $status = ($request->estado == 'A') ? 0 : $request->estado; 
                $facturas->where(function ($query) use ($request, $status) {
                    $query->orWhere('factura.estatus', $status);
                });
            }else{
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('factura.estatus', 1);
                });
            }
            if($request->correo){
                $correo = ($request->correo == 'A') ? 0 : $request->correo; 
                $facturas->where(function ($query) use ($request, $correo) {
                    $query->orWhere('factura.correo', $correo);
                });
            }
            if($request->municipio){
                $facturas->where(function ($query) use ($request) {
                    $query->orWhere('c.fk_idmunicipio', $request->municipio);
                });
            }
        }
        
        if(auth()->user()->rol == 8){
            $facturas=$facturas->where('factura.estatus', 1);
        }

        $facturas->where('factura.empresa', $identificadorEmpresa);
        $facturas->where('factura.tipo', '!=', 2)->where('factura.tipo', '!=', 5)->where('factura.tipo', '!=', 6)
                 ->where('factura.lectura',1);

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $facturas->where('cs.oficina', auth()->user()->oficina);
            }
        }

        return datatables()->eloquent($facturas)
        ->editColumn('codigo', function (Factura $factura) {
            return $factura->id ? "<a href=" . route('facturas.show', $factura->id) . ">$factura->codigo</a>" : "";
        })
        ->editColumn('cliente', function (Factura $factura) {
            return  $factura->cliente ? "<a href=" . route('contactos.show', $factura->cliente) . ">{$factura->nombrecliente} {$factura->ape1cliente} {$factura->ape2cliente}</a>" : "";
        })
        ->editColumn('fecha', function (Factura $factura) {
            return date('d-m-Y', strtotime($factura->fecha));
        })
        ->editColumn('vencimiento', function (Factura $factura) {
            return (date('Y-m-d') > $factura->vencimiento && $factura->estatus == 1) ? '<span class="text-danger">' . date('d-m-Y', strtotime($factura->vencimiento)) . '</span>' : date('d-m-Y', strtotime($factura->vencimiento));
        })
        ->addColumn('total', function (Factura $factura) use ($moneda) {
            return "{$moneda} {$factura->parsear($factura->total()->total)}";
        })
        ->addColumn('impuesto', function (Factura $factura) use ($moneda) {
            return "{$moneda} {$factura->parsear($factura->impuestos_totales())}";
        })
        ->addColumn('pagado', function (Factura $factura) use ($moneda) {
            return "{$moneda} {$factura->parsear($factura->pagado)}";
        })
        ->addColumn('pendiente', function (Factura $factura) use ($moneda) {
            return "{$moneda} {$factura->parsear($factura->porpagar)}";
        })
        ->addColumn('estado', function (Factura $factura) {
            return   '<span class="text-' . $factura->estatus(true) . '">' . $factura->estatus() . '</span>';
        })
        ->addColumn('acciones', $modoLectura ?  "" : "facturas.acciones-facturas")
        ->rawColumns(['codigo', 'cliente', 'estado', 'acciones', 'vencimiento'])
        ->toJson();
    }

    public function create($producto=false, $cliente=false){
        $this->getAllPermissions(Auth::user()->id);
        //echo $cliente;die;
        $nro=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('preferida',1)->where('estado',1)->where('tipo',1)->first();

        $tipo_documento = Factura::where('empresa',Auth::user()->empresa)->latest('tipo')->first();

        //obtiene las formas de pago relacionadas con este modulo (Facturas)
        $relaciones = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();
        
        if (!$nro) {
            $mensaje='Debes crear una numeración para facturas de venta preferida';
            return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
        }
        if ($nro->inicio==$nro->final) {
            $nro->estado=0;
            $nro->save();
            $mensaje='Debes crear una numeración para facturas de venta preferida';
            return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
        }
        if ($nro->hasta) {
            if ($nro->hasta<date('Y-m-d')) {
                $nro->estado=0;
                $nro->save();
                $mensaje='Debes crear una numeración para facturas de venta preferida';
                return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
            }
        }
        //se obtiene la fecha de hoy
        $fecha = date('d-m-Y');

        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        $inventario = Inventario::select('inventario.id','inventario.tipo_producto','inventario.producto','inventario.ref',
        DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
        ->where('empresa',Auth::user()->empresa)
        ->where('status', 1)
        ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
        ->orderBy('producto','ASC')
        ->get();
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        //$clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->where('status',1)->orderBy('nombre','asc')->get();
        $clientes = (Auth::user()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();

        //Datos necesarios para hacer funcionar la ventana modal
        $dataPro = (new InventarioController)->create();
        $medidas2 = $dataPro->medidas;
        $unidades2 = $dataPro->unidades;
        $extras2 = $dataPro->extras;
        $listas2 = $dataPro->listas;
        $bodegas2 = $dataPro->bodegas;
        $categorias = Puc::where('empresa',auth()->user()->empresa)
         ->whereRaw('length(codigo) > 6')
         ->get();
        $identificaciones=TipoIdentificacion::all();
        //$vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
        //$listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        // /Datos necesarios para hacer funcionar la ventana modal
        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
        view()->share(['icon' =>'', 'title' => 'Nueva Facturas de Venta', 'subseccion' => 'venta']);

        $title = "Nueva Factura de Venta";
        $seccion = "facturas";
        $subseccion = "venta";

        return view('facturas.create')->with(compact('clientes', 'tipo_documento',
            'inventario', 'numeraciones', 'nro','vendedores', 'terminos', 'impuestos',
            'cliente', 'bodegas', 'listas', 'producto', 'fecha', 'retenciones',
            'categorias', 'identificaciones', 'tipos_empresa', 'prefijos', 'medidas2',
            'unidades2', 'extras2', 'listas2','bodegas2','title','seccion','subseccion',
            'extras','relaciones'));
    }

    public function create_electronica($producto=false, $cliente=false){
        $this->getAllPermissions(Auth::user()->id);
        //echo $cliente;die;
        $nro=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('preferida',1)->where('estado',1)->where('tipo',2)->first();
        $tipo_documento = Factura::where('empresa',Auth::user()->empresa)->latest('tipo')->first();

        //obtiene las formas de pago relacionadas con este modulo (Facturas)
        $relaciones = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();

        if (!$nro) {
            $mensaje='Debes crear una numeración para facturas de venta preferida';
            return redirect('empresa/configuracion/numeraciones/dian')->with('error', $mensaje);
        }
        if ($nro->inicio==$nro->final) {
            $nro->estado=0;
            $nro->save();
            $mensaje='Debes crear una numeración para facturas de venta preferida';
            return redirect('empresa/configuracion/numeraciones/dian')->with('error', $mensaje);
        }
        if ($nro->hasta) {
            if ($nro->hasta<date('Y-m-d')) {
                $nro->estado=0;
                $nro->save();
                $mensaje='Debes crear una numeración para facturas de venta preferida';
                return redirect('empresa/configuracion/numeraciones/dian')->with('error', $mensaje);
            }
        }
        //se obtiene la fecha de hoy
        $fecha = date('d-m-Y');

        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        $inventario = Inventario::select('inventario.id','inventario.tipo_producto','inventario.producto','inventario.ref',
            DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
            ->where('empresa',Auth::user()->empresa)
            ->where('status', 1)
            ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
            ->orderBy('producto','ASC')
            ->get();

        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        //$clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->where('status',1)->orderBy('nombre','asc')->get();
        $clientes = (Auth::user()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();

        //Datos necesarios para hacer funcionar la ventana modal
        $dataPro = (new InventarioController)->create();
        $medidas2 = $dataPro->medidas;
        $unidades2 = $dataPro->unidades;
        $extras2 = $dataPro->extras;
        $listas2 = $dataPro->listas;
        $bodegas2 = $dataPro->bodegas;
        $categorias=Categoria::where('empresa',Auth::user()->empresa)->orWhere('empresa', 1)->whereNull('asociado')->get();
        $identificaciones=TipoIdentificacion::all();
        //$vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
        //$listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        // /Datos necesarios para hacer funcionar la ventana modal

        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
        view()->share(['icon' =>'', 'title' => 'Nueva Factura Electrónica', 'subseccion' => 'venta-electronica']);

        $title = "Nueva Factura Electrónica";
        $seccion = "facturas";
        $subseccion = "venta-electronica";

        return view('facturas-electronica.create')->with(compact('clientes', 'tipo_documento', 'inventario', 'numeraciones', 'nro','vendedores', 'terminos', 'impuestos','cliente', 'bodegas', 'listas', 'producto', 'fecha', 'retenciones','categorias', 'identificaciones', 'tipos_empresa', 'prefijos', 'medidas2','unidades2', 'extras2', 'listas2','bodegas2','title','seccion','subseccion','extras','relaciones'));
    }

    public function create_cliente($cliente){
        return $this->create(false, $cliente);
    }

    public function create_item($item){
        $inventario =Inventario::where('id',$item)->where('empresa',Auth::user()->empresa)->first();
        if ($inventario) {
            return $this->create($inventario, false);
        }
        abort(404);
    }

    public function remisionAfactura($nroR,$producto=false, $cliente=false){
        $this->getAllPermissions(Auth::user()->id);
        $remision = Remision::where('remisiones.empresa',Auth::user()->empresa)->where('remisiones.nro',$nroR)->first();
        $itemsRemision = ItemsRemision::where('items_remision.remision', $remision->id)->get();
        $nro=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('preferida',1)->where('estado',1)->first();
        if (!$nro) {
            $mensaje='Debes crear una numeración para facturas de venta preferida';
            return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
        }
        if ($nro->inicio==$nro->final) {
            $nro->estado=0;
            $nro->save();
            $mensaje='Debes crear una numeración para facturas de venta preferida';
            return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
        }
        if ($nro->hasta) {
            if ($nro->hasta<date('Y-m-d')) {
                $nro->estado=0;
                $nro->save();
                $mensaje='Debes crear una numeración para facturas de venta preferida';
                return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
            }
        }

        //se obtiene la fecha de hoy
        $fecha = date('d-m-Y');

        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
        $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();
        $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();

        //Datos necesarios para hacer funcionar la ventana modal
        $dataPro = (new InventarioController)->create();
        $medidas2 = $dataPro->medidas;
        $unidades2 = $dataPro->unidades;
        $extras2 = $dataPro->extras;
        $listas2 = $dataPro->listas;
        $bodegas2 = $dataPro->bodegas;
        $categorias=Categoria::where('empresa',Auth::user()->empresa)->orWhere('empresa', 1)->whereNull('asociado')->get();
        $identificaciones=TipoIdentificacion::all();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        // /Datos necesarios para hacer funcionar la ventana modal

        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
        view()->share(['icon' =>'', 'title' => 'Nueva Facturas de Venta', 'subseccion' => 'venta']);
        return view('facturas.facturaRemision')->with(compact('clientes', 'inventario', 'numeraciones', 'nro','vendedores', 'terminos', 'impuestos', 'cliente', 'bodegas', 'listas', 'producto', 'fecha', 'retenciones','categorias', 'identificaciones', 'tipos_empresa', 'prefijos', 'medidas2', 'unidades2', 'extras2', 'listas2','bodegas2','remision','itemsRemision', 'extras'));
    }

  /**
  * Registrar una nueva factura
  * Si hay items inventariable resta los valores al inventario
  * @param Request $request
  * @return redirect
  */
    public function store(Request $request){
        $request->validate([
            'vendedor' => 'required',
        ]);

        $nro = false;
        $contrato = false;
        $num = Factura::where('empresa',1)->orderby('nro','asc')->get()->last();

        if(!isset($request->electronica)){
            $nro=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('preferida',1)->where('estado',1)->where('tipo',1)->first();
            $contrato =    Contrato::where('client_id',$request->cliente)->first();

            //Obtenemos el número depende del contrato que tenga asignado (con fact electrónica o estandar).
            $nro = $nro->tipoNumeracion($contrato);
        }

        //Por acá entra cuando quiero crear una factura electrónica sin que esté asociadaa un contrato
        if (!$nro) {
            if(isset($request->electronica)){
                //No se llama el metodo de tipoNumeracion por que las facturas electrónicas no necesitan de un contrato para ser generadas.
                $nro=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('preferida',1)->where('estado',1)->where('tipo',2)->first();
                if(!$nro){
                    $mensaje='Debes crear una numeración para facturas de venta preferida';
                    return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
                }
            }else{
                $mensaje='Debes crear una numeración para facturas de venta preferida';
                return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
            }
        }

        //Actualiza el nro de inicio para la numeracion seleccionada
        $inicio = $nro->inicio;
        $nro->inicio += 1;

        if($request->nro_remision){
            DB::table('remisiones')->where('nro', $request->nro_remision)->update(['estatus' => 3]);
        }

        //Generacion de llave unica para acceso por correo
        $key = Hash::make(date("H:i:s"));
        $toReplace = array('/', '$','.');
        $key = str_replace($toReplace, "", $key);
        //

        if($num){
            $numero = $num->nro + 1;
        }else{
            $numero = 1;
        }

        $tipo = 1; //1= normal, 2=Electrónica.

        // Retorna si un cliente puede crear factura electrónica o no.
        $electronica = Factura::booleanFacturaElectronica($request->cliente);

        if($contrato){
            if($contrato->facturacion == 3 && !$electronica){
                return redirect('empresa/facturas/facturas_electronica')->with('success', "La Factura Electrónica no pudo ser creada por que no ha pasado el tiempo suficiente desde la ultima factura");
            }elseif($contrato->facturacion == 3 && $electronica){
                $tipo = 2;
                $request->documento = $tipo;
            }
        }

        if(isset($request->electronica)){$tipo = 2;}

        //Si el tipo de documento es cuenta de cobro sigue su proceso normal.
        if($request->documento != 3){
            $request->documento = $tipo;
        }

        $factura = new Factura;
        $factura->nonkey = $key;
        $factura->nro = $numero;
        $factura->codigo=$nro->prefijo.$inicio;
        $factura->numeracion=$nro->id;
        $factura->plazo=$request->plazo;
        $factura->term_cond=$request->term_cond;
        $factura->facnotas=$request->notas;
        $factura->empresa=Auth::user()->empresa;
        $factura->cliente=$request->cliente;
        $factura->tipo=$tipo;
        $factura->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
        $factura->vencimiento=date('Y-m-d', strtotime("+".$request->plazo." days", strtotime($request->fecha)));
        $factura->suspension=date('Y-m-d', strtotime("+".$request->plazo." days", strtotime($request->fecha)));
        $factura->pago_oportuno = date('Y-m-d', strtotime("+".($request->plazo-1)." days", strtotime($request->fecha)));
        $factura->observaciones=mb_strtolower($request->observaciones);
        $factura->vendedor=$request->vendedor;
        $factura->lista_precios=$request->lista_precios;
        $factura->bodega=$request->bodega;
        $factura->nro_remision = $request->nro_remision;
        $factura->tipo_operacion = $request->tipo_operacion;
        $factura->ordencompra    = $request->ordencompra;
        $factura->cuenta_id    = $request->relacion;
        $factura->created_by = Auth::user()->id;

        if($contrato){
            $factura->contrato_id = $contrato->id;
        }else{
            /*
            Validamos aca de nuevo si tiene contrato o no, para que las facturas electronicas que tienen contrato
             tengan la posibilidad de que se les guarde el id del contrato en la factura.
             */
            $contrato = Contrato::where('client_id',$request->cliente)->first();
            if($contrato){
                $factura->contrato_id = $contrato->id;
            }
        }

        $factura->save();
        $nro->save();

        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
        if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        }
        //Ciclo para registrar los itemas de la factura
        for ($i=0; $i < count($request->ref) ; $i++) {
            $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
            if($impuesto){
                $impuesto->porcentaje = $impuesto->porcentaje;
            }else{
                $impuesto->porcentaje = '';
            }
            $producto = Inventario::where('id', $request->item[$i])->first();
            //Si el producto es inventariable y existe esa bodega, restará el valor registrado
            if ($producto->tipo_producto==1) {
                $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
                if ($ajuste) {
                    $ajuste->nro-=$request->cant[$i];
                    $ajuste->save();
                }
            }
            $items = new ItemsFactura;
            $items->factura=$factura->id;
            $items->producto=$request->item[$i];
            $items->ref=$request->ref[$i];
            $items->precio=$this->precision($request->precio[$i]);
            $items->descripcion=$request->descripcion[$i];
            $items->id_impuesto=$request->impuesto[$i];
            $items->impuesto=$impuesto->porcentaje;
            $items->cant=$request->cant[$i];
            $items->desc=$request->desc[$i];
            $items->save();
        }

        //Registrar retennciones
        if ($request->retencion) {
            foreach ($request->retencion as $key => $value) {
                if ($request->precio_reten[$key]) {
                    $retencion = Retencion::where('id', $request->retencion[$key])->first();
                    $reten = new FacturaRetencion;
                    $reten->factura=$factura->id;
                    $reten->valor=$this->precision($request->precio_reten[$key]);
                    $reten->retencion=$retencion->porcentaje;
                    $reten->id_retencion=$retencion->id;
                    $reten->save();
                }
            }
        }

        //Actualiza el nro de inicio para la numeracion seleccionada
        $cant=Factura::where('empresa',Auth::user()->empresa)->where('codigo','=',($nro->prefijo.$inicio))->count();
        if($cant==0){
            $nro->inicio-=1;
            $nro->save();
        }

        PucMovimiento::facturaVenta($factura,1, $request);

        //Creo la variable para el mensaje final, y la variable print (imprimir)
        $mensaje='Se ha creado satisfactoriamente la factura';
        $print=false;

        if($tipo == 2){
            $mensaje = 'Se ha creado correctamente la factura electrónica';
        }

        //Si se selecciono imprimir, para enviarla y que se abra la ventana emergente con el pdf
        if ($request->print) {
            $print=$factura->nro;
        }

        //Llamada a la funcion enviar en caso de que se haya seleccionado la opcion "Enviar por correo"
        if ($request->send) {
            $this->enviar($factura->nro, null, false);
        }

        //Se redirecciona a la vista Nuevo ingreso, si se selecciono la opcion "Agregar Pago"
        if ($request->pago) {
            return redirect('empresa/ingresos/create/'.$request->cliente.'/'.$factura->id)->with('print', $print)->with('success', $mensaje);
        }
        //Se redirecciona a la vista Nuevo Factura, si se selecciono la opcion "Crear una nueva"
        else if ($request->new) {
            return redirect('empresa/facturas/create')->with('success', $mensaje)->with('print', $print);
        }else if($tipo == 2){
            return redirect('empresa/facturas/facturas_electronica')->with('success', $mensaje)->with('print', $print)->with('codigo', $factura->id);
        }
        return redirect('empresa/facturas')->with('success', $mensaje)->with('print', $print)->with('codigo', $factura->id);
    }

  /**
  * Formulario para modificar los datos de una factura
  * @param int $id
  * @return view
  */
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $this->url = back()->getTargetUrl();
        $factura = Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $retencionesFacturas = FacturaRetencion::where('factura', $factura->id)->get();
        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();

        //obtiene las formas de pago relacionadas con este modulo (Facturas)
        $relaciones = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();
        $formasPago = PucMovimiento::where('documento_id',$factura->id)->where('tipo_comprobante',3)->where('enlace_a',4)->get();

        if ($factura) {
            if ($factura->estatus==1) {
                //Obtengo el objeto bodega
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $factura->bodega)->first();
                if (!$bodega) {
                    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
                }
                $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
                ->where('empresa',Auth::user()->empresa)
                ->where('status', 1)
                ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();

                $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
                $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
                $items = ItemsFactura::where('factura',$factura->id)->get();
                $clientes = (Auth::user()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
                $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
                $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
                $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
                $tipo_documento = Factura::where('empresa',Auth::user()->empresa)->latest('tipo')->first();

                $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();
                $medidas=DB::table('medidas')->get();
                $unidades=DB::table('unidades_medida')->get();
                $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();

                $identificaciones=TipoIdentificacion::all();
                $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
                $prefijos=DB::table('prefijos_telefonicos')->get();

                if($factura->tipo==1) {
                    view()->share(['icon' =>'', 'title' => 'Modificar Factura de Venta '.$factura->codigo, 'subseccion' => 'venta']);
                }elseif($factura->tipo==2){
                    view()->share(['icon' =>'', 'title' => 'Modificar Factura Electrónica '.$factura->codigo, 'subseccion' => 'venta-electronica']);
                }else{
                    view()->share(['title' => 'Cuenta de Cobro '.$factura->codigo]);
                }

                return view('facturas.edit')->with(compact('clientes', 'inventario', 'vendedores', 'terminos', 'impuestos', 'factura', 'items', 'listas', 'bodegas', 'retencionesFacturas', 'retenciones', 'tipo_documento', 'categorias', 'medidas', 'unidades', 'prefijos', 'tipos_empresa', 'identificaciones', 'extras','relaciones','formasPago'));
            }
            return redirect('empresa/facturas')->with('success', 'La factura de venta '.$factura->codigo.' ya esta cerrada');
        }
        return redirect('empresa/facturas')->with('success', 'No existe un registro con ese id');
    }

  /**
  * Modificar los datos de la factura
  * @param Request $request
  * @return redirect
  */
    public function update(Request $request, $id){
        $desc=0;
        $factura =Factura::find($id);
        if ($factura) {
            if ($factura->estatus==1) {
                //se devolveran todos los items al inventario
                // Asi evitar que no exista la posibilidad de error en el momento de restar los items abajo
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $factura->bodega)->first();
                $items = ItemsFactura::join('inventario as inv', 'inv.id', '=', 'items_factura.producto')->select('items_factura.*')->where('items_factura.factura',$factura->id)->where('inv.tipo_producto', 1)->get();
                foreach ($items as $item) {
                    $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $item->producto)->first();
                    if ($ajuste) {
                        $ajuste->nro+=$item->cant;
                        $ajuste->save();
                    }
                }

                //Modificacion de los datos de la factura
                $factura->notas =$request->notas;
                $factura->cliente=$request->cliente;
                $factura->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
                $factura->vencimiento=Carbon::parse($request->vencimiento)->format('Y-m-d');
                $factura->observaciones=mb_strtolower($request->observaciones).' | Factura Editada por: '.Auth::user()->nombres.' el '.date('d-m-Y g:i:s A');
                $factura->vendedor=$request->vendedor;
                $factura->lista_precios=$request->lista_precios;
                $factura->bodega=$request->bodega;
                $factura->plazo=$request->plazo;
                $factura->term_cond=$request->term_cond;
                $factura->facnotas=$request->notas;
                $factura->tipo_operacion = $request->tipo_operacion;
                $factura->ordencompra    = $request->ordencompra;
                $factura->cuenta_id    = $request->relacion;
                $factura->save();

                $inner=array();
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
                if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
                    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
                }
                //Ciclo para registrar y/o modificar los itemas de la factura
                for ($i=0; $i < count($request->ref) ; $i++) {
                    $cat='id_item'.($i+1);
                    if($request->$cat){
                        $items = ItemsFactura::where('id', $request->$cat)->first();
                    }else{
                        $items = new ItemsFactura;
                    }
                    $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
                    $producto = Inventario::where('id', $request->item[$i])->first();
                    //Si el producto es inventariable y existe esa bodega, restará el valor registrado
                    if ($producto->tipo_producto==1) {
                        $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
                        if ($ajuste) {
                            $ajuste->nro-=$request->cant[$i];
                            $ajuste->save();
                        }
                    }
                    $items->factura=$factura->id;
                    $items->producto=$request->item[$i];
                    $items->ref=$request->ref[$i];
                    $items->precio=$this->precision($request->precio[$i]);
                    $items->descripcion=$request->descripcion[$i];
                    $items->id_impuesto=$request->impuesto[$i];
                    $items->impuesto=$impuesto->porcentaje;
                    $items->cant=$request->cant[$i];
                    //$items->desc=$request->desc[$i];
                    $desc=$request->desc[$i];
                    $items->save();
                    $inner[]=$items->id;
                }
                DB::table('factura_retenciones')->where('factura', $factura->id)->delete();
                //Registrar retennciones
                if ($request->retencion) {
                    foreach ($request->retencion as $key => $value) {
                        if ($request->precio_reten[$key]) {
                            $retencion = Retencion::where('id', $request->retencion[$key])->first();
                            $reten = new FacturaRetencion;
                            $reten->factura=$factura->id;
                            $reten->valor=$this->precision($request->precio_reten[$key]);
                            $reten->retencion=$retencion->porcentaje;
                            $reten->id_retencion=$retencion->id;
                            $reten->save();
                        }
                    }
                }

                if (count($inner)>0) {
                    DB::table('items_factura')->where('factura', $factura->id)->whereNotIn('id', $inner)->delete();
                }

                if($desc > 0){
                    $descuento = new Descuento;
                    $descuento->factura    = $items->factura;
                    $descuento->descuento  = $desc;
                    $descuento->created_by = Auth::user()->id;
                    $descuento->save();
                }

                PucMovimiento::facturaVenta($factura,2,$request);
                $mensaje='Se ha modificado satisfactoriamente la factura';
                return redirect($request->page)->with('success', $mensaje)->with('codigo', $factura->id);
            }
            return redirect('empresa/facturas')->with('success', 'La factura de venta '.$factura->codigo.' ya esta cerrada');
        }
        return redirect('empresa/facturas')->with('success', 'No existe un registro con ese id');
    }

  /**
  * Ver los datos de una factura
  * @param int $id
  * @return view
  */
    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $factura = Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $retenciones = FacturaRetencion::where('factura', $factura->id)->get();

        $limitDate   = (Carbon::parse($factura->created_at))->addDay();
        $actualDate  = Carbon::now();
        $wait        = (( $limitDate->greaterThanOrEqualTo($actualDate) && $factura->modificado == 0)? false: true);
        $mody        = $factura->modificado == 1 ? true : false;

        if($mody){
            $realStatus = $mody;
        }elseif ($wait){
            $realStatus = $wait;
        }else{
            $realStatus = false;
        }
        if ($factura) {
            if($factura->tipo == 1){
                view()->share(['title' => 'Facturas de Venta '.$factura->codigo]);
            }elseif($factura->tipo == 2){
                view()->share(['title' => 'Factura Electrónica '.$factura->codigo, 'subseccion' => 'venta-electronica']);
            }else{
                view()->share(['title' => 'Cuenta de Cobro '.$factura->codigo]);
            }
            $items = ItemsFactura::where('factura',$factura->id)->get();
            return view('facturas.show')->with(compact('factura', 'items', 'retenciones', 'realStatus'));
        }
        return redirect('empresa/facturas')->with('success', 'No existe un registro con ese id');
    }

    public function showMovimiento($id){
        $this->getAllPermissions(Auth::user()->id);
        $factura = Factura::find($id);
        /*
        obtenemos los movimiento sque ha tenido este documento
        sabemos que se trata de un tipo de movimiento 03
        */
        $movimientos = PucMovimiento::where('documento_id',$id)->where('tipo_comprobante',3)->get();
        if(count($movimientos) == 0){
            return back()->with('error', 'La factura: ' . $factura->codigo . " no tiene un asiento contable.");
        }
        if ($factura) {
            view()->share(['title' => 'Detalle Movimiento ' .$factura->codigo]);
            $items = ItemsFactura::where('factura',$factura->id)->get();
            return view('facturas.show-movimiento')->with(compact('factura','movimientos'));
        }
    }

    public function copia($id){
        return $this->pdf($id, 'copia');
    }

    public function pdf($id, $tipo='original'){
        $tipo1=$tipo;
        /**
         * * toma en cuenta que para ver los mismos
         * * datos debemos hacer la misma consulta
         * **/

        $factura = Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $resolucion = NumeracionFactura::where('empresa',Auth::user()->empresa)->latest()->first();

        if($factura->tipo == 1){
            view()->share(['title' => 'Descargar Factura']);
            if ($tipo<>'original') {
                $tipo='Copia Factura de Venta';
            }else{
                $tipo='Factura de Venta Original';
            }
        }elseif($factura->tipo == 3){
            view()->share(['title' => 'Descargar Cuenta de Cobro']);
            if ($tipo<>'original') {
                $tipo='Cuenta de Cobro Copia';
            }else{
                $tipo='Cuenta de Cobro Original';
            }
        }

        if ($factura) {
            $items = ItemsFactura::where('factura',$factura->id)->get();
            $itemscount=ItemsFactura::where('factura',$factura->id)->count();
            $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
            //return view('pdf.factura')->with(compact('items', 'factura', 'itemscount', 'tipo'));
            $pdf = PDF::loadView('pdf.electronica', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion'));
            return $pdf->download('factura-'.$factura->codigo.($tipo<>'original'?'-copia':'').'.pdf');
        }
    }

    public function Imprimircopia($id){
        return $this->Imprimir($id, 'copia');
    }

    public function Imprimir($id, $tipo='original', $especialFe = false){
        $tipo1=$tipo;

        /**
         * * toma en cuenta que para ver los mismos
         * * datos debemos hacer la misma consulta
         * **/

        $factura = ($especialFe) ? Factura::where('nonkey', $id)->first()
        : Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();

        if($factura->tipo == 1){
            view()->share(['title' => 'Imprimir Factura']);
            if ($tipo<>'original') {
                $tipo='Copia Factura de Venta';
            }else{
                $tipo='Factura de Venta Original';
            }
        }elseif($factura->tipo == 3){
            view()->share(['title' => 'Imprimir Cuenta de Cobro']);
            if ($tipo<>'original') {
                $tipo='Cuenta de Cobro Copia';
            }else{
                $tipo='Cuenta de Cobro Original';
            }
        }

        $resolucion = ($especialFe) ? NumeracionFactura::where('empresa', $factura->empresa)->latest()->first()
        : NumeracionFactura::where('empresa',Auth::user()->empresa)->latest()->first();

        if ($factura) {
            $items = ItemsFactura::where('factura',$factura->id)->get();
            $itemscount=ItemsFactura::where('factura',$factura->id)->count();
            $retenciones = FacturaRetencion::where('factura', $factura->id)->get();

            if($factura->emitida == 1){
                $impTotal = 0;
                foreach ($factura->total()->imp as $totalImp){
                    if(isset($totalImp->total)){
                        $impTotal = $totalImp->total;
                    }
                }

                $CUFEvr = $factura->info_cufe($factura->id, $impTotal);
                $infoEmpresa = Empresa::find(Auth::user()->empresa);
                $data['Empresa'] = $infoEmpresa->toArray();
                $infoCliente = Contacto::find($factura->cliente);
                $data['Cliente'] = $infoCliente->toArray();
                /*..............................
                Construcción del código qr a la factura
                ................................*/
                $impuesto = 0;
                foreach ($factura->total()->imp as $key => $imp) {
                    if(isset($imp->total)){
                        $impuesto = $imp->total;
                    }
                }

                $codqr = "NumFac:" . $factura->codigo . "\n" .
                "NitFac:"  . $data['Empresa']['nit']   . "\n" .
                "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
                "FecFac:" . Carbon::parse($factura->created_at)->format('Y-m-d') .  "\n" .
                "HoraFactura" . Carbon::parse($factura->created_at)->format('H:i:s').'-05:00' . "\n" .
                "ValorFactura:" .  number_format($factura->total()->subtotal, 2, '.', '') . "\n" .
                "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
                "ValorOtrosImpuestos:" .  0.00 . "\n" .
                "ValorTotalFactura:" .  number_format($factura->total()->subtotal + $factura->impuestos_totales(), 2, '.', '') . "\n" .
                "CUFE:" . $CUFEvr;
                /*..............................
                Construcción del código qr a la factura
                ................................*/

                $pdf = PDF::loadView('pdf.factura', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr','CUFEvr'));
            }else{
                $pdf = PDF::loadView('pdf.factura', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion'));
            }
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf']);
        }
    }

    public function imprimirFe($id){
        return $this->Imprimir($id, 'original', true);
    }

    public function imprimirTirilla($id, $tipo='original'){
        $tipo1=$tipo;

        /**
         * toma en cuenta que para ver los mismos
         * datos debemos hacer la misma consulta
         **/
        $factura = Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if($factura->tipo == 1){
            view()->share(['title' => 'Imprimir Factura']);
            if ($tipo<>'original') {
                $tipo='Copia Factura de Venta';
            }else{
                $tipo='Factura de Venta Original';
            }
        }elseif($factura->tipo == 3){
            view()->share(['title' => 'Imprimir Cuenta de Cobro']);
            if ($tipo<>'original') {
                $tipo='Cuenta de Cobro Copia';
            }else{
                $tipo='Cuenta de Cobro Original';
            }

        }
        $resolucion = NumeracionFactura::where('empresa',Auth::user()->empresa)->latest()->first();

        if ($factura) {
            $items = ItemsFactura::where('factura',$factura->id)->get();
            $itemscount=ItemsFactura::where('factura',$factura->id)->count();
            $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
            $ingreso = IngresosFactura::where('factura',$factura->id)->first();

            $paper_size = array(0,0,270,580);
            $pdf = PDF::loadView('pdf.plantillas.factura_tirilla', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','ingreso'));
            $pdf->setPaper($paper_size, 'portrait');
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
        }
    }

    public function enviarcopia($id){
        return $this->enviar($id, null, true, 'copia');
    }
    
    public function enviar($id, $emails=null, $redireccionar=true, $tipo='original'){
        if ($tipo==!'original') {
            $tipo='Copia factura de venta';
        }else{
            $tipo='Factura de venta original';
        }
        
        view()->share(['title' => 'Imprimir Factura']);
        $factura = Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($factura) {
            if (!$emails) {
                $emails=$factura->cliente()->email;
                if ($factura->cliente()->asociados('number')>0) {
                    $email=$emails;
                    $emails=array();
                    if ($email) {$emails[]=$email;}
                    foreach ($factura->cliente()->asociados() as $asociado) {
                        if ($asociado->notificacion==1 && $asociado->email) {
                            $emails[]=$asociado->email;
                        }
                    }
                }
            }

            if (!$emails) {
                return redirect('empresa/facturas/'.$factura->id)->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
            }

            $items = ItemsFactura::where('factura',$factura->id)->get();
            $itemscount=ItemsFactura::where('factura',$factura->id)->count();
            $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
            //return view('pdf.factura')->with(compact('items', 'factura', 'itemscount'));
            $resolucion = NumeracionFactura::where('empresa',Auth::user()->empresa)->latest()->first();
            $ingreso = IngresosFactura::where('factura',$factura->id)->first();
            //---------------------------------------------//
            if($factura->emitida == 1){
                $impTotal = 0;
                foreach ($factura->total()->imp as $totalImp){
                    if(isset($totalImp->total)){
                        $impTotal = $totalImp->total;
                    }
                }

                $CUFEvr = $factura->info_cufe($factura->id, $impTotal);
                $infoEmpresa = Empresa::find(Auth::user()->empresa);
                $data['Empresa'] = $infoEmpresa->toArray();
                $infoCliente = Contacto::find($factura->cliente);
                $data['Cliente'] = $infoCliente->toArray();
                /*..............................
                Construcción del código qr a la factura
                ................................*/
                $impuesto = 0;
                foreach ($factura->total()->imp as $key => $imp) {
                    if(isset($imp->total)){
                        $impuesto = $imp->total;
                    }
                }

                $codqr = "NumFac:" . $factura->codigo . "\n" .
                "NitFac:"  . $data['Empresa']['nit']   . "\n" .
                "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
                "FecFac:" . Carbon::parse($factura->created_at)->format('Y-m-d') .  "\n" .
                "HoraFactura" . Carbon::parse($factura->created_at)->format('H:i:s').'-05:00' . "\n" .
                "ValorFactura:" .  number_format($factura->total()->subtotal, 2, '.', '') . "\n" .
                "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
                "ValorOtrosImpuestos:" .  0.00 . "\n" .
                "ValorTotalFactura:" .  number_format($factura->total()->subtotal + $factura->impuestos_totales(), 2, '.', '') . "\n" .
                "CUFE:" . $CUFEvr;
                /*..............................
                Construcción del código qr a la factura
                ................................*/
                $pdf = PDF::loadView('pdf.electronica', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr','CUFEvr','ingreso'))->stream();
            }else{
                $pdf = PDF::loadView('pdf.electronica', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','ingreso'))->stream();
            }
            //-----------------------------------------------//

            $data = array(
                'email'=> 'info@istingenieria.online',
            );
            $total = Funcion::Parsear($factura->total()->total);
            $empresa = Empresa::find($factura->empresa);
            $key = Hash::make(date("H:i:s"));
            $toReplace = array('/', '$','.');
            $key = str_replace($toReplace, "", $key);
            $factura->nonkey = $key;
            $factura->save();
            $cliente = $factura->cliente()->nombre.' '.$factura->cliente()->apellidos();
            $tituloCorreo = Auth::user()->empresa()->nombre.": Factura N° $factura->codigo";
            $xmlPath = 'xml/empresa'.auth()->user()->empresa.'/FV/FV-'.$factura->codigo.'.xml';
            //return $xmlPath;

            $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
            if($host){
                $existing = config('mail');
                $new =array_merge(
                    $existing, [
                        'host' => $host->servidor,
                        'port' => $host->puerto,
                        'encryption' => $host->seguridad,
                        'username' => $host->usuario,
                        'password' => $host->password,
                        'from' => [
                            'address' => $host->address,
                            'name' => $host->name
                        ],
                    ]
                );
                config(['mail'=>$new]);
            }

            Mail::send('emails.email', compact('factura', 'total', 'cliente'), function($message) use ($pdf, $emails,$tituloCorreo,$xmlPath){
                $message->attachData($pdf, 'factura.pdf', ['mime' => 'application/pdf']);
                if(file_exists($xmlPath)){
                    $message->attach($xmlPath, ['as' => 'factura.xml', 'mime' => 'text/plain']);
                }
                $message->to($emails)->subject($tituloCorreo);
            });
        }
        //$factura->correo = 1;
        $factura->observaciones = ' | Factura Enviada por: '.Auth::user()->nombres.' el '.date('d-m-Y g:i:s A');
        $factura->save();
        if ($redireccionar) {
            return redirect('empresa/facturas/'.$factura->id)->with('success', 'Se ha enviado satisfactoriamente la factura por correo electrónico');
            //return back()->with('success', 'Se ha enviado satisfactoriamente la factura por correo electrónico');
        }
        return "Enviado";
    }

    public function cliente_factura_json($cliente, $cerradas=false){
        $facturas=Factura::where('empresa',Auth::user()->empresa);
        $facturas=$facturas->where('cliente', $cliente)->OrderBy('id', 'desc')->select('codigo', 'id')->get();
        return json_encode($facturas);
    }

    public function cliente_factura_json_all($cliente){
        $items=$this->cliente_factura_json($cliente);
        return array('cliente'=>Contacto::find($cliente), 'items'=>$items);
    }

    public function items_factura_json($id){
        $items = ItemsFactura::where('factura',$id)->get();
        foreach ($items as $key => $value) {
            $items[$key]->producto=$value->producto();
        }
        return json_encode($items);
    }

    public function factura_json($id){
        $factura = Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $array=array();
        if ($factura) {
            $array["fecha"]=date('d/m/Y', strtotime($factura->fecha));
            $array["vencimiento"]=date('d/m/Y', strtotime($factura->vencimiento));
            $array["observaciones"]=$factura->observaciones;
            $array["total"]=$factura->total()->total;
            $array["pagado"]=$factura->pagado();
            $array["porpagar"]=$factura->porpagar();
        }
        return json_encode($array);
    }

    public function anular($id){
        $factura = Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($factura) {
            if ($factura->estatus==1) {
                $factura->estatus=2;
                $factura->observaciones = $factura->observaciones.' | Factura Anulada por: '.Auth::user()->nombres.' el '.date('d-m-Y g:i:s A');
                $factura->save();
                return back()->with('success', 'Se ha anulado la factura');
            }else if($factura->estatus==2){
                $factura->estatus=1;
                $factura->observaciones = $factura->observaciones.' | Factura Abierta por: '.Auth::user()->nombres.' el '.date('d-m-Y g:i:s A');
                $factura->save();
                return back()->with('success', 'Se cambiado a abierta la factura');
            }
            return redirect('empresa/facturas')->with('success', 'La factura no esta abierta');
        }
        return redirect('empresa/facturas')->with('success', 'No existe un registro con ese id');
    }

    public function cerrar($id){
        $factura = Factura::where('empresa',Auth::user()->empresa)->where('tipo',1)->where('id', $id)->first();
        if ($factura) {
            if ($factura->estatus==1) {
                $factura->estatus=0;
                $factura->observaciones = $factura->observaciones.' | Factura Cerrada por: '.Auth::user()->nombres.' el '.date('d-m-Y g:i:s A');
                $factura->save();
                return back()->with('success', 'Se ha cerrado la factura');
            }
            return redirect('empresa/facturas')->with('success', 'La factura no esta abierta');
        }
        return redirect('empresa/facturas')->with('success', 'No existe un registro con ese id');
    }

    public function datatable_producto(Request $request, $producto=null){
        // storing  request (ie, get/post) global array to a variable
        $requestData =  $request;
        $columns = array(
        // datatable column index  => database column name
            0 => 'factura.codigo',
            1 => 'nombrecliente',
            2 => 'factura.fecha',
            3 => 'factura.vencimiento',
            4 => 'total',
            5 => 'pagado',
            6 => 'porpagar',
            7=>'factura.estatus',
            8=>'acciones'
        );
        $facturas=Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')->select('factura.*', DB::raw('c.nombre as nombrecliente'), DB::raw('c.apellido1 as ape1cliente'), DB::raw('c.apellido2 as ape2cliente'))->where('factura.empresa',Auth::user()->empresa)->where('factura.tipo',1);

        $facturas=$facturas->whereRaw('factura.id in (Select distinct(factura) from items_factura where producto='.$producto.' and tipo_inventario=1)');



        if (isset($requestData->search['value'])) {
          // if there is a search parameter, $requestData['search']['value'] contains search parameter
           $facturas=$facturas->where(function ($query) use ($requestData) {
              $query->where('factura.codigo', 'like', '%'.$requestData->search['value'].'%')
              ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        $totalFiltered=$totalData=$facturas->count();
        // $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);

        $facturas=$facturas->get();

        $data = array();
        foreach ($facturas as $factura) {
            $nestedData = array();
            $nestedData[] = '<a href="'.route('facturas.show',$factura->nro).'">'.$factura->codigo.'</a>';
            $nestedData[] = '<a href="'.route('contactos.show',$factura->cliente).'" target="_blank">'.$factura->nombrecliente.' '.$factura->ape1cliente.' '.$factura->ape2cliente.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
            $nestedData[] = date('d-m-Y', strtotime($factura->vencimiento));
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->pagado());
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->porpagar());
            $nestedData[] = '<spam class="text-'.$factura->estatus(true).'">'.$factura->estatus().'</spam>';
            $boton = '<a href="'.route('facturas.show',$factura->nro).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
              <a href="'.route('facturas.imprimir',['id' => $factura->nro, 'name'=> 'Factura No. '.$factura->codigo.'.pdf']).'" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a> ';

              if($factura->estatus==1){
              $boton .= '<a  href="'.route('ingresos.create_id', ['cliente'=>$factura->cliente, 'factura'=>$factura->nro]).'" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
              <a href="'.route('facturas.edit',$factura->nro).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>';

            }

            $boton.=' <form action="'.route('factura.anular',$factura->nro).'" method="POST" class="delete_form" style="display: none;" id="anular-factura'.$factura->id.'">'.csrf_field().'</form>';
            if($factura->estatus==1){
              $boton .= '<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('."'anular-factura".$factura->id."', '¿Está seguro de que desea anular la factura de venta?', ' ');".'"><i class="fas fa-minus"></i></button> ';
            }
            else if($factura->estatus==2){
              $boton.='<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('."'anular-factura".$factura->id."', '¿Está seguro de que desea abrir la factura de venta?', ' ');".'"><i class="fas fa-unlock-alt"></i></button>';
            }

            $nestedData[]=$boton;
            $data[] = $nestedData;
        }
        $json_data = array(
            "draw" => intval($requestData->draw),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );
        return json_encode($json_data);
    }

    public function datatable_producto_R($producto=null){
        // storing  request (ie, get/post) global array to a variable
        $columns = array(
            // datatable column index  => database column name
            0 => 'factura.codigo',
            1 => 'nombrecliente',
            2 => 'factura.fecha',
            3 => 'factura.vencimiento',
            4 => 'total',
            5 => 'pagado',
            6 => 'porpagar',
            7=>'factura.estatus',
            8=>'acciones'
        );
        $facturas=Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')->select('factura.*', DB::raw('c.nombre as nombrecliente'), DB::raw('c.apellido1 as ape1cliente'), DB::raw('c.apellido2 as ape2cliente'))->where('factura.empresa',Auth::user()->empresa)->where('factura.tipo',1);

        $facturas=$facturas->whereRaw('factura.id in (Select distinct(factura) from items_factura where producto='.$producto.' and tipo_inventario=1)');

        $totalFiltered=$totalData=$facturas->count();
        $facturas=$facturas->get();

        $data = array();
        foreach ($facturas as $factura) {
            $nestedData = array();
            $nestedData[] = '<a href="'.route('facturas.show',$factura->nro).'">'.$factura->codigo.'</a>';
            $nestedData[] = '<a href="'.route('contactos.show',$factura->cliente).'" target="_blank">'.$factura->nombrecliente.' '.$factura->ape1cliente.' '.$factura->ape2cliente.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
            $nestedData[] = date('d-m-Y', strtotime($factura->vencimiento));
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->pagado());
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->porpagar());
            $nestedData[] = '<spam class="text-'.$factura->estatus(true).'">'.$factura->estatus().'</spam>';
            $boton = '<a href="'.route('facturas.show',$factura->nro).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
          <a href="'.route('facturas.imprimir',['id' => $factura->nro, 'name'=> 'Factura No. '.$factura->codigo.'.pdf']).'" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a> ';

            if($factura->estatus==1){
                $boton .= '<a  href="'.route('ingresos.create_id', ['cliente'=>$factura->cliente, 'factura'=>$factura->nro]).'" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
          <a href="'.route('facturas.edit',$factura->nro).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>';

            }

            $boton.=' <form action="'.route('factura.anular',$factura->nro).'" method="POST" class="delete_form" style="display: none;" id="anular-factura'.$factura->id.'">'.csrf_field().'</form>';
            if($factura->estatus==1){
                $boton .= '<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('."'anular-factura".$factura->id."', '¿Está seguro de que desea anular la factura de venta?', ' ');".'"><i class="fas fa-minus"></i></button> ';
            }
            else if($factura->estatus==2){
                $boton.='<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('."'anular-factura".$factura->id."', '¿Está seguro de que desea abrir la factura de venta?', ' ');".'"><i class="fas fa-unlock-alt"></i></button>';
            }

            $nestedData[]=$boton;
            $data[] = $nestedData;
        }
        return json_encode($data);
    }

    public function datatable_cliente(Request $request, $contacto){
        // storing  request (ie, get/post) global array to a variable
        $requestData =  $request;
        $columns = array(
        // datatable column index  => database column name
            0 => 'factura.codigo',
            1 => 'nombrecliente',
            2 => 'factura.fecha',
            3 => 'factura.vencimiento',
            4 => 'total',
            5 => 'pagado',
            6 => 'porpagar',
            7 => 'factura.estatus',
            8 => 'acciones'
        );
        $facturas = Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->leftJoin('vendedores as v', 'factura.vendedor', '=', 'v.id')
            ->select('factura.*', DB::raw('c.nombre as nombrecliente'), DB::raw('c.apellido1 as ape1cliente'), DB::raw('c.apellido2 as ape2cliente'),
                DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'),
                DB::raw('(SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) - (Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id) ) as porpagar'))
            ->where('factura.empresa',Auth::user()->empresa)
            ->whereIn('factura.tipo',[1,2])
            ->where('factura.cliente',$contacto)
            ->groupBy('if.factura');

        if (isset($requestData->search['value'])) {
          // if there is a search parameter, $requestData['search']['value'] contains search parameter
           $facturas=$facturas->where(function ($query) use ($requestData) {
              $query->where('factura.codigo', 'like', '%'.$requestData->search['value'].'%')
              ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        $totalFiltered=$totalData=$facturas->count();
        //$facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);

        $facturas=$facturas->get();

        $data = array();
        foreach ($facturas as $factura) {
            $nestedData = array();
            $nestedData[] = '<a href="'.route('facturas.show',$factura->id).'">'.$factura->codigo.'</a>';
            $nestedData[] = '<a href="'.route('contactos.show',$factura->cliente).'" target="_blank">'.$factura->nombrecliente.' '.$factura->ape1cliente.' '.$factura->ape2cliente.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
            if(date('Y-m-d') > $factura->vencimiento && $factura->estatus==1){
                $nestedData[] = '<spam class="text-danger">'.date('d-m-Y', strtotime($factura->vencimiento)).'</spam>';
            }else{
                $nestedData[] = date('d-m-Y', strtotime($factura->vencimiento));
            }
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->pagado());
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->porpagar());
            $nestedData[] = '<spam class="text-'.$factura->estatus(true).'">'.$factura->estatus().'</spam>';
            $boton = '<a href="'.route('facturas.show',$factura->id).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
            <a href="'.route('facturas.imprimir',['id' => $factura->id, 'name'=> 'Factura No. '.$factura->codigo.'.pdf']).'" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a> ';

            if($factura->estatus==1){
              $boton .= '<a  href="'.route('ingresos.create_id', ['cliente'=>$factura->cliente, 'factura'=>$factura->id]).'" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
              <a href="'.route('facturas.edit',$factura->id).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>';
            }

            if($factura->estatus==1 && $factura->promesa_pago==null){
                $boton .= '<a href="javascript:modificarPromesa('.$factura->id.')" class="btn btn-outline-danger btn-icons promesa ml-1" idfactura="'.$factura->id.'" title="Promesa de Pago"><i class="fas fa-calendar"></i></a>';
            }

            $boton.=' <form action="'.route('factura.anular',$factura->id).'" method="POST" class="delete_form" style="display: none;" id="anular-factura'.$factura->id.'">'.csrf_field().'</form>';
            if($factura->estatus==1){
                $boton .= '<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('."'anular-factura".$factura->id."', '¿Está seguro de que desea anular la factura de venta?', ' ');".'"><i class="fas fa-minus"></i></button> ';
            }else if($factura->estatus==2){
                $boton.='<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('."'anular-factura".$factura->id."', '¿Está seguro de que desea abrir la factura de venta?', ' ');".'"><i class="fas fa-unlock-alt"></i></button>';
            }
            $nestedData[]=$boton;
            $data[] = $nestedData;
        }
        $json_data = array(
            "draw" => intval($requestData->draw),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );
        return json_encode($json_data);
    }

    public function aceptarFe($id){
        $factura = Factura::find($id);
        $factura->statusdian = 1;
        $factura->save();
        $mensaje = "Se ha aceptado la factura electrónica";
        return redirect('empresa/facturas')->with('success', $mensaje);
    }

    public function facturaRetenciones(Request $request){
        $factura = Factura::findOrFail($request->id);
        $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
        foreach ($retenciones as $retencion){
            $retencionId[] = $retencion->id;
        }
        return json_encode($retencionId);
    }

    public function xmlFacturaVentaMasivoIni(){
        $empresa = Auth::user()->empresa;
        $facturas = Factura::where('empresa', $empresa)->where('emitida', 0)->where('tipo',2)->where('modificado', 0)->limit(5)->get();

        foreach ($facturas as $factura) {
            $factura->modificado = 1;
            $factura->save();
        }

        foreach ($facturas as $factura) {
            $this->xmlFacturaVentaMasivo($factura->id, $empresa);
        }

        return back()->with('message_success', "Importacion masiva temrinada");
    }

    public function xmlFacturaVenta($id){
        $FacturaVenta = Factura::find($id);

        if (!$FacturaVenta) {
            return redirect('/empresa/facturas/facturas_electronica')->with('error', "No se ha encontrado la factura de venta, comuniquese con soporte.");
        }

        $FacturaVenta->emitida = $FacturaVenta->emitida;
        $FacturaVenta->save();

        if (Factura::where('empresa', auth()->user()->empresa)->count() > 0) {
            //Tomamos el tiempo en el que se crea el registro
            Session::put('posttimer', Factura::where('empresa', auth()->user()->empresa)->orderBy('updated_at', 'desc')->first()->updated_at);
            $sw = 1;

            if(isset($ultimoingreso)){
                //Recorremos la sesion para obtener la fecha
                foreach (Session::get('posttimer') as $key) {
                    if ($sw == 1) {
                        $ultimoingreso = $key;
                        $sw = 0;
                    }
                }

                //Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creación
                $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);

                //Si el tiempo es de menos de 10 segundos mandamos al listado general
                if ($diasDiferencia <= 10) {
                    $mensaje = "La factura electrónica ya ha sido enviada.";
                    return redirect('empresa/facturas/facturas_electronica')->with('success', $mensaje);
                }
            }
            
        }

        $ResolucionNumeracion = NumeracionFactura::where('empresa', Auth::user()->empresa)->where('num_equivalente', 0)->where('nomina',0)->where('tipo',2)->where('preferida', 1)->first();

        $infoEmpresa = Auth::user()->empresaObj;
        $data['Empresa'] = $infoEmpresa->toArray();

        $retenciones = FacturaRetencion::where('factura', $FacturaVenta->id)->get();

        $impTotal = 0;

        foreach ($FacturaVenta->total()->imp as $totalImp) {
            if (isset($totalImp->total)) {
                $impTotal += $totalImp->total;
            }
        }
        $items = ItemsFactura::where('factura', $id)->get();

        $decimal = explode(".", $impTotal);
        if (
            isset($decimal[1]) && $decimal[1] >= 50 || isset($decimal[1]) && $decimal[1] == 5 || isset($decimal[1]) && $decimal[1] == 4
            || isset($decimal[1]) && $decimal[1] == 3 || isset($decimal[1]) && $decimal[1] == 2 || isset($decimal[1]) && $decimal[1] == 1
        ) {
            $impTotal = round($impTotal);
        } else {
            $impTotal = round($impTotal);
        }

        $CUFEvr = $FacturaVenta->info_cufe($FacturaVenta->id, $impTotal);

        $infoCliente = Contacto::find($FacturaVenta->cliente);
        $data['Cliente'] = $infoCliente->toArray();

        $responsabilidades_empresa = DB::table('empresa_responsabilidad as er')
            ->join('responsabilidades_facturacion as rf', 'rf.id', '=', 'er.id_responsabilidad')
            ->select('rf.*')
            ->where('er.id_empresa', '=', Auth::user()->empresa)->where('er.id_responsabilidad', 5)
            ->orWhere('er.id_responsabilidad', 7)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 12)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 20)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 29)->where('er.id_empresa', '=', Auth::user()->empresa)->get();

        //-- Construccion del pdf a enviar con el código qr + el envío del archivo xml --//
        if ($FacturaVenta) {
            $emails = $FacturaVenta->cliente()->email;
            if ($FacturaVenta->cliente()->asociados('number') > 0) {
                $email = $emails;
                $emails = array();
                if ($email) {
                    $emails[] = $email;
                }
                foreach ($FacturaVenta->cliente()->asociados() as $asociado) {
                    if ($asociado->notificacion == 1 && $asociado->email) {
                        $emails[] = $asociado->email;
                    }
                }
            }

            $tituloCorreo =  $data['Empresa']['nit'] . ";" . $data['Empresa']['nombre'] . ";" . $FacturaVenta->codigo . ";01;" . $data['Empresa']['nombre'];

            $isImpuesto = 1;
            // return $data;
            //   if(auth()->user()->empresa == 1)
            //   {
            //       return $xml = response()->view('templates.xml.01',compact('CUFEvr','ResolucionNumeracion','FacturaVenta', 'data','items','retenciones','responsabilidades_empresa','emails','impTotal','isImpuesto'))->header('Cache-Control', 'public')
            //   ->header('Content-Description', 'File Transfer')
            //   ->header('Content-Disposition', 'attachment; filename=FV-'.$FacturaVenta->codigo.'.xml')
            //   ->header('Content-Transfer-Encoding', 'binary')
            //   ->header('Content-Type', 'text/xml');
            //   }

            //-- Generación del XML a enviar a la DIAN -- //
            $xml = view('templates.xml.01', compact('CUFEvr', 'ResolucionNumeracion', 'FacturaVenta', 'data', 'items', 'retenciones', 'responsabilidades_empresa', 'emails', 'impTotal', 'isImpuesto'));

            //-- Envío de datos a la DIAN --//
            $res = $this->EnviarDatosDian($xml);

            //-- Decodificación de respuesta de la DIAN --//
            $res = json_decode($res, true);


            if (isset($res['errorType'])) {
                if ($res['errorType'] == "KeyError") {
                    return back()->with('message_denied', "La dian está presentando problemas para emitir documentos electrónicos, inténtelo más tarde.");
                }
            }

            if (!isset($res['statusCode']) && isset($res['message'])) {
                return redirect('/empresa/facturas/facturas_electronica')->with('message_denied', $res['message']);
            }

            $statusCode = $res['statusCode'] ?? null; //200

            if (!isset($statusCode)) {
                return back()->with('message_denied', isset($res['message']) ? $res['message'] : 'Error en la emisión del docuemento, intente nuevamente en un momento');
            }

            //-- Guardamos la respuesta de la dian solo cuando son errores--//
            if ($statusCode != 200) {
                $FacturaVenta->dian_response = $res['statusCode'] ?? null;
                $FacturaVenta->save();
            }

            //-- Validación 1 del status code (Cuando hay un error) --//
            if ($statusCode != 200) {
                $message = $res['errorMessage'];
                $errorReason = $res['errorReason'];

                //Validamos si depronto la factura fue emitida pero no quedamos con ningun registro de ella.
                $saveNoJson = $statusJson = $this->validateStatusDian(auth()->user()->empresaObj->nit, $FacturaVenta->codigo, "01", $ResolucionNumeracion->prefijo);

                $statusJson = json_decode($statusJson, true);

                if ($statusJson["statusCode"] == 200) {

                    //linea comentada por ahorro de espacio en bd, ay que esta información de las facturas procesadas se puede obtener mediante consulta api.
                    // $FacturaVenta->dian_response = $saveNoJson;
                    $message = "Factura emitida correctamente por validación";
                    $FacturaVenta->emitida = 1;
                    $FacturaVenta->fecha_expedicion = Carbon::now();

                    //Llave unica para acceso por correo
                    $key = Hash::make(date("H:i:s"));
                    $toReplace = array('/', '$', '.');
                    $key = str_replace($toReplace, "", $key);
                    $FacturaVenta->nonkey = $key;

                    $FacturaVenta->save();

                    $this->generateXmlPdfEmail($statusJson['document'], $FacturaVenta, $emails, $data, $CUFEvr, $items, $ResolucionNumeracion, $tituloCorreo);
                } else {
                    return back()->with('message_denied', $message)->with('errorReason', $errorReason);
                }
            }

            $document = $res['document'];

            //-- estátus de que la factura ha sido aprobada --//
            if ($statusCode == 200) {

                //Llave unica para acceso por correo
                $key = Hash::make(date("H:i:s"));
                $toReplace = array('/', '$', '.');
                $key = str_replace($toReplace, "", $key);
                $FacturaVenta->nonkey = $key;
                $FacturaVenta->save();
                //

                $message = "Factura emitida correctamente";
                $FacturaVenta->emitida = 1;
                $FacturaVenta->fecha_expedicion = Carbon::now();
                $FacturaVenta->save();

                $this->generateXmlPdfEmail($document, $FacturaVenta, $emails, $data, $CUFEvr, $items, $ResolucionNumeracion, $tituloCorreo);
            }
            return back()->with('message_success', $message);
        }
    }

   /**
     * Metodo de consulta
     * Consultamos si una factura ya fue emititda y no quedamos con registro de ella, de ser así la guardamos, en bd, generamos el xml y enviamos el correo al cliente.
     */
    public function generateXmlPdfEmail($document, $FacturaVenta, $emails, $data, $CUFEvr, $items, $ResolucionNumeracion, $tituloCorreo){

        $empresa = auth()->user()->empresaObj;

        $document = base64_decode($document);

        //-- Generación del archivo .xml mas el lugar donde se va a guardar --//
        $path = public_path() . '/xml/empresa' . auth()->user()->empresa;

        if (!File::exists($path)) {
            File::makeDirectory($path);
            $path = $path . "/FV";
            File::makeDirectory($path);
        } else {
            $path = public_path() . '/xml/empresa' . auth()->user()->empresa . "/FV";
        }

        $namexml = 'FV-' . $FacturaVenta->codigo . ".xml";
        $ruta_xmlresponse = $path . "/" . $namexml;
        $file = fopen($ruta_xmlresponse, "w");
        fwrite($file, $document . PHP_EOL);
        fclose($file);

        if (is_array($emails)) {
            $max = count($emails);
        } else {
            $max = 1;
        }

        if (!$emails || $max == 0) {

            return redirect('empresa/facturas/facturas_electronica' . $FacturaVenta->nro)->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
        }


        /*..............................
        Construcción del código qr a la factura
        ................................*/
        $impuesto = 0;
        foreach ($FacturaVenta->total()->imp as $key => $imp) {
            if (isset($imp->total)) {
                $impuesto = $imp->total;
            }
        }

        $decimal = explode(".", $impuesto);
        if (isset($decimal[1]) && $decimal[1] > 50) {
            $impuesto = round($impuesto);
        }

        $codqr = "NumFac:" . $FacturaVenta->codigo . "\n" .
            "NitFac:"  . $data['Empresa']['nit']   . "\n" .
            "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
            "FecFac:" . Carbon::parse($FacturaVenta->created_at)->format('Y-m-d') .  "\n" .
            "HoraFactura" . Carbon::parse($FacturaVenta->created_at)->format('H:i:s') . '-05:00' . "\n" .
            "ValorFactura:" .  number_format($FacturaVenta->total()->subtotal, 2, '.', '') . "\n" .
            "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
            "ValorOtrosImpuestos:" .  0.00 . "\n" .
            "ValorTotalFactura:" .  number_format($FacturaVenta->total()->subtotal + $FacturaVenta->impuestos_totales(), 2, '.', '') . "\n" .
            "CUFE:" . $CUFEvr;

        /*..............................
        Construcción del código qr a la factura
        ................................*/

        $itemscount = $items->count();
        $retenciones = FacturaRetencion::where('factura', $FacturaVenta->id)->get();
        $resolucion  = $ResolucionNumeracion;
        $tipo = "original";
        $factura = $FacturaVenta;
        $vendedor = Vendedor::where('id', $FacturaVenta->vendedor)->first();

        if ($factura->tipo_operacion == 3) {
            $pdf = PDF::loadView('pdf.facturatercero', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones', 'resolucion', 'codqr', 'CUFEvr', 'vendedor', 'empresa'))
                ->save(public_path() . "/convertidor" . "/FV-" . $factura->codigo . ".pdf")->stream();
        } else {                                           
            $pdf = PDF::loadView('pdf.electronica', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones', 'resolucion', 'codqr', 'CUFEvr', 'vendedor', 'empresa'))
                ->save(public_path() . "/convertidor" . "/FV-" . $factura->codigo . ".pdf")->stream();
        }

        //Construccion del archivo zip.
        $zip = new ZipArchive();

        //Después creamos un archivo zip temporal que llamamos miarchivo.zip y que eliminaremos después de descargarlo.
        //Para indicarle que tiene que crearlo ya que no existe utilizamos el valor ZipArchive::CREATE.
        $nombreArchivoZip = "FV-" . $factura->codigo . ".zip";

        $zip->open("convertidor/" . $nombreArchivoZip, ZipArchive::CREATE);

        if (!$zip->open($nombreArchivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            return ("Error abriendo ZIP en $nombreArchivoZip");
        }

        $ruta_pdf = public_path() . "/convertidor" . "/FV-" . $factura->codigo . ".pdf";

        $zip->addFile($ruta_xmlresponse, "FV-" . $factura->codigo . ".xml");
        $zip->addFile($ruta_pdf, "FV-" . $factura->codigo . ".pdf");
        $resultado = $zip->close();

        /*..............................
        Construcción del envío de correo electrónico
        ................................*/

        $data = array(
            'email' => 'info@networksoft.online',
        );
        $total = Funcion::Parsear($factura->total()->total);
        $cliente = $FacturaVenta->cliente()->nombre;

        $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
        if($host){
            $existing = config('mail');
            $new =array_merge(
                $existing, [
                    'host' => $host->servidor,
                    'port' => $host->puerto,
                    'encryption' => $host->seguridad,
                    'username' => $host->usuario,
                    'password' => $host->password,
                ]
            );
            config(['mail'=>$new]);
        }

        // Mail::send('emails.email', compact('factura', 'total', 'cliente', 'empresa'), function ($message) use ($pdf, $emails, $ruta_xmlresponse, $FacturaVenta, $nombreArchivoZip, $tituloCorreo, $empresa) {
        //     $message->attach($nombreArchivoZip, ['as' => $nombreArchivoZip, 'mime' => 'application/octet-stream', 'Content-Transfer-Encoding' => 'Binary']);
        //     $message->from('info@gestordepartes.net', Auth::user()->empresaObj->nombre);
        //     $message->to($emails)->subject($tituloCorreo);
        // });

        // Si quieres puedes eliminarlo después:
        if (isset($nombreArchivoZip)) {
            unlink($nombreArchivoZip);
            unlink($ruta_pdf);
        }
    }

    public function xmlFacturaVentabyCorreo($id)
    {
        $empresa = auth()->user()->empresaObj;

        $ResolucionNumeracion = NumeracionFactura::where('empresa', Auth::user()->empresa)->where('num_equivalente', 0)->where('tipo',2)->where('preferida', 1)->first();

        $infoEmpresa = Empresa::find(Auth::user()->empresa);
        $data['Empresa'] = $infoEmpresa->toArray();

        $FacturaVenta = Factura::find($id);
        $vendedor = Vendedor::where('id', $FacturaVenta->vendedor)->first();

        //Generacion de llave unica para acceso por correo
        $key = Hash::make(date("H:i:s"));
        $toReplace = array('/', '$', '.');
        $key = str_replace($toReplace, "", $key);
        $FacturaVenta->nonkey = $key;
        $FacturaVenta->save();
        //
        $retenciones = FacturaRetencion::where('factura', $FacturaVenta->id)->get();

        $impTotal = 0;

        foreach ($FacturaVenta->total()->imp as $totalImp) {
            if (isset($totalImp->total)) {
                $impTotal += $totalImp->total;
            }
        }
        $items = ItemsFactura::where('factura', $id)->get();

        $CUFEvr = $FacturaVenta->info_cufe($FacturaVenta->id, $impTotal);

        $infoCliente = Contacto::find($FacturaVenta->cliente);
        $data['Cliente'] = $infoCliente->toArray();

        $responsabilidades_empresa = DB::table('empresa_responsabilidad as er')
            ->join('responsabilidades_facturacion as rf', 'rf.id', '=', 'er.id_responsabilidad')
            ->select('rf.*')
            ->where('er.id_empresa', Auth::user()->empresa)
            ->get();

        $emails = $FacturaVenta->cliente()->email;
        if ($FacturaVenta->cliente()->asociados('number') > 0) {
            $email = $emails;
            $emails = array();
            if ($email) {
                $emails[] = $email;
            }
            foreach ($FacturaVenta->cliente()->asociados() as $asociado) {
                if ($asociado->notificacion == 1 && $asociado->email) {
                    $emails[] = $asociado->email;
                }
            }
        }


        //-- Generación del XML a enviar a la DIAN -- //
        $xml = view('templates.xml.01', compact('CUFEvr', 'ResolucionNumeracion', 'FacturaVenta', 'data', 'items', 'retenciones', 'responsabilidades_empresa', 'emails', 'vendedor'));

        /*return $xml = response()->view('templates.xml.01',compact('CUFEvr','ResolucionNumeracion','FacturaVenta', 'data','items','retenciones','responsabilidades_empresa'))->header('Cache-Control', 'public')
        ->header('Content-Description', 'File Transfer')
        ->header('Content-Disposition', 'attachment; filename=FV-'.$FacturaVenta->codigo.'.xml')
        ->header('Content-Transfer-Encoding', 'binary')
        ->header('Content-Type', 'text/xml');*/

        //$message = $res['statusMessage'];
        $message = "Factura por correo con xml enviada correctamente";

        //-- Generación del archivo .xml mas el lugar donde se va a guardar --//
        $path = public_path() . '/xml/empresa' . auth()->user()->empresa;

        if (!File::exists($path)) {
            File::makeDirectory($path);
            $path = $path . "/FV";
            File::makeDirectory($path);
        } else {
            $path = public_path() . '/xml/empresa' . auth()->user()->empresa . "/FV";
        }

        $namexml = 'FV-' . $FacturaVenta->codigo . ".xml";
        $ruta_xmlresponse = $path . "/" . $namexml;
        $file = fopen($ruta_xmlresponse, "w");
        fwrite($file, $xml . PHP_EOL);
        fclose($file);

        //-- Construccion del pdf a enviar con el código qr + el envío del archivo xml --//
        if ($FacturaVenta) {
            if (!$emails || count($emails) == 0) {
                return redirect('empresa/facturas/' . $FacturaVenta->nro)->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
            }


            /*..............................
        Construcción del código qr a la factura
        ................................*/
            $impuesto = 0;
            foreach ($FacturaVenta->total()->imp as $key => $imp) {
                if (isset($imp->total)) {
                    $impuesto = $imp->total;
                }
            }

            $codqr = "NumFac:" . $FacturaVenta->codigo . "\n" .
                "NitFac:"  . $data['Empresa']['nit']   . "\n" .
                "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
                "FecFac:" . Carbon::parse($FacturaVenta->created_at)->format('Y-m-d') .  "\n" .
                "HoraFactura" . Carbon::parse($FacturaVenta->created_at)->format('H:i:s') . '-05:00' . "\n" .
                "ValorFactura:" .  number_format($FacturaVenta->total()->subtotal, 2, '.', '') . "\n" .
                "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
                "ValorOtrosImpuestos:" .  0.00 . "\n" .
                "ValorTotalFactura:" .  number_format($FacturaVenta->total()->subtotal + $FacturaVenta->impuestos_totales(), 2, '.', '') . "\n" .
                "CUFE:" . $CUFEvr;

            /*..............................
        Construcción del código qr a la factura
        ................................*/

            $itemscount = $items->count();
            $retenciones = FacturaRetencion::where('factura', $FacturaVenta->id)->get();
            $resolucion  = $ResolucionNumeracion;
            $tipo = "original";
            $factura = $FacturaVenta;

            if ($factura->tipo_operacion == 3) {
                $detalle_recaudo = $factura->detalleRecaudo();
                $pdf = PDF::loadView('pdf.facturatercero', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones', 'resolucion', 'codqr', 'CUFEvr', 'detalle_recaudo', 'vendedor', 'empresa'))->stream();
            } else {
                $pdf = PDF::loadView('pdf.factura', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones', 'resolucion', 'codqr', 'CUFEvr', 'vendedor', 'empresa'))->stream();
            }
            /*..............................
        Construcción del envío de correo electrónico
        ................................*/
            $data = array(
                'email' => 'info@networksoft.online',
            );

            $total = Funcion::Parsear($factura->total()->total);
            $cliente = $FacturaVenta->cliente()->nombre;

            $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
            if($host){
                $existing = config('mail');
                $new =array_merge(
                    $existing, [
                        'host' => $host->servidor,
                        'port' => $host->puerto,
                        'encryption' => $host->seguridad,
                        'username' => $host->usuario,
                        'password' => $host->password,
                    ]
                );
                config(['mail'=>$new]);
            }

            Mail::send('emails.email', compact('factura', 'total', 'cliente', 'empresa'), function ($message) use ($pdf, $emails, $ruta_xmlresponse, $FacturaVenta) {
                $message->attachData($pdf, 'FV-' . $FacturaVenta->codigo . '.pdf', ['mime' => 'application/pdf']);
                $message->attach($ruta_xmlresponse);
                $message->from('info@networksoft.online', Auth::user()->empresaObj->nombre);
                $message->to($emails)->subject(Auth::user()->empresaObj->nombre . " Factura Electrónica " . $FacturaVenta->codigo);
            });
        }
        return back()->with('message_success', $message);
    }

    public function validate_dian(Request $request)
    {
        $factura = Factura::find($request->id);
        $responsabilidades = Auth::user()->contador_responsabilidades();
        $numeracion = NumeracionFactura::where('empresa', Auth::user()->empresa)->where('tipo',2)->where('id', $factura->numeracion)->first();
        $empresa  = Auth::user()->empresaObj;
        $cliente  = $factura->cliente();
        
        //Inicializamos la variable para ver si tiene las nuevas responsabilidades que no da la dian 042
        $resp = 0;
        if($responsabilidades > 0){
            foreach (Empresa::find(auth()->user()->empresa)->responsabilidades() as $respo) {
                if (
                    $respo->id_responsabilidad == 5 || $respo->id_responsabilidad == 7 || $respo->id_responsabilidad == 12
                    || $respo->id_responsabilidad == 20 || $respo->id_responsabilidad == 29
                    ) {
                        $resp = 1;
                    }
            }
        }

        if ($cliente->tip_iden != 6) {
            $cliente->tipo_persona      = 1; //-- Persona Natural
            $cliente->responsableiva    = 2; //-- No responsable de iva
            $cliente->save();
        }


        //-- Validación de si la ultima factura creada fue emitida o si es la primer factura a emitir que la deje --//

        if ($numeracion->prefijo != null || $numeracion->prefijo != "") {
            $numero = intval(preg_replace('/[^0-9]+/', '', $factura->codigo), 10);
            $codigo    = substr($factura->codigo, strlen($numeracion->prefijo), strlen($numero));
        } else {
            $codigo = $factura->codigo;
        }


        //Si tenemos una pasada factura a la que estamos intentando emitir entra a este if
        if (Factura::where('empresa', Auth::user()->empresa)->where('numeracion', $factura->numeracion)->where('codigo', $numeracion->prefijo . ($codigo - 1))->count() > 0) {
            $ultfact = Factura::where('empresa', Auth::user()->empresa)->where('numeracion', $factura->numeracion)->where('codigo', $numeracion->prefijo . ($codigo - 1))->first();

            if ($ultfact->emitida == null || $ultfact->emitida == 2 || $ultfact->emitida == 0) { //-- si es null o es 2(no emitida) o 0 no emitida
                $emitida = false;
            } else {
                $emitida = true;
            }
        } elseif ($codigo == $numeracion->inicioverdadero) { //-- si no entra es por que hay la posibilidad de que sea la primer factura emitida de esa numeración
            $emitida = true;
        } else { //cambió el prefijo de una numeracion existente ademas hay mas facturas con esa numeración sin emitir
              /*
            Actualizacion: Como no es igual al inicioverdadero es muy probable que no 
            se este emitiendo desde el numero de inicio verdadero si no que arranco un poco mas
            adelante.
            */
            $emitida = true;
        }

        return response()->json([
            "numeracion" => $numeracion, "responsabilidades" => $responsabilidades, "empresa" => $empresa,
            "cliente" => $cliente, "total" => $factura->total()->total,
            "emitida" => $emitida, "responsabilidad" => $resp
        ]);
    }

    public function xmlFacturaVentaFe($id)
    {

        $empresa = auth()->user()->empresaObj;

        $FacturaVenta = Factura::where('nonkey', $id)->first();
        $ResolucionNumeracion = NumeracionFactura::where('empresa', $FacturaVenta->empresa)->where('tipo',2)->where('preferida', 1)->first();

        $infoEmpresa = Empresa::find($FacturaVenta->empresa);
        $data['Empresa'] = $infoEmpresa->toArray();

        $retenciones = FacturaRetencion::where('factura', $FacturaVenta->id)->get();

        $SumImp = 0;

        foreach ($FacturaVenta->total()->imp as $totalImp) {
            if (isset($totalImp->total)) {
                $impTotal += $totalImp->total;
            }
        }
        $items = ItemsFactura::where('factura', $id)->get();

        $CUFEvr = $FacturaVenta->info_cufe($FacturaVenta->id, $impTotal);

        $infoCliente = Contacto::find($FacturaVenta->cliente);
        $data['Cliente'] = $infoCliente->toArray();

        $responsabilidades_empresa = DB::table('empresa_responsabilidad as er')
            ->join('responsabilidades_facturacion as rf', 'rf.id', '=', 'er.id_responsabilidad')
            ->select('rf.*')
            ->where('er.id_empresa', Auth::user()->empresa)
            ->get();

        return $xml = response()->view('templates.xml.01', compact('CUFEvr', 'ResolucionNumeracion', 'FacturaVenta', 'data', 'items', 'retenciones', 'responsabilidades_empresa'))->header('Cache-Control', 'public')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Disposition', 'attachment; filename=FV-' . $FacturaVenta->codigo . '.xml')
            ->header('Content-Transfer-Encoding', 'binary')
            ->header('Content-Type', 'text/xml');

        //-- Construccion del pdf a enviar con el código qr + el envío del archivo xml --//
        if ($FacturaVenta) {

            /*..............................
            Construcción del código qr a la factura
            ................................*/
            foreach ($FacturaVenta->total()->imp as $key => $imp) {
                if (isset($imp->total)) {
                    $impuesto = $imp->total;
                }
            }

            $codqr = "NumFac:" . $FacturaVenta->codigo . "\n" .
                "NitFac:"  . $data['Empresa']['nit']   . "\n" .
                "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
                "FecFac:" . Carbon::parse($FacturaVenta->created_at)->format('Y-m-d') .  "\n" .
                "HoraFactura" . Carbon::parse($FacturaVenta->created_at)->format('H:i:s') . '-05:00' . "\n" .
                "ValorFactura:" .  number_format($FacturaVenta->total()->subtotal, 2, '.', '') . "\n" .
                "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
                "ValorOtrosImpuestos:" .  0.00 . "\n" .
                "ValorTotalFactura:" .  number_format($FacturaVenta->total()->subtotal + $FacturaVenta->impuestos_totales(), 2, '.', '') . "\n" .
                "CUFE:" . $CUFEvr;

            /*..............................
                  Construcción del código qr a la factura
                  ................................*/

            $itemscount = $items->count();
            $retenciones = FacturaRetencion::where('factura', $FacturaVenta->id)->get();
            $resolucion  = $ResolucionNumeracion;
            $tipo = "original";
            $factura = $FacturaVenta;
            $vendedor = Vendedor::where('id', $FacturaVenta->vendedor)->first();

            if ($factura->tipo_operacion == 3) {
                $detalle_recaudo = $factura->detalleRecaudo();
                $pdf = PDF::loadView('pdf.facturatercero', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones', 'resolucion', 'codqr', 'CUFEvr', 'detalle_recaudo', 'vendedor', 'empresa'))->stream();
            } else {
                $pdf = PDF::loadView('pdf.factura', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones', 'resolucion', 'codqr', 'CUFEvr', 'vendedor', 'empresa'))->stream();
            }

            return response($pdf->withHeaders(['Content-Type' => 'application/pdf',]));
        }
    }

    public function validate_technicalkey_dian()
    {
        $empresa = auth()->user()->empresaObj;

        //Si está habilitado frente a la Dian y no tiene aún la clave técnica.
        if ($empresa) {
            if ($empresa->estado_dian == 1 && $empresa->technicalkey == null) {
                $softwareCode = "49fab599-4556-4828-a30b-852a910c5bb1";
                $accountCodeVendor = "890930534";
                $accountCode = $empresa->nit;
                $json = $this->getTechnicalKey($softwareCode, $accountCodeVendor, $accountCode);
                $json = json_decode($json, true);

                if (isset($json['statusCode'])) {
                    if ($json['statusCode'] != 404) {
                        $empresa->technicalkey =  $json['numberingRangelist'][0]['technicalKey'];
                        $empresa->save();


                        //Envio del correo electrónico.
                        $rango_numeracion = $json['numberingRangelist'];
                        $tituloCorreo = "Facturador Electrónico Activado";
                        $emails = auth()->user()->empresaObj->email;

                        $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
                        if($host){
                            $existing = config('mail');
                            $new =array_merge(
                                $existing, [
                                    'host' => $host->servidor,
                                    'port' => $host->puerto,
                                    'encryption' => $host->seguridad,
                                    'username' => $host->usuario,
                                    'password' => $host->password,
                                ]
                            );
                            config(['mail'=>$new]);
                        }

                        Mail::send('emails.dian.felicidades', compact('empresa', 'rango_numeracion'), function ($message) use ($emails, $tituloCorreo) {
                            $message->from('info@networksoft.online', 'Facturación Electrónica - Gestor de Partes');
                            $message->to($emails)->subject($tituloCorreo);
                        });

                        return response()->json(1);
                    }
                } else {
                    return response()->json(0);
                }
            } else {
                return response()->json(0);
            }
        }
    }

    public function validateTimeEmicion(){
        if(auth()->user()->empresa()->estado_dian == 1){
            $numeracion = NumeracionFactura::where('empresa',auth()->user()->empresa)->where('preferida',1)->where('tipo',2)->first();
            $pendientes = Factura::where('empresa',auth()->user()->empresa)->where('numeracion',$numeracion->id)
            ->where('emitida',0)->where('created_at','<=',Carbon::now()->subDay(1))->get();
        return response()->json($pendientes);
        }else return null;
    }

    public function mensaje($id){
        $factura = Factura::find($id);
        $hora = date('G');
        $mensaje = "Se le informa que su factura ha sido generada bajo el Nro. ".$factura->codigo.", por un monto de $".$factura->parsear($factura->total()->total);
        
        $numero = str_replace('+','',$factura->cliente()->celular);
        $numero = str_replace(' ','',$numero);

        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('status', 1)->first();
        if($servicio){
            if($servicio->nombre == 'Hablame SMS'){
                if($servicio->api_key && $servicio->user && $servicio->pass){
                    $post['toNumber'] = $numero;
                    $post['sms'] = $mensaje;

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/marketing',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',CURLOPT_POSTFIELDS => json_encode($post),
                        CURLOPT_HTTPHEADER => array(
                            'account: '.$servicio->user,
                            'apiKey: '.$servicio->api_key,
                            'token: '.$servicio->pass,
                            'Content-Type: application/json'
                        ),
                    ));
                    $result = curl_exec ($curl);
                    $err  = curl_error($curl);
                    curl_close($curl);

                    $response = json_decode($result, true);
                    if(isset($response['error'])){
                        if($response['error']['code'] == 1000303){
                            $msj = 'Cuenta no encontrada';
                        }else{
                            $msj = $response['error']['details'];
                        }
                        $factura->response = $msj;
                        $factura->save();
                        return back()->with('danger', 'Envío Fallido: '.$msj);
                    }else{
                        if($response['status'] == '1x000'){
                            $msj = 'SMS recíbido por hablame exitosamente';
                        }else if($response['status'] == '1x152'){
                            $msj = 'SMS entregado al operador';
                        }else if($response['status'] == '1x153'){
                            $msj = 'SMS entregado al celular';
                        }
                        $factura->response = $msj;
                        $factura->save();
                        return back()->with('success', 'Envío Éxitoso: '.$msj);
                    }
                }else{
                    $mensaje = 'EL MENSAJE NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                    return back()->with('danger', $mensaje);
                }
            }elseif($servicio->nombre == 'SmsEasySms'){
                if($servicio->user && $servicio->pass){
                    $post['to'] = array('57'.$numero);
                    $post['text'] = $mensaje;
                    $post['from'] = "SMS";
                    $login = $servicio->user;
                    $password = $servicio->pass;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://sms.istsas.com/Api/rest/message");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array(
                        "Accept: application/json",
                        "Authorization: Basic ".base64_encode($login.":".$password)));
                    $result = curl_exec ($ch);
                    $err  = curl_error($ch);
                    curl_close($ch);

                    if ($err) {

                    }else{
                        $response = json_decode($result, true);
                        if(isset($response['error'])){
                            if($response['error']['code'] == 102){
                                $msj = "No hay destinatarios válidos (Cumpla con el formato de nro +5700000000000)";
                            }else if($response['error']['code'] == 103){
                                $msj = "Nombre de usuario o contraseña desconocidos";
                            }else if($response['error']['code'] == 104){
                                $msj = "Falta el mensaje de texto";
                            }else if($response['error']['code'] == 105){
                                $msj = "Mensaje de texto demasiado largo";
                            }else if($response['error']['code'] == 106){
                                $msj = "Falta el remitente";
                            }else if($response['error']['code'] == 107){
                                $msj = "Remitente demasiado largo";
                            }else if($response['error']['code'] == 108){
                                $msj = "No hay fecha y hora válida para enviar";
                            }else if($response['error']['code'] == 109){
                                $msj = "URL de notificación incorrecta";
                            }else if($response['error']['code'] == 110){
                                $msj = "Se superó el número máximo de piezas permitido o número incorrecto de piezas";
                            }else if($response['error']['code'] == 111){
                                $msj = "Crédito/Saldo insuficiente";
                            }else if($response['error']['code'] == 112){
                                $msj = "Dirección IP no permitida";
                            }else if($response['error']['code'] == 113){
                                $msj = "Codificación no válida";
                            }else{
                                $msj = $response['error']['description'];
                            }
                            $factura->response = $msj;
                            $factura->save();
                            return back()->with('danger', 'Envío Fallido: '.$msj);
                        }else{
                            $factura->response = 'Mensaje enviado correctamente.';
                            $factura->save();
                            return back()->with('success', 'Mensaje enviado correctamente.');
                        }
                    }
                }else{
                    $mensaje = 'EL MENSAJE NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                    return back()->with('danger', $mensaje);
                }
            }else{
                if($servicio->user && $servicio->pass){
                    $post['to'] = array('57'.$numero);
                    $post['text'] = $mensaje;
                    $post['from'] = "";
                    $login = $servicio->user;
                    $password = $servicio->pass;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://masivos.colombiared.com.co/Api/rest/message");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                    curl_setopt($ch, CURLOPT_HTTPHEADER,
                    array(
                        "Accept: application/json",
                        "Authorization: Basic ".base64_encode($login.":".$password)));
                    $result = curl_exec ($ch);
                    $err  = curl_error($ch);
                    curl_close($ch);

                    if ($err) {

                    }else{
                        $response = json_decode($result, true);
                        if(isset($response['error'])){
                            if($response['error']['code'] == 102){
                                $msj = "No hay destinatarios válidos (Cumpla con el formato de nro +5700000000000)";
                            }else if($response['error']['code'] == 103){
                                $msj = "Nombre de usuario o contraseña desconocidos";
                            }else if($response['error']['code'] == 104){
                                $msj = "Falta el mensaje de texto";
                            }else if($response['error']['code'] == 105){
                                $msj = "Mensaje de texto demasiado largo";
                            }else if($response['error']['code'] == 106){
                                $msj = "Falta el remitente";
                            }else if($response['error']['code'] == 107){
                                $msj = "Remitente demasiado largo";
                            }else if($response['error']['code'] == 108){
                                $msj = "No hay fecha y hora válida para enviar";
                            }else if($response['error']['code'] == 109){
                                $msj = "URL de notificación incorrecta";
                            }else if($response['error']['code'] == 110){
                                $msj = "Se superó el número máximo de piezas permitido o número incorrecto de piezas";
                            }else if($response['error']['code'] == 111){
                                $msj = "Crédito/Saldo insuficiente";
                            }else if($response['error']['code'] == 112){
                                $msj = "Dirección IP no permitida";
                            }else if($response['error']['code'] == 113){
                                $msj = "Codificación no válida";
                            }else{
                                $msj = $response['error']['description'];
                            }
                            $factura->response = $msj;
                            $factura->save();
                            return back()->with('danger', 'Envío Fallido: '.$msj);
                        }else{
                            $factura->response = 'Mensaje enviado correctamente.';
                            $factura->save();
                            return back()->with('success', 'Mensaje enviado correctamente.');
                        }
                    }
                }else{
                    $mensaje = 'EL MENSAJE NO SE PUDO ENVIAR PORQUE FALTA INFORMACIÓN EN LA CONFIGURACIÓN DEL SERVICIO';
                    return back()->with('danger', $mensaje);
                }
            }
        }else{
            return back()->with('danger', 'DISCULPE, NO POSEE NINGUN SERVICIO DE SMS HABILITADO. POR FAVOR HABILÍTELO PARA DISFRUTAR DEL SERVICIO');
        }
    }
    
    public function promesa_pago($id){
        $factura = Factura::where('id', $id)->first();
        return json_encode($factura);
    }
    
    public function store_promesa(Request $request) {
        $request->validate([
            'id' => 'required',
            'promesa_pago' => 'required'
        ]);

        $factura = Factura::where('id', $request->id)->first();
        
        $numero = 0;
        $numero = PromesaPago::all()->count();
        $numero++;
        
        $promesa_pago = New PromesaPago;
        $promesa_pago->nro = $numero;
        $promesa_pago->factura = $factura->id;
        $promesa_pago->cliente = $factura->cliente;
        $promesa_pago->fecha = $factura->vencimiento;
        $promesa_pago->vencimiento = $request->promesa_pago;
        $promesa_pago->created_by = Auth::user()->id;
        $promesa_pago->save();
        
        $factura->promesa_pago  = $request->promesa_pago;
        $factura->vencimiento   = $request->promesa_pago;
        $factura->observaciones = 'Añadiendo Promesa de Pago';
        $factura->observaciones = $factura->observaciones.' | Factura Editada por: '.Auth::user()->nombres.' el '.date('d-m-Y g:i:s A'). ' para añadir Promesa de Pago Nro. '.$promesa_pago->nro;
        $factura->save();

        /* VERIFICAR SI EL CONTRATO ESTÁ DESHABILITADO PARA HABILITARLO */

        $contrato = $factura->cliente()->contrato();
        if ($contrato) {
            $mikrotik = Mikrotik::find($contrato->server_configuration_id);
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write('/ip/firewall/address-list/print', TRUE);
                $ARRAYS = $API->read();

                #ELIMINAMOS DE MOROSOS#
                    $API->write('/ip/firewall/address-list/print', false);
                    $API->write('?address='.$contrato->ip, false);
                    $API->write("?list=morosos",false);
                    $API->write('=.proplist=.id');
                    $ARRAYS = $API->read();

                    if(count($ARRAYS)>0){
                        $API->write('/ip/firewall/address-list/remove', false);
                        $API->write('=.id='.$ARRAYS[0]['.id']);
                        $READ = $API->read();
                    }
                #ELIMINAMOS DE MOROSOS#

                #AGREGAMOS A IP_AUTORIZADAS#
                    $API->comm("/ip/firewall/address-list/add", array(
                        "address" => $contrato->ip,
                        "list" => 'ips_autorizadas'
                        )
                    );
                #AGREGAMOS A IP_AUTORIZADAS#

                $contrato->state = 'enabled';
                $contrato->save();
                $API->disconnect();
            }
        }
        
        return response()->json([
            'success' => true,
            'type' => 'success',
            'message' => 'Registrada con Éxito',
            'promesa_pago' => date('d-m-Y', strtotime($factura->promesa_pago))
        ]);
    }
    
    public function ImprimirElec($id, $tipo='original', $especialFe = false){
        $tipo1=$tipo;
        
        /**
         * * toma en cuenta que para ver los mismos
         * * datos debemos hacer la misma consulta
         **/
         
        $factura = ($especialFe) ? Factura::where('nonkey', $id)->first() : Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        
        if($factura->tipo == 1){
            view()->share(['title' => 'Imprimir Factura']);
            if ($tipo<>'original') {
                $tipo='Copia Factura de Venta';
            }else{
                $tipo='Factura de Venta Original';
            }
        }elseif($factura->tipo == 3){
            view()->share(['title' => 'Imprimir Cuenta de Cobro']);
            if ($tipo<>'original') {
                $tipo='Cuenta de Cobro Copia';
            }else{
                $tipo='Cuenta de Cobro Original';
            }
        }
        
        
        $resolucion = ($especialFe) ? NumeracionFactura::where('empresa', $factura->empresa)->latest()->first() : NumeracionFactura::where('empresa',Auth::user()->empresa)->latest()->first();
        
        if ($factura) {
            $items = ItemsFactura::where('factura',$factura->id)->get();
            $itemscount=ItemsFactura::where('factura',$factura->id)->count();
            $retenciones = FacturaRetencion::where('factura', $factura->id)->get();
            $ingreso = IngresosFactura::where('factura',$factura->id)->first();
            if($factura->emitida == 1){
                $impTotal = 0;
                foreach ($factura->total()->imp as $totalImp){
                    if(isset($totalImp->total)){
                        $impTotal = $totalImp->total;
                    }
                }
                
                $CUFEvr = $factura->info_cufe($factura->id, $impTotal);
                $infoEmpresa = Empresa::find(Auth::user()->empresa);
                $data['Empresa'] = $infoEmpresa->toArray();
                $infoCliente = Contacto::find($factura->cliente);
                $data['Cliente'] = $infoCliente->toArray();
                
                /*..............................
                Construcción del código qr a la factura
                ................................*/
                
                $impuesto = 0;
                foreach ($factura->total()->imp as $key => $imp) {
                    if(isset($imp->total)){
                        $impuesto = $imp->total;
                    }
                }
                
                $codqr = "NumFac:" . $factura->codigo . "\n" .
                "NitFac:"  . $data['Empresa']['nit']   . "\n" .
                "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
                "FecFac:" . Carbon::parse($factura->created_at)->format('Y-m-d') .  "\n" .
                "HoraFactura" . Carbon::parse($factura->created_at)->format('H:i:s').'-05:00' . "\n" .
                "ValorFactura:" .  number_format($factura->total()->subtotal, 2, '.', '') . "\n" .
                "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
                "ValorOtrosImpuestos:" .  0.00 . "\n" .
                "ValorTotalFactura:" .  number_format($factura->total()->subtotal + $factura->impuestos_totales(), 2, '.', '') . "\n" .
                "CUFE:" . $CUFEvr;
                
                /*..............................
                Construcción del código qr a la factura
                ................................*/
                
                $pdf = PDF::loadView('pdf.electronica', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr','CUFEvr','ingreso'));
            }else{
                $fechaActual = date("Y-m-d", strtotime(Carbon::now()));
                //traemos todas las facturas que el vencimiento haya pasado la fecha actual.
                $facturasVencidas = Factura::where('cliente',$factura->cliente)->where('vencimiento','<',$fechaActual)->get();
                $saldoMesAnterior=0;
                
                //sumamos todo lo que deba el cliente despues de la fecha de vencimiento
                foreach($facturasVencidas as $vencida){
                    $saldoMesAnterior+=$vencida->porpagar();
                }
                
                // return response()->json($factura->estadoCuenta()->saldoMesAnterior);
                
                $codqr = "NumFac:" . $factura->codigo . "\n" .
                "NitFac:"  . "121234234"   . "\n" .
                "DocAdq:" .  "121234234" . "\n" .
                "FecFac:" . Carbon::parse($factura->created_at)->format('Y-m-d') .  "\n" .
                "HoraFactura" . Carbon::parse($factura->created_at)->format('H:i:s').'-05:00' . "\n" .
                "ValorFactura:" .  number_format($factura->total()->subtotal, 2, '.', '') . "\n" .
                "ValorIVA:" .  number_format(12000, 2, '.', '') . "\n" .
                "ValorOtrosImpuestos:" .  0.00 . "\n" .
                "ValorTotalFactura:" .  number_format($factura->total()->subtotal + $factura->impuestos_totales(), 2, '.', '') . "\n";
                //   return view('pdf.electronica', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr'));
                $pdf = PDF::loadView('pdf.electronica', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones','resolucion','codqr','ingreso'));
            }
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf']);
        }
    }
    
    public function exportData(){
        $facturas =  Factura::join('contactos as c','c.id','factura.cliente')
        ->join('items_factura as if','if.factura','=','factura.id')
        ->select('factura.*','c.nombre','c.direccion', 'c.nit', 'c.celular','c.email','if.ref','if.precio','if.descripcion as nombreItem')
        ->get();
         $objPHPExcel = new PHPExcel();
         
        $tituloReporte = "Reporte de Contactos de Facturas";
        $titulosColumnas = array(
            'cliente',
            'cedula',
            'fecha',
            'vencimiento',
            'item',
            'ref',
            'email',
            'precio',
            'direccion',
            'Telefono',
            
        );
        $letras = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z'
        );

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
            ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
            ->setTitle("Reporte Excel Contactos") // Titulo
            ->setSubject("Reporte Excel Contactos") //Asunto
            ->setDescription("Reporte de Contactos") //Descripci���n
            ->setKeywords("reporte Contactos") //Etiquetas
            ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', $tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:C2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'Fecha ' . date('d-m-Y')); // Titulo del reporte

        $estilo = array(
            'font' => array('bold' => true, 'size' => 12, 'name' => 'Times New Roman'),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:V3')->applyFromArray($estilo);

        $estilo = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'd08f50')
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:V3')->applyFromArray($estilo);


        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        $i = 4;
        $letra = 0;
 
        $empresa = Empresa::find(Auth::user()->empresa);
        foreach ($facturas as $factura) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0] . $i,  $factura->nombre)
                ->setCellValue($letras[1] . $i, $factura->nit)
                ->setCellValue($letras[2] . $i, $factura->fecha)
                ->setCellValue($letras[3] . $i, $factura->vencimiento)
                ->setCellValue($letras[4] . $i, $factura->nombreItem)
                ->setCellValue($letras[5] . $i, $factura->ref)
                ->setCellValue($letras[6] . $i, $factura->email)
                ->setCellValue($letras[7] . $i, $factura->precio)
                ->setCellValue($letras[8] . $i, $factura->direccion)
                ->setCellValue($letras[9] . $i, $factura->celular);
                
                
            $i++;
        }

        $estilo = array(
            'font' => array('size' => 12, 'name' => 'Times New Roman'),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:V' . $i)->applyFromArray($estilo);

        for ($i = 'A'; $i <= $letras[20]; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(true);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Contactos');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Contactos.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function updateContratoId(){
        $facturas = Factura::all();

        foreach($facturas as $factura){
            //a cada favtura vamos a buscarle su contrato si tiene se le asocia la nueva id si no se salta
            $contrato = Contrato::where('client_id',$factura->cliente)->first();

            if($contrato){
                $factura->contrato_id = $contrato->id;
                $factura->save();
            }
        }

        return "Actualizacion lista";
    }

    public function xmlFacturaVentaMasivo($id)
    {
        $FacturaVenta = Factura::find($id);

        if (!$FacturaVenta) {
            return redirect('/empresa/facturas')->with('error', "No se ha encontrado la factura de venta, comuniquese con soporte.");
        }

        $FacturaVenta->emitida = $FacturaVenta->emitida;
        $FacturaVenta->save();

        $ResolucionNumeracion = NumeracionFactura::where('empresa', Auth::user()->empresa)->where('num_equivalente', 0)->where('tipo',2)->where('preferida', 1)->first();

        $infoEmpresa = Auth::user()->empresaObj;
        $data['Empresa'] = $infoEmpresa->toArray();

        $retenciones = FacturaRetencion::where('factura', $FacturaVenta->id)->get();

        $impTotal = 0;

        foreach ($FacturaVenta->total()->imp as $totalImp) {
            if (isset($totalImp->total)) {
                $impTotal += $totalImp->total;
            }
        }
        $items = ItemsFactura::where('factura', $id)->get();

        $decimal = explode(".", $impTotal);
        if (
            isset($decimal[1]) && $decimal[1] >= 50 || isset($decimal[1]) && $decimal[1] == 5 || isset($decimal[1]) && $decimal[1] == 4
            || isset($decimal[1]) && $decimal[1] == 3 || isset($decimal[1]) && $decimal[1] == 2 || isset($decimal[1]) && $decimal[1] == 1
        ) {
            $impTotal = round($impTotal);
        } else {
            $impTotal = round($impTotal);
        }


        $CUFEvr = $FacturaVenta->info_cufe($FacturaVenta->id, $impTotal);

        $infoCliente = Contacto::find($FacturaVenta->cliente);
        $data['Cliente'] = $infoCliente->toArray();


        $responsabilidades_empresa = DB::table('empresa_responsabilidad as er')
            ->join('responsabilidades_facturacion as rf', 'rf.id', '=', 'er.id_responsabilidad')
            ->select('rf.*')
            ->where('er.id_empresa', '=', Auth::user()->empresa)->where('er.id_responsabilidad', 5)
            ->orWhere('er.id_responsabilidad', 7)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 12)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 20)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 29)->where('er.id_empresa', '=', Auth::user()->empresa)->get();

        //-- Construccion del pdf a enviar con el código qr + el envío del archivo xml --//
        if ($FacturaVenta) {
            $emails = $FacturaVenta->cliente()->email;
            if ($FacturaVenta->cliente()->asociados('number') > 0) {
                $email = $emails;
                $emails = array();
                if ($email) {
                    $emails[] = $email;
                }
                foreach ($FacturaVenta->cliente()->asociados() as $asociado) {
                    if ($asociado->notificacion == 1 && $asociado->email) {
                        $emails[] = $asociado->email;
                    }
                }
            }

            $tituloCorreo =  $data['Empresa']['nit'] . ";" . $data['Empresa']['nombre'] . ";" . $FacturaVenta->codigo . ";01;" . $data['Empresa']['nombre'];

            $isImpuesto = 1;

            //-- Generación del XML a enviar a la DIAN -- //
            $xml = view('templates.xml.01', compact('CUFEvr', 'ResolucionNumeracion', 'FacturaVenta', 'data', 'items', 'retenciones', 'responsabilidades_empresa', 'emails', 'impTotal', 'isImpuesto'));

            //-- Envío de datos a la DIAN --//
            $res = $this->EnviarDatosDian($xml);

            //-- Decodificación de respuesta de la DIAN --//
            $res = json_decode($res, true);



            if (isset($res['errorType'])) {
                if ($res['errorType'] == "KeyError") {
                    return back()->with('message_denied', "La dian está presentando problemas para emitir documentos electrónicos, inténtelo más tarde.");
                }
            }

            if (!isset($res['statusCode']) && isset($res['message'])) {
                return redirect('/empresa/facturas')->with('message_denied', $res['message']);
            }

            $statusCode = $res['statusCode'] ?? null; //200

            if (!isset($statusCode)) {
                return back()->with('message_denied', isset($res['message']) ? $res['message'] : 'Error en la emisión del docuemento, intente nuevamente en un momento');
            }

            //-- Guardamos la respuesta de la dian solo cuando son errores--//
            if ($statusCode != 200) {
                $FacturaVenta->dian_response = $res['statusCode'] ?? null;
                $FacturaVenta->save();
            }

            //-- Validación 1 del status code (Cuando hay un error) --//
            if ($statusCode != 200) {
                $message = $res['errorMessage'];
                $errorReason = $res['errorReason'];

                //Validamos si depronto la factura fue emitida pero no quedamos con ningun registro de ella.
                $saveNoJson = $statusJson = $this->validateStatusDian(auth()->user()->empresaObj->nit, $FacturaVenta->codigo, "01", $ResolucionNumeracion->prefijo);

                $statusJson = json_decode($statusJson, true);

                if ($statusJson["statusCode"] == 200) {

                    //linea comentada por ahorro de espacio en bd, ay que esta información de las facturas procesadas se puede obtener mediante consulta api.
                    // $FacturaVenta->dian_response = $saveNoJson;
                    $message = "Factura emitida correctamente por validación";
                    $FacturaVenta->emitida = 1;
                    $FacturaVenta->fecha_expedicion = Carbon::now();

                    //Llave unica para acceso por correo
                    $key = Hash::make(date("H:i:s"));
                    $toReplace = array('/', '$', '.');
                    $key = str_replace($toReplace, "", $key);
                    $FacturaVenta->nonkey = $key;

                    $FacturaVenta->save();

                    $this->generateXmlPdfEmail($statusJson['document'], $FacturaVenta, $emails, $data, $CUFEvr, $items, $ResolucionNumeracion, $tituloCorreo);
                } else {
                    return back()->with('message_denied', $message)->with('errorReason', $errorReason);
                }
            }

            $document = $res['document'];

            //-- estátus de que la factura ha sido aprobada --//
            if ($statusCode == 200) {

                //Llave unica para acceso por correo
                $key = Hash::make(date("H:i:s"));
                $toReplace = array('/', '$', '.');
                $key = str_replace($toReplace, "", $key);
                $FacturaVenta->nonkey = $key;
                $FacturaVenta->save();
                //

                $message = "Factura emitida correctamente";
                $FacturaVenta->emitida = 1;
                $FacturaVenta->fecha_expedicion = Carbon::now();
                $FacturaVenta->save();

                $this->generateXmlPdfEmail($document, $FacturaVenta, $emails, $data, $CUFEvr, $items, $ResolucionNumeracion, $tituloCorreo);
            }
        }
    }

    public function downloadEfecty(){
        $valor = 0;
        $facturas = Factura::query()
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->leftJoin('contracts as cs', 'c.id', '=', 'cs.client_id')
            ->leftJoin('vendedores as v', 'factura.vendedor', '=', 'v.id')
            ->select('factura.id',
                DB::raw('c.nit as referencia'),
                DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as valor'),
                'factura.created_at',
                DB::raw('c.nombre as campo1'),
                DB::raw('c.apellido1 as campo2'),
                DB::raw('c.apellido2 as campo3'),
                'factura.codigo as campo5')
            ->groupBy('factura.id')
            ->where('factura.estatus', 1)
            ->get();

        $filePath = "efecty-".date('dmYHi').".txt";
        $file = fopen($filePath, "w");
        fputs($file, '"01"|REFERENCIA|VALOR|FECHA|CAMPO1|CAMPO2|CAMPO3|CAMPO4|CAMPO5'.PHP_EOL);
        foreach($facturas as $factura){
            $campo1 = ($factura->campo1) ? $factura->campo1 : 'NA';
            $campo2 = ($factura->campo2) ? $factura->campo2 : 'NA';
            $campo3 = ($factura->campo3) ? $factura->campo3 : 'NA';

            fputs($file, '"02"|"'.$factura->referencia.'"|'.round($factura->total()->total).'|'.$factura->created_at.'|"'.$campo1.'"|"'.$campo2.'"|"'.$campo3.'"|"NA"|"'.$factura->campo5.'"'.PHP_EOL);
            $valor += $factura->total()->total;
        }
        fputs($file, '"03"|'.count($facturas).'|'.round($valor).'|'.date('Y-m-d H:i:s').'|||||'.PHP_EOL);
        fclose($file);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($filePath));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function whatsapp($id){
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'WHATSAPP')->where('status', 1)->first();
        if($servicio){
            if($servicio->api_key && $servicio->numero){
                $factura = Factura::find($id);
                $plantilla = Plantilla::where('empresa', Auth::user()->empresa)->where('clasificacion', 'Facturacion')->where('tipo', 2)->where('status', 1)->get()->last();

                if($plantilla){
                    $mensaje = str_replace('{{ $company }}', Auth::user()->empresa()->nombre, $plantilla->contenido);
                    $mensaje = str_replace('{{ $name }}', ucfirst($factura->cliente()->nombre), $mensaje);
                    $mensaje = str_replace('{{ $factura->codigo }}', $factura->codigo, $mensaje);
                    $mensaje = str_replace('{{ $factura->parsear($factura->total()->total) }}', $factura->parsear($factura->total()->total), $mensaje);
                }else{
                    $mensaje = Auth::user()->empresa()->nombre.", le informa que su factura ha sido generada bajo el Nro. ".$factura->codigo.", por un monto de $".$factura->parsear($factura->total()->total);
                }

                $numero = str_replace('+','',$factura->cliente()->celular);
                $numero = str_replace(' ','',$numero);
                $numero = (substr($numero, 0, 2) == 57) ? $numero : '57'.$numero;

                $url='https://api.callmebot.com/whatsapp.php?source=php&phone=+'.$numero.'&text='.urlencode($mensaje).'&apikey='.$servicio->api_key;

                if($ch = curl_init($url)){
                    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $html = curl_exec($ch);
                    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if($status == 200){
                        return back()->with('success', 'EL MENSAJE VÍA WHATSAPP HA SIDO ENVIADO DE MANERA EXITOSA');
                    }else{
                        return back()->with('danger', 'EL MENSAJE VÍA WHATSAPP NO HA PODIDO SER ENVIADO');
                    }
                }else{
                    return back()->with('danger', 'EL MENSAJE VÍA WHATSAPP NO HA PODIDO SER ENVIADO');
                }
            }else{
                return back()->with('danger', 'DISCULPE, EL SERVICIO CALLMEBOT NO ESTÁ CONFIGURADO. POR FAVOR CONFIGURE PARA DISFRUTAR DEL SERVICIO');
            }
        }else{
            return back()->with('danger', 'DISCULPE, NO POSEE NINGUN SERVICIO DE WHATSAPP HABILITADO. POR FAVOR HABILÍTELO PARA DISFRUTAR DEL SERVICIO');
        }
    }

    public function convertirelEctronica($facturaId){
        $factura = Factura::find($facturaId);
        $num = Factura::where('empresa',1)->orderby('nro','asc')->get()->last();

        if($num){
            $numero = $num->nro + 1;
        }else{
            $numero = 1;
        }
        
        $nro=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('preferida',1)->where('estado',1)->where('tipo',2)->first();

        //Actualiza el nro de inicio para la numeracion seleccionada
        $inicio = $nro->inicio;
        $nro->inicio += 1;
        $nro->save();

        $factura->codigo=$nro->prefijo.$inicio;
        $factura->nro = $numero;
        $factura->numeracion = $nro->id;
        $factura->tipo = 2;
        $factura->fecha=Carbon::now()->format('Y-m-d');
        $factura->save();

        return back()->with('success','Factura con el nuevo código: '.$factura->codigo. ' convertida correctamente.');
    }

    public function emisionMasivaXml($facturas){
        $empresa = Auth::user()->empresa;
        $facturas = explode(",", $facturas);

        for ($i=0; $i < count($facturas) ; $i++) {
            $factura = Factura::where('empresa', $empresa)->where('emitida', 0)->where('tipo',2)->where('modificado', 0)->where('id', $facturas[$i])->first();

            if(isset($factura)){
                $factura->modificado = 1;
                $factura->save();

                $this->xmlFacturaVentaMasivo($factura->id, $empresa);
            }
        }
        return response()->json([
            'success' => true,
            'text'    => 'Emisión masiva de facturas electrónicas temrinada',
        ]);
    }

    public function exportar(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Facturas de Ventas";
        $titulosColumnas = array('Codigo', 'Fecha', 'Cliente', 'Identificacion', 'Subtotal', 'Impuesto', 'Total', 'Abono', 'Saldo', 'Forma de Pago');

        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific�1�7�1�7�1�7
        ->setTitle("Reporte Excel Factura de Ventas") // Titulo
        ->setSubject("Reporte Excel Factura de Ventas") //Asunto
        ->setDescription("Reporte de Factura de Ventas") //Descripci�1�7�1�7�1�7n
        ->setKeywords("reporte Factura de Ventas") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah�1�7�1�7�1�7 el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:J1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:J2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2','Fecha '.date('d-m-Y')); // Titulo del reporte

        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:J3')->applyFromArray($estilo);

        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:J3')->applyFromArray($estilo);

        $estilo =array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => substr(Auth::user()->empresa()->color,1))
            ),
            'font'  => array(
                'bold'  => true,
                'size'  => 12,
                'name'  => 'Times New Roman',
                'color' => array(
                    'rgb' => 'FFFFFF'
                ),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:J3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i=4;
        $letra=0;

        $facturas = Factura::query()
            ->join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->leftJoin('contracts as cs', 'c.id', '=', 'cs.client_id')
            ->leftJoin('vendedores as v', 'factura.vendedor', '=', 'v.id')
            ->select('factura.tipo','factura.promesa_pago','factura.id', 'factura.correo', 'factura.mensaje', 'factura.codigo', 'factura.nro', DB::raw('c.nombre as nombrecliente'), DB::raw('c.apellido1 as ape1cliente'), DB::raw('c.apellido2 as ape2cliente'), DB::raw('c.email as emailcliente'), DB::raw('c.celular as celularcliente'), DB::raw('c.nit as nitcliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', 'factura.vendedor','factura.emitida', DB::raw('v.nombre as nombrevendedor'),DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'), DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) as pagado'),         DB::raw('(SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant) + (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) - ((Select SUM(pago) from ingresos_factura where factura=factura.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura.id)) - (Select if(SUM(pago), SUM(pago), 0) from notas_factura where factura=factura.id)) as porpagar'))
            ->groupBy('factura.id');

        if($request->codigo!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura.codigo', 'like', "%{$request->codigo}%");
            });
        }
        if($request->cliente!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura.cliente', $request->cliente);
            });
        }
        if($request->corte!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('cs.fecha_corte', $request->corte);
            });
        }
        if($request->creacion!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura.fecha', $request->creacion);
            });
        }
        if($request->vencimiento!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura.vencimiento', $request->vencimiento);
            });
        }
        if($request->estado!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura.estatus', $request->estado);
            });
        }
        if($request->municipio!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('c.fk_idmunicipio', $request->municipio);
            });
        }
        $facturas->where('factura.tipo', $request->tipo)->where('factura.lectura',1);

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $facturas->where('cs.oficina', auth()->user()->oficina);
            }
        }

        $facturas = $facturas->get();
        $moneda = auth()->user()->empresa()->moneda;

        foreach ($facturas as $factura) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $factura->codigo)
                ->setCellValue($letras[1].$i, date('d-m-Y', strtotime($factura->fecha)))
                ->setCellValue($letras[2].$i, $factura->nombrecliente.' '.$factura->ape1cliente.' '.$factura->ape2cliente)
                ->setCellValue($letras[3].$i, $factura->cliente()->tip_iden('true').' '.$factura->nitcliente)
                ->setCellValue($letras[4].$i, $moneda.' '.$factura->parsear(($factura->total - $factura->impuestos_totales())))
                ->setCellValue($letras[5].$i, $moneda.' '.$factura->parsear(($factura->impuestos_totales())))
                ->setCellValue($letras[6].$i, $moneda.' '.$factura->parsear(($factura->total()->total)))
                ->setCellValue($letras[7].$i, $moneda.' '.$factura->parsear(($factura->pagado)))
                ->setCellValue($letras[8].$i, $moneda.' '.$factura->parsear(($factura->porpagar)))
                ->setCellValue($letras[9].$i, ($factura->cuenta_id) ?$factura->formaPago()->nombre:'');
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:J'.$i)->applyFromArray($estilo);

        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        if($request->tipo==1){
            $objPHPExcel->getActiveSheet()->setTitle('Facturas de Ventas');
        }elseif($request->tipo==2){
            $objPHPExcel->getActiveSheet()->setTitle('Facturas de Ventas Electrónicas');
        }

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if($request->tipo==1){
            header('Content-Disposition: attachment;filename="Reporte_Facturas_Ventas.xlsx"');
        }elseif($request->tipo==2){
            header('Content-Disposition: attachment;filename="Reporte_Facturas_Ventas_Electronicas.xlsx"');
        }
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
