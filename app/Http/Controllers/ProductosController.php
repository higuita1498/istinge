<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Mail;
use Validator;
use Illuminate\Validation\Rule;
use Auth;
use DB;
use Session;

use App\Producto;
use App\User;
use App\Contrato;
use App\Campos;
use App\Oficina;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\ProductosBodega;

class ProductosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'productos', 'title' => 'Productos', 'icon' => 'far fa-hdd']);
    }

    public function index_asignacion(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 21)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        $clientes = Contrato::join('contactos', 'contracts.client_id', '=', 'contactos.id')->where('contracts.status', 1)->select('contracts.nro', 'contactos.nombre', 'contactos.apellido1', 'contactos.apellido2', 'contactos.nit')->orderBy('contactos.nombre', 'ASC')->get();
        $users = User::where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        view()->share(['invertfalse' => true, 'title' => 'Asignaciones de Productos', 'subseccion' => 'asignaciones_pro']);
        return view('productos.index_asignacion')->with(compact('tabla', 'clientes', 'users'));
    }

    public function index_devolucion(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 21)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        $clientes = Contrato::join('contactos', 'contracts.client_id', '=', 'contactos.id')->where('contracts.status', 1)->select('contracts.nro', 'contactos.nombre', 'contactos.apellido1', 'contactos.apellido2', 'contactos.nit')->orderBy('contactos.nombre', 'ASC')->get();
        $users = User::where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        view()->share(['invertfalse' => true, 'title' => 'Devoluciones de Productos', 'subseccion' => 'devoluciones_pro']);
        return view('productos.index_devolucion')->with(compact('tabla', 'clientes', 'users'));
    }

    public function productos(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $productos = Producto::query()
        ->join('contracts', 'contracts.id', '=', 'productos.contrato')
        ->join('contactos', 'contracts.client_id', '=', 'contactos.id')
        ->select('productos.*', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.apellido1 as c_apellido1', 'contactos.apellido2 as c_apellido2', 'contactos.nit as c_nit')
        ->where('productos.empresa', Auth::user()->empresa);

        if ($request->filtro == true) {
            if($request->nro){
                $productos->where(function ($query) use ($request) {
                    $query->orWhere('productos.nro', $request->nro);
                });
            }
            if($request->cliente){
                $productos->where(function ($query) use ($request) {
                    $query->orWhere('productos.contrato', $request->cliente);
                });
            }
            if($request->desde){
                $productos->where(function ($query) use ($request) {
                    $query->whereDate('productos.created_at', '>=', Carbon::parse($request->desde)->format('Y-m-d'));
                });
            }
            if($request->hasta){
                $productos->where(function ($query) use ($request) {
                    $query->whereDate('productos.created_at', '<=', Carbon::parse($request->hasta)->format('Y-m-d'));
                });
            }
            if($request->tipo){
                $productos->where(function ($query) use ($request) {
                    $query->orWhere('productos.tipo', $request->tipo);
                });
            }
            if($request->status){
                $productos->where(function ($query) use ($request) {
                    $query->orWhere('productos.status', $request->status);
                });
            }
            if($request->producto_venta){
                $productos->where(function ($query) use ($request) {
                    $query->orWhere('productos.venta', $request->producto_venta);
                });
            }
        }

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $productos->where('productos.oficina', auth()->user()->oficina);
            }
        }

        return datatables()->eloquent($productos)
            ->editColumn('nro', function (Producto $producto) {
                if($producto->tipo == 1){
                    return "<a href=" . route('productos.show_asignacion', $producto->id) . ">{$producto->nro}</a>";
                }else{
                    return "<a href=" . route('productos.show_devolucion', $producto->id) . ">{$producto->nro}</a>";
                }
            })
            ->editColumn('cliente', function (Producto $producto) {
                return "<a href=" . route('contactos.show', $producto->c_id) . ">{$producto->c_nombre} {$producto->c_apellido1} {$producto->c_apellido2}</a>";
            })
            ->editColumn('created_at', function (Producto $producto) {
                return date('d-m-Y', strtotime($producto->created_at));
            })
            ->editColumn('created_by', function (Producto $producto) {
                return $producto->created_by()->nombres;
            })
            ->editColumn('status', function (Producto $producto) {
                return '<div class="text-'.$producto->status(true).' font-weight-bold">'.$producto->status().'</div>';
            })
            ->editColumn('venta', function (Producto $producto) {
                return $producto->venta();
            })
            ->addColumn('acciones', $modoLectura ?  "" : "productos.acciones")
            ->rawColumns(['acciones', 'nro', 'cliente', 'created_at', 'created_by', 'status', 'venta'])
            ->toJson();
    }

    public function create_asignacion(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nueva Asignación', 'subseccion' => 'asignaciones_pro']);

        $contratos = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Contrato::join('contactos', 'contracts.client_id', '=', 'contactos.id')->where('contracts.status', 1)->where('contracts.oficina', Auth::user()->oficina)->select('contracts.id', 'contracts.nro', 'contactos.nombre', 'contactos.apellido1', 'contactos.apellido2', 'contactos.nit')->orderBy('contactos.nombre', 'ASC')->get() : Contrato::join('contactos', 'contracts.client_id', '=', 'contactos.id')->where('contracts.status', 1)->select('contracts.id', 'contracts.nro', 'contactos.nombre', 'contactos.apellido1', 'contactos.apellido2', 'contactos.nit')->orderBy('contactos.nombre', 'ASC')->get();
        // $productos = Inventario::join('productos_bodegas as pp', 'pp.producto', '=', 'inventario.id')->where('pp.nro', '>', 0)->where('inventario.type', 'MODEMS')->select('inventario.id', 'inventario.ref', 'inventario.producto')->get();
        $productos = Inventario::join('productos_bodegas as pp', 'pp.producto', '=', 'inventario.id')->where('inventario.type', 'MODEMS')->select('inventario.id', 'inventario.ref', 'inventario.producto')->get();
        $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
        return view('productos.create_asignacion')->with(compact('contratos', 'productos', 'oficinas'));
    }

    public function store_asignacion(Request $request){
        $empresa = Auth::user()->empresa;
        $numero = Producto::where('empresa', $empresa)->where('tipo', 1)->get()->last();
        if($numero){
            $nro = $numero->nro + 1;
        }else{
            $nro = 1;
        }

        $item = Inventario::find($request->producto);

        $producto                    = new Producto;
        $producto->empresa           = $empresa;
        $producto->oficina           = ($request->oficina) ? $request->oficina : null;
        $producto->nro               = $nro;
        $producto->tipo              = 1;
        $producto->producto          = $request->producto;
        $producto->precio            = $item->precio;
        $producto->impuesto          = $item->impuesto;
        $producto->contrato          = $request->contrato;
        $producto->venta             = $request->venta;
        $producto->tipo_pago         = $request->tipo_pago;
        $producto->cuotas            = ($request->tipo_pago==1) ? 1 : $request->cuotas;
        $producto->cuotas_pendientes = ($request->tipo_pago==1) ? 1 : $request->cuotas;
        $producto->status            = ($request->venta==0) ? 3 : 2;
        $producto->created_by        = Auth::user()->id;
        $producto->save();

        $bodega = Bodega::where('empresa', $empresa)->where('status', 1)->first();
        if ($bodega) {
            $ajuste = ProductosBodega::where('empresa', $empresa)->where('bodega', $bodega->id)->where('producto', $request->producto)->first();
            if ($ajuste) {
                $ajuste->nro -= 1;
                $ajuste->save();
            }
        }

        $mensaje = 'SE HA CREADO SATISFACTORIAMENTE LA ASIGNACIÓN DEL PRODUCTO';
        return redirect('empresa/productos/asignacion')->with('success', $mensaje);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $producto = Producto::find($id);

        if ($producto) {
            $titulo = $producto->tipo == 2 ? 'Devolución':'Asignación';
            view()->share(['title' => $titulo.' Nro. '.$producto->nro]);
            return view('productos.show')->with(compact('producto'));
        }
        return redirect('empresa/productos/asignacion')->with('danger', 'ASIGNACIÓN DE PRODUCTO NO ENCONTRADA, INTENTE NUEVAMENTE');
    }

    public function create_devolucion(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nueva Devolución']);

        $contratos = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Producto::join('contracts', 'contracts.id', '=', 'productos.contrato')->join('contactos', 'contracts.client_id', '=', 'contactos.id')->join('inventario', 'inventario.id', '=', 'productos.producto')->where('contracts.oficina', Auth::user()->oficina)->select('inventario.ref', 'inventario.producto', 'productos.id', 'contracts.nro', 'contactos.nombre', 'contactos.apellido1', 'contactos.apellido2', 'contactos.nit')->orderBy('contactos.nombre', 'ASC')->groupBy('productos.contrato')->get() : Producto::join('contracts', 'contracts.id', '=', 'productos.contrato')->join('contactos', 'contracts.client_id', '=', 'contactos.id')->join('inventario', 'inventario.id', '=', 'productos.producto')->select('inventario.ref', 'inventario.producto', 'productos.id', 'contracts.nro', 'contactos.nombre', 'contactos.apellido1', 'contactos.apellido2', 'contactos.nit')->orderBy('contactos.nombre', 'ASC')->groupBy('productos.contrato')->get();
        $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
        return view('productos.create_devolucion')->with(compact('contratos', 'oficinas'));
    }

    public function store_devolucion(Request $request){dd($request->all());
        $empresa = Auth::user()->empresa;
        $numero = Producto::where('empresa', $empresa)->where('tipo', 2)->get()->last();
        if($numero){
            $nro = $numero->nro + 1;
        }else{
            $nro = 1;
        }

        $producto             = new Producto;
        $producto->empresa    = $empresa;
        $producto->oficina    = ($request->oficina) ? $request->oficina : null;
        $producto->nro        = $nro;
        $producto->tipo       = 2;
        $producto->producto   = $request->producto;
        $producto->contrato   = $request->contrato;
        $producto->created_by = Auth::user()->id;
        $producto->save();

        $bodega = Bodega::where('empresa', $empresa)->where('status', 1)->first();
        if ($bodega) {
            $ajuste = ProductosBodega::where('empresa', $empresa)->where('bodega', $bodega->id)->where('producto', $request->producto)->first();
            if ($ajuste) {
                $ajuste->nro += 1;
                $ajuste->save();
            }
        }

        $mensaje = 'SE HA CREADO SATISFACTORIAMENTE LA DEVOLUCIÓN DEL PRODUCTO';
        return redirect('empresa/productos/devolucion')->with('success', $mensaje);
    }
}
