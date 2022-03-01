<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa;
use App\Campos;
use Carbon\Carbon;
use Validator;
use Illuminate\Validation\Rule;
use Auth;
use DB;

class CamposController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        view()->share(['seccion' => 'configuracion', 'title' => 'Organización de Campos', 'icon' =>'']);
    }

    public function index(){

    }

    public function organizar($id){
        $this->getAllPermissions(Auth::user()->id);
        $tabla = Campos::where('modulo', $id)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
        $campos = Campos::where('modulo', $id)->where('estado', 0)->where('empresa', Auth::user()->empresa)->get();

        view()->share(['title' => 'Organizar Tabla '.$tabla[0]->modulo()]);
        return view('configuracion.campos_tabla.organizar')->with(compact('campos', 'tabla', 'id'));
    }

    public function organizar_store(Request $request){
        DB::table('campos')->where('modulo', $request->id)->where('empresa', Auth::user()->empresa)->update(['estado' => 0]);
        foreach ($request->table as $key => $value) {
            $campo = Campos::where('id', $value)->where('empresa', Auth::user()->empresa)->first();
            if ($campo) {
                $campo->orden  = ($key+1);
                $campo->estado = 1;
                $campo->save();
            }
        }
        $mensaje='SE HA REGISTRADO SATISFACTORIAMENTE LA CONFIGURACIÓN DE LA TABLA';
        return redirect('empresa/configuracion/campos/'.$request->id.'/organizar')->with('success', $mensaje);
    }

    public function create(){

    }

    public function store(Request $request){

    }

    public function show($id){

    }

    public function edit($id){

    }

    public function update(Request $request, $id){

    }

    public function destroy($id){

    }

    public function act_desc($id){

    }
}
