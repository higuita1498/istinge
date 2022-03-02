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
use App\Puerto;

class PuertosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'configuracion', 'title' => 'Puertos de Conexión', 'icon' => 'fas fa-project-diagram']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $puertos = Puerto::where('empresa', Auth::user()->empresa)->get();
        return view('configuracion.puertos.index')->with(compact('puertos'));
    }

    public function puertos(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $puertos = Puerto::query()->where('empresa', Auth::user()->empresa);

        return datatables()->eloquent($puertos)
            ->editColumn('nombre', function (Puerto $puerto) {
                return $puerto->nombre;
            })
            ->editColumn('estado', function (Puerto $puerto) {
                return "<span class='text-{$puerto->estado("true")}'><strong>{$puerto->estado()}</strong></span>";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "configuracion.puertos.acciones")
            ->rawColumns(['acciones', 'nombre', 'estado'])
            ->toJson();
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Puerto de Conexión']);
        return view('configuracion.puertos.create');
    }
    
    public function store(Request $request){
        $puerto = new Puerto;
        $puerto->nombre      = $request->nombre;
        $puerto->estado      = $request->estado;
        $puerto->created_by  = Auth::user()->id;
        $puerto->empresa     = Auth::user()->empresa;
        $puerto->save();

        $mensaje='SE HA CREADO SATISFACTORIAMENTE EL PUERTO DE CONEXIÓN';
        return redirect('empresa/configuracion/puertos-conexion')->with('success', $mensaje);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $puerto = Puerto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($puerto) {
            view()->share(['title' => $puerto->nombre]);
            return view('configuracion.puertos.show')->with(compact('puerto'));
        }
        return redirect('empresa/configuracion/puertos-conexion')->with('danger', 'PUERTO DE CONEXIÓN NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $puerto = Puerto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        
        if ($puerto) {
            view()->share(['title' => 'Editar Puerto: '.$puerto->nombre]);
            return view('configuracion.puertos.edit')->with(compact('puerto'));
        }
        return redirect('empresa/configuracion/puertos-conexion')->with('danger', 'PUERTO DE CONEXIÓN NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $puerto = Puerto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        
        if ($puerto) {
            $puerto->nombre      = $request->nombre;
            $puerto->estado      = $request->estado;
            $puerto->updated_by  = Auth::user()->id;
            $puerto->save();
            
            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE EL PUERTO DE CONEXIÓN';
            return redirect('empresa/configuracion/puertos-conexion')->with('success', $mensaje);
        }
        return redirect('empresa/configuracion/puertos-conexion')->with('danger', 'PUERTO DE CONEXIÓN NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function destroy($id){
        $puerto = Puerto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        
        if($puerto){
            $puerto->delete();
            $mensaje = 'SE HA ELIMINADO EL PUERTO DE CONEXIÓN CORRECTAMENTE';
            return redirect('empresa/configuracion/puertos-conexion')->with('success', $mensaje);
        }else{
            return redirect('empresa/configuracion/puertos-conexion')->with('danger', 'PUERTO DE CONEXIÓN NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
    
    public function act_des($id){
        $puerto = Puerto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        
        if($puerto){
            if($puerto->estado == 0){
                $puerto->estado = 1;
                $mensaje = 'SE HA HABILITADO EL PUERTO DE CONEXIÓN CORRECTAMENTE';
            }else{
                $puerto->estado = 0;
                $mensaje = 'SE HA DESHABILITADO EL PUERTO DE CONEXIÓN CORRECTAMENTE';
            }
            $puerto->save();
            return redirect('empresa/configuracion/puertos-conexion')->with('success', $mensaje);
        }else{
            return redirect('empresa/configuracion/puertos-conexion')->with('danger', 'PUERTO DE CONEXIÓN NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
}
