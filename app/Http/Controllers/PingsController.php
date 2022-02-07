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
use App\Nodo;
use App\Ping;

class PingsController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'inicio', 'title' => 'Pings Fallidos', 'icon' => 'fas fa-plug']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $contratos = Contrato::where('status', 1)->get();
        return view('pings.index')->with(compact('contratos'));
    }

    public function pings(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $pings = Ping::query()
        ->where('fecha', date('Y-m-d'));
        return datatables()->eloquent($pings)
            ->editColumn('contrato', function (Ping $ping) {
                return "<a href=" . route('contratos.show', $ping->contrato) . " target='_blank'>".$ping->contrato."</div></a>";
            })
            ->editColumn('ip', function (Ping $ping) {
                return $ping->ip;
            })
            ->editColumn('estado', function (Ping $ping) {
                return $ping->estado;
            })
            ->editColumn('created_at', function (Ping $ping) {
                return $ping->updated_at;
            })
            ->addColumn('acciones', $modoLectura ?  "" : "pings.acciones")
            ->rawColumns(['acciones', 'contrato'])
            ->toJson();
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Access Point']);
        $nodos = Nodo::where('status', 1)->get();
        return view('access-point.create')->with(compact('nodos'));
    }
    
    public function store(Request $request){
        $ap = new AP;
        $ap->nombre = $request->nombre;
        $ap->password = $request->password;
        $ap->modo_red = $request->modo_red;
        $ap->descripcion = $request->descripcion;
        $ap->nodo = $request->nodo;
        $ap->status = $request->status;
        $ap->created_by = Auth::user()->id;
        $ap->save();

        $mensaje='SE HA CREADO SATISFACTORIAMENTE EL ACCESS POINT';
        return redirect('empresa/access-point')->with('success', $mensaje);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $ap = AP::find($id);

        if ($ap) {
            $contratos = Contrato::where('ap', $ap->id)->get();
            view()->share(['title' => $ap->nombre]);
            return view('access-point.show')->with(compact('ap', 'contratos'));
        }
        return redirect('empresa/access-point')->with('danger', 'ACCESS POINT NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $ap = AP::find($id);
        
        if ($ap) {
            view()->share(['title' => 'Editar AP: '.$ap->nombre]);
            $nodos = Nodo::where('status', 1)->get();
            return view('access-point.edit')->with(compact('ap', 'nodos'));
        }
        return redirect('empresa/access-point')->with('danger', 'ACCESS POINT NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $ap = AP::find($id);
        
        if ($ap) {
            $ap->nombre = $request->nombre;
            $ap->password = $request->password;
            $ap->modo_red = $request->modo_red;
            $ap->descripcion = $request->descripcion;
            $ap->nodo = $request->nodo;
            $ap->status = $request->status;
            $ap->updated_by = Auth::user()->id;
            $ap->save();
            
            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE EL ACCESS POINT';
            return redirect('empresa/access-point')->with('success', $mensaje);
        }
        return redirect('empresa/access-point')->with('danger', 'ACCESS POINT NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function destroy($id){
        $ap = AP::find($id);
        
        if($ap){
            $ap->delete();
            $mensaje = 'SE HA ELIMINADO EL ACCESS POINT CORRECTAMENTE';
            return redirect('empresa/access-point')->with('success', $mensaje);
        }else{
            return redirect('empresa/access-point')->with('danger', 'ACCESS POINT NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
    
    public function act_des($id){
        $ap = AP::find($id);
        
        if($ap){
            if($ap->status == 0){
                $ap->status = 1;
                $mensaje = 'SE HA HABILITADO EL ACCESS POINT CORRECTAMENTE';
            }else{
                $ap->status = 0;
                $mensaje = 'SE HA DESHABILITADO EL ACCESS POINT CORRECTAMENTE';
            }
            $ap->save();
            return redirect('empresa/access-point')->with('success', $mensaje);
        }else{
            return redirect('empresa/access-point')->with('danger', 'ACCESS POINT NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
}
