<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\TipoEmpresa; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth; 
use Session;

class TiposEmpresaController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'configuracion', 'title' => 'Tipos de Contacto', 'icon' =>'']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
 		$tipos = TipoEmpresa::where('empresa',Auth::user()->empresa)->get();

 		return view('configuracion.tiposempresa.index')->with(compact('tipos'));   		
 	}

  /**
  * Formulario para crear un nuevo tipo de empresa
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nuevo Tipo de Empresa']);
    return view('configuracion.tiposempresa.create'); 
  }

  /**
  * Registrar un nuevo tipo de empresa
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
      
       if( TipoEmpresa::where('empresa',auth()->user()->empresa)->count() > 0){
            //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', TipoEmpresa::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
    $sw = 1;

    //Recorremos la sesion para obtener la fecha
    foreach (Session::get('posttimer') as $key) {
      if ($sw == 1) {
        $ultimoingreso = $key;
        $sw=0;
      }
    }

//Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creación
    $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);

//Si el tiempo es de menos de 30 segundos mandamos al listado general
    if ($diasDiferencia <= 10) {
      $mensaje = "El formulario ya ha sido enviado.";
     return redirect('empresa/configuracion/tiposempresa')->with('success', $mensaje);
    }
       }
      
    $request->validate([
      'nombre' => 'required|max:200'
    ]); 
    $tipo = new TipoEmpresa;
    $tipo->empresa=Auth::user()->empresa;
    $tipo->nombre=$request->nombre;
    $tipo->descripcion=$request->descripcion;
    $tipo->save();

    $mensaje='Se ha creado satisfactoriamente el Tipo de Empresa';
    return redirect('empresa/configuracion/tiposempresa')->with('success', $mensaje)->with('tipo_id', $tipo->id);
  }

    public function storeback(Request $request)
  {
      $preApp = app('url')->previous();
    $request->validate([
      'nombre' => 'required|max:200'
    ]);
    $tipo = new TipoEmpresa;
    $tipo->empresa=Auth::user()->empresa;
    $tipo->nombre=$request->nombre;
    $tipo->descripcion=$request->descripcion;
    $tipo->save();

    $mensaje='Se ha creado satisfactoriamente el Tipo de Empresa';
    //return redirect('empresa/configuracion/tiposempresa')->with('success', $mensaje)->with('tipo_id', $tipo->id);
  $type = TipoEmpresa::all()->last()->id;
    return redirect()->to($preApp.'?'.http_build_query(['cnt' => $type]))->withInput()->with("success-newtypecontact", "Nuevo tipo Contacto Creado satisfactoriamente");
  }

  /**
  * Formulario para modificar los datos de un tipo de empresa
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $tipo = TipoEmpresa::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($tipo) {        
      view()->share(['title' => 'Modificar Tipo de Empresa']);
      return view('configuracion.tiposempresa.edit')->with(compact('tipo'));
    }
    return redirect('empresa/configuracion/tiposempresa')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos del tipo de empresa
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $tipo =TipoEmpresa::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($tipo) {
      $request->validate([
        'nombre' => 'required|max:200'
      ]);
      $tipo->nombre=$request->nombre;
      $tipo->descripcion=$request->descripcion;
      $tipo->save();
      $mensaje='Se ha modificado satisfactoriamente el Tipo de Empresa';
      return redirect('empresa/configuracion/tiposempresa')->with('success', $mensaje)->with('tipo_id', $tipo->id);

    }
    return redirect('empresa/configuracion/tiposempresa')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar un tipo de empresa
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $tipo=TipoEmpresa::where('empresa',Auth::user()->empresa)->where('id', $id)->first(); 
    if ($tipo->usado()==0) {
      $tipo->delete();
    }  
    return redirect('empresa/configuracion/tiposempresa')->with('success', 'Se ha eliminado el Tipo de Empresa');
  }


}