<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\CamposExtra; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth; 
use DB;
class CamposPersonalizadosInventarioController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'configuracion', 'title' => 'Campos Extra para Inventario', 'icon' =>'']);
  }

  /**
  * Index para ver los campos extras registrados
  * @return view
  */
  public function index(){
      $this->getAllPermissions(Auth::user()->id);
 		$campos = CamposExtra::where('empresa',Auth::user()->empresa)->get();
 		return view('configuracion.campos.index')->with(compact('campos'));   		
 	}

  /**
  * Vista que permite ordenar los campos extras
  * @return view
  */
  public function organizar(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Organizar Campos']);
    $campos = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->where('tabla', 0)->get();
    $tabla = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->where('tabla', '<>', 0)->orderBy('tabla')->get();

    return view('configuracion.campos.organizar')->with(compact('campos', 'tabla'));       
  }

  /**
  * Funcion que guarda el orden de los campos extras
  * con respeto a la tabla inventarios
  * @return redirect
  */
  public function organizar_store(Request $request){
    DB::table('campos_extra_inventario')->where('tabla', '<>', 0)->update(['tabla'=>0]);
    foreach ($request->table as $key => $value) {
      $campo = CamposExtra::where('empresa',Auth::user()->empresa)->where('id', $value)->first();
      if ($campo) {        
        $campo->tabla=($key+1);
        $campo->save();
      }
    }
    $mensaje='Se ha registrado satisfactoriamente la configuración de la tabla';
    return redirect('empresa/configuracion/personalizar_inventario')->with('success', $mensaje);

  }
  

  /**
  * Formulario para crear un nuevo campo
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nuevo Campo']);
    return view('configuracion.campos.create'); 
  }

  /**
  * Registrar un nuevo campo
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
    $request->validate([
      'nombre' => 'required|max:250',
      'campo' => 'required|max:250',
      'tipo' => 'required|numeric'
    ]); 

    $campo = CamposExtra::where('empresa',Auth::user()->empresa)->where('campo', $request->campo)->first();
    if ($campo) {
      $errors=(object) array();
      $errors->campo='El campo se encuentra ya esta en uso';
      return back()->withErrors($errors)->withInput();
    }
    $campo = new CamposExtra;
    $campo->empresa=Auth::user()->empresa;
    $campo->campo=$request->campo;
    $campo->nombre=$request->nombre;
    $campo->descripcion=$request->descripcion;
    $campo->varchar=$request->varchar;
    $campo->tipo=$request->tipo;
    $campo->default=$request->default;
    $campo->autocompletar=$request->autocompletar;
    $campo->save();
    $mensaje='Se ha creado satisfactoriamente el campo extra';
    return redirect('empresa/configuracion/personalizar_inventario')->with('success', $mensaje)->with('campo_id', $campo->id);
  }

  /**
  * Vista de los datos de un campo
  * @param int $id
  * @return view
  */
  public function show($id){
      $this->getAllPermissions(Auth::user()->id);
    $campo = CamposExtra::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($campo) {        
      view()->share(['title' => 'Modificar Campo']);
      return view('configuracion.campos.show')->with(compact('campo'));
    }
    return redirect('empresa/configuracion/personalizar_inventario')->with('success', 'No existe un registro con ese id');
  }


  /**
  * Formulario para modificar los datos de un campo
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $campo = CamposExtra::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($campo) {        
      view()->share(['title' => 'Modificar Campo']);
      return view('configuracion.campos.edit')->with(compact('campo'));
    }
    return redirect('empresa/configuracion/personalizar_inventario')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos del banco
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $campo =CamposExtra::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($campo) {
      
      $request->validate([
        'nombre' => 'required|max:250',
        'campo' => 'required|max:250',
        'tipo' => 'required|numeric'
      ]); 
      $campos = CamposExtra::where('empresa',Auth::user()->empresa)->where('campo', $request->campo)->where('id', '<>', $id)->first();
      if ($campos) {
        $errors=(object) array();
        $errors->campo='El campo se encuentra ya esta en uso';
        return back()->withErrors($errors)->withInput();
      }

      $campo->campo=$request->campo;
      $campo->nombre=$request->nombre;
      $campo->descripcion=$request->descripcion;
      $campo->varchar=$request->varchar;
      $campo->tipo=$request->tipo;
      $campo->default=$request->default;
      $campo->autocompletar=$request->autocompletar;
      $campo->save();
    $mensaje='Se ha modificado satisfactoriamente el campo extra';
    return redirect('empresa/configuracion/personalizar_inventario')->with('success', $mensaje)->with('campo_id', $campo->id);

    }
    return redirect('empresa/configuracion/personalizar_inventario')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar un campo
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $campo=CamposExtra::where('empresa',Auth::user()->empresa)->where('id', $id)->first(); 
    if ($campo->usado()==0) {
      $campo->delete();
    }  
    return redirect('empresa/configuracion/personalizar_inventario')->with('success', 'Se ha eliminado el campo');
  }

  /**
  * Funcion para cambiar el estatus del campo
  * @param int $id
  * @return redirect
  */
  public function act_desc($id){
    $campo = CamposExtra::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($campo) {        
        if ($campo->status==1) {
          $mensaje='Se ha desactivado el campo extra';
          $campo->status=0;
          $campo->tabla=0;
          $campo->save();
        }
        else{
          $mensaje='Se ha activado el campo extra';
          $campo->status=1;
          $campo->save();
        }
    return redirect('empresa/configuracion/personalizar_inventario')->with('success', $mensaje)->with('campo_id', $campo->id);
    }
    return redirect('empresa/configuracion/personalizar_inventario')->with('success', 'No existe un registro con ese id');
  }



 

}