<?php

namespace App\Http\Controllers;
use App\Contacto;
use App\Empresa;
use App\Retencion;
use Illuminate\Http\Request;
use App\Categoria;  use App\Impuesto;  
use App\CamposExtra;
use App\Model\Inventario\Inventario; 
use App\Model\Inventario\Bodega;
use App\Model\Inventario\ListaPrecios; 
use App\Model\Inventario\ProductosBodega; 
use App\Model\Inventario\ProductosPrecios;
use App\TipoEmpresa;
use App\TipoIdentificacion; 
use App\Vendedor;
use Carbon\Carbon;
use Image; use File;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Session;
use Validator;
use App\Funcion;
use Illuminate\Validation\Rule; 
use Auth; use DB;   
include_once(app_path() .'/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel; use PHPExcel_IOFactory; use PHPExcel_Style_Alignment; use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use ZipArchive;
use PHPExcel_Shared_ZipArchive; 
use App\Puc;
use App\ProductoServicio;
use App\ProductoCuenta;

class InventarioController extends Controller{
    public $id;
    
    public function __construct() {
        $this->middleware('auth');
        view()->share(['inicio' => 'master', 'seccion' => 'inventario', 'title' => 'Inventario', 'icon' =>'fas fa-boxes', 'subseccion'=>'items_venta']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['invert'=>true]);
        $busqueda=false;
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $campos=array('', 'ref', 'producto', 'precio', 'disp', 'publico');
        $tabla = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->orderBy('tabla')->get();
        $pagination = 25;
        
        if($request->itemsPage == 2 || $request->itemsPage == 3){
            $pagination = $request->itemsPage == 2 ? 50 : 100;
        }
        
        //Tomo los campos extra
        foreach ($tabla as $key => $value) {
            $campos[]="extr_".$value->campo;
        }
        
        //Si no hay datos registrados
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        
        $select=array('inventario.*', DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inventario.id) as disp'));
        
        if ($request->lista && $request->lista>1) {
            $precio=ListaPrecios::where('empresa', Auth::user()->empresa)->where('nro', $request->lista)->first();
            $select[]='pp.precio as precio';
            $campos[3]=$orderby='pp.precio';
            $productos = Inventario::join('productos_precios as pp', 'pp.producto', '=', 'inventario.id')->where('pp.lista', $precio->id)->select($select);
        }else{
            $productos = Inventario::select($select);
        }
        
        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
        $productos = $productos->where('inventario.empresa',Auth::user()->empresa)->whereIn('type',['MATERIAL','MODEMS']);
        
        if ($request->name_1) {
            $busqueda=true; $appends['name_1']=$request->name_1; $productos=$productos->where('inventario.ref', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
            $busqueda=true; $appends['name_2']=$request->name_2; $productos=$productos->where('inventario.producto', 'like', '%' .$request->name_2.'%');
        }
        if ($request->name_3) {
            $busqueda=true; $appends['name_3']=$request->name_3; $appends['name_3_simb']=$request->name_3_simb; $productos=$productos->where($campos[3], $request->name_3_simb, $request->name_3);
        }
        if ($request->name_4) {
            $busqueda=true; $appends['name_4']=$request->name_4; $appends['name_4_simb']=$request->name_4_simb; $productos=$productos->whereRaw(DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inventario.id) '.$request->name_4_simb.$request->name_4));
        }
        if ($request->name_5) {
            $busqueda=true; $appends['name_5']=$request->name_5; $productos=$productos->where('publico', $request->name_5);
        }
        
        $cont=6;
        foreach ($tabla as $key => $value) {
            $tite='name_'.$cont;
            if ($request->$tite) {
                $busqueda=true;
                $appends[$tite]=$request->$tite;
                $productos=$productos->leftjoin('inventario_meta','id_producto','=','inventario.id')->where('meta_key',$value->campo)->where('meta_value','LIKE','%'. $request->$tite. '%');
            }
            $cont++;
        }
        
        if(!($request->name_1 || $request->name_2 || $request->name_3 || $request->name_4 || $request->name_5)){
            $productos = $productos->OrderBy('id', 'DESC')->paginate($pagination)->appends($appends);
        }else{
            $productos = $productos->OrderBy($orderby, $order)->paginate($pagination)->appends($appends);
        }
        
        $totalProductos= Inventario::where('empresa',Auth::user()->empresa)->where('status',1)->whereIn('type',['MATERIAL','MODEMS'])->count();
        view()->share(['title' => 'Productos']);
        $type = '';
        return view('inventario.index1')->with(compact('totalProductos','productos', 'tabla', 'request', 'listas', 'busqueda', 'type'));
    }
    
    public function modems(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['invert'=>true]);
        $busqueda=false;
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $campos=array('', 'ref', 'producto', 'precio', 'disp', 'publico');
        $tabla = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->orderBy('tabla')->get();
        $pagination = 25;
        if($request->itemsPage == 2 || $request->itemsPage == 3){
            $pagination = $request->itemsPage == 2 ? 50 : 100;
        }
        foreach ($tabla as $key => $value) {
            $campos[]="extr_".$value->campo;
        }
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        $select=array('inventario.*',DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inventario.id) as disp'));
        
        if ($request->lista && $request->lista>1) {
            $precio=ListaPrecios::where('empresa', Auth::user()->empresa)->where('nro', $request->lista)->first();
            $select[]='pp.precio as precio';
            $campos[3]=$orderby='pp.precio';
            $productos = Inventario::join('productos_precios as pp', 'pp.producto', '=', 'inventario.id')->where('pp.lista', $precio->id)->select($select);
        }else{
            $productos = Inventario::select($select);
        }
        
        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
        $productos = $productos->where('inventario.empresa',Auth::user()->empresa)->where('type','MODEMS');
        if ($request->name_1) {
            $busqueda=true; $appends['name_1']=$request->name_1; $productos=$productos->where('inventario.ref', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
            $busqueda=true; $appends['name_2']=$request->name_2; $productos=$productos->where('inventario.producto', 'like', '%' .$request->name_2.'%');
        }
        if ($request->name_3) {
            $busqueda=true; $appends['name_3']=$request->name_3; $appends['name_3_simb']=$request->name_3_simb; $productos=$productos->where($campos[3], $request->name_3_simb, $request->name_3);
        }
        if ($request->name_4) {
            $busqueda=true; $appends['name_4']=$request->name_4; $appends['name_4_simb']=$request->name_4_simb; $productos=$productos->whereRaw(DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inventario.id) '.$request->name_4_simb.$request->name_4));
        }
        if ($request->name_5) {
            $busqueda=true; $appends['name_5']=$request->name_5; $productos=$productos->where('publico', $request->name_5);
        }
        
        $cont=6;
        foreach ($tabla as $key => $value) {
            $tite='name_'.$cont;
            if ($request->$tite) {
                $busqueda=true;
                $appends[$tite]=$request->$tite;
                $productos=$productos->leftjoin('inventario_meta','id_producto','=','inventario.id')->where('meta_key',$value->campo)->where('meta_value','LIKE','%'. $request->$tite. '%');
            }
            $cont++;
        }
        if(!($request->name_1 || $request->name_2 || $request->name_3 || $request->name_4 || $request->name_5)){
            $productos = $productos->OrderBy('id', 'DESC')->paginate($pagination)->appends($appends);
        }else{
            $productos = $productos->OrderBy($orderby, $order)->paginate($pagination)->appends($appends);
        }
        
        $totalProductos= Inventario::where('empresa',Auth::user()->empresa)->where('status',1)->where('type','MODEMS')->count();
        view()->share(['seccion' => 'inventario', 'title' => 'Módems', 'icon' =>'fas fa-boxes', 'subseccion'=>'modems']);
        $type = 'MODEMS';
        return view('inventario.index1')->with(compact('totalProductos','productos', 'tabla', 'request', 'listas', 'busqueda', 'type'));
    }
    
    public function material(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['invert'=>true]);
        $busqueda=false;
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $campos=array('', 'ref', 'producto', 'precio', 'disp', 'publico');
        $tabla = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->orderBy('tabla')->get();
        $pagination = 25;
        if($request->itemsPage == 2 || $request->itemsPage == 3){
            $pagination = $request->itemsPage == 2 ? 50 : 100;
        }
        foreach ($tabla as $key => $value) {
            $campos[]="extr_".$value->campo;
        }
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        $select=array('inventario.*',DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inventario.id) as disp'));
        
        if ($request->lista && $request->lista>1) {
            $precio=ListaPrecios::where('empresa', Auth::user()->empresa)->where('nro', $request->lista)->first();
            $select[]='pp.precio as precio';
            $campos[3]=$orderby='pp.precio';
            $productos = Inventario::join('productos_precios as pp', 'pp.producto', '=', 'inventario.id')->where('pp.lista', $precio->id)->select($select);
        }else{
            $productos = Inventario::select($select);
        }
        
        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
        $productos = $productos->where('inventario.empresa',Auth::user()->empresa)->whereIn('type',['MATERIAL','MODEMS']);
        if ($request->name_1) {
            $busqueda=true; $appends['name_1']=$request->name_1; $productos=$productos->where('inventario.ref', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
            $busqueda=true; $appends['name_2']=$request->name_2; $productos=$productos->where('inventario.producto', 'like', '%' .$request->name_2.'%');
        }
        if ($request->name_3) {
            $busqueda=true; $appends['name_3']=$request->name_3; $appends['name_3_simb']=$request->name_3_simb; $productos=$productos->where($campos[3], $request->name_3_simb, $request->name_3);
        }
        if ($request->name_4) {
            $busqueda=true; $appends['name_4']=$request->name_4; $appends['name_4_simb']=$request->name_4_simb; $productos=$productos->whereRaw(DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inventario.id) '.$request->name_4_simb.$request->name_4));
        }
        if ($request->name_5) {
            $busqueda=true; $appends['name_5']=$request->name_5; $productos=$productos->where('publico', $request->name_5);
        }
        
        $cont=6;
        foreach ($tabla as $key => $value) {
            $tite='name_'.$cont;
            if ($request->$tite) {
                $busqueda=true;
                $appends[$tite]=$request->$tite;
                $productos=$productos->leftjoin('inventario_meta','id_producto','=','inventario.id')->where('meta_key',$value->campo)->where('meta_value','LIKE','%'. $request->$tite. '%');
            }
            $cont++;
        }
        if(!($request->name_1 || $request->name_2 || $request->name_3 || $request->name_4 || $request->name_5)){
            $productos = $productos->OrderBy('id', 'DESC')->paginate($pagination)->appends($appends);
        }else{
            $productos = $productos->OrderBy($orderby, $order)->paginate($pagination)->appends($appends);
        }
        $totalProductos= Inventario::where('empresa',Auth::user()->empresa)->where('status',1)->whereIn('type',['MATERIAL','MODEMS'])->count();
        view()->share(['seccion' => 'inventario', 'title' => 'Productos', 'icon' =>'fas fa-boxes', 'subseccion'=>'material']);
        $type = 'MATERIAL';
        return view('inventario.index1')->with(compact('totalProductos','productos', 'tabla', 'request', 'listas', 'busqueda', 'type'));
    }

    public function television(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $busqueda=false;
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $campos=array('', 'ref', 'producto', 'precio', 'disp', 'publico');
        $tabla = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->orderBy('tabla')->get();
        $pagination = 25;
        if($request->itemsPage == 2 || $request->itemsPage == 3){
            $pagination = $request->itemsPage == 2 ? 50 : 100;
        }
        foreach ($tabla as $key => $value) {
            $campos[]="extr_".$value->campo;
        }
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        $select=array('inventario.*',DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inventario.id) as disp'));

        if ($request->lista && $request->lista>1) {
            $precio=ListaPrecios::where('empresa', Auth::user()->empresa)->where('nro', $request->lista)->first();
            $select[]='pp.precio as precio';
            $campos[3]=$orderby='pp.precio';
            $productos = Inventario::join('productos_precios as pp', 'pp.producto', '=', 'inventario.id')->where('pp.lista', $precio->id)->select($select);
        }else{
            $productos = Inventario::select($select);
        }

        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
        $productos = $productos->where('inventario.empresa',Auth::user()->empresa)->where('type','TV');
        if ($request->name_1) {
            $busqueda=true; $appends['name_1']=$request->name_1; $productos=$productos->where('inventario.ref', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
            $busqueda=true; $appends['name_2']=$request->name_2; $productos=$productos->where('inventario.producto', 'like', '%' .$request->name_2.'%');
        }
        if ($request->name_3) {
            $busqueda=true; $appends['name_3']=$request->name_3; $appends['name_3_simb']=$request->name_3_simb; $productos=$productos->where($campos[3], $request->name_3_simb, $request->name_3);
        }
        if ($request->name_4) {
            $busqueda=true; $appends['name_4']=$request->name_4; $appends['name_4_simb']=$request->name_4_simb; $productos=$productos->whereRaw(DB::raw('(SELECT sum(nro) from productos_bodegas WHERE producto=inventario.id) '.$request->name_4_simb.$request->name_4));
        }
        if ($request->name_5) {
            $busqueda=true; $appends['name_5']=$request->name_5; $productos=$productos->where('publico', $request->name_5);
        }

        $cont=6;
        foreach ($tabla as $key => $value) {
            $tite='name_'.$cont;
            if ($request->$tite) {
                $busqueda=true;
                $appends[$tite]=$request->$tite;
                $productos=$productos->leftjoin('inventario_meta','id_producto','=','inventario.id')->where('meta_key',$value->campo)->where('meta_value','LIKE','%'. $request->$tite. '%');
            }
            $cont++;
        }
        if(!($request->name_1 || $request->name_2 || $request->name_3 || $request->name_4 || $request->name_5)){
            $productos = $productos->OrderBy('id', 'DESC')->paginate($pagination)->appends($appends);
        }else{
            $productos = $productos->OrderBy($orderby, $order)->paginate($pagination)->appends($appends);
        }

        $totalProductos= Inventario::where('empresa',Auth::user()->empresa)->where('status',1)->where('type','TV')->count();
        $type = 'TV';
        view()->share(['seccion' => 'inventario', 'title' => 'Planes de Televisión', 'icon' =>'fas fa-boxes', 'subseccion'=>'planes_tv']);
        return view('inventario.index1')->with(compact('totalProductos','productos', 'tabla', 'request', 'listas', 'busqueda', 'type'));
    }
    
    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        $empresa = Auth::user()->empresa;
        $listas = ListaPrecios::where('empresa',$empresa)->where('status', 1)->where('id','>',1)->get();
        $impuestos = Impuesto::where('empresa',$empresa)->orWhere('empresa', null)->where('estado', 1)->get();
        $medidas=DB::table('medidas')->get();
        $bodegas = Bodega::where('empresa',$empresa)->where('status', 1)->get();
        $unidades=DB::table('unidades_medida')->get();
        view()->share(['icon' =>'', 'title' => 'Nuevo Producto']);
        $extras = CamposExtra::where('empresa',$empresa)->where('status', 1)->get();
        
        $identificaciones=TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa',$empresa)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa',$empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',$empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        // $cuentas = Puc::where('empresa',$empresa)->where('estatus',1)->get();
        
        //Tomar las categorias del puc que no son transaccionables.
        $cuentas = Puc::where('empresa',$empresa)
        ->where('estatus',1)
        ->whereRaw('length(codigo) > 6')
        ->get();

        $autoRetenciones = Retencion::where('empresa',Auth::user()->empresa)->where('estado',1)->where('modulo',2)->get();
        $type = '';
        return view('inventario.create')->with(compact('unidades', 'medidas', 'impuestos', 'extras', 'listas', 'bodegas','identificaciones', 'tipos_empresa', 'prefijos', 'vendedores', 'listas','cuentas', 'type','autoRetenciones'));
    }

    public function television_create(){
        $this->getAllPermissions(Auth::user()->id);
        $empresa = Auth::user()->empresa;
        $listas = ListaPrecios::where('empresa',$empresa)->where('status', 1)->where('id','>',1)->get();
        $impuestos = Impuesto::where('empresa',$empresa)->orWhere('empresa', null)->where('estado', 1)->get();
        $categorias=Categoria::where('empresa',$empresa)->where('estatus', 1)->whereNull('asociado')->get();
        $medidas=DB::table('medidas')->get();
        $bodegas = Bodega::where('empresa',$empresa)->where('status', 1)->get();
        $unidades=DB::table('unidades_medida')->get();
        view()->share(['icon' =>'', 'title' => 'Nuevo Plan de Televisión']);
        $extras = CamposExtra::where('empresa',$empresa)->where('status', 1)->get();

        $identificaciones=TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa',$empresa)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa',$empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',$empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        $type = 'TV';
        //Tomar las categorias del puc que no son transaccionables.
        $cuentas = Puc::where('empresa',$empresa)
        ->where('estatus',1)
        ->whereRaw('length(codigo) > 6')
        ->get();
        $autoRetenciones = Retencion::where('empresa',$empresa)->where('estado',1)->where('modulo',2)->get();
        return view('inventario.create')->with(compact('categorias', 'unidades', 'medidas', 'impuestos', 'extras', 'listas', 'bodegas','identificaciones', 'tipos_empresa', 'prefijos', 'vendedores', 'listas','cuentas', 'type', 'autoRetenciones'));
    }
    
    public function store(Request $request){

        $request->validate([
            'producto' => 'required',
            'impuesto' => 'required|numeric',
            'tipo_producto' => 'required|numeric'
        ]);
        
        if ($request->imagen) {
            $request->validate([
                //'imagen'=>'mimes:jpeg,jpg,png| max:1000'
            ],['imagen.mimes' => 'La extensión del imagen debe ser jpeg, jpg, png',
                //'imagen.max' => 'El peso máximo para el imagen es de 1000KB',
            ]);
        }
        
        $errors= (object) array();
        if ($request->ref) {
            $error =Inventario::where('ref', $request->ref)->where('empresa',Auth::user()->empresa)->count();
            if ($error>0) {
                $errors->ref='El código de referencia ya se encuentra registrado para otro producto';
                return back()->withErrors($errors)->withInput();
            }
        }
        $impuesto = Impuesto::where('id', $request->impuesto)->first();
        $inventario = new Inventario;
        $inventario->empresa=Auth::user()->empresa;
        $inventario->producto=ucwords($request->producto);
        $inventario->ref=$request->ref;
        $inventario->descripcion=mb_strtolower($request->descripcion);
        $inventario->linea = $request->linea;
        $inventario->precio=$this->precision($request->precio);
        $inventario->id_impuesto=$request->impuesto;
        $inventario->type=$request->type;
        if($request->publico){
            $inventario->publico=$request->publico;
        }
        $inventario->impuesto=$impuesto->porcentaje;
        $inventario->tipo_producto=$request->tipo_producto;
        $inventario->unidad=1;$inventario->nro=0;
        $inventario->categoria=$request->categoria;
        $inventario->lista = 0;
        $inventario->link = $request->link;
        $inventario->type_autoretencion = $request->tipo_autoretencion;
        $inventario->save();
        
        if ($request->tipo_producto==1) {
            $request->validate([
                'unidad' => 'required|exists:unidades_medida,id',
                'costo_unidad' => 'required|numeric'
            ]);
            if ($request->bodega) {
                foreach ($request->bodega as $key => $value) {
                    if ($request->bodegavalor[$key]) {
                        $bodega = new ProductosBodega;
                        $bodega->empresa=Auth::user()->empresa;;
                        $bodega->bodega=$value;
                        $bodega->producto=$inventario->id;
                        $bodega->nro=$request->bodegavalor[$key];
                        $bodega->inicial=$request->bodegavalor[$key];
                        $bodega->save();
                    }
                }
            }
            $inventario->unidad=$request->unidad;
            $inventario->costo_unidad=$this->precision($request->costo_unidad);
            $inventario->save();
        }
        
        if ($request->preciolista) {
            foreach ($request->preciolista as $key => $value) {
                if ($request->preciolistavalor[$key]) {
                    $precio = new ProductosPrecios;
                    $precio->empresa=Auth::user()->empresa;;
                    $precio->lista=$value;
                    $precio->producto=$inventario->id;
                    $precio->precio=$this->precision($request->preciolistavalor[$key]);
                    $precio->save();
                }
            }
        }

        //Desarrollo pendiente de cuentas por producto
        if ($request->cuentacontable) {
            foreach ($request->cuentacontable as $key => $value) {
                    DB::table('producto_cuentas')->insert([
                        'cuenta_id' => $value,
                        'inventario_id' => $inventario->id
                    ]);
            }
        }

        //introduccion de cuentas de productos y servicios (inv, costo, venta y dev).
        if(isset($request->inventario)){
            $pr = new ProductoCuenta;
            $pr->cuenta_id = $request->inventario;
            $pr->inventario_id = $inventario->id;
            $pr->tipo = 1;
            $pr->save();
        }
       
        if(isset($request->costo)){
            $pr = new ProductoCuenta;
            $pr->cuenta_id = $request->costo;
            $pr->inventario_id = $inventario->id;
            $pr->tipo = 2;
            $pr->save();
        }

        if(isset($request->venta)){
            $pr = new ProductoCuenta;
            $pr->cuenta_id = $request->venta;
            $pr->inventario_id = $inventario->id;
            $pr->tipo = 3;
            $pr->save();
        }

        if(isset($request->devolucion)){
            $pr = new ProductoCuenta;
            $pr->cuenta_id = $request->devolucion;
            $pr->inventario_id = $inventario->id;
            $pr->tipo = 4;
            $pr->save();
        }

        if(isset($request->autoretencion)){
            $pr = new ProductoCuenta;
            $pr->cuenta_id = $request->autoretencion;
            $pr->inventario_id = $inventario->id;
            $pr->tipo = 5;
            $pr->save();
        }


        
        $inserts=array();
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        foreach ($extras as $campo) {
            $extra='ext_'.$campo->campo;
            if ($request->$extra) {
                $insert=array('empresa'=>Auth::user()->empresa,
                'id_producto'=>$inventario->id,
                'meta_key'=>$campo->campo,
                'meta_value'=>$request->$extra);
                $inserts[]=$insert;
            }
        }
        
        if (count($inserts)>0) {
            DB::table('inventario_meta')->insert($inserts);
        }
        
        //-----------------------LIBRERIA INVENTORY IMAGE-------------------------------//
        if($request->imagen) {       
            $imagen = $request->file('imagen');
            $nombre_imagen = $inventario->id.'.'.$imagen->getClientOriginalExtension();
            $imagen = Image::make($imagen);
            $request->imagen=$nombre_imagen;
            $path = public_path() . '/images/Empresas/Empresa'.Auth::user()->empresa;
            if(!File::exists($path)){
                File::makeDirectory($path);
            }
            $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa.'/inventario';
            if (!File::exists($path)) {
                File::makeDirectory($path);
            }
            if ($imagen->filesize() > 200000) {
                $imagen->resize(1000,1000,function($constraint){$constraint->aspectRatio();})->save($path.'/'.$nombre_imagen);
                $inventario->imagen=$nombre_imagen;
                $inventario->save();
            }else{ 
                $imagen->save($path.'/'.$nombre_imagen);
                $inventario->imagen=$nombre_imagen;
                $inventario->save();
            }
        }
        
        if ($request->imagenes_extra) {
            foreach ($request->file('imagenes_extra') as $key => $imagen_ex) {
                $nombre_imagen = time().random_int(1000, 99999).'.'.$imagen_ex->getClientOriginalExtension();
                $imagen_extra = Image::make($imagen_ex);
                $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa.'/inventario/'.$inventario->id; 
                if(!File::exists($path)) {
                    File::makeDirectory($path);
                }
                if($imagen_extra->filesize() > 200000) {
                    $imagen_extra->resize(1000,1000,function($constraint){$constraint->aspectRatio();});
                    $imagen_extra->save($path.'/'.$nombre_imagen);
                }else{
                    $imagen_extra->save($path.'/'.$nombre_imagen);
                }
                
                DB::table('imagenesxinventario')->insert(
                    ['producto' => $inventario->id, 'imagen' => $nombre_imagen, 'created_at'=>Carbon::now()]
                );
            }   
        }
        //----------------------/LIBRERIA INVENTORY IMAGE-------------------------------//
        
        $mensaje='Registro creado satisfactoriamente el producto';
        if($inventario->type == 'TV'){
            return redirect('empresa/inventario/television')->with('success', 'Se ha registrado satisfactoriamente el plan de televisión')->with('producto_id', $inventario->id);
        }
        return redirect('empresa/inventario')->with('success', $mensaje)->with('producto_id', $inventario->id);
        if($request->type == 'PLAN'){
            return redirect('empresa/inventario')->with('success', $mensaje)->with('producto_id', $inventario->id);
        }elseif($request->type == 'MATERIAL' || $request->type == 'MODEMS'){
            return redirect('empresa/inventario/material')->with('success', $mensaje)->with('producto_id', $inventario->id);
        }else{
            return redirect('empresa/inventario/modem')->with('success', $mensaje)->with('producto_id', $inventario->id);
        }
    }
  
    public function storeBack(Request $request){
        $preApp = $request->toUrl != '' ? $request->toUrl : false;
        
        $request->validate([
            'producto' => 'required',
            'categoria' => 'required|exists:categorias,id',
            'impuesto' => 'required|numeric',
            'tipo_producto' => 'required|numeric',
        ]);
        if ($request->imagen) {
            $request->validate([
                //'imagen'=>'mimes:jpeg,jpg,png| max:1000'
            ],['imagen.mimes' => 'La extensión del imagen debe ser jpeg, jpg, png',
                //'imagen.max' => 'El peso máximo para el imagen es de 1000KB',
            ]);
        }
        
        $errors= (object) array();
        
        if ($request->ref) {
            $error =Inventario::where('ref', $request->ref)->where('empresa',Auth::user()->empresa)->count();
            if ($error>0) {
                $arrayPost['status']  = 'error';
                $arrayPost['mensaje'] = 'El código de referencia ya se encuentra registrado para otro producto';
                echo json_encode($arrayPost);
                exit;
            }
        }
        
        $impuesto = Impuesto::where('id', $request->impuesto)->first();
        $inventario = new Inventario;
        $inventario->empresa=Auth::user()->empresa;
        $inventario->producto=ucwords($request->producto);
        $inventario->ref=$request->ref;
        $inventario->descripcion=mb_strtolower($request->descripcion);
        $inventario->precio=$this->precision($request->precio);
        $inventario->id_impuesto=$request->impuesto;
        $inventario->type='MATERIAL';
        if($request->publico){
            $inventario->publico=$request->publico;
        }
        $inventario->impuesto=$impuesto->porcentaje;
        $inventario->tipo_producto=$request->tipo_producto;
        $inventario->unidad=1;$inventario->nro=0;
        $inventario->categoria=$request->categoria;
        $inventario->lista = 0;
        $inventario->save();
        
        if ($request->tipo_producto==1) {
            $request->validate([
                'unidad' => 'required|exists:unidades_medida,id',
                'costo_unidad' => 'required|numeric'
            ]);
            if ($request->bodega) {
                foreach ($request->bodega as $key => $value) {
                    $bodega = new ProductosBodega;
                    $bodega->empresa=Auth::user()->empresa;;
                    $bodega->bodega=$value;
                    $bodega->producto=$inventario->id;
                    $bodega->nro=$request->bodegavalor[$key];
                    $bodega->inicial=$request->bodegavalor[$key];
                    $bodega->save();
                }
            }
            $inventario->unidad=$request->unidad;
            $inventario->costo_unidad=$this->precision($request->costo_unidad);
            $inventario->save();
        }
        
        if ($request->preciolista) {
            foreach ($request->preciolista as $key => $value) {
                if ($request->preciolistavalor[$key]) {
                    $precio = new ProductosPrecios;
                    $precio->empresa=Auth::user()->empresa;;
                    $precio->lista=$value;
                    $precio->producto=$inventario->id;
                    $precio->precio=$this->precision($request->preciolistavalor[$key]);
                    $precio->save();
                }
            }
        }
        
        $inserts=array();
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        foreach ($extras as $campo) {
            $extra='ext_'.$campo->campo;
            if ($request->$extra) {
                $insert=array('empresa'=>Auth::user()->empresa, 'id_producto'=>$inventario->id, 'meta_key'=>$campo->campo, 'meta_value'=>$request->$extra);
                $inserts[]=$insert;
            }
        }
        
        if (count($inserts)>0) {
            DB::table('inventario_meta')->insert($inserts);
        }
        
        $productId = Inventario::all()->last()->id;
        $product   = Inventario::all()->last()->producto;
        
        if($preApp != false){
            //return redirect()->to($preApp.'?'.http_build_query(['pro' => $product]));
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'No se pudo realizar el registro. Verifique los datos';
            echo json_encode($arrayPost);
            exit;
        }else{
            //return $this->createModal($product);
            $arrayPost['status']  = 'OK';
            $arrayPost['id'] = $productId;
            $arrayPost['producto'] = $product;
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function createModal($item){
        view()->share(['icon' =>'', 'title' => 'Nueva Facturas de Proveedores', 'subseccion' => 'facturas_proveedores']);
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        $inventario =
        Inventario::select('inventario.*',
        DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
        ->where('empresa',Auth::user()->empresa)
        ->where('status', 1)->get();
        $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        
        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
        $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();
        $identificaciones=TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        $dataPro = (new InventarioController)->create();
        $categorias2 = $dataPro->categorias;
        $unidades2 = $dataPro->unidades;
        $medidas2 = $dataPro->medidas;
        $impuestos2 = $dataPro->impuestos;
        $extras2 = $dataPro->extras;
        $listas2 = $dataPro->listas;
        $bodegas2 = $dataPro->bodegas;
        $identificaciones2 = $dataPro->identificaciones;
        $tipos_empresa2 = $dataPro->tipos_empresa;
        $prefijos2 = $dataPro->prefijos;
        $vendedores2 = $dataPro->vendedores;
        $producto = Inventario::where('id',$item)->where('empresa',Auth::user()->empresa)->first();
        $proveedor=false;
        
        view()->share(['icon' =>'', 'title' => 'Nueva Factura de Compra', 'subseccion' => 'facturas_proveedores']);
        return view('facturasp.create')->with(compact('inventario', 'bodegas', 'clientes', 'impuestos', 'categorias', 'retenciones',
        'proveedor', 'producto','identificaciones', 'tipos_empresa', 'prefijos', 'vendedores', 'listas',
        'categorias2', 'unidades2','medidas2', 'impuestos2', 'extras2', 'listas2', 'bodegas2', 'identificaciones2',
        'tipos_empresa2', 'prefijos2', 'vendedores2'));
    }
    
    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $inventario =Inventario::where('id',$id)->where('empresa',Auth::user()->empresa)->first();
        if ($inventario) {
            view()->share(['title' => $inventario->producto, 'icon' =>' ', 'middel'=>true,]);
            $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            return view('inventario.show')->with(compact('inventario', 'extras'));
        }
        return redirect('empresa/inventario')->with('success', 'No existe un registro con ese id');
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $empresa = Auth::user()->empresa;
        $listas = ListaPrecios::where('empresa',$empresa)->where('status', 1)->where('nro','>',1)->get();
        $impuestos = Impuesto::where('empresa',$empresa)->orWhere('empresa', null)->where('estado', 1)->get();
        $categorias=Categoria::where('empresa',$empresa)->where('estatus', 1)->whereNull('asociado')->get();
        $bodegas = Bodega::where('empresa',$empresa)->where('status', 1)->get();
        $inventario =Inventario::where('id',$id)->where('empresa',$empresa)->first();
        // $cuentas = ProductoServicio::where('en_uso',1)->get();
        $cuentas = Puc::where('empresa',$empresa)
        ->where('estatus',1)
        ->whereRaw('length(codigo) > 6')
        ->get();

        $autoRetenciones = Retencion::where('empresa',Auth::user()->empresa)->where('estado',1)->where('modulo',2)->get();
        
        if ($inventario) {
            $categorias=Categoria::where('empresa',$empresa)->whereNull('asociado')->get();
            $extras = CamposExtra::where('empresa',$empresa)->where('status', 1)->get();
            $medidas=DB::table('medidas')->get();
            $unidades=DB::table('unidades_medida')->get();
            $cuentasInventario = $inventario->cuentas();
            return view('inventario.edit')->with(compact('categorias', 'inventario', 'medidas', 'unidades', 'impuestos', 'extras', 'bodegas', 'listas','cuentasInventario','cuentas','autoRetenciones'));
        }
        return redirect('empresa/inventario')->with('success', 'No existe un registro con ese id');
    }
    
    public function update(Request $request, $id){

        $inventario =Inventario::find($id);
        if ($inventario) {
            $request->validate([
                'producto' => 'required',
                'precio' => 'numeric',
                'impuesto' => 'required|numeric',
            ]);
            $errors= (object) array();
            if ($request->ref) {
                $error =Inventario::where('ref', $request->ref)->where('empresa',Auth::user()->empresa)->where('id','<>',$id)->count();
                if ($error>0) {
                    $errors->ref='El código de referencia ya se encuentra registrado para otro producto';
                    return back()->withErrors($errors)->withInput();
                }
            }
            
            //----------------------LIBRERIA INVENTORY IMAGE-------------------------------//
            if ($request->file('imagen')) {
                $request->validate([
                    /*  'imagen'=>'mimes:jpeg,jpg,png| max:1000'*/
                ],['imagen.mimes' => 'La extensión del imagen debe ser jpeg, jpg, png',
                    /*'imagen.max' => 'El peso máximo para el imagen es de 1000KB',*/
                ]);
                $path = public_path() . '/images/Empresas/Empresa'.Auth::user()->empresa;
                if(!File::exists($path)){
                    File::makeDirectory($path);
                }
                if ($inventario->imagen) {
                    $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa.'/inventario/'.$inventario->imagen;
                    if (file_exists($path) ) {
                        unlink($path);  //borra fichero
                    }
                }
                //CLASE DE IMAGE INVENTORY
                if($request->imagen) {
                    $imagen = $request->file('imagen');
                    $nombre_imagen = $inventario->id.'.'.$imagen->getClientOriginalExtension();
                    $imagen = Image::make($imagen);
                    
                    $request->imagen=$nombre_imagen;
                    $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa.'/inventario';
                    
                    if (!File::exists($path)) {
                        File::makeDirectory($path);
                    }
                    
                    if ($imagen->filesize() > 200000) {
                        $request->imagen = $nombre_imagen;
                        $path = public_path() . '/images/Empresas/Empresa' . Auth::user()->empresa . '/inventario';
                        $imagen->resize(1000, 1000, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($path . '/' . $nombre_imagen);
                        $inventario->imagen = $nombre_imagen;
                    } else {
                        $request->imagen = $nombre_imagen;
                        $path = public_path() . '/images/Empresas/Empresa' . Auth::user()->empresa . '/inventario';
                        $imagen->save($path . '/' . $nombre_imagen);
                        $inventario->imagen = $nombre_imagen;
                        $inventario->save();
                    }
                }
            }
            //----------------------/LIBRERIA INVENTORY IMAGE-------------------------------//
            
            if(isset($request->publico)){
                $inventario->publico=$request->publico;
            }
            
            $monto = str_replace('.','',$request->precio);
            $monto = str_replace(',','.',$monto);
            $impuesto = Impuesto::where('id', $request->impuesto)->first();
            $inventario->id_impuesto=$request->impuesto;
            $inventario->impuesto=$impuesto->porcentaje;
            $inventario->producto=$request->producto;
            $inventario->ref=$request->ref;
            $inventario->descripcion=mb_strtolower($request->descripcion);
            $inventario->linea = $request->linea;
            $inventario->precio=$this->precision($monto);
            $inventario->tipo_producto=$request->tipo_producto;
            $inventario->categoria=$request->categoria;
            $inventario->lista = $request->list;
            $inventario->link = $request->link;
            $inventario->type = $request->type;
            $inventario->type_autoretencion = $request->tipo_autoretencion;
            $inventario->save();
            
            if ($request->tipo_producto==1) {
                $request->validate([
                    'unidad' => 'required|exists:unidades_medida,id',
                    'costo_unidad' => 'required|numeric'
                ]);
                $inventario->unidad=$request->unidad;
                $inventario->costo_unidad=$this->precision($request->costo_unidad);
                $inventario->save();
            }
            $inserts=array();
            if ($request->preciolista) {
                foreach ($request->preciolista as $key => $value) {
                    if ($request->preciolistavalor[$key]) {
                        $precio = new ProductosPrecios;
                        $id='idlistaprecio'.$key;
                        if ($request->$id) {
                            $exist=ProductosPrecios::where('empresa', Auth::user()->empresa)->where('producto', $inventario->id)->where('id', $request->$id)->first();
                            if ($exist) { $precio = $exist; }
                        }
                        $precio->empresa=Auth::user()->empresa;;
                        $precio->lista=$value;
                        $precio->producto=$inventario->id;
                        $precio->precio=$this->precision($request->preciolistavalor[$key]);
                        $precio->save();
                        $inserts[]=$precio->id;
                    }
                }
                if (count($inserts)>0) {
                    ProductosPrecios::where('empresa', Auth::user()->empresa)->where('producto', $inventario->id)->whereNotIn('id', $inserts)->delete();
                }
            }else{
                ProductosPrecios::where('empresa', Auth::user()->empresa)->where('producto', $inventario->id)->delete();
            }
            
            $services = array();
            
            if(isset($request->inventario)){
                array_push($services,$request->inventario);
            }

            if(isset($request->costo)){
                array_push($services,$request->costo);
            }

            if(isset($request->venta)){
                array_push($services,$request->venta);
            }
            
            if(isset($request->devolucion)){
                array_push($services,$request->devolucion);
            }

            if(isset($request->autoretencion)){
                array_push($services,$request->autoretencion);
            }

            if($request->cuentacontable){
                $request->cuentacontable = array_merge($request->cuentacontable, $services);
            }else{
                $request->cuentacontable = $services;
            }

            //actualizando cuentas del inventario
            $insertsCuenta=array();
            if ($request->cuentacontable) {
                foreach ($request->cuentacontable as $key) {

                    if(!DB::table('producto_cuentas')->
                    where('cuenta_id',$key)->
                    where('inventario_id',$inventario->id)->first()){
                        
                        $idCuentaPro = DB::table('producto_cuentas')->insertGetId([
                            'cuenta_id' => $key,
                            'inventario_id' => $inventario->id
                        ]);

                    }else{
                        $idCuentaPro = DB::table('producto_cuentas')->
                        where('cuenta_id',$key)->
                        where('inventario_id',$inventario->id)->first()->id;
                    }
                    $insertsCuenta[]=$idCuentaPro;
                }
                if (count($insertsCuenta)>0) {
                    DB::table('producto_cuentas')
                    ->where('inventario_id',$inventario->id)
                    ->whereNotIn('id',$insertsCuenta)->delete();
                }
            }else{
                DB::table('producto_cuentas')
                    ->where('inventario_id',$inventario->id)
                    ->delete();
            }

            //Actualizacion de cuentas contables por tipo
            if(isset($request->inventario)){
                $inven= ProductoCuenta::where('inventario_id',$inventario->id)->where('cuenta_id',$request->inventario)->first();
                if($inven){
                    $inven->tipo = 1;
                    $inven->save();
                }
            }

            if(isset($request->costo)){
                $inven= ProductoCuenta::where('inventario_id',$inventario->id)->where('cuenta_id',$request->costo)->first();
                if($inven){
                    $inven->tipo = 2;
                    $inven->save();
                }
            }

            if(isset($request->venta)){
                $inven= ProductoCuenta::where('inventario_id',$inventario->id)->where('cuenta_id',$request->venta)->first();
                if($inven){
                    $inven->tipo = 3;
                    $inven->save();
                }
            }

            if(isset($request->devolucion)){
                $inven= ProductoCuenta::where('inventario_id',$inventario->id)->where('cuenta_id',$request->devolucion)->first();
                if($inven){
                    $inven->tipo = 4;
                    $inven->save();
                }
            }

            if(isset($request->autoretencion)){
                $inven= ProductoCuenta::where('inventario_id',$inventario->id)->where('cuenta_id',$request->autoretencion)->first();

                if($request->tipo_autoretencion == 1){
                    $inven->delete();
                }else{
                    if($inven){
                        $inven->tipo = 5;
                        $inven->save();
                    }
                }
            }
            
            if ($request->tipo_producto==1) {
                $request->validate([
                    'unidad' => 'required|exists:unidades_medida,id',
                    'costo_unidad' => 'required|numeric'
                ]);
                if ($request->bodega) {
                    foreach ($request->bodega as $key => $value) {
                        if ($request->bodegavalor[$key]) {
                            $bodega = new ProductosBodega;
                            $bodega->empresa=Auth::user()->empresa;
                            $bodega->bodega=$value;
                            $bodega->producto=$inventario->id;
                            $bodega->nro=$request->bodegavalor[$key];
                            $bodega->inicial=$request->bodegavalor[$key];
                            $bodega->save();
                        }
                    }
                }
                $inventario->unidad=$request->unidad;
                $inventario->costo_unidad=$this->precision($request->costo_unidad);
                $inventario->save();
            }
            
            $inserts=array();
            if ($request->tipo_producto==1) {
                $request->validate([
                    'unidad' => 'required|exists:unidades_medida,id',
                    'costo_unidad' => 'required|numeric'
                ]);
                if ($request->bodega) {
                    foreach ($request->bodega as $key => $value) {
                        if ($request->bodegavalor[$key] || $request->bodegavalor[$key] == 0) {
                            $bodega = new ProductosBodega;
                            $id = 'idbodega' . $key;
                            if ($request->$id) {
                                $exist = ProductosBodega::where('empresa', Auth::user()->empresa)->where('producto', $inventario->id)->where('id', $request->$id)->first();
                                if ($exist) {
                                    $bodega = $exist;
                                }
                            }
                            $bodega->empresa = Auth::user()->empresa;;
                            $bodega->bodega = $value;
                            $bodega->producto = $inventario->id;
                            $bodega->nro = $request->bodegavalor[$key];
                            $bodega->inicial = $request->bodegavalor[$key];
                            $bodega->save();
                            $inserts[] = $bodega->id;
                        }
                        if (count($inserts) > 0) {
                            ProductosBodega::where('empresa', Auth::user()->empresa)->where('producto', $inventario->id)->whereNotIn('id', $inserts)->delete();
                        }
                    }
                } else {
                    ProductosBodega::where('empresa', Auth::user()->empresa)->where('producto', $inventario->id)->delete();
                }
            }
            
            $inserts=array();
            $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            foreach ($extras as $campo) {
                $extra='ext_'.$campo->campo;
                $select=DB::table('inventario_meta')->where('empresa', Auth::user()->empresa)->where('id_producto', $inventario->id)->where('meta_key', $campo->campo)->first();
                $insert=array('empresa'=>Auth::user()->empresa, 'id_producto'=>$inventario->id, 'meta_key'=>$campo->campo, 'meta_value'=>$request->$extra);
                if ($select) {
                    DB::table('inventario_meta')->where('empresa', Auth::user()->empresa)->where('id_producto', $inventario->id)->where('meta_key', $campo->campo)->update($insert);
                }else{
                    $inserts[]=$insert;
                }
            }
            
            if (count($inserts)>0) {
                DB::table('inventario_meta')->insert($inserts);
            }
        }
        if($inventario->type == 'TV'){
            return redirect('empresa/inventario/television')->with('success', 'Se ha modificado satisfactoriamente el plan de televisión')->with('producto_id', $inventario->id);
        }
        return redirect('empresa/inventario/')->with('success', 'Se ha modificado satisfactoriamente el producto');
    }
    
    public function json($id=false, Request $request){
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
        if (!$bodega) {
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        }
        $select=array('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'));
        if ($request->precios) {
            $precios = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->precios)->first();
            if ($precios) {
                if ($precios->nro>0) {
                    $select[]=DB::raw('(Select precio from productos_precios where lista='.$precios->id.' and producto=inventario.id) as precio_secun');
                }
            }
        }
        
        if (!$id) {
            $inventario =Inventario::select($select)->where('status',1)->where('empresa',Auth::user()->empresa);
            if ($request->inventariables) {
                $inventario=$inventario->where('inventario.tipo_producto', 1);
            }
            $inventario=$inventario->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->orderBy('id','DESC')->get();
            if ($inventario) {
                foreach ($inventario as $key => $item) {
                    $item->precio=$this->precision($item->precio);
                    $item->costo_unidad=$this->precision($item->costo_unidad);
                }
                return json_encode($inventario);
            }
        }else{
            $inventario =Inventario::select($select)->where('id',$id)->where('empresa',Auth::user()->empresa)->first();
            if ($inventario) {
                $inventario->precio=$this->precision($inventario->precio);
                $inventario->costo_unidad=$this->precision($inventario->costo_unidad);
                //Se obtiene el inventario del producto buscado
                $inventario->inventario = $inventario->inventario();
                $inventario->inventariable= $inventario->esInventariable();
                $inventario->cuentas = $inventario->booleanCuentas();
                //<-->
                return json_encode($inventario);
            }
        }
    }
    
    public function importar(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Importar Inventario desde Excel']);
        return view('inventario.importar');
    }
    
    public function actualizar(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Actualizar Inventario desde Excel']);
        return view('inventario.actualizar');
    }
    
    public function imagenes(Request $request, $id){
        //----------------------LIBRERIA INVENTORY IMAGE-------------------------------//
        $inventario =Inventario::find($id);
        if ($inventario) {
            if ($request->tipo=='add') {
                $imagen = $request->file('file');
                $nombre_imagen = time().random_int(1000, 99999).'.'.$imagen->getClientOriginalExtension();
                $imagen_extra = Image::make($imagen);
                
                $request->imagen=$nombre_imagen;
                $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa.'/inventario/'.$id;
                
                if ($imagen_extra->filesize() > 200000) {
                    $imagen_extra->resize(1000,1000,function($constraint){$constraint->aspectRatio();})->save($path.'/'.$nombre_imagen);
                    $imagen_extra->save($path.'/'.$nombre_imagen);
                }else{ 
                    $imagen->move($path,$nombre_imagen);
                    //$imagen_extra->save($path.'/'.$nombre_imagen);
                }
                
                //$imagen->move($path,$nombre_imagen);
                
                DB::table('imagenesxinventario')->insert(
                    ['producto' => $id, 'imagen' => $nombre_imagen, 'created_at'=>Carbon::now()]);
            }else{
                $imagen=DB::table('imagenesxinventario')->where('producto', $inventario->id)->where('id', $request->img)->first();
                $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa.'/inventario/'.$inventario->id.'/'.$imagen->imagen;
                if (file_exists($path) ) {
                    unlink($path);
                }
                DB::table('imagenesxinventario')->where('producto', $inventario->id)->where('id', $request->img)->delete();
            }
        }
        //----------------------/LIBRERIA INVENTORY IMAGE-------------------------------//
    }
    
    public function ejemplo(){
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Inventario de ".Auth::user()->empresa()->nombre;
        $titulosColumnas = array('Nombre (Requerido)', 'Referencia (Requerido)', 'Categoria (Requerido)', 'Descripcion', 'Precio General (Requerido)', 'Costo unitario (Requerido para inventariables)', 'Cantidad inicial en bodega Principal (Requerido para inventariables)', 'Nombre impuesto', 'Porcentaje impuesto', 'Bodega (Requerido)', 'Lista');
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        foreach($extras as $campo){
            $titulosColumnas[]=$this->normaliza($campo->nombre." ".($campo->descripcion?'('.$campo->descripcion.')':'')).($campo->tipo==1?" (Requerido)":'');
        }
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $totalcolumnas = count($titulosColumnas);
        $totalcolumnas = $letras[$totalcolumnas-1];
        
        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Inventario") // Titulo
        ->setSubject("Reporte Excel Inventario") //Asunto
        ->setDescription("Reporte de Inventario") //Descripci���n
        ->setKeywords("reporte Inventario") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:C2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2','Fecha '.date('d-m-Y')); // Titulo del reporte
        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$totalcolumnas.'3')->applyFromArray($estilo);
        
        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:'.$totalcolumnas.'3')->applyFromArray($estilo);
        
        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }
        
        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:'.$totalcolumnas.$i)->applyFromArray($estilo);
        
        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }
        
        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Inventario');
        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);
        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,5);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Plantilla_Inventario.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    public function cargando(Request $request){
        $request->validate([
            'archivo' => 'required',
        ],[
            'archivo.mimes' => 'El archivo debe ser de extensión xlsx'
        ]);
        
        if(isset($request->publico)){
            $publico=$request->publico;
        }
        $imagen = $request->file('archivo');
        $nombre_imagen = time().'archivo.'.$imagen->getClientOriginalExtension();
        $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa;
        $imagen->move($path,$nombre_imagen);
        Ini_set ('max_execution_time', 3600);
        $fileWithPath=$path."/".$nombre_imagen;
        $inputFileType = PHPExcel_IOFactory::identify($fileWithPath);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($fileWithPath);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $lista = '';
        
        for ($row = 4; $row <= $highestRow; $row++){
            $request= (object) array();
            $error= (object) array();
            $nombre=$sheet->getCell("A".$row)->getValue();
            if (empty($nombre)) {
                break;
            }
            $request->ref=$sheet->getCell("B".$row)->getValue();
            if (!$request->ref) {
                $error->ref="El campo Referencia es obligatorio";
            }else{
                $cant =Inventario::where('ref', $request->ref)->where('empresa',Auth::user()->empresa)->count();
                if ($cant>0) {
                    $error->ref='El código de referencia ya se encuentra registrado para otro producto';
                }
            }
            
            $request->categoria=$sheet->getCell("C".$row)->getValue();
            if (!$request->categoria) {
                $error->categoria="El campo Categoria es obligatorio";
            }else{
                $cant=Categoria::where('empresa',Auth::user()->empresa)->where('nombre', 'like', $request->categoria)->count();
                if ($cant==0) {
                    $error->categoria='La categoria '.$request->categoria.' no esta registrada en sus categorias';
                }
            }
            
            $request->precio=$sheet->getCell("E".$row)->getValue();
            $request->costo_unidad=$sheet->getCell("F".$row)->getValue();
            $request->cant=$sheet->getCell("G".$row)->getValue();
            if (!$request->costo_unidad && $request->cant) {
                $error->costo_unidad="Agregaste Cantidad Inicial pero no Costo Unitario";
            }
            if ($request->costo_unidad && (!$request->cant && $request->cant != 0 )) {
                $error->cant="Agregaste Costo Unitario pero no Cantidad Inicial";
            }
            
            $request->bodega=$sheet->getCell("J".$row)->getValue();
            if ($request->bodega){
                $bodega = Bodega::where('bodega','like', '%' . $request->bodega.'%'  )->where('empresa',Auth::user()->empresa)->where('status', 1)->first();
                if (!$bodega) {
                    $error->bodegas="No hay una bodega registrada para guadar los productos inventariables";
                }
            }
            
            $letra=10;
            foreach ($extras as $campo) {
                $letra=$letra+1;
                $extra=$campo->campo;
                $request->$extra=$sheet->getCell($letras[$letra].$row)->getValue();
                if ($campo->tipo==1 && !$request->$extra) {
                    $error->$campo="El campo ".$campo->nombre." es obligatorio";
                }
            }
            
            if (count((array) $error)>0) {
                $fila["error"]='FILA '.$row;
                $error=(array) $error;
                var_dump($error);
                var_dump($fila);
                array_unshift ( $error ,$fila);
                $result=(object) $error;
                //reenvia los errores
                return back()->withErrors($result)->withInput();
            }
        }
        
        for ($row = 4; $row <= $highestRow; $row++){
            $nombre=$sheet->getCell("A".$row)->getValue();
            if (empty($nombre)) {
                break;
            }
            $impuesto = Impuesto::where('id', 0)->first();
            if ($sheet->getCell("H".$row)->getValue() && $sheet->getCell("I".$row)->getValue()) {
                if ($sheet->getCell("H".$row)->getValue()!='Ninguno' && $sheet->getCell("I".$row)->getValue()>0) {
                    $impuesto = Impuesto::where('empresa', Auth::user()->empresa)->where('nombre', 'like', $sheet->getCell("H".$row)->getValue())->where('porcentaje', $sheet->getCell("I".$row)->getValue())->first();
                }
            }else{
                if ($sheet->getCell("H".$row)->getValue()) {
                    if ($sheet->getCell("H".$row)->getValue()!='Ninguno') {
                        $impuesto = Impuesto::where('empresa', Auth::user()->empresa)->where('nombre', 'like', $sheet->getCell("H".$row)->getValue())->first();
                    }
                }else if ($sheet->getCell("I".$row)->getValue()) {
                    if ($sheet->getCell("I".$row)->getValue()!=0) {
                        $impuesto = Impuesto::where('empresa', Auth::user()->empresa)->where('porcentaje', $sheet->getCell("I".$row)->getValue())->first();
                    }
                }
            }
            
            $nombreLista = mb_strtolower($request->lista=$sheet->getCell("K".$row)->getValue());
            
            if($nombreLista == 'mas vendidos'){$lista = 1;}
            elseif($nombreLista == 'recientes'){$lista = 2;}
            elseif($nombreLista == 'oferta'){$lista = 3;}
            else{$lista = 0;}
            
            if (!$impuesto) {
                $impuesto = Impuesto::where('id', 0)->first();
            }
            
            $request->categoria=$sheet->getCell("C".$row)->getValue();
            $categoria=Categoria::where('empresa',Auth::user()->empresa)->where('nombre', 'like', $request->categoria)->first();
            
            $inventario = new Inventario;
            $inventario->empresa=Auth::user()->empresa;
            $inventario->producto=ucwords(mb_strtolower($nombre));
            $inventario->ref=mb_strtolower($sheet->getCell("B".$row)->getValue());
            $inventario->descripcion=mb_strtolower($sheet->getCell("D".$row)->getValue());
            $inventario->id_impuesto=(!$impuesto) ? 0 : $impuesto->id;
            $inventario->impuesto=(!$impuesto) ? 0 : $impuesto->porcentaje;
            $inventario->tipo_producto=2;
            $inventario->lista=$lista;
            $inventario->precio=$this->precision($sheet->getCell("E".$row)->getValue());
            $inventario->categoria=$categoria->id;
            $costo_unidad=$sheet->getCell("F".$row)->getValue();
            $cant=$sheet->getCell("G".$row)->getValue();
            if(isset($publico)){
                $inventario->publico=$publico;
            }
            $inventario->type='MATERIAL';
            $inventario->save();
            
            if ($costo_unidad && $cant) {
                $registro = new ProductosBodega;
                $registro->empresa=Auth::user()->empresa;
                $registro->bodega=$bodega->id;
                $registro->producto=$inventario->id;
                $registro->nro=$cant;
                $registro->inicial=$cant;
                $registro->save();
                $inventario->costo_unidad=$this->precision($costo_unidad);
                $inventario->unidad=1;
                $inventario->tipo_producto=1;
                $inventario->save();
            }
            
            $letra=10;
            $inserts=array();
            foreach ($extras as $campo) {
                $letra=$letra+1;
                $extra=$campo->campo;
                $request->$extra=$sheet->getCell($letras[$letra].$row)->getValue();
                $insert=array('empresa'=>Auth::user()->empresa, 'id_producto'=>$inventario->id, 'meta_key'=>$campo->campo, 'meta_value'=>$request->$extra);
                $inserts[]=$insert;
            }
            
            if (count($inserts)>0) {
                DB::table('inventario_meta')->insert($inserts);
            }
        }
        return redirect('empresa/inventario/importar')->with('success', 'Se ha cargado satisfactoriamente los productos');
    }
    
    public function exportar(){
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('max_execution_time', -1);
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Inventario de " . Auth::user()->empresa()->nombre;
        $titulosColumnas = array('Codigo', 'Nombre (Requerido)', 'Referencia (Requerido)', 'Categoria (Requerido)', 'Descripcion', 'Precio General (Requerido)', 'Costo unitario (Requerido para inventariables)', 'Cantidad (Requerido para inventariables)', 'Nombre impuesto', 'Porcentaje impuesto');
        $extras = CamposExtra::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

        foreach ($extras as $campo) {
            $titulosColumnas[] = $this->normaliza($campo->nombre . " " . ($campo->descripcion ? '(' . $campo->descripcion . ')' : '')) . ($campo->tipo == 1 ? " (Requerido)" : '');
        }
        $letras = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $totalcolumnas = count($titulosColumnas);
        $totalcolumnas = $letras[$totalcolumnas - 1];
        $objPHPExcel->getProperties()->setCreator("Sistema")// Nombre del autor
        ->setLastModifiedBy("Sistema")//Ultimo usuario que lo modific���
        ->setTitle("Reporte Excel Inventario")// Titulo
        ->setSubject("Reporte Excel Inventario")//Asunto
        ->setDescription("Reporte de Inventario")//Descripci���n
        ->setKeywords("reporte Inventario")//Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:D1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', $tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:C2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'Fecha ' . date('d-m-Y')); // Titulo del reporte

        $estilo = array('font' => array('bold' => true, 'size' => 12, 'name' => 'Times New Roman'), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $totalcolumnas . '3')->applyFromArray($estilo);

        $estilo = array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $totalcolumnas . '3')->applyFromArray($estilo);

        for ($i = 0; $i < count($titulosColumnas); $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i] . '3', utf8_decode($titulosColumnas[$i]));
        }

        $i = 4;
        $letra = 0;

        $idEmpresa = Auth::user()->empresa;
        $consulta = DB::select("
                        SELECT
                            inv.*,
                            meta.meta_key,
                            meta.meta_value,
                            (
                              SELECT 
                                SUM(nro) 
                              FROM 
                                productos_bodegas 
                              where 
                                empresa= inv.empresa AND producto = inv.id
                            ) AS inventario,
                            catg.nombre AS catg,
                            impu.nombre AS impues
                        FROM
                        inventario AS inv
                        INNER JOIN inventario_meta AS meta ON inv.id = meta.id_producto
                        INNER JOIN categorias AS catg ON catg.id = inv.categoria
                        LEFT JOIN impuestos AS impu ON impu.id =  inv.id_impuesto
                        WHERE
                        inv.empresa = {$idEmpresa}
                        AND
                        inv.type <> 'PLAN'
        ");

        $products = [];
        foreach($consulta as $item){
            $products[$item->id]['id'] = $item->id;
            $products[$item->id]['ref'] = $item->ref;
            $products[$item->id]['producto'] = $item->producto;
            $products[$item->id]['precio'] = $item->precio;
            $products[$item->id]['descripcion'] = $item->descripcion;
            $products[$item->id]['tipo_producto'] = $item->tipo_producto;
            $products[$item->id]['costo_unidad'] = $item->costo_unidad;
            $products[$item->id]['impuesto'] = $item->impuesto;
            $products[$item->id]['inventario'] = $item->inventario;
            $products[$item->id]['impues'] = $item->impues;
            if($item->impuesto == 0){
                $impuesto = 'Ninguno';
            }else{
                $impuesto = $item->impuesto.' %';
            }
            $products[$item->id]['impuesto'] = $impuesto;
            $products[$item->id]['catg'] = $item->catg;
            $products[$item->id][$item->meta_key] = $item->meta_value;
        }

        foreach ($products as $product) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0] . $i, $product['id'])
                ->setCellValue($letras[1] . $i, $product['producto'])
                ->setCellValue($letras[2] . $i, $product['ref'])
                ->setCellValue($letras[3] . $i, $product['catg'])
                ->setCellValue($letras[4] . $i, $product['descripcion'])
                ->setCellValue($letras[5] . $i, $this->precision($product['precio']));
            if ($product['tipo_producto'] == 1) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($letras[6] . $i, $this->precision($product['costo_unidad']))
                    ->setCellValue($letras[7] . $i, $product['inventario']);
            }
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[8] . $i, $product['impues']/*  $producto->impuesto(true)->nombre*/)
                ->setCellValue($letras[9] . $i, $product['impuesto']);
            $letra = 9;
            foreach ($extras as $campo) {
                $letra = $letra + 1;
                if(isset($product[$campo->campo])) {
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue($letras[$letra] . $i, $product[$campo->campo]);
                }
            }
            $i++;
        }
        
        $estilo = array('font' => array('size' => 12, 'name' => 'Times New Roman'),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:' . $totalcolumnas . $i)->applyFromArray($estilo);
        
        for ($i = 'A'; $i <= $letras[20]; $i++) {
            if ($i == 'B' || $i == 'E') {
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(80);
            } else {
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(true);
            }
        }
        
        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Reporte de Inventario');
        
        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);
        
        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A4');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0, 5);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Inventario.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    public function actualizando(Request $request){
        $request->validate([
            'archivo' => 'required',
        ],[
            'archivo.mimes' => 'El archivo debe ser de extensión xlsx'
        ]);
        if(isset($request->publico)){
            if ($request->publico==1 || $request->publico==0) {
                $publico=$request->publico;
            }
        }
        
        $imagen = $request->file('archivo');
        $nombre_imagen = 'archivo.'.$imagen->getClientOriginalExtension();
        $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa;
        $imagen->move($path,$nombre_imagen);
        Ini_set ('max_execution_time', 500);
        $fileWithPath=$path."/".$nombre_imagen;
        $inputFileType = PHPExcel_IOFactory::identify($fileWithPath);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($fileWithPath);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $cont = 0;
        
        for ($row = 4; $row <= $highestRow; $row++){
            $cont++;
            $request= (object) array();
            $error= (object) array();
            $codigo=$sheet->getCell("A".$row)->getValue();
            if (empty($codigo)) {
                break;
            }
            $inv =Inventario::where('id', $codigo)->where('empresa',Auth::user()->empresa)->first();
            if (!$inv) {
                $error->codigo='El campo Código no coincide con los registros';
            }
            
            if($cont == 500){
                $erro = "Solo puede actualizar 500 items por archivo";
            }
            
            $request->producto=$sheet->getCell("B".$row)->getValue();
            if (!$request->producto) {
                $error->producto="El campo Nombre es obligatorio";
            }
            
            $request->ref=$sheet->getCell("C".$row)->getValue();
            if (!$request->ref) {
                $error->ref="El campo Referencia es obligatorio";
            }else{
                $cant =Inventario::where('ref', $request->ref)->where('id','!=',$inv->id)->where('empresa',Auth::user()->empresa)->count();
                if ($cant>0) {
                    $error->ref='El código de referencia ya se encuentra registrado para otro producto';
                }
            }
            
            $request->categoria=$sheet->getCell("D".$row)->getValue();
            if (!$request->categoria) {
                $error->categoria="El campo Categoria es obligatorio";
            }else{
                $cant=Categoria::where('empresa',Auth::user()->empresa)->where('nombre', 'like', $request->categoria)->count();
                if ($cant==0) {
                    $error->categoria='La categoria '.$request->categoria.' no esta registrada en sus categorias';
                }
            }
            
            $request->precio=$sheet->getCell("F".$row)->getValue();
            if (!$request->precio) {
                $error->precio="El campo Precio es obligatorio";
            }
            
            $request->costo_unidad=$sheet->getCell("G".$row)->getValue();
            $request->cant=$sheet->getCell("H".$row)->getValue();
            if (!$request->costo_unidad && (!$request->cant && $request->cant != 0 )) {
                $error->costo_unidad="Agregaste Cantidad Inicial pero no Costo Unitario";
            }
            if ($request->costo_unidad && (!$request->cant && $request->cant != 0 )) {
                $error->cant="Agregaste Precio Unitario pero no Cantidad Inicial";
            }
            if ($request->costo_unidad && $request->cant){
                $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->count();
                if ($bodegas==0) {
                    $error->bodegas="No hay una bodega registrada para guadar los productos inventariables";
                }
            }
            
            $letra=9;
            foreach ($extras as $campo) {
                $letra=$letra+1;
                $extra=$campo->campo;
                $request->$extra=$sheet->getCell($letras[$letra].$row)->getValue();
                if ($campo->tipo==1 && !$request->$extra) {
                    $error->$campo="El campo ".$campo->nombre." es obligatorio";
                }
            }
            
            if (count((array) $error)>0) {
                $fila["error"]='FILA '.$row;
                $error=(array) $error;
                var_dump($error);
                var_dump($fila);
                array_unshift ( $error ,$fila);
                $result=(object) $error;
                //reenvia los errores
                return back()->withErrors($result)->withInput();
            }
        }
        
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        
        for ($row = 4; $row <= $highestRow; $row++){
            $codigo=$sheet->getCell("A".$row)->getValue();
            if (empty($codigo)) {
                break;
            }
            $impuesto = Impuesto::where('id', 0)->first();
            if ($sheet->getCell("I".$row)->getValue() && $sheet->getCell("J".$row)->getValue()) {
                if ($sheet->getCell("I".$row)->getValue()!='Ninguno' && $sheet->getCell("J".$row)->getValue()>0) {
                    $impuesto = Impuesto::where('empresa', Auth::user()->empresa)->where('nombre', 'like', $sheet->getCell("I".$row)->getValue())->where('porcentaje', $sheet->getCell("J".$row)->getValue())->first();
                }
            }else{
                if ($sheet->getCell("I".$row)->getValue()) {
                    if ($sheet->getCell("I".$row)->getValue()!='Ninguno') {
                        $impuesto = Impuesto::where('empresa', Auth::user()->empresa)->where('nombre', 'like', $sheet->getCell("I".$row)->getValue())->first();
                    }
                }else if ($sheet->getCell("J".$row)->getValue()) {
                    if ($sheet->getCell("J".$row)->getValue()!=0) {
                        $impuesto = Impuesto::where('empresa', Auth::user()->empresa)->where('porcentaje', $sheet->getCell("J".$row)->getValue())->first();
                    }
                }
            }
            if (!$impuesto) {
                $impuesto = Impuesto::where('id', 0)->first();
            }
            
            $request->categoria=$sheet->getCell("D".$row)->getValue();
            $categoria=Categoria::where('empresa',Auth::user()->empresa)->where('nombre', 'like', $request->categoria)->first();
            $inventario =Inventario::find($codigo);
            $inventario->producto=ucwords(mb_strtolower($sheet->getCell("B".$row)->getValue()));
            $inventario->ref=mb_strtolower($sheet->getCell("C".$row)->getValue());
            $inventario->descripcion=mb_strtolower($sheet->getCell("E".$row)->getValue());
            $inventario->id_impuesto=$impuesto->id;
            $inventario->impuesto=$impuesto->porcentaje;
            $inventario->tipo_producto=2;
            $inventario->precio=$this->precision($sheet->getCell("F".$row)->getValue());
            $inventario->unidad=1;$inventario->nro=0;
            $inventario->categoria=$categoria->id;
            $inventario->type='MATERIAL';
            $cant=$sheet->getCell("H".$row)->getValue();
            $costo_unidad= !$sheet->getCell("G".$row)->getValue() && !$cant ? null : $costo_unidad= $sheet->getCell("G".$row)->getValue();
            if($cant && $costo_unidad == null){
                $costo_unidad = 0.0;
            }
            if(isset($publico)){
                $inventario->publico=$publico;
            }
            $inventario->save();
            if ($costo_unidad || $costo_unidad === 0.0) {
                $registro=ProductosBodega::where('bodega', $bodega->id)->where('producto', $inventario->id)->first();
                if (!$registro) {
                    $registro = new ProductosBodega;
                    $registro->empresa=Auth::user()->empresa;
                    $registro->bodega=$bodega->id;
                    $registro->producto=$inventario->id;
                    $registro->nro=$cant;
                }
                $registro->inicial=$cant;
                $registro->nro=$cant;
                $registro->save();
                
                $inventario->costo_unidad=$this->precision($costo_unidad);
                $inventario->unidad=1;
                $inventario->tipo_producto=1;
                $inventario->save();
            }
            $letra=9;
            $inserts=array();
            foreach ($extras as $campo) {
                $letra=$letra+1;
                $extra=$campo->campo;
                $request->$extra=$sheet->getCell($letras[$letra].$row)->getValue();
                if ($request->$extra) {
                    $select=DB::table('inventario_meta')->where('empresa', Auth::user()->empresa)
                    ->where('id_producto', $inventario->id)
                    ->where('meta_key', $campo->campo)->first();
                    $insert=array('empresa'=>Auth::user()->empresa,
                    'id_producto'=>$inventario->id,
                    'meta_key'=>$campo->campo,
                    'meta_value'=>$request->$extra);
                    if ($select) {
                        DB::table('inventario_meta')->where('empresa', Auth::user()->empresa)->where('id_producto', $inventario->id)->where('meta_key', $campo->campo)->update($insert);
                    }else{
                        $inserts[]=$insert;
                    }
                }
            }
            if (count($inserts)>0) {
                DB::table('inventario_meta')->insert($inserts);
            }
        }
        return redirect('empresa/inventario/actualizar')->with('success', 'Se ha modificado satisfactoriamente los productos');
    }
    
    public function destroy($id, Request $request){
        $inventario =Inventario::find($id);
        if ($inventario) {
            if ($inventario->uso()==0) {
                if ($inventario->imagen) {
                    $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa.'/inventario/'.$inventario->imagen;
                    if (file_exists($path) ) {
                        unlink($path);
                    }
                }
                
                ProductosBodega::where('empresa', Auth::user()->empresa)->where('producto', $inventario->id)->delete();
                $inventario->delete();
            }
        }
        return back()->with('success', 'Se ha eliminado el producto');
    }
    
    public function publicar($id){
        $inventario =Inventario::find($id);
        if ($inventario) {
            if ($inventario->publico==1) {
                $inventario->publico=0;
                $mensaje="Se ha ocultado el item en la web";
            }else{
                $inventario->publico=1;
                $mensaje="Se ha publicado el item en la web";
            }
            $inventario->save();
            return back()->with('success', $mensaje);
        }
        about(404);
    }
    
    public function act_desc($id){
        $inventario =Inventario::where('id',$id)->where('empresa',Auth::user()->empresa)->first();
        if ($inventario) {
            if ($inventario->status==1) {
                $mensaje='Se ha desactivado el producto';
                $inventario->status=0;
                $inventario->save();
            }else{
                $mensaje='Se ha activado el producto';
                $inventario->status=1;
                $inventario->save();
            }
            return back()->with('success', $mensaje);
        }
        return back()->with('success', 'No existe un registro con ese id');
    }

    public function repararLinea(){
        $cont = 0;
        $metas = DB::select("SELECT
            h.id_producto,
            p.producto,
            p.id
            FROM
            inventario_meta as h
            LEFT JOIN inventario p ON
            p.id = h.id_producto
            WHERE
            h.empresa = 1 or p.id IS NULL
            GROUP BY
            h.id_producto");
        
        foreach($metas as $meta){
            $cont++;
            if($meta->id == null){
                $nombre = 'no existe';
                DB::table('inventario_meta')->where('id_producto', $meta->id_producto)->delete();
            }else{
                $nombre = $meta->producto;
            }
            echo $cont.'----'.$meta->id_producto.'---'. $nombre .'</br>';  
        }
    }

    public function getDataTable(){
        $productos = Inventario::select(['id','ref','producto','precio','unidad','publico'])->where('empresa',Auth::user()->empresa)->get();
        return Datatables::of($productos)
        ->addColumn('referencia', function ($producto){
            return '<div class="elipsis-short"><a href="'.route('inventario.show',$producto->id).'">'.$producto->ref.'</a></div>';
        })
        ->addColumn('precio_producto', function ($producto){
            return $producto->precio;
        })
        ->editColumn('unidad', function ($producto) {
            if ($producto->unidad == ''){
                return 'N/A';
            }else{
                return $producto->unidad;
            }
        })
        ->addColumn('web', function ($producto){
            if($producto->publico == 1){
                return '<label class="text-success" ><i class="fas fa-check"></i></label>';
            }else{
                return '<label class="text-danger"><i class="fas fa-ban"></i></label>';
            }
        })
        ->addColumn('acciones',function ($producto) {
            return '<a href="'.route('inventario.show',$producto->id).'" class="btn btn-outline-info btn-icons"><i class="far fa-eye"></i></i></a>
                <a href="'.route('inventario.edit',$producto->id).'" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
                <button type="button" id="eliminar" title="Eliminar Producto" class="btn btn-outline-danger btn-icons"><i class="fas fa-times"></i></button>';
        })
        ->rawColumns(['acciones','referencia','precio_producto', 'web'])->make(true);
    }
}
