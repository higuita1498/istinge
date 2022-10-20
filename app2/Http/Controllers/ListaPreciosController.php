<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Inventario\ListaPrecios; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth;
use Session;

class ListaPreciosController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['title' => 'Lista de Precios', 'seccion' => 'inventario', 'icon' =>'', 'subseccion'=>'lista_precio']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
 		$listas = ListaPrecios::where('empresa',Auth::user()->empresa)->get();

 		return view('listaprecios.index')->with(compact('listas'));   		
 	}

  /**
  * Formulario para crear un nueva lista
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nueva Lista de Precios']);
    return view('listaprecios.create'); 
  }

  /**
  * Registrar un nueva lista
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
      
      if( ListaPrecios::where('empresa',auth()->user()->empresa)->count() > 0){
      
        //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', ListaPrecios::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
      return redirect('empresa/inventario/lista_precios')->with('success', $mensaje);
    }
      }
      
    $request->validate([
        'nombre' => 'required|max:200',
    ]); 
    $lista = new ListaPrecios;
    $lista->nro=ListaPrecios::where('empresa',Auth::user()->empresa)->count()+1;
    $lista->empresa=Auth::user()->empresa;
    $lista->nombre=$request->nombre;
    $lista->porcentaje=$request->porcentaje;
    $lista->tipo=$request->tipo;
    $lista->save();
    $mensaje='Se ha creado satisfactoriamente la lista de precios';
    return redirect('empresa/inventario/lista_precios')->with('success', $mensaje)->with('lista_id', $lista->nro);
  }

  /**
  * Formulario para modificar los datos de una lista
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $lista = ListaPrecios::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($lista) {        
      view()->share(['title' => 'Modificar Lista de Precios']);
      return view('listaprecios.edit')->with(compact('lista'));
    }
    return redirect('empresa/inventario/lista_precios')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos de la lista
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $lista = ListaPrecios::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($lista) {
      $request->validate([
          'nombre' => 'required|max:200',
      ]); 
      $lista->nombre=$request->nombre;
      $lista->porcentaje=$request->porcentaje;
      $lista->save();
      $mensaje='Se ha modificado satisfactoriamente la lista de precios';
      return redirect('empresa/inventario/lista_precios')->with('success', $mensaje)->with('lista_id', $lista->nro);

    }
    return redirect('empresa/inventario/lista_precios')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar una lista
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $lista = ListaPrecios::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();  
    if ($lista) {        
      if (!$lista->uso() && $lista->nro>1) {
        $lista->delete();
      }
    }    
    return redirect('empresa/inventario/lista_precios')->with('success', 'Se ha eliminado la lista de precios');
  }

  
  /**
  * Funcion para activar o desactivar una lista
  * @param int $id
  * @return redirect
  */
  public function act_desc($id){      
    $lista = ListaPrecios::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();  
    if ($lista) {        
        if ($lista->status==1) {
          $mensaje='Se ha desactivado la lista de precios';
          $lista->status=0;
          $lista->save();
        }
        else{
          $mensaje='Se ha activado la lista de precios';
          $lista->status=1;
          $lista->save();
        }
      return redirect('empresa/inventario/lista_precios')->with('success', $mensaje)->with('bodega_id', $lista->nro);
    }
    return redirect('empresa/inventario/lista_precios')->with('success', 'No existe un registro con ese id');
  }

 

}