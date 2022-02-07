<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; 
use App\Notificacion; 
use Carbon\Carbon; 
use Validator; 
use Illuminate\Validation\Rule;  
use Auth; 
use Session;

class NotificacionesController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'notificaciones', 'title' => 'Notificaciones APP', 'icon' =>'fas fa-comment']);
  }

    public function index(){
        $this->getAllPermissions(Auth::user()->id);
        $notificaciones = Notificacion::all();
 		return view('notificaciones.index')->with(compact('notificaciones'));
 	}
 	
    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        return view('notificaciones.create'); 
    }
    
    public function store(Request $request){
        $request->validate([
            'mensaje' => 'required|max:2000',
            'desde' => 'required',
            'hasta' => 'required',
            'tipo' => 'required',
            'status' => 'required',
        ]);
        $notificacion = new Notificacion;
        $notificacion->tipo = $request->tipo;
        $notificacion->mensaje = $request->mensaje;
        $notificacion->desde = Carbon::parse($request->desde)->format('Y-m-d');
        $notificacion->hasta = Carbon::parse($request->hasta)->format('Y-m-d');
        $notificacion->status = $request->status;
        $notificacion->save();
        $mensaje='Se ha creado satisfactoriamente la notificación';
        return redirect('empresa/notificaciones')->with('success', $mensaje);
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $notificacion = Notificacion::find($id);
        if ($notificacion) {        
            return view('notificaciones.edit')->with(compact('notificacion'));
        }
        return 'No existe un registro con ese id';
    }
    
    public function update(Request $request, $id){
        $notificacion = Notificacion::find($id);
        if ($notificacion) {
            $request->validate([
                'mensaje' => 'required|max:2000',
                'desde' => 'required',
                'hasta' => 'required',
                'tipo' => 'required',
                'status' => 'required'
            ]);
            $notificacion->tipo = $request->tipo;
            $notificacion->mensaje = $request->mensaje;
            $notificacion->desde = Carbon::parse($request->desde)->format('Y-m-d');
            $notificacion->hasta = Carbon::parse($request->hasta)->format('Y-m-d');
            $notificacion->status = $request->status;
            $notificacion->save();
            $mensaje='Se ha modificado satisfactoriamente la notificación';
            return redirect('empresa/notificaciones')->with('success', $mensaje);
        }
        return redirect('master/notificaciones')->with('success', 'No existe un registro con ese id');
    }
    
    public function destroy($id){      
        $notificacion = Notificacion::find($id);
        if ($notificacion) {        
            $notificacion->delete();
        }
        return redirect('empresa/notificaciones')->with('success', 'Se ha eliminado satisfactoriamente la notificación');
    }
    
    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $notificacion = Notificacion::find($id);
        if ($notificacion) {
            return view('notificaciones.show')->with(compact('notificacion'));
        }
        return redirect('master/notificaciones')->with('success', 'No existe un registro con ese id');
    }
    
    public function act_desc($id){
        $notificacion = Notificacion::find($id);
        if ($notificacion) {        
            if ($notificacion->status==1) {
                $mensaje='Se ha desactivado satisfactoriamente la notificación';
                $notificacion->estatus=0;
                $notificacion->save();
            } else {
                $mensaje='Se ha activado satisfactoriamente la notificación';
                $notificacion->estatus=1;
                $notificacion->save();
            }
            return redirect('empresa/notificaciones')->with('success', $mensaje);
        }
        return redirect('empresa/notificaciones')->with('success', 'No existe un registro con ese id');
    }
}