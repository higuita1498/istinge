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
use App\AP;
use App\Campos;

class APController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'zonas', 'subseccion' => 'gestion_ap', 'title' => 'Access Point', 'icon' => 'fas fa-project-diagram']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $nodos = Nodo::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status', 1)->where('empresa', Auth::user()->empresa)->get();

        return view('access-point.index')->with(compact('nodos','aps'));
    }

    public function ap(Request $request){

        $modoLectura = auth()->user()->modo_lectura();
        $aps = AP::query()->where('empresa', Auth::user()->empresa);

        if ($request->filtro == true) {

            if($request->nombre){
                $aps->where(function ($query) use ($request) {
                    $query->orWhere('nombre', 'like', "%{$request->nombre}%");
                });
            }
            if($request->ip){
                dd($request->ip);
                $aps->where(function ($query) use ($request) {
                    $query->orWhere('ip', 'like', "%{$request->ip}%");
                });
            }
            if($request->modo_red){
                $aps->where(function ($query) use ($request) {
                    $query->orWhere('modo_red', 'like', "%{$request->modo_red}%");
                });
            }
            if($request->status >=0){
                $aps->where(function ($query) use ($request) {
                    $query->orWhere('status', 'like', "%{$request->status}%");
                });
            }
        }

        return datatables()->eloquent($aps)
            ->editColumn('nombre', function (AP $ap) {
                return "<a href=" . route('access-point.show', $ap->id) . ">{$ap->nombre}</div></a>";
            })
            ->editColumn('modo_red', function (AP $ap) {
                return $ap->modo_red();
            })
            ->editColumn('nodo', function (AP $ap) {
                return $ap->nodo()->nombre;
            })
            ->editColumn('status', function (AP $ap) {
                return "<span class='text-{$ap->status("true")}'><strong>{$ap->status()}</strong></span>";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "access-point.acciones")
            ->rawColumns(['acciones', 'nombre', 'modo_red', 'status', 'nodo'])
            ->toJson();
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Access Point']);
        $nodos = Nodo::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        return view('access-point.create')->with(compact('nodos'));
    }

    public function store(Request $request){

        $ap = new AP;
        $ap->nombre      = $request->nombre;
        $ap->password    = $request->password;
        $ap->modo_red    = $request->modo_red;
        $ap->descripcion = $request->descripcion;
        $ap->nodo        = $request->nodo;
        $ap->status      = $request->status;
        $ap->ip      = $request->ip;
        $ap->created_by  = Auth::user()->id;
        $ap->empresa     = Auth::user()->empresa;
        $ap->save();

        $mensaje='SE HA CREADO SATISFACTORIAMENTE EL ACCESS POINT';
        return redirect('empresa/access-point')->with('success', $mensaje);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $ap = AP::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($ap) {
            $contratos = Contrato::where('ap', $ap->id)->get();
            $tabla = Campos::where('modulo', 2)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
            view()->share(['title' => $ap->nombre]);
            return view('access-point.show')->with(compact('ap', 'contratos', 'tabla'));
        }
        return redirect('empresa/access-point')->with('danger', 'ACCESS POINT NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $ap = AP::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($ap) {
            view()->share(['title' => 'Editar AP: '.$ap->nombre]);
            $nodos = Nodo::where('status', 1)->get();
            return view('access-point.edit')->with(compact('ap', 'nodos'));
        }
        return redirect('empresa/access-point')->with('danger', 'ACCESS POINT NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $ap = AP::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($ap) {
            $ap->nombre      = $request->nombre;
            $ap->password    = $request->password;
            $ap->modo_red    = $request->modo_red;
            $ap->descripcion = $request->descripcion;
            $ap->nodo        = $request->nodo;
            $ap->status      = $request->status;
            $ap->updated_by  = Auth::user()->id;
            $ap->empresa     = Auth::user()->empresa;
            $ap->save();

            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE EL ACCESS POINT';
            return redirect('empresa/access-point')->with('success', $mensaje);
        }
        return redirect('empresa/access-point')->with('danger', 'ACCESS POINT NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function destroy($id){
        $ap = AP::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if($ap){
            $ap->delete();
            $mensaje = 'SE HA ELIMINADO EL ACCESS POINT CORRECTAMENTE';
            return redirect('empresa/access-point')->with('success', $mensaje);
        }else{
            return redirect('empresa/access-point')->with('danger', 'ACCESS POINT NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }

    public function act_des($id){
        $ap = AP::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

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

    public function state_lote($aps, $state){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $aps = explode(",", $aps);

        for ($i=0; $i < count($aps) ; $i++) {
            $ap = AP::find($aps[$i]);

            if($ap){
                if($state == 'disabled'){
                    $ap->status = 0;
                }elseif($state == 'enabled'){
                    $ap->status = 1;
                }
                $ap->save();
                $succ++;
            }else{
                $fail++;
            }
        }

        return response()->json([
            'success'   => true,
            'fallidos'  => $fail,
            'correctos' => $succ,
            'state'     => $state
        ]);
    }

    public function destroy_lote($aps){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $aps = explode(",", $aps);

        for ($i=0; $i < count($aps) ; $i++) {
            $ap = AP::find($aps[$i]);
            if ($ap->uso()==0) {
                $ap->delete();
                $succ++;
            } else {
                $fail++;
            }
        }

        return response()->json([
            'success'   => true,
            'fallidos'  => $fail,
            'correctos' => $succ,
            'state'     => 'eliminados'
        ]);
    }
}
