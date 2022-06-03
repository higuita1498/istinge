<?php

namespace App\Http\Controllers;
use DB;
use Auth;
use App\Puc;
use App\Contacto;
use App\Numeracion;
use App\PucMovimiento;
use App\Campos;
use App\TipoEmpresa;

use Illuminate\Http\Request;

class SaldosInicialesController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
        view()->share(['seccion' => 'Saldos Iniciales', 'title' => 'Saldos Iiniciales', 'icon' =>'fas fa-plus', 'subseccion' => 'saldos']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $movimientos = PucMovimiento::where('empresa',Auth::user()->empresa)->groupBy('nro')->get();
        // return $movimientos;

        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Saldos Iniciales', 'subseccion' => 'Saldos Iniciales']);
        $busqueda=false;
        if ($request->name_1 || $request->name_2 || $request->name_3|| isset($request->name_4) || $request->name_5) {
            $busqueda='contactos.clientes';
        }
        $tipo='/0';
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $movimientos=$this->busqueda($request, [0,2]);
        $totalContactos = Contacto::where('empresa',Auth::user()->empresa)->count();
        $contactos = Contacto::where('empresa',Auth::user()->empresa)->get();
        $tipo_usuario = 0;
        $tabla = Campos::where('modulo', 1)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
        // return $tabla;
        view()->share(['middel' => true]);
        return view('saldosiniciales.index')->with(compact('contactos','totalContactos','tipo_usuario','tabla'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        view()->share(['seccion' => 'saldosiniciales', 'title' => 'Saldos Iniciales', 'icon' =>'fas fa-list-ul']);
        $this->getAllPermissions(Auth::user()->id);
        
        $tipos = DB::table('tipo_comprobante')->get();
        $puc = Puc::where('empresa',auth()->user()->empresa)
        ->whereRaw('length(codigo) > 6')
        ->get();
        $movimientos = Contacto::where('empresa',auth()->user()->empresa)->get();

        return view('saldosiniciales.create',compact('tipos','puc','contactos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request;

        PucMovimiento::saldoInicial($request);
        return back()->with('success','Se ha creado correctamente el movimiento contable');
    }

    public function show($nro){

        $this->getAllPermissions(Auth::user()->id);
        /*
        obtenemos los movimientoss que ha tenido este documento
        sabemos que se trata de un tipo de movimiento 999 (saldos iniciales)
        */
        $movimientos = PucMovimiento::where('nro',$nro)->where('tipo_comprobante',999)->get();
        if(count($movimientos) == 0){
            return back()->with('error', 'No se pudo encontrar el documento: ' . $nro . ".");
        }

        view()->share(['title' => 'Detalle Movimiento ' .$nro]);
        return view('saldosiniciales.show')->with(compact('movimientos','nro'));
    }

    public function edit($nro){
        $this->getAllPermissions(Auth::user()->id);

        $movimientos = PucMovimiento::where('nro',$nro)->where('tipo_comprobante',999)->get();
        if(count($movimientos) == 0){
            return back()->with('error', 'No se pudo encontrar el documento: ' . $nro . ".");
        }

        $movimiento = PucMovimiento::where('nro',$nro)->where('tipo_comprobante',999)->first();

        $tipos = DB::table('tipo_comprobante')->get();
        $puc = Puc::where('empresa',auth()->user()->empresa)
        ->whereRaw('length(codigo) > 6')
        ->get();
        $movimientos = Contacto::where('empresa',auth()->user()->empresa)->get();

        view()->share(['title' => 'Editar movimiento ' .$nro]);

        return view('saldosiniciales.edit',compact('movimientos','tipos','puc','contactos', 'movimiento','nro'));
    }

    public function update(Request $request){
        PucMovimiento::saldoInicial($request,2,$request->nro);
        return back()->with('success','Se ha actualizado correctamente el movimiento contable');
    }

    public function validateCartera(Request $request){

        if(isset($request->pucId)){ 
            $puc = Puc::find($request->pucId);

            $codigo = substr($puc->codigo, 0, 4);
            
            $grupo = Puc::where('codigo',$codigo)->first();

            if($grupo){
                if($grupo->id_grupo == 38 || $grupo->id_grupo == 39){
                    return response()->json(true);
                }else{
                    return response()->json(false);
                }
            }else{
                return response()->json(false);  
            }
            
        }else{
            return response()->json(false);
        }
    }

    public function saldos(Request $request)
    {
        $modoLectura = auth()->user()->modo_lectura();
        $movimientos = PucMovimiento::query();

        if ($request->filtro == true) {
            
        }

        $movimientos->where('puc_movimiento.empresa', auth()->user()->empresa);

        return datatables()->eloquent($movimientos)
            ->editColumn('nro', function (PucMovimiento $mov) {
                return $mov->nro;
            })
            ->editColumn('tipo_comprobante', function (PucMovimiento $mov) {
                return $mov->tipoComprobante()->nombre;
            })
            ->editColumn('codigo_cuenta', function (PucMovimiento $mov) {
                return $mov->codigo_cuenta;
            })
            ->editColumn('cliente', function (PucMovimiento $mov) {
                return $mov->cliente->nombre;
            })
            ->editColumn('detalle', function (PucMovimiento $mov) {
                return $mov->asociadoA();
            })
            ->editColumn('debito', function (PucMovimiento $mov) {
                return $mov->totalDebito()->total;
            })
            ->editColumn('credito', function (PucMovimiento $mov) {
                return $mov->totalCredito()->total;
            })

            ->addColumn('acciones', $modoLectura ?  "" : "saldosiniciales.acciones")
            ->rawColumns(['acciones'])
            ->toJson();
    }

    public function busqueda($request, $tipo = false){
        $this->getAllPermissions(Auth::user()->id);
        $campos = array(
            'id',
            'contactos.nombre',
            'contactos.nit',
            'contactos.telefono1',
            'contactos.tipo_contacto',
            'te.nombre'
        );
        if (!$request->orderby) {
            $request->orderby = 0;
            $request->order = 1;
        }
        $orderby = $campos[$request->orderby];
        $order = $request->order == 1 ? 'DESC' : 'ASC';
        $appends = array('orderby' => $request->orderby, 'order' => $request->order);
        $movimientos = Contacto::join('tipos_empresa as te', 'te.id', '=', 'contactos.tipo_empresa')
            ->leftJoin('vendedores as v', 'contactos.vendedor', '=', 'v.id')
            ->select(
                'contactos.*',
                'te.nombre as tipo_emp',
                DB::raw('v.nombre as nombrevendedor', 'count(contactos.id) as total')
            )
            ->where('contactos.empresa', Auth::user()->empresa)->where('lectura', 0);
        if ($request->name_1) {
            $appends['name_1'] = $request->name_1;
            $movimientos = $movimientos->where('contactos.nombre', 'like', '%' . $request->name_1 . '%');
        }
        if ($request->name_2) {
            $appends['name_2'] = $request->name_2;
            $movimientos = $movimientos->where('contactos.nit', 'like', '%' . $request->name_2 . '%');
        }
        if ($request->name_3) {
            $appends['name_3'] = $request->name_3;
            $movimientos = $movimientos->where('contactos.telefono1', 'like', '%' . $request->name_3 . '%');
        }
        if (isset($request->name_4)) {
            $appends['name_4'] = $request->name_4;
            $movimientos = $movimientos->where('contactos.tipo_contacto', $request->name_4);
        }
        if ($tipo) {
            $movimientos = $movimientos->whereIn('contactos.tipo_contacto', $tipo);
        }
        if ($request->name_5) {
            $appends['name_5'] = $request->name_5;
            $movimientos = $movimientos->where('contactos.tipo_empresa', $request->name_5);
        }
        if ($request->name_6) {
            $appends['name_6'] = $request->name_6;
            $movimientos = $movimientos->where('v.nombre', 'like', '%' . $request->name_6 . '%');
        }
        $movimientos = $movimientos->OrderBy($orderby, $order)->paginate(25)->appends($appends);
        return $movimientos;
    }
}
