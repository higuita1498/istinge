<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use Auth;
use Session;

use App\Empresa;
use App\Canal;

class CanalesController extends Controller{
  public function __construct(){
    $this->middleware('auth');
    view()->share(['seccion' => 'configuracion', 'title' => 'Canales de Venta', 'icon' =>'fas fa-store']);
  }

  public function index(){
    $this->getAllPermissions(Auth::user()->id);
 		$canales = Canal::where('empresa',Auth::user()->empresa)->get();
 		return view('configuracion.canales.index')->with(compact('canales'));
 	}

  public function create(){
    $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nuevo Canal de Venta']);
    return view('configuracion.canales.create');
  }

  public function store(Request $request){
    $request->validate([
      'nombre' => 'required|max:250',
    ]); 

    $canal = new Canal;
    $canal->empresa       = Auth::user()->empresa;
    $canal->nombre        = $request->nombre;
    $canal->observaciones = $request->observaciones;
    $canal->created_by    = Auth::user()->id;
    $canal->save();

    return redirect('empresa/configuracion/canales')->with('success', 'SE HA CREADO SATISFACTORIAMENTE EL CANAL')->with('canal_id', $canal->id);
  }

  public function edit($id){
    $this->getAllPermissions(Auth::user()->id);

    $canal = Canal::where('empresa', Auth::user()->empresa)->where('id', $id)->first();
    if ($canal) {
      view()->share(['title' => 'Modificar Canal de Venta']);
      return view('configuracion.canales.edit')->with(compact('canal'));
    }
    return redirect('empresa/configuracion/canales')->with('danger', 'NO EXISTE UN REGISTRO CON ESE ID');
  }

  public function update(Request $request, $id){
    $canal = Canal::where('empresa', Auth::user()->empresa)->where('id', $id)->first();
    if ($canal) {
      $request->validate([
        'nombre' => 'required|max:250'
      ]);

      $canal->nombre        = $request->nombre;
      $canal->observaciones = $request->observaciones;
      $canal->updated_by    = Auth::user()->id;
      $canal->save();

      return redirect('empresa/configuracion/canales')->with('success', 'SE HA MODIFICADO SATISFACTORIAMENTE EL CANAL')->with('canal_id', $canal->id);
    }
    return redirect('empresa/configuracion/canales')->with('danger', 'NO EXISTE UN REGISTRO CON ESE ID');
  }

  public function destroy($id){      
    $canal = Canal::where('empresa', Auth::user()->empresa)->where('id', $id)->first();
    if ($canal->usado()==0) {
      $canal->delete();
      return redirect('empresa/configuracion/canales')->with('success', 'SE HA ELIMINADO SATISFACTORIAMENTE EL CANAL');
    }  
    return redirect('empresa/configuracion/canales')->with('danger', 'NO EXISTE UN REGISTRO CON ESE ID');
  }

  public function act_desc($id){
    $canal = Canal::where('empresa', Auth::user()->empresa)->where('id', $id)->first();
    if ($canal) {
      if ($canal->status==1) {
        $mensaje       = 'SE HA DESHABILITADO EL CANAL SATISFACTORIAMENTE';
        $canal->status = 0;
        $canal->save();
      }else{
        $mensaje       = 'SE HA HABILITADO EL CANAL SATISFACTORIAMENTE';
        $canal->status = 1;
        $canal->save();
      }
      return redirect('empresa/configuracion/canales')->with('success', $mensaje)->with('canal_id', $canal->id);
    }
    return redirect('empresa/configuracion/canales')->with('danger', 'NO EXISTE UN REGISTRO CON ESE ID');
  }
}
