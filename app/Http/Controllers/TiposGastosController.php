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

use App\User;
use App\TiposGastos;

class TiposGastosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'configuracion', 'title' => 'Tipos de Gastos', 'icon' => 'fas fa-minus']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        return view('tipos-gastos.index');
    }

    public function tipos_gastos(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $tipos = TiposGastos::query();

        if ($request->filtro == true) {
            switch ($request) {
                case !empty($request->nombre):
                    $tipos->where(function ($query) use ($request) {
                        $query->orWhere('nombre', 'like', "%{$request->nombre}%");
                    });
                    break;
                case !empty($request->descripcion):
                    $tipos->where(function ($query) use ($request) {
                        $query->orWhere('descripcion', 'like', "%{$request->descripcion}%");
                    });
                    break;
                case !empty($request->cta_contable):
                    $tipos->where(function ($query) use ($request) {
                        $query->orWhere('cta_contable', $request->cta_contable);
                    });
                    break;

                case !empty($request->estado):
                    $tipos->where(function ($query) use ($request) {
                        $query->orWhere('estado', 'like', "%{$request->estado}%");
                    });
                    break;
                default:
                    break;
            }
        }

        return datatables()->eloquent($tipos)
            ->editColumn('id', function (TiposGastos $tipo) {
                return "<a href=" . route('tipos-gastos.show', $tipo->id) . ">{$tipo->id}</div></a>";
            })
            ->editColumn('nombre', function (TiposGastos $tipo) {
                return "<a href=" . route('tipos-gastos.show', $tipo->id) . ">{$tipo->nombre}</div></a>";
            })
            ->editColumn('descripcion', function (TiposGastos $tipo) {
                return $tipo->descripcion;
            })
            ->editColumn('estado', function (TiposGastos $tipo) {
                return "<span class='text-{$tipo->estado("true")}'><strong>{$tipo->estado()}</strong></span>";
            })
            ->editColumn('created_by', function (TiposGastos $tipo) {
                return $tipo->created_by()->nombres;
            })
            ->addColumn('acciones', $modoLectura ?  "" : "tipos-gastos.acciones")
            ->rawColumns(['acciones', 'nombre', 'id', 'estado'])
            ->toJson();
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Tipo de Gasto']);
        return view('tipos-gastos.create');
    }
    
    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|max:250'
        ]);
        
        $tipo = new TiposGastos;
        $tipo->nombre = $request->nombre;
        $tipo->descripcion = $request->descripcion;
        $tipo->created_by = Auth::user()->id;
        $tipo->save();

        $mensaje='SE HA CREADO SATISFACTORIAMENTE EL TIPO DE GASTO';
        return redirect('empresa/tipos-gastos')->with('success', $mensaje);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $tipo = TiposGastos::find($id);

        if ($tipo) {
            view()->share(['title' => $tipo->nombre]);
            return view('tipos-gastos.show')->with(compact('tipo'));
        }
        return redirect('empresa/tipos-gastos')->with('danger', 'TIPO GASTO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $tipo = TiposGastos::find($id);
        
        if ($tipo) {
            view()->share(['title' => 'Editar: '.$tipo->nombre]);
            return view('tipos-gastos.edit')->with(compact('tipo'));
        }
        return redirect('empresa/tipos-gastos')->with('danger', 'TIPO GASTO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $request->validate([
            'nombre' => 'required|max:250'
        ]);
        
        $tipo = TiposGastos::find($id);
        
        if ($tipo) {
            $tipo->nombre = $request->nombre;
            $tipo->descripcion = $request->descripcion;
            $tipo->updated_by = Auth::user()->id;
            $tipo->save();
            
            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE EL TIPO DE GASTO';
            return redirect('empresa/tipos-gastos')->with('success', $mensaje);
        }
        return redirect('empresa/tipos-gastos')->with('danger', 'TIPO GASTO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function destroy($id){
        $tipo = TiposGastos::find($id);
        
        if($tipo){
            $tipo->delete();
            $mensaje = 'SE HA ELIMINADO EL TIPO GASTO CORRECTAMENTE';
            return redirect('empresa/tipos-gastos')->with('success', $mensaje);
        }else{
            return redirect('empresa/tipos-gastos')->with('danger', 'TIPO GASTO NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
    
    public function act_des($id){
        $tipo = TiposGastos::find($id);
        
        if($tipo){
            if($tipo->estado == 0){
                $tipo->estado = 1;
                $mensaje = 'SE HA HABILITADO EL TIPO GASTO CORRECTAMENTE';
            }else{
                $tipo->estado = 0;
                $mensaje = 'SE HA DESHABILITADO EL TIPO GASTO CORRECTAMENTE';
            }
            $tipo->save();
            return redirect('empresa/tipos-gastos')->with('success', $mensaje);
        }else{
            return redirect('empresa/tipos-gastos')->with('danger', 'TIPO GASTO NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
}
