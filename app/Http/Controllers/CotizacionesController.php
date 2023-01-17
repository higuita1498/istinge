<?php

namespace App\Http\Controllers;

use App\Categoria;
use App\NumeracionFactura;
use App\Model\Ingresos\Factura;
use App\TipoEmpresa;
use Illuminate\Http\Request;
use App\Empresa; use App\Contacto; use App\TipoIdentificacion;
use App\Cotizacion; use App\Impuesto; use App\Numeracion;
use App\Vendedor; use App\TerminosPago;
use App\CamposExtra;  use App\Funcion;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\ProductosBodega;
use Carbon\Carbon; use Mail; use Validator;
use Illuminate\Validation\Rule; use Auth;
use bcrypt; use DB;
use Barryvdh\DomPDF\Facade as PDF;
use Session;
use Config;
use App\ServidorCorreo;
use App\Campos;

class CotizacionesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'facturas', 'title' => 'Cotizaciones', 'icon' =>'fas fa-plus', 'subseccion' => 'cotizacion']);
    }

    /**
     * Vista Principal de las cotizaciones
     * La consulta es tan grande para hacer funcionar las flechas, ya que hay valores qe no estan en la tabla
     */
    public function indexOLD(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $busqueda=false;
        $campos=array('', 'factura.cot_nro', 'nombrecliente', 'factura.fecha', 'total', 'factura.estatus');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
        $facturas=Cotizacion::leftjoin('contactos as c', 'factura.cliente', '=', 'c.id')
            ->leftjoin('factura_contacto as fc', 'factura.id', '=', 'fc.factura')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->select('factura.id', 'factura.codigo', 'factura.cot_nro', DB::raw('if(factura.cliente,c.nombre,fc.nombre) as nombrecliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus',
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'))
            ->where('factura.empresa',Auth::user()->empresa)->where('factura.tipo', 3);

        if ($request->name_1) {
            $busqueda=true; $appends['name_1']=$request->name_1; $facturas=$facturas->where('factura.cot_nro', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
            $busqueda=true; $appends['name_2']=$request->name_2; $facturas=$facturas->where(function ($query) use ($request){
                $query->where('c.nombre', 'like', '%' .$request->name_2.'%')->orwhere('fc.nombre', 'like', '%' .$request->name_2.'%');
            });
        }
        if ($request->name_3) {
            $busqueda=true; $appends['name_3']=$request->name_3; $facturas=$facturas->where('factura.fecha', date('Y-m-d', strtotime($request->name_3)));
        }
        if ($request->name_5 && $request->name_5 != 't') {
            $busqueda=true; $appends['name_5']=$request->name_5; $facturas=$facturas->where('factura.estatus', $request->name_5);
        }
        $facturas=$facturas->groupBy('if.factura');

        if ($request->name_4) {
            $busqueda=true; $appends['name_4']=$request->name_4; $appends['name_4_simb']=$request->name_4_simb; $facturas=$facturas->havingRaw(DB::raw('(SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)) '.$request->name_4_simb.' ?'), [$request->name_4]);
        }


        $facturas=$facturas->OrderBy($orderby, $order)->paginate(25)->appends($appends);
        return view('cotizaciones.index')->with(compact('facturas', 'request', 'busqueda'));
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 19)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();

        return view('cotizaciones.index', compact('tabla'));
    }

    public function cotizaciones(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $moneda = auth()->user()->empresa()->moneda;
        $cotizaciones = Cotizacion::query()->
            leftjoin('contactos as c', 'factura.cliente', '=', 'c.id')->
            leftjoin('factura_contacto as fc', 'factura.id', '=', 'fc.factura')->
            join('items_factura as if', 'factura.id', '=', 'if.factura')->
            select('factura.id', 'factura.codigo', 'factura.cot_nro', DB::raw('if(factura.cliente,c.nombre,fc.nombre) as nombrecliente'), 'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus', DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'))->
            where('factura.empresa',Auth::user()->empresa)->
            where('factura.tipo', 3)->
            where('factura.codigo', NULL);

        if ($request->filtro == true) {
            if($request->cot_nro){
                $cotizaciones->where(function ($query) use ($request) {
                    $query->orWhere('factura.cot_nro', 'like', "%{$request->cot_nro}%");
                });
            }
            if($request->nombre){
                $cotizaciones->where(function ($query) use ($request) {
                    $query->orWhere('fc.nombre', 'like', "%{$request->nombre}%");
                });
            }
            if($request->fecha){
                $cotizaciones->where(function ($query) use ($request) {
                    $query->orWhere('factura.fecha', date('Y-m-d', strtotime($request->fecha)));
                });
            }
            if($request->estatus){
                $cotizaciones->where(function ($query) use ($request) {
                    $query->orWhere('factura.estatus', $request->estatus);
                });
            }
        }

        $cotizaciones = $cotizaciones->groupBy('if.factura');

        return datatables()->eloquent($cotizaciones)
        ->editColumn('cot_nro', function (Cotizacion $cotizacion) {
            return $cotizacion->cot_nro ? "<a href=" . route('cotizaciones.show', $cotizacion->cot_nro) . ">$cotizacion->cot_nro</a>" : "";
        })
        ->editColumn('cliente', function (Cotizacion $cotizacion) {
            return  $cotizacion->cliente ? "<a href=" . route('contactos.show', $cotizacion->cliente()->id) . " target='_blank'>{$cotizacion->cliente()->nombre} {$cotizacion->cliente()->apellidos()}</a>" : "";
        })
        ->editColumn('fecha', function (Cotizacion $cotizacion) {
            return date('d-m-Y', strtotime($cotizacion->fecha));
        })
        ->addColumn('total', function (Cotizacion $cotizacion) use ($moneda) {
            return "{$moneda} {$cotizacion->parsear($cotizacion->total()->total)}";
        })
        ->addColumn('estatus', function (Cotizacion $cotizacion) {
            return   '<span class="font-weight-bold text-' . $cotizacion->estatus(true) . '">' . $cotizacion->estatus(). '</span>';
        })
        ->addColumn('acciones', $modoLectura ?  "" : "cotizaciones.acciones")
        ->rawColumns(['cot_nro','cliente','fecha','total','estatus', 'acciones'])
        ->toJson();
    }

    /**
     * Formulario para crear un nueva Factura
     * @return view
     */
    public function create(){
        $this->getAllPermissions(Auth::user()->id);

      //view()->share(['icon' =>'', 'title' => 'Nueva Cotización', 'subseccion' => 'cotizacion']);
       $title = 'Nueva Cotización';
        $subseccion = 'cotizacion';

        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
        $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();

        $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $identificaciones=TipoIdentificacion::all();

        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();

        //Datos necesarios para hacer funcionar la ventana modal
        $dataPro = (new InventarioController)->create();
        $medidas2 = $dataPro->medidas;
        $unidades2 = $dataPro->unidades;
        $extras2 = $dataPro->extras;
        $listas2 = $dataPro->listas;
        $bodegas2 = $dataPro->bodegas;


        $categorias=Categoria::where('empresa',Auth::user()->empresa)
            ->orWhere('empresa', 1)
            ->whereNull('asociado')->get();

        return view('cotizaciones.create')->with(compact('clientes', 'inventario', 'vendedores',
                                                'terminos', 'impuestos', 'extras', 'bodegas', 'listas', 'categorias',
            'medidas2', 'unidades2', 'extras2', 'listas2','bodegas2','identificaciones', 'tipos_empresa','prefijos','title','subseccion'));
    }

    /**
     * Registrar una nueva factura
     * Si hay items inventariable resta los valores al inventario
     * @param Request $request
     * @return redirect
     */
    public function store(Request $request){
        $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();
        $caja=$nro->cotizacion;
        while (true) {
            $numero=Cotizacion::where('empresa', Auth::user()->empresa)->where('cot_nro', $caja)->count();
            if ($numero==0) {
                break;
            }
            $caja++;
        }


        $factura = new Cotizacion;
        $factura->notas =$request->notas;
        $factura->tipo =3;
        $factura->cot_nro=$caja;
        $factura->empresa=Auth::user()->empresa;
        if ($request->tipocliente==1) {
            $factura->cliente=$request->cliente;
        }
        $factura->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
        $factura->vencimiento=Carbon::parse($request->vencimiento)->format('Y-m-d');
        $factura->observaciones=mb_strtolower($request->observaciones);
        $factura->vendedor=$request->vendedor;
        $factura->lista_precios=$request->lista_precios;
        $factura->bodega=$request->bodega;
        $factura->save();

        if ($request->tipocliente!=1) {
            DB::table('factura_contacto')->insert(['factura'=>$factura->id, 'nombre'=>ucwords(mb_strtolower($request->clienterapido)), 'telefono'=>$request->telefono, 'email'=>$request->email ]);
        }

        for ($i=0; $i < count($request->item) ; $i++) {
            $items = new ItemsFactura;
            $items->factura=$factura->id;
            $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
            $producto = Inventario::where('id', $request->item[$i])->first();

            if (is_numeric($request->item[$i])) {
                $items->producto=$request->item[$i];
            }
            else{
                $id=DB::table('inventario_volatil')->insertGetId(['empresa'=>Auth::user()->empresa, 'producto'=>mb_strtolower($request->item[$i])]);
                $camposextra='campoextra'.$request->camposextra[$i];
                $datoextra='datoextra'.$request->camposextra[$i];
                if ($request->$camposextra) {
                    $array=array();
                    for ($j=0; $j < count($request->$camposextra) ; $j++) {
                        $array[]=array('empresa'=>Auth::user()->empresa, 'id_producto'=>$id, 'meta_key'=>$request->$camposextra[$j], 'meta_value'=>$request->$datoextra[$j]);
                    }

                    if (count($array)>0) {
                        DB::table('inventario_volatil_meta')->insert($array);
                    }
                }
                $items->tipo_inventario=2;
                $items->producto=$id;
            }

            $items->ref=$request->ref[$i];
            $items->precio=$this->precision($request->precio[$i]);
            $items->descripcion=$request->descripcion[$i];
            $items->id_impuesto=$request->impuesto[$i];
            $items->impuesto=$impuesto->porcentaje;
            $items->cant=$request->cant[$i];
            $items->desc=$request->desc[$i];
            $items->save();

            $nro->cotizacion=$caja+1;
            $nro->save();

        }
        $factura =Cotizacion::find($factura->id);
        $mensaje='Se ha creado satisfactoriamente la cotización';
        if ($factura->cliente()->email) {
            $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
            if($host){
                $existing = config('mail');
                $new =array_merge(
                    $existing, [
                        'host' => $host->servidor,
                        'port' => $host->puerto,
                        'encryption' => $host->seguridad,
                        'username' => $host->usuario,
                        'password' => $host->password,
                        'from' => [
                            'address' => $host->address,
                            'name' => $host->name
                        ],
                    ]
                );
                config(['mail'=>$new]);
            }
            $this->enviar($factura->cot_nro, null, false);
            $mensaje.=', Se ha enviado la cotización al correo del cliente';
        }
        return redirect('empresa/cotizaciones')->with('success', $mensaje)->with('codigo', $factura->id);
    }

    /**
     * Muestra la cotizacion como si fuera a editarla
     */
    public function facturar($id){
        $this->getAllPermissions(Auth::user()->id);
        $cotizacion = Cotizacion::where('empresa',Auth::user()->empresa)->where('tipo',3)->where('cot_nro', $id)->first();
        if ($cotizacion) {

            $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->get();
            $nro=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('preferida',1)->where('estado',1)->first();
            if (!$nro) {
                $mensaje='Debes crear una numeración para facturas de venta preferida';
                return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
            }
            //Obtengo el objeto bodega
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $cotizacion->bodega)->first();
            if (!$bodega) {
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
            }
            $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
            $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            $identificaciones=TipoIdentificacion::all();
            $items = ItemsFactura::where('factura',$cotizacion->id)->get();
            $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();
            view()->share(['icon' =>'', 'title' => 'Facturar Cotización '.$cotizacion->cot_nro]);
            $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
            $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
            $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
            $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            
            return view('cotizaciones.facturar')->with(compact('numeraciones', 'nro', 'clientes', 'inventario', 'vendedores', 'terminos', 'impuestos', 'cotizacion', 'items', 'extras', 'identificaciones', 'listas', 'bodegas'));
        }
        return redirect('empresa/cotizaciones')->with('success', 'No existe un registro con ese id');
    }

    /**
     * Ver una cotizacion
     * @param int $id
     * @return view
     */
    public function show($id){

        $this->getAllPermissions(Auth::user()->id);
        $factura = Cotizacion::where('empresa',Auth::user()->empresa)->where('tipo',3)->where('cot_nro', $id)->first();
        if ($factura) {
            view()->share(['title' => 'Cotización '.$factura->cot_nro, 'invert'=>true, 'icon' =>'']);

            $items = ItemsFactura::where('factura',$factura->id)->get();
            return view('cotizaciones.show')->with(compact('factura', 'items'));
        }
        return redirect('empresa/cotizaciones')->with('success', 'No existe un registro con ese id');
    }

    /**
     * Funcion para generar un PDF
     * @param int $id
     * @return PDF
     */
    public function Imprimir($id){
        /**
         * toma en cuenta que para ver los mismos
         * datos debemos hacer la misma consulta
         **/
        view()->share(['title' => 'Imprimir Cotización']);
        $factura = Cotizacion::where('empresa',Auth::user()->empresa)->where('tipo',3)->where('cot_nro', $id)->first();
        if ($factura) {

            $items = ItemsFactura::where('factura',$factura->id)->get();
            $itemscount=ItemsFactura::where('factura',$factura->id)->count();
            $pdf = PDF::loadView('pdf.cotizacion', compact('items', 'factura', 'itemscount'));
            return  response ($pdf->stream())->withHeaders([
                'Content-Type' =>'application/pdf',]);

        }
    }

    /**
     * Formulario para modificar una cotizacion
     * @param int $id
     * @return view
     */
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $cotizacion = Cotizacion::where('empresa',Auth::user()->empresa)->where('tipo',3)->where('cot_nro', $id)->first();
            $categorias=Categoria::where('empresa',Auth::user()->empresa)
        ->orWhere('empresa', 1)
        ->whereNull('asociado')->get();
        $medidas=DB::table('medidas')->get();
        $unidades=DB::table('unidades_medida')->get();
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();

        if ($cotizacion) {
            //Obtengo el objeto bodega
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $cotizacion->bodega)->first();
            if (!$bodega) {
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
            }
            $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();

            $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();

            $items = ItemsFactura::where('factura',$cotizacion->id)->get();
            $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();
            view()->share(['icon' =>'', 'title' => 'Modificar Cotización '.$cotizacion->cot_nro]);
            $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
            $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
            $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
            $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
            
            
            $dataPro = (new InventarioController)->create();
            $medidas2 = $dataPro->medidas;
            $unidades2 = $dataPro->unidades;
            $extras2 = $dataPro->extras;
            $listas2 = $dataPro->listas;
            $bodegas2 = $dataPro->bodegas;
            $categorias=Categoria::where('empresa',Auth::user()->empresa)
                ->orWhere('empresa', 1)
                ->whereNull('asociado')->get();
              $identificaciones=TipoIdentificacion::all();
              //$vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
              //$listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
              $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
              $prefijos=DB::table('prefijos_telefonicos')->get();
              // /Datos necesarios para hacer funcionar la ventana modal
            view()->share(['icon' =>'', 'title' => 'Editar Cotización '.$cotizacion->cot_nro]);
            return view('cotizaciones.edit')->with(compact('clientes', 'inventario', 'vendedores', 'terminos', 'impuestos', 'cotizacion', 'items', 'extras', 'listas', 'bodegas', 'categorias', 'medidas', 'extras',  'medidas2',
                'unidades2', 'extras2', 'listas2','bodegas2'));
        }
        return redirect('empresa/cotizaciones')->with('success', 'No existe un registro con ese id');

    }


    /**
     * Modificar los datos de la cotizacion
     * @param Request $request
     * @return redirect
     */
    public function update(Request $request, $id){
        $factura =Cotizacion::find($id);
        if ($factura) { //Si extiste el registro
            $factura->notas =$request->notas;

            if ($request->facturar) {
                $factura->tipo = 1;
            }

            $factura->estatus =1;
            $factura->empresa=Auth::user()->empresa;
            $factura->lista_precios=$request->lista_precios;
            $factura->bodega=$request->bodega;
            if ($request->tipocliente==1) { //Si el cliente es de contactos o contacto rapido
                $factura->cliente=$request->cliente;
            }
            if ($request->facturar) {
                $nro=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('preferida',1)->where('estado',1)->first();
                if (!$nro) {
                    $mensaje='Debes crear una numeración para facturas de venta preferida';
                    return redirect('empresa/configuracion/numeraciones')->with('error', $mensaje);
                }

                $factura->nro= Factura::where('empresa',Auth::user()->empresa)->where('tipo','!=',3)->count()+1;
                $factura->codigo=$nro->prefijo.$nro->inicio;
                $factura->plazo=$request->plazo;
                $factura->term_cond=$request->term_cond;
                $factura->facnotas=$request->notas;
                $factura->tipo = 1;
            }
            if ($request->tipocliente!=1) {
                $clienterapido=DB::table('factura_contacto')->where('factura', $factura->id)->first();
                if ($request->facturar) {
                    $contacto = new Contacto;
                    $contacto->empresa=Auth::user()->empresa;
                    $contacto->tip_iden=$request->tip_iden;
                    $contacto->nit=$request->identificacion;
                    $contacto->nombre=ucwords(mb_strtolower($request->clienterapido));
                    $contacto->email=mb_strtolower($request->email);
                    $contacto->telefono1=$request->telefono;
                    $contacto->tipo_contacto=0;
                    $contacto->observaciones='Creado desde contacto rapido';
                    $contacto->save();
                    $factura->cliente=$contacto->id;
                    $factura->tipo = 1;
                }
                else{
                    if ($clienterapido) {
                        DB::table('factura_contacto')->where('factura', $factura->id)->update(['nombre'=>ucwords(mb_strtolower($request->clienterapido)), 'telefono'=>$request->telefono, 'email'=>$request->email ]);
                    }
                    else{
                        DB::table('factura_contacto')->insert(['factura'=>$factura->id, 'nombre'=>ucwords(mb_strtolower($request->clienterapido)), 'telefono'=>$request->telefono, 'email'=>$request->email ]);

                    }
                    $factura->cliente=null;
                }

            }

            $factura->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $factura->vencimiento=Carbon::parse($request->vencimiento)->format('Y-m-d');
            $factura->observaciones=mb_strtolower($request->observaciones);
            $factura->vendedor=$request->vendedor;
            $factura->save();
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
            if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
            }
            $array=array();
            $inner=array();
            for ($i=0; $i < count($request->ref) ; $i++) {
                $cat='id_item'.($i+1);//Variable para id del item
                if($request->$cat){//si es un item registrado o nuevo
                    $items = ItemsFactura::where('id', $request->$cat)->first();
                }
                else{
                    $items = new ItemsFactura;
                }

                $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
                $producto = Inventario::where('id', $request->item[$i])->first();
                if($request->$cat){
                    //si el producto de del inventario (1) o es volatil (2)
                    if($items->tipo_inventario==2){
                        $id=$items->producto;
                        DB::table('inventario_volatil')->where('id', $items->producto)->update(['producto'=>$request->item[$i]]);
                        $array[]=$id;
                        $camposextra='campoextra'.$request->camposextra[$i];
                        $datoextra='datoextra'.$request->camposextra[$i];
                        if ($request->$camposextra) { //si el producto volatil esta tiene campos extras
                            $idis=array();
                            for ($j=0; $j < count($request->$camposextra) ; $j++) {
                                $campo=CamposExtra::where('empresa', Auth::user()->empresa)->where('id', $request->$camposextra[$j])->Orwhere('campo', $request->$camposextra[$j])->first();//obtener valores del campo extra
                                //consulto valores del producto para el campo extra
                                $extra=DB::table('inventario_volatil_meta')->where('empresa', Auth::user()->empresa)->where('meta_key', $request->$camposextra[$j])->Orwhere('meta_key', $request->$camposextra[$j])->first();
                                if ($extra) {//si esta registrado ese campo para el producto
                                    $idis[]=$extra->id;
                                    DB::table('inventario_volatil_meta')->where('id', $extra->id)->update(['meta_key'=>$campo->campo,'meta_value'=>$request->$datoextra[$j]]);
                                }
                                else{
                                    $idis[]=DB::table('inventario_volatil_meta')->insertGetId(['empresa'=>Auth::user()->empresa, 'id_producto'=>$id, 'meta_key'=>$campo->campo, 'meta_value'=>$request->$datoextra[$j]]);
                                }
                            }
                            if (count($idis)>0) {
                                DB::table('inventario_volatil_meta')->whereNotIn('id', $idis)->where('id_producto', $id)->delete();
                            }

                        }
                        else{
                            DB::table('inventario_volatil_meta')->where('id_producto', $id)->delete();
                        }
                        $items->tipo_inventario=2;
                        $items->producto=$id;

                        if ($request->facturar) {
                            $inventario = new Inventario;
                            $inventario->empresa=Auth::user()->empresa;
                            $inventario->producto=ucwords(mb_strtolower($request->item[$i]));
                            $inventario->tipo_producto=2;
                            $inventario->ref=$request->ref[$i];
                            $inventario->precio=$this->precision($request->precio[$i]);
                            $inventario->descripcion=$request->descripcion[$i];
                            $inventario->id_impuesto=$request->impuesto[$i];
                            $inventario->impuesto=$impuesto->porcentaje;
                            $inventario->unidad=1;$inventario->nro=0;
                            $inventario->save();
                            $items->producto=$inventario->id;
                            $items->tipo_inventario=1;

                            $camposextra='campoextra'.$request->camposextra[$i];
                            $datoextra='datoextra'.$request->camposextra[$i];
                            if ($request->$camposextra) {
                                for ($j=0; $j < count($request->$camposextra) ; $j++) {
                                    $campo=CamposExtra::where('empresa', Auth::user()->empresa)->where('id', $request->$camposextra[$j])->Orwhere('campo', $request->$camposextra[$j])->first();//obtener valores del campo extra
                                    DB::table('inventario_meta')->insert(['empresa'=>Auth::user()->empresa, 'id_producto'=>$inventario->id, 'meta_key'=>$campo->campo, 'meta_value'=>$request->$datoextra[$j]]);
                                }
                            }

                        }
                    }
                    else{
                        $items->producto=$request->item[$i];
                        if ($request->facturar) {
                            $producto = Inventario::where('id', $request->item[$i])->first();
                            //Si el producto es inventariable y existe esa bodega, restará el valor registrado
                            if ($producto->tipo_producto==1) {
                                $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
                                if ($ajuste) {
                                    $ajuste->nro-=$request->cant[$i];
                                    $ajuste->save();
                                }
                            }

                        }
                    }
                }
                else{
                    if (is_numeric($request->item[$i])) {
                        $items->producto=$request->item[$i];
                    }
                    else{
                        $id=DB::table('inventario_volatil')->insertGetId(['empresa'=>Auth::user()->empresa, 'producto'=>$request->item[$i]]);
                        $array[]=$id;
                        $camposextra='campoextra'.$request->camposextra[$i];
                        $datoextra='datoextra'.$request->camposextra[$i];
                        if ($request->$camposextra) { //si el producto volatil esta tiene campos extras
                            $idis=array();
                            for ($j=0; $j < count($request->$camposextra) ; $j++) {
                                $campo=CamposExtra::where('empresa', Auth::user()->empresa)->where('id', $request->$camposextra[$j])->Orwhere('campo', $request->$camposextra[$j])->first();//obtener valores del campo extra
                                //consulto valores del producto para el campo extra
                                $extra=DB::table('inventario_volatil_meta')->where('empresa', Auth::user()->empresa)->where('meta_key', $request->$camposextra[$j])->Orwhere('meta_key', $request->$camposextra[$j])->first();
                                if ($extra) {//si esta registrado ese campo para el producto
                                    $idis[]=$extra->id;
                                    DB::table('inventario_volatil_meta')->where('id', $extra->id)->update(['meta_key'=>$campo->campo,'meta_value'=>$request->$datoextra[$j]]);
                                }
                                else{
                                    $idis[]=DB::table('inventario_volatil_meta')->insertGetId(['empresa'=>Auth::user()->empresa, 'id_producto'=>$id, 'meta_key'=>$request->$camposextra[$j], 'meta_value'=>$request->$datoextra[$j]]);
                                }
                            }
                            if (count($idis)>0) {
                                DB::table('inventario_volatil_meta')->whereNotIn('id', $idis)->where('id_producto', $id)->delete();
                            }

                        }
                        else{
                            DB::table('inventario_volatil_meta')->where('id_producto', $id)->delete();
                        }
                        $items->tipo_inventario=2;
                        $items->producto=$id;
                    }
                }

                $items->factura=$factura->id;
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
                DB::table('items_factura')->where('factura', $factura->id)->whereNotIn('id', $inner)->delete();
            }

            if ($request->facturar) {
                $cant=Cotizacion::where('empresa',Auth::user()->empresa)->where('tipo',1)->where('codigo','=',($nro->prefijo.$nro->inicio))->count();
                if($cant>0){
                    $nro->inicio=$nro->inicio+1;
                    $nro->save();}
                $mensaje='Se ha facturado satisfactoriamente la cotización';
                return redirect('empresa/facturas/'.$factura->id)->with('success', $mensaje)->with('codigo', $factura->id);

            }
            $mensaje='Se ha modificado satisfactoriamente la factura';
            return redirect('empresa/cotizaciones')->with('success', $mensaje)->with('codigo', $factura->id);

        }
        return redirect('empresa/cotizacionesi')->with('success', 'No existe un registro con ese id');
    }

    /**
     * Funcion para enviar por correo al cliente
     * @return redirect
     */
    public function enviar($id, $emails=null, $redireccionar=true){
        /**
         * toma en cuenta que para ver los mismos
         * datos debemos hacer la misma consulta
         **/
        view()->share(['title' => 'Enviando Cotización']);
        $factura = Cotizacion::where('empresa',Auth::user()->empresa)->where('tipo',3)->where('cot_nro', $id)->first();
        if ($factura) {
            if (!$emails) {
                $emails[]=$factura->cliente()->email;
                if ($factura->cliente) {
                    if ($factura->cliente()->asociados('number')>0) {
                        foreach ($factura->cliente()->asociados() as $asociado) {
                            if ($asociado->notificacion==1 && $asociado->email) {
                                $emails[]=$asociado->email;
                            }
                        }
                    }

                }
            }
            if (!$emails || count($emails)==0) {
                if ($redireccionar) {
                    return redirect('empresa/cotizaciones/'.$factura->cot_nro)->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
                }
                return false;
            }
            $items = ItemsFactura::where('factura',$factura->id)->get();
            $itemscount=ItemsFactura::where('factura',$factura->id)->count();
            $pdf = PDF::loadView('pdf.cotizacion', compact('items', 'factura', 'itemscount'))->stream();

            $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
            if($host){
                $existing = config('mail');
                $new =array_merge(
                    $existing, [
                        'host' => $host->servidor,
                        'port' => $host->puerto,
                        'encryption' => $host->seguridad,
                        'username' => $host->usuario,
                        'password' => $host->password,
                        'from' => [
                            'address' => $host->address,
                            'name' => $host->name
                        ],
                    ]
                );
                config(['mail'=>$new]);
            }

            self::sendMail('emails.cotizacion', compact('factura'), compact('pdf', 'emails', 'factura'), function($message) use ($pdf, $emails, $factura){
                $message->from(Auth::user()->empresa()->email, Auth::user()->empresa()->nombre);
                $message->to($emails)->subject('Cotización #'.$factura->cot_nro);
                $message->attachData($pdf, 'cotizacion.pdf', ['mime' => 'application/pdf']);
            });
        }
        if ($redireccionar) {
            return redirect('empresa/cotizaciones/'.$factura->cot_nro)->with('success', 'Se ha enviado el correo');
        }
    }

    public function datatable_producto(Request $request, $producto){
        // storing  request (ie, get/post) global array to a variable
        $requestData =  $request;
        $columns = array(
            // datatable column index  => database column name
            0 => 'factura.cot_nro',
            1 => 'nombrecliente',
            2 => 'factura.fecha',
            3 => 'factura.vencimiento',
            4 => 'total',
            5 => 'pagado',
            6 => 'porpagar',
            7=>'factura.estatus',
            8=>'acciones'
        );
        $facturas=Cotizacion::leftjoin('contactos as c', 'factura.cliente', '=', 'c.id')
            ->leftjoin('factura_contacto as fc', 'factura.id', '=', 'fc.factura')->select('factura.*', DB::raw('if(factura.cliente,c.nombre,fc.nombre) as nombrecliente'))->where('factura.empresa',Auth::user()->empresa)->where('factura.tipo',3)->whereRaw('factura.id in (Select distinct(factura) from items_factura where producto='.$producto.' and tipo_inventario=1)');

        if ($requestData->search['value']) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $facturas=$facturas->where(function ($query) use ($requestData) {
                $query->where('factura.cot_nro', 'like', '%'.$requestData->search['value'].'%')
                    ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%')
                    ->orwhere('fc.nombre', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        $totalFiltered=$totalData=$facturas->count();
        $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);

        $facturas=$facturas->get();
        $data = array();
        foreach ($facturas as $factura) {
            $nestedData = array();
            $nestedData[] = '<a href="'.route('cotizaciones.show',$factura->cot_nro).'">'.$factura->cot_nro.'</a>';
            if($factura->cliente){
                $nestedData[] = '<a href="'.route('contactos.show',$factura->cliente).'" target="_blanck">'.$factura->nombrecliente.'</a>';
            }
            else{
                $nestedData[] = $factura->nombrecliente;
            }

            $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
            $nestedData[] = '<spam class="text-'.$factura->estatus(true).'">'.$factura->estatus().'</spam>';

            $boton = '<a href="'.route('cotizaciones.imprimir.nombre',['id' => $factura->cot_nro, 'name'=> 'Cotizacion No. '.$factura->cot_nro.'.pdf']).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a> 
        <a href="'.route('cotizaciones.edit',$factura->cot_nro).'" target="_blank" class="btn btn-outline-primary btn-icons"title="Editar"><i class="fas fa-edit"></i></a> ';

            if($factura->estatus!=2){
                $boton .= '<a href="'.route('cotizaciones.imprimir',$factura->cot_nro).'" target="_blanck"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>';
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

    public function destroy($id){
        $factura = Cotizacion::where('empresa',Auth::user()->empresa)->where('tipo',3)->where('cot_nro', $id)->first();

        //Proceso para saber cual es el número mas alto en consecutivos de una cotizacion de la empresa.
        $last_cotizacion= Cotizacion::where('empresa',Auth::user()->empresa)->where('tipo',3)->orderBy('cot_nro','DESC')->first();

        if ($factura) {

            if($last_cotizacion->cot_nro == $factura->cot_nro)
            {
        
                //si es igual es por que es la ultima cotizacion y el cot_nro ya no existe, entonces vamos a reducir este numero en la Numeracion.
                $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();
                $nro->cotizacion = $nro->cotizacion - 1;
                $nro->save();
                

                $items = ItemsFactura::where('factura',$factura->id)->get();
                DB::table('factura_contacto')->where('factura', $factura->id)->delete();
                foreach($items as $item)
                {
                    if($item->tipo_inventario==1){
                        DB::table('inventario_volatil_meta')->where('id_producto', $item->id)->delete();
                        DB::table('inventario_volatil')->where('id', $item->id)->delete();
                    }
                }
                ItemsFactura::where('factura',$factura->id)->delete();
                $factura->delete();
                $mensaje='Se ha eliminado satisfactoriamente la cotización';
                return back()->with('success', $mensaje);
            }
            return redirect('empresa/cotizaciones')->with('error', 'No puedes eliminar esta cotización por que no es la ultima creada.');
        }
        return redirect('empresa/cotizaciones')->with('success', 'No existe un registro con ese id');
    }

    public function datatable_cliente(Request $request, $cliente){
        // storing  request (ie, get/post) global array to a variable
                $requestData =  $request;
        $columns = array(
            // datatable column index  => database column name
            0 => 'factura.cot_nro',
            1 => 'nombrecliente',
            2 => 'factura.fecha',
            3 => 'total',
            4 => 'factura.estatus',
            5 => 'acciones'
        );

        $facturas = Cotizacion::leftjoin('contactos as c', 'factura.cliente', '=', 'c.id')
            ->leftjoin('factura_contacto as fc', 'factura.id', '=', 'fc.factura')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->select('factura.id', 'factura.codigo', 'factura.cot_nro', DB::raw('if(factura.cliente,c.nombre,fc.nombre) as nombrecliente'),
                'factura.cliente', 'factura.fecha', 'factura.vencimiento', 'factura.estatus',
                DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'))
            ->where('factura.empresa',Auth::user()->empresa)
            ->where('factura.tipo', 3)
            ->where('factura.cliente', $cliente);


        if ($requestData->search['value']) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $facturas=$facturas->where(function ($query) use ($requestData) {
                $query->where('factura.cot_nro', 'like', '%'.$requestData->search['value'].'%')
                    ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%')
                    ->orwhere('fc.nombre', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        $facturas=$facturas->groupBy('if.factura');
        $totalFiltered=$totalData=$facturas->count();
        $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);


        $facturas=$facturas->get();

        $data = array();
        foreach ($facturas as $factura) {
            $nestedData = array();
            $nestedData[] = '<a href="'.route('cotizaciones.show',$factura->cot_nro).'">'.$factura->cot_nro.'</a>';
            if($factura->cliente){
                $nestedData[] = '<a href="'.route('contactos.show',$factura->cliente).'" target="_blanck">'.$factura->nombrecliente.'</a>';
            }
            else{
                $nestedData[] = $factura->nombrecliente;
            }

            $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
            $nestedData[] = '<spam class="text-'.$factura->estatus(true).'">'.$factura->estatus().'</spam>';
            $boton = '<a href="'.route('cotizaciones.imprimir.nombre',['id' => $factura->cot_nro, 'name'=> 'Cotizacion No. '.$factura->cot_nro.'.pdf']).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a> 
        <a href="'.route('cotizaciones.edit',$factura->cot_nro).'" target="_blank" class="btn btn-outline-primary btn-icons"title="Editar"><i class="fas fa-edit"></i></a> ';

            if($factura->estatus!=2){
                $boton .= '<a href="'.route('cotizaciones.imprimir',$factura->cot_nro).'" target="_blanck"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
          <form action="'.route('cotizaciones.destroy',$factura->cot_nro).'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-factura'.$factura->id.'"> '.csrf_field().'
              <input name="_method" type="hidden" value="DELETE">
              </form>
              <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('."'eliminar-factura".$factura->id."', '¿Estas seguro que deseas eliminar la cotización?', 'Se borrara de forma permanente');".'"><i class="fas fa-times"></i></button>
              ';
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
}
