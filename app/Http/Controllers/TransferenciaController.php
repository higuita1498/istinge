<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Inventario\Bodega; 
use App\Model\Inventario\ProductosTransferencia;
use App\Model\Inventario\TransferenciasBodegas; 
use App\Model\Inventario\ProductosBodega;
use App\Model\Inventario\Inventario; 
use Validator; use Illuminate\Validation\Rule;  
use Carbon\Carbon; use DB; use Auth; 
use Barryvdh\DomPDF\Facade as PDF;
use Session;
class TransferenciaController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['title' => 'Transferencias entre Bodegas', 'seccion' => 'inventario', 'icon' =>'', 'subseccion'=>'gestion_items']);
  }

  /**
  * Index para ver las transferencia
  * @return view
  */
  public function index(Request $request){
      $this->getAllPermissions(Auth::user()->id);
    $busqueda=false;
    $campos=array('', 'transferencias_bodegas.nro', 'transferencias_bodegas.fecha', 'origen', 'destino');
    if (!$request->orderby) {
      $request->orderby=1; $request->order=1;
    }
    $orderby=$campos[$request->orderby];
    $order=$request->order==1?'DESC':'ASC';
    $appends=array(['orderby'=>$request->orderby, 'order'=>$request->order]);
    $transferencias = TransferenciasBodegas::select('transferencias_bodegas.*', 
      DB::raw('(select bodega from bodegas where id=transferencias_bodegas.bodega_origen) as origen'), 
      DB::raw('(select bodega from bodegas where id=transferencias_bodegas.bodega_destino) as destino'))
    ->where('transferencias_bodegas.empresa', Auth::user()->empresa);
    $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();

    if ($request->name_1) { 
      $busqueda=true; $appends['name_1']=$request->name_1; 
      $transferencias=$transferencias->where('transferencias_bodegas.nro', $request->name_1);   
    }
    if ($request->name_2) { 
      $busqueda=true; $appends['name_2']=$request->name_2; 
      $transferencias=$transferencias->where('transferencias_bodegas.fecha', date('Y-m-d', strtotime($request->name_2)));   
    }
    if ($request->name_3) { 
      $busqueda=true; $appends['name_3']=$request->name_3; 
      $transferencias=$transferencias->where('transferencias_bodegas.bodega_origen', $request->name_3); 
    }
    if ($request->name_4) { 
      $busqueda=true; $appends['name_4']=$request->name_4; 
      $transferencias=$transferencias->where('transferencias_bodegas.bodega_destino', $request->name_4); 
    }

 		$transferencias =$transferencias->OrderBy($orderby, $order)->paginate(25)->appends($appends);

 		return view('bodegas.transferencias.index')->with(compact('transferencias', 'request', 'busqueda', 'bodegas'));   		
 	}

  /**
  * Formulario para crear un nueva transferencia
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nueva Transferencia entre Bodegas']);
    $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    return view('bodegas.transferencias.create')->with(compact('bodegas'));
  }

  /**
  * Registrar un nuevo transferencia
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
        
          if( TransferenciasBodegas::where('empresa',auth()->user()->empresa)->count() > 0){
      //Tomamos el tiempo en el que se crea el registro
        Session::put('posttimer', TransferenciasBodegas::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
        return redirect('empresa/inventario/bodegas/transferencia')->with('success', $mensaje);
  }
          }
      
    $request->validate([
      'bodega_origen' => 'required|exists:bodegas,id',
      'bodega_destino' => 'required|exists:bodegas,id',
      'fecha' => 'required',

    ]); 
    $transferencia = new TransferenciasBodegas;
    $transferencia->nro=TransferenciasBodegas::where('empresa', Auth::user()->empresa)->count()+1;
    $transferencia->empresa=Auth::user()->empresa;
    $transferencia->bodega_origen=$request->bodega_origen;
    $transferencia->bodega_destino=$request->bodega_destino;
    $transferencia->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
    $transferencia->observaciones=$request->observaciones;
    $transferencia->save();

    for ($i=0; $i < count($request->item) ; $i++) {

      $producto = Inventario::where('id', $request->item[$i])->where('empresa', Auth::user()->empresa)->first();
      if ($producto) {
        $trans=new ProductosTransferencia;
        $trans->transferencia=$transferencia->id;
        $trans->producto=$producto->id;
        $trans->bodega_destino=$transferencia->bodega_destino;
        $trans->bodega_origen=$transferencia->bodega_origen;      
        $trans->nro=$request->cant[$i];
        $trans->save();
        $origen=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $transferencia->bodega_origen)->where('producto', $producto->id)->first();
        $destino=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $transferencia->bodega_destino)->where('producto', $producto->id)->first();
        if (!$destino) {
          $destino=new ProductosBodega;
          $destino->empresa=Auth::user()->empresa;
          $destino->bodega=$transferencia->bodega_destino;  
          $destino->producto=$producto->id;
          $destino->inicial=0;
          $destino->nro=0;
        }

        $destino->nro+=$request->cant[$i];
        $destino->save();

        $origen->nro-=$request->cant[$i];
        $origen->save();  
      }
    }


    $mensaje='Se ha creado satisfactoriamente la transferencia';
    return redirect('empresa/inventario/bodegas/transferencia')->with('success', $mensaje)->with('transferencia_id', $transferencia->id);
  }

  /**
  * Ver los datos de una transferencia
  * @param int $id
  * @return view
  */
  public function show($id){
      $this->getAllPermissions(Auth::user()->id);
    $transferencia = TransferenciasBodegas::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($transferencia) {        
      view()->share(['title' => 'Transferencia entre Bodegas: '.$transferencia->nro]);
      $trans=ProductosTransferencia::where('transferencia', $transferencia->id)->get();
      return view('bodegas.transferencias.show')->with(compact('transferencia', 'trans'));
    }
    return redirect('empresa/inventario/bodegas/transferencia')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Formulario para modificar los datos de una transferencia
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $transferencia = TransferenciasBodegas::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($transferencia) {        
      view()->share(['title' => 'Modificar Transferencia entre Bodegas']);
      $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
      $trans=ProductosTransferencia::where('transferencia', $transferencia->id)->get();
      $inventario =ProductosBodega::join('inventario as inv', 'inv.id', '=', 'productos_bodegas.producto')->select('productos_bodegas.*', 'inv.producto', 'inv.ref', 'inv.id as id_producto')-> where('productos_bodegas.empresa',Auth::user()->empresa)->where('productos_bodegas.bodega', $transferencia->bodega_origen)->where('inv.tipo_producto', 1)->get();

      return view('bodegas.transferencias.edit')->with(compact('transferencia', 'bodegas', 'trans', 'inventario'));
    }
    return redirect('empresa/inventario/bodegas/transferencia')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos de la transferencia
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $transferencia = TransferenciasBodegas::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($transferencia) {
      $request->validate([
        'bodega_origen' => 'required|exists:bodegas,id',
        'bodega_destino' => 'required|exists:bodegas,id',
        'fecha' => 'required',
      ]); 
      $transferencia->bodega_origen=$request->bodega_origen;
      $transferencia->bodega_destino=$request->bodega_destino;
      $transferencia->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
      $transferencia->observaciones=$request->observaciones;
      $transferencia->save();
      $array=array();
      for ($i=0; $i < count($request->item) ; $i++) {
        $producto = Inventario::where('id', $request->item[$i])->where('empresa', Auth::user()->empresa)->first();
        if ($producto) {
          $origen=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $transferencia->bodega_origen)->where('producto', $producto->id)->first();
          $destino=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $transferencia->bodega_destino)->where('producto', $producto->id)->first();   
          if (!$destino) {
            $destino=new ProductosBodega;
            $destino->empresa=Auth::user()->empresa;
            $destino->bodega=$transferencia->bodega_destino;  
            $destino->producto=$producto->id;
            $destino->inicial=0;
            $destino->nro=0;
          }
          $trans=ProductosTransferencia::where('producto', $producto->id)->where('transferencia', $transferencia->id)->where('bodega_destino', $transferencia->bodega_destino)->where('bodega_origen', $transferencia->bodega_origen)->first();
          if (!$trans) {
            $trans=new ProductosTransferencia;
            $destino->nro+=$request->cant[$i];
            $destino->save();

            $origen->nro-=$request->cant[$i];
            $origen->save();  
          }
          else{
            $valor=0;
            if ($trans->nro!=$request->cant[$i]) {
              if ($trans->nro>$request->cant[$i]) {
                $valor=$trans->nro-$request->cant[$i]*-1;
              }
              else{
                $valor=$request->cant[$i]-$trans->nro;
              }
              $destino->nro+=$valor;
              $destino->save();
              $origen->nro-=$valor*-1;
              $origen->save();
            }   
          }

          $trans->bodega_destino=$transferencia->bodega_destino;
          $trans->bodega_origen=$transferencia->bodega_origen; 
          $trans->transferencia=$transferencia->id;
          $trans->producto=$producto->id;
          $trans->nro=$request->cant[$i];
          $trans->save();
          $array[]=$trans->id;
        }
      }

      if (count($array)>0) {
        $eliminados=ProductosTransferencia::where('transferencia', $transferencia->id)->whereNotIn('id', $array)->get();
        foreach ($eliminados as $eliminado) {
          $origen=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $eliminado->bodega_origen)->where('producto', $eliminado->producto)->first();
          $destino=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $eliminado->bodega_destino)->where('producto', $eliminado->producto)->first();   
          $destino->nro-=$eliminado->nro;
          $destino->save();
          $origen->nro+=$eliminado->nro;
          $origen->save(); 
        }
        $eliminados=ProductosTransferencia::where('transferencia', $transferencia->id)->whereNotIn('id', $array)->delete();
      }
      $mensaje='Se ha modificado satisfactoriamente la bodega';
      return redirect('empresa/inventario/bodegas/transferencia')->with('success', $mensaje)->with('transferencia_id', $transferencia->id);

    }
    return redirect('empresa/inventario/bodegas/transferencia')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para eliminar una transferencia
  * @param int $id
  * @return redirect
  */
  public function destroy($id){  
    $transferencia = TransferenciasBodegas::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($transferencia) {
        $eliminados=ProductosTransferencia::where('transferencia', $transferencia->id)->get();
        foreach ($eliminados as $eliminado) {
          $origen=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $eliminado->bodega_origen)->where('producto', $eliminado->producto)->first();
          $destino=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $eliminado->bodega_destino)->where('producto', $eliminado->producto)->first();   
          $destino->nro-=$eliminado->nro;
          $destino->save();
          $origen->nro+=$eliminado->nro;
          $origen->save(); 
        }
        $eliminados=ProductosTransferencia::where('transferencia', $transferencia->id)->delete();
        TransferenciasBodegas::where('id', $transferencia->id)->delete();
    }    
    return redirect('empresa/inventario/bodegas/transferencia')->with('success', 'Se ha eliminado la transferencia');
  }

  public function imprimir ($id){
    /**
     * toma en cuenta que para ver los mismos 
     * datos debemos hacer la misma consulta
    **/
    view()->share(['title' => 'Imprimir Transferencia']);

    $transferencia = TransferenciasBodegas::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($transferencia) {        
      $trans=ProductosTransferencia::where('transferencia', $transferencia->id)->get();
      $itemscount=ProductosTransferencia::where('transferencia', $transferencia->id)->count();

      $pdf = PDF::loadView('pdf.transferencia', compact('transferencia', 'trans', 'itemscount'));
      return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
    }

  }   

}