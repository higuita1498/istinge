<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\Impuesto; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth; 
use Session;

class ImpuestosController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'configuracion', 'title' => 'Tipos de Impuestos', 'icon' =>'']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
 		$impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->get();

 		return view('configuracion.impuestos.index')->with(compact('impuestos'));   		
 	}

  /**
  * Formulario para crear un nuevo impuesto
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nuevo Tipo de Impuesto']);
    return view('configuracion.impuestos.create'); 
  }

  /**
  * Registrar un nuevo impuesto
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
      
       if( Impuesto::where('empresa',auth()->user()->empresa)->count() > 0){
      
      //Tomamos el tiempo en el que se crea el registro
        Session::put('posttimer', Impuesto::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
       return redirect('empresa/configuracion/impuestos')->with('success', $mensaje);
  }
       }
      
    $request->validate([
      'nombre' => 'required|max:250',
      'porcentaje' => 'required|numeric',
      'tipo' => 'required|numeric'
    ]); 
    $impuesto = new Impuesto;
    $impuesto->empresa=Auth::user()->empresa;
    $impuesto->nombre=$request->nombre;
    $impuesto->porcentaje=$request->porcentaje;
    $impuesto->tipo=$request->tipo;
    $impuesto->descripcion=$request->descripcion;
    $impuesto->save();

    $mensaje='Se ha creado satisfactoriamente el tipo de impuesto';
    return redirect('empresa/configuracion/impuestos')->with('success', $mensaje)->with('impuesto_id', $impuesto->id);
  }

  /**
  * Formulario para modificar los datos de un impuesto
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $impuesto = Impuesto::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($impuesto) {        
      view()->share(['title' => 'Modificar Tipo de Impuesto']);
      return view('configuracion.impuestos.edit')->with(compact('impuesto'));
    }
    return redirect('empresa/configuracion/impuestos')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos del banco
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $impuesto =Impuesto::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($impuesto) {
      $request->validate([
        'nombre' => 'required|max:250',
        'porcentaje' => 'required|numeric',
        'tipo' => 'required|numeric'
      ]);
      $impuesto->nombre=$request->nombre;
      $impuesto->porcentaje=$request->porcentaje;
      $impuesto->tipo=$request->tipo;
      $impuesto->descripcion=$request->descripcion;
      $impuesto->save();
      $mensaje='Se ha modificado satisfactoriamente el tipo de impuesto';
      return redirect('empresa/configuracion/impuestos')->with('success', $mensaje)->with('impuesto_id', $impuesto->id);

    }
    return redirect('empresa/configuracion/impuestos')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar un impuesto
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $impuesto=Impuesto::where('empresa',Auth::user()->empresa)->where('id', $id)->first(); 
    if ($impuesto->usado()==0) {
      $impuesto->delete();
    }  
    return redirect('empresa/configuracion/impuestos')->with('success', 'Se ha eliminado el tipo de impuesto');
  }

  /**
  * Funcion para cambiar el estatus de la numeracion
  * @param int $id
  * @return redirect
  */
  public function act_desc($id){
    $impuesto = Impuesto::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($impuesto) {        
        if ($impuesto->estado==1) {
          $mensaje='Se ha desactivado el tipo de impuesto';
          $impuesto->estado=0;
          $impuesto->save();
        }
        else{
          $mensaje='Se ha activado el tipo de impuesto';
          $impuesto->estado=1;
          $impuesto->save();
        }
      return redirect('empresa/configuracion/impuestos')->with('success', $mensaje)->with('impuesto_id', $impuesto->id);
    }
    return redirect('empresa/configuracion/impuestos')->with('success', 'No existe un registro con ese id');
  }



 

}