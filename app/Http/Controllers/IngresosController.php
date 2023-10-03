<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Empresa;
use App\Banco; use App\Contacto;
use App\Categoria; use App\Retencion;
use App\Movimiento; use App\Impuesto;
use App\Numeracion;
use App\Model\Inventario\Inventario;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosFactura;
use App\Model\Ingresos\IngresosCategoria;
use App\Model\Ingresos\IngresosRetenciones;
use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosCategoria;
use Carbon\Carbon;  use Mail; use Auth;
use Validator; use Illuminate\Validation\Rule;
use bcrypt; use DB;
use Session;
use Barryvdh\DomPDF\Facade as PDF;
use App\Contrato;
use App\Mikrotik;
use App\User;
use App\CRM;
use App\Campos;
use Config;
use App\ServidorCorreo;
use App\Integracion;
use App\Puc;
use App\PucMovimiento;
use App\Anticipo;
use App\FormaPago;
use App\NumeracionFactura;
use App\Funcion;

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

include_once(app_path() .'/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Shared_ZipArchive;

use App\Producto;

class IngresosController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
        view()->share(['seccion' => 'facturas', 'subseccion' => 'ingresos', 'title' => 'Pagos / Ingresos', 'icon' =>'fas fa-plus']);
    }

    public function indexOLD(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $busqueda=false;
        $campos=array('', 'ingresos.nro', 'nombrecliente', 'detalle', 'ingresos.fecha', 'banco', 'ingresos.estatus', 'monto');
        if (!$request->orderby) {
            $request->orderby=1; $request->order=1;
        }
        $orderby=$campos[$request->orderby];
        $order=$request->order==1?'DESC':'ASC';
        $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();

        $ingresos = Ingreso::leftjoin('contactos as c', 'c.id', '=', 'ingresos.cliente')
        ->leftjoin('ingresos_factura as if', 'if.ingreso', '=', 'ingresos.id')
        ->join('bancos as b', 'b.id', '=', 'ingresos.cuenta')
        ->select('ingresos.*', DB::raw('if(ingresos.tipo=1, group_concat(if.factura), "")
        as detalle'), 'c.nombre as nombrecliente', 'b.nombre as banco',
        DB::raw('
        (if(ingresos.tipo=1,
        (SUM(if.pago)+(Select if(SUM(valor), SUM(valor),0) from ingresos_retenciones where ingreso=ingresos.id)),
        if(ingresos.tipo=3, ingresos.total_debito, ((Select SUM((cant*valor)+(valor*(impuesto/100)*cant)) from ingresos_categoria where ingreso=ingresos.id)-(Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where ingreso=ingresos.id))))
        ) as monto'))
        ->where('ingresos.empresa',Auth::user()->empresa)->groupBy( 'ingresos.id');


        $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
        if ($request->name_1) {
            $busqueda=true; $appends['name_1']=$request->name_1; $ingresos=$ingresos->where('ingresos.nro', 'like', '%' .$request->name_1.'%');
        }
        if ($request->name_2) {
            $busqueda=true; $appends['name_2']=$request->name_2; $ingresos=$ingresos->where('c.nombre', 'like', '%' .$request->name_2.'%');
        }
        if ($request->name_3) {
            $busqueda=true; $appends['name_3']=$request->name_3; $ingresos=$ingresos->where('ingresos.fecha', date('Y-m-d', strtotime($request->name_3)));
        }
        if ($request->name_4) {
            $busqueda=true; $appends['name_4']=$request->name_4; $ingresos=$ingresos->where('ingresos.cuenta', $request->name_4);
        }
        if ($request->name_5) {
            $busqueda=true; $appends['name_5']=$request->name_5; $ingresos=$ingresos->where('ingresos.metodo_pago', $request->name_5);
        }

        $ingresos=$ingresos->OrderBy($orderby, $order)->paginate(25)->appends($appends);
        $metodos_pago = DB::table('metodos_pago')->get();
        return view('ingresos.index')->with(compact('ingresos', 'request', 'busqueda','bancos','metodos_pago'));
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $bancos = Banco::where('empresa', Auth::user()->empresa)->where('estatus', 1)->get();
        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Contacto::where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre','asc')->get() : Contacto::where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre','asc')->get();
        //$clientes = Contacto::where('empresa', auth()->user()->empresa)->orderBy('nombre','asc')->get();
        $metodos = DB::table('metodos_pago')->where('id', '!=', 8)->where('id', '!=', 7)->get();
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 5)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();

        return view('ingresos.indexnew', compact('bancos','clientes','metodos','tabla'));
    }

    public function ingresos(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $empresa = auth()->user()->empresa;
        $modoLectura = auth()->user()->modo_lectura();
        $ingresos = Ingreso::query()
        ->select('ingresos.*','contactos.nombre','contactos.apellido1','contactos.apellido2','bancos.nombre as banco')
        ->leftjoin('ingresos_factura as if', 'if.ingreso', '=', 'ingresos.id')
        ->leftjoin('contactos', 'contactos.id', '=', 'ingresos.cliente')
        ->join('bancos', 'bancos.id', '=', 'ingresos.cuenta');

        if ($request->filtro == true) {
            if($request->numero){
                $ingresos->where(function ($query) use ($request) {
                    $query->orWhere('ingresos.nro', 'like', "%{$request->numero}%");
                });
            }
            if($request->comprobante_pago){
                $ingresos->where(function ($query) use ($request) {
                    $query->orWhere('ingresos.comprobante_pago', 'like', "%{$request->comprobante_pago}%");
                });
            }
            if($request->cliente){
                $ingresos->where(function ($query) use ($request) {
                    $query->orWhere('ingresos.cliente', $request->cliente);
                });
            }
            if($request->banco){
                $ingresos->where(function ($query) use ($request) {
                    $query->orWhere('ingresos.cuenta', $request->banco);
                });
            }
            if($request->metodo){
                $ingresos->where(function ($query) use ($request) {
                    $query->orWhere('ingresos.metodo_pago', $request->metodo);
                });
            }
            if($request->fecha){
                $ingresos->where(function ($query) use ($request) {
                    $query->orWhere('ingresos.fecha', $request->fecha);
                });
            }
            if($request->estado){
                $ingresos->where(function ($query) use ($request) {
                    $query->orWhere('ingresos.estatus', $request->estado);
                });
            }
        }

        $ingresos->where('ingresos.empresa', $empresa)->groupBy('ingresos.id');

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $ingresos->where('contactos.oficina', auth()->user()->oficina);
            }
        }

        return datatables()->eloquent($ingresos)
            ->editColumn('nro', function (Ingreso $ingreso) {
                return isset($ingreso->nro) ? "<a href=" . route('ingresos.show', $ingreso->id) . ">{$ingreso->nro}</div></a>" : '';
            })
            ->editColumn('comprobante_pago', function (Ingreso $ingreso) {
                return isset($ingreso->comprobante_pago) ? "<a href=" . route('ingresos.show', $ingreso->id) . ">{$ingreso->comprobante_pago}</div></a>" : '';
            })
            ->editColumn('cliente', function (Ingreso $ingreso) {
                return isset($ingreso->nombre) ? "<a href=" . route('contactos.show', $ingreso->cliente) . ">{$ingreso->nombre} {$ingreso->apellido1} {$ingreso->apellido2}</div></a>" : auth()->user()->empresa()->nombre;
            })
            ->addColumn('detalle', function (Ingreso $ingreso) {
                return $ingreso->detalle();
            })
            ->editColumn('fecha', function (Ingreso $ingreso) {
                return date('d-m-Y', strtotime($ingreso->fecha));
            })
            ->editColumn('cuenta', function (Ingreso $ingreso) {
                return  $ingreso->banco ?? '';
            })
            ->addColumn('estado', function (Ingreso $ingreso) {
                return $ingreso->estatus();
            })
            ->addColumn('monto', function (Ingreso $ingreso) {
                return auth()->user()->empresa()->moneda . " {$ingreso->parsear($ingreso->pago())}";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "ingresos.acciones-ingresos")
            ->rawColumns(['nro', 'cliente', 'comprobante_pago', 'acciones'])
            ->toJson();
    }

    public function create($cliente=false, $factura=false, $banco=false){
        $this->getAllPermissions(Auth::user()->id);

        $pers = $cliente;
        $bank = $banco;
        dd($pers);
        view()->share(['icon' =>'', 'title' => 'Nuevo Ingreso', 'subseccion' => 'ingresos']);

        if ($cliente && !$factura) {
            $banco=$cliente; $cliente=false;
        }
        $numero = (Ingreso::where('empresa', Auth::user()->empresa)->get());
        if (count($numero)>0){
            $numero = ($numero->last())->nro+1;
        }else{
            $numero = 1;
        }
        $contrato = false;
        if($cliente){
            $contrato = Contrato::where('client_id',$cliente)->first();
        }

        //$bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
        (Auth::user()->cuenta > 0) ? $bancos = Banco::where('empresa',Auth::user()->empresa)->whereIn('id',[Auth::user()->cuenta,Auth::user()->cuenta_1,Auth::user()->cuenta_2,Auth::user()->cuenta_3,Auth::user()->cuenta_4])->where('estatus',1)->get() : $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus',1)->get();
        $clientes = (Auth::user()->empresa()->oficina) ? Contacto::where('status', 1)->whereIn('tipo_contacto',[0,2])->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre','asc')->get() : Contacto::where('status', 1)->whereIn('tipo_contacto',[0,2])->where('empresa', Auth::user()->empresa)->orderBy('nombre','asc')->get();
        //$clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->where('status', 1)->get();
        $metodos_pago =DB::table('metodos_pago')->whereIn('id',[1,2,3,4,5,6,9])->orderby('orden','asc')->get();
        $inventario = Inventario::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
         //Tomar las categorias del puc que no son transaccionables.
         $categorias = Puc::where('empresa',auth()->user()->empresa)
         ->whereRaw('length(codigo) > 6')
         ->get();

        //obtiene los anticipos relacionados con este modulo (Ingresos)
        $anticipos = Anticipo::where('relacion',1)->orWhere('relacion',3)->get();

        //tomamos las formas de pago cuando no es un recibo de caja por anticipo
        $formas = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();
        dd($clientes);
        //obtiene las formas de pago relacionadas con este modulo (Facturas)
        $relaciones = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();

        return view('ingresos.create')->with(compact('contrato','clientes', 'inventario', 'cliente', 'factura',
        'bancos', 'metodos_pago', 'impuestos',
        'retenciones',  'banco', 'numero','pers','bank','categorias','anticipos','formas','relaciones'));
    }

    public function saldoContacto($id){
        $cliente = Contacto::find($id);
        $contrato = Contrato::where('client_id',$id)->first();
        if($cliente->saldo_favor == null){
            $saldo = 0;
        }else{
            $saldo = $cliente->saldo_favor;
        }
        return json_encode(['saldo' => $saldo, 'contrato' => $contrato->opciones_dian]);
    }

    public function pendiente($cliente, $id=false){
        $this->getAllPermissions(Auth::user()->id);
        $facturas = Factura::where('cliente', $cliente)->where('empresa',Auth::user()->empresa)->where('estatus', 1);
        $facturas = $facturas->orderBy('created_at', 'desc')->take(30)->get();
        $contrato = Contrato::where('client_id',$cliente)->first();
        //$total = Factura::where('cliente', $cliente)->where('empresa',Auth::user()->empresa)->where('tipo','!=',2)->where('estatus', 1)->count();
        $total = 1;

        return view('ingresos.pendiente')->with(compact('facturas', 'id', 'total','contrato'));
    }

    public function ingpendiente($cliente, $id=false){
        $this->getAllPermissions(Auth::user()->id);
        $facturas=Factura::where('cliente', $cliente)->where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
        $entro=false;
        $retencioness = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        $items = IngresosFactura::where('ingreso',$ingreso->id)->get();
        $new=$facturas;
        $contrato = Contrato::where('client_id',$cliente)->first();

        //obtiene las formas de pago relacionadas con este modulo (Facturas)
        $relaciones = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();
        $formasPago = PucMovimiento::where('documento_id',$ingreso->id)->where('tipo_comprobante',1)->whereIn('enlace_a',[4,5])->get();

        foreach ($items as $item) {
            foreach ($facturas as $factura) {
                if ($factura->id==$item->factura) {
                    $entro=true;
                }
            }
            if (!$entro) {
                $new[]=Factura::where('id', $item->factura)->first();
            }
            $entro=false;
        }

        foreach($facturas as $factura){
            foreach($factura->recibosAnticipo(1) as $recibo){
                // dd($recibo->saldoFavorUsado());
            }

        }
        return view('ingresos.ingpendiente')->with(compact('facturas', 'id', 'items',
        'ingreso', 'retencioness','contrato','formasPago','relaciones'
    ));
    }

    public function store(Request $request){
        // return $request;
        //el tipo 2 significa que estoy realizando un ingreso para darle un anticipo a un cliente
        if($request->realizar == 2){
            //Cuando se realiza el ingreso por categoría.
            $this->storeIngresoPucCategoria($request);

            $mensaje='SE HA CREADO SATISFACTORIAMENTE EL PAGO';
            return redirect('empresa/ingresos')->with('success', $mensaje);
        }else{
            if(isset($request->comprobante_pago)){
                if(Ingreso::where('comprobante_pago', $request->comprobante_pago)->count() > 0){
                    return back()->withInput()->with('danger', 'DISCULPE, EL NRO DE COMPROBANTE DE PAGO INGRESADO YA HA SIDO REGISTRADO');
                }
            }

            if(auth()->user()->rol == 8){
                $monto_pagar = 0;
                foreach ($request->factura_pendiente as $key => $value) {
                    if ($request->precio[$key]) {
                        $monto_pagar += $request->precio[$key];
                    }
                }

                if($monto_pagar > auth()->user()->saldo){
                    $mensaje='NO POSEE SALDO DISPONIBLE PARA CANCELAR LA FACTURA, LO INVITAMOS A REALIZAR UNA RECARGA';
                    return back()->with('danger', $mensaje)->withInput();
                }
            }

            //Si es tipo 1, osea coversion de factura estandar a electrónica con emisión
            if ($request->tipo == 1) {

                foreach ($request->factura_pendiente as $key => $value) {

                    $factura = Factura::find($request->factura_pendiente[$key]);
                    if($factura->estatus == 0){
                        $mensaje='DISCULPE ESTÁ INTENTANDO PAGAR UNA FACTURA YA PAGADA. (FACTURA N° '.$factura->codigo.')';
                        return back()->with('danger', $mensaje)->withInput();
                    }

                    //Conversión de factura estandar a factura electrónica.
                    if(isset($request->tipo_electronica)){
                        //primero recuperamos
                        $nro=NumeracionFactura::where('empresa',1)->where('preferida',1)->where('estado',1)->where('tipo',2)->first();
                        $inicio = $nro->inicio;

                        if($factura->tipo != 2 && $request->precio[$key] > 0)
                        {
                            $factura->tipo = 2;
                            $factura->codigo = $nro->prefijo.$inicio;
                            $factura->numeracion = $nro->id;
                            $factura->fecha =  Carbon::now()->format('Y-m-d');
                            if($factura->vencimiento < Carbon::now()->format('Y-m-d')){
                                $factura->vencimiento = Carbon::now()->format('Y-m-d');
                            }
                            $factura->save();

                            $nro->inicio += 1;
                            $nro->save();
                        }
                    }
                }

                if($request->tipo_electronica == 2){
                    foreach ($request->factura_pendiente as $key => $value) {
                        $factura = Factura::find($request->factura_pendiente[$key]);
                        //si tiene el tipo 2 es por que desean emitir la(s) factura(s).
                        if($factura->emitida != 1){
                            $emision = app(FacturasController::class)->xmlFacturaVentaMasivo($factura->id);
                        }
                    }
                }
            }

            if (Ingreso::where('empresa', auth()->user()->empresa)->count() > 0) {
                Session::put('posttimer', Ingreso::where('empresa', auth()->user()->empresa)->get()->last()->created_at);
                $sw = 1;

                foreach (Session::get('posttimer') as $key) {
                    if ($sw == 1) {
                        $ultimoingreso = $key;
                        $sw = 0;
                    }
                }

                if(isset($ultimoingreso)){
                    $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);

                    if ($diasDiferencia <= 10) {
                        $mensaje='EL PAGO NO HA SIDO PROCESADO, INTÉNTELO NUEVAMENTE';
                        return back()->with('danger', $mensaje)->withInput();
                    }
                }
            }

            $request->validate([
                'cuenta' => 'required|numeric'
            ]);

            $nro = Numeracion::where('empresa', Auth::user()->empresa)->first();
            $caja = $nro->caja;

            while (true) {
                $numero = Ingreso::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
                if ($numero == 0) {
                    break;
                }
                $caja++;
            }

            $ingreso = new Ingreso;
            $ingreso->nro = $caja;
            $ingreso->empresa = Auth::user()->empresa;
            $ingreso->cliente = $request->cliente;
            $ingreso->cuenta = $request->cuenta;
            $ingreso->metodo_pago = $request->metodo_pago;
            $ingreso->notas = $request->notas;
            $ingreso->tipo = $request->tipo;
            $ingreso->fecha = Carbon::parse($request->fecha)->format('Y-m-d');
            $ingreso->observaciones = mb_strtolower($request->observaciones);
            $ingreso->created_by = Auth::user()->id;
            $ingreso->anticipo = $request->saldofavor > 0 ? '1' : ''; // variables que me indican si se trata de un anticipo
            $ingreso->valor_anticipo = $request->saldofavor > 0 ? $request->saldofavor : ''; //variables que me indican si se trata de un anticipo
            $ingreso->comprobante_pago = $request->comprobante_pago;
            $ingreso->save();

            //Si el tipo de ingreso es de facturas
            if ($ingreso->tipo == 1) {
                $saldoFavorUsado = 0;
                foreach ($request->factura_pendiente as $key => $value) {
                    if ($request->precio[$key]) {
                        $precio = $this->precision($request->precio[$key]);
                        $factura = Factura::find($request->factura_pendiente[$key]);

                        /*
                        vamos a sumar el total del anticipo usado sobre una factura
                        (este se aplica cuando se crea la factura de venta en una forma de pago)
                        */
                        $saldoFavorUsado+=$factura->saldoFavorUsado();

                        $retencion = 'fact' . $factura->id . '_retencion';
                        $precio_reten = 'fact' . $factura->id . '_precio_reten';
                        if ($request->$retencion) {
                            foreach ($request->$retencion as $key2 => $value2) {
                                if ($request->$precio_reten[$key2]) {
                                    $retencion = Retencion::where('id', $value2)->first();
                                    $items = new IngresosRetenciones;
                                    $items->ingreso = $ingreso->id;
                                    $items->factura = $factura->id;
                                    $items->valor = $this->precision($request->$precio_reten[$key2]);
                                    $precio += $this->precision($request->$precio_reten[$key2]);
                                    $items->retencion = $retencion->porcentaje;
                                    $items->id_retencion = $retencion->id;
                                    $items->save();
                                }
                            }
                        }

                        $items = new IngresosFactura;
                        $items->ingreso = $ingreso->id;
                        $items->factura = $factura->id;
                        $items->pagado = $factura->pagado();
                        $items->puc_factura = $factura->cuenta_id;
                        $items->puc_banco = $request->saldofavor > 0 ? $request->forma_pago : $request->forma_pago;
                        $items->anticipo = $request->saldofavor > 0 ? $request->anticipo_factura : null;

                        /*
                        Validacion cuando se recibe un valor mayor a la factura. entonces guardamos
                        sobre el total de la factura por que el resto es saldo a favor.
                        */
                        if($factura->total()->total < $request->precio[$key]){
                            $items->pago = $factura->total()->total;
                            $factura->estatus = 0;
                            $factura->save();
                        }else{
                            $items->pago=$this->precision($request->precio[$key]);
                        }

                        if ($this->precision($precio) == $this->precision($factura->porpagar())) {
                            $factura->estatus = 0;
                            $factura->save();

                            CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->delete();

                            $crms = CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->get();
                            foreach ($crms as $crm) {
                                $crm->delete();
                            }
                        }

                        $items->save();
                    }
                }
            } else { //Si el tipo de ingreso es de categorias
                foreach ($request->categoria as $key => $value) {
                    if ($request->precio_categoria[$key]) {
                        $impuesto = Impuesto::where('id', $request->impuesto_categoria[$key])->first();
                        if (!$impuesto) {
                            $impuesto = Impuesto::where('id', 0)->first();
                        }

                        $items = new IngresosCategoria;
                        $items->valor = $this->precision($request->precio_categoria[$key]);
                        $items->id_impuesto = $request->impuesto_categoria[$key];
                        $items->ingreso = $ingreso->id;
                        $items->categoria = $request->categoria[$key];
                        $items->cant = $request->cant_categoria[$key];
                        $items->descripcion = $request->descripcion_categoria[$key];
                        $items->impuesto = $impuesto->porcentaje;
                        $items->save();
                    }
                }
                if ($request->retencion) {
                    foreach ($request->retencion as $key => $value) {
                        if ($request->precio_reten[$key]) {
                            $retencion = Retencion::where('id', $request->retencion[$key])->first();
                            $items = new IngresosRetenciones;
                            $items->ingreso = $ingreso->id;
                            $items->valor = $this->precision($request->precio_reten[$key]);
                            $items->retencion = $retencion->porcentaje;
                            $items->id_retencion = $retencion->id;
                            $items->save();
                        }
                    }
                }
            }

            //registramos el saldo a favor que se generó al pagar la factura
            if($request->saldofavor > 0){
                $contacto = Contacto::find($request->cliente);
                $contacto->saldo_favor = $contacto->saldo_favor+$request->saldofavor;
                $contacto->save();

                $ingreso->puc_banco = $request->forma_pago; //cuenta de forma de pago genérico del ingreso. (en memoria)
                $ingreso->anticipo = $request->anticipo_factura; //cuenta de anticipo genérico del ingreso. (en memoria)

                $ingreso->saldoFavorIngreso = $request->saldofavor; //Variable en memoria, no creada.
                PucMovimiento::ingreso($ingreso,1,1,$request);
            }else{
                $ingreso->puc_banco = $request->forma_pago; //cuenta de forma de pago genérico del ingreso. (en memoria)
                PucMovimiento::ingreso($ingreso,1,2,$request);
            }

            //sumo a las numeraciones el recibo
            $nro->caja = $caja + 1;
            $nro->save();

            //Registro el Movimiento
            $ingreso = Ingreso::find($ingreso->id);
            //ingresos
            $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);

            //Necesitamos obtener el valor que usamos de saldo a favor para descontarlo del banco, ya que se guardó. (obtener todo el total)
            if($saldoFavorUsado > 0){
                //la cuenta de anticipo es la 6
                $this->up_transaccion(6, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 2, $saldoFavorUsado, $ingreso->fecha, $ingreso->descripcion);
            }

            if ($ingreso->tipo == 1) {
                if($factura->estatus == 0){
                    $cliente = Contacto::where('id', $request->cliente)->first();
                    $contrato = Contrato::where('id',$factura->contrato_id)->first();
                    if($contrato){
                        $contrato->state = "enabled";
                        $contrato->save();
                    }
                    if(!$contrato){
                        $contrato = Contrato::where('client_id', $cliente->id)->first();
                        if($contrato){
                            $contrato->state = "enabled";
                            $contrato->save();
                        }
                    }

                    // return $contrato;

                    /* * * API MK * * */

                    if($contrato){
                        $asignacion = Producto::where('contrato', $contrato->id)->where('venta', 1)->where('status', 2)->where('cuotas_pendientes', '>', 0)->get()->last();

                        if ($asignacion) {
                            $cuotas_pendientes = $asignacion->cuotas_pendientes -= 1;
                            $asignacion->cuotas_pendientes = $cuotas_pendientes;
                            if ($cuotas_pendientes == 0) {
                                $asignacion->status = 1;
                            }
                            $asignacion->save();
                        }

                        if($contrato->server_configuration_id){
                            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

                            $API = new RouterosAPI();
                            $API->port = $mikrotik->puerto_api;

                            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                                $API->write('/ip/firewall/address-list/print', TRUE);
                                $ARRAYS = $API->read();

                                #ELIMINAMOS DE MOROSOS#
                                $API->write('/ip/firewall/address-list/print', false);
                                $API->write('?address='.$contrato->ip, false);
                                $API->write("?list=morosos",false);
                                $API->write('=.proplist=.id');
                                $ARRAYS = $API->read();

                                if(count($ARRAYS)>0){
                                    $API->write('/ip/firewall/address-list/remove', false);
                                    $API->write('=.id='.$ARRAYS[0]['.id']);
                                    $READ = $API->read();
                                }
                                #ELIMINAMOS DE MOROSOS#

                                #AGREGAMOS A IP_AUTORIZADAS#
                                $API->comm("/ip/firewall/address-list/add", array(
                                    "address" => $contrato->ip,
                                    "list" => 'ips_autorizadas'
                                    )
                                );
                                #AGREGAMOS A IP_AUTORIZADAS#

                                $API->disconnect();

                                $contrato->state = 'enabled';
                                $contrato->save();
                            }
                        }
                    }

                    /* * * API MK * * */

                    /* * * ENVÍO SMS * * */
                    $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('status', 1)->first();
                    if($servicio){
                        $numero = str_replace('+','',$cliente->celular);
                        $numero = str_replace(' ','',$numero);
                        $mensaje = "Estimado Cliente, le informamos que hemos recibido el pago de su factura por valor de ".$factura->parsear($precio)." gracias por preferirnos. ".Auth::user()->empresa()->slogan;
                        if($servicio->nombre == 'Hablame SMS'){
                            if($servicio->api_key && $servicio->user && $servicio->pass){
                                $post['toNumber'] = $numero;
                                $post['sms'] = $mensaje;

                                $curl = curl_init();
                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/marketing',
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'POST',CURLOPT_POSTFIELDS => json_encode($post),
                                    CURLOPT_HTTPHEADER => array(
                                        'account: '.$servicio->user,
                                        'apiKey: '.$servicio->api_key,
                                        'token: '.$servicio->pass,
                                        'Content-Type: application/json'
                                    ),
                                ));
                                $result = curl_exec ($curl);
                                $err  = curl_error($curl);
                                curl_close($curl);
                            }
                        }elseif($servicio->nombre == 'SmsEasySms'){
                            if($servicio->user && $servicio->pass){
                                $post['to'] = array('57'.$numero);
                                $post['text'] = $mensaje;
                                $post['from'] = "SMS";
                                $login = $servicio->user;
                                $password = $servicio->pass;

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, "https://sms.istsas.com/Api/rest/message");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                                curl_setopt($ch, CURLOPT_HTTPHEADER,
                                    array(
                                        "Accept: application/json",
                                        "Authorization: Basic ".base64_encode($login.":".$password)));
                                $result = curl_exec ($ch);
                                $err  = curl_error($ch);
                                curl_close($ch);
                            }
                        }else{
                            if($servicio->user && $servicio->pass){
                                $post['to'] = array('57'.$numero);
                                $post['text'] = $mensaje;
                                $post['from'] = "";
                                $login = $servicio->user;
                                $password = $servicio->pass;

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, "https://masivos.colombiared.com.co/Api/rest/message");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_POST, 1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                                curl_setopt($ch, CURLOPT_HTTPHEADER,
                                    array(
                                        "Accept: application/json",
                                        "Authorization: Basic ".base64_encode($login.":".$password)));
                                $result = curl_exec ($ch);
                                $err  = curl_error($ch);
                                curl_close($ch);
                            }
                        }
                    }
                    /* * * ENVÍO SMS * * */
                }
            }

            if(auth()->user()->rol == 8){
                $user = User::find(auth()->user()->id);
                $user->ganancia += 900;
                $user->saldo -= $monto_pagar;
                $user->save();
            }

            if($request->cant_facturas > 1){
                $nro = Numeracion::where('empresa', Auth::user()->empresa)->first();
                $caja = $nro->caja;

                while (true) {
                    $numero = Ingreso::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
                    if ($numero == 0) {
                        break;
                    }
                    $caja++;
                }

                $ingreso = new Ingreso;
                $ingreso->nro = $caja;
                $ingreso->empresa = Auth::user()->empresa;
                $ingreso->cliente = $request->cliente;
                $ingreso->cuenta = $request->cuenta;
                $ingreso->metodo_pago = $request->metodo_pago;
                $ingreso->notas = $request->notas;
                $ingreso->tipo = 2;
                $ingreso->fecha = Carbon::parse($request->fecha)->format('Y-m-d');
                $ingreso->observaciones = 'Ingreso por concepto de reconexión';
                $ingreso->created_by = Auth::user()->id;
                $ingreso->save();

                $items = new IngresosCategoria;
                $items->valor = $this->precision(10000);
                $items->id_impuesto = 2;
                $items->ingreso = $ingreso->id;
                $items->categoria = 56;
                $items->cant = 1;
                $items->descripcion = 'Ingreso por concepto de reconexión';
                $items->impuesto = 0;
                $items->save();

                //sumo a las numeraciones el recibo
                $nro->caja = $caja + 1;
                $nro->save();

                //Registro el Movimiento
                $ingreso = Ingreso::find($ingreso->id);
                //ingresos
                $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, 'Ingreso por concepto de reconexión');

                $facturas = Factura::where('cliente', $ingreso->cliente)->where('estatus', 1)->get();
                if ($facturas) {
                    foreach ($facturas as $factura) {
                        $factura->estatus = 0;
                        $factura->save();
                    }
                }
            }

            $tirilla = false;
            if ($request->tirilla) {
                $tirilla = true;
            }

            ### ADJUNTO DE PAGO ###

            $xmax = 1080; $ymax = 720;
            if($request->file('adjunto_pago')){
                $ext_permitidas = array('image/jpeg','image/png','image/gif');
                $file = $request->file('adjunto_pago');
                $nombre =  'adjunto_pago_'.$ingreso->nro.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $ingreso->adjunto_pago = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                        break;
                    }
                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }

                $ingreso->save();
            }

            $mensaje = 'SE HA CREADO SATISFACTORIAMENTE EL PAGO';
            return redirect('empresa/ingresos/'.$ingreso->id)->with('success', $mensaje)->with('factura_id', $ingreso->id)->with('tirilla', $tirilla);
        }
    }

    public function storeIngresoPucCategoria($request){

        $nro = Numeracion::where('empresa', Auth::user()->empresa)->first();
            $caja = $nro->caja;

        while (true) {
            $numero = Ingreso::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
            if ($numero == 0) {
                break;
            }
            $caja++;
        }

        //sumo a las numeraciones el recibo
        $nro->caja = $caja + 1;
        $nro->save();

        $ingreso = new Ingreso;
        $ingreso->nro = $caja;
        $ingreso->empresa = Auth::user()->empresa;
        $ingreso->cliente = $request->cliente;
        $ingreso->cuenta = $request->cuenta;
        $ingreso->metodo_pago = $request->metodo_pago;
        $ingreso->notas = $request->notas;
        $ingreso->tipo = 2;
        $ingreso->fecha = Carbon::parse($request->fecha)->format('Y-m-d');
        $ingreso->observaciones = mb_strtolower($request->observaciones);
        $ingreso->created_by = Auth::user()->id;
        $ingreso->anticipo = 1;
        $ingreso->valor_anticipo = $request->valor_recibido;
        $ingreso->save();

        $impuesto = Impuesto::where('porcentaje',0)->first();

        //Registramos el ingreso de anticipo en una sola cuenta del puc.
        $items = new IngresosCategoria;
        $items->valor = $this->precision($request->valor_recibido);
        $items->id_impuesto = $impuesto->id;
        $items->impuesto = $impuesto->porcentaje;
        $items->ingreso = $ingreso->id;
        $items->categoria = $request->puc;
        $items->anticipo = $request->anticipo; //hace referencia a la pk de la tabla anticipo
        $items->cant = 1;
        $items->save();

        $contacto = Contacto::find($request->cliente);
        $contacto->saldo_favor+=$request->valor_recibido;
        $contacto->save();

        //ingresos
        $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, 'Ingreso por concepto de reconexión');

        //mandamos por parametro el ingreso y el 1 (guardar)
        PucMovimiento::ingreso($ingreso,1,0);
    }

    public function updateIngresoPucCategoria($request,$id){

        //sumo a las numeraciones el recibo
        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        // dd($ingreso,$request);
        $ingreso->empresa = Auth::user()->empresa;
        $ingreso->cliente = $request->cliente;
        $ingreso->cuenta = $request->cuenta;
        $ingreso->metodo_pago = $request->metodo_pago;
        $ingreso->notas = $request->notas;
        $ingreso->tipo = 2;
        $ingreso->fecha = Carbon::parse($request->fecha)->format('Y-m-d');
        $ingreso->observaciones = mb_strtolower($request->observaciones);
        $ingreso->created_by = Auth::user()->id;
        $ingreso->anticipo = 1;
        $ingreso->valor_anticipo = $request->valor_recibido;
        $ingreso->save();

        $impuesto = Impuesto::where('porcentaje',0)->first();

        //Registramos el ingreso de anticipo en una sola cuenta del puc.
        $items = IngresosCategoria::where('ingreso',$ingreso->id)->get();
        // dd($items);
        foreach($items as $item){
        $item->valor = $this->precision($request->valor_recibido);
        $item->id_impuesto = $impuesto->id;
        $item->impuesto = $impuesto->porcentaje;
        $item->ingreso = $ingreso->id;
        $item->categoria = $request->puc;
        $item->anticipo = $request->anticipo; //hace referencia a la pk de la tabla anticipo
        $item->cant = 1;
        $item->save();
        }

        $contacto = Contacto::find($request->cliente);
        $contacto->saldo_favor+=$request->valor_recibido;
        $contacto->save();

        //ingresos
        $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);

        //mandamos por parametro el ingreso y el 1 (guardar)
        PucMovimiento::ingreso($ingreso,2,0);
    }

    public function showMovimiento($id){
        $this->getAllPermissions(Auth::user()->id);
        $ingreso = Ingreso::find($id);
        /*
        obtenemos los movimiento sque ha tenido este documento
        sabemos que se trata de un tipo de movimiento 03
        */
        $movimientos = PucMovimiento::where('documento_id',$id)->where('tipo_comprobante',1)->get();
        if ($ingreso) {
            view()->share(['title' => 'Detalle Movimiento ' .$ingreso->codigo]);
            return view('ingresos.show-movimiento')->with(compact('ingreso','movimientos'));
        }
        return redirect('empresa/ingresos')->with('success', 'No existe un registro con ese id');
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($ingreso) {
            if ($ingreso->tipo==1) {
                $titulo='Pago a facturas de venta';
                $items = IngresosFactura::where('ingreso',$ingreso->id)->get();
            }else if($ingreso->tipo==3){
                $titulo=$ingreso->detalle(true);
            }else{
                $titulo='Ingreso';
                $items = IngresosCategoria::where('ingreso',$ingreso->id)->get();
            }
            view()->share(['icon' =>'', 'title' => $titulo, 'middel'=>true]);
            $retenciones = IngresosRetenciones::where('ingreso',$ingreso->id)->get();
            $print = false;
            return view('ingresos.show')->with(compact('ingreso', 'items', 'retenciones', 'print'));
        }
        return redirect('empresa/ingresos')->with('error', 'No existe un registro con ese id');
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();

        //tomamos las formas de pago cuando no es un recibo de caja por anticipo
        $formas = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();

        $formasPago = PucMovimiento::where('documento_id',$ingreso->id)->where('tipo_comprobante',1)->whereIn('enlace_a',[4,5])->get();

        if ($ingreso) {
            view()->share(['icon' =>'', 'title' => 'Modificar Ingreso (Recibo de Caja) #'.$ingreso->nro]);
            if ($ingreso->tipo==3) {
                return redirect('empresa/ingresos')->with('error', 'No puede editar un pago de nota de débito');
            }
            if ($ingreso->tipo==4) {
                return redirect('empresa/ingresos')->with('error', 'No puede editar una transferencia');
            }
            $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
            $clientes = (Auth::user()->empresa()->oficina) ? Contacto::where('status', 1)->whereIn('tipo_contacto',[0,2])->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre','asc')->get() : Contacto::where('status', 1)->whereIn('tipo_contacto',[0,2])->where('empresa', Auth::user()->empresa)->orderBy('nombre','asc')->get();
            $metodos_pago =DB::table('metodos_pago')->get();
            $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
            $categorias = Puc::where('empresa',auth()->user()->empresa)
            ->whereRaw('length(codigo) > 6')
            ->get();
            $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
            $items= $retencionesIngreso=array();
            $items = IngresosFactura::where('ingreso',$ingreso->id);

            if($ingreso->tipo==2){
                $items = IngresosCategoria::where('ingreso',$ingreso->id);
                $retencionesIngreso = IngresosRetenciones::where('ingreso',$ingreso->id)->get();
            }

            $cuentaIngresoDinero = false;
            $cuentaAnticipo = false;
            $valorAnticipo = false;
            if($ingreso->anticipo == 1){
                $itemAnticipo = $items->first();
                $cuentaIngresoDinero = $itemAnticipo->categoria; //hace referencia a la pk de la tabla puc
                $cuentaAnticipo = $itemAnticipo->anticipo; //hace referencia a la pk de la tabla anticipo
                $valorAnticipo = round($itemAnticipo->valor);
            }

            $items = $items->get();

            //obtiene los anticipos relacionados con este modulo (Ingresos)
            $anticipos = Anticipo::where('relacion',1)->orWhere('relacion',3)->get();

            //obtiene las formas de pago relacionadas con este modulo (Facturas)
            $relaciones = FormaPago::where('relacion',1)->orWhere('relacion',3)->get();


            return view('ingresos.edit')->with(compact('ingreso', 'items', 'clientes', 'retencionesIngreso',
            'categorias', 'bancos', 'metodos_pago', 'impuestos','items', 'retenciones','formasPago','anticipos','formas'
            ,'cuentaIngresoDinero','cuentaAnticipo','valorAnticipo','relaciones'));
        }
        return redirect('empresa/ingresos')->with('error', 'No existe un registro con ese id');
    }

    public function update(Request $request, $id){
        //el tipo 2 significa que estoy realizando un ingreso para darle un anticipo a un cliente
        if($request->realizar == 2){

            //Cuando se realiza el ingreso por categoría.
            $this->updateIngresoPucCategoria($request,$id);
            $mensaje='SE HA ACTUALIZADO SATISFACTORIAMENTE EL ANTICIPO';
            return redirect('empresa/ingresos')->with('success', $mensaje);

        }

        //pendiente metodo de actualizar un ingreso por categorias, (en elos movimeintos del puc)

        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();

        if ($ingreso) {
            if ($ingreso->tipo==3) {
                return redirect('empresa/ingresos')->with('error', 'No puede editar un pago de nota de débito');
            }
            $request->validate([
                'cuenta' => 'required|numeric'
            ]);

            //Si se cambia de tipo se elimina todos
            if ($ingreso->tipo!=$request->tipo) {
                if ($ingreso->tipo==1) {
                    DB::table('factura')->where('empresa',Auth::user()->empresa)->whereRaw('id in (Select id from ingresos_factura where ingreso=?)', [$ingreso->id])->update(['estatus'=>1]);
                    IngresosFactura::where('ingreso',$ingreso->id)->delete();
                }else{
                    IngresosCategoria::where('ingreso',$ingreso->id)->delete();
                }
                IngresosRetenciones::where('ingreso',$ingreso->id)->delete();
            }


            $ingreso->cliente=$request->cliente;
            $ingreso->cuenta=$request->cuenta;
            $ingreso->metodo_pago=$request->metodo_pago;
            $ingreso->notas=$request->notas;
            $ingreso->tipo=$request->tipo;
            $ingreso->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $ingreso->observaciones=mb_strtolower($request->observaciones);
            $ingreso->updated_by = Auth::user()->id;
            $ingreso->save();

            //Si el tipo de ingreso es de facturas de venta
            if ($ingreso->tipo==1) {
                foreach ($request->factura_pendiente as $key => $value) {
                    $factura = Factura::find($request->factura_pendiente[$key]);
                    $items = IngresosFactura::where('ingreso',$ingreso->id)->where('factura', $factura->id)->first();
                    $porpagar=$factura->porpagar();
                    if ($request->precio[$key]) {
                        if (!$items) {
                            $items = new IngresosFactura;
                            $items->factura=$request->factura_pendiente[$key];
                            $items->pagado=$factura->pagado();
                            $items->ingreso=$ingreso->id;
                        }else{
                            $porpagar+=$this->precision($items->pago);
                        }
                        $items->pago=$this->precision($request->precio[$key]);
                        $items->save();
                        $precio=$this->precision($request->precio[$key]);
                        $retencion='fact'.$factura->id.'_retencion';
                        $precio_reten='fact'.$factura->id.'_precio_reten';
                        $cont=0; $fact=0;
                        if ($request->$retencion) {
                            $inner=array();
                            foreach ($request->$retencion as $key2 => $value2) {
                                if ($request->$precio_reten[$key2]) {
                                    $retencion = Retencion::where('id', $value2)->first();
                                    $cont+=1;
                                    $id='fact'.$factura->id.'_nro_'.$cont;
                                    if ($request->$id) {
                                        $items = IngresosRetenciones::where('id', $request->$id)->first();
                                    }else{
                                        $items = new IngresosRetenciones;
                                    }
                                    $inner[]=$items->id;
                                    $items->ingreso=$ingreso->id;
                                    $items->factura=$factura->id;
                                    $items->valor=$this->precision($request->$precio_reten[$key2]);
                                    $precio+=$this->precision( $request->$precio_reten[$key2]);
                                    $items->retencion=$retencion->porcentaje;
                                    $items->id_retencion=$retencion->id;
                                    $items->save();
                                }
                            }
                            if (count($inner)>0) {
                                DB::table('ingresos_retenciones')->where('ingreso', $ingreso->id)->where('factura', $factura->id)->whereNotIn('id', $inner)->delete();
                            }
                        }else{
                            DB::table('ingresos_retenciones')->where('ingreso', $ingreso->id)->where('factura', $factura->id)->delete();
                        }
                        if ($this->precision($factura->pagado())==$this->precision($factura->total()->total)) {
                            $factura->estatus=0;
                        }else{
                            $factura->estatus=1;
                        }
                        $factura->save();
                    }else{
                        if($items){
                            $items->delete();
                            $factura->estatus=1;
                            $factura->save();
                        }
                    }
                }
            }else{ //Ingresos por categorias
                $retencionesIngreso = IngresosRetenciones::where('ingreso',$ingreso->id)->get();
                $inner=array();
                foreach ($request->categoria as $key => $value) {
                    if ($request->precio_categoria[$key]) {
                        $cat='id_cate'.($key+1);
                        if($request->$cat){
                            $items = IngresosCategoria::where('id', $request->$cat)->first();
                        }else{
                            $items = new IngresosCategoria;
                        }
                        $impuesto = Impuesto::where('id', $request->impuesto_categoria[$key])->first();
                        if (!$impuesto) {
                            $impuesto = Impuesto::where('id', 0)->first();
                        }
                        $items->valor=$request->precio_categoria[$key];
                        $items->id_impuesto=$request->impuesto_categoria[$key];
                        $items->ingreso=$ingreso->id;
                        $items->categoria=$request->categoria[$key];
                        $items->cant=$request->cant_categoria[$key];
                        $items->descripcion=$request->descripcion_categoria[$key];
                        $items->impuesto=$impuesto->porcentaje;
                        $items->save();
                        $inner[]=$items->id;
                    }
                }
                if (count($inner)>0) {
                    DB::table('ingresos_categoria')->where('ingreso', $ingreso->id)->whereNotIn('id', $inner)->delete();
                }
                $inner=array();
                if ($request->retencion) {
                    foreach ($request->retencion as $key => $value) {
                        if ($request->precio_reten[$key]) {
                            $cat='reten'.($key+1);
                            if($request->$cat){
                                $items = IngresosRetenciones::where('id', $request->$cat)->first();
                            }else{
                                $items = new IngresosRetenciones;
                            }
                            $retencion = Retencion::where('id', $request->retencion[$key])->first();
                            $items->ingreso=$ingreso->id;
                            $items->valor=$request->precio_reten[$key];
                            $items->retencion=$retencion->porcentaje;
                            $items->id_retencion=$retencion->id;
                            $items->save();
                            $inner[]=$items->id;
                        }
                    }
                    if (count($inner)>0) {
                        DB::table('ingresos_retenciones')->where('ingreso', $ingreso->id)->whereNotIn('id', $inner)->delete();
                    }
                }else{
                    DB::table('ingresos_retenciones')->where('ingreso', $ingreso->id)->delete();
                }
            }

            //registramos el saldo a favor que se generó al pagar la factura
            if($request->saldofavor > 0){
                $contacto = Contacto::find($request->cliente);
                $contacto->saldo_favor = $contacto->saldo_favor+$request->saldofavor;
                $contacto->save();

                $ingreso->puc_banco = $request->forma_pago; //cuenta de forma de pago genérico del ingreso. (en memoria)
                $ingreso->anticipo = $request->anticipo_factura; //cuenta de anticipo genérico del ingreso. (en memoria)

                $ingreso->saldoFavorIngreso = $request->saldofavor; //Variable en memoria, no creada.
                PucMovimiento::ingreso($ingreso,2,1,$request);
            }else{
                $ingreso->puc_banco = $request->forma_pago; //cuenta de forma de pago genérico del ingreso. (en memoria)
                PucMovimiento::ingreso($ingreso,2,2,$request);
            }

            //ingresos
            $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);

            $mensaje='Se ha modificado satisfactoriamente el ingreso';
            return redirect('empresa/ingresos')->with('success', $mensaje)->with('ingreso_id', $ingreso->id);
        }
        return redirect('empresa/ingresos')->with('error', 'No existe un registro con ese id');
    }

    public function Imprimir($id){
        view()->share(['title' => 'Imprimir Ingreso']);
        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($ingreso) {
            if ($ingreso->tipo==1) {
                $itemscount=IngresosFactura::where('ingreso',$ingreso->id)->count();
                $items = IngresosFactura::where('ingreso',$ingreso->id)->get();
            }else if ($ingreso->tipo==2){
                $itemscount=IngresosCategoria::where('ingreso',$ingreso->id)->count();
                $items = IngresosCategoria::where('ingreso',$ingreso->id)->get();
            }else{
                $itemscount=1;
                $items = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->get();
            }
            $retenciones = IngresosRetenciones::where('ingreso',$ingreso->id)->get();
            $empresa = Empresa::find($ingreso->empresa);
            $pdf = PDF::loadView('pdf.ingreso', compact('ingreso', 'items', 'retenciones', 'itemscount','empresa'));
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
        }
    }

    public function imprimirTirilla($id, $tipo='original'){
        view()->share(['title' => 'Imprimir Ingreso']);
        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($ingreso) {
            if ($ingreso->tipo==1) {
                $itemscount=IngresosFactura::where('ingreso',$ingreso->id)->count();
                $items = IngresosFactura::join('items_factura as itf','itf.factura','ingresos_factura.factura')->select('itf.*')->where('ingreso',$ingreso->id)->get();
            }else if ($ingreso->tipo==2){
                $itemscount=IngresosCategoria::where('ingreso',$ingreso->id)->count();
                $items = IngresosCategoria::where('ingreso',$ingreso->id)->get();
            }else{
                $itemscount=1;
                $items = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->get();
            }
            $retenciones = IngresosRetenciones::where('ingreso',$ingreso->id)->get();
            $resolucion = NumeracionFactura::where('empresa', Auth::user()->empresa)
            ->where('num_equivalente', 0)->where('nomina',0)->where('tipo',2)->where('preferida', 1)->first();
            $empresa = Empresa::find($ingreso->empresa);
            $paper_size = array(0,0,270,580);
            $pdf = PDF::loadView('pdf.plantillas.ingreso_tirilla', compact('ingreso', 'items', 'retenciones',
             'itemscount','empresa', 'resolucion'));
            $pdf->setPaper($paper_size, 'portrait');
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf']);
        }
    }

    public function enviar($id, $emails=null, $redireccionar=true){
        view()->share(['title' => 'Enviando Recibo de Caja']);
        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($ingreso) {
            if (!$emails) {
                $emails[]=$ingreso->cliente()->email;
                if ($ingreso->cliente()->asociados('number')>0) {
                    $email=$emails;
                    foreach ($ingreso->cliente()->asociados() as $asociado) {
                        if ($asociado->notificacion==1 && $asociado->email) {
                            $emails[]=$asociado->email;
                        }
                    }
                }
            }
            if (!$emails || count($emails)==0) {
                return redirect('empresa/ingresos/'.$ingreso->nro)->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
            }

            if ($ingreso->tipo==1) {
                $itemscount=IngresosFactura::where('ingreso',$ingreso->id)->count();
                $items = IngresosFactura::where('ingreso',$ingreso->id)->get();
            }else{
                $itemscount=IngresosCategoria::where('ingreso',$ingreso->id)->count();
                $items = IngresosCategoria::where('ingreso',$ingreso->id)->get();
            }

            $pdf = PDF::loadView('pdf.ingreso', compact('ingreso', 'items', 'retenciones', 'itemscount'))->stream();
            $asunto = "Recibo de Caja # $ingreso->nro";

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

            self::sendMail('emails.ingreso', compact('ingreso'), compact('pdf', 'emails', 'ingreso', 'asunto'), function($message) use ($pdf, $emails, $ingreso, $asunto){
                $message->from(Auth::user()->empresa()->email, Auth::user()->empresa()->nombre);
                $message->to($emails)->subject($asunto);
                $message->attachData($pdf, 'recibo.pdf', ['mime' => 'application/pdf']);
            });
        }

        if ($redireccionar) {
            return redirect('empresa/ingresos/'.$ingreso->id)->with('success', 'Se ha enviado el correo');
        }
    }

    public function anular($id){
        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($ingreso) {
            $ingreso->updated_by = Auth::user()->id;
            if ($ingreso->tipo==3) {
                return redirect('empresa/pagos')->with('error', 'No puede editar un ingreso de nota de débito');
            }
            if ($ingreso->tipo==4) {
                return redirect('empresa/pagos')->with('error', 'No puede editar una transferencia');
            }
            if ($ingreso->estatus==1) {
                $ingreso->estatus=2;
                $mensaje='Se ha anulado satisfactoriamente el pago';
            }else{
                if ($ingreso->tipo==1) {
                    $items = IngresosFactura::where('ingreso',$ingreso->id)->get();
                    foreach ($items as $item) {
                        $factura= $item->factura();
                        if ($factura->porpagar()<$item->pago) {
                            return back()->with('error', 'El monto es mayor que lo que falta por pagar en la venta')->with('ingreso_id', $ingreso->id);
                        }
                    }
                }
                $ingreso->estatus=1;
                $mensaje='Se ha abierto satisfactoriamente el pago';
            }
            $ingreso->save();

            if ($ingreso->tipo==1) {
                $items=ingresosFactura::where('ingreso',$ingreso->id)->get();
                foreach ($items as $item) {
                    $factura= $item->factura();
                    if ($this->precision($factura->porpagar())<=0) {
                        $factura->estatus=0;
                    }else{
                        $factura->estatus=1;
                    }
                    $factura->save();
                }
            }

            $this->chage_status_transaccion(1, $ingreso->id, $ingreso->estatus);
            return back()->with('success', $mensaje)->with('ingreso_id', $ingreso->id);
        }
        return back()->with('error', 'No existe un registro con ese id');
    }

    public function destroy($id){
        $ingreso = Ingreso::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($ingreso) {
            if ($ingreso->tipo==3) {
                return redirect('empresa/pagos')->with('error', 'No puede editar un pago de nota de débito');
            }else if ($ingreso->tipo==1) {
                if ($ingreso->estatus!=2) {
                    $ids=DB::table('ingresos_factura')->where('ingreso', $ingreso->id)->select('factura', 'pago')->get();
                    $factura=array();
                    foreach ($ids as $id) {
                        $factura[]=$id->factura;
                    }
                    DB::table('factura')->where('empresa',Auth::user()->empresa)->whereIn('id', $factura)->update(['estatus'=>1]);
                }
                IngresosFactura::where('ingreso', $ingreso->id)->delete();
                //ingresos
                $this->destroy_transaccion(1, $ingreso->id);
            }else if ($ingreso->tipo==2){
                IngresosCategoria::where('ingreso', $ingreso->id)->delete();
                //ingresos
                $this->destroy_transaccion(1, $ingreso->id);
            }else if($ingreso->tipo==4){
                IngresosCategoria::where('ingreso', $ingreso->id)->delete();
                $mov1=Movimiento::where('modulo', 1)->where('id_modulo', $ingreso->id)->first();
                if ($mov1) {
                    $gasto=Gastos::where('id', $mov1->id_modulo)->first();
                    if ($gasto) {
                        GastosCategoria::where('gasto', $gasto->id)->delete();
                        $gasto->delete();
                    }
                    Movimiento::where('transferencia', $mov1->id)->delete();
                    $mov1->delete();
                }
            }

            DB::table('ingresos_retenciones')->where('ingreso', $ingreso->id)->delete();
            $ingreso->delete();

            $mensaje='Se ha eliminado satisfactoriamente el ingreso';
            //return redirect('empresa/ingresos')->with('success', $mensaje);
            return back()->with('success', $mensaje);
        }
        return redirect('empresa/ingresos')->with('error', 'No existe un registro con ese id');
    }

    public function efecty(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Carga de Archivos Efecty', 'icon' => 'fas fa-cloud-upload-alt']);

        return view('ingresos.efecty');
    }

    public function efecty_xlsx(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Carga de Archivos Efecty XLSX', 'icon' => 'fas fa-cloud-upload-alt']);

        return view('ingresos.efecty_xlsx');
    }

    public function efecty_store(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $request->validate([
            'archivo_efecty' => 'required'
        ]);

        if($request->archivo_efecty){
            $registros = [];
            $mensaje = '';
            $gestor = fopen($request->archivo_efecty, "r"); # Modo r, read
            if (!$gestor) {
                return back()->with('danger','ERROR: ALGO HA FALLADO EN LA CARGA DEL ARCHIVO, INTENTE NUEVAMENTE');
            }
            $tamanio_bufer = 400; # bytes
            while (($lectura = fgets($gestor, $tamanio_bufer)) != false) {
                $lectura = explode("|", $lectura);
                if($lectura[0] == '01' || $lectura[0] == '03'){}else{
                    array_push($registros, $lectura);
                }
            }
            if (!feof($gestor)) {
                return back()->with('danger','ERROR: ALGO HA FALLADO EN LA APERTURA Y LECTURA DEL ARCHIVO, INTENTE NUEVAMENTE');
            }
            fclose($gestor);

            foreach ($registros as $registro) {
                $codigo = substr($registro['8'],1,-3);
                $precio = $this->precision($registro['2']);
                $nit = substr($registro['1'], 1, -1);
                $factura = Factura::where('factura.codigo', $codigo)->first();
                if($factura){
                    if($factura->estatus == 0){
                        $mensaje .= 'FACTURA N° '.$factura->codigo.' YA SE ENCUENTRA PAGADA<br>';
                    }elseif($factura->estatus == 1){
                        $nro = Numeracion::where('empresa', Auth::user()->empresa)->first();
                        $caja = $nro->caja;

                        while (true) {
                            $numero = Ingreso::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
                            if ($numero == 0) {
                                break;
                            }
                            $caja++;
                        }

                        $banco = Banco::where('empresa',Auth::user()->empresa)->where('nombre', 'EFECTY')->first();

                        $ingreso              = new Ingreso;
                        $ingreso->nro         = $caja;
                        $ingreso->empresa     = Auth::user()->empresa;
                        $ingreso->cliente     = $factura->cliente;
                        $ingreso->cuenta      = $banco->id;
                        $ingreso->metodo_pago = 1;
                        $ingreso->notas       = 'Pago Realizado por Carga de Archivo';
                        $ingreso->tipo        = 1;
                        $ingreso->fecha       = Carbon::parse($request->fecha)->format('Y-m-d');
                        $ingreso->created_by  = Auth::user()->id;
                        $ingreso->save();

                        $items                = new IngresosFactura;
                        $items->ingreso       = $ingreso->id;
                        $items->factura       = $factura->id;
                        $items->pagado        = $factura->pagado();
                        $items->pago          = $precio;
                        $items->save();

                        if ($precio >= $factura->porpagar()) {
                            $factura->estatus = 0;
                            $factura->save();
                            CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->delete();
                            $crms = CRM::where('cliente', $factura->cliente)->whereIn('estado', [0,2,3,6])->get();
                            foreach ($crms as $crm) {
                                $crm->delete();
                            }
                        }

                        ##SUMO A LAS NUMERACIONES EL RECIBO
                        $nro->caja = $caja + 1;
                        $nro->save();

                        ##REGISTRO EL MOVIMIENTO
                        //$ingreso = Ingreso::find($ingreso->id);
                        $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);

                        if ($factura->estatus == 0) {
                            $cliente = Contacto::where('id', $factura->cliente)->first();
                            $contrato = Contrato::where('client_id', $cliente->id)->first();
                            $res = DB::table('contracts')->where('client_id',$cliente->id)->update(["state" => 'enabled']);

                            /* * * API MK * * */
                            if($contrato && $contrato->server_configuration_id){
                                $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

                                $API = new RouterosAPI();
                                $API->port = $mikrotik->puerto_api;

                                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                                    $API->write('/ip/firewall/address-list/print', TRUE);
                                    $ARRAYS = $API->read();

                                    #ELIMINAMOS DE MOROSOS#
                                    $API->write('/ip/firewall/address-list/print', false);
                                    $API->write('?address='.$contrato->ip, false);
                                    $API->write("?list=morosos",false);
                                    $API->write('=.proplist=.id');
                                    $ARRAYS = $API->read();

                                    if(count($ARRAYS)>0){
                                        $API->write('/ip/firewall/address-list/remove', false);
                                        $API->write('=.id='.$ARRAYS[0]['.id']);
                                        $READ = $API->read();
                                    }
                                    #ELIMINAMOS DE MOROSOS#

                                    #AGREGAMOS A IP_AUTORIZADAS#
                                    $API->comm("/ip/firewall/address-list/add", array(
                                        "address" => $contrato->ip,
                                        "list" => 'ips_autorizadas'
                                        )
                                    );
                                    #AGREGAMOS A IP_AUTORIZADAS#

                                    $API->disconnect();

                                    $contrato->state = 'enabled';
                                    $contrato->save();
                                }
                            }
                            /* * * API MK * * */

                            /* * * ENVÍO SMS * * */
                            $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'SMS')->where('status', 1)->first();
                            if($servicio){
                                $numero = str_replace('+','',$cliente->celular);
                                $numero = str_replace(' ','',$numero);
                                $mensaje = "Estimado Cliente, le informamos que hemos recibido el pago de su factura por valor de ".$factura->parsear($precio)." gracias por preferirnos. ".Auth::user()->empresa()->slogan;
                                if($servicio->nombre == 'Hablame SMS'){
                                    if($servicio->api_key && $servicio->user && $servicio->pass){
                                        $post['toNumber'] = $numero;
                                        $post['sms'] = $mensaje;

                                        $curl = curl_init();
                                        curl_setopt_array($curl, array(
                                            CURLOPT_URL => 'https://api103.hablame.co/api/sms/v3/send/marketing',
                                            CURLOPT_RETURNTRANSFER => true,
                                            CURLOPT_ENCODING => '',
                                            CURLOPT_MAXREDIRS => 10,
                                            CURLOPT_TIMEOUT => 0,
                                            CURLOPT_FOLLOWLOCATION => true,
                                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                            CURLOPT_CUSTOMREQUEST => 'POST',CURLOPT_POSTFIELDS => json_encode($post),
                                            CURLOPT_HTTPHEADER => array(
                                                'account: '.$servicio->user,
                                                'apiKey: '.$servicio->api_key,
                                                'token: '.$servicio->pass,
                                                'Content-Type: application/json'
                                            ),
                                        ));
                                        $result = curl_exec ($curl);
                                        $err  = curl_error($curl);
                                        curl_close($curl);
                                    }
                                }elseif($servicio->nombre == 'SmsEasySms'){
                                    if($servicio->user && $servicio->pass){
                                        $post['to'] = array('57'.$numero);
                                        $post['text'] = $mensaje;
                                        $post['from'] = "SMS";
                                        $login = $servicio->user;
                                        $password = $servicio->pass;

                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, "https://sms.istsas.com/Api/rest/message");
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($ch, CURLOPT_POST, 1);
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                                        curl_setopt($ch, CURLOPT_HTTPHEADER,
                                            array(
                                                "Accept: application/json",
                                                "Authorization: Basic ".base64_encode($login.":".$password)));
                                        $result = curl_exec ($ch);
                                        $err  = curl_error($ch);
                                        curl_close($ch);
                                    }
                                }else{
                                    if($servicio->user && $servicio->pass){
                                        $post['to'] = array('57'.$numero);
                                        $post['text'] = $mensaje;
                                        $post['from'] = "";
                                        $login = $servicio->user;
                                        $password = $servicio->pass;

                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, "https://masivos.colombiared.com.co/Api/rest/message");
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($ch, CURLOPT_POST, 1);
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                                        curl_setopt($ch, CURLOPT_HTTPHEADER,
                                            array(
                                                "Accept: application/json",
                                                "Authorization: Basic ".base64_encode($login.":".$password)));
                                        $result = curl_exec ($ch);
                                        $err  = curl_error($ch);
                                        curl_close($ch);
                                    }
                                }
                            }
                            /* * * ENVÍO SMS * * */

                            $mensaje .= 'La factura '.$factura->codigo.' del cliente '.$cliente->nombre.' '.$cliente->apellidos().' ha sido pagada bajo el ingreso Nro.'.$ingreso->nro.' por '.Auth::user()->empresa()->moneda.''.Funcion::Parsear($precio).'<br>';
                        }
                    }
                }else{
                    $mensaje .= '(FACTURA N° '.$codigo.') NO ENCONTRADA<br>';
                }
            }
            return back()->with('success', $mensaje);
        }else{
            return back()->with('danger','ERROR: EL ARCHIVO NO HA PODIDO SER CARGADO A LA PLATAFORMA, INTENTE NUEVAMENTE');
        }
    }

    //metodo que calcula que recibos de caja tiene un anticipo para poder cruzar en una forma de pago.
    public function recibosAnticipo(Request $request){

        //obtenemos los ingresos que tiene un anticpo vigente.
        if(!isset($request->recibo) || $request->recibo == 0){
            $ingresos = Ingreso::where('cliente',$request->cliente)
            ->where('anticipo',1)
            ->where('valor_anticipo','>',0)
            ->get();
        }else{
            $ingresos = [];
        }


        return response()->json($ingresos);
    }

    public function efecty_store_xlsx(Request $request){
        $request->validate([
            'archivo_efecty' => 'required|mimes:xlsx',
        ],[
            'archivo_efecty.mimes' => 'El archivo debe ser de extensión xlsx'
        ]);

        $create=0;
        $modf=0;
        $mensaje = '';
        $imagen = $request->file('archivo_efecty');
        $nombre_imagen = 'archivo_efecty.'.$imagen->getClientOriginalExtension();
        $path = public_path() .'/images/Empresas/Empresa'.Auth::user()->empresa;
        $imagen->move($path,$nombre_imagen);
        Ini_set ('max_execution_time', 500);
        $fileWithPath=$path."/".$nombre_imagen;
        //Identificando el tipo de archivo
        $inputFileType = PHPExcel_IOFactory::identify($fileWithPath);
        //Creando el lector.
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        //Cargando al lector de excel el archivo, le pasamos la ubicacion
        $objPHPExcel = $objReader->load($fileWithPath);
        //obtengo la hoja 0
        $sheet = $objPHPExcel->getSheet(0);
        //obtiene el tamaño de filas
        $highestRow = $sheet->getHighestRow();
        //obtiene el tamaño de columnas
        $highestColumn = $sheet->getHighestColumn();

        for ($row = 2; $row <= $highestRow; $row++){
            $request= (object) array();
            //obtengo el A4 desde donde empieza la data
            $nit=$sheet->getCell("B".$row)->getValue();
            if (empty($nit)) {
                break;
            }

            $request->monto=$sheet->getCell("C".$row)->getValue();
            $request->fecha=$sheet->getCell("D".$row)->getValue();
            $error=(object) array();

            if (!$request->monto) {
                $error->monto="EL CAMPO MONTO ES OBLIGATORIO";
            }
            if (!$request->fecha) {
                $error->monto="EL CAMPO FECHA ES OBLIGATORIO";
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

        for ($row = 2; $row <= $highestRow; $row++){
            $request        = (object) array();
            $request->nit   = $sheet->getCell("B".$row)->getValue();
            $request->monto = $sheet->getCell("C".$row)->getValue() / 10000;
            $request->fecha = date('Y-m-d');

            $cliente = Contacto::where('nit', $request->nit)->where('status', 1)->first();
            if($cliente){
                $factura = Factura::where('cliente',$cliente->id)->where('empresa',Auth::user()->empresa)->where('estatus', 1)->get()->last();

                if($factura){
                    $nro = Numeracion::where('empresa', Auth::user()->empresa)->first();
                    $caja = $nro->caja;

                    while (true) {
                        $numero = Ingreso::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
                        if ($numero == 0) {
                            break;
                        }
                        $caja++;
                    }

                    $banco = Banco::where('empresa',Auth::user()->empresa)->where('nombre', 'EFECTY')->first();

                    $ingreso              = new Ingreso;
                    $ingreso->nro         = $caja;
                    $ingreso->empresa     = Auth::user()->empresa;
                    $ingreso->cliente     = $factura->cliente;
                    $ingreso->cuenta      = $banco->id;
                    $ingreso->metodo_pago = 1;
                    $ingreso->notas       = 'Pago Realizado por Carga de Archivo';
                    $ingreso->tipo        = 1;
                    $ingreso->fecha       = $request->fecha;
                    $ingreso->created_by  = Auth::user()->id;
                    $ingreso->save();

                    $precio               = $this->precision($request->monto);
                    $items                = new IngresosFactura;
                    $items->ingreso       = $ingreso->id;
                    $items->factura       = $factura->id;
                    $items->pagado        = $factura->pagado();
                    $items->pago          = $this->precision($request->monto);

                    if ($this->precision($request->monto) == $this->precision($factura->porpagar())) {
                        $factura->estatus = 0;
                        $factura->save();
                    }
                    $items->save();

                    ##SUMO A LAS NUMERACIONES EL RECIBO
                    $nro->caja = $caja + 1;
                    $nro->save();

                    ##REGISTRO EL MOVIMIENTO
                    $ingreso = Ingreso::find($ingreso->id);
                    $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);
                    $create++;
                }
            }
        }

        if ($create>0) {
            $mensaje = 'SE HA COMPLETADO EXITOSAMENTE LA CARGA DE DATOS AL SISTEMA - FACTURAS PAGADAS '.$create;
            $style   = 'success';
        }else{
            $mensaje = 'SE HA COMPLETADO EXITOSAMENTE LA CARGA DE DATOS AL SISTEMA PERO NO HAY FACTURAS POR PAGAR';
            $style   = 'danger';
        }
        return back()->with($style, $mensaje);
    }
}
