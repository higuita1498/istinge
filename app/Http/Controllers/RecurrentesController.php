<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\Contacto; use App\Model\Inventario\Inventario; use App\TipoIdentificacion;  
use App\Model\Ingresos\FacturaRecurrente; use  App\Model\Ingresos\ItemsFacturaRecurrente; use Carbon\Carbon;  
use Validator; use Illuminate\Validation\Rule;  use Auth; use App\Impuesto;
use App\NumeracionFactura;  use App\Vendedor; use App\TerminosPago;
use App\Model\Inventario\Bodega; use App\Model\Inventario\ListaPrecios; 
use App\Factura; use App\ItemsFactura; 
use App\Model\Inventario\ProductosBodega; 
use DB;
use Barryvdh\DomPDF\Facade as PDF;
class RecurrentesController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    view()->share(['seccion' => 'facturas', 'title' => 'Facturas Recurrentes', 'icon' =>'fas fa-plus', 'subseccion' => 'recurrentes']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
 		$facturas = FacturaRecurrente::where('empresa',Auth::user()->empresa)->OrderBy('nro', 'DESC')->get();
 		return view('recurrentes.index')->with(compact('facturas'));   		
 	} 
  

  public function create($cliente=false){
      $this->getAllPermissions(Auth::user()->id);
    $inventario = Inventario::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();
    view()->share(['icon' =>'', 'title' => 'Nueva Factura de Venta Recurrente', 'subseccion' => 'venta']);
    $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
    $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
    $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
    $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
    $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
    $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();


    return view('recurrentes.create')->with(compact('clientes', 'inventario', 'numeraciones', 'vendedores', 'terminos', 'impuestos', 'print', 'cliente', 'bodegas', 'listas')); 
  } 


  /**
  * Registrar una nueva factura
  * Si hay items inventariable resta los valores al inventario
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){  
    $factura = new FacturaRecurrente;    
    $factura->nro=FacturaRecurrente::where('empresa',Auth::user()->empresa)->count()+1;
    $factura->numeracion=$request->numeracion;
    $factura->plazo=$request->plazos;
    $factura->term_cond=$request->term_cond;
    $factura->notas=$request->notas;      
    $factura->empresa=Auth::user()->empresa;
    $factura->cliente=$request->cliente;
    $factura->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
    if($request->vencimiento){$factura->vencimiento=Carbon::parse($request->vencimiento)->format('Y-m-d');}    
    $factura->observaciones=mb_strtolower($request->observaciones);
    $factura->frecuencia=$request->frecuencia;
    if ($factura->fecha>=date('Y-m-d')) {
      $factura->proxima=Carbon::parse($request->fecha)->format('Y-m-d');      
    }
    else{
      $factura->proxima=date('Y-m-d', strtotime("+".$factura->frecuencia." month", strtotime($request->fecha)));
    }

    $factura->lista_precios=$request->lista_precios;
    $factura->bodega=$request->bodega;    
    $factura->save();
 
    //Ciclo para registrar los itemas de la factura
    for ($i=0; $i < count($request->ref) ; $i++) { 
      $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
      $producto = Inventario::where('id', $request->item[$i])->first(); 
      $items = new ItemsFacturaRecurrente;
      $items->factura_recurrente=$factura->id;
      $items->producto=$request->item[$i];
      $items->ref=$request->ref[$i];
      $items->precio=$this->precision($request->precio[$i]); 
      $items->descripcion=$request->descripcion[$i];
      $items->id_impuesto=$request->impuesto[$i];
      $items->impuesto=$impuesto->porcentaje;
      $items->cant=$request->cant[$i];
      $items->desc=$request->desc[$i];
      $items->save();
    }
        
    $mensaje='Se ha creado satisfactoriamente la factura recurrente primera emisión '.date('d-m-Y', strtotime($factura->proxima));    
    return redirect('empresa/recurrentes')->with('success', $mensaje);
  }

 
  /**
  * Formulario para modificar los datos de una factura
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $factura = FacturaRecurrente::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($factura) {
      view()->share(['icon' =>'', 'title' => 'Modificar Factura de Venta Recurrente: '.$factura->nro, 'subseccion' => 'recurrentes']);
      //Obtengo el objeto bodega
      $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $factura->bodega)->first();
      if (!$bodega) {
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
      }
      $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
      $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
      $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
      $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
      $items = ItemsFacturaRecurrente::where('factura_recurrente',$factura->id)->get();
      $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();
      $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
      $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
      $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
      return view('recurrentes.edit')->with(compact('clientes', 'inventario', 'vendedores', 'terminos', 'impuestos', 'factura', 'items', 'listas', 'bodegas', 'numeraciones')); 

    }
    return redirect('empresa/recurrentes')->with('success', 'No existe un registro con ese id');    
  } 


  /**
  * Modificar los datos de la factura
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $factura =FacturaRecurrente::find($id);
    if ($factura) {     

        //Modificacion de los datos de la factura
      $factura->numeracion=$request->numeracion;
      $factura->plazo=$request->plazos;
      $factura->term_cond=$request->term_cond;
      $factura->notas=$request->notas;      
      $factura->empresa=Auth::user()->empresa;
      $factura->cliente=$request->cliente;
      $factura->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
      if($request->vencimiento){$factura->vencimiento=Carbon::parse($request->vencimiento)->format('Y-m-d');}    
      $factura->observaciones=mb_strtolower($request->observaciones);
      $factura->frecuencia=$request->frecuencia;
      if ($factura->fecha>=date('Y-m-d')) {
        $factura->proxima=Carbon::parse($request->fecha)->format('Y-m-d');      
      }
      else{
        $fecha=$request->fecha;
        while (true) {
          $fecha=date('Y-m-d', strtotime("+".$factura->frecuencia." month", strtotime($request->fecha)));
          if ($fecha>date('Y-m-d')) { break; }
        }
        $factura->proxima=$fecha;
      }
      $factura->lista_precios=$request->lista_precios;
      $factura->bodega=$request->bodega;    
      $factura->save();
        $inner=array();
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
        if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
          $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        }
        //Ciclo para registrar y/o modificar los itemas de la factura
        for ($i=0; $i < count($request->ref) ; $i++) { 
          $cat='id_item'.($i+1);
          if($request->$cat){
            $items = ItemsFacturaRecurrente::where('id', $request->$cat)->first();
          }
          else{
            $items = new ItemsFacturaRecurrente;
          }
          $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
          $producto = Inventario::where('id', $request->item[$i])->first();
          $items->factura_recurrente=$factura->id;
          $items->producto=$request->item[$i];
          $items->ref=$request->ref[$i];
          $items->precio=$this->precision($request->precio[$i]); 
          $items->descripcion=$request->descripcion[$i];
          $items->id_impuesto=$request->impuesto[$i];
          $items->impuesto=$impuesto->porcentaje;
          $items->cant=$request->cant[$i];
          $items->desc=$request->desc[$i];
          $items->save();
          $inner[]=$items->id;
        }

        if (count($inner)>0) {
          DB::table('items_factura_recurrente')->where('factura_recurrente', $factura->id)->whereNotIn('id', $inner)->delete();
        }

        $mensaje='Se ha modificado satisfactoriamente la factura recurrente';
        return redirect('empresa/recurrentes')->with('success', $mensaje)->with('codigo', $factura->id);

    }
    return redirect('empresa/recurrentes')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Ver los datos de una factura
  * @param int $id
  * @return view
  */
  public function show($id){
      $this->getAllPermissions(Auth::user()->id);
    $factura = FacturaRecurrente::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($factura) {
      view()->share(['icon' =>'', 'title' => 'Factura de Venta Recurrente', 'subseccion' => 'recurrentes']);      
      $items = ItemsFacturaRecurrente::where('factura_recurrente',$factura->id)->get();      
      return view('recurrentes.show')->with(compact('factura', 'items')); 

    }
    return redirect('empresa/recurrentes')->with('success', 'No existe un registro con ese id');    
  } 

  public function destroy($id){
    $factura = FacturaRecurrente::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($factura) {
          
      $items = ItemsFacturaRecurrente::where('factura_recurrente',$factura->id)->delete();  
       $factura->delete();   
      $mensaje='Se ha eliminado satisfactoriamente la factura recurrente';
      return back()->with('success', $mensaje);

    }
    return redirect('empresa/recurrentes')->with('success', 'No existe un registro con ese id');    

  }

}