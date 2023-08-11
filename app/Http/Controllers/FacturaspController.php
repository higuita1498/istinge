<?php

namespace App\Http\Controllers;
use App\CamposExtra;
use App\Http\Requests\Gastos\FacturaspStoreRequest;
use App\TerminosPago;
use Illuminate\Http\Request;
use App\Empresa; use App\Contacto;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\ProductosBodega;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Gastos\ItemsFacturaProv;
use App\Model\Gastos\FacturaProveedoresRetenciones;
use App\Impuesto;
use App\Categoria;
use App\TipoEmpresa;
use App\TipoIdentificacion;
use App\Vendedor;
use App\Model\Inventario\ListaPrecios;
use App\Numeracion;
use App\Retencion;
use App\Funcion;
use Validator; use Illuminate\Validation\Rule; use Auth;
use Carbon\Carbon; use Mail; use DB;
use Barryvdh\DomPDF\Facade as PDF;
use Session;
use App\FormaPago;
use App\PucMovimiento;
use App\Puc;
use App\Campos;
use App\NumeracionFactura;
use App\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

include_once(app_path() . '/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;

class FacturaspController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        view()->share(['seccion' => 'gastos', 'title' => 'Facturas de Proveedores', 'icon' =>'fas fa-minus', 'subseccion' => 'facturas_proveedores']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $empresaActual = auth()->user()->empresa;

        $proveedores = Contacto::join('factura_proveedores as f', 'contactos.id', '=', 'f.proveedor')->where('contactos.status', 1)->groupBy('f.proveedor')->select('contactos.*')->orderBy('contactos.nombre','asc')->get();

        view()->share(['middel' => true]);
        $tipo = false;
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 6)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();

        return view('facturasp.indexnew', compact('proveedores','tipo','tabla'));
    }

    public function facturasp(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $identificadorEmpresa = auth()->user()->empresa;
        $moneda = auth()->user()->empresaObj->moneda;

        $facturas = FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.*')
            ->where('factura_proveedores.empresa', $identificadorEmpresa)
            ->where('factura_proveedores.tipo', 1)
            ->where('codigo_dian', null)
            ->where(function ($query) {
                return $query->where('factura_proveedores.modo', 1)
                    ->orWhere('factura_proveedores.modo', null);
            })
            ->when($request->nro, function ($query) use ($request) {
                return $query->where('factura_proveedores.nro', 'like', "%{$request->nro}%");
            })
            ->when($request->codigo, function ($query) use ($request) {
                return $query->where(function ($newQuery) use ($request) {
                    $newQuery->where('factura_proveedores.codigo', 'like', "%{$request->codigo}%")
                        ->orWhere('factura_proveedores.codigo_dian', 'like', "%{$request->codigo}%");
                });
            })
            ->when($request->proveedor, function ($query) use ($request) {
                return $query->where('factura_proveedores.proveedor', $request->proveedor);
            })
            ->when($request->comprador, function ($query) use ($request) {
                return $query->where('factura_proveedores.comprador', $request->comprador);
            })
            ->when($request->creacion, function ($query) use ($request) {
                return $query->whereDate('factura_proveedores.fecha_factura', $request->creacion);
            })
            ->when($request->vencimiento, function ($query) use ($request) {
                return $query->whereDate('factura_proveedores.vencimiento_factura', $request->vencimiento);
            })
            ->when($request->estatus, function ($query) use ($request) {
                return $query->where('factura_proveedores.estatus', (int) $request->estatus);
            })
            ->when($request->etiqueta, function ($query) use ($request) {
                return $query->where('factura_proveedores.etiqueta_id', $request->etiqueta);
            })
            ->when($request->created_by, function ($query) use ($request) {
                return $query->where('factura_proveedores.created_by', $request->created_by);
            })
            ->when($request->total && $request->comparador, function ($query) use ($request) {
                return $query->havingRaw('(SUM(
                    (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+
                    (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) )'
                    . $request->comparador . ' ?', [$request->total]);
            })
            ->groupBy('factura_proveedores.id');


        return datatables()->eloquent($facturas)
            ->editColumn('nro', function (FacturaProveedores $factura) {
                return $factura->nro ? "<a href=" . route('facturasp.show', $factura->id) . ">$factura->nro</a>" : "";
            })
            ->editColumn('codigo', function (FacturaProveedores $factura) {
                return ($factura->codigo_dian != null) ? "<a href=" . route('facturasp.show', $factura->id) . ">$factura->codigo_dian</a>" : "<a href=" . route('facturasp.show', $factura->id) . ">$factura->codigo</a>";
            })
            ->editColumn('proveedor', function (FacturaProveedores $factura) {
                return  $factura->proveedor()->nombre ? "<a href=" . route('contactos.show', $factura->proveedor) . ">{$factura->proveedor()->nombre}</a>" : "";
            })
            ->editColumn('creacion', function (FacturaProveedores $factura) {
                return date('d-m-Y', strtotime($factura->fecha_factura));
            })
            ->editColumn('comprador', function (FacturaProveedores $factura) {
                return  $factura->comprador ? "<a href=" . route('contactos.show', $factura->comprador) . ">{$factura->comprador()->nombre}</a>" : "";
            })
            ->editColumn('vencimiento', function (FacturaProveedores $factura) {
                return (date('Y-m-d') > $factura->vencimiento_factura && $factura->estatus == 1) ? '<span class="text-danger">' . date('Y-m-d', strtotime($factura->vencimiento_factura)) . '</span>' : date('Y-m-d', strtotime($factura->vencimiento_factura));
            })
            ->addColumn('total', function (FacturaProveedores $factura) use ($moneda) {
                return "{$moneda} {$factura->parsear($factura->total()->total)}";
            })
            ->addColumn('pagado', function (FacturaProveedores $factura) use ($moneda) {
                return "{$moneda} {$factura->parsear($factura->pagado())}";
            })
            ->addColumn('pagar', function (FacturaProveedores $factura) use ($moneda) {
                return   $factura->porpagar() >= 0 ? "{$moneda} {$factura->parsear($factura->porpagar())}" : "$moneda 0";
            })
            ->addColumn('estado', function (FacturaProveedores $factura) {
                $msj = '';
                return   '<span class="text-' . $factura->estatus(true) . '">' . $factura->estatus() . ' ' . $msj . '</span>';
            })
            ->addColumn('acciones', $modoLectura ?  "" : "facturasp.acciones")
            ->rawColumns(['nro', 'codigo', 'proveedor', 'creacion', 'vencimiento', 'estado', 'acciones'])
            ->toJson();
        }

    public function indexOLD(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $busqueda = false;
        $campos=array('', 'factura_proveedores.nro', 'factura_proveedores.codigo', 'nombrecliente', 'factura_proveedores.fecha_factura','factura_proveedores.vencimiento_factura',  'total',  'porpagar',  'pagado');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $facturas=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
        ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
        ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro',
        DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura',
        'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus',
        DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
        DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar'))->where('factura_proveedores.empresa',Auth::user()->empresa)->where('factura_proveedores.tipo',1);

        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
        if ($request->name_1) {
            $busqueda=true; $appends['name_1']=$request->name_1; $facturas=$facturas->where('factura_proveedores.codigo', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
            $busqueda=true; $appends['name_2']=$request->name_2;
            $contactos = Contacto::select('id')->whereIn('tipo_contacto', [1,2])->where('nombre', 'like', $request->name_2.'%')->get();
            $arrayId = array();
            foreach ($contactos as $contacto){
                $arrayId[] = $contacto->id;
            }
            $facturas=$facturas->whereIn('factura_proveedores.proveedor', $arrayId);
        }
        if ($request->name_3) {
            $busqueda=true; $appends['name_3']=$request->name_3; $facturas=$facturas->where('factura_proveedores.fecha_factura', date('Y-m-d', strtotime($request->name_3)));
        }
        if ($request->name_4) {
            $busqueda=true; $appends['name_4']=$request->name_4; $facturas=$facturas->where('factura_proveedores.vencimiento_factura', date('Y-m-d', strtotime($request->name_4)));
        }
        if($request->name_11){
            $busqueda=true; $appends['name_5']=$request->name_11;
            $facturas=$facturas->where('factura_proveedores.nro', 'like', $request->name_11.'%');
        }
        if ($request->name_8) {
            $busqueda=true; $appends['name_8']=$request->name_8; $facturas=$facturas->whereIn('factura_proveedores.estatus', $request->name_8);
        }
        if ($request->name_7) {
            $busqueda=true; $appends['name_7']=$request->name_7; $appends['name_7_simb']=$request->name_7_simb; $facturas=$facturas->whereRaw(DB::raw('((Select SUM(pago) from gastos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from gastos_retenciones where factura=factura_proveedores.id)) '.$request->name_7_simb.' ?'), [$request->name_7]);
        }
        if ($request->name_6) {
            $busqueda=true;$appends['name_6_simb']=$request->name_6_simb;  $appends['name_6']=$request->name_6; $facturas=$facturas->whereRaw('(Select SUM(pago) from gastos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from gastos_retenciones where factura=factura_proveedores.id) '.$request->name_6_simb.' ? ', [$request->name_6]);
        }

        $facturas = $facturas->groupBy('if.factura');

        if ($request->name_5) {
            $busqueda=true; $appends['name_5']=$request->name_5; $appends['name_5_simb']=$request->name_5_simb; $facturas=$facturas->havingRaw('SUM(
                (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) '.$request->name_5_simb.' ?', [$request->name_5]);
        }
        $facturas=$facturas->OrderBy($orderby, $order)->paginate(10)->appends($appends);
        return view('facturasp.index')->with(compact('facturas', 'request', 'busqueda'));
    }

    public function create($proveedor=false, $producto=false){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['icon' =>'', 'title' => 'Nueva Facturas de Proveedores', 'subseccion' => 'facturas_proveedores']);
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        $inventario =
        Inventario::select('inventario.id','inventario.tipo_producto','inventario.producto','inventario.ref',
            DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
            ->where('empresa',Auth::user()->empresa)
            ->where('status', 1)
            ->where('type', '<>', 'PLAN')
            ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
            ->get();

        //obtiene las formas de pago relacionadas con este modulo (Facturas)
        $relaciones = FormaPago::where('relacion',2)->orWhere('relacion',3)->get();

        $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
        $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
        $clientes = Contacto::select('contactos.id','contactos.nombre','contactos.nit', 'contactos.apellido1', 'contactos.apellido2')->where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $identificaciones=TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        $dataPro = (new InventarioController)->create();
        $categorias = Puc::where('empresa',auth()->user()->empresa)
         ->whereRaw('length(codigo) > 4')
         ->get();
        //Se crea una instancia de facturas_proveedores y se le summa 1 al codigo
        $facturaP = FacturaProveedores::where('empresa', Auth::user()->empresa)->get()->last();
        if (!$facturaP) {
            $codigoFactura = 0;
        } else{
            $codigoFactura = $facturaP->codigo;
        }
        //dd($codigoFactura++);
        $codigoFactura++;
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

        view()->share(['icon' =>'', 'title' => 'Nueva Factura de Compra', 'subseccion' => 'facturas_proveedores', 'seccion' => 'gastos']);
        return view('facturasp.create')
        ->with(compact('relaciones','inventario', 'bodegas', 'clientes', 'impuestos', 'categorias', 'retenciones',
            'proveedor', 'producto','identificaciones', 'tipos_empresa', 'prefijos', 'vendedores', 'listas',
            'categorias2', 'unidades2','medidas2', 'impuestos2', 'extras2', 'listas2', 'bodegas2', 'identificaciones2',
            'tipos_empresa2', 'prefijos2', 'vendedores2', 'codigoFactura','terminos', 'extras'));
    }

    public function create_item($item){
        $inventario =Inventario::where('id',$item)->where('empresa',Auth::user()->empresa)->first();
        if ($inventario) {
            return $this->create(false, $inventario);
        }
        abort(404);
    }

    public function store(Request $request){

        $empresa =  auth()->user()->empresa;
        if( FacturaProveedores::where('empresa',$empresa)->count() > 0){
            //Tomamos el tiempo en el que se crea el registro
            Session::put('posttimer', FacturaProveedores::where('empresa',$empresa)->get()->last()->created_at);
            $sw = 1;
            //Recorremos la sesion para obtener la fecha
            if(isset($ultimoingreso)){
                foreach (Session::get('posttimer') as $key) {
                    if ($sw == 1) {
                        $ultimoingreso = $key;
                        $sw=0;
                    }
                }
                //Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creaci���n
                $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);
                //Si el tiempo es de menos de 30 segundos mandamos al listado general
                if ($diasDiferencia <= 10) {
                    $mensaje = "El formulario ya ha sido enviado.";
                    return redirect('empresa/facturasp')->with('success', $mensaje);
                }
            }
        }

        $codigoRepetido  = FacturaProveedores::select('id', 'empresa', 'proveedor', 'codigo', 'codigo_dian')
            ->where('empresa', $empresa)
            ->where('codigo', $request->codigo)
            ->where('proveedor', $request->proveedor)
            ->first();

        if ($codigoRepetido) {
            return back()
                ->with('error', "Ya existe una factura con el número {$request->codigo} para el mismo cliente.");
        }

        $last = FacturaProveedores::where('empresa', Auth::user()->empresa)->select('nro')->where('tipo', 1)->get()->last();
        if(!$last){
            $nro = 1;
        }else{
            $nro = $last->nro+1;
        }

        $factura = new FacturaProveedores;
        $factura->proveedor =$request->proveedor;
        $factura->tipo =1;
        $factura->nro = $nro;
        $factura->comprador = $request->comprador;
        $factura->plazo = $request->plazo;
        $factura->empresa=Auth::user()->empresa;
        $factura->fecha_factura=Carbon::parse($request->fecha)->format('Y-m-d');
        $factura->vencimiento_factura=Carbon::parse($request->vencimiento)->format('Y-m-d');
        $factura->observaciones_factura=mb_strtolower($request->observaciones_factura);
        $factura->notas=mb_strtolower($request->notas);
        $factura->bodega=$request->bodega;
        $factura->codigo=$request->codigo;
        $factura->cuenta_id    = $request->relacion;

        /* Si le estamos comprando a una persona no obligada a factruar */
        if ($request->equivalente) {

            /* Validamos que el código dian de la factura o haya sido asignado aún */
            if (FacturaProveedores::where('empresa', $empresa)->where('codigo_dian', $request->codigo_dian)->first()) {
                $mensaje = "El número dian {$request->codigo_dian} ya ha sido usado en otra factura de proveedor. Intente nuevamente";
                return redirect()->back()->with('error', $mensaje);
            }
            $factura->codigo_dian = $request->codigo_dian;

            $nro = NumeracionFactura::where('empresa', $empresa)->where('preferida', 1)->where('estado', 1)->where('num_equivalente', 1)->first();

            if (!$nro) {
                $mensaje = 'Debes crear o activar una numeración para facturas de proveedores y elegirla como preferida';
                return redirect()->back()->with('error', $mensaje);
            }

            //Actualiza el nro de inicio para la numeracion seleccionada
            $inicio = $nro->inicio;
            $nro->inicio += 1;
            $nro->save();
            $factura->numeracion = $nro->id;
        }

        $factura->save();
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
        if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        }
        //Ciclo para registrar los itemas de la factura
        for ($i=0; $i < count($request->item) ; $i++) {
            $items = new ItemsFacturaProv;
            $items->factura=$factura->id;
            if (is_numeric($request->item[$i])) {
                $producto = Inventario::where('id', $request->item[$i])->first();
                if($producto->tipo_producto == 2){
                    DB::table('inventario')->where('id', $producto->id)->update(['tipo_producto' => 1]);
                    $productoBodega = new ProductosBodega();
                    $productoBodega->empresa=Auth::user()->empresa;;
                    $productoBodega->bodega=$bodega->id;
                    $productoBodega->producto=$producto->id;
                    $productoBodega->inicial=$request->cant[$i];
                    $productoBodega->save();
                }

                $items->producto=$producto->id;
                $items->tipo_item=1;
                //Si el producto es inventariable y existe esa bodega, agregara el valor registrado
                $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
                if ($ajuste) {
                    $ajuste->nro+=$request->cant[$i];
                    $ajuste->save();
                }
            }else{
                $item=explode('_', $request->item[$i])[1];
                $categorias=Puc::where('empresa',Auth::user()->empresa)->where('id',  $item)->first();
                $items->producto=$categorias->id;
                $items->tipo_item=2;
            }
            $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
            if ($impuesto) {
                $items->id_impuesto=$request->impuesto[$i];
                $items->impuesto=$impuesto->porcentaje;
            }
            $items->ref=$request->item[$i];
            $items->precio=$this->precision($request->precio[$i]);
            $items->descripcion=$request->descripcion[$i];
            $items->cant=$request->cant[$i];
            $items->desc=$request->desc[$i];
            $items->save();
        }

        if ($request->retencion) {
            foreach ($request->retencion as $key => $value) {
                if ($request->precio_reten[$key]) {
                    $retencion = Retencion::where('id', $request->retencion[$key])->first();
                    $items = new FacturaProveedoresRetenciones;

                    if($retencion->tipo == 6){
                        $this->precision($request->precio_reten[$key]);
                    }
                    $items->factura=$factura->id;
                    $items->valor=$this->precision($request->precio_reten[$key]);
                    $items->retencion=$retencion->porcentaje;
                    $items->id_retencion=$retencion->id;
                    $items->save();
                }
            }
        }

        PucMovimiento::facturaCompra($factura,1,$request);

        //Creo la variable para el mensaje final, y la variable print (imprimir)
        $mensaje='Se ha creado satisfactoriamente la factura';
        $print=false;
        //Si se selecciono imprimir, para enviarla y que se abra la ventana emergente con el pdf
        if ($request->print) {
            $print=$factura->nro;
        }
        //Llamada a la funcion enviar en caso de que se haya seleccionado la opcion "Enviar por correo"
        if ($request->send) {
            //$this->enviar($factura->nro, null, false);
        }
        //Se redirecciona a la vista Nuevo ingreso, si se selecciono la opcion "Agregar Pago"
        if ($request->pago) {
            return redirect('empresa/pagos/create/'.$request->proveedor.'/'.$factura->id)->with('print', $print)->with('success', $mensaje);
        }
        //Se redirecciona a la vista Nuevo Factura, si se selecciono la opcion "Crear una nueva"
        else if ($request->new) {
            return redirect('empresa/facturasp/create')->with('success', $mensaje)->with('print', $print);
        }

        if ($request->equivalente) {
            return redirect('empresa/facturasp/listadocumentossoporte')->with('success', $mensaje)->with('codigo', $factura->id);
        }

        return redirect('empresa/facturasp')->with('success', $mensaje)->with('codigo', $factura->id);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $factura = FacturaProveedores::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $retenciones = FacturaProveedoresRetenciones::where('factura', $factura->id)->get();
        if ($factura) {
            view()->share(['title' => 'Factura de Proveedor: '.$factura->nro, 'icon' =>'']);
            $items = ItemsFacturaProv::where('factura',$factura->id)->get();
            return view('facturasp.show')->with(compact('factura', 'items','retenciones'));
        }
        return redirect('empresa/facturasp')->with('success', 'No existe un registro con ese id');
    }

    public function showId($id){
        $this->getAllPermissions(Auth::user()->id);
        return $this->show($id);
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $factura = FacturaProveedores::where('empresa',Auth::user()->empresa)->where('id', $id)->where('tipo',1)->first();
        if ($factura) {
            view()->share(['title' => 'Modificar Factura de Proveedor: '.$factura->codigo, 'icon' =>'']);
            $items = ItemsFacturaProv::where('factura',$factura->id)->get();
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $factura->bodega)->first();
            if (!$bodega) {
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
            }

            // return $factura->gastosAnticipo(1);

            //obtiene las formas de pago relacionadas con este modulo (Facturas)
            $relaciones = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();

            $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
                ->where('empresa',Auth::user()->empresa)
                ->where('status', 1)
                ->where('type', '<>', 'PLAN')
                ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
                ->get();
            $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            $retencionesFacturas=FacturaProveedoresRetenciones::where('factura', $factura->id)->get();
            $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
            $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
            $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
            $categorias=Puc::where('empresa',auth()->user()->empresa)
            ->whereRaw('length(codigo) > 4')
            ->get();
            $identificaciones=TipoIdentificacion::all();
            $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            $prefijos=DB::table('prefijos_telefonicos')->get();
            $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
            $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
            $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();

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
            $formasPago = PucMovimiento::where('documento_id',$factura->id)->where('tipo_comprobante',4)->where('enlace_a',4)->get();

            view()->share(['title' => 'Modificar Factura de Proveedor: '.$factura->codigo, 'icon' =>'','subseccion' => 'facturas_proveedores', 'seccion' => 'gastos']);
            return view('facturasp.edit')->with(compact('relaciones','factura', 'items', 'inventario', 'bodegas',
             'clientes', 'impuestos', 'categorias', 'retencionesFacturas', 'retenciones','listas','categorias2',
             'unidades2','medidas2', 'impuestos2', 'extras2', 'listas2', 'bodegas2', 'identificaciones2', 'tipos_empresa2',
             'prefijos2', 'vendedores2','identificaciones', 'prefijos','tipos_empresa','terminos','vendedores', 'extras', 'formasPago'));
        }
        return redirect('empresa/facturasp')->with('success', 'No existe un registro con ese id');
    }

    public function update(Request $request, $id){
        $factura =FacturaProveedores::find($id);
        if ($factura) {
            if ($factura->estatus==1 && $factura->tipo==1) {
                $factura->proveedor =$request->proveedor;
                $factura->comprador =$request->comprador;
                $factura->plazo =$request->plazo;
                $factura->fecha_factura=Carbon::parse($request->fecha)->format('Y-m-d');
                $factura->vencimiento_factura=Carbon::parse($request->vencimiento)->format('Y-m-d');
                $factura->observaciones_factura=mb_strtolower($request->observaciones_factura);
                $factura->notas=mb_strtolower($request->notas);
                $factura->term_cond=mb_strtolower($request->term_cond);
                $factura->bodega=$request->bodega;
                $factura->codigo=$request->codigo;
                $factura->cuenta_id    = $request->relacion;
                $factura->save();

                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $factura->bodega)->first();
                $items = ItemsFacturaProv::join('inventario as inv', 'inv.id', '=', 'items_factura_proveedor.producto')->select('items_factura_proveedor.*')->where('items_factura_proveedor.factura',$factura->id)->where('items_factura_proveedor.tipo_item', 1)->where('inv.tipo_producto', 1)->get();
                foreach ($items as $item) {
                    $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $item->producto)->first();
                    if ($ajuste) {
                        $ajuste->nro-=$item->cant;
                        $ajuste->save();
                    }
                }
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
                if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
                    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
                }
                $inner=array();
                //Ciclo para registrar y/o modificar los itemas de la factura
                for ($i=0; $i < count($request->item) ; $i++) {
                    $cat='id_item'.($i+1);
                    if($request->$cat){
                        $items = ItemsFacturaProv::where('id', $request->$cat)->first();
                    }else{
                        $items = new ItemsFacturaProv;
                    }
                    $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();

                    if (is_numeric($request->item[$i])) {
                        $producto = Inventario::where('empresa',Auth::user()->empresa)->where('id', $request->item[$i])->first();
                        if($producto->tipo_producto == 2){
                            DB::table('inventario')->where('id', $producto->id)->update(['tipo_producto' => 1]);
                            $productoBodega = new ProductosBodega();
                            $productoBodega->empresa=Auth::user()->empresa;;
                            $productoBodega->bodega=$bodega->id;
                            $productoBodega->producto=$producto->id;
                            $productoBodega->inicial=$request->cant[$i];
                            $productoBodega->save();
                        }
                        $items->producto=$producto->id;
                        $items->tipo_item=1;
                        //Si el producto es inventariable y existe esa bodega, agregara el valor registrado
                        $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
                        if ($ajuste) {
                            $ajuste->nro+=$request->cant[$i];
                            $ajuste->save();
                        }
                    }else{
                        $item=explode('_', $request->item[$i])[1];
                        $categorias=Puc::where('empresa',Auth::user()->empresa)->where('id',  $item)->first();
                        $items->producto=$categorias->id;
                        $items->tipo_item=2;
                    }

                    $items->factura=$factura->id;
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
                    DB::table('items_factura_proveedor')->where('factura', $factura->id)->whereNotIn('id', $inner)->delete();
                }

                //Agregar las retenciones
                if ($request->retencion) {
                    foreach ($request->retencion as $key => $value) {
                        if ($request->precio_reten[$key]) {
                            $cat='reten'.($key+1);
                            if($request->$cat){
                                $items = FacturaProveedoresRetenciones::where('id', $request->$cat)->first();
                            }else{
                                $items = new FacturaProveedoresRetenciones;
                            }
                            $retencion = Retencion::where('id', $request->retencion[$key])->first();
                            $items->factura=$factura->id;
                            $items->valor=$request->precio_reten[$key];
                            $items->retencion=$retencion->porcentaje;
                            $items->id_retencion=$retencion->id;
                            $items->save();
                            $inner[]=$items->id;
                        }
                    }
                    if (count($inner)>0) {
                        DB::table('factura_proveedores_retenciones')->where('factura', $factura->id)->whereNotIn('id', $inner)->delete();
                    }
                }else{
                    DB::table('factura_proveedores_retenciones')->where('factura', $factura->id)->delete();
                }

                PucMovimiento::facturaCompra($factura,2, $request);

                $mensaje='Se ha modificado satisfactoriamente la factura de proveedor';
                return redirect('empresa/facturasp')->with('success', $mensaje)->with('codigo', $factura->id);
            }
            return redirect('empresa/facturasp')->with('success', 'La factura de proveedor '.$orden->orden_nro.' ya esta cerrada');
        }
        return redirect('empresa/facturasp')->with('success', 'No existe un registro con ese id');
    }

    public function destroy($id, Request $request){
        $factura =FacturaProveedores::find($id);
        if ($factura) {
            if ($factura->estatus==1 && $factura->tipo==1) {
                //se restaran todos los items al inventario
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $factura->bodega)->first();
                $items = ItemsFacturaProv::join('inventario as inv', 'inv.id', '=', 'items_factura_proveedor.producto')->select('items_factura_proveedor.*')->where('items_factura_proveedor.factura',$factura->id)->where('items_factura_proveedor.tipo_item', 1)->where('inv.tipo_producto', 1)->get();
                foreach ($items as $item) {
                    $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $item->producto)->first();
                    if ($ajuste) {
                        $ajuste->nro-=$item->cant;
                        $ajuste->save();
                    }
                }
                FacturaProveedoresRetenciones::where('factura', $factura->id)->delete();
                ItemsFacturaProv::where('factura', $factura->id)->delete();
                $factura->delete();
            }
        }
        return redirect('empresa/facturasp')->with('success', 'Se ha eliminado la factura de proveedor exitosamente');
    }

    public function datatable_producto(Request $request, $producto){
        // storing  request (ie, get/post) global array to a variable
        $requestData =  $request;
        $columns = array(
            // datatable column index  => database column name
            0 => 'factura_proveedores.codigo',
            1 => 'nombrecliente',
            2 => 'factura_proveedores.fecha_factura',
            3 => 'factura_proveedores.vencimiento_factura',
            4 => 'total',
            5 => 'pagado',
            6 => 'porpagar',
            7=>'acciones'
        );
        $facturas=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.*', DB::raw('c.nombre as nombrecliente'), DB::raw('c.apellido1 as ape1cliente'), DB::raw('c.apellido2 as ape2cliente'),
            DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
            DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
            DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar'))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)
            ->where('factura_proveedores.tipo',1)
            ->groupBy('if.factura');

        if (isset($requestData->search['value'])) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $facturas=$facturas->where(function ($query) use ($requestData) {
                $query->where('factura_proveedores.codigo', 'like', '%'.$requestData->search['value'].'%')->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
            });
        }

        $totalFiltered=$totalData=$facturas->count();

        $facturas=$facturas->get();
        foreach ($facturas as $factura) {
            $factura->total = $factura->total()->total;
            $factura->pagado = $factura->pagado();
            $factura->porpagar = $factura->porpagar();
        }

        if($requestData['order'][0]['column'] == 4 || $requestData['order'][0]['column'] == 5 || $requestData['order'][0]['column'] == 6 ){
            switch ($requestData['order'][0]['column']){
                case 4:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('total') : $facturas = $facturas->sortByDesc('total');
                break;
                case 5:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('pagado') : $facturas = $facturas->sortByDesc('pagado');
                break;
                case 6:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('porpagar') : $facturas = $facturas->sortByDesc('porpagar');
                break;
            }
        }else{
            switch ($requestData['order'][0]['column']){
                case 3:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('fecha_factura') : $facturas = $facturas->sortByDesc('fecha_factura');
                break;
                case 2:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('vencimiento_factura') : $facturas = $facturas->sortByDesc('vencimiento_factura');
                break;
            }
        }

        $data = array();
        foreach ($facturas as $factura) {
            $nestedData = array();
            $nestedData[] = '<a href="'.route('facturasp.showid',$factura->id).'">'.($factura->codigo?$factura->codigo:$factura->nro).'</a>';
            $nestedData[] = '<a href="'.route('contactos.show',$factura->proveedor).'" target="_blank">'.$factura->nombrecliente.' '.$factura->ape1cliente.' '.$factura->ape2cliente.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($factura->fecha_factura));
            $nestedData[] = date('d-m-Y', strtotime($factura->vencimiento_factura));
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->pagado());
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->porpagar());
            $boton = '<a   href="'.route('facturasp.showid',$factura->id).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>';
            if($factura->tipo ==1 && $factura->estatus==1){
                $boton.='<a  href="'.route('pagos.create_id', ['cliente'=>$factura->proveedor, 'factura'=>$factura->nro]).'" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
                <a href="'.route('facturasp.edit', $factura->id).'"  class="btn btn-outline-primary btn-icons" title="Edidtar"><i class="fas fa-edit"></i></a>
                <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="'."confirmar('eliminar-factura".$factura->id."', '¿Estas seguro que deseas eliminar la factura de compra?', 'Se borrara de forma permanente');".'"><i class="fas fa-times"></i></button><form action="'. route('facturasp.destroy',$factura->id) .'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-factura'.$factura->id.'">'. csrf_field() .'<input name="_method" type="hidden" value="DELETE"></form>';
            }
            $nestedData[]=$boton;
            $data[] = $nestedData;
        }
        $json_data = array(
            "draw" => intval($requestData->draw),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );
        return json_encode($json_data);
    }

    public function datatable_cliente(Request $request, $contacto){
        dd("ingreso a este metodo");
        // storing  request (ie, get/post) global array to a variable
        $requestData =  $request;
        $columns = array(
            // datatable column index  => database column name
            0 => 'factura_proveedores.codigo',
            1 => 'nombrecliente',
            2 => 'factura_proveedores.fecha_factura',
            3 => 'factura_proveedores.vencimiento_factura',
            4 => 'total',
            5 => 'pagado',
            6 => 'porpagar',
            7=>'acciones'
        );
        $facturas=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.*', DB::raw('c.nombre as nombrecliente'), DB::raw('c.apellido1 as ape1cliente'), DB::raw('c.apellido2 as ape2cliente'),
                DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
                DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar'))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)
            ->where('factura_proveedores.tipo',1)
            ->groupBy('if.factura')
            ->where('factura_proveedores.proveedor', $contacto);

        if (isset($requestData->search['value'])) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $facturas=$facturas->where(function ($query) use ($requestData) {
                $query->where('factura_proveedores.codigo', 'like', '%'.$requestData->search['value'].'%')->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
            });
        }

        $totalFiltered=$totalData=$facturas->count();
        $facturas=$facturas->get();

        foreach ($facturas as $factura) {
            $factura->total = $factura->total()->total;
            $factura->pagado = $factura->pagado();
            $factura->porpagar = $factura->porpagar();
        }

        if($requestData['order'][0]['column'] == 4 || $requestData['order'][0]['column'] == 5 || $requestData['order'][0]['column'] == 6 ){
            switch ($requestData['order'][0]['column']){
                case 4:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('total') : $facturas = $facturas->sortByDesc('total');
                break;
                case 5:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('pagado') : $facturas = $facturas->sortByDesc('pagado');
                break;
                case 6:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('porpagar') : $facturas = $facturas->sortByDesc('porpagar');
                break;
            }
        }else{
            switch ($requestData['order'][0]['column']){
                case 3:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('fecha_factura') : $facturas = $facturas->sortByDesc('fecha_factura');
                break;
                case 2:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('vencimiento_factura') : $facturas = $facturas->sortByDesc('vencimiento_factura');
                break;
                case 0:
                    $facturas = $requestData['order'][0]['dir'] == 'asc' ? $facturas->sortBy('codigo') : $facturas = $facturas->sortByDesc('codigo');
                break;
            }
        }

        $data = array();
        foreach ($facturas as $factura) {
            $nestedData = array();
            $nestedData[] = '<a href="'.route('facturasp.showid',$factura->id).'">'.($factura->codigo?$factura->codigo:$factura->nro).'</a>';
            $nestedData[] = '<a href="'.route('contactos.show',$factura->proveedor).'" target="_blank">'.$factura->nombrecliente.' '.$factura->ape1cliente.' '.$factura->ape2cliente.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($factura->fecha_factura));
            $nestedData[] = date('d-m-Y', strtotime($factura->vencimiento_factura));
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total);
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->pagado);
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->porpagar);
            $boton = '<a   href="'.route('facturasp.showid',$factura->id).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>';
            if($factura->tipo ==1 && $factura->estatus==1){
                $boton.='<a  href="'.route('pagos.create_id', ['cliente'=>$factura->proveedor, 'factura'=>$factura->nro]).'" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
                <a href="'.route('facturasp.edit', $factura->id).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
                <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="'."confirmar('eliminar-factura".$factura->id."', '¿Estas seguro que deseas eliminar la factura de compra?', 'Se borrara de forma permanente');".'"><i class="fas fa-times"></i></button><form action="'. route('facturasp.destroy',$factura->id) .'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-factura'.$factura->id.'">'. csrf_field() .'<input name="_method" type="hidden" value="DELETE"></form>';
            }
            $nestedData[]=$boton;
            $data[] = $nestedData;
        }
        $json_data = array(
            "draw" => intval($requestData->draw),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );
        return json_encode($json_data);
    }

    public function proveedor_factura_json($proveedor, $cerradas=false){
        $facturas=FacturaProveedores::where('empresa',Auth::user()->empresa)->where('tipo',1);
        $facturas = $facturas->where('proveedor', $proveedor)->OrderBy('id', 'desc')->select('codigo', 'id')->get();
        return json_encode($facturas);
    }

    public function facturap_json($id){
        $factura = FacturaProveedores::where('empresa',Auth::user()->empresa)->where('tipo',1)->where('id', $id)->first();
        $array=array();
        if ($factura) {
            $array["fecha"]=date('d/m/Y', strtotime($factura->fecha_factura));
            $array["vencimiento"]=date('d/m/Y', strtotime($factura->vencimiento_factura));
            $array["observaciones"]=$factura->observaciones;
            $array["total"]=$factura->total()->total;
            $array["pagado"]=$factura->pagado();
            $array["porpagar"]=$factura->porpagar();
        }
        return json_encode($array);
    }

    public function copia($id){
        return $this->pdf($id, 'copia');
    }

    public function pdf($id, $tipo='original'){
        $tipo1=$tipo;
        if ($tipo<>'original') {
            $tipo='Copia factura de venta';
        }
        else{
            $tipo='Factura de venta original';
        }
        /**
         * toma en cuenta que para ver los mismos
         * datos debemos hacer la misma consulta
         **/
        view()->share(['title' => 'Descargar Factura']);
        $factura = FacturaProveedores::where('empresa',Auth::user()->empresa)->where('tipo',1)->where('id', $id)->first();

        if ($factura) {

            $items = ItemsFacturaProv::where('factura',$factura->id)->get();
            $itemscount=ItemsFacturaProv::where('factura',$factura->id)->count();
            //return view('pdf.factura')->with(compact('items', 'factura', 'itemscount', 'tipo'));

            $pdf = PDF::loadView('pdf.facturap', compact('items', 'factura', 'itemscount', 'tipo'));
            return $pdf->download('factura-'.$factura->codigo.($tipo<>'original'?'-copia':'').'.pdf');

        }

    }

    public function Imprimircopia($id){
        return $this->Imprimir($id, 'copia');
    }

    public function Imprimir($id, $tipo='original'){
        $tipo1=$tipo;
        if ($tipo<>'original') {
            $tipo='Copia de factura de Proveedor';
        }
        else{
            $tipo=' Factura de Proveedor Original';
        }
        /**
         * toma en cuenta que para ver los mismos
         * datos debemos hacer la misma consulta
         **/
        view()->share(['title' => 'Imprimir Factura']);
        $factura = FacturaProveedores::where('empresa',Auth::user()->empresa)->where('tipo',1)->where('id', $id)->first();


        if($factura) {
            $items = ItemsFacturaProv::where('factura',$factura->id)->get();
            $itemscount=ItemsFacturaProv::where('factura',$factura->id)->count();
            $retenciones = FacturaProveedoresRetenciones::where('factura', $factura->id)->get();

            $pdf = PDF::loadView('pdf.facturap', compact('items', 'factura', 'itemscount', 'tipo', 'retenciones'));
            return  response ($pdf->stream())->withHeaders([
                'Content-Type' =>'application/pdf',]);

        }

    }

    public function showMovimiento($id){
        $this->getAllPermissions(Auth::user()->id);
        $factura = FacturaProveedores::find($id);

        /*
            obtenemos los movimiento sque ha tenido este documento
            sabemos que se trata de un tipo de movimiento 03
        */

        $movimientos = PucMovimiento::where('documento_id',$id)->where('tipo_comprobante',4)->get();

        if ($factura) {
            view()->share(['title' => 'Detalle Movimiento ' .$factura->codigo]);
            $items = ItemsFacturaProv::where('factura',$factura->id)->get();
            return view('facturasp.show-movimiento')->with(compact('factura','movimientos'));
        }

        return redirect('empresa/facturas')->with('success', 'No existe un registro con ese id');
      }

    public function exportar(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Facturas de Compras";
        $titulosColumnas = array('Codigo', 'Fecha', 'Cliente', 'Identificacion', 'Subtotal', 'Impuesto', 'Total', 'Abono', 'Saldo', 'Forma de Pago');

        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific�1�7�1�7�1�7
        ->setTitle("Reporte Excel Factura de Compras") // Titulo
        ->setSubject("Reporte Excel Factura de Compras") //Asunto
        ->setDescription("Reporte de Factura de Compras") //Descripci�1�7�1�7�1�7n
        ->setKeywords("reporte Factura de Compras") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah�1�7�1�7�1�7 el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:J1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:J2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2','Fecha '.date('d-m-Y')); // Titulo del reporte

        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:J3')->applyFromArray($estilo);

        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:J3')->applyFromArray($estilo);

        $estilo =array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => substr(Auth::user()->empresa()->color,1))
            ),
            'font'  => array(
                'bold'  => true,
                'size'  => 12,
                'name'  => 'Times New Roman',
                'color' => array(
                    'rgb' => 'FFFFFF'
                ),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A3:J3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i=4;
        $letra=0;

        $empresa = auth()->user()->empresa;
        $moneda = auth()->user()->empresa()->moneda;

        $facturas = FacturaProveedores::query()
            ->leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.nro',
                DB::raw('c.nombre as nombreproveedor'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha_factura',
                'factura_proveedores.vencimiento_factura', 'factura_proveedores.estatus')
            ->where('factura_proveedores.empresa', $empresa)
            ->where('factura_proveedores.tipo',1)
            ->groupBy('factura_proveedores.id');

        if($request->codigo!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura_proveedores.codigo', 'like', "%{$request->codigo}%");
            });
        }
        if($request->nro!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura_proveedores.nro', 'like', "%{$request->nro}%");
            });
        }
        if($request->proveedor!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura_proveedores.proveedor', $request->proveedor);
            });
        }
        if($request->creacion!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura_proveedores.fecha_factura', $request->creacion);
            });
        }
        if($request->vencimiento!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura_proveedores.vencimiento_factura', $request->vencimiento);
            });
        }
        if($request->estado!=null){
            $facturas->where(function ($query) use ($request) {
                $query->orWhere('factura_proveedores.estatus', $request->estado);
            });
        }

        $facturas = $facturas->get();

        foreach ($facturas as $factura) {
            $identificacion = $factura->proveedor()->tip_iden('true').' '.$factura->proveedor()->nit;
            if($factura->proveedor()->tip_iden == 6 && $factura->proveedor()->dv){
                $identificacion .= '-'.$factura->proveedor()->dv;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $factura->codigo)
                ->setCellValue($letras[1].$i, date('d-m-Y', strtotime($factura->fecha_factura)))
                ->setCellValue($letras[2].$i, $factura->nombreproveedor.' '.$factura->proveedor()->apellidos())
                ->setCellValue($letras[3].$i, $identificacion)
                ->setCellValue($letras[4].$i, $moneda.' '.$factura->parsear(($factura->total()->subtotal)))
                ->setCellValue($letras[5].$i, $moneda.' '.$factura->parsear(($factura->total()->ivas)))
                ->setCellValue($letras[6].$i, $moneda.' '.$factura->parsear($factura->total()->total))
                ->setCellValue($letras[7].$i, $moneda.' '.$factura->parsear($factura->pagado()))
                ->setCellValue($letras[8].$i, $moneda.' '.$factura->parsear($factura->porpagar()))
                ->setCellValue($letras[9].$i, ($factura->cuenta_id) ?$factura->formaPago()->nombre:'');
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:J'.$i)->applyFromArray($estilo);

        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Facturas de Compras');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Facturas_Compras.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function NumEquivalenteAjax(Request $request)
    {
        try {
            $numeracion = NumeracionFactura::where('empresa', auth()->user()->empresa)->where('preferida', 1)->where('estado', 1)->where('num_equivalente', 1)->first();

            if ($numeracion) {
                return response()->json($numeracion);
            } else {
                return 0;
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);

            // return back()->with('error', $th->getMessage())->withInput();
        }
    }

    public function lista_documentos_soporte(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['seccion' => 'gastos', 'title' => 'Documentos Soporte', 'icon' => 'fas fa-minus', 'subseccion' => 'facturas_proveedores']);

        $factura = new FacturaProveedores();
        // $estadoModulo = $factura->estadoModulo();

        $empresa = auth()->user()->empresa;
        $proveedores = Contacto::where('empresa',  $empresa)->whereIn('tipo_contacto', [1, 2])->get();
        $compradores = Vendedor::where('empresa', $empresa)->where('estado', 1)->get();
        $usuarios = User::where('empresa', $empresa)->get();

        // $etiquetas = EtiquetaEstado::where('empresa', $empresa)
        //     ->where('estatus', 1)
        //     ->where('tipo', 4)
        //     ->with('color')
        //     ->get();

        $etiquetas = [];


        return view('doc-soporte.index', compact('proveedores', 'compradores', 'usuarios', 'etiquetas'));
    }

    public function documentos_soporte(Request $request)
    {
        $modoLectura = auth()->user()->modo_lectura();
        $identificadorEmpresa = auth()->user()->empresa;
        $moneda = auth()->user()->empresaObj->moneda;


        $facturas = FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select('factura_proveedores.*')
            ->where('factura_proveedores.empresa', $identificadorEmpresa)
            ->where('factura_proveedores.tipo', 1)
            ->where('codigo_dian', '!=', null)
            ->where(function ($query) {
                return $query->where('factura_proveedores.modo', 1)
                    ->orWhere('factura_proveedores.modo', null);
            })
            ->when($request->nro, function ($query) use ($request) {
                return $query->where('factura_proveedores.nro', 'like', "%{$request->nro}%");
            })
            ->when($request->codigo, function ($query) use ($request) {
                return $query->where(function ($newQuery) use ($request) {
                    $newQuery->where('factura_proveedores.codigo', 'like', "%{$request->codigo}%")
                        ->orWhere('factura_proveedores.codigo_dian', 'like', "%{$request->codigo}%");
                });
            })
            ->when($request->proveedor, function ($query) use ($request) {
                return $query->where('factura_proveedores.proveedor', $request->proveedor);
            })
            ->when($request->comprador, function ($query) use ($request) {
                return $query->where('factura_proveedores.comprador', $request->comprador);
            })
            ->when($request->creacion, function ($query) use ($request) {
                return $query->whereDate('factura_proveedores.fecha_factura', $request->creacion);
            })
            ->when($request->vencimiento, function ($query) use ($request) {
                return $query->whereDate('factura_proveedores.vencimiento_factura', $request->vencimiento);
            })
            ->when($request->estatus, function ($query) use ($request) {
                return $query->where('factura_proveedores.estatus', (int) $request->estatus);
            })
            ->when($request->etiqueta, function ($query) use ($request) {
                return $query->where('factura_proveedores.etiqueta_id', $request->etiqueta);
            })
            ->when($request->created_by, function ($query) use ($request) {
                return $query->where('factura_proveedores.created_by', $request->created_by);
            })
            ->when($request->total && $request->comparador, function ($query) use ($request) {
                return $query->havingRaw('(SUM(
                    (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+
                    (if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) )'
                    . $request->comparador . ' ?', [$request->total]);
            })
            ->groupBy('factura_proveedores.id');

        // $etiquetas = EtiquetaEstado::where('empresa', $identificadorEmpresa)
        //     ->where('estatus', 1)
        //     ->where('tipo', 4)
        //     ->with('color')
        //     ->get();

        $etiquetas = [];


        return datatables()->eloquent($facturas)
            ->editColumn('nro', function (FacturaProveedores $factura) {
                return $factura->nro ? "<a href=" . route('facturasp.show', $factura->id) . ">$factura->nro</a>" : "";
            })
            ->editColumn('codigo', function (FacturaProveedores $factura) {
                return ($factura->codigo_dian != null) ? "<a href=" . route('facturasp.show', $factura->id) . ">$factura->codigo_dian</a>" : "<a href=" . route('facturasp.show', $factura->id) . ">$factura->codigo</a>";
            })
            ->editColumn('proveedor', function (FacturaProveedores $factura) {
                return  $factura->proveedor()->nombre ? "<a href=" . route('contactos.show', $factura->proveedor) . ">{$factura->proveedor()->nombre}</a>" : "";
            })->editColumn('comprador', function (FacturaProveedores $factura) {
                return  $factura->comprador ? "<a href=" . route('contactos.show', $factura->comprador) . ">{$factura->comprador()->nombre}</a>" : "";
            })
            ->addColumn('fecha', function (FacturaProveedores $factura) {
                return date('Y-m-d', strtotime($factura->fecha_factura));
            })
            ->editColumn('vencimiento', function (FacturaProveedores $factura) {
                return (date('Y-m-d') > $factura->vencimiento_factura && $factura->estatus == 1) ? '<span class="text-danger">' . date('Y-m-d', strtotime($factura->vencimiento_factura)) . '</span>' : date('Y-m-d', strtotime($factura->vencimiento_factura));
            })
            ->addColumn('total', function (FacturaProveedores $factura) use ($moneda) {
                return "{$moneda} {$factura->parsear($factura->total()->total)}";
            })
            ->addColumn('impuesto', function (FacturaProveedores $factura) use ($moneda) {
                return "{$moneda} {$factura->parsear($factura->impuestos_totales())}";
            })
            ->addColumn('pagado', function (FacturaProveedores $factura) use ($moneda) {
                return "{$moneda} {$factura->parsear($factura->pagado())}";
            })
            ->addColumn('pendiente', function (FacturaProveedores $factura) use ($moneda) {
                return   $factura->porpagar() >= 0 ? "{$moneda} {$factura->parsear($factura->porpagar())}" : "$moneda 0";
            })
            ->addColumn('etiqueta', function (FacturaProveedores $factura) use ($etiquetas) {
                $factura->etiqueta;
                if ($factura->estadoModulo()) {
                    return view('facturasp.etiqueta', compact('etiquetas', 'factura'));
                }
                return '';
            })
            ->addColumn('estatus', function (FacturaProveedores $factura) {
                $msj = '';
                if (Auth::user()->empresaObj->estado_dian == 1 && Auth::user()->empresaObj->equivalente == 1) {
                    $msj = $factura->emitida == 1 ? '- Emitida' : '- No Emitida';
                }

                return   '<span class="text-' . $factura->estatus(true) . '">' . $factura->estatus() . ' ' . $msj . '</span>';
            })
            ->addColumn('observaciones', function (FacturaProveedores $factura) {
                if ($factura->observaciones) {
                    return "<a href='javascript:modificarObservaciones(" . $factura->id . ")'><p id='observacion-parrafo-" . $factura->id . "' title='" . $factura->observaciones . "'>" . substr($factura->observaciones, 0, 24) . "</p></a>";
                } else {
                    return "<a class='btn btn-default btn-sm rounded p-0 m-0 float-left' href='javascript:modificarObservaciones(" . $factura->id . ")'><p id='observacion-parrafo-" . $factura->id . "'>agregar<i class='far fa-plus-square icon-md' style='font-size:18px'></i></p></a>";
                }
            })
            ->addColumn('created_by', function (FacturaProveedores $factura) {
                return  $factura->created_by ? $factura->created_by()->nombres : "";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "facturasp.acciones")
            ->rawColumns(['nro', 'codigo', 'proveedor', 'comprador', 'vencimiento', 'estatus', 'observaciones', 'acciones'])
            ->toJson();
    }

    public function anular($id)
    {
        $factura = FacturaProveedores::where('empresa', Auth::user()->empresa)->where('id', $id)->first();
        if ($factura) {
            if ($factura->estatus == 1) {
                $factura->estatus = 7;
                $factura->update();
                return back()->with('success', 'Se ha anulado la factura');
            } elseif ($factura->estatus == 7) {
                $factura->estatus = 1;
                $factura->update();
                return back()->with('success', 'Se cambiado a abierta la factura');
            }

            return redirect('empresa/facturasp')->with('success', 'La factura no esta abierta');
        }

        return redirect('empresa/facturasp')->with('success', 'No existe un registro con ese id');
    }

    public function xmlFacturaProveedor($id, $emails = false)
    {
        $facturaP = FacturaProveedores::find($id);

        if (!$facturaP) {
            return redirect('/empresa/facturas')->with('error', "No se ha encontrado la factura de venta, comuniquese con soporte.");
        }

        $facturaP->emitida = $facturaP->emitida;
        $facturaP->save();

        if (FacturaProveedores::where('empresa', auth()->user()->empresa)->count() > 0) {
            //Tomamos el tiempo en el que se crea el registro
            Session::put('posttimer', FacturaProveedores::where('empresa', auth()->user()->empresa)->orderBy('updated_at', 'desc')->first()->updated_at);
            $sw = 1;
            if (isset($ultimoingreso)) {
                //Recorremos la sesion para obtener la fecha
                foreach (Session::get('posttimer') as $key) {
                    if ($sw == 1) {
                        $ultimoingreso = $key;
                        $sw = 0;
                    }
                }
                //Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creación
                $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);
                //Si el tiempo es de menos de 10 segundos mandamos al listado general
                if ($diasDiferencia <= 10) {
                    $mensaje = "La factura electrónica ya ha sido enviada.";
                    return redirect('empresa/facturas')->with('success', $mensaje);
                }
            }

            if (isset($ultimoingreso)) {
                //Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creación
                $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);

                //Si el tiempo es de menos de 10 segundos mandamos al listado general
                if ($diasDiferencia <= 10) {
                    $mensaje = "La factura electrónica ya ha sido enviada.";
                    return redirect('empresa/facturas')->with('success', $mensaje);
                }
            }
        }

        $ResolucionNumeracion = NumeracionFactura::where('empresa', Auth::user()->empresa)->where('num_equivalente', 1)->where('nomina', 0)->where('preferida', 1)->first();
        if (!$ResolucionNumeracion) {
            return back()->with('message_denied', "No existe la numeración equivalente, revise la resolución y emita nuevamente.");
        }
        $infoEmpresa = Auth::user()->empresaObj;
        $data['Empresa'] = $infoEmpresa->toArray();

        $retenciones = FacturaProveedoresRetenciones::where('factura', $facturaP->id)->get();

        $impTotal = 0;

        foreach ($facturaP->total()->imp as $totalImp) {
            if (isset($totalImp->total)) {
                $impTotal += $totalImp->total;
            }
        }
        $items = ItemsFacturaProv::where('factura', $id)->get();

        $decimal = explode(".", $impTotal);
        if (
            isset($decimal[1]) && $decimal[1] >= 50 || isset($decimal[1]) && $decimal[1] == 5 || isset($decimal[1]) && $decimal[1] == 4
            || isset($decimal[1]) && $decimal[1] == 3 || isset($decimal[1]) && $decimal[1] == 2 || isset($decimal[1]) && $decimal[1] == 1
        ) {
            $impTotal = round($impTotal, 2);
        } else {
            $impTotal = round($impTotal, 2);
        }

        $CUFEvr = $facturaP->info_cufe($facturaP->id, $impTotal);
        $infoCliente = Contacto::find($facturaP->proveedor);
        $data['Cliente'] = $infoCliente->toArray();


        $responsabilidades_empresa = DB::table('empresa_responsabilidad as er')
            ->join('responsabilidades_facturacion as rf', 'rf.id', '=', 'er.id_responsabilidad')
            ->select('rf.*')
            ->where('er.id_empresa', '=', Auth::user()->empresa)->where('er.id_responsabilidad', 5)
            ->orWhere('er.id_responsabilidad', 7)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 12)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 20)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 29)->where('er.id_empresa', '=', Auth::user()->empresa)->get();

        //-- Construccion del pdf a enviar con el código qr + el envío del archivo xml --//
        if ($facturaP) {
            if (empty($emails)) {
                $emails = array();
            } else {
                $emails = explode(",", $emails);
            }
            array_push($emails, $facturaP->cliente()->email);
            $emails = $facturaP->cliente()->email;
            if ($facturaP->cliente()->asociados('number') > 0) {
                $email = $emails;
                $emails = array();
                if ($email) {
                    $emails[] = $email;
                }
                foreach ($facturaP->cliente()->asociados() as $asociado) {
                    if ($asociado->notificacion == 1 && $asociado->email) {
                        $emails[] = $asociado->email;
                    }
                }
            }

            $tituloCorreo =  $data['Empresa']['nit'] . ";" . $data['Empresa']['nombre'] . ";" . $facturaP->codigo_dian . ";05;" . $data['Empresa']['nombre'];
            $isImpuesto = 1;
            $facturaP->vencimiento = $facturaP->vencimiento_factura;

            // if(auth()->user()->empresa == 1)
            // {
            //          return $xml = response()->view('templates.xml.05',compact('CUFEvr','ResolucionNumeracion','facturaP', 'data','items','retenciones','responsabilidades_empresa','emails','impTotal','isImpuesto'))->header('Cache-Control', 'public')
            //             ->header('Content-Description', 'File Transfer')
            //             ->header('Content-Disposition', 'attachment; filename=FC-'.$facturaP->codigo_dian.'.xml')
            //             ->header('Content-Transfer-Encoding', 'binary')
            //             ->header('Content-Type', 'text/xml');
            // }

            //-- Generación del XML a enviar a la DIAN -- //
            $xml = view('templates.xml.05', compact('CUFEvr', 'ResolucionNumeracion', 'facturaP', 'data', 'items', 'retenciones', 'responsabilidades_empresa', 'emails', 'impTotal', 'isImpuesto'));

            //-- Envío de datos a la DIAN --//
            $res = $this->EnviarDatosDian($xml);

            //-- Decodificación de respuesta de la DIAN --//
            $res = json_decode($res, true);

            // dd($res);

            if (isset($res['errorType'])) {
                if ($res['errorType'] == "KeyError") {
                    return back()->with('message_denied', "La dian está presentando problemas para emitir documentos electrónicos, inténtelo más tarde.");
                }
            }

            if (!isset($res['statusCode']) && isset($res['message'])) {
                return redirect('/empresa/facturasp/listadocumentossoporte')->with('message_denied', $res['message']);
            }

            $statusCode = Arr::exists($res, 'statusCode') ? $res['statusCode'] : null; //200

            if (!isset($statusCode)) {
                return back()->with('message_denied', isset($res['message']) ? $res['message'] : 'Error en la emisión del docuemento, intente nuevamente en un momento');
            }

            //-- Guardamos la respuesta de la dian solo cuando son errores--//
            if ($statusCode != 200) {
                // dd($statusCode);
                $statusCode = array('statusCode' => $statusCode);
                $facturaP->dian_response = $statusCode;
                $facturaP->save();
            }

            //-- Validación 1 del status code (Cuando hay un error) --//
            if ($statusCode != 200) {
                $message = $res['errorMessage'];
                $errorReason = $res['errorReason'];

                //Validamos si depronto la factura fue emitida pero no quedamos con ningun registro de ella.
                $statusJson = $this->validateStatusDocumentoSoporte($facturaP->cliente()->nit, $facturaP->codigo_dian, "05", $infoEmpresa->nit);

                $statusJson = json_decode($statusJson, true);

                if ($statusJson["statusCode"] == 200) {

                    //linea comentada por ahorro de espacio en bd, ay que esta información de las facturas procesadas se puede obtener mediante consulta api.
                    // $facturaP->dian_response = $saveNoJson;
                    $message = "Documento soporte emitido correctamente por validación";
                    $facturaP->emitida = 1;
                    $facturaP->fecha_expedicion = Carbon::now();

                    //Llave unica para acceso por correo
                    $key = Hash::make(date("H:i:s"));
                    $toReplace = array('/', '$', '.');
                    $key = str_replace($toReplace, "", $key);
                    $facturaP->nonkey = $key;

                    $facturaP->save();
                    // $this->generateXmlPdfEmail($statusJson['document'], $facturaP, $emails, $data, $CUFEvr, $items, $ResolucionNumeracion, $tituloCorreo);

                    $res = $statusJson;
                } else {
                    return back()->with('message_denied', $message)->with('errorReason', $errorReason);
                }
            }

            $document = $res['document'];

            //-- estátus de que la factura ha sido aprobada --//
            if ($statusCode == 200) {

                //Llave unica para acceso por correo
                $key = Hash::make(date("H:i:s"));
                $toReplace = array('/', '$', '.');
                $key = str_replace($toReplace, "", $key);
                $facturaP->nonkey = $key;
                $facturaP->save();
                //

                $message = "Factura emitida correctamente";
                $facturaP->emitida = 1;
                $facturaP->fecha_expedicion = Carbon::now();
                $facturaP->save();
                // $this->generateXmlPdfEmail($document, $facturaP, $emails, $data, $CUFEvr, $items, $ResolucionNumeracion, $tituloCorreo);
            }
            return back()->with('message_success', $message);
        }
    }

    public function validate_dian(Request $request)
    {
        $factura = FacturaProveedores::find($request->id);

        $factura->load('clienteObj');

        $empresa  = Empresa::select('id', 'fk_idpais', 'fk_iddepartamento', 'fk_idmunicipio', 'dv', 'fk_idpais', 'fk_iddepartamento', 'fk_idmunicipio', 'dv')
            ->with('responsabilidades')
            ->where('id', auth()->user()->empresa)
            ->first();

        $responsabilidades = $empresa->responsabilidades->count();

        $numeracion = NumeracionFactura::where('empresa', $empresa->id)
            ->where('id', $factura->numeracion)
            ->first();

        $cliente  = $factura->clienteObj;

        //Inicializamos la variable para ver si tiene las nuevas responsabilidades que no da la dian 042
        $resp = 0;

        foreach ($empresa->responsabilidades as $responsabilidad) {

            $listaResponsabilidadesDian = [5, 7, 12, 20, 29];

            if (in_array($responsabilidad->pivot->id_responsabilidad, $listaResponsabilidadesDian)) {
                $resp = 1;
            }
        }


        if ($cliente->tip_iden != 6) {
            $cliente->tipo_persona      = 1; //-- Persona Natural
            $cliente->responsableiva    = 2; //-- No responsable de iva
            $cliente->save();
        }


        //-- Validación de si la ultima factura creada fue emitida o si es la primer factura a emitir que la deje --//
        if ($numeracion) {
            if ($numeracion->prefijo != null || $numeracion->prefijo != "") {
                $numero = intval(preg_replace('/[^0-9]+/', '', $factura->codigo), 10);
                //$codigo    = substr($factura->codigo, strlen($numeracion->prefijo), strlen($numero));
                $codigo = $numero;
            } else {
                $codigo = $factura->codigo;
            }
        }
        $emitida = false;
        if ($numeracion) {
            //Si tenemos una pasada factura a la que estamos intentando emitir entra a este if
            if (FacturaProveedores::where('empresa', Auth::user()->empresa)->where('numeracion', $factura->numeracion)->where('codigo', $numeracion->prefijo . ($codigo - 1))->count() > 0) {
                $ultfact = FacturaProveedores::where('empresa', Auth::user()->empresa)->where('numeracion', $factura->numeracion)->where('codigo', $numeracion->prefijo . ($codigo - 1))->first();

                if ($ultfact->emitida == null || $ultfact->emitida == 2 || $ultfact->emitida == 0) { //-- si es null o es 2(no emitida) o 0 no emitida
                    $emitida = false;
                } else {
                    $emitida = true;
                }
            } elseif ($codigo == $numeracion->inicioverdadero) { //-- si no entra es por que hay la posibilidad de que sea la primer factura emitida de esa numeración
                $emitida = true;
            } else { //cambió el prefijo de una numeracion existente ademas hay mas facturas con esa numeración sin emitir
                /*
            Actualizacion: Como no es igual al inicioverdadero es muy probable que no
            se este emitiendo desde el numero de inicio verdadero si no que arranco un poco mas
            adelante.
            */
                $emitida = true;
            }
        }

        return response()->json([
            "numeracion" => $numeracion, "responsabilidades" => $responsabilidades, "empresa" => $empresa,
            "cliente" => $cliente, "total" => $factura->total()->total,
            "emitida" => $emitida, "responsabilidad" => $resp
        ]);
    }

    public function createDocsoporte($proveedor = false, $producto = false)
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['icon' => '', 'title' => 'Nueva Facturas de Proveedores', 'subseccion' => 'facturas_proveedores']);

        $empresaActual = auth()->user()->empresa;
        $terminos = TerminosPago::where('empresa', $empresaActual)->get();

        $modulo = new FacturaProveedores();
        $estadoModulo = $modulo->estadoModulo();

        $bodega = Bodega::where('empresa', $empresaActual)->where('status', 1)->first();
        $inventario =
            Inventario::select(
                'inventario.id',
                'inventario.tipo_producto',
                'inventario.producto',
                'inventario.ref',
                DB::raw('(Select nro from productos_bodegas where bodega=' . $bodega->id . ' and producto=inventario.id LIMIT 1) as nro')
            )
            ->where('empresa', $empresaActual)
            ->where('status', 1)
            ->where('tipo_producto', '<>', 3)
            ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega=' . $bodega->id . '), true)')
            ->get();


        $bodegas = Bodega::where('empresa', $empresaActual)->where('status', 1)->get();

        $retenciones = Retencion::where('empresa', $empresaActual)->get();

        $terminos = TerminosPago::where('empresa', $empresaActual)->get();

        $clientes = Contacto::select('id', 'nombre', 'nit')
            ->where('empresa', $empresaActual)
            ->whereIn('tipo_contacto', [1, 2])
            ->get();

        $impuestos = Impuesto::where('estado', 1)
            ->where(function ($query) use ($empresaActual) {
                $query->where('empresa', $empresaActual)
                    ->orWhereNull('empresa');
            })
            ->get();

        $categorias = Categoria::where('empresa', $empresaActual)->where('estatus', 1)->whereNull('asociado')->get();
        $extras = CamposExtra::where('empresa', $empresaActual)->where('status', 1)->get();
        $identificaciones = TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa', $empresaActual)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa', $empresaActual)->where('status', 1)->get();
        $tipos_empresa = TipoEmpresa::where('empresa', $empresaActual)->get();
        $prefijos = DB::table('prefijos_telefonicos')->get();

        $dataPro = (new InventarioController())->create();

        //Se crea una instancia de facturas_proveedores y se le summa 1 al codigo
        $facturaP = FacturaProveedores::where('empresa', $empresaActual)->get()->last();

        if (!$facturaP) {
            $codigoFactura = 0;
        } else {
            $codigoFactura = $facturaP->codigo;
        }

        //dd($codigoFactura++);
        $codigoFactura++;
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


        $grupos = Inventario::where('tipo_producto', 3)->where('status', 1)->where('empresa', $empresaActual)->with('grupoItems')->get();

        // $etiquetas = EtiquetaEstado::where('empresa', auth()->user()->empresa)
        //     ->where('estatus', 1)
        //     ->where('tipo', 4)
        //     ->with('color')
        //     ->get();

        //obtiene las formas de pago relacionadas con este modulo (Facturas)
        $relaciones = FormaPago::where('relacion',2)->orWhere('relacion',3)->get();


        $etiquetas = [];
        $colores = [];
        // $colores =  CrmColor::all();


        view()->share(['icon' => '', 'title' => 'Nuevo Documento Soporte', 'subseccion' => 'facturas_proveedores']);

        $docsoporte = true;

        return view('facturasp.create', compact(
            'inventario',
            'bodegas',
            'clientes',
            'impuestos',
            'categorias',
            'retenciones',
            'terminos',
            'proveedor',
            'producto',
            'identificaciones',
            'tipos_empresa',
            'prefijos',
            'vendedores',
            'listas',
            'categorias2',
            'unidades2',
            'medidas2',
            'impuestos2',
            'extras2',
            'listas2',
            'bodegas2',
            'identificaciones2',
            'tipos_empresa2',
            'prefijos2',
            'vendedores2',
            'codigoFactura',
            'terminos',
            'extras',
            'grupos',
            'etiquetas',
            'colores',
            'estadoModulo',
            'docsoporte',
            'relaciones'
        ));
    }
}
