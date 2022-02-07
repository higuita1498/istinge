<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\TipoEmpresa; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth; 
use Session; use App\Contacto; use DB; use App\Model\Inventario\Inventario;
use App\Model\Inventario\Bodega;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Gastos\ItemsFacturaProv;

class BusquedaProveedorController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    
    view()->share(['seccion' => 'configuracion', 'title' => 'Tipos de Contacto', 'icon' =>'']);
  }

  public function index(Request $request){
    
    $this->getAllPermissions(Auth::user()->id);
      // Consulta por: x - x - x - fabricante
      if ($request->marca == null && $request->linea == null && $request->categoria == null && $request->fabricante != null) {
      $consulta = DB::table('prov_fabricante')->join('contactos','prov_fabricante.id_proveedor', '=', 'contactos.id')->where('prov_fabricante.id_fabricante','=',$request->fabricante)->select('contactos.id')->get();
    }
    // Consulta por: x - x - categoria - x
    else if($request->marca == null && $request->linea == null && $request->categoria != null && $request->fabricante == null)
    {
      $consulta = DB::table('prov_categoria')->join('contactos','prov_categoria.id_proveedor', '=', 'contactos.id')->where('prov_categoria.id_categoria','=',$request->categoria)->select('contactos.id')->get();
      
    }
    // Consulta por: x - linea - x - x 
      else if($request->marca == null && $request->linea != null && $request->categoria == null && $request->fabricante == null)
    {
      $consulta = DB::table('prov_linea')->join('contactos','prov_linea.id_proveedor', '=', 'contactos.id')->where('prov_linea.id_linea','=',$request->linea)->select('contactos.id')->get();
    }
    // Consulta por: marca - x - x - x
     else if($request->marca != null && $request->linea == null && $request->categoria == null && $request->fabricante == null){

      $consulta = DB::table('prov_marca')->join('contactos','prov_marca.id_proveedor', '=', 'contactos.id')->where('prov_marca.id_marca','=',$request->marca)->select('contactos.id')->get();

    }
   // Consulta por: marca - linea - x - x
    else if($request->marca != null && $request->linea != null && $request->categoria == null && $request->fabricante == null)
    {
  
      $consulta = DB::table('prov_marca')
      ->join('prov_linea','prov_linea.id_proveedor', '=', 'prov_marca.id_proveedor')
      ->select('prov_linea.id_proveedor as id','prov_marca.id_proveedor as id')
      ->where('prov_marca.id_marca','=',$request->marca)
      ->where('prov_linea.id_linea','=',$request->linea)
      ->get();

    }
    // Consulta por: marca - linea - categoria - x
    else if($request->marca != null && $request->linea != null && $request->categoria != null && $request->fabricante == null){
      $consulta = DB::table('prov_marca')
      ->join('prov_linea','prov_linea.id_proveedor', '=', 'prov_marca.id_proveedor')
      ->join('prov_categoria','prov_categoria.id_proveedor', '=' , 'prov_linea.id_proveedor')
      ->select('prov_linea.id_proveedor as id','prov_marca.id_proveedor as id', 'prov_categoria.id_proveedor as id')
      ->where('prov_marca.id_marca','=',$request->marca)
      ->where('prov_linea.id_linea','=',$request->linea)
      ->where('prov_categoria.id_categoria','=',$request->categoria)
      ->get();

    }
    // Consulta por: marca - linea - categoria - fabricante
    else if($request->marca != null && $request->linea != null && $request->categoria != null && $request->fabricante != null){

      $consulta = DB::table('prov_marca')
      ->join('prov_linea','prov_linea.id_proveedor', '=', 'prov_marca.id_proveedor')
      ->join('prov_categoria','prov_categoria.id_proveedor', '=' , 'prov_linea.id_proveedor')
      ->join('prov_fabricante', 'prov_fabricante.id_proveedor', '=' , 'prov_categoria.id_proveedor')
      ->select('prov_linea.id_proveedor as id','prov_marca.id_proveedor as id', 'prov_categoria.id_proveedor as id', 'prov_fabricante.id_proveedor as id')
      ->where('prov_marca.id_marca','=',$request->marca)
      ->where('prov_linea.id_linea','=',$request->linea)
      ->where('prov_categoria.id_categoria','=',$request->categoria)
      ->where('prov_fabricante.id_fabricante', '=', $request->fabricante)
      ->get();

    }
    // Consulta por: marca - x - categoria - fabricante
    else if($request->marca != null && $request->linea == null && $request->categoria != null && $request->fabricante != null)
    {
      $consulta = DB::table('prov_marca')
      ->join('prov_categoria','prov_categoria.id_proveedor', '=' , 'prov_marca.id_proveedor')
      ->join('prov_fabricante', 'prov_fabricante.id_proveedor', '=' , 'prov_categoria.id_proveedor')
      ->select('prov_marca.id_proveedor as id', 'prov_categoria.id_proveedor as id', 'prov_fabricante.id_proveedor as id')
      ->where('prov_marca.id_marca','=',$request->marca)
      ->where('prov_categoria.id_categoria','=',$request->categoria)
      ->where('prov_fabricante.id_fabricante', '=', $request->fabricante)
      ->get();

    }
    // Consulta por: marca - x - x - fabricante
    else if($request->marca != null && $request->linea == null && $request->categoria == null && $request->fabricante != null)
    {
      $consulta = DB::table('prov_marca')
      ->join('prov_fabricante', 'prov_fabricante.id_proveedor', '=' , 'prov_marca.id_proveedor')
      ->select('prov_marca.id_proveedor as id', 'prov_fabricante.id_proveedor as id')
      ->where('prov_marca.id_marca','=',$request->marca)
      ->where('prov_fabricante.id_fabricante', '=', $request->fabricante)
      ->get();

    }
    // Consulta por: marca - x - Categoria - x
    else if($request->marca != null && $request->linea == null && $request->categoria != null && $request->fabricante == null)
    {
        $consulta = DB::table('prov_marca')
      ->join('prov_categoria','prov_categoria.id_proveedor', '=' , 'prov_marca.id_proveedor')
      ->select('prov_marca.id_proveedor as id', 'prov_categoria.id_proveedor as id')
      ->where('prov_marca.id_marca','=',$request->marca)
      ->where('prov_categoria.id_categoria', '=', $request->categoria)
      ->get();

    }


    // Consulta por: x - Linea - Categoria - x
    else if($request->marca == null && $request->linea != null && $request->categoria != null && $request->fabricante == null)
    {
      $consulta = DB::table('prov_linea')
      ->join('prov_categoria','prov_categoria.id_proveedor', '=' , 'prov_linea.id_proveedor')
      ->select('prov_linea.id_proveedor as id', 'prov_categoria.id_proveedor as id')
      ->where('prov_linea.id_linea','=',$request->linea)
      ->where('prov_categoria.id_categoria', '=', $request->categoria)
      ->get();

    }
    // Consulta por: x - Linea - x - fabricante
    else if($request->marca == null && $request->linea != null && $request->categoria == null && $request->fabricante != null)
    {
      $consulta = DB::table('prov_linea')
      ->join('prov_fabricante','prov_fabricante.id_proveedor', '=' , 'prov_linea.id_proveedor')
      ->select('prov_linea.id_proveedor as id', 'prov_fabricante.id_proveedor as id')
      ->where('prov_linea.id_linea','=',$request->linea)
      ->where('prov_fabricante.id_fabricante', '=', $request->fabricante)
      ->get();

    }
    // Consulta por: x - Linea - Categoria - fabricante
    else if($request->marca == null && $request->linea != null && $request->categoria != null && $request->fabricante != null)
    {
      $consulta = DB::table('prov_linea')
      ->join('prov_categoria','prov_categoria.id_proveedor', '=' , 'prov_linea.id_proveedor')
      ->join('prov_fabricante', 'prov_fabricante.id_proveedor', '=' , 'prov_categoria.id_proveedor')
      ->select('prov_linea.id_proveedor as id', 'prov_categoria.id_proveedor as id', 'prov_fabricante.id_proveedor as id')
      ->where('prov_linea.id_linea','=',$request->linea)
      ->where('prov_categoria.id_categoria','=',$request->categoria)
      ->where('prov_fabricante.id_fabricante', '=', $request->fabricante)
      ->get();

    }
    // Consulta por: x - x - Categoria - fabricante
    else if($request->marca == null && $request->linea == null && $request->categoria != null && $request->fabricante != null)
    {
      $consulta = DB::table('prov_categoria')
      ->join('prov_fabricante','prov_fabricante.id_proveedor', '=' , 'prov_categoria.id_proveedor')
      ->select('prov_categoria.id_proveedor as id', 'prov_fabricante.id_proveedor as id')
      ->where('prov_categoria.id_categoria','=',$request->categoria)
      ->where('prov_fabricante.id_fabricante', '=', $request->fabricante)
      ->get();

    }
    else
    {
      $consulta = null;
    }

    /*......................................................
    Despues de obtener la consulta, vamos a obtener los proveedores
    ........................................................*/
    if ($consulta == null) {
      $proveedores = Contacto::where('tipo_contacto',1)->where('empresa', Auth::user()->empresa)->where('status',1)
      ->orWhere('tipo_contacto',2)->where('empresa', Auth::user()->empresa)->where('status',1)->get();
    }
    else
    {
      $consulta = $this->toarray($consulta);
      $proveedores = Contacto::whereIn('id', $consulta)->orderBy('id', 'desc')->get();
    
    }

    foreach ($proveedores as $prov) {
          $prov->tipo_empresa = $prov->tipo_empresa();
      }

      /*...........................................
      Me de de un nuevo atributo a la colecion traida con get
      .............................................*/
      $proveedores->map(function($prov){
          $prov->tipo_venta = $prov->tipo_venta();
        });
      return datatables()->of($proveedores)->make(true);
 	}

  public function getproveedoresxproducto(Request $request)
  { 
        $this->getAllPermissions(Auth::user()->id);
        $consulta = DB::table('factura_proveedores as fp')
       ->join('items_factura_proveedor as ifp','ifp.factura','=','fp.id')
       ->join('contactos as c','c.id','=','fp.proveedor')
       ->select('c.id')
       ->where('fp.empresa',Auth::user()->empresa)
       ->where('ifp.producto',$request->id)->get();


       $consulta = $this->toarray($consulta);
      $proveedores = Contacto::whereIn('id', $consulta)->orderBy('id', 'desc')->get();

       foreach ($proveedores as $prov) {
          $prov->tipo_empresa = $prov->tipo_empresa();
      }

      /*...........................................
      Me Ðade un nuevo atributo a la colecion traida con get
      .............................................*/
          $proveedores->map(function($prov){
          $prov->tipo_venta = $prov->tipo_venta();
        });

       return datatables()->of($proveedores)->make(true);
  }

    public function listabusquedaproveedor()
  { 
    
    $this->getAllPermissions(Auth::user()->id);
    view()->share(['seccion' => 'configuracion', 'title' => 'Proveedores/Clientes', 'icon' =>'fas fa-cogs']);

    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();

    $productos = Inventario::select('inventario.id','inventario.tipo_producto','inventario.producto','inventario.ref', 
        DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
        ->where('empresa',Auth::user()->empresa)
        ->where('status', 1)
        ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
        ->orderBy('id','DESC')
        ->get();

    return view('busquedas.proveedores.lista',compact('productos'));
    
  }


    public function agregarmarcaprov($id)
  {
      $this->getAllPermissions(Auth::user()->id);
  
    $proveedor = Contacto::find($id);
    
    $marcasv = DB::table('proveedor_marca')->where('tipo_marca',1)->get();
    $marcasm = DB::table('proveedor_marca')->where('tipo_marca',2)->get();

    $categoriasv = DB::table('proveedor_categoria')->where('tipo_categoria',1)->get();
    $categoriasm = DB::table('proveedor_categoria')->where('tipo_categoria',2)->get();

    $lineasv = DB::table('proveedor_linea')->where('tipo_linea',1)->get();
    $lineasm = DB::table('proveedor_linea')->where('tipo_linea',2)->get();

    $fabricantesm = DB::table('proveedor_fabricante')->where('tipo_fabricante',2)->get();
    $fabricantesv = DB::table('proveedor_fabricante')->where('tipo_fabricante',1)->get();


    /*.....................................................
                  Campos checckeados
    .......................................................*/

    $marcascheck = DB::table('prov_marca')->where('id_proveedor',$id)->get();
    $categoriascheck = DB::table('prov_categoria')->where('id_proveedor', $id)->get();
    $lineascheck = DB::table('prov_linea')->where('id_proveedor',$id)->get();
    $fabricantescheck = DB::table('prov_fabricante')->where('id_proveedor',$id)->get(); 

    view()->share(['title' => 'Proveedor - Marcas', 'icon' =>'fas fa-cogs']);

    return view('busquedas.proveedores.create',compact('proveedor','marcasm','marcasv','categoriasm','categoriasv','lineasv','lineasm','fabricantesm', 'fabricantesv', 'marcascheck', 'categoriascheck', 'lineascheck', 'fabricantescheck'));
    }

    public function asociarproveedor(Request $request)
  {
    /*.......................................................
                Guardado de marcas de un proveedor
    ...........................................................*/
    $marcascheck = DB::table('prov_marca')->where('id_proveedor', '=', $request->idproveedor)->get();

    foreach ($marcascheck as $mcheck) {
                if (isset($request->marca)) {
                    if (!in_array($mcheck->id_marca, $request->marca)) 
                     {DB::table('prov_marca')->where('id', '=', $mcheck->id)->delete();}
                }else{
                    foreach ($marcascheck as $mcheck) 
                    {DB::table('prov_marca')->where('id',$mcheck->id)->delete();}   
                }
            }

             if ($marcascheck->count() < 1) {
                if (isset($request->marca)) {
                  for ($i = 0; $i < count($request->marca); $i++) {
                     DB::table('prov_marca')->insert([
                    'id_proveedor' => $request->idproveedor,
                    'id_marca' => $request->marca[$i]
                    ]);
                  }
                }
            }

      $marcascheck = DB::table('prov_marca')->select('id_marca')->where('id_proveedor', '=', $request->idproveedor)->get();
        
        $arraydb=array();
        foreach ($marcascheck as $mcheck) {array_push($arraydb, $mcheck->id_marca);}

        if (isset($request->marca)) {
                for ($i = 0; $i < count($request->marca); $i++) {
                    if (!in_array($request->marca[$i], $arraydb)) {
                    DB::table('prov_marca')->insert([
                    'id_proveedor' => $request->idproveedor,
                    'id_marca' => $request->marca[$i]
                     ]);
                    }
                }
            }

      /*.......................................................
                Guardado de lineas de un proveedor
    ...........................................................*/
    $lineascheck = DB::table('prov_linea')->where('id_proveedor', '=', $request->idproveedor)->get();

    foreach ($lineascheck as $lcheck) {
                if (isset($request->linea)) {
                    if (!in_array($lcheck->id_linea, $request->linea)) 
                     {DB::table('prov_linea')->where('id', '=', $lcheck->id)->delete();}
                }else{
                    foreach ($lineascheck as $lcheck) 
                    {DB::table('prov_linea')->where('id',$lcheck->id)->delete();}   
                }
            }

             if ($lineascheck->count() < 1) {
                if (isset($request->linea)) {
                  for ($i = 0; $i < count($request->linea); $i++) {
                     DB::table('prov_linea')->insert([
                    'id_proveedor' => $request->idproveedor,
                    'id_linea' => $request->linea[$i]
                    ]);
                  }
                }
            }

      $lineascheck = DB::table('prov_linea')->select('id_linea')->where('id_proveedor', '=', $request->idproveedor)->get();
        
        $arraydb=array();
        foreach ($lineascheck as $lcheck) {array_push($arraydb, $lcheck->id_linea);}

        if (isset($request->linea)) {
                for ($i = 0; $i < count($request->linea); $i++) {
                    if (!in_array($request->linea[$i], $arraydb)) {
                    DB::table('prov_linea')->insert([
                    'id_proveedor' => $request->idproveedor,
                    'id_linea' => $request->linea[$i]
                     ]);
                    }
                }
            }



      /*.......................................................
                Guardado de fabricantes de un proveedor
    ...........................................................*/
    $fabricantescheck = DB::table('prov_fabricante')->where('id_proveedor', '=', $request->idproveedor)->get();

    foreach ($fabricantescheck as $fcheck) {
                if (isset($request->fabricante)) {
                    if (!in_array($fcheck->id_fabricante, $request->fabricante)) 
                     {DB::table('prov_fabricante')->where('id', '=', $fcheck->id)->delete();}
                }else{
                    foreach ($fabricantescheck as $fcheck) 
                    {DB::table('prov_fabricante')->where('id',$fcheck->id)->delete();}   
                }
            }

             if ($fabricantescheck->count() < 1) {
                if (isset($request->fabricante)) {
                  for ($i = 0; $i < count($request->fabricante); $i++) {
                     DB::table('prov_fabricante')->insert([
                    'id_proveedor' => $request->idproveedor,
                    'id_fabricante' => $request->fabricante[$i]
                    ]);
                  }
                }
            }

      $fabricantescheck = DB::table('prov_fabricante')->select('id_fabricante')->where('id_proveedor', '=', $request->idproveedor)->get();
        
        $arraydb=array();
        foreach ($fabricantescheck as $fcheck) {array_push($arraydb, $fcheck->id_fabricante);}

        if (isset($request->fabricante)) {
                for ($i = 0; $i < count($request->fabricante); $i++) {
                    if (!in_array($request->fabricante[$i], $arraydb)) {
                    DB::table('prov_fabricante')->insert([
                    'id_proveedor' => $request->idproveedor,
                    'id_fabricante' => $request->fabricante[$i]
                     ]);
                    }
                }
            }


      /*.......................................................
                Guardado de categorias de un proveedor
    ...........................................................*/
    $categoriascheck = DB::table('prov_categoria')->where('id_proveedor', '=', $request->idproveedor)->get();

    foreach ($categoriascheck as $ccheck) {
                if (isset($request->categoria)) {
                    if (!in_array($ccheck->id_categoria, $request->categoria)) 
                     {DB::table('prov_categoria')->where('id', '=', $ccheck->id)->delete();}
                }else{
                    foreach ($categoriascheck as $ccheck) 
                    {DB::table('prov_categoria')->where('id',$ccheck->id)->delete();}   
                }
            }

             if ($categoriascheck->count() < 1) {
                if (isset($request->categoria)) {
                  for ($i = 0; $i < count($request->categoria); $i++) {
                     DB::table('prov_categoria')->insert([
                    'id_proveedor' => $request->idproveedor,
                    'id_categoria' => $request->categoria[$i]
                    ]);
                  }
                }
            }

      $categoriascheck = DB::table('prov_categoria')->select('id_categoria')->where('id_proveedor', '=', $request->idproveedor)->get();
        
        $arraydb=array();
        foreach ($categoriascheck as $ccheck) {array_push($arraydb, $ccheck->id_categoria);}

        if (isset($request->categoria)) {
                for ($i = 0; $i < count($request->categoria); $i++) {
                    if (!in_array($request->categoria[$i], $arraydb)) {
                    DB::table('prov_categoria')->insert([
                    'id_proveedor' => $request->idproveedor,
                    'id_categoria' => $request->categoria[$i]
                     ]);
                    }
                }
            }

        return redirect('busquedaproveedor')->with('success','Proveedor asociado correctamente');
   
  }

  public function guardarcampop(Request $request)
  {
    $id = "";
    if($request->nombre != null || $request->nombre != "") 
    {
    if ($request->tipo == "lineav") {
      if (DB::table('proveedor_linea')->where('nombre', $request->nombre)->where('tipo_linea',1)->count() == 0) {
        $id =  DB::table('proveedor_linea')->insertGetId([
          'nombre' => $request->nombre,
          'tipo_linea' => 1,
        ]);
      }
      
    }else if ($request->tipo == "lineam") {
      if (DB::table('proveedor_linea')->where('nombre', $request->nombre)->where('tipo_linea',2)->count() == 0) {
      $id =  DB::table('proveedor_linea')->insertGetId([
          'nombre' => $request->nombre,
          'tipo_linea' => 2,
        ]);
    }
    } else if ($request->tipo == "categoriav") {
      if (DB::table('proveedor_categoria')->where('nombre', $request->nombre)->where('tipo_categoria',1)->count() == 0) {
       $id = DB::table('proveedor_categoria')->insertGetId([
          'nombre' => $request->nombre,
          'tipo_categoria' => 1,
        ]);
     }

    } else if ($request->tipo == "categoriam") {
      if (DB::table('proveedor_categoria')->where('nombre', $request->nombre)->where('tipo_categoria',2)->count() == 0) {
      $id =   DB::table('proveedor_categoria')->insertGetId([
          'nombre' => $request->nombre,
          'tipo_categoria' => 2,
        ]);
    }
    } else if ($request->tipo == "fabricantev") {
      if (DB::table('proveedor_fabricante')->where('nombre', $request->nombre)->where('tipo_fabricante',1)->count() == 0) {
      $id =   DB::table('proveedor_fabricante')->insertGetId([
          'nombre' => $request->nombre,
          'tipo_fabricante' => 1,
        ]);
    }
    } else if ($request->tipo == "fabricantem") {
      if (DB::table('proveedor_fabricante')->where('nombre', $request->nombre)->where('tipo_fabricante',2)->count() == 0) {
      $id =   DB::table('proveedor_fabricante')->insertGetId([
          'nombre' => $request->nombre,
          'tipo_fabricante' => 2,
        ]);
    }
    }
    return response()->json(['tipo' => $request->tipo , 'nombre' => $request->nombre, 'id' => $id]);
    }
    //return back()->with('success','Campo creado correctamente');
  }

   public function llenarbusquedaproveedor(Request $request)
  {
    /*...............................................
     si es igual a 1 entonces se quiere realizar busqueda de vehiculares
    .................................................*/
    if ($request->campo_id == 1) {
        $marcas = DB::table('proveedor_marca')->where('tipo_marca','=',1)->get();
        $lineas = DB::table('proveedor_linea')->where('tipo_linea','=',1)->get();
        $categorias = DB::table('proveedor_categoria')->where('tipo_categoria','=',1)->get();
        $fabricantes = DB::table('proveedor_fabricante')->where('tipo_fabricante','=',1)->get();
    }
    else if($request->campo_id == 2)
    {
      $marcas = DB::table('proveedor_marca')->where('tipo_marca','=',2)->get();
        $lineas = DB::table('proveedor_linea')->where('tipo_linea','=',2)->get();
        $categorias = DB::table('proveedor_categoria')->where('tipo_categoria','=',2)->get();
        $fabricantes = DB::table('proveedor_fabricante')->where('tipo_fabricante','=',2)->get();
    }

    return response()->json(['marcas' => $marcas, 'lineas' => $lineas, 'categorias' => $categorias, 'fabricantes' => $fabricantes]);
  }

  public function configurarcampos()
  {
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['seccion' => 'configuracion', 'title' => 'Configuracion Campos Proveedores', 'icon' =>'']);

    $marcasv = DB::table('proveedor_marca')->where('tipo_marca',1)->get();
    $marcasm = DB::table('proveedor_marca')->where('tipo_marca',2)->get();

    $categoriasv = DB::table('proveedor_categoria')->where('tipo_categoria',1)->get();
    $categoriasm = DB::table('proveedor_categoria')->where('tipo_categoria',2)->get();

    $lineasv = DB::table('proveedor_linea')->where('tipo_linea',1)->get();
    $lineasm = DB::table('proveedor_linea')->where('tipo_linea',2)->get();

    $fabricantesm = DB::table('proveedor_fabricante')->where('tipo_fabricante',2)->get();
    $fabricantesv = DB::table('proveedor_fabricante')->where('tipo_fabricante',1)->get();

    return view('busquedas.proveedores.campos', compact('marcasv','marcasm','categoriasv','categoriasm','lineasv','lineasm','fabricantesm','fabricantesv'));

  }

   public function toarray($object)
    {
        $array = array();
        foreach ($object as $value) {
            $array[] = $value->id;
        }
        return $array;
    }

  
}