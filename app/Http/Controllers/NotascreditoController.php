<?php

namespace App\Http\Controllers;

use App\Banco;
use App\Categoria;
use App\Contacto;
use App\Funcion;
use App\Impuesto;
use App\Empresa;
use App\Model\Gastos\Gastos;
use App\Model\Ingresos\Devoluciones;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\FacturaRetencion;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\ItemsNotaCredito;
use App\Model\Ingresos\NotaCredito;
use App\Model\Ingresos\NotaCreditoFactura;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\ProductosBodega;
use App\NotaRetencion;
use App\NotaSaldo;
use App\Numeracion;
use App\Retencion;
use Auth;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Mail;
use Session;
use Validator;
use App\NumeracionFactura;
use DOMDocument; use QrCode; use File;
use Config;
use App\ServidorCorreo;
use ZipArchive;
use App\Campos;
use App\PucMovimiento;
use App\FormaPago;

class NotascreditoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'); 
        view()->share(['seccion' => 'facturas', 'title' => 'Notas de Crédito', 'icon' =>'fas fa-plus', 'subseccion' => 'credito']);
    }

    /**
     * Vista Principal de las notas de credito
     */
    public function indexOLD(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $busqueda=false;
        $campos=array('', 'notas_credito.nro', 'nombrecliente', 'notas_credito.fecha', 'total', 'por_aplicar');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';

        $facturas = NotaCredito::leftjoin('contactos as c', 'c.id', '=', 'notas_credito.cliente')
        ->join('items_notas as if', 'notas_credito.id', '=', 'if.nota')

        ->select('notas_credito.*', 'c.nombre as nombrecliente', 'c.apellido1 as ape1cliente', 'c.apellido2 as ape2cliente', DB::raw('SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
        DB::raw('(SUM(
          (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) - if(
          (Select SUM(monto) from notas_devolucion_dinero where nota=notas_credito.id), 
          (Select SUM(monto) from notas_devolucion_dinero where nota=notas_credito.id), 0)) as por_aplicar'))->where('notas_credito.empresa',Auth::user()->empresa);

        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
        if ($request->name_1) {
            $busqueda=true; $appends['name_1']=$request->name_1; $facturas=$facturas->where('notas_credito.nro', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
            $busqueda=true; $appends['name_2']=$request->name_2; $facturas=$facturas->where('c.nombre', 'like', '%' .$request->name_2.'%');
        }
        if ($request->name_3) {
            $busqueda=true; $appends['name_3']=$request->name_3; $facturas=$facturas->where('notas_credito.fecha', date('Y-m-d', strtotime($request->name_3)));
        }
        $facturas=$facturas->groupBy('if.nota');
        if ($request->name_4) {
            $busqueda=true; $appends['name_4']=$request->name_4; $appends['name_4_simb']=$request->name_4_simb; $facturas=$facturas->havingRaw(DB::raw('SUM(
              (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) '.$request->name_4_simb.' ?'), [$request->name_4]);
        }
        $facturas=$facturas->OrderBy($orderby, $order)->paginate(100)->appends($appends);

        return view('notascredito.index')->with(compact('facturas','request', 'busqueda'));
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 18)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();

        return view('notascredito.index', compact('tabla'));
    }

    public function notascredito (Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $moneda = auth()->user()->empresa()->moneda;
        $notas = NotaCredito::query()->
            leftjoin('contactos as c', 'c.id', '=', 'notas_credito.cliente')->
            join('items_notas as if', 'notas_credito.id', '=', 'if.nota')->
            select('notas_credito.*', 'c.nombre as nombrecliente', 'c.apellido1 as ape1cliente', 'c.apellido2 as ape2cliente', DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'), DB::raw('(SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) - if((Select SUM(monto) from notas_devolucion_dinero where nota=notas_credito.id),(Select SUM(monto) from notas_devolucion_dinero where nota=notas_credito.id), 0)) as por_aplicar'))->
            where('notas_credito.empresa',Auth::user()->empresa);

        if ($request->filtro == true) {
            if($request->nro){
                $notas->where(function ($query) use ($request) {
                    $query->orWhere('notas_credito.nro', 'like', "%{$request->nro}%");
                });
            }
            if($request->nombre){
                $notas->where(function ($query) use ($request) {
                    $query->orWhere('c.nombre', 'like', "%{$request->nombre}%");
                });
            }
            if($request->fecha){
                $notas->where(function ($query) use ($request) {
                    $query->orWhere('notas_credito.fecha', date('Y-m-d', strtotime($request->fecha)));
                });
            }
            if($request->emitida){
                $emitida = ($request->emitida == 'A') ? 0 : $request->emitida;
                $notas->where(function ($query) use ($emitida) {
                    $query->orWhere('notas_credito.emitida', $emitida);
                });
            }
        }

        $notas = $notas->groupBy('if.nota');

        return datatables()->eloquent($notas)
        ->editColumn('nro', function (NotaCredito $notas) {
            return $notas->nro ? "<a href=" . route('notascredito.show', $notas->nro) . ">$notas->nro</a>" : "";
        })
        ->editColumn('cliente', function (NotaCredito $notas) {
            return  $notas->cliente ? "<a href=" . route('contactos.show', $notas->cliente()->id) . " target='_blank'>{$notas->cliente()->nombre} {$notas->cliente()->apellidos()}</a>" : "";
        })
        ->editColumn('fecha', function (NotaCredito $notas) {
            return date('d-m-Y', strtotime($notas->fecha));
        })
        ->addColumn('total', function (NotaCredito $notas) use ($moneda) {
            return "{$moneda} {$notas->parsear($notas->total()->total)}";
        })
        ->addColumn('por_aplicar', function (NotaCredito $notas) use ($moneda) {
            return "{$moneda} {$notas->parsear($notas->por_aplicar())}";
        })
        ->addColumn('emitida', function (NotaCredito $notas) {
            return   '<span class="font-weight-bold text-' . $notas->emitida(true) . '">' . $notas->emitida(). '</span>';
        })
        ->addColumn('acciones', $modoLectura ?  "" : "notascredito.acciones")
        ->rawColumns(['nro','cliente','fecha','total','por_aplicar','emitida', 'acciones'])
        ->toJson();
    }

    /**
     * Formulario para crear un nueva nota de credito
     * @return view
     */
    public function create($producto=false){ 
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['icon' =>'', 'title' => 'Nueva Nota de Crédito', 'subseccion' => 'credito']);
        $numero = Numeracion::where('empresa',Auth::user()->empresa)->first()->credito;
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
        $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();
        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
        $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
        $tipos=DB::table('tipos_nota_credito')->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();

        //obtiene las formas de pago relacionadas con este modulo (Facturas)
        $relaciones = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();

        return view('notascredito.create')->with(compact('producto','categorias','clientes', 'inventario', 'impuestos', 'tipos', 'bancos', 'listas', 'bodegas','retenciones', 'numero','relaciones'));
    }

    /**
     * Registrar una nueva nota de credito
     * Si hay items inventariable sumar los valores al inventario
     * @param Request $request
     * @return redirect
     */
    public function store(Request $request)
    {

        //Validaciones con respecto a la nueva programacion del cda.
        if ($request->tipo_operacion == 3) {
            if (count($request->item) > 1) {
                $mensaje = "Si el tipo de operación es nota de crédito con detalle de recaudo a terceros no puedes facturar más de dos productos.";
                return back()->with('error', $mensaje);
            }
            if (DB::table('detalle_terceros')->where('fk_idempresa', auth()->user()->empresa)->where('inventario_id', $request->item[0])->count() == 0) {
                $mensaje = "El producto que deseas facturar no tiene un detalle de recaudo asociado.";
                return back()->with('error', $mensaje);
            }
        }

        

        if ($request->factura) {
            $total = 0;
            $precioNotas = 0;
            $retencion = 0;
            $impuesto = 0;
            $descuento = 0;
            $z = 1;

            //MULTI IMPUESTOS
            for ($i = 0; $i < count($request->precio); $i++) {
                $total += ($request->cant[$i] * $request->precio[$i]);

                if ($request->descuento[$i] != null) {
                    $descuento = ($request->precio[$i] * $request->cant[$i]) * $request->descuento[$i] / 100;
                    $total -= $descuento;
                }

                $data  = $request->all();

                if (isset($data['impuesto' . $z])) {
                    if ($data['impuesto' . $z]) {
                        for ($x = 0; $x < count($data['impuesto' . $z]); $x++) {
                            $porcentaje = Impuesto::find($data['impuesto' . $z][$x])->porcentaje;
                            if ($porcentaje > 0) {
                                $impuesto += (($request->cant[$i] * $request->precio[$i] - $descuento) * $porcentaje) / 100;
                            }
                        }
                    }
                }
                //en caso tal de no existir el desarrollo de multiimpuestos
                else if(isset($data['impuesto'])){
                    if ($data['impuesto']) {
                        for ($x = 0; $x < count($data['impuesto']); $x++) {
                            $porcentaje = Impuesto::find($data['impuesto'][$x])->porcentaje;
                            if ($porcentaje > 0) {
                                $impuesto += (($request->cant[$i] * $request->precio[$i] - $descuento) * $porcentaje) / 100;
                            }
                        }
                    }
                }
                $z++;
            }
            $total += $impuesto;
            //MULTI IMPUESTOS

            if ($request->precio_reten) {
                for ($i = 0; $i < count($request->precio_reten); $i++) {
                    $retencion += $request->precio_reten[$i];
                }
            }

            $total = $total - $retencion;
            $fc = Factura::find($request->factura);

            foreach ($fc->notas_credito() as $notas) {
                //Acumulado de total en notas creditos de la factura.
                $precioNotas += $notas->nota()->total()->total;
            }

            if (round($precioNotas) > round($fc->total()->total)) {
                return redirect()->back()->with('error', "La nota crédito supera el valor de la factura de venta");
            }
        }

        $montoFactura = 0;
        $montoRetenciones = 0;
        $impuestoItem = 0;

        $nro = Numeracion::where('empresa', Auth::user()->empresa)->first();
        $caja = $nro->credito;

        while (true) {
            $numero = NotaCredito::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
            if ($numero == 0) {
                break;
            }
            $caja++;
        }

        $notac = new NotaCredito();
        $notac->nro = $caja;
        $notac->empresa = Auth::user()->empresa;
        $notac->cliente = $request->cliente;
        $notac->tipo = $request->tipo;
        $notac->fecha = Carbon::parse($request->fecha)->format('Y-m-d');
        $notac->observaciones = mb_strtolower($request->observaciones);
        $notac->notas = $request->notas;
        $notac->lista_precios = $request->lista_precios;
        $notac->bodega = $request->bodega;
        $notac->tipo_operacion = $request->tipo_operacion;
        $notac->ordencompra    = $request->ordencompra;
        // $notac->tiempo_creacion = now();

        if ($request->tipo_operacion == 3) {
            $impuesto = Impuesto::where('id', $request->impuesto1[0])->first();
            $notac->total_recaudo  = $request->detalle_monto;

            if ($request->desc) {
                $descuento = $request->desc[0];
            } else {
                $descuento = 0;
            }

            //Descontamos un posible descuento.
            $notac->total_valor = $this->precision($request->precio[0] * $request->cant[0]) - ($this->precision($request->precio[0]) * $descuento / 100);
            //añadimos los pósibles porcentajes de iva sobre el producto.
            $notac->total_valor += ($this->precision($request->precio[0]) * $request->cant[0] * $impuesto->porcentaje / 100);
            //restamos retenciones
            if ($request->precio_reten) {
                $totalreten = 0;
                foreach ($request->precio_reten as $valor => $v) {
                    $totalreten += $v;
                }
                $notac->total_valor -= $totalreten;
            } else {
                $totalreten = 0;
            }
            //Sumamos detalle de recaudo
            $notac->total_valor += $request->detalle_monto;
        }

        if (auth()->user()->empresaObj->estado_dian == 1) {
            $notac->technicalkey = auth()->user()->empresaObj->technicalkey;
        }

        $notac->save();

        $bodega = Bodega::where('empresa', Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
        if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
            $bodega = Bodega::where('empresa', Auth::user()->empresa)->where('status', 1)->first();
        }

        //for ($i=0; $i < count($request->ref) ; $i++) {
        $z = 1;
        foreach ($request->item as $i => $valor) {
            //$impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
            $producto = Inventario::where('id', $request->item[$i])->first();
            //Si el producto es inventariable y existe esa bodega, restará el valor registrado
            if ($producto->tipo_producto == 1) {
                $ajuste = ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();

                if ($ajuste) {
                    $ajuste->nro += $request->cant[$i];
                    $ajuste->save();
                }
            }

            if ($request->descuento[$i]) {
                $descuento = ($request->precio[$i] * $request->cant[$i]) * $request->descuento[$i] / 100;
                $precioItem = ($request->precio[$i] * $request->cant[$i]) - $descuento;

                //$impuestoItem = ($precioItem * $impuesto->porcentaje) / 100;
                $tmp = $precioItem + $impuestoItem;

                $montoFactura += $tmp;
            } else {
                $precioItem = $request->precio[$i] * $request->cant[$i];
                //$impuestoItem = ($precioItem * $impuesto->porcentaje) / 100;
                $montoFactura += $precioItem + $impuestoItem;
            }


            $items = new ItemsNotaCredito();

            //MULTI IMPUESTOS
            $data  = $request->all();
            if (isset($data['impuesto' . $z])) {
                if ($data['impuesto' . $z]) {
                    for ($x = 0; $x < count($data['impuesto' . $z]); $x++) {
                        $id_cat = 'id_impuesto_' . $x;
                        $cat = 'impuesto_' . $x;
                        $impuesto = Impuesto::where('id', $data['impuesto' . $z][$x])->first();
                        if ($impuesto) {
                            if ($x == 0) {
                                $items->id_impuesto = $impuesto->id;
                                $items->impuesto = $impuesto->porcentaje;
                            } elseif ($x > 0) {
                                $items->$id_cat = $impuesto->id;
                                $items->$cat = $impuesto->porcentaje;
                            }
                        }
                    }
                }
            }
            //en caso tal de no tener el desarrolo de multiples ivas
            elseif(isset($data['impuesto'])){
                if ($data['impuesto']) {
                    for ($x = 0; $x < count($data['impuesto']); $x++) {
                        $id_cat = 'id_impuesto_' . $x;
                        $cat = 'impuesto_' . $x;
                        $impuesto = Impuesto::where('id', $data['impuesto'][$x])->first();
                        if ($impuesto) {
                            if ($x == 0) {
                                $items->id_impuesto = $impuesto->id;
                                $items->impuesto = $impuesto->porcentaje;
                            } elseif ($x > 0) {
                                $items->$id_cat = $impuesto->id;
                                $items->$cat = $impuesto->porcentaje;
                            }
                        }
                    }
                }
            }
            //MULTI IMPUESTOS

            $items->nota = $notac->id;
            $items->producto = $request->item[$i];
            $items->ref = $request->ref[$i];
            $items->precio = $this->precision($request->precio[$i]);
            $items->descripcion = $request->descripcion[$i];
            $items->cant = $request->cant[$i];
            $items->desc = $request->descuento[$i];
            $items->save();
            $z++;
        }

        if ($request->retencion) {
            foreach ($request->retencion as $key => $value) {
                if ($request->precio_reten[$key]) {
                    $retencion = Retencion::where('id', $request->retencion[$key])->first();
                    $reten = new NotaRetencion();
                    $reten->notas = $notac->id;
                    $reten->tipo = 1; //hace referencia a devoluciones tipo crédito.
                    $reten->valor = $this->precision($request->precio_reten[$key]);
                    $reten->retencion = $retencion->porcentaje;
                    $reten->id_retencion = $retencion->id;
                    $reten->save();
                }
                $montoRetenciones += $request->precio_reten[$key];
            }
            $montoFactura = $this->precision($montoFactura) - $this->precision($montoRetenciones);
        }


        if ($request->factura) {
            //$monto=$this->precision($request->monto_fact[$i]);
            // $factura = Factura::find($request->factura[$i]);
            $factura = Factura::find($request->factura);
            $factura->estatus = 0;
            $factura->save();

            //acumulador de notas credito de una factura
            $precioNota = 0;

            foreach ($factura->notas_credito() as $notas) {
                $precioNota += $notas->nota()->total()->total;
            }

            $items = new NotaCreditoFactura();
            $items->nota = $notac->id;
            $items->factura = $factura->id;
            //$items->pago = $this->precision($factura->porpagar());
            $items->pago = $this->precision($total);
            if ($this->precision($montoFactura) == $this->precision($factura->porpagar())) {
                $factura->estatus = 0;
                $factura->save();
            }

            if ($factura->total()->total == $precioNota) {
                $factura->estatus = 0;
            } elseif ($factura->total()->total == $factura->pagado()) {
                $factura->estatus = 0;
            } else {
                $factura->estatus = 1;
            }

            $factura->save();
            $items->save();

            if ($factura->pagado()) {
                $saldoNota = new NotaSaldo();
                $saldoNota->id_nota = $notac->id;
                $saldoNota->saldo_nota = $factura->pagado();
                $saldoNota->save();

                $cliente = Contacto::find($request->cliente);
                $cliente->saldo_favor += $factura->pagado();
                $cliente->save();
            }
        }

        $nro->credito = $caja + 1;
        $nro->save();
        PucMovimiento::notaCredito($notac,1, $request);

        $mensaje = 'Se ha creado satisfactoriamente la nota de crédito';
        return redirect('empresa/notascredito')->with('success', $mensaje)->with('nota_id', $notac->id);
    }

    /**
     * Ver un Nota Credito
     * @param int $id
     * @return view
     */
    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $nota = NotaCredito::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($nota) {

            view()->share(['title' => 'Nota Crédito:  '.$nota->nro, 'invert'=>true, 'icon' =>'']);
            $retenciones = NotaRetencion::where('notas', $nota->id)->get();

            $items = ItemsNotaCredito::where('nota',$nota->id)->get();
            $facturas = NotaCreditoFactura::where('nota',$nota->id)->get();
            $devoluciones = Devoluciones::where('nota',$nota->id)->get();
            return view('notascredito.show')->with(compact('nota', 'items', 'facturas', 'devoluciones','retenciones'));
        }
        return redirect('empresa/notascredito')->with('success', 'No existe un registro con ese id');
    }

    /**
     * Formulario para modificar los datos de una  nota de credito
     * @param int $id
     * @return view
     */
    public function edit($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $nota = NotaCredito::where('empresa', Auth::user()->empresa)->where('nro', $id)->first();
        $retenciones = Retencion::where('empresa', Auth::user()->empresa)->where('modulo',1)->get();

        if (isset($nota)) {
            $retencionesNotas = NotaRetencion::join('retenciones as r', 'r.id', '=', 'notas_retenciones.id_retencion')
                ->where('notas', $nota->id)->where('r.empresa', Auth::user()->empresa)
                ->select('notas_retenciones.*')
                ->get();

            view()->share(['title' => 'Editar Nota de Credito ', 'icon' => '']);

            $facturaContacto = Factura::where('cliente', $nota->cliente)->select('id', 'codigo')->get();

            $notasFacturas = NotaCreditoFactura::where('nota', $nota->id)->first();

            $categorias = Categoria::where('empresa', Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();
            $proveedores = Contacto::where('empresa', Auth::user()->empresa)->whereIn('tipo_contacto', [1, 2])->get();
            $items = ItemsNotaCredito::where('nota', $nota->id)->get();
            $listas = ListaPrecios::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

            $facturas_reg = NotaCreditoFactura::where('nota', $nota->id)->get();
            $bodega = Bodega::where('empresa', Auth::user()->empresa)->where('id', $nota->bodega)->first();

            $retenciones = Retencion::where('empresa', Auth::user()->empresa)->where('modulo',1)->get();

            $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega=' . $bodega->id . ' and producto=inventario.id) as nro'))->where('empresa', Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega=' . $bodega->id . '), true)')->get();

            //$inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega=' . $bodega->id . ' and producto=inventario.id) as nro'))->where('empresa', Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega=' . $bodega->id . '), true)')->get();

            $bodegas = Bodega::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
            $bancos = Banco::where('empresa', Auth::user()->empresa)->where('estatus', 1)->get();
            $impuestos = Impuesto::where('empresa', Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
            $clientes = Contacto::where('empresa', Auth::user()->empresa)->whereIn('tipo_contacto', [0, 2])->get();
            $devoluciones = Devoluciones::where('nota', $nota->id)->get();
            $tipos = DB::table('tipos_nota_credito')->get();

            //obtiene las formas de pago relacionadas con este modulo (Facturas)
            $relaciones = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();
            $formasPago = PucMovimiento::where('documento_id',$nota->id)->where('tipo_comprobante',6)->where('enlace_a',4)->get();

            // return $nota->modelDetalle()->factura();

            return view('notascredito.edit')->with(compact(
                'nota',
                'retencionesNotas',
                'retenciones',
                'categorias',
                'items',
                'notasFacturas',
                'facturaContacto',
                'clientes',
                'inventario',
                'impuestos',
                'bancos',
                'bodegas',
                'devoluciones',
                'proveedores',
                'facturas_reg',
                'listas',
                'tipos',
                'formasPago',
                'relaciones'
            ));
        }

        return redirect('empresa/notascredito')->with('success', 'No existe un registro con ese id');
    }

    /**
     * Modificar los datos de la nota de debito
     * @param Request $request
     * @return redirect
     */
    public function update(Request $request, $id)
    {
       
        //Validaciones con respecto a la nueva programacion del cda.
        if ($request->tipo_operacion == 3) {
            if (count($request->item) > 1) {
                $mensaje = "Si el tipo de operación es nota de crédito con detalle de recaudo a terceros; no puedes facturar más de dos productos.";
                return back()->with('error', $mensaje);
            }
            if (DB::table('detalle_terceros')->where('fk_idempresa', auth()->user()->empresa)->where('inventario_id', $request->item[0])->count() == 0) {
                $mensaje = "El producto que deseas facturar no tiene un detalle de recaudo asociado.";
                return back()->with('error', $mensaje);
            }
        }

        if ($request->factura) {
            $total = 0;
            $precioNotas = 0;
            $retencion = 0;
            $impuesto = 0;
            $descuento = 0;
            $z = 1;

            //MULTI IMPUESTOS
            for ($i = 0; $i < count($request->precio); $i++) {
                $total += ($request->cant[$i] * $request->precio[$i]);

                if ($request->desc[$i] != null) {
                    $descuento = ($request->precio[$i] * $request->cant[$i]) * $request->desc[$i] / 100;
                    $total -= $descuento;
                }

                $data  = $request->all();

                if (isset($data['impuesto' . $z])) {
                    if ($data['impuesto' . $z]) {
                        for ($x = 0; $x < count($data['impuesto' . $z]); $x++) {
                            $porcentaje = Impuesto::find($data['impuesto' . $z][$x])->porcentaje;
                            if ($porcentaje > 0) {
                                $impuesto += (($request->cant[$i] * $request->precio[$i] - $descuento) * $porcentaje) / 100;
                            }
                        }
                    }
                }
                //en caso tal de no tener el desarrollo de multiples ivas
                elseif(isset($data['impuesto'])){
                    if ($data['impuesto']) {
                        for ($x = 0; $x < count($data['impuesto']); $x++) {
                            $porcentaje = Impuesto::find($data['impuesto'][$x])->porcentaje;
                            if ($porcentaje > 0) {
                                $impuesto += (($request->cant[$i] * $request->precio[$i] - $descuento) * $porcentaje) / 100;
                            }
                        }
                    }
                }
                $z++;
            }
            $total += $impuesto;
            //MULTI IMPUESTOS

            if ($request->precio_reten) {
                for ($i = 0; $i < count($request->precio_reten); $i++) {
                    $retencion += $request->precio_reten[$i];
                }
            }

            $total = $total - $retencion;
            $fc = Factura::find($request->factura);

            foreach ($fc->notas_credito() as $notas) {
                //Acumulado de total en notas creditos de la factura.
                if ($notas->nota != $id) {
                    $precioNotas += $notas->nota()->total()->total;
                }
            }

            $precioNotas += $total;

            if (round($precioNotas) > round($fc->total()->total)) {
                return redirect()->back()->with('error', "La nota crédito supera el valor de la factura de venta");
            }
        }

        $nota = NotaCredito::where('empresa', Auth::user()->empresa)->where('id', $id)->first();
        $montoFactura = 0;
        $montoRetenciones = 0;
        if ($nota) {
            //Recoloco los items los productos en la bodega
            $items = ItemsNotaCredito::join('inventario as inv', 'inv.id', '=', 'items_notas.producto')
                ->select('items_notas.*')
                ->where('items_notas.nota', $nota->id)
                ->where('inv.tipo_producto', 1)->get();
            $bodega = Bodega::where('empresa', Auth::user()->empresa)
                ->where('status', 1)
                ->where('id', $request->bodega)->first();

            foreach ($items as $item) {
                $ajuste = ProductosBodega::where('empresa', Auth::user()->empresa)
                    ->where('bodega', $bodega->id)
                    ->where('producto', $item->producto)->first();
                if ($ajuste) {
                    $ajuste->nro += $item->cant;
                    $ajuste->save();
                }
            }

            //Coloco el estatus de la factura en abierta
            $facturas_reg = NotaCreditoFactura::where('nota', $nota->id)->get();
            foreach ($facturas_reg as $factura) {
                $dato = $factura->factura();
                $dato->estatus = 1;
                $dato->save();
            }

            //Modifico los datos de la nota
            $nota->cliente       = $request->cliente;
            $nota->tipo          = $request->tipo;
            $nuevaFecha = Carbon::parse($request->fecha)->format('Y-m-d');
            // $nuevaFecha != $nota->fecha ? $nota->tiempo_creacion = now() : '';
            $nota->fecha = $nuevaFecha;
            $nota->observaciones = mb_strtolower($request->observaciones);
            $nota->notas         = $request->notas;
            $nota->lista_precios = $request->lista_precios;
            $nota->bodega        = $request->bodega;
            $nota->tipo_operacion = $request->tipo_operacion;
            $nota->ordencompra    = $request->ordencompra;

            if ($request->tipo_operacion == 3) {
                $impuesto = Impuesto::where('id', $request->impuesto1[0])->first();
                $nota->total_recaudo  = $request->detalle_monto;

                if ($request->desc) {
                    $descuento = $request->desc[0];
                } else {
                    $descuento = 0;
                }

                //Descontamos un posible descuento.
                $nota->total_valor = $this->precision($request->precio[0] * $request->cant[0]) - ($this->precision($request->precio[0]) * $descuento / 100);
                //añadimos los pósibles porcentajes de iva sobre el producto.
                $nota->total_valor += ($this->precision($request->precio[0]) * $request->cant[0] * $impuesto->porcentaje / 100);
                //restamos retenciones
                if ($request->precio_reten) {
                    $totalreten = 0;
                    foreach ($request->precio_reten as $valor => $v) {
                        $totalreten += $v;
                    }
                    $nota->total_valor -= $totalreten;
                } else {
                    $totalreten = 0;
                }
                //Sumamos detalle de recaudo
                $nota->total_valor += $request->detalle_monto;
            }

            $nota->save();
    
            //Compruebo que existe la bodega y la uso
            //$bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
            if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
                $bodega = Bodega::where('empresa', Auth::user()->empresa)->where('status', 1)->first();
            }

            $inner = array();
            //Recorro los Categoría/Ítem
            $z = 1;
            for ($i = 0; $i < count($request->item); $i++) {
                //$impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
                $cat = 'item' . ($i + 1);
                $items = array();
                if ($request->$cat) { //Comprobar que exixte ese id
                    $items = ItemsNotaCredito::where('id', $request->$cat)->first();
                }

                if (!$items) {
                    $items = new ItemsNotaCredito();
                    $items->nota = $nota->id;
                }

                //Comprobar que el nro que se guarda el item
                $producto = Inventario::where('id', $request->item[$i])->first();
                if (!$producto) {
                    continue;
                }
                $items->producto = $producto->id;
                if ($producto->tipo_producto == 1) {
                    //Si el producto es inventariable y existe esa bodega, agregara el valor registrado
                    $ajuste = ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
                    if ($ajuste) {
                        $ajuste->nro -= $request->cant[$i];
                        $ajuste->save();
                    }
                }

                /*INICIO DE CALCULOS PARA SACAR EL SALDO A FAVOR AL CONTACTO*/

                if ($request->desc[$i]) {
                    $descuento = ($request->precio[$i] * $request->cant[$i]) * $request->desc[$i] / 100;
                    $precioItem = ($request->precio[$i] * $request->cant[$i]) - $descuento;

                    $impuestoItem = 0;
                    $tmp = $precioItem + $impuestoItem;

                    $montoFactura += $tmp;
                }

                /*FIN DE CALCULOS*/

                //MULTI IMPUESTOS
                $items->id_impuesto = null;
                $items->impuesto = null;
                // $items->id_impuesto_1 = null;
                // $items->impuesto_1 = null;
                // $items->id_impuesto_2 = null;
                // $items->impuesto_2 = null;
                // $items->id_impuesto_3 = null;
                // $items->impuesto_3 = null;
                // $items->id_impuesto_4 = null;
                // $items->impuesto_4 = null;
                // $items->id_impuesto_5 = null;
                // $items->impuesto_5 = null;
                // $items->id_impuesto_6 = null;
                // $items->impuesto_6 = null;
                // $items->id_impuesto_7 = null;
                // $items->impuesto_7 = null;

                $data  = $request->all();

                if (isset($data['impuesto' . $z])) {
                    if ($data['impuesto' . $z]) {
                        for ($x = 0; $x < count($data['impuesto' . $z]); $x++) {
                            $id_cat = 'id_impuesto_' . $x;
                            $cat = 'impuesto_' . $x;
                            $impuesto = Impuesto::where('id', $data['impuesto' . $z][$x])->first();
                            if ($impuesto) {
                                if ($x == 0) {
                                    $items->id_impuesto = $impuesto->id;
                                    $items->impuesto = $impuesto->porcentaje;
                                } elseif ($x > 0) {
                                    $items->$id_cat = $impuesto->id;
                                    $items->$cat = $impuesto->porcentaje;
                                }
                            }
                        }
                    }
                    //en caso tal de no tener los multiples ivas
                }else if(isset($data['impuesto'])){
                    if ($data['impuesto']) {
                        for ($x = 0; $x < count($data['impuesto']); $x++) {
                            $id_cat = 'id_impuesto_' . $x;
                            $cat = 'impuesto_' . $x;
                            $impuesto = Impuesto::where('id', $data['impuesto'][$x])->first();
                            if ($impuesto) {
                                if ($x == 0) {
                                    $items->id_impuesto = $impuesto->id;
                                    $items->impuesto = $impuesto->porcentaje;
                                } elseif ($x > 0) {
                                    $items->$id_cat = $impuesto->id;
                                    $items->$cat = $impuesto->porcentaje;
                                }
                            }
                        }
                    }
                }
                //MULTI IMPUESTOS

                $montoFactura = $montoFactura + $items->itemCantImpuesto();

                $items->precio = $this->precision($request->precio[$i]);
                $items->descripcion = $request->descripcion[$i];
                $items->cant = $request->cant[$i];
                $items->ref = $request->ref[$i];
                $items->desc = $request->desc[$i];
                $items->save();
                $inner[] = $items->id;
                $z++;
            }

            if ($request->retencion) {
                foreach ($request->retencion as $key => $value) {
                    if ($request->precio_reten[$key]) {
                        $retencion = Retencion::where('id', $request->retencion[$key])->first();
                        $retencionesTmp = NotaRetencion::where('notas', $nota->id)->where('id_retencion', $retencion->id)->count();
                        if ($retencionesTmp > 0) {
                            continue;
                        }
                        $reten = new NotaRetencion();
                        $reten->notas = $nota->id;
                        $reten->tipo = 1; //hace referencia a devoluciones tipo crédito.
                        $reten->valor = $this->precision($request->precio_reten[$key]);
                        $reten->retencion = $retencion->porcentaje;
                        $reten->id_retencion = $retencion->id;
                        $reten->save();
                    }
                    $montoRetenciones += $request->precio_reten[$key];
                }
                $montoFactura = $this->precision($montoFactura) - $this->precision($montoRetenciones);
            } else {
                $nota_retencion = NotaRetencion::where('notas', $nota->id)->get();
                if ($nota_retencion) {
                    NotaRetencion::where('notas', $nota->id)->delete();
                }
            }

            if (count($inner) > 0) {
                ItemsNotaCredito::where('nota', $nota->id)->whereNotIn('id', $inner)->delete();
            }

            //Pregunto si hay facturas asociadas
            $notaFactura = NotaCreditoFactura::where('nota', $nota->id)->get()->last();

            $factura = Factura::find($request->factura);

            //acumulador de notas credito de una factura
            $precioNota = 0;

            foreach ($factura->notas_credito() as $notas) {
                $precioNota += $notas->nota()->total()->total;
            }

            $notaFactura->nota = $nota->id;
            $notaFactura->factura = $factura->id;
            $notaFactura->pago = $this->precision($factura->porpagar());
            $notaFactura->save();
            if ($this->precision($montoFactura) == $this->precision($factura->porpagar())) {
                $factura->estatus = 0;
                $factura->save();
            }

            if ($factura->total()->total == $precioNota) {
                $factura->estatus = 0;
            } elseif ($factura->total()->total == $factura->pagado()) {
                $factura->estatus = 0;
            } else {
                $factura->estatus = 1;
            }

            $factura->save();

            $items->save();

            if ($factura->pagado()) {
                $saldoNota = new NotaSaldo();
                $saldoNota->id_nota = $nota->id;
                $saldoNota->saldo_nota = $factura->pagado();
                $saldoNota->save();

                $cliente = Contacto::find($request->cliente);
                $saldo_contacto = NotaSaldo::where('id_nota', $nota->id)->first();

                if ($saldo_contacto) {
                    $cliente->saldo_favor = $cliente->saldo_favor - $saldo_contacto->saldo_nota;
                    $cliente->save();
                }
                $cliente->saldo_favor = $cliente->saldo_favor + $factura->pagado();
                $cliente->save();

            }
            
            foreach($nota->itemsNota as $item){
                foreach($item->cuentasContable() as $cuentaItem){
                    $nota->modelDetalle()->factura()->formaPagoRequest(1);
                }
            }

            PucMovimiento::notaCredito($nota,2,$request);
            $mensaje = 'Se ha modificado satisfactoriamente la nota de Crédito';
            return redirect('empresa/notascredito')->with('success', $mensaje)->with('nota_id', $nota->id);
        }
    }

    /**
     * FUNCION PARA Eliminar una nota de credito
     */
    public function destroy($id)
    {
        $nota = NotaCredito::where('empresa', Auth::user()->empresa)->where('id', $id)->first();
        if ($nota) {

            //Recoloco los items los productos en la bodega
            $items = ItemsNotaCredito::join('inventario as inv', 'inv.id', '=', 'items_notas.producto')->select('items_notas.*')->where('items_notas.nota', $nota->id)->where('inv.tipo_producto', 1)->get();
            foreach ($items as $item) {
                $ajuste = ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $nota->bodega)->where('producto', $item->producto)->first();
                if ($ajuste) {
                    $ajuste->nro -= $item->cant;
                    $ajuste->save();
                }
            }
            ItemsNotaCredito::where('nota', $nota->id)->delete();
            $nota_retencion = NotaRetencion::where('notas', $nota->id)->get();

            if ($nota_retencion) {
                NotaRetencion::where('notas', $nota->id)->delete();
            }

            $cliente = Contacto::find($nota->cliente);
            $saldo_contacto = NotaSaldo::where('id_nota', $nota->id)->first();

            if ($saldo_contacto) {
                $cliente->saldo_favor = $cliente->saldo_favor - $saldo_contacto->saldo_nota;
                $cliente->save();
                NotaSaldo::where('id_nota', $nota->id)->delete();
            }


            //Coloco el estatus de la factura en abierta
            $facturas_reg = NotaCreditoFactura::where('nota', $nota->id)->get();
            foreach ($facturas_reg as $factura) {
                $dato = $factura->factura();
                $dato->estatus = 1;
                $dato->save();
            }
            NotaCreditoFactura::where('nota', $nota->id)->delete();


            $items = Devoluciones::where('nota', $nota->id)->get();
            foreach ($items as $key => $value) {
                //gastos
                $gasto = Gastos::where('empresa', Auth::user()->empresa)->where('nro_devolucion', $value->id)->first();
                if ($gasto) {
                    $this->destroy_transaccion(3, $gasto->id);
                    $gasto->delete();
                }
            }
            Devoluciones::where('nota', $nota->id)->delete();
            $nota->delete();
            $mensaje = 'Se ha eliminado satisfactoriamente la nota de crédito';
            return back()->with('success', $mensaje);
        }
        return redirect('empresa/notascredito')->with('success', 'No existe un registro con ese id');
    }

    /**
     * Funcion para generar el pdf
     */
    public function Imprimir($id){
        /**
         * toma en cuenta que para ver los mismos
         * datos debemos hacer la misma consulta
         **/
        view()->share(['title' => 'Imprimir Nota de Crédito']);
        $nota = NotaCredito::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($nota) {
            $retenciones = NotaRetencion::where('notas', $nota->id)->get();
            $items = ItemsNotaCredito::where('nota',$nota->id)->get();
            $itemscount = ItemsNotaCredito::where('nota',$nota->id)->count();
            $facturas = NotaCreditoFactura::where('nota',$nota->id)->get();
            
            if($nota->emitida == 1){
                $infoEmpresa = Empresa::find(Auth::user()->empresa);
                $data['Empresa'] = $infoEmpresa->toArray();

                $infoCliente = Contacto::find($nota->cliente);
                $data['Cliente'] = $infoCliente->toArray();
                
                $impTotal = 0;

                foreach ($nota->total()->imp as $totalImp){
                    if(isset($totalImp->total)){
                        $impTotal = $totalImp->total;
                    }
                }
                
                $infoCude = [
                  'Numfac' => $nota->nro,
                  'FecFac' => Carbon::parse($nota->created_at)->format('Y-m-d'),
                  'HorFac' => Carbon::parse($nota->created_at)->format('H:i:s').'-05:00',
                  'ValFac' => number_format($nota->total()->subtotal,2,'.',''),
                  'CodImp' => '01',
                  'ValImp' => number_format($impTotal,2,'.',''),
                  'CodImp2'=> '04',
                  'ValImp2'=> '0.00',
                  'CodImp3'=> '03',
                  'ValImp3'=> '0.00',
                  'ValTot' => number_format($nota->total()->subtotal + $nota->impuestos_totales(), 2, '.', ''),
                  'NitFE'  => $data['Empresa']['nit'],
                  'NumAdq' => $nota->cliente()->nit,
                  'pin'    => 75315,
                  'TipoAmb'=> 2,
              ];

              $CUDE = $infoCude['Numfac'].$infoCude['FecFac'].$infoCude['HorFac'].$infoCude['ValFac'].$infoCude['CodImp'].$infoCude['ValImp'].$infoCude['CodImp2'].$infoCude['ValImp2'].$infoCude['CodImp3'].$infoCude['ValImp3'].$infoCude['ValTot'].$infoCude['NitFE'].$infoCude['NumAdq'].$infoCude['pin'].$infoCude['TipoAmb'];
              $CUDEvr = hash('sha384',$CUDE);
              
              $codqr = "NumFac:" . $nota->codigo . "\n" .
              "NitFac:"  . $data['Empresa']['nit']   . "\n" .
              "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
              "FecFac:" . Carbon::parse($nota->created_at)->format('Y-m-d') .  "\n" .
              "HoraFactura" . Carbon::parse($nota->created_at)->format('H:i:s').'-05:00' . "\n" .
              "ValorFactura:" .  number_format($nota->total()->subtotal, 2, '.', '') . "\n" .
              "ValorIVA:" .  number_format($impTotal, 2, '.', '') . "\n" .
              "ValorOtrosImpuestos:" .  0.00 . "\n" .
              "ValorTotalFactura:" .  number_format($nota->total()->subtotal + $nota->impuestos_totales(), 2, '.', '') . "\n" .
              "CUDE:" . $CUDEvr;
              
              
              
              $pdf = PDF::loadView('pdf.credito', compact('nota', 'items', 'facturas', 'retenciones','itemscount','codqr','CUDEvr'));
              return  response ($pdf->stream())->withHeaders([ 'Content-Type' =>'application/pdf',]);
          }
      else{
        $pdf = PDF::loadView('pdf.credito', compact('nota', 'items', 'facturas', 'retenciones','itemscount'));
        return  response ($pdf->stream())->withHeaders([ 'Content-Type' =>'application/pdf',]);
    }
        }
}

