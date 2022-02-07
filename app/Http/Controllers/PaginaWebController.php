<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Model\Inventario\Inventario; use Auth; 
use App\CamposExtra;

class PaginaWebController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'Pagina Web', 'title' => 'Página Web', 'icon' =>'fas fa-cogs']);
  }

  public function index()
  {
      $this->getAllPermissions(Auth::user()->id);
   return view('paginaweb.index');
 }

 public function pedidos()
 {
     $this->getAllPermissions(Auth::user()->id);
   $pedidos = DB::table('webpedido')->where('empresa_id',auth()->user()->empresa)->where('transactionState','!=',null)->get();
   view()->share(['title' => 'Página Web - Pedidos', 'icon' =>'fas fa-boxes']);
   return view('paginaweb.pedidos',compact('pedidos'));
 }

 public function detallepedido($id)
 {
     $this->getAllPermissions(Auth::user()->id);
  //json_decode(Pedido::ConsultaEstado(),true)
	//$pedido = response()->json(DB::table('webpedido')->where('id',$id)->first());
  $pedido = DB::table('webpedido')->where('id',$id)->first();
  $detallepedido = DB::table('webpedido_detalle')->where('webpedido_id',$id)->get();
  $total = 0;
  foreach ($detallepedido as $det) {
    $total = $total + $det->cantidad * $det->precio;
  }
  
  return view('paginaweb.detallepedido',compact('pedido','detallepedido','total'));
}

public function comentarios()
{
  //$comentarios = DB::table('webcomentarios')->where('empresa_id',auth()->user()->empresa)->get();
    $this->getAllPermissions(Auth::user()->id);
  $comentarios =  DB::table('webcomentarios')->select('id','id_producto','nombre_producto','url_producto', DB::raw('Count(id_producto) as cantidadproductos, Sum(calificacion) as sumcalificacion'))->where('empresa_id',auth()->user()->empresa)->groupBy('id_producto','nombre_producto','url_producto')->get();

  

  //DB::table('inventario_meta as im')->join('inventario as i', 'i.id', '=', 'im.id_producto')->select('im.meta_value', DB::raw('Count(i.id) as total'))->where('im.empresa',Inventario::$empresa)->where('im.meta_key', 'marca')->where('i.publico', 1)->groupBy('im.meta_value')->get();

    view()->share(['title' => 'Página Web - Comentarios', 'icon' =>'fas fa-comments']);
    return view('paginaweb.comentarios',compact('comentarios'));
}

public function detallecomentarios($id)
{
    $this->getAllPermissions(Auth::user()->id);
  $inventario =Inventario::where('id',$id)->where('empresa',Auth::user()->empresa)->first();
  if ($inventario) {
    view()->share(['title' => $inventario->producto, 'icon' =>' ', 'middel'=>true,]);
    $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $comentarios = DB::table('webcomentarios')->where('id_producto',$id)->where('empresa_id',Auth::user()->empresa)->get();
    
    return view('paginaweb.detallecomentarios',compact('comentarios','inventario','extras'));
  }
  else
  {
     return redirect('empresa/inventario')->with('success', 'No existe un registro con ese id');
  }
}

    public function personas()
    {
        $this->getAllPermissions(Auth::user()->id);
        $personas = DB::table('webpersonas')->where('empresa_id',Auth::user()->empresa)->get();
        view()->share(['title' => 'Página Web - Personas/Usuarios', 'icon' =>'fas fa-users']);
        return view('paginaweb.personas', compact('personas'));
    }
}
