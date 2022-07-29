<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\Retencion; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth;
use Session;
use App\Puc;

class RetencionesController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'configuracion', 'title' => 'Retenciones', 'icon' =>'']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
 		$retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->orWhere('empresa', null)->get();

 		return view('configuracion.retenciones.index')->with(compact('retenciones'));   		
 	}

  /**
  * Formulario para crear un nuevo retención
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nuevo Tipo de Retención']);

    $cuentas = Puc::cuentasTransaccionables();

    return view('configuracion.retenciones.create', compact('cuentas')); 
  }

  /**
  * Registrar un nuevo retención
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
      
      
      if( Retencion::where('empresa',auth()->user()->empresa)->count() > 0){
      //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', Retencion::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
    $sw = 1;

    //Recorremos la sesion para obtener la fecha
    foreach (Session::get('posttimer') as $key) {
      if ($sw == 1) {
        $ultimoingreso = $key;
        $sw=0;
      }
    }

    //Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creación
    if(isset($ultimoingreso)){
      $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);

      //Si el tiempo es de menos de 30 segundos mandamos al listado general
          if ($diasDiferencia <= 10) {
            $mensaje = "El formulario ya ha sido enviado.";
          return redirect('empresa/configuracion/retenciones')->with('success', $mensaje);
          }
    }
   
      }
      
    $request->validate([
      'nombre' => 'required|max:250',
      'porcentaje' => 'required|numeric',
      'tipo' => 'required|numeric'
    ]); 
    $retencion = new Retencion;
    $retencion->empresa=Auth::user()->empresa;
    $retencion->nombre=$request->nombre;
    $retencion->porcentaje=$request->porcentaje;
    $retencion->tipo=$request->tipo;
    $retencion->descripcion=$request->descripcion;
    $retencion->puc_compra = $request->compra;
    $retencion->puc_venta = $request->venta;
    $retencion->save();

    $mensaje='Se ha creado satisfactoriamente el tipo de retención';
    return redirect('empresa/configuracion/retenciones')->with('success', $mensaje)->with('retencion_id', $retencion->id);
  }

  /**
  * Formulario para modificar los datos de un retención
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $retencion = Retencion::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    $cuentas = Puc::cuentasTransaccionables();
    if ($retencion) {        
      view()->share(['title' => 'Modificar Tipo de Retención']);
      return view('configuracion.retenciones.edit')->with(compact('retencion','cuentas'));
    }
    return redirect('empresa/configuracion/retenciones')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos del banco
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $retencion =Retencion::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($retencion) {
      $request->validate([
        'nombre' => 'required|max:250',
        'porcentaje' => 'required|numeric',
        'tipo' => 'required|numeric'
      ]);
      $retencion->nombre=$request->nombre;
      $retencion->porcentaje=$request->porcentaje;
      $retencion->tipo=$request->tipo;
      $retencion->descripcion=$request->descripcion;
      $retencion->puc_compra = $request->compra;
      $retencion->puc_venta = $request->venta;
      $retencion->save();
      $mensaje='Se ha modificado satisfactoriamente el tipo de retención';
      return redirect('empresa/configuracion/retenciones')->with('success', $mensaje)->with('retencion_id', $retencion->id);

    }
    return redirect('empresa/configuracion/retenciones')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar un retención
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $retencion=Retencion::where('empresa',Auth::user()->empresa)->where('id', $id)->first(); 
    if ($retencion->usado()==0) {
      $retencion->delete();
    }  
    return redirect('empresa/configuracion/retenciones')->with('success', 'Se ha eliminado el tipo de retención');
  }

  /**
  * Funcion para cambiar el estatus de la numeracion
  * @param int $id
  * @return redirect
  */
  public function act_desc($id){
    $retencion = Retencion::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($retencion) {        
        if ($retencion->estado==1) {
          $mensaje='Se ha desactivado el tipo de retención';
          $retencion->estado=0;
          $retencion->save();
        }
        else{
          $mensaje='Se ha activado el tipo de retención';
          $retencion->estado=1;
          $retencion->save();
        }
      return redirect('empresa/configuracion/retenciones')->with('success', $mensaje)->with('retencion_id', $retencion->id);
    }
    return redirect('empresa/configuracion/retenciones')->with('success', 'No existe un registro con ese id');
  }


  //Auto reteneciones
  public function autoIndex(){
    $this->getAllPermissions(Auth::user()->id);
    view()->share(['seccion' => 'configuracion', 'title' => 'Auto Retenciones', 'icon' =>'']);
    $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',2)->orWhere('empresa', null)->get();

    return view('configuracion.autoretencion.index')->with(compact('retenciones'));   
  }

  public function autoCreate(){
    $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nuevo Tipo de Auto Retención']);

    $cuentas = Puc::cuentasTransaccionables();

    return view('configuracion.autoretencion.create', compact('cuentas')); 
  }

  public function autoStore(Request $request){
    $request->validate([
      'nombre' => 'required|max:250',
      'porcentaje' => 'required|numeric',
    ]); 
    $retencion = new Retencion;
    $retencion->empresa=Auth::user()->empresa;
    $retencion->nombre=$request->nombre;
    $retencion->porcentaje=$request->porcentaje;
    $retencion->descripcion=$request->descripcion;
    $retencion->puc_compra = $request->compra; //debito para el modulo 2 
    $retencion->puc_venta = $request->venta; //credito para el modulo 2
    $retencion->modulo = 2;
    $retencion->save();

    $mensaje='Se ha creado satisfactoriamente el tipo de auto retención';
    return redirect('empresa/configuracion/autoretenciones')->with('success', $mensaje)->with('retencion_id', $retencion->id);
  }

  public function autoEdit($id){
    $retencion =Retencion::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    $this->getAllPermissions(Auth::user()->id);
    $cuentas = Puc::cuentasTransaccionables();
    if ($retencion) {        
      view()->share(['title' => 'Modificar Tipo de Auto Retención']);
      return view('configuracion.autoretencion.edit')->with(compact('retencion','cuentas'));
    }
    return redirect('empresa/configuracion/retenciones')->with('success', 'No existe un registro con ese id');
  }

  public function autoUpdate(Request $request, $id){
    $retencion =Retencion::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($retencion) {
      $request->validate([
        'nombre' => 'required|max:250',
        'porcentaje' => 'required|numeric',
      ]);
      $retencion->nombre=$request->nombre;
      $retencion->porcentaje=$request->porcentaje;
      $retencion->descripcion=$request->descripcion;
      $retencion->puc_compra = $request->compra; //debito para el modulo 2 
      $retencion->puc_venta = $request->venta; //credito para el modulo 2
      $retencion->save();
      $mensaje='Se ha modificado satisfactoriamente el tipo de auto retención';
      return redirect('empresa/configuracion/autoretenciones')->with('success', $mensaje)->with('retencion_id', $retencion->id);

    }
    return redirect('empresa/configuracion/autoretenciones')->with('success', 'No existe un registro con ese id');
  }


  //End Autoretenciones
 

}