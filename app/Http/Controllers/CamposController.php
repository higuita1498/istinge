<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa;
use App\Campos;
use App\User;
use Carbon\Carbon;
use Validator;
use Illuminate\Validation\Rule;
use Auth;
use DB;

class CamposController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(500);
        view()->share(['seccion' => 'configuracion', 'title' => 'Organización de Campos', 'icon' =>'']);
    }

    public function index(){

    }

    public function organizar($id){
        $this->getAllPermissions(Auth::user()->id);

        $visibles = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->select('campos.*')->where('campos_usuarios.id_modulo', $id)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();

        $ocultos = Campos::where('campos.modulo', $id)
           ->whereNotExists(function ($query) {
               $query->select(DB::raw(1))
                     ->from('campos_usuarios')
                     ->where('campos_usuarios.id_usuario', Auth::user()->id)
                     ->whereColumn('campos_usuarios.id_campo', 'campos.id');
           })
           ->get();
           $visiblesFin = '';
           if(isset($visibles[0])){
               $visiblesFin = $visibles[0]->modulo();
           }
        view()->share(['title' => 'Organizar Tabla '.$visiblesFin]);
        return view('configuracion.campos_tabla.organizar')->with(compact('ocultos', 'visibles', 'id'));
    }

    public function organizar_store(Request $request){
        DB::table('campos_usuarios')->where('id_modulo', $request->id)->where('id_usuario', Auth::user()->id)->delete();
        foreach ($request->table as $key => $value) {
            DB::table('campos_usuarios')->insert(['id_modulo' => $request->id, 'id_usuario' => Auth::user()->id, 'id_campo' => $value, 'orden' => ($key+1), 'estado' => 1]);
        }
        $mensaje='SE HA REGISTRADO SATISFACTORIAMENTE LA CONFIGURACIÓN DE LA TABLA';
        $visibles = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', $request->id)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
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

    public function aplicar(){
        Ini_set('max_execution_time', 500);
        $campos = Campos::all();
        $usuarios = User::all();
        foreach ($usuarios as $usuario) {
            foreach ($campos as $campo) {
                if($campo->orden != null){
                    DB::table('campos_usuarios')->insert([
                        'id_modulo'  => $campo->modulo,
                        'id_usuario' => $usuario->id,
                        'id_campo'   => $campo->id,
                        'orden'      => $campo->orden,
                        'estado'     => $campo->estado
                    ]);
                }
            }
        }
    }
}
