<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\Vendedor; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth; 
use Session;

class VendedoresController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'configuracion', 'title' => 'Vendedores', 'icon' =>'']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
 		$vendedores = Vendedor::where('empresa',Auth::user()->empresa)->get();

 		return view('configuracion.vendedores.index')->with(compact('vendedores'));   		
 	}

  /**
  * Formulario para crear un nuevo banco
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nuevo Vendedor']);
    return view('configuracion.vendedores.create'); 
  }

  /**
  * Registrar un nuevo vendedor
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){

      if( Vendedor::where('empresa',auth()->user()->empresa)->count() > 0){
                //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', Vendedor::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
     return redirect('empresa/configuracion/vendedores')->with('success', $mensaje);
    }
      }

      
    $request->validate([
          'nombre' => 'required|max:250',
          'identificacion' => 'required|numeric'
    ]); 
    $vendedor = new Vendedor;
    $vendedor->empresa=Auth::user()->empresa;
    $vendedor->nombre=ucwords(mb_strtolower($request->nombre));
    $vendedor->identificacion=$request->identificacion;
    $vendedor->observaciones=$request->observaciones;
    $vendedor->save();

    $mensaje='Se ha creado satisfactoriamente el vendedor';
    return redirect('empresa/configuracion/vendedores')->with('success', $mensaje)->with('vendedor_id', $vendedor->id);
  }

  /**
  * Formulario para modificar los datos de un vendedor
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $vendedor = Vendedor::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($vendedor) {        
      view()->share(['title' => 'Modificar vendedor']);
      return view('configuracion.vendedores.edit')->with(compact('vendedor'));
    }
    return redirect('empresa/configuracion/vendedores')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos del banco
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $vendedor =Vendedor::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($vendedor) {
      $request->validate([
            'nombre' => 'required|max:250'
      ]); 
      $vendedor->nombre=$request->nombre;
      $vendedor->identificacion=$request->identificacion;
      $vendedor->observaciones=$request->observaciones;
      $vendedor->save();
      $mensaje='Se ha modificado satisfactoriamente el vendedor';
      return redirect('empresa/configuracion/vendedores')->with('success', $mensaje)->with('vendedor_id', $vendedor->id);

    }
    return redirect('empresa/configuracion/vendedores')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar un banco
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $vendedor=Vendedor::where('empresa',Auth::user()->empresa)->where('id', $id)->first(); 
    if ($vendedor->usado()==0) {
      $vendedor->delete();
    }  
    return redirect('empresa/configuracion/vendedores')->with('success', 'Se ha eliminado el vendedor');
  }

  /**
  * Funcion para cambiar el estatus de la numeracion
  * @param int $id
  * @return redirect
  */
  public function act_desc($id){
    $vendedor = Vendedor::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($vendedor) {        
        if ($vendedor->estado==1) {
          $mensaje='Se ha desactivado el vendedor';
          $vendedor->estado=0;
          $vendedor->save();
        }
        else{
          $mensaje='Se ha activado el vendedor';
          $vendedor->estado=1;
          $vendedor->save();
        }
      return redirect('empresa/configuracion/vendedores')->with('success', $mensaje)->with('vendedor_id', $vendedor->id);
    }
    return redirect('empresa/configuracion/vendedores')->with('success', 'No existe un registro con ese id');
  }



 

}