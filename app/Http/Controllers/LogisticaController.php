<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\Banco; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth; 
use App\Factura;use App\Contacto;
class LogisticaController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'logistica', 'title' => 'Logística', 'icon' =>'fas fa-parachute-box']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
 		$bancos = Banco::where('empresa',Auth::user()->empresa)->get();

 		return view('logistica.index')->with(compact('bancos'));   		
 	}

  /**
  * Formulario para crear un nuevo banco
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();
    view()->share(['title' => 'Nuevo Envío']);
    return view('logistica.create')->with(compact('clientes'));     
  }

  /** 
  * Registrar un nuevo banco
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
    $request->validate([
          'tipo_cta' => 'required|numeric',
          'nombre' => 'required|max:200',
          'saldo' => 'required|numeric',
          'fecha' => 'required'
    ]); 
    $banco = new Banco;
    $banco->empresa=Auth::user()->empresa;
    $banco->tipo_cta=$request->tipo_cta;
    $banco->nombre=$request->nombre;
    $banco->nro_cta=$request->nro_cta;
    $banco->saldo=$request->saldo;
    $banco->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
    $banco->descripcion=$request->descripcion;
    $banco->save();

    $mensaje='Se ha creado satisfactoriamente el banco';
    return redirect('empresa/bancos')->with('success', $mensaje)->with('banco_id', $banco->id);
  }

  /**
  * Formulario para modificar los datos de un banco
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $banco = Banco::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($banco) {        
      view()->share(['title' => 'Modificar Cuenta: '.$banco->nombre]);
      return view('bancos.edit')->with(compact('banco'));
    }
    return redirect('master/bancos')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos del banco
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $banco =Banco::find($id);
    if ($banco) {
      $request->validate([
        'nombre' => 'required|max:200',
        'saldo' => 'required|numeric',
        'fecha' => 'required'
      ]);
      $banco->nombre=$request->nombre;
      $banco->nro_cta=$request->nro_cta;
      $banco->saldo=$request->saldo;
      $banco->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
      $banco->descripcion=$request->descripcion;
      $banco->save();
      $mensaje='Se ha modificado satisfactoriamente el banco';
      return redirect('empresa/bancos')->with('success', $mensaje)->with('banco_id', $banco->id);

    }
    return redirect('master/bancos')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar un banco
  * @param int $id
  * @return redirect
  */
  public function destroy($id){      
    $banco=Banco::find($id);   
    if ($banco) {        
      $banco->delete();
    }    
    return redirect('empresa/bancos')->with('success', 'Se ha eliminado el banco');
  }

  /**
  * Ver un banco
  * @param int $id
  * @return view
  */
  public function show($id){
      $this->getAllPermissions(Auth::user()->id);

    $banco = Banco::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($banco) {        
      view()->share(['title' => 'Ver Cuenta: '.$banco->nombre]);
      return view('bancos.show')->with(compact('banco'));
    }
    return redirect('master/empresas')->with('success', 'No existe un registro con ese id');
  }


 

}