public function items_fact($id){

    $factura = Factura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    $retencionesFacturas = FacturaRetencion::where('factura', $factura->id)->get();
    $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();

    if ($factura) {
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $factura->bodega)->first();
        if (!$bodega) {
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        }
        $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
        ->where('empresa',Auth::user()->empresa)->where('status', 1)
        ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
            //

        $items = ItemsFactura::select('items_factura.*','inventario.producto as nombre')->join('inventario','inventario.id','=','items_factura.producto')->where('factura',$factura->id)->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)
        ->orWhere('empresa', null)
        ->Where('estado', 1)->get();

    }
    return json_encode($items);

}

public function facturas_retenciones($id){
    $retencionesFacturas = FacturaRetencion::where('factura', $id)
    ->join('retenciones','retenciones.id','=','factura_retenciones.id_retencion')->get();
    return json_encode($retencionesFacturas);
}

    /**
     * Funcion para enviar por correo al cliente
     */
    public function enviar($id, $emails=null, $redireccionar=true){
        /**
         * toma en cuenta que para ver los mismos
         * datos debemos hacer la misma consulta
         **/
        $emails=array();
        view()->share(['title' => 'Enviando Nota Crédito']);
        $nota = NotaCredito::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($nota) {
            if (!$emails) {
                $emails[]=$nota->cliente()->email;
                if ($nota->cliente()->asociados(true)>0) {
                    foreach ($nota->cliente()->asociados() as $asociado) {
                        if ($asociado->notificacion==1 && $asociado->email) {
                            $emails[]=$asociado->email;
                        }
                    }
                }
            }

            if (count($emails)==0) {
                if ($redireccionar) {
                    return back()->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
                }
                return false;
            }
            
        
            $total = Funcion::Parsear($nota->total()->total);
            $items = ItemsNotaCredito::where('nota',$nota->id)->get();
            
            $itemscount= $items->count();
            $facturas = NotaCreditoFactura::where('nota',$nota->id)->get();
            $retenciones = FacturaRetencion::join('notas_factura as nf','nf.factura','=','factura_retenciones.factura')
            ->join('retenciones','retenciones.id','=','factura_retenciones.id_retencion')
            ->where('nf.nota',$nota->id)->get();
    
            
            if($nota->emitida == 1){
        
                $infoEmpresa = Empresa::find(Auth::user()->empresa);
                $data['Empresa'] = $infoEmpresa->toArray();

                $infoCliente = Contacto::find($nota->cliente);
                $data['Cliente'] = $infoCliente->toArray();
                
                $impTotal = 0;

                foreach ($nota->total()->imp as $totalImp){
                    if(isset($totalImp->total)){
                        $impTotal = $totalImp->total;
                    }
                }
            
                
                $infoCude = [
                  'Numfac' => $nota->nro,
                  'FecFac' => Carbon::parse($nota->created_at)->format('Y-m-d'),
                  'HorFac' => Carbon::parse($nota->created_at)->format('H:i:s').'-05:00',
                  'ValFac' => number_format($nota->total()->subtotal,2,'.',''),
                  'CodImp' => '01',
                  'ValImp' => number_format($impTotal,2,'.',''),
                  'CodImp2'=> '04',
                  'ValImp2'=> '0.00',
                  'CodImp3'=> '03',
                  'ValImp3'=> '0.00',
                  'ValTot' => number_format($nota->total()->subtotal + $nota->impuestos_totales(), 2, '.', ''),
                  'NitFE'  => $data['Empresa']['nit'],
                  'NumAdq' => $nota->cliente()->nit,
                  'pin'    => 75315,
                  'TipoAmb'=> 2,
              ];

              $CUDE = $infoCude['Numfac'].$infoCude['FecFac'].$infoCude['HorFac'].$infoCude['ValFac'].$infoCude['CodImp'].$infoCude['ValImp'].$infoCude['CodImp2'].$infoCude['ValImp2'].$infoCude['CodImp3'].$infoCude['ValImp3'].$infoCude['ValTot'].$infoCude['NitFE'].$infoCude['NumAdq'].$infoCude['pin'].$infoCude['TipoAmb'];
              $CUDEvr = hash('sha384',$CUDE);
              
              $codqr = "NumFac:" . $nota->codigo . "\n" .
              "NitFac:"  . $data['Empresa']['nit']   . "\n" .
              "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
              "FecFac:" . Carbon::parse($nota->created_at)->format('Y-m-d') .  "\n" .
              "HoraFactura" . Carbon::parse($nota->created_at)->format('H:i:s').'-05:00' . "\n" .
              "ValorFactura:" .  number_format($nota->total()->subtotal, 2, '.', '') . "\n" .
              "ValorIVA:" .  number_format($impTotal, 2, '.', '') . "\n" .
              "ValorOtrosImpuestos:" .  0.00 . "\n" .
              "ValorTotalFactura:" .  number_format($nota->total()->subtotal + $nota->impuestos_totales(), 2, '.', '') . "\n" .
              "CUDE:" . $CUDEvr;
              
              //$pdf = PDF::loadView('pdf.credito', compact('nota', 'items', 'facturas', 'retenciones','itemscount','codqr','CUDEvr'));

              $pdf = PDF::loadView('pdf.credito', compact('nota', 'items', 'facturas', 'retenciones','itemscount','codqr','CUDEvr'))->stream();

    /*..............................
    Construcción del envío de correo electrónico
    ................................*/

    $data = array(
      'email'=> 'info@gestordepartes.net',
  );
    $total = Funcion::Parsear($nota->total()->total);
    $cliente = $nota->cliente()->nombre;
    $xmlPath = 'xml/empresa'.auth()->user()->empresa.'/NC/NC-'.$nota->codigo.'.xml';

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

    self::sendMail('emails.notascredito', compact('nota','total','cliente'), compact('pdf', 'emails','nota','xmlPath'), function($message) use ($pdf, $emails,$nota,$xmlPath)
    {
      $message->attachData($pdf, 'NotaCredito.pdf', ['mime' => 'application/pdf']);
      
      if(file_exists($xmlPath)){
        $message->attach($xmlPath, ['as' => 'NotaCredito.xml', 'mime' => 'text/plain']);
        }    
      
      $message->from('info@gestordepartes.net', Auth::user()->empresa()->nombre);
      $message->to($emails)->subject(Auth::user()->empresa()->nombre . " Nota Crédito Electrónica " . $nota->nro);
  });
          }
      else{
        $pdf = PDF::loadView('pdf.credito', compact('nota', 'items', 'facturas', 'retenciones','itemscount'))->stream();
        
        
            $empresa = Empresa::find($nota->empresa);
            $cliente = $nota->cliente()->nombre;
            $tituloCorreo = "NOTA CREDITO: $nota->nro PROVEEDOR: $empresa->nombre ";
            
             $total = Funcion::Parsear($nota->total()->total);
             $cliente = $nota->cliente()->nombre;

            self::sendMail('emails.notascredito', compact('nota', 'cliente', 'total'), compact('pdf', 'emails', 'nota', 'tituloCorreo'), function($message) use ($pdf, $emails, $nota, $tituloCorreo)
            {
                $message->from(Auth::user()->empresa()->email, Auth::user()->empresa()->nombre);
                $message->to($emails)->subject($tituloCorreo);
                $message->attachData($pdf, 'credito.pdf', ['mime' => 'application/pdf']);
            });
            
    }
        }
        if ($redireccionar) {
            return back()->with('success', 'Se ha enviado el correo');
        }
    }

    #DATATABLE DE LAS NOTAS
    public function datatable_producto(Request $request, $producto){
        // storing  request (ie, get/post) global array to a variable
        $requestData =  $request;
        $columns = array(
            // datatable column index  => database column name
            0 => 'notas_credito.nro',
            1 => 'nombrecliente',
            2 => 'notas_credito.fecha'
        );
        $facturas=NotaCredito::leftjoin('contactos as c', 'notas_credito.cliente', '=', 'c.id')->select('notas_credito.*', DB::raw('c.nombre as nombrecliente'))->where('notas_credito.empresa',Auth::user()->empresa)->whereRaw('notas_credito.id in (Select distinct(nota) from items_notas where producto='.$producto.')');

        if ($requestData->search['value']) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $facturas=$facturas->where(function ($query) use ($requestData) {
                $query->where('notas_credito.nro', 'like', '%'.$requestData->search['value'].'%')
                ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        $totalFiltered=$totalData=$facturas->count();
        $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);


        $facturas=$facturas->get();

        $data = array();
        foreach ($facturas as $factura) {

            $nestedData = array();
            $nestedData[] = '<a href="'.route('notascredito.show',$factura->nro).'">'.$factura->nro.'</a>';
            $nestedData[] = '<a href="'.route('contactos.show',$factura->cliente).'" target="_blanck">'.$factura->nombrecliente.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->por_aplicar());
            $boton = '<a href="'.route('notascredito.show',$factura->nro).'"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
            <a href="'.route('notascredito.imprimir.nombre',['id' => $factura->nro, 'name'=> 'Nota Credito No. '.$factura->nro.'.pdf']).'" target="_blanck" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
            <a href="'.route('notascredito.edit',$factura->nro).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
            <form action="'.route('notascredito.destroy',$factura->id).'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-notascredito'.$factura->id.'">
            '.csrf_field().'
            <input name="_method" type="hidden" value="DELETE">
            </form>
            <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('."'eliminar-notascredito".$factura->id."', '¿Estas seguro que deseas eliminar nota de crédito?', 'Se borrara de forma permanente');".'"><i class="fas fa-times"></i></button>
            ';



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
        // storing  request (ie, get/post) global array to a variable
        $requestData =  $request;
        $columns = array(
            // datatable column index  => database column name
            0 => 'notas_credito.nro',
            1 => 'nombrecliente',
            2 => 'notas_credito.fecha',
            3 => 'total',
            4 => 'por_aplicar'
        );
        $facturas=NotaCredito::leftjoin('contactos as c', 'notas_credito.cliente', '=', 'c.id')
            ->leftjoin('items_notas as if', 'notas_credito.id', '=', 'if.nota')
            ->select('notas_credito.*', DB::raw('c.nombre as nombrecliente'),
                DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                DB::raw('(SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) - if(
        (Select SUM(monto) from notas_devolucion_dinero where nota=notas_credito.id), 
        (Select SUM(monto) from notas_devolucion_dinero where nota=notas_credito.id), 0)) as por_aplicar'))
            ->where('notas_credito.empresa',Auth::user()->empresa)
            ->where('notas_credito.cliente', $contacto)
            ->groupBy('if.nota');


        if ($requestData->search['value']) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $facturas=$facturas->where(function ($query) use ($requestData) {
                $query->where('notas_credito.nro', 'like', '%'.$requestData->search['value'].'%')
                    ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);

        $facturas=$facturas->get();
        $totalFiltered=$totalData=$facturas->count();
        $data = array();
        foreach ($facturas as $factura) {
            $nestedData = array();
            $nestedData[] = '<a href="'.route('notascredito.show',$factura->nro).'">'.$factura->nro.'</a>';
            $nestedData[] = '<a href="'.route('contactos.show',$factura->cliente).'" target="_blanck">'.$factura->nombrecliente.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
            $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->por_aplicar());
            $boton = '<a href="'.route('notascredito.show',$factura->nro).'"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
            <a href="'.route('notascredito.imprimir.nombre',['id' => $factura->nro, 'name'=> 'Nota Credito No. '.$factura->nro.'.pdf']).'" target="_blanck" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
            <a href="'.route('notascredito.edit',$factura->nro).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
            <form action="'.route('notascredito.destroy',$factura->id).'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-notascredito'.$factura->id.'">
            '.csrf_field().'
            <input name="_method" type="hidden" value="DELETE">
            </form>
            <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('."'eliminar-notascredito".$factura->id."', '¿Estas seguro que deseas eliminar nota de crédito?', 'Se borrara de forma permanente');".'"><i class="fas fa-times"></i></button>
            ';


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

    public function xmlNotaCredito($id)
    {
        $NotaCredito = NotaCredito::find($id);

        $ResolucionNumeracion = NumeracionFactura::where('empresa', Auth::user()->empresa)->where('preferida', 1)->where('tipo',2)->first(); 

        $infoEmpresa = Auth::user()->empresaObj;
        $data['Empresa'] = $infoEmpresa->toArray();

        $retenciones = NotaRetencion::select('notas_retenciones.*', 'retenciones.tipo as id_tipo')
            ->join('retenciones', 'retenciones.id', '=', 'notas_retenciones.id_retencion')
            ->where('notas', $NotaCredito->id)
            ->where('retenciones.empresa', Auth::user()->empresa)
            ->get();

        //-------------- Factura Relacionada -----------------------//
        $nroFacturaRelacionada =  NotaCreditoFactura::where('nota', $id)->first()->factura;
        $FacturaRelacionada    = Factura::find($nroFacturaRelacionada) ?? null;

        $impTotal = 0;
        if (isset($FacturaRelacionada)) {
            foreach ($FacturaRelacionada->total()->imp as $totalImp) {
                if (isset($totalImp->total)) {
                    $impTotal = $totalImp->total;
                }
            }
        }

        $decimal = explode(".", $impTotal);
        if (
            isset($decimal[1]) && $decimal[1] >= 50 || isset($decimal[1]) && $decimal[1] == 5 || isset($decimal[1]) && $decimal[1] == 4
            || isset($decimal[1]) && $decimal[1] == 3 || isset($decimal[1]) && $decimal[1] == 2 || isset($decimal[1]) && $decimal[1] == 1
        ) {
            $impTotal = round($impTotal);
        } else {
            $impTotal = round($impTotal);
        }

        $CufeFactRelacionada  = json_decode($this->validateStatusDian($infoEmpresa->nit, $FacturaRelacionada->codigo, "01", $ResolucionNumeracion->prefijo),true); 
        if($CufeFactRelacionada["statusCode"] == 404){
            return redirect('empresa/notascredito')->with('error', 'La factura de la nota crédito no ha sido emitida.');
        }
        $CufeFactRelacionada  = $CufeFactRelacionada["uuid"];
        //--------------Fin Factura Relacionada -----------------------//


        $impTotal = 0;

        foreach ($NotaCredito->total()->imp as $totalImp) {
            if (isset($totalImp->total)) {
                $impTotal = $totalImp->total;
            }
        }

        $decimal = explode(".", $impTotal);
        if (isset($decimal[1])) {
            $impTotal = round($impTotal);
        }

        $items = ItemsNotaCredito::where('nota', $id)->get();

        if (isset($NotaCredito->tiempo_creacion)) {
            if($NotaCredito->tiempo_creacion){
                $horaFac = $NotaCredito->tiempo_creacion;
            }
        } else {
            $horaFac = $NotaCredito->created_at;
        }

        $infoCude = [
            'Numfac' => $NotaCredito->nro,
            'FecFac' => Carbon::parse($NotaCredito->fecha)->format('Y-m-d'),
            'HorFac' => Carbon::parse($horaFac)->format('H:i:s') . '-05:00',
            'ValFac' => number_format($NotaCredito->total()->subtotal - $NotaCredito->total()->descuento, 2, '.', ''),
            'CodImp' => '01',
            'ValImp' => number_format($impTotal, 2, '.', ''),
            'CodImp2' => '04',
            'ValImp2' => '0.00',
            'CodImp3' => '03',
            'ValImp3' => '0.00',
            'ValTot' => number_format($NotaCredito->total()->subtotal + $NotaCredito->impuestos_totales() - $NotaCredito->total()->descuento, 2, '.', ''),
            'NitFE'  => $data['Empresa']['nit'],
            'NumAdq' => $NotaCredito->cliente()->nit,
            'pin'    => 75315,
            'TipoAmb' => 1,
        ];

        $CUDE = $infoCude['Numfac'] . $infoCude['FecFac'] . $infoCude['HorFac'] . $infoCude['ValFac'] . $infoCude['CodImp'] . $infoCude['ValImp'] . $infoCude['CodImp2'] . $infoCude['ValImp2'] . $infoCude['CodImp3'] . $infoCude['ValImp3'] . $infoCude['ValTot'] . $infoCude['NitFE'] . $infoCude['NumAdq'] . $infoCude['pin'] . $infoCude['TipoAmb'];


        $CUDEvr = hash('sha384', $CUDE);

        $infoCliente = Contacto::find($NotaCredito->cliente);
        $data['Cliente'] = $infoCliente->toArray();

        $responsabilidades_empresa = DB::table('empresa_responsabilidad as er')
            ->join('responsabilidades_facturacion as rf', 'rf.id', '=', 'er.id_responsabilidad')
            ->select('rf.*')
            ->where('er.id_empresa', '=', Auth::user()->empresa)->where('er.id_responsabilidad', 5)
            ->orWhere('er.id_responsabilidad', 7)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 12)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 20)->where('er.id_empresa', '=', Auth::user()->empresa)
            ->orWhere('er.id_responsabilidad', 29)->where('er.id_empresa', '=', Auth::user()->empresa)->get();

        $emails = $NotaCredito->cliente()->email;
        if ($NotaCredito->cliente()->asociados('number') > 0) {
            $email = $emails;
            $emails = array();
            if ($email) {
                $emails[] = $email;
            }
            foreach ($NotaCredito->cliente()->asociados() as $asociado) {
                if ($asociado->notificacion == 1 && $asociado->email) {
                    $emails[] = $asociado->email;
                }
            }
        }

        $tituloCorreo =  $data['Empresa']['nit'] . ";" . $data['Empresa']['nombre'] . ";" . $NotaCredito->nro . ";91;" . $data['Empresa']['nombre'];

        if (is_array($emails)) {
            $max = count($emails);
        } else {
            $max = 1;
        }


        if (!$emails || $max == 0) {
            return redirect('empresa/notascredito/' . $NotaCredito->nro)->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
        }

        $isImpuesto = 1;
        // foreach($NotaCredito->total()->imp as $impuesto){
        //     if(isset($impuesto->total)){
        //         $isImpuesto = 1;
        //     }
        // }


        // if(auth()->user()->empresa == 114){
        //     return response()->view('templates.xml.91',compact('CUDEvr','ResolucionNumeracion','NotaCredito', 'data','items','retenciones','FacturaRelacionada','CufeFactRelacionada','responsabilidades_empresa','emails','impTotal','isImpuesto'))
        //      ->header('Cache-Control', 'public')
        //      ->header('Content-Description', 'File Transfer')
        //      ->header('Content-Disposition', 'attachment; filename=NC-'.$NotaCredito->nro.'.xml')
        //      ->header('Content-Transfer-Encoding', 'binary')
        //      ->header('Content-Type', 'text/xml');
        //  }

        $xml = view('templates.xml.91', compact('CUDEvr', 'ResolucionNumeracion', 'NotaCredito', 'data', 'items', 'retenciones', 'FacturaRelacionada', 'CufeFactRelacionada', 'responsabilidades_empresa', 'emails', 'impTotal', 'isImpuesto'));

        //-- Envío de datos a la DIAN --//
        $res = $this->EnviarDatosDian($xml);

        //-- Decodificación de respuesta de la DIAN --//
        $res = json_decode($res, true);

        if (!isset($res['statusCode']) && isset($res['message'])) {
            return redirect('/empresa/notascredito')->with('message_denied', $res['message']);
        }

        $statusCode = $res['statusCode']; //200

        //-- Guardamos la respuesta de la dian cuando sea negativa --//
        if ($statusCode != 200) {
            $NotaCredito->dian_response = $res['statusCode'];
            $NotaCredito->save();
        }

        //-- Validación 1 del status code (Cuando hay un error) --//
        if ($statusCode != 200) {
            $message = $res['errorMessage'];
            $errorReason = $res['errorReason'];
            $statusCode =  $res['statusCode']; 

            //Validamos si depronto la nota crédito fue emitida pero no quedamos con ningun registro de ella.
            $saveNoJson = $statusJson = $this->validateStatusDianNota(auth()->user()->empresaObj->nit, $NotaCredito->nro, "91");

            //Decodificamos repsuesta y la guardamos en la variable status json
            $statusJson = json_decode($statusJson, true);

            if ($statusJson["statusCode"] != 200) {
                //Validamos enviando la solciitud de esta manera, ya que funciona de varios modos
                $res = $saveNoJson = $statusJson = $this->validateStatusDianNota(auth()->user()->empresaObj->nit, $NotaCredito->nro, "91");

                //Decodificamos repsuesta y la guardamos en la variable status json
                $statusJson = json_decode($statusJson, true);
            }

            if ($statusJson["statusCode"] == 200) {
                $message = "Nota crédito emitida correctamente";
                $NotaCredito->emitida = 1;
                $NotaCredito->dian_response = $statusJson["statusCode"];
                $NotaCredito->fecha_expedicion = Carbon::now();
                $NotaCredito->save();

                $this->generateXmlPdfEmail($statusJson['document'], $NotaCredito, $emails, $data, $CUDEvr, $items, $ResolucionNumeracion, $tituloCorreo);
            } else {

                //Si no pasa despues de validar el estado de la dian, probablemente sea por que esta tirando error de "documento procesado anteriormente" así no esté procesado
                //entonces requerimos ir al xml colocar un espacio en el nro y colocar un espacio en el cude.
                $responseConEspacio = $this->reenvioXmlEspacio(
                    $NotaCredito,
                    $impTotal,
                    $data,
                    $ResolucionNumeracion,
                    $items,
                    $retenciones,
                    $FacturaRelacionada,
                    $CufeFactRelacionada,
                    $responsabilidades_empresa,
                    $emails,
                    $isImpuesto
                );

                if (isset($responseConEspacio["statusCode"])) {

                    if ($responseConEspacio["statusCode"] == 200) {
                        $message = "Nota crédito emitida correctamente";
                        $NotaCredito->emitida = 1;
                        $NotaCredito->dian_response = $responseConEspacio['statusCode'];
                        $NotaCredito->notificacion = 1;
                        $NotaCredito->fecha_expedicion = Carbon::now();
                        $NotaCredito->save();

                        $this->generateXmlPdfEmail($responseConEspacio['document'], $NotaCredito, $emails, $data, $CUDEvr, $items, $ResolucionNumeracion, $tituloCorreo);
                    } else {
                        return back()->with('message_denied', $message)->with('errorReason', $errorReason)->with('statusCode', $statusCode);
                    }
                } else {
                    return back()->with('message_denied', $message)->with('errorReason', $errorReason)->with('statusCode', $statusCode);
                }
            }
        }

        //-- estátus de que la factura ha sido aprobada --//
        if ($statusCode == 200) {
            $message = "Nota crédito emitida correctamente";
            $NotaCredito->emitida = 1;
            $NotaCredito->fecha_expedicion = Carbon::now();
            $NotaCredito->save();

            $this->generateXmlPdfEmail($res['document'], $NotaCredito, $emails, $data, $CUDEvr, $items, $ResolucionNumeracion, $tituloCorreo);
        }
        return back()->with('message_success', $message);
    }

    public function reenvioXmlEspacio($NotaCredito, $impTotal, $data, $ResolucionNumeracion, $items, $retenciones, $FacturaRelacionada, $CufeFactRelacionada, $responsabilidades_empresa, $emails, $isImpuesto)
    {
        //Hacemos parche que nos ha servido hasta el momento para este tipo de errores
        $NotaCredito->nro = "  " . $NotaCredito->nro;

        $infoCude = [
            'Numfac' => $NotaCredito->nro,
            'FecFac' => Carbon::parse($NotaCredito->fecha)->format('Y-m-d'),
            'HorFac' => Carbon::parse($NotaCredito->created_at)->format('H:i:s') . '-05:00',
            'ValFac' => number_format($NotaCredito->total()->subtotal - $NotaCredito->total()->descuento, 2, '.', ''),
            'CodImp' => '01',
            'ValImp' => number_format($impTotal, 2, '.', ''),
            'CodImp2' => '04',
            'ValImp2' => '0.00',
            'CodImp3' => '03',
            'ValImp3' => '0.00',
            'ValTot' => number_format($NotaCredito->total()->subtotal + $NotaCredito->impuestos_totales() - $NotaCredito->total()->descuento, 2, '.', ''),
            'NitFE'  => $data['Empresa']['nit'],
            'NumAdq' => $NotaCredito->cliente()->nit,
            'pin'    => 75315,
            'TipoAmb' => 1,
        ];

        $CUDE = $infoCude['Numfac'] . $infoCude['FecFac'] . $infoCude['HorFac'] . $infoCude['ValFac'] . $infoCude['CodImp'] . $infoCude['ValImp'] . $infoCude['CodImp2'] . $infoCude['ValImp2'] . $infoCude['CodImp3'] . $infoCude['ValImp3'] . $infoCude['ValTot'] . $infoCude['NitFE'] . $infoCude['NumAdq'] . $infoCude['pin'] . $infoCude['TipoAmb'];


        $CUDEvr = hash('sha384', $CUDE);

        // if(auth()->user()->empresa == 240){
        //  return response()->view('templates.xml.91',compact('CUDEvr','ResolucionNumeracion','NotaCredito', 'data','items','retenciones','FacturaRelacionada','CufeFactRelacionada','responsabilidades_empresa','emails','impTotal','isImpuesto'))
        //     ->header('Cache-Control', 'public')
        //     ->header('Content-Description', 'File Transfer')
        //     ->header('Content-Disposition', 'attachment; filename=NC-'.$NotaCredito->nro.'.xml')
        //     ->header('Content-Transfer-Encoding', 'binary')
        //     ->header('Content-Type', 'text/xml');
        // }

        $xml = view('templates.xml.91', compact('CUDEvr', 'ResolucionNumeracion', 'NotaCredito', 'data', 'items', 'retenciones', 'FacturaRelacionada', 'CufeFactRelacionada', 'responsabilidades_empresa', 'emails', 'impTotal', 'isImpuesto'));

        $res = $this->EnviarDatosDian($xml);

        $res = json_decode($res, true);

        return $res;
    }

    /**
     * Metodo de generacion de xml,pdf y envio de email de una nota credito dian
     * Consultamos si una factura ya fue emititda y no quedamos con registro de ella, de ser así la guardamos, en bd, generamos el xml y enviamos el correo al cliente.
     */
    public function generateXmlPdfEmail($document, $NotaCredito, $emails, $data, $CUDEvr, $items, $ResolucionNumeracion, $tituloCorreo)
    {

        $empresa = auth()->user()->empresaObj;
        $document = base64_decode($document);

        //-- Generación del archivo .xml mas el lugar donde se va a guardar --//
        $path = public_path() . '/xml/empresa' . auth()->user()->empresa;

        if (!File::exists($path)) {
            File::makeDirectory($path);
            $path = $path . "/NC";
            File::makeDirectory($path);
        } else {
            $path = public_path() . '/xml/empresa' . auth()->user()->empresa . "/NC";
            if (!File::exists($path)) {
                File::makeDirectory($path);
            }
        }

        $namexml = 'NC-' . $NotaCredito->nro . ".xml";
        $ruta_xmlresponse = $path . "/" . $namexml;
        $file = fopen($ruta_xmlresponse, "w");
        fwrite($file, $document . PHP_EOL);
        fclose($file);

        //-- Construccion del pdf a enviar con el código qr + el envío del archivo xml --//
        if ($NotaCredito) {

            /*..............................
        Construcción del código qr a la factura
        ................................*/
            $impuesto = 0;
            foreach ($NotaCredito->total()->imp as $key => $imp) {
                if (isset($imp->total)) {
                    $impuesto = $imp->total;
                }
            }

            $codqr = "NumFac:" . $NotaCredito->codigo . "\n" .
                "NitFac:"  . $data['Empresa']['nit']   . "\n" .
                "DocAdq:" .  $data['Cliente']['nit'] . "\n" .
                "FecFac:" . Carbon::parse($NotaCredito->created_at)->format('Y-m-d') .  "\n" .
                "HoraFactura" . Carbon::parse($NotaCredito->created_at)->format('H:i:s') . '-05:00' . "\n" .
                "ValorFactura:" .  number_format($NotaCredito->total()->subtotal, 2, '.', '') . "\n" .
                "ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
                "ValorOtrosImpuestos:" .  0.00 . "\n" .
                "ValorTotalFactura:" .  number_format($NotaCredito->total()->subtotal + $NotaCredito->impuestos_totales(), 2, '.', '') . "\n" .
                "CUDE:" . $CUDEvr;

            /*..............................
            Construcción del código qr a la factura
            ................................*/

            $itemscount = $items->count();
            $nota = $NotaCredito;
            $facturas = NotaCreditoFactura::where('nota', $nota->id)->get();
            $retenciones = FacturaRetencion::join('notas_factura as nf', 'nf.factura', '=', 'factura_retenciones.factura')
                ->join('retenciones', 'retenciones.id', '=', 'factura_retenciones.id_retencion')
                ->where('nf.nota', $nota->id)->get();

            if ($nota->tipo_operacion == 3) {
                $detalle_recaudo = Factura::where('id', $facturas->first()->factura)->first();
                $nota->placa = $detalle_recaudo->placa;
                $detalle_recaudo = $detalle_recaudo->detalleRecaudo();
                $pdf = PDF::loadView('pdf.creditotercero', compact('nota', 'items', 'facturas', 'itemscount', 'codqr', 'CUDEvr', 'detalle_recaudo'))
                    ->save(public_path() . "/convertidor" . "/NC-" . $nota->nro . ".pdf")->stream();
            } else {
                $pdf = PDF::loadView('pdf.credito', compact('nota', 'items', 'facturas', 'retenciones', 'itemscount', 'codqr', 'CUDEvr', 'empresa'))
                    ->save(public_path() . "/convertidor" . "/NC-" . $nota->nro . ".pdf")->stream();
            }

            /*..............................
            Construcción del envío de correo electrónico
            ................................*/

            $data = array(
                'email' => 'info@gestordepartes.net',
            );
            $totalRecaudo = 0;
            if ($nota->total_recaudo != null) {
                $totalRecaudo = $nota->total_recaudo;
            }
            $total = Funcion::Parsear($nota->total()->total + $totalRecaudo);
            $cliente = $nota->cliente()->nombre;

            //Construccion del archivo zip.
            $zip = new ZipArchive();

            //Después creamos un archivo zip temporal que llamamos miarchivo.zip y que eliminaremos después de descargarlo.
            //Para indicarle que tiene que crearlo ya que no existe utilizamos el valor ZipArchive::CREATE.
            $nombreArchivoZip = "NC-" . $nota->nro . ".zip";

            $zip->open("convertidor/" . $nombreArchivoZip, ZipArchive::CREATE);

            if (!$zip->open($nombreArchivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                return ("Error abriendo ZIP en $nombreArchivoZip");
            }

            $ruta_pdf = public_path() . "/convertidor" . "/NC-" . $nota->nro . ".pdf";

            $zip->addFile($ruta_xmlresponse, "NC-" . $nota->nro . ".xml");
            $zip->addFile($ruta_pdf, "NC-" . $nota->nro . ".pdf");
            $resultado = $zip->close();

            self::sendMail('emails.notascredito', compact('nota', 'total', 'cliente'), compact('pdf', 'emails', 'ruta_xmlresponse', 'nota', 'nombreArchivoZip', 'tituloCorreo'), function ($message) use ($pdf, $emails, $ruta_xmlresponse, $nota, $nombreArchivoZip, $tituloCorreo) {
                $message->attach($nombreArchivoZip, ['as' => $nombreArchivoZip, 'mime' => 'application/octet-stream', 'Content-Transfer-Encoding' => 'Binary']);


                /*Peticiones de clientes que no quieren que se mande la factura de venta por fuera del zip (tal como si es permitido por la DIAN)*/
                if (config('app.name') == "Gestoru" && auth()->user()->empresa != 52) {
                    $message->attachData($pdf, 'NC-' . $nota->nro . '.pdf', ['mime' => 'application/pdf']);
                }

                $message->from('info@gestordepartes.net', Auth::user()->empresaObj->nombre);
                $message->to(array_filter((array) $emails))->subject($tituloCorreo);
            });

            // Si quieres puedes eliminarlo después:
            if (isset($nombreArchivoZip)) {
                unlink($nombreArchivoZip);
                unlink($ruta_pdf);
            }
        }
    }


    public function validateTimeEmicion()
    {
        if (Auth::user()->empresaObj->estado_dian == 1) {
            $pendientes = NotaCredito::where('empresa', auth()->user()->empresa)
                ->where('emitida', 0)
                ->whereDate('fecha', '>=', '2019-11-01')
                ->get();
            return response()->json($pendientes);
        } else {
            return null;
        }
    }

    public function showMovimiento($id){
        $this->getAllPermissions(Auth::user()->id);
        $nota = NotaCredito::find($id);
        /*
        obtenemos los movimiento sque ha tenido este documento
        sabemos que se trata de un tipo de movimiento 03
        */
        $movimientos = PucMovimiento::where('documento_id',$id)->where('tipo_comprobante',6)->get();
        if(count($movimientos) == 0){
            return back()->with('error', 'La nota crédito: ' . $nota->nro . " no tiene un asiento contable.");
        }
        if ($nota) {
            view()->share(['title' => 'Detalle Movimiento ' .$nota->nro]);
            return view('notascredito.show-movimiento')->with(compact('nota','movimientos'));
        }
    }

    public function facturaAsociada(Request $request){
        $factura = Factura::find($request->facturaRelacionada);
        if($factura){
            $factura->total = $factura->total()->total;
            return response()->json(["factura"=>$factura]);
        }
    }


}
