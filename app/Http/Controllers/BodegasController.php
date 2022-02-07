<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Inventario\Bodega; use App\Model\Inventario\ProductosBodega;
use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth; 
use Session;

class BodegasController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['title' => 'Bodegas', 'seccion' => 'inventario', 'icon' =>'', 'subseccion'=>'bodegas']);
  }

  /**
  * Index para ver las bodegas registradas
  * @return view
  */
  public function index(){
      $this->getAllPermissions(Auth::user()->id);
 		$bodegas = Bodega::where('empresa',Auth::user()->empresa)->get();
 		return view('bodegas.index')->with(compact('bodegas'));   		
 	}

  /**
  * Formulario para crear un nuevo Bodega
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nueva Bodega']);
    return view('bodegas.create'); 
  }

  /**
  * Registrar un nuevo bodega
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
      
     if( Bodega::where('empresa',auth()->user()->empresa)->count() > 0){
      
                   //Tomamos el tiempo en el que se crea el registro
        Session::put('posttimer', Bodega::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
       return redirect('empresa/inventario/bodegas')->with('success', $mensaje);
  }
     }
      
    $request->validate([
        'nombre' => 'required|max:200',
    ]); 
    $bodega = new Bodega;
    $bodega->nro=Bodega::where('empresa',Auth::user()->empresa)->count()+1;
    $bodega->empresa=Auth::user()->empresa;
    $bodega->bodega=$request->nombre;
    $bodega->direccion=$request->direccion;
    $bodega->observaciones=$request->observaciones;
    $bodega->save();
    $mensaje='Se ha creado satisfactoriamente la bodega';
    return redirect('empresa/inventario/bodegas')->with('success', $mensaje)->with('bodega_id', $bodega->nro);
  }

  /**
  * Formulario para modificar los datos de un bodega
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($bodega) {        
      view()->share(['title' => 'Modificar Bodega']);
      return view('bodegas.edit')->with(compact('bodega'));
    }
    return redirect('empresa/inventario/bodegas')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos del bodega
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($bodega) {
      $request->validate([
          'nombre' => 'required|max:200',
      ]); 
      $bodega->bodega=$request->nombre;
      $bodega->direccion=$request->direccion;
      $bodega->observaciones=$request->observaciones;
      $bodega->save();
      $mensaje='Se ha modificado satisfactoriamente la bodega';
      return redirect('empresa/inventario/bodegas')->with('success', $mensaje)->with('bodega_id', $bodega->nro);

    }
    return redirect('empresa/inventario/bodegas')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar una bodega
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();  
    if ($bodega) {        
      if (!$bodega->uso()) {
        $bodega->delete();
      }
    }    
    return redirect('empresa/inventario/bodegas')->with('success', 'Se ha eliminado la bodega');
  }

  
  /**
  * Funcion para activar o desactivar una bodega
  * @param int $id
  * @return redirect
  */
  public function act_desc($id){      
    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();  
    if ($bodega) {        
        if ($bodega->status==1) {
          $mensaje='Se ha desactivado la bodega';
          $bodega->status=0;
          $bodega->save();
        }
        else{
          $mensaje='Se ha activado la bodega';
          $bodega->status=1;
          $bodega->save();
        }
      return redirect('empresa/inventario/bodegas')->with('success', $mensaje)->with('bodega_id', $bodega->nro);;
    }
    return redirect('empresa/inventario/bodegas')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion que retorna los productos 
  * de la bodega especificos
  */
  public function json($id){
    $inventario =ProductosBodega::join('inventario as inv', 'inv.id', '=', 'productos_bodegas.producto')->select('productos_bodegas.*', 'inv.producto', 'inv.ref', 'inv.id as id_producto', 'inv.precio')-> where('productos_bodegas.empresa',Auth::user()->empresa)->where('productos_bodegas.bodega', $id)->where('inv.tipo_producto', 1)->get();
    return json_encode($inventario);
  }
 

}