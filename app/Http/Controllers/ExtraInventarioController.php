<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\ProductosBodega;
use Auth; use DB; 
class ExtraInventarioController extends Controller
{
	/** 
	* Create a new controller instance.
	*
	* @return void
	*/
	public function __construct()
	{
		$this->middleware('auth');
		view()->share(['inicio' => 'master', 'seccion' => 'inventario', 'title' => 'Inventario', 'icon' =>'fas fa-boxes', 'subseccion'=>'items_venta']);
	}

	/**
	* Funcion para mostrar el valor del inventario
	*/
	public function valorinventario(Request $request){
        $this->getAllPermissions(Auth::user()->id);
		if (!$request->valor_bodega) {
			$request->valor_bodega=0;			
		}

		$canttotal = ProductosBodega::join('inventario as inv', 'inv.id', '=', 'productos_bodegas.producto')->select(
			DB::raw('(Select (if(SUM(precio), SUM(precio), 0)+inv.precio)/(if(count(precio), count(precio), 0)+1) from productos_precios where producto=inv.id) * SUM(productos_bodegas.nro) as precio_total'), 
			DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inv.id) as total'), 
			DB::raw('SUM(productos_bodegas.nro) as total_bodega'));


		$productos = ProductosBodega::join('inventario as inv', 'inv.id', '=', 'productos_bodegas.producto')
		->select('inv.id', 'inv.producto', 'inv.ref', 'inv.status', 
			DB::raw('SUM(productos_bodegas.nro) as total_bodega'), 
			DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inv.id) as total'),   
			DB::raw('(Select unidad from unidades_medida where id=inv.unidad) as unidad'), 
			DB::raw('(Select (if(SUM(precio), SUM(precio), 0)+inv.precio)/(if(count(precio), count(precio), 0)+1) from productos_precios where producto=inv.id)as precio'),
			DB::raw('(Select (if(SUM(precio), SUM(precio), 0)+inv.precio)/(if(count(precio), count(precio), 0)+1) from productos_precios where producto=inv.id) * SUM(productos_bodegas.nro) as precio_total'));
		$productos = $productos ->where('productos_bodegas.empresa',Auth::user()->empresa);

		$campos=array('', 'inv.producto', 'inv.ref', 'total_bodega', 'total', 'unidad', 'inv.status', 'precio', 'precio_total');
		if (!$request->orderby) {
	      $request->orderby=1; $request->order=1;
	    }
	    $orderby=$campos[$request->orderby];
	    $order=$request->order==1?'DESC':'ASC';


		$canttotal= $canttotal ->where('productos_bodegas.empresa',Auth::user()->empresa);
		if ($request->valor_bodega>0) {
			$productos =$productos->where('productos_bodegas.bodega', $request->valor_bodega);
			$canttotal =$canttotal->where('productos_bodegas.bodega', $request->valor_bodega);
			$request->bodega=Bodega::where('empresa',Auth::user()->empresa)->where('id', $request->valor_bodega)->first()->bodega;

		}
		$productos =$productos->groupBy('productos_bodegas.producto');
		$canttotal =$canttotal->groupBy('productos_bodegas.producto')->get();
		$total=$cant=$totalcant=0;
		$cant=0;
		foreach ($canttotal as $key => $value) {
			/*echo $total."+".$value->precio_total."=".($total+$value->precio_total)."<br>";*/
			$total+=$value->precio_total;
			$cant+=$value->total_bodega;
			$totalcant+=$value->total;
		}

		$valortotal=$total;
		$canttotal=$cant;
		$productos =$productos->OrderBy($orderby, $order)->paginate(25)->appends(['orderby'=>$request->orderby, 'order'=>$request->order]);
		$bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
		/*$productos = Inventario::where('empresa',Auth::user()->empresa)->paginate(25);
		$canttotal = Inventario::where('empresa',Auth::user()->empresa)->sum('nro');*/
		view()->share(['title' => 'Valor Actual del Inventario', 'icon' =>'', 'subseccion'=>'valor_inventario']);
		return view('inventario.valorinventario')->with(compact('bodegas', 'request', 'productos', 'canttotal', 'valortotal', 'totalcant')); 
	}

	/**
	* Funcion para mostrar el ajuste del inventario
	*/
	public function gestion(){
        $this->getAllPermissions(Auth::user()->id);
		view()->share(['title' => 'Gestión de Items', 'icon' =>'', 'subseccion'=>'gestion_items']);
		return view('inventario.gestion_items'); 
	}
}