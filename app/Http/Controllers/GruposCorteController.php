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

use App\Mikrotik;
use App\User;
use App\Contrato;
use App\GrupoCorte;
use App\Campos;

class GruposCorteController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'zonas', 'subseccion' => 'grupo_corte', 'title' => 'Grupos de Corte', 'icon' => 'fas fa-project-diagram']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        return view('grupos-corte.index');
    }

    public function grupos(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $grupos = GrupoCorte::query()
            ->where('empresa', Auth::user()->empresa);
        if ($request->filtro == true) {
            if($request->nombre){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('nombre', 'like', "%{$request->nombre}%");
                });
            }
            if($request->fecha_factura){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_factura', 'like', "%{$request->fecha_factura}%");
                });
            }
            if($request->fecha_pago){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_pago', 'like', "%{$request->fecha_pago}%");
                });
            }
            if($request->fecha_corte){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_corte', 'like', "%{$request->fecha_corte}%");
                });
            }
            if($request->fecha_suspension){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_suspension', 'like', "%{$request->fecha_suspension}%");
                });
            }
            if($request->status >= 0){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('status', 'like', "%{$request->status}%");
                });
            }
        }

        return datatables()->eloquent($grupos)
            ->editColumn('id', function (GrupoCorte $grupo) {
                return "<a href=" . route('grupos-corte.show', $grupo->id) . ">{$grupo->id}</div></a>";
            })
            ->editColumn('nombre', function (GrupoCorte $grupo) {
                return "<a href=" . route('grupos-corte.show', $grupo->id) . ">{$grupo->nombre}</div></a>";
            })
            ->editColumn('fecha_factura', function (GrupoCorte $grupo) {
                return ($grupo->fecha_factura == 0) ? 'No aplica' : $grupo->fecha_factura;
            })
            ->editColumn('fecha_pago', function (GrupoCorte $grupo) {
                return ($grupo->fecha_pago == 0) ? 'No aplica' : $grupo->fecha_pago;
            })
            ->editColumn('fecha_corte', function (GrupoCorte $grupo) {
                return ($grupo->fecha_corte == 0) ? 'No aplica' : $grupo->fecha_corte;
            })
            ->editColumn('fecha_suspension', function (GrupoCorte $grupo) {
                return ($grupo->fecha_suspension == 0) ? 'No aplica' : $grupo->fecha_suspension;
            })
            ->editColumn('hora_suspension', function (GrupoCorte $grupo) {
                return date('g:i A', strtotime($grupo->hora_suspension));
            })
            ->editColumn('status', function (GrupoCorte $grupo) {
                return "<span class='text-{$grupo->status("true")}'><strong>{$grupo->status()}</strong></span>";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "grupos-corte.acciones")
            ->rawColumns(['acciones', 'nombre', 'id', 'status'])
            ->toJson();
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Grupo de Corte']);
        return view('grupos-corte.create');
    }
    
    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|max:250',
            'fecha_corte' => 'required|numeric',
            'fecha_suspension' => 'required|numeric',
            'fecha_factura' => 'required|numeric',
            'fecha_pago' => 'required|numeric',
            'hora_suspension' => 'required',
        ]);

        $hora_suspension = explode(":", $request->hora_suspension);
        $hora_suspension_limit = $hora_suspension[0]+2;
        $hora_suspension_limit = $hora_suspension_limit.':'.$hora_suspension[1];
        
        $grupo = new GrupoCorte;
        $grupo->nombre = $request->nombre;
        $grupo->fecha_factura = $request->fecha_factura;
        $grupo->fecha_pago = $request->fecha_pago;
        $grupo->fecha_corte = $request->fecha_corte;
        $grupo->fecha_suspension = $request->fecha_suspension;
        $grupo->hora_suspension = $request->hora_suspension;
        $grupo->hora_suspension_limit = $hora_suspension_limit;
        $grupo->status = $request->status;
        $grupo->created_by = Auth::user()->id;
        $grupo->empresa = Auth::user()->empresa;
        $grupo->save();

        $mensaje='SE HA CREADO SATISFACTORIAMENTE EL GRUPO DE CORTE';
        return redirect('empresa/grupos-corte')->with('success', $mensaje);
    }

    public function storeBack(Request $request){
        $hora_suspension = explode(":", $request->hora_suspension);
        $hora_suspension_limit = $hora_suspension[0]+2;
        $hora_suspension_limit = $hora_suspension_limit.':'.$hora_suspension[1];

        $grupo                   = new GrupoCorte;
        $grupo->nombre           = $request->nombre;
        $grupo->fecha_factura    = $request->fecha_factura;
        $grupo->fecha_pago       = $request->fecha_pago;
        $grupo->fecha_corte      = $request->fecha_corte;
        $grupo->fecha_suspension = $request->fecha_suspension;
        $grupo->hora_suspension  = $request->hora_suspension;
        $grupo->hora_suspension_limit = $hora_suspension_limit;
        $grupo->status           = $request->status;
        $grupo->created_by       = Auth::user()->id;
        $grupo->empresa          = Auth::user()->empresa;
        $grupo->save();

        if ($grupo) {
            $arrayPost['success']    = true;
            $arrayPost['id']         = GrupoCorte::all()->last()->id;
            $arrayPost['suspension'] = GrupoCorte::all()->last()->fecha_suspension;
            $arrayPost['corte']      = GrupoCorte::all()->last()->fecha_corte;
            $arrayPost['nombre']     = GrupoCorte::all()->last()->nombre;
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $grupo = GrupoCorte::find($id);

        if ($grupo) {
            $contratos = Contrato::where('grupo_corte', $grupo->id)->where('empresa', Auth::user()->empresa)->count();
            $tabla = Campos::where('modulo', 2)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
            view()->share(['title' => $grupo->nombre]);
            return view('grupos-corte.show')->with(compact('grupo', 'contratos', 'tabla'));
        }
        return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $grupo = GrupoCorte::find($id);
        
        if ($grupo) {
            view()->share(['title' => 'Editar: '.$grupo->nombre]);
            return view('grupos-corte.edit')->with(compact('grupo'));
        }
        return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $request->validate([
            'nombre' => 'required|max:250',
            'fecha_corte' => 'required|numeric',
            'fecha_suspension' => 'required|numeric',
            'fecha_factura' => 'required|numeric',
            'fecha_pago' => 'required|numeric',
            'hora_suspension' => 'required',
        ]);
        
        $grupo = GrupoCorte::find($id);
        
        if ($grupo) {
            $hora_suspension = explode(":", $request->hora_suspension);
            $hora_suspension_limit = $hora_suspension[0]+2;
            $hora_suspension_limit = $hora_suspension_limit.':'.$hora_suspension[1];

            $grupo->nombre           = $request->nombre;
            $grupo->fecha_factura    = $request->fecha_factura;
            $grupo->fecha_pago       = $request->fecha_pago;
            $grupo->fecha_corte      = $request->fecha_corte;
            $grupo->fecha_suspension = $request->fecha_suspension;
            $grupo->hora_suspension  = $request->hora_suspension;
            $grupo->hora_suspension_limit = $hora_suspension_limit;
            $grupo->status           = $request->status;
            $grupo->updated_by       = Auth::user()->id;
            $grupo->save();
            
            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE EL GRUPO DE CORTE';
            return redirect('empresa/grupos-corte')->with('success', $mensaje);
        }
        return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function destroy($id){
        $grupo = GrupoCorte::find($id);
        
        if($grupo){
            $grupo->delete();
            $mensaje = 'SE HA ELIMINADO EL GRUPO DE CORTE CORRECTAMENTE';
            return redirect('empresa/grupos-corte')->with('success', $mensaje);
        }else{
            return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
    
    public function act_des($id){
        $grupo = GrupoCorte::find($id);
        
        if($grupo){
            if($grupo->status == 0){
                $grupo->status = 1;
                $mensaje = 'SE HA HABILITADO EL GRUPO DE CORTE CORRECTAMENTE';
            }else{
                $grupo->status = 0;
                $mensaje = 'SE HA DESHABILITADO EL GRUPO DE CORTE CORRECTAMENTE';
            }
            $grupo->save();
            return redirect('empresa/grupos-corte')->with('success', $mensaje);
        }else{
            return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
}
