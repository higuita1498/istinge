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

use App\Oficina;
use App\User;
use App\Campos;

class OficinasController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'oficina', 'title' => 'Oficinas', 'icon' => 'fas fa-store-alt']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
      $tabla = Campos::where('modulo', 17)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
      view()->share(['middel' => true]);
      return view('oficinas.index')->with(compact('tabla'));
    }

    public function oficina(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $oficinas = Oficina::query()->where('empresa', Auth::user()->empresa);

        if ($request->filtro == true) {
            if($request->nombre){
                $oficinas->where(function ($query) use ($request) {
                    $query->orWhere('nombre', 'like', "%{$request->nombre}%");
                });
            }
            if($request->direccion){
                $oficinas->where(function ($query) use ($request) {
                    $query->orWhere('direccion', 'like', "%{$request->direccion}%");
                });
            }
            if($request->telefono){
                $oficinas->where(function ($query) use ($request) {
                    $query->orWhere('telefono', 'like', "%{$request->telefono}%");
                });
            }
            if($request->status){
                $status = ($request->status == 'A') ? 0 : $request->status;
                $oficinas->where(function ($query) use ($request, $status) {
                    $query->orWhere('status', $status);
                });
            }
        }

        return datatables()->eloquent($oficinas)
        ->editColumn('nombre', function (Oficina $oficina) {
            return $oficina->nombre;
        })
        ->editColumn('direccion', function (Oficina $oficina) {
            return $oficina->direccion;
        })
        ->editColumn('telefono', function (Oficina $oficina) {
            return $oficina->telefono;
        })
        ->editColumn('status', function (Oficina $oficina) {
            return "<span class='text-{$oficina->status("true")}'><strong>{$oficina->status()}</strong></span>";
        })
        ->addColumn('acciones', $modoLectura ?  "" : "oficinas.acciones")
        ->rawColumns(['acciones', 'nombre', 'status'])
        ->toJson();
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nueva Oficina']);
        return view('oficinas.create');
    }
    
    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|max:200',
        ]);

        $ap = new Oficina;
        $ap->nombre      = $request->nombre;
        $ap->direccion    = $request->direccion;
        $ap->telefono    = $request->telefono;
        $ap->created_by  = Auth::user()->id;
        $ap->empresa     = Auth::user()->empresa;
        $ap->save();

        $mensaje='SE HA CREADO SATISFACTORIAMENTE LA OFICINA';
        return redirect('empresa/oficinas')->with('success', $mensaje);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $oficina = Oficina::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($oficina) {
            view()->share(['title' => $oficina->nombre]);
            return view('oficinas.show')->with(compact('oficina'));
        }
        return redirect('empresa/oficinas')->with('danger', 'OFICINA NO ENCONTRADA, INTENTE NUEVAMENTE');
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $oficina = Oficina::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        
        if ($oficina) {
            view()->share(['title' => 'Editar oficina: '.$oficina->nombre]);
            return view('oficinas.edit')->with(compact('oficina'));
        }
        return redirect('empresa/oficinas')->with('danger', 'OFICINA NO ENCONTRADA, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $oficina = Oficina::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        
        if ($oficina) {
            $request->validate([
                'nombre' => 'required|max:200',
            ]);

            $oficina->nombre      = $request->nombre;
            $oficina->direccion    = $request->direccion;
            $oficina->telefono    = $request->telefono;
            $oficina->updated_by  = Auth::user()->id;
            $oficina->empresa     = Auth::user()->empresa;
            $oficina->save();
            
            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE LA OFICINA';
            return redirect('empresa/oficinas')->with('success', $mensaje);
        }
        return redirect('empresa/oficinas')->with('danger', 'OFICINA NO ENCONTRADA, INTENTE NUEVAMENTE');
    }
    
    public function destroy($id){
        $oficina = Oficina::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        
        if($oficina){
            $oficina->delete();
            $mensaje = 'SE HA ELIMINADO LA OFICINA CORRECTAMENTE';
            return redirect('empresa/oficinas')->with('success', $mensaje);
        }else{
            return redirect('empresa/oficinas')->with('danger', 'OFICINA NO ENCONTRADA, INTENTE NUEVAMENTE');
        }
    }
    
    public function status($id){
        $oficina = Oficina::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        
        if($oficina){
            if($oficina->status == 0){
                $oficina->status = 1;
                $mensaje = 'SE HA HABILITADO LA OFICINA CORRECTAMENTE';
            }else{
                $oficina->status = 0;
                $mensaje = 'SE HA DESHABILITADO LA OFICINA CORRECTAMENTE';
            }
            $oficina->updated_by  = Auth::user()->id;
            $oficina->save();
            return redirect('empresa/oficinas')->with('success', $mensaje);
        }else{
            return redirect('empresa/oficinas')->with('danger', 'OFICINA NO ENCONTRADA, INTENTE NUEVAMENTE');
        }
    }
}
