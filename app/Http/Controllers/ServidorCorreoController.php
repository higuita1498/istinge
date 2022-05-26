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

use App\ServidorCorreo;

class ServidorCorreoController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'configuracion', 'title' => 'Servidor de Correo', 'icon' => 'fas fa-envelope']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $servidor = ServidorCorreo::where('empresa', Auth::user()->empresa)->first();
        return view('servidor-correo.index')->with(compact('servidor'));
    }

    public function update(Request $request, $id){
        $servidor = ServidorCorreo::where('empresa', Auth::user()->empresa)->first();
        
        if ($servidor) {
            if($request->estado == 1){
                $request->validate([
                    'servidor'  => 'required',
                    'seguridad' => 'required',
                    'puerto'    => 'required',
                    'usuario'   => 'required',
                    'password'  => 'required',
                    'estado'    => 'required',
                    'address'   => 'required',
                    'name'      => 'required',
                ]);
            }

            $servidor->empresa   = Auth::user()->empresa;
            $servidor->servidor  = $request->servidor;
            $servidor->seguridad = $request->seguridad;
            $servidor->puerto    = $request->puerto;
            $servidor->usuario   = $request->usuario;
            $servidor->password  = $request->password;
            $servidor->estado    = $request->estado;
            $servidor->address   = $request->address;
            $servidor->name      = $request->name;
            $servidor->save();
            $mensaje='SE HA CREADO SATISFACTORIAMENTE LA CONFIGURACIÓN DEL SERVIDOR';
            return redirect('empresa/servidor-correo')->with('success', $mensaje);
        }
    }
}
