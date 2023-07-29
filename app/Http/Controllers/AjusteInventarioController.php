<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
 use App\Model\Inventario\AjusteInventario;
use App\Model\Inventario\Bodega; 
use App\Model\Inventario\ProductosTransferencia;
use App\Model\Inventario\TransferenciasBodegas; 
use App\Model\Inventario\ProductosBodega;
use App\Model\Inventario\Inventario; 
use Carbon\Carbon; use DB; use Auth; 
use Validator; use Illuminate\Validation\Rule; 
use Session;

class AjusteInventarioController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['title' => 'Ajustes de Inventario', 'seccion' => 'inventario', 'icon' =>'', 'subseccion'=>'ajustes_inventario']);
  }

  /**
  * Index para ver los ajustes Registrados
  * @return view
  */
  public function index(Request $request){
    $this->getAllPermissions(Auth::user()->id);
    $busqueda=false;
    $campos=array('', 'ajuste_inventario.fecha', 'nombreproducto', 'ajuste_inventario.cant', 'ajuste_inventario.ajuste', 'ajuste_inventario.costo_unitario', 'total');
    if (!$request->orderby) {
      $request->orderby=1; $request->order=1;
    }
    $orderby=$campos[$request->orderby];
    $order=$request->order==1?'DESC':'ASC';
    $ajustes = AjusteInventario::select('ajuste_inventario.*', DB::raw('(Select producto from inventario where id=ajuste_inventario.producto) as nombreproducto'), DB::raw(('(cant * costo_unitario) as total')))->
    where('empresa', Auth::user()->empresa);
    $appends=array();
    if ($request->name_1) { 
      $busqueda=true; $appends['name_1']=$request->name_1; 
      $ajustes=$ajustes->where('ajuste_inventario.fecha', date('Y-m-d', strtotime($request->name_1)));   
    }
    if ($request->name_2) { 
      $busqueda=true; $appends['name_2']=$request->name_2; $ajustes=$ajustes->whereRaw('ajuste_inventario.producto in (Select id from inventario where empresa='.Auth::user()->empresa.' and producto like "%'.$request->name_2.'%")'); 
    }
    if ($request->name_3) {
      $busqueda=true; $appends['name_3']=$request->name_3; $appends['name_3_simb']=$request->name_3_simb; 
      $ajustes=$ajustes->where('ajuste_inventario.cant', $request->name_3_simb, $request->name_3);
    }
    if ($request->name_4) { 
      $busqueda=true; $appends['name_4']=$request->name_4; $ajustes=$ajustes->where('ajuste_inventario.ajuste', $request->name_4); 
    } 
    if ($request->name_5) {
      $busqueda=true; $appends['name_5']=$request->name_5; $appends['name_5_simb']=$request->name_5_simb; 
      $ajustes=$ajustes->where('ajuste_inventario.costo_unitario', $request->name_5_simb, $request->name_5);
    }
    if ($request->name_6) {
      $busqueda=true; $appends['name_6']=$request->name_6; $appends['name_6_simb']=$request->name_6_simb; 
      $ajustes=$ajustes->whereRaw('(cant * costo_unitario) '.$request->name_6_simb.$request->name_6);
    }
    $ajustes =$ajustes->OrderBy($orderby, $order)->paginate(25)->appends(['orderby'=>$request->orderby, 'order'=>$request->order]);

    return view('inventario.ajustes.index')->with(compact('ajustes', 'request', 'busqueda'));       
  }

  /**
  * Formulario para crear un nuevo ajuste
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Aplicar un Ajuste al Inventario']);
    $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    return view('inventario.ajustes.create')->with(compact('bodegas'));
  }

  /**
  * Registrar un nuevo ajuste
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
    $request->validate([
      'bodega' => 'required|exists:bodegas,id',
      'fecha' => 'required',
      'costo' => 'required',
      'ajuste' => 'required',
      'cant' => 'required',
      'producto'=> 'required',

    ]); 
    $ajuste = new AjusteInventario;
    $ajuste->nro=AjusteInventario::where('empresa', Auth::user()->empresa)->count()+1;
    $ajuste->empresa=Auth::user()->empresa;
    $ajuste->bodega=$request->bodega;
    $ajuste->producto=$request->producto;
    $ajuste->costo_unitario=$this->precision($request->costo);
    $ajuste->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
    $ajuste->ajuste=$request->ajuste;
    $ajuste->cant=$request->cant;
    $ajuste->observaciones=$request->observaciones;
    $ajuste->save();
    $valor=$ajuste->cant;
    if ($ajuste->ajuste==0) {
      $valor*=-1;
    }
    $bodega=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $ajuste->bodega)->where('producto', $ajuste->producto)->first();
    if ($bodega) {
      $bodega->nro+=$valor;
      $bodega->save();
    }
    $mensaje='Se ha creado ajustado satisfactoriamente en item';
    return redirect('empresa/inventario/ajustes')->with('success', $mensaje)->with('ajuste_id', $ajuste->id);
  }

  /**
  * Formulario para modificar los datos de un ajuste
  * @param int $id
  * @return view
  */
  public function show($id){
      $this->getAllPermissions(Auth::user()->id);
    $ajuste = AjusteInventario::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($ajuste) {        
      view()->share(['title' => 'Ajuste de Inventario']);
      return view('inventario.ajustes.show')->with(compact('ajuste'));
    }
    return redirect('empresa/inventario/ajustes')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Formulario para modificar los datos de un ajuste
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $ajuste = AjusteInventario::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($ajuste) {        
      view()->share(['title' => 'Editar Ajuste de Inventario']);
      $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
      $inventario =ProductosBodega::join('inventario as inv', 'inv.id', '=', 'productos_bodegas.producto')->select('productos_bodegas.*', 'inv.producto', 'inv.ref', 'inv.id as id_producto')-> where('productos_bodegas.empresa',Auth::user()->empresa)->where('productos_bodegas.bodega', $ajuste->bodega)->where('inv.tipo_producto', 1)->get();
      return view('inventario.ajustes.edit')->with(compact('ajuste', 'bodegas', 'inventario'));
    }
    return redirect('empresa/inventario/bodegas/transferencia')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos de la ajuste
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $ajuste = AjusteInventario::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($ajuste) {
      $request->validate([
        'bodega' => 'required|exists:bodegas,id',
        'fecha' => 'required',
        'costo' => 'required',
        'ajuste' => 'required',
        'cant' => 'required',
        'producto'=> 'required',

      ]); 
      $cambios=true;
      if ($ajuste->bodega!=$request->bodega || $ajuste->producto!=$request->producto || $ajuste->ajuste!=$request->ajuste || 
        $ajuste->cant!=$request->cant) {
        $cambios=false;
        $valor=$ajuste->cant;
        if ($ajuste->ajuste==1) {
          $valor*=-1;
        }
        $bodega=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $ajuste->bodega)->where('producto', $ajuste->producto)->first();
        if ($bodega) {
          $bodega->nro+=$valor;
          $bodega->save();
        }
      }     

      $ajuste->bodega=$request->bodega;
      $ajuste->producto=$request->producto;
      $ajuste->costo_unitario=$this->precision($request->costo);
      $ajuste->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
      $ajuste->ajuste=$request->ajuste;
      $ajuste->cant=$request->cant;
      $ajuste->observaciones=$request->observaciones;
      $ajuste->save();
      if ($cambios) {
        $valor=$ajuste->cant;
        if ($ajuste->ajuste==0) {
          $valor*=-1;
        }
        $bodega=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $ajuste->bodega)->where('producto', $ajuste->producto)->first();
        if ($bodega) {
          $bodega->nro+=$valor;
          $bodega->save();
        }
      }    
      $mensaje='Se ha modificado satisfactoriamente la bodega';
      return redirect('empresa/inventario/ajustes')->with('success', $mensaje)->with('ajuste_id', $ajuste->id);

    }
    return redirect('empresa/inventario/ajustes')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar una ajuste
  * @param int $id
  * @return redirect
  */
  public function destroy($id){  
    $ajuste = AjusteInventario::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($ajuste) {
        $valor=$ajuste->cant;
        if ($ajuste->ajuste==1) {
          $valor*=-1;
        }
        $bodega=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $ajuste->bodega)->where('producto', $ajuste->producto)->first();
        if ($bodega) {
          $bodega->nro+=$valor;
          $bodega->save();
        }
        AjusteInventario::where('empresa',Auth::user()->empresa)->where('nro', $id)->delete();
    }    
    return redirect('empresa/inventario/ajustes')->with('success', 'Se ha eliminado el ajuste');
  }

}