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

        $visibles = Campos::where('campos.modulo', $id)
           ->whereExists(function ($query) {
               $query->select(DB::raw(1))
                     ->from('campos_usuarios')
                     ->where('campos_usuarios.id_usuario', Auth::user()->id)
                     ->whereColumn('campos_usuarios.id_campo', 'campos.id');
           })
           ->get();

        $ocultos = Campos::where('campos.modulo', $id)
           ->whereNotExists(function ($query) {
               $query->select(DB::raw(1))
                     ->from('campos_usuarios')
                     ->where('campos_usuarios.id_usuario', Auth::user()->id)
                     ->whereColumn('campos_usuarios.id_campo', 'campos.id');
           })
           ->get();

        //view()->share(['title' => 'Organizar Tabla '.$visibles[0]->modulo()]);
        view()->share(['title' => 'Organizar Tabla '.$visibles[0]->modulo()]);
        return view('configuracion.campos_tabla.organizar')->with(compact('ocultos', 'visibles', 'id'));
    }

    public function organizar_store(Request $request){
        DB::table('campos_usuarios')->where('id_modulo', $request->id)->where('id_usuario', Auth::user()->id)->delete();
        foreach ($request->table as $key => $value) {
            DB::table('campos_usuarios')->insert(['id_modulo' => $request->id, 'id_usuario' => Auth::user()->id, 'id_campo' => $value, 'orden' => ($key+1), 'estado' => 1]);
        }
        $mensaje='SE HA REGISTRADO SATISFACTORIAMENTE LA CONFIGURACIÓN DE LA TABLA';
        $visibles = Campos::where('campos.modulo', $request->id)
           ->whereExists(function ($query) {
               $query->select(DB::raw(1))
                     ->from('campos_usuarios')
                     ->where('campos_usuarios.id_usuario', Auth::user()->id)
                     ->whereColumn('campos_usuarios.id_campo', 'campos.id');
           })
           ->get();
        return redirect('empresa/'.$visibles[0]->modulo('true'))->with('success', $mensaje);
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
