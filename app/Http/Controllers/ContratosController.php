<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Validator;
use Auth;
use DB;
use Session;

use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosCategoria;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\Inventario;
use App\Solicitud;
use App\Empresa;
use App\Contrato;
use App\Servicio;
use App\User;
use App\AP;
use App\Contacto;
use App\TipoIdentificacion;
use App\Vendedor;
use App\TipoEmpresa;
use App\Numeracion;
use App\Impuesto;
use App\Categoria;
use App\Movimiento;
use App\MovimientoLOG;
use App\Servidor;
use App\Mikrotik;
use App\Funcion;
use App\PlanesVelocidad;
use App\Interfaz;
use App\Ping;
use App\Canal;
use App\Integracion;
use App\Nodo;
use App\GrupoCorte;
use App\Segmento;
use App\Campos;
use App\Puerto;
use App\Oficina;
use App\CRM;

include_once(app_path() .'/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Shared_ZipArchive;

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;

class ContratosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'contratos', 'subseccion' => 'listado', 'title' => 'Contratos de Servicio', 'icon' =>'fas fa-file-contract']);
    }

    public function index(Request $request){

        $this->getAllPermissions(Auth::user()->id);
        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $planes = PlanesVelocidad::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servidores = Mikrotik::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $grupos = GrupoCorte::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        view()->share(['title' => 'Contratos', 'invert' => true]);
        $tipo = false;
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 2)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        $nodos = Nodo::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $canales = Canal::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        return view('contratos.indexnew', compact('clientes','planes','servidores','grupos','tipo','tabla','nodos','aps', 'vendedores', 'canales'));
    }

    public function disabled(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $planes = PlanesVelocidad::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servidores = Mikrotik::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $grupos = GrupoCorte::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        view()->share(['title' => 'Contratos', 'invert' => true]);
        $tipo = 'disabled';
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 2)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        $nodos = Nodo::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $canales = Canal::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        return view('contratos.indexnew', compact('clientes','planes','servidores','grupos','tipo','tabla','nodos','aps', 'vendedores', 'canales'));
    }

    public function enabled(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $planes = PlanesVelocidad::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servidores = Mikrotik::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $grupos = GrupoCorte::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        view()->share(['title' => 'Contratos', 'invert' => true]);
        $tipo = 'enabled';
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 2)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        $nodos = Nodo::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $canales = Canal::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        return view('contratos.indexnew', compact('clientes','planes','servidores','grupos','tipo','tabla','nodos','aps', 'vendedores', 'canales'));
    }

    public function contratos(Request $request, $nodo){

        $modoLectura = auth()->user()->modo_lectura();
        $contratos = Contrato::query()
			->select('contracts.*', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.apellido1 as c_apellido1','municipios.nombre as nombre_municipio' ,'contactos.apellido2 as c_apellido2', 'contactos.nit as c_nit', 'contactos.celular as c_telefono', 'contactos.email as c_email', 'contactos.barrio as c_barrio', 'contactos.direccion', 'contactos.celular as c_celular','contactos.fk_idmunicipio', 'contactos.email as c_email', 'contactos.id as c_id', 'contactos.firma_isp', 'contactos.estrato as c_estrato', DB::raw('(select fecha from ingresos where ingresos.cliente = contracts.client_id and ingresos.tipo = 1 LIMIT 1) AS pago'))
            ->selectRaw('INET_ATON(contracts.ip) as ipformat')
            // ->orderByDesc('ipformat')
            ->join('contactos', 'contracts.client_id', '=', 'contactos.id')
            ->join('municipios', 'contactos.fk_idmunicipio', '=', 'municipios.id');
        // return $contratos->get();
        if ($request->filtro == true) {
            if($request->cliente_id){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.client_id', $request->cliente_id);
                });
            }
            if($request->plan){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.plan_id', $request->plan);
                });
            }
            if($request->ip){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.ip', 'like', "%{$request->ip}%");
                });
            }
            if($request->mac){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.mac_address', 'like', "%{$request->mac}%");
                });
            }
            if($request->grupo_corte){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.grupo_corte', $request->grupo_corte);
                });
            }
            if($request->state){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.state', $request->state);
                });
            }
            if($request->conexion){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.conexion', $request->conexion);
                });
            }
            if($request->server_configuration_id){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.server_configuration_id', $request->server_configuration_id);
                });
            }
            if($request->nodo){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.nodo', $request->nodo);
                });
            }
            if($request->ap){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.ap', $request->ap);
                });
            }

            if($request->c_direccion){

                $direccion = $request->c_direccion;
                $direccion = explode(' ', $direccion);
                $direccion = array_reverse($direccion);

                foreach($direccion as $dir){
                    $dir = strtolower($dir);
                    $dir = str_replace("#","",$dir);
                    //$dir = str_replace("-","",$dir);
                    //$dir = str_replace("/","",$dir);

                    $contratos->where(function ($query) use ($dir) {
                        $query->orWhere('contactos.direccion', 'like', "%{$dir}%");
                        $query->orWhere('contracts.address_street', 'like', "%{$dir}%");
                    });
                }

            }
            if($request->c_direccion_precisa){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.address_street', 'like', "%{$request->c_direccion_precisa}%");
                    $query->orWhere('contactos.direccion', 'like', "%{$request->c_direccion_precisa}%");
                });
            }
            if($request->c_barrio){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contactos.barrio', 'like', "%{$request->c_barrio}%");
                });
            }
            if($request->c_celular){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contactos.celular', 'like', "%{$request->c_celular}%");
                });
            }
            if($request->c_email){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contactos.email', 'like', "%{$request->c_email}%");
                });
            }
            if($request->vendedor){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.vendedor', $request->vendedor);
                });
            }
            if($request->canal){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.canal', $request->canal);
                });
            }
            if($request->tecnologia){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.tecnologia', $request->tecnologia);
                });
            }
            if($request->facturacion){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.facturacion', $request->facturacion);
                });
            }
            if($request->desde){
                $contratos->where(function ($query) use ($request) {
                    $query->whereDate('contracts.created_at', '>=', Carbon::parse($request->desde)->format('Y-m-d'));
                });
            }
            if($request->hasta){
                $contratos->where(function ($query) use ($request) {
                    $query->whereDate('contracts.created_at', '<=', Carbon::parse($request->hasta)->format('Y-m-d'));
                });
            }
            if($request->fecha_corte){
                    $idContratos = Contrato::select('contracts.*')
                                    ->join('contactos', 'contactos.id', '=', 'contracts.client_id')
                                    ->join('factura as f','f.cliente','=','contactos.id')
                                    ->whereDate('f.vencimiento', Carbon::parse($request->fecha_corte)->format('Y-m-d'))
                                    ->groupBy('contracts.id')
                                    ->get()
                                    ->keyBy('id')
                                    ->keys()
                                    ->all();

                    $contratos->where(function ($query) use ($idContratos) {
                        $query->whereIn('contracts.id', $idContratos);
                    });
            }
            if($request->tipo_contrato){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.tipo_contrato', $request->tipo_contrato);
                });
            }
            if($request->nro){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.nro', 'like', "%{$request->nro}%");
                });
            }
            if($request->observaciones){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contracts.observaciones', 'like', "%{$request->observaciones}%");
                });
            }
            if($request->c_estrato){
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contactos.estrato', 'like', "%{$request->c_estrato}%");
                });
            }
        }

        $contratos->where('contracts.status', 1)->where('contracts.empresa', Auth::user()->empresa);
        $nodo = explode("-", $nodo);

        if ($nodo[0] == 'n') {
            $contratos->where('contracts.nodo', $nodo[1]);
        }elseif ($nodo[0] == 'a') {
            $contratos->where('contracts.ap', $nodo[1]);
        }elseif ($nodo[0] == 'g') {
            $contratos->where('contracts.grupo_corte', $nodo[1]);
        }elseif ($nodo[0] == 'm') {
            $contratos->where('contracts.server_configuration_id', $nodo[1]);
            $contratos->where('contracts.ip_autorizada', 0);
        }elseif ($nodo[0] == 'p') {
            $contratos->where('contracts.plan_id', $nodo[1]);
        }

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $contratos->where('contracts.oficina', auth()->user()->oficina);
            }
        }

        return datatables()->eloquent($contratos)
            ->editColumn('nro', function (Contrato $contrato) {
                if($contrato->ip){
                    return $contrato->nro ? "<a href=" . route('contratos.show', $contrato->id) . " class='ml-2'><strong>$contrato->nro</strong></a>" : "";
                }else{
                    return $contrato->nro ? "<a href=" . route('contratos.show', $contrato->id) . " class='ml-2'><strong>$contrato->nro</strong></a>" : "";
                }
            })
            ->editColumn('client_id', function (Contrato $contrato) {
                return  "<a href=" . route('contactos.show', $contrato->c_id) . ">{$contrato->c_nombre} {$contrato->c_apellido1} {$contrato->c_apellido2} {$contrato->municipio}</a>";
            })
            ->editColumn('nit', function (Contrato $contrato) {
                return '('.$contrato->cliente()->tip_iden('mini').') '.$contrato->c_nit;
            })
            ->editColumn('telefono', function (Contrato $contrato) {
                return $contrato->c_telefono;
            })
            ->editColumn('email', function (Contrato $contrato) {
                return $contrato->c_email;
            })
            ->editColumn('barrio', function (Contrato $contrato) {
                return $contrato->c_barrio;
            })
            ->editColumn('fk_idmunicipio', function (Contrato $contrato) {
                return $contrato->nombre_municipio;
            })
            ->editColumn('plan', function (Contrato $contrato) {
                if($contrato->plan_id){
                    return "<div class='elipsis-short-325'><a href=".route('planes-velocidad.show',$contrato->plan()->id)." target='_blank'>{$contrato->plan()->name}</a></div>";
                }else{
                    return 'N/A';
                }
            })
            ->editColumn('mac', function (Contrato $contrato) {
                return ($contrato->mac_address) ? $contrato->mac_address : 'N/A';
            })
            ->editColumn('ipformat', function (Contrato $contrato) {
                // return ($contrato->ip) ? '<a href="http://'.$contrato->ip.'" target="_blank">'.$contrato->ip.'  <i class="fas fa-external-link-alt"></i></a>' : 'N/A';
                $puerto = $contrato->puerto ? ':'.$contrato->puerto->nombre : '';
                return ($contrato->ipformat) ? '<a href="http://'.$contrato->ip.''.$puerto.'" target="_blank">'.$contrato->ip.''.$puerto.'  <i class="fas fa-external-link-alt"></i></a>' : 'N/A';
                    // return $contrato->ipformat;
            })
			->editColumn('grupo_corte', function (Contrato $contrato) {
                return $contrato->grupo_corte('true');
            })
            ->editColumn('state', function (Contrato $contrato) {
                return '<span class="text-'.$contrato->status('true').' font-weight-bold">'.$contrato->status().'</span>';
            })
            ->editColumn('pago', function (Contrato $contrato) {
                return ($contrato->pago($contrato->c_id)) ? '<a href='.route('ingresos.show', $contrato->pago($contrato->c_id)->id).' target="_blank">Nro. '.$contrato->pago($contrato->c_id)->nro.' | '.date('d-m-Y', strtotime($contrato->pago($contrato->c_id)->fecha)).'</a>' : 'N/A';
            })
            ->editColumn('servicio', function (Contrato $contrato) {
                return 'N/A';
            })
            ->editColumn('conexion', function (Contrato $contrato) {
                if($contrato->conexion){
                    return $contrato->conexion();
                }
                return 'N/A';
            })
            ->editColumn('server_configuration_id', function (Contrato $contrato) {
                return ($contrato->server_configuration_id) ? $contrato->servidor()->nombre : 'N/A';
            })
            ->editColumn('interfaz', function (Contrato $contrato) {
                return ($contrato->interfaz) ? $contrato->interfaz : 'N/A';
            })
            ->editColumn('nodo', function (Contrato $contrato) {
                // Puede ser un objeto o un String. ¿Por qué?
                $nodo = $contrato->nodo();
                if (is_object($nodo)) {
                    return $nodo->nombre;
                }
                return "N/A";
            })
            ->editColumn('ap', function (Contrato $contrato) {
                // Puede ser un objeto o un String. ¿Por qué?
                $ap = $contrato->ap();
                if (is_object($ap)) {
                    return $ap->nombre;
                }
                return "N/A";
            })
            ->editColumn('direccion', function (Contrato $contrato) {
                return ($contrato->address_street) ? $contrato->address_street : $contrato->direccion;
            })
            ->editColumn('celular', function (Contrato $contrato) {
                return $contrato->c_celular;
            })
            ->editColumn('email', function (Contrato $contrato) {
                return $contrato->c_email;
            })
            ->editColumn('factura', function (Contrato $contrato) {
                return $contrato->factura();
            })
            ->editColumn('servicio_tv', function (Contrato $contrato) {
                return ($contrato->servicio_tv) ? '<a href='.route('inventario.show', $contrato->servicio_tv).' target="_blank">'.$contrato->plan('true')->producto.'</a>' : 'N/A';
            })
            ->editColumn('vendedor', function (Contrato $contrato) {
                return ($contrato->vendedor) ? $contrato->vendedor()->nombre : 'N/A';
            })
            ->editColumn('canal', function (Contrato $contrato) {
                return ($contrato->canal) ? $contrato->canal()->nombre : 'N/A';
            })
            ->editColumn('tecnologia', function (Contrato $contrato) {
                return ($contrato->tecnologia) ? $contrato->tecnologia() : 'N/A';
            })
            ->editColumn('facturacion', function (Contrato $contrato) {
                return ($contrato->facturacion) ? $contrato->facturacion() : 'N/A';
            })
            ->editColumn('tipo_contrato', function (Contrato $contrato) {
                return ($contrato->tipo_contrato) ? ucfirst($contrato->tipo_contrato) : 'N/A';
            })
            ->editColumn('created_at', function (Contrato $contrato) {
                return ($contrato->created_at) ? date('d-m-Y', strtotime($contrato->created_at)) : 'N/A';
            })
            ->editColumn('estrato', function (Contrato $contrato) {
                return ($contrato->c_estrato) ? $contrato->c_estrato : 'N/A';
            })
            ->editColumn('observaciones', function (Contrato $contrato) {
                return ($contrato->observaciones) ? $contrato->observaciones : 'N/A';
            })
            ->editColumn('acciones', $modoLectura ?  "" : "contratos.acciones")
            ->rawColumns(['nro', 'client_id', 'nit', 'telefono', 'email', 'barrio', 'plan', 'mac', 'ipformat', 'grupo_corte', 'state', 'pago', 'servicio', 'factura', 'servicio_tv', 'acciones', 'vendedor', 'canal', 'tecnologia', 'observaciones', 'created_at'])
            ->toJson();
    }

    public function create($cliente = false){
        // $profile = $API->comm("/ppp/profile/getall");
        // dd($profile);

        $this->getAllPermissions(Auth::user()->id);
        $empresa = Auth::user()->empresa;
        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        // $clientes = Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();

        $cajas    = DB::table('bancos')->where('tipo_cta',3)->where('estatus',1)->where('empresa', Auth::user()->empresa)->get();
        $servidores = Mikrotik::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $planes = PlanesVelocidad::where('status', 1)->where('empresa', Auth::user()->empresa)->get();

        $identificaciones=TipoIdentificacion::all();
        $paises  =DB::table('pais')->where('codigo', 'CO')->get();
        $departamentos = DB::table('departamentos')->get();
        $nodos = Nodo::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $marcas = DB::table('marcas')->get();
        $grupos = GrupoCorte::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $puertos = Puerto::where('empresa', Auth::user()->empresa)->get();
        $servicios = Inventario::where('empresa', Auth::user()->empresa)->where('type', 'TV')->where('status', 1)->get();
        $serviciosOtros = Inventario::where('empresa', Auth::user()->empresa)->where('type','<>','TV')->where('status', 1)->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $canales = Canal::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $gmaps = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'GMAPS')->first();
        $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

        view()->share(['icon'=>'fas fa-file-contract', 'title' => 'Nuevo Contrato']);
        return view('contratos.create')->with(compact('clientes', 'planes', 'servidores', 'identificaciones',
        'paises', 'departamentos','nodos', 'aps', 'marcas', 'grupos', 'cliente', 'puertos', 'empresa',
         'servicios', 'vendedores', 'canales', 'gmaps', 'oficinas','serviciosOtros'));
    }

    public function store(Request $request){

        $this->getAllPermissions(Auth::user()->id);
        $request->validate([
            'client_id' => 'required',
            'grupo_corte' => 'required',
            'facturacion' => 'required',
            'contrato_permanencia' => 'required',
            'tipo_contrato' => 'required',
        ]);

        if($request->contrato_permanencia == 1){
            $request->validate([
                'contrato_permanencia_meses' => 'required'
            ]);
        }

        if(!$request->server_configuration_id && !$request->servicio_tv){
            return back()->with('danger', 'ESTÁ INTENTANDO GENERAR UN CONTRATO PERO NO HA SELECCIONADO NINGÚN SERVICIO')->withInput();
        }

        if($request->mac_address){
            $mac_address = Contrato::where('mac_address', $request->mac_address)->where('status', 1)->first();

            if ($mac_address) {
                return back()->withInput()->with('danger', 'LA DIRECCIÓN MAC YA SE ENCUENTRA REGISTRADA PARA OTRO CONTRATO');
            }
        }

        if($request->server_configuration_id){
            $request->validate([
                'plan_id' => 'required',
                'server_configuration_id' => 'required',
                'ip' => 'required',
                'conexion' => 'required',
            ]);
        }elseif($request->servicio_tv){
            $request->validate([
                'servicio_tv' => 'required'
            ]);
        }
        $ppoe_local_adress = "";
        $mikrotik = Mikrotik::where('id', $request->server_configuration_id)->first();
        $plan = PlanesVelocidad::where('id', $request->plan_id)->first();
        $cliente = Contacto::find($request->client_id);
        $servicio = $cliente->nombre.' '.$cliente->apellido1.' '.$cliente->apellido2;

        if ($mikrotik) {
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            $registro = false;
            $getall = '';
            //$API->debug = true;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                dd("hgola se conecto a la api");
                $nro = Numeracion::where('empresa', 1)->first();

                $nro_contrato = $nro->contrato;

                while (true) {
                    $numero = Contrato::where('nro', $nro_contrato)->count();
                    if ($numero == 0) {
                        break;
                    }
                    $nro_contrato++;
                }

                $rate_limit = '';
                $priority        = $plan->prioridad;
                $burst_limit     = (strlen($plan->burst_limit_subida)>1) ? $plan->burst_limit_subida.'/'.$plan->burst_limit_bajada : '';
                $burst_threshold = (strlen($plan->burst_threshold_subida)>1) ? $plan->burst_threshold_subida.'/'.$plan->burst_threshold_bajada : '';
                $burst_time      = ($plan->burst_time_subida) ? $plan->burst_time_subida.'/'.$plan->burst_time_bajada : '';
                $limit_at        = (strlen($plan->limit_at_subida)>1) ? $plan->limit_at_subida.'/'.$plan->limit_at_bajada  : '';
                $max_limit       = $plan->upload.'/'.$plan->download;

                if($max_limit){ $rate_limit .= $max_limit; }
                if(strlen($burst_limit)>3){ $rate_limit .= ' '.$burst_limit; }
                if(strlen($burst_threshold)>3){ $rate_limit .= ' '.$burst_threshold; }
                if(strlen($burst_time)>3){ $rate_limit .= ' '.$burst_time; }
                if($priority){ $rate_limit .= ' '.$priority; }
                if(strlen($limit_at)>3){ $rate_limit .= ' '.$limit_at; }

                /*PPPOE*/
                if($request->conexion == 1){

                    $ppoe_local_adress = $request->local_address;

                    $API->comm("/ppp/secret/add", array(
                        "name"           => $request->usuario,
                        "password"       => $request->password,
                        "profile"        => $request->profile,
                        "local-address"  => $request->local_address,
                        "remote-address" => $request->ip,
                        "service"        => 'pppoe',
                        "comment"        => $this->normaliza($servicio).'-'.$nro_contrato
                        )
                    );


                    // $API->comm("/queue/simple/add", array(
                    //     "name"            => $this->normaliza($servicio).'-'.$nro_contrato,
                    //     "target"          => $request->ip,
                    //     "max-limit"       => $plan->upload.'/'.$plan->download,
                    //     "burst-limit"     => $burst_limit,
                    //     "burst-threshold" => $burst_threshold,
                    //     "burst-time"      => $burst_time,
                    //     "priority"        => $priority,
                    //     "limit-at"        => $limit_at
                    //     )
                    // );
                }

                /*DHCP*/
                if($request->conexion == 2){
                    if($plan->dhcp_server){
                        if($request->simple_queue == 'dinamica'){
                            $API->comm("/ip/dhcp-server/set\n=name=".$plan->dhcp_server."\n=address-pool=static-only\n=parent-queue=".$plan->parenta);

                            $API->comm("/ip/dhcp-server/lease/add", array(
                                "comment"     => $this->normaliza($servicio).'-'.$nro_contrato,
                                "address"     => $request->ip,
                                "server"      => $plan->dhcp_server,
                                "mac-address" => $request->mac_address,
                                "rate-limit"  => $rate_limit
                                )
                            );

                            $name = $API->comm("/ip/dhcp-server/lease/getall", array(
                                "?comment" => $this->normaliza($servicio).'-'.$nro_contrato
                                )
                            );
                        }elseif ($request->simple_queue == 'estatica') {
                            $API->comm("/ip/dhcp-server/lease/add", array(
                                "comment"     => $this->normaliza($servicio).'-'.$nro_contrato,
                                "address"     => $request->ip,
                                "server"      => $plan->dhcp_server,
                                "mac-address" => $request->mac_address
                                )
                            );

                            $name = $API->comm("/ip/dhcp-server/lease/getall", array(
                                "?comment" => $this->normaliza($servicio).'-'.$nro_contrato
                                )
                            );

                            if($name){
                                $registro = true;
                                $API->comm("/queue/simple/add", array(
                                    "name"            => $this->normaliza($servicio).'-'.$nro_contrato,
                                    "target"          => $request->ip,
                                    "max-limit"       => $plan->upload.'/'.$plan->download,
                                    "burst-limit"     => $burst_limit,
                                    "burst-threshold" => $burst_threshold,
                                    "burst-time"      => $burst_time,
                                    "priority"        => $priority,
                                    "limit-at"        => $limit_at
                                    )
                                );
                            }
                        }
                    }else{
                        $mensaje='NO SE HA PODIDO CREAR EL CONTRATO DE SERVICIOS, NO EXISTE UN SERVIDOR DHCP DEFINIDO PARA EL PLAN '.$plan->name;
                        return redirect('empresa/contratos')->with('danger', $mensaje);
                    }
                }

                /*IP ESTÁTICA*/
                if($request->conexion == 3){

                    if($mikrotik->amarre_mac == 1){
                        $request->validate([
                            'mac_address' => 'required'
                        ]);

                        $API->comm("/ip/arp/add", array(
                            "comment"     => $this->normaliza($servicio).'-'.$nro_contrato,
                            "address"     => $request->ip,
                            "interface"   => $request->interfaz,
                            "mac-address" => $request->mac_address
                            )
                        );
                    }

                    $API->comm("/queue/simple/add", array(
                        "name"            => $this->normaliza($servicio).'-'.$nro_contrato,
                        "target"          => $request->ip,
                        "max-limit"       => $plan->upload.'/'.$plan->download,
                        "burst-limit"     => $burst_limit,
                        "burst-threshold" => $burst_threshold,
                        "burst-time"      => $burst_time,
                        "priority"        => $priority,
                        "limit-at"        => $limit_at,
                        // "queue"           => $plan->queue_type_subida.'/'.$plan->queue_type_bajada
                        )
                    );

                    $name = $API->comm("/queue/simple/getall", array(
                        "?target" => $request->ip
                        )
                    );

                    if($name){
                        $registro = true;
                    }

                    if($request->ip_new){
                        if($mikrotik->amarre_mac == 1){
                            $API->comm("/ip/arp/add", array(
                                "comment"     => $this->normaliza($servicio).'-'.$nro_contrato,
                                "address"     => $request->ip_new,
                                "interface"   => $request->interfaz,
                                "mac-address" => $request->mac_address
                                )
                            );
                        }

                        $API->comm("/queue/simple/add", array(
                                "name"            => $this->normaliza($servicio).'-'.$nro_contrato,
                                "target"          => $request->ip,
                                "max-limit"       => $plan->upload.'/'.$plan->download,
                                "burst-limit"     => $burst_limit,
                                "burst-threshold" => $burst_threshold,
                                "burst-time"      => $burst_time,
                                "priority"        => $priority,
                                "limit-at"        => $limit_at
                            )
                        );
                    }
                }

                /*VLAN*/
                if($request->conexion == 4){
                    $API->comm("/interface/vlan/add", array(
                        "name"        => $request->name_vlan,
                        "vlan-id"     => $request->id_vlan,
                        "interface"   => $request->interfaz
                        )
                    );

                    $API->comm("/ip/address/add", array(
                        "address"     => $request->local_address,
                        "interface"   => $request->name_vlan
                        )
                    );

                    $API->comm("/queue/simple/add", array(
                            "name"            => $this->normaliza($servicio).'-'.$nro_contrato,
                            "target"          => $request->ip,
                            "max-limit"       => $plan->upload.'/'.$plan->download,
                            "burst-limit"     => $burst_limit,
                            "burst-threshold" => $burst_threshold,
                            "burst-time"      => $burst_time,
                            "priority"        => $priority,
                            "limit-at"        => $limit_at
                        )
                    );
                }

                $ip_autorizada = 0;

                if($mikrotik->regla_ips_autorizadas == 1){
                    $API->comm("/ip/firewall/address-list/add\n=list=ips_autorizadas\n=address=".$request->ip);
                    $ip_autorizada = 1;
                }

                $API->disconnect();

                $contrato = new Contrato();
                $contrato->plan_id                 = $request->plan_id;
                $contrato->nro                     = $nro_contrato;
                $contrato->servicio                = $this->normaliza($servicio).'-'.$nro_contrato;
                $contrato->client_id               = $request->client_id;
                $contrato->server_configuration_id = $request->server_configuration_id;
                $contrato->ip                      = $request->ip;
                $contrato->ip_new                  = $request->ip_new;
                $contrato->usuario                 = $request->usuario;
                $contrato->password                = $request->password;
                $contrato->conexion                = $request->conexion;
                $contrato->simple_queue            = $request->simple_queue;
                $contrato->interfaz                = $request->interfaz;
                $contrato->local_address           = $request->local_address;
                $contrato->direccion_local_address = $request->direccion_local_address;
                $contrato->local_address_new       = $request->local_address_new;
                $contrato->profile                 = $request->profile;
                $contrato->local_adress_pppoe      = $ppoe_local_adress;
                $contrato->mac_address             = $request->mac_address;
                $contrato->id_vlan                 = $request->id_vlan;
                $contrato->name_vlan               = $request->name_vlan;
                $contrato->marca_router            = $request->marca_router;
                $contrato->modelo_router           = $request->modelo_router;
                $contrato->marca_antena            = $request->marca_antena;
                $contrato->modelo_antena           = $request->modelo_antena;
                $contrato->grupo_corte             = $request->grupo_corte;
                $contrato->facturacion             = $request->facturacion;
                $contrato->ip_autorizada           = $ip_autorizada;
                $contrato->empresa                 = Auth::user()->empresa;
                $contrato->puerto_conexion         = $request->puerto_conexion;
                $contrato->latitude                = $request->latitude;
                $contrato->longitude               = $request->longitude;
                $contrato->contrato_permanencia    = $request->contrato_permanencia;
                $contrato->serial_onu              = $request->serial_onu;
                $contrato->linea                   = $request->linea;
                $contrato->descuento               = $request->descuento;
                $contrato->vendedor                = $request->vendedor;
                $contrato->canal                   = $request->canal;
                $contrato->address_street          = $request->address_street;
                $contrato->tecnologia              = $request->tecnologia;
                $contrato->tipo_contrato           = $request->tipo_contrato;
                $contrato->observaciones           = $request->observaciones;


                if($request->tipo_suspension_no == 1){
                    $contrato->tipo_nosuspension = 1;
                    $contrato->fecha_desde_nosuspension = $request->fecha_desde_nosuspension;
                    $contrato->fecha_hasta_nosuspension = $request->fecha_hasta_nosuspension;
                }

                if($request->factura_individual){
                    $contrato->factura_individual = $request->factura_individual;
                }

                if($request->ap){
                    $ap = AP::find($request->ap);
                    $contrato->nodo    = $ap->nodo;
                    $contrato->ap      = $request->ap;
                }

                if($request->servicio_tv){
                    $contrato->servicio_tv = $request->servicio_tv;
                }

                if($request->oficina){
                    $contrato->oficina = $request->oficina;
                }

                if($request->contrato_permanencia_meses){
                    $contrato->contrato_permanencia_meses = $request->contrato_permanencia_meses;
                }

                if($request->costo_reconexion){
                    $contrato->costo_reconexion = $request->costo_reconexion;
                }

                ### DOCUMENTOS ADJUNTOS ###

                if($request->adjunto_a) {
                    $file = $request->file('adjunto_a');
                    $nombre =  $file->getClientOriginalName();
                    Storage::disk('documentos')->put($nombre, \File::get($file));
                    $contrato->adjunto_a = $nombre;
                    $contrato->referencia_a = $request->referencia_a;
                }
                if($request->adjunto_b) {
                    $file = $request->file('adjunto_b');
                    $nombre =  $file->getClientOriginalName();
                    Storage::disk('documentos')->put($nombre, \File::get($file));
                    $contrato->adjunto_b = $nombre;
                    $contrato->referencia_b = $request->referencia_b;
                }
                if($request->adjunto_c) {
                    $file = $request->file('adjunto_c');
                    $nombre =  $file->getClientOriginalName();
                    Storage::disk('documentos')->put($nombre, \File::get($file));
                    $contrato->adjunto_c = $nombre;
                    $contrato->referencia_c = $request->referencia_c;
                }
                if($request->adjunto_d) {
                    $file = $request->file('adjunto_d');
                    $nombre =  $file->getClientOriginalName();
                    Storage::disk('documentos')->put($nombre, \File::get($file));
                    $contrato->adjunto_d = $nombre;
                    $contrato->referencia_d = $request->referencia_d;
                }

                ### DOCUMENTOS ADJUNTOS ###

                $contrato->creador = Auth::user()->nombres;
                $contrato->save();

                $nro->contrato = $nro_contrato + 1;
                $nro->save();

                if($registro){
                    $mensaje='SE HA CREADO SATISFACTORIAMENTE EL CONTRATO DE SERVICIOS EN EL SISTEMA Y LA MIKROTIK';
                }else{
                    $mensaje='SE HA CREADO SATISFACTORIAMENTE EL CONTRATO DE SERVICIOS';
                }

                return redirect('empresa/contratos/'.$contrato->id)->with('success', $mensaje);
            } else {
                $mensaje='NO SE HA PODIDO CREAR EL CONTRATO DE SERVICIOS';
                return redirect('empresa/contratos')->with('danger', $mensaje);
            }
        }else{
            $nro = Numeracion::where('empresa', 1)->first();
            $nro_contrato = $nro->contrato;

            while (true) {
                $numero = Contrato::where('nro', $nro_contrato)->count();
                if ($numero == 0) {
                    break;
                }
                $nro_contrato++;
            }

            $contrato = new Contrato();
            $contrato->nro                  = $nro_contrato;
            $contrato->servicio             = $this->normaliza($servicio).'-'.$nro_contrato;
            $contrato->client_id            = $request->client_id;
            $contrato->grupo_corte          = $request->grupo_corte;
            $contrato->facturacion          = $request->facturacion;
            $contrato->empresa              = Auth::user()->empresa;
            $contrato->latitude             = $request->latitude;
            $contrato->longitude            = $request->longitude;
            $contrato->contrato_permanencia = $request->contrato_permanencia;
            $contrato->servicio_tv          = $request->servicio_tv;
            $contrato->descuento            = $request->descuento;
            $contrato->vendedor             = $request->vendedor;
            $contrato->canal                = $request->canal;
            $contrato->address_street       = $request->address_street;
            $contrato->tipo_contrato        = $request->tipo_contrato;
            $contrato->observaciones           = $request->observaciones;

            if($request->factura_individual){
                $contrato->factura_individual   = $request->factura_individual;
            }

            if($request->oficina){
                $contrato->oficina = $request->oficina;
            }

            if($request->contrato_permanencia_meses){
                $contrato->contrato_permanencia_meses = $request->contrato_permanencia_meses;
            }

            if($request->costo_reconexion){
                $contrato->costo_reconexion = $request->costo_reconexion;
            }

            ### DOCUMENTOS ADJUNTOS ###

            if($request->adjunto_a) {
                $file = $request->file('adjunto_a');
                $nombre =  $file->getClientOriginalName();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->adjunto_a = $nombre;
                $contrato->referencia_a = $request->referencia_a;
            }
            if($request->adjunto_b) {
                $file = $request->file('adjunto_b');
                $nombre =  $file->getClientOriginalName();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->adjunto_b = $nombre;
                $contrato->referencia_b = $request->referencia_b;
            }
            if($request->adjunto_c) {
                $file = $request->file('adjunto_c');
                $nombre =  $file->getClientOriginalName();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->adjunto_c = $nombre;
                $contrato->referencia_c = $request->referencia_c;
            }
            if($request->adjunto_d) {
                $file = $request->file('adjunto_d');
                $nombre =  $file->getClientOriginalName();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->adjunto_d = $nombre;
                $contrato->referencia_d = $request->referencia_d;
            }

            ### DOCUMENTOS ADJUNTOS ###

            $contrato->creador = Auth::user()->nombres;
            $contrato->save();

            $nro->contrato = $nro_contrato + 1;
            $nro->save();

            return redirect('empresa/contratos/'.$contrato->id)->with('success', 'SE HA CREADO SATISFACTORIAMENTE EL CONTRATO DE SERVICIOS');
        }

        ## Otro tipo de servicio ingresa tenga o no tenga mk ##
        if($request->servicio_otro){
            $contrato->servicio_otro = $request->servicio_otro;
            $contrato->save();
        }
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->
        select('contracts.plan_id','contracts.id','contracts.opciones_dian','contracts.nro','contracts.state','contracts.interfaz',
        'c.nombre','c.apellido1', 'c.apellido2','c.nit','c.celular','c.telefono1','contracts.ip','contracts.mac_address',
        'contracts.server_configuration_id','contracts.conexion','contracts.marca_router','contracts.modelo_router',
        'contracts.marca_antena','contracts.modelo_antena','contracts.nodo','contracts.ap','contracts.interfaz',
        'contracts.local_address','contracts.local_address_new','contracts.ip_new','contracts.grupo_corte',
         'contracts.facturacion', 'contracts.fecha_suspension', 'contracts.usuario','contracts.local_adress_pppoe','contracts.direccion_local_address', 'contracts.password',
         'contracts.adjunto_a', 'contracts.referencia_a', 'contracts.adjunto_b', 'contracts.referencia_b',
         'contracts.adjunto_c', 'contracts.referencia_c', 'contracts.adjunto_d','contracts.profile', 'contracts.referencia_d',
         'contracts.simple_queue', 'contracts.latitude', 'contracts.longitude', 'contracts.servicio_tv','contracts.servicio_otro',
         'contracts.contrato_permanencia', 'contracts.contrato_permanencia_meses', 'contracts.serial_onu',
          'contracts.linea', 'contracts.descuento', 'contracts.vendedor', 'contracts.canal', 'contracts.address_street',
          'contracts.tecnologia', 'contracts.costo_reconexion', 'contracts.tipo_contrato', 'contracts.puerto_conexion',
          'contracts.observaciones','contracts.fecha_hasta_nosuspension','contracts.fecha_desde_nosuspension','contracts.tipo_nosuspension')
          ->where('contracts.id', $id)->where('contracts.empresa', Auth::user()->empresa)->first();


        $planes = ($contrato->server_configuration_id) ? PlanesVelocidad::where('status', 1)->where('mikrotik', $contrato->server_configuration_id)->get() : PlanesVelocidad::where('status', 1)->get();
        $nodos = Nodo::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $marcas = DB::table('marcas')->get();
        $servidores = Mikrotik::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $interfaces = Interfaz::all();
        $grupos = GrupoCorte::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $puertos = Puerto::where('empresa', Auth::user()->empresa)->get();
        $servicios = Inventario::where('empresa', Auth::user()->empresa)->where('type', 'TV')->where('status', 1)->get();
        $serviciosOtros = Inventario::where('empresa', Auth::user()->empresa)->where('type','<>','TV')->where('status', 1)->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $canales = Canal::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $gmaps = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'GMAPS')->first();
        $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();

        if ($contrato) {
            view()->share(['icon'=>'fas fa-file-contract', 'title' => 'Editar Contrato: '.$contrato->nro]);
            return view('contratos.edit')->with(compact('contrato','planes','nodos','aps', 'marcas', 'servidores',
            'interfaces', 'grupos', 'puertos', 'servicios', 'vendedores', 'canales', 'gmaps', 'oficinas','serviciosOtros'));
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function update(Request $request, $id){

        $this->getAllPermissions(Auth::user()->id);
        $request->validate([
            'grupo_corte' => 'required',
            'facturacion' => 'required',
            'contrato_permanencia' => 'required',
            'nro' => 'required',
            'tipo_contrato' => 'required'
        ]);

        if($request->contrato_permanencia == 1){
            $request->validate([
                'contrato_permanencia_meses' => 'required'
            ]);
        }

        $verificar = Contrato::where('empresa', Auth::user()->empresa)->where('nro', $request->nro)->where('id', '<>', $id)->first();

        if($verificar){
            return back()->with('danger', 'ESTÁ INTENTANDO REGISTRAR UN NRO DE CONTRATO QUE YA SE ENCUENTRA REGISTRADO');
        }

        if(!$request->server_configuration_id && !$request->servicio_tv){
            return back()->with('danger', 'ESTÁ INTENTANDO GENERAR UN CONTRATO PERO NO HA SELECCIONADO NINGÚN SERVICIO');
        }

        if($request->mac_address){
            $mac_address = Contrato::where('mac_address', $request->mac_address)->where('status', 1)->where('id', '<>', $id)->first();

            if ($mac_address) {
                return back()->withInput()->with('danger', 'LA DIRECCIÓN MAC YA SE ENCUENTRA REGISTRADA PARA OTRO CONTRATO');
            }
        }

        if($request->server_configuration_id){
            $request->validate([
                'plan_id' => 'required',
                'server_configuration_id' => 'required',
                'ip' => 'required',
                'conexion' => 'required',
            ]);
        }elseif($request->servicio_tv){
            $request->validate([
                'servicio_tv' => 'required'
            ]);
        }

        $contrato = Contrato::find($id);
        $descripcion = '';
        $registro = false;
        $getall = '';
        if ($contrato) {
            $plan = PlanesVelocidad::where('id', $request->plan_id)->first();
            $mikrotik = ($plan) ? Mikrotik::where('id', $plan->mikrotik)->first() : false;
            $cliente = $contrato->cliente();
            $servicio = $cliente->nombre.' '.$cliente->apellido1.' '.$cliente->apellido2;

            if ($mikrotik) {
                $API = new RouterosAPI();
                $API->port = $mikrotik->puerto_api;
                //$API->debug = true;

                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    ## ELIMINAMOS DE MK ##
                    if($contrato->conexion == 1){
                        //OBTENEMOS AL CONTRATO MK
                        $mk_user = $API->comm("/ppp/secret/getall", array(
                            "?remote-address" => $contrato->ip,
                            )
                        );
                        if($mk_user){
                            // REMOVEMOS EL SECRET
                            $API->comm("/ppp/secret/remove", array(
                                ".id" => $mk_user[0][".id"],
                                )
                            );
                        }
                    }

                    if($contrato->conexion == 2){
                        $name = $API->comm("/ip/dhcp-server/lease/getall", array(
                            "?address" => $contrato->ip
                            )
                        );
                        if($name){
                            // REMOVEMOS EL IP DHCP
                            $API->comm("/ip/dhcp-server/lease/remove", array(
                                ".id" => $name[0][".id"],
                                )
                            );
                        }
                    }

                    if($contrato->conexion == 3){
                        //OBTENEMOS AL CONTRATO MK
                        $mk_user = $API->comm("/ip/arp/getall", array(
                            "?address" => $contrato->ip // IP DEL CLIENTE
                            )
                        );
                        if($mk_user){
                            // REMOVEMOS EL IP ARP
                            $API->comm("/ip/arp/remove", array(
                                ".id" => $mk_user[0][".id"],
                                )
                            );
                        }
                    }

                    #ELMINAMOS DEL QUEUE#
                    $queue = $API->comm("/queue/simple/getall", array(
                        "?target" => $contrato->ip.'/32'
                        )
                    );

                    // if($queue){
                    //     $API->comm("/queue/simple/remove", array(
                    //         ".id" => $queue[0][".id"],
                    //         )
                    //     );
                    // }
                    #ELMINAMOS DEL QUEUE#

                    #ELIMINAMOS DE IP_AUTORIZADAS#
                    $API->write('/ip/firewall/address-list/print', TRUE);
                    $ARRAYS = $API->read();

                    $API->write('/ip/firewall/address-list/print', false);
                    $API->write('?address='.$contrato->ip, false);
                    $API->write("?list=ips_autorizadas",false);
                    $API->write('=.proplist=.id');
                    $ARRAYS = $API->read();

                    if(count($ARRAYS)>0){
                        $API->write('/ip/firewall/address-list/remove', false);
                        $API->write('=.id='.$ARRAYS[0]['.id']);
                        $READ = $API->read();
                    }
                    #ELIMINAMOS DE IP_AUTORIZADAS#
                    ## ELIMINAMOS DE MK ##

                    $rate_limit      = '';
                    $priority        = $plan->prioridad;
                    $burst_limit     = (strlen($plan->burst_limit_subida)>1) ? $plan->burst_limit_subida.'/'.$plan->burst_limit_bajada : '';
                    $burst_threshold = (strlen($plan->burst_threshold_subida)>1) ? $plan->burst_threshold_subida.'/'.$plan->burst_threshold_bajada : '';
                    $burst_time      = ($plan->burst_time_subida) ? $plan->burst_time_subida.'/'.$plan->burst_time_bajada: '';
                    $limit_at        = (strlen($plan->limit_at_subida)>1) ? $plan->limit_at_subida.'/'.$plan->limit_at_bajada : '';
                    $max_limit       = $plan->upload.'/'.$plan->download;

                    if($max_limit){ $rate_limit .= $max_limit; }
                    if(strlen($burst_limit)>3){ $rate_limit .= ' '.$burst_limit; }
                    if(strlen($burst_threshold)>3){ $rate_limit .= ' '.$burst_threshold; }
                    if(strlen($burst_time)>3){ $rate_limit .= ' '.$burst_time; }
                    if($priority){ $rate_limit .= ' '.$priority; }
                    if($limit_at){ $rate_limit .= ' '.$limit_at; }

                    /*PPPOE*/
                    if($request->conexion == 1){
                        $API->comm("/ppp/secret/add", array(
                            "name"           => $request->usuario,
                            "password"       => $request->password,
                            "profile"        => $request->profile,
                            "local-address"  => $request->local_address,
                            "remote-address" => $request->ip,
                            "service"        => 'pppoe',
                            "comment"        => $this->normaliza($servicio).'-'.$request->nro
                            )
                        );

                            $getall = $API->comm("/ppp/secret/getall", array(
                            "?local-address" => $request->ip
                            )
                        );
                    }

                    /*DHCP*/
                    if($request->conexion == 2){
                        if(isset($plan->dhcp_server)){
                            if($request->simple_queue == 'dinamica'){
                                $API->comm("/ip/dhcp-server/set\n=name=".$plan->dhcp_server."\n=address-pool=static-only\n=parent-queue=".$plan->parenta);
                                $API->comm("/ip/dhcp-server/lease/add", array(
                                    "comment"     => $this->normaliza($servicio).'-'.$request->nro,
                                    "address"     => $request->ip,
                                    "server"      => $plan->dhcp_server,
                                    "mac-address" => $request->mac_address,
                                    "rate-limit"  => $rate_limit
                                    )
                                );
                            }elseif ($request->simple_queue == 'estatica') {
                                $API->comm("/ip/dhcp-server/lease/add", array(
                                    "comment"     => $this->normaliza($servicio).'-'.$request->nro,
                                    "address"     => $request->ip,
                                    "server"      => $plan->dhcp_server,
                                    "mac-address" => $request->mac_address
                                    )
                                );
                            }

                            $getall = $API->comm("/ip/dhcp-server/lease/getall", array(
                                "?address" => $request->ip
                                )
                            );
                        }else{
                            $mensaje='NO SE HA PODIDO EDITAR EL CONTRATO DE SERVICIOS, NO EXISTE UN SERVIDOR DHCP DEFINIDO PARA EL PLAN '.$plan->name;
                            return redirect('empresa/contratos')->with('danger', $mensaje);
                        }
                    }

                    /*IP ESTÁTICA*/
                    if($request->conexion == 3){
                        if($mikrotik->amarre_mac == 1){
                            $API->comm("/ip/arp/add", array(
                                "comment"     => $this->normaliza($servicio).'-'.$request->nro,
                                "address"     => $request->ip,
                                "interface"   => $request->interfaz,
                                "mac-address" => $request->mac_address
                                )
                            );

                            $getall = $API->comm("/ip/arp/getall", array(
                                "?address" => $request->ip
                                )
                            );
                        }
                        if($queue){
                            $API->comm("/queue/simple/set", array(
                                ".id"             => $queue[0][".id"],
                                "target"          => $request->ip,
                                "max-limit"       => $plan->upload.'/'.$plan->download,
                                "burst-limit"     => $burst_limit,
                                "burst-threshold" => $burst_threshold,
                                "burst-time"      => $burst_time,
                                "priority"        => $priority,
                                "limit-at"        => $limit_at
                                )
                            );
                        }else{
                            $API->comm("/queue/simple/add", array(
                                "name"            => $this->normaliza($servicio).'-'.$request->nro,
                                "target"          => $request->ip,
                                "max-limit"       => $plan->upload.'/'.$plan->download,
                                "burst-limit"     => $burst_limit,
                                "burst-threshold" => $burst_threshold,
                                "burst-time"      => $burst_time,
                                "priority"        => $priority,
                                "limit-at"        => $limit_at
                                )
                            );
                        }
                    }

                    /*VLAN*/
                    if($request->conexion == 4){

                    }
                    //if($getall){
                        $registro = true;
                        $queue = $API->comm("/queue/simple/getall", array(
                            "?target" => $contrato->ip.'/32'
                            )
                        );

                        // if($queue){
                        //     $API->comm("/queue/simple/set", array(
                        //         ".id"             => $queue[0][".id"],
                        //         "target"          => $request->ip,
                        //         "max-limit"       => $plan->upload.'/'.$plan->download,
                        //         "burst-limit"     => $burst_limit,
                        //         "burst-threshold" => $burst_threshold,
                        //         "burst-time"      => $burst_time,
                        //         "priority"        => $priority,
                        //         "limit-at"        => $limit_at
                        //         )
                        //     );
                        // }else{
                        //     $API->comm("/queue/simple/add", array(
                        //         "name"            => $this->normaliza($servicio).'-'.$request->nro,
                        //         "target"          => $request->ip,
                        //         "max-limit"       => $plan->upload.'/'.$plan->download,
                        //         "burst-limit"     => $burst_limit,
                        //         "burst-threshold" => $burst_threshold,
                        //         "burst-time"      => $burst_time,
                        //         "priority"        => $priority,
                        //         "limit-at"        => $limit_at
                        //         )
                        //     );
                        // }
                    //}
                    #AGREGAMOS A IP_AUTORIZADAS#
                    $API->comm("/ip/firewall/address-list/add", array(
                        "address" => $request->ip,
                        "list" => 'ips_autorizadas'
                        )
                    );
                    #AGREGAMOS A IP_AUTORIZADAS#
                }

                $API->disconnect();

                if($registro){
                    $grupo = GrupoCorte::find($request->grupo_corte);

                    if($contrato->grupo_corte){
                        $descripcion .= ($contrato->grupo_corte == $request->grupo_corte) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Grupo de Corte</b> de '.$contrato->grupo_corte()->nombre.' a '.$grupo->nombre.'<br>';
                    }else{
                        $descripcion .= ($contrato->grupo_corte == $request->grupo_corte) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Grupo de Corte</b> a '.$grupo->nombre.'<br>';
                    }
                    $contrato->grupo_corte = $request->grupo_corte;
                    $contrato->facturacion = $request->facturacion;

                    /*$descripcion .= ($contrato->fecha_corte == $request->fecha_corte) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Fecha de Corte</b> de '.$contrato->fecha_corte.' a '.$request->fecha_corte.'<br>';
                    $contrato->fecha_corte = $request->fecha_corte;*/

                    $descripcion .= ($contrato->fecha_suspension == $request->fecha_suspension) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Fecha de Suspensión Personalizada</b> a '.$request->fecha_suspension.'<br>';
                    $contrato->fecha_suspension = $request->fecha_suspension;

                    $plan_old = ($contrato->plan_id) ? PlanesVelocidad::find($contrato->plan_id)->name : 'Ninguno';
                    $plan_new = PlanesVelocidad::find($request->plan_id);

                    $descripcion .= ($contrato->plan_id == $request->plan_id) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Plan</b> de '.$plan_old.' a '.$plan_new->name.'<br>';
                    $contrato->plan_id = $request->plan_id;

                    $descripcion .= ($contrato->ip == $request->ip) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio de IP</b> de '.$contrato->ip.' a '.$request->ip.'<br>';
                    $contrato->ip = $request->ip;

                    $descripcion .= ($contrato->ip_new == $request->ip_new) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio de IP</b> de '.$contrato->ip_new.' a '.$request->ip_new.'<br>';
                    $contrato->ip_new = $request->ip_new;

                    $descripcion .= ($contrato->local_address == $request->local_address) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio de Segmento</b> de '.$contrato->local_address.' a '.$request->local_address.'<br>';
                    $contrato->local_address = $request->local_address;

                    $descripcion .= ($contrato->local_address_new == $request->local_address_new) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio de Segmento</b> de '.$contrato->local_address_new.' a '.$request->local_address_new.'<br>';
                    $contrato->local_address_new = $request->local_address_new;

                    $descripcion .= ($contrato->mac_address == $request->mac_address) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio de MAC</b> de '.$contrato->mac_address.' a '.$request->mac_address.'<br>';
                    $contrato->mac_address   = $request->mac_address;

                    $descripcion .= ($contrato->marca_router == $request->marca_router) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Marca Router</b> de '.$contrato->marca_router.' a '.$request->marca_router.'<br>';
                    $contrato->marca_router  = $request->marca_router;

                    $descripcion .= ($contrato->modelo_router == $request->modelo_router) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Modelo Router</b> de '.$contrato->modelo_router.' a '.$request->modelo_router.'<br>';
                    $contrato->modelo_router = $request->modelo_router;

                    $descripcion .= ($contrato->marca_antena == $request->marca_antena) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Marca Antena</b> de '.$contrato->marca_antena.' a '.$request->marca_antena.'<br>';
                    $contrato->marca_antena  = $request->marca_antena;

                    $descripcion .= ($contrato->modelo_antena == $request->modelo_antena) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Modelo Antena</b> de '.$contrato->modelo_antena.' a '.$request->modelo_antena.'<br>';
                    $contrato->modelo_antena = $request->modelo_antena;

                    $descripcion .= ($contrato->interfaz == $request->interfaz) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio de Interfaz</b> de '.$contrato->interfaz.' a '.$request->interfaz.'<br>';
                    $contrato->interfaz = $request->interfaz;

                    if($request->ap){
                        $ap_new = AP::find($request->ap);
                        $ap_old = AP::find($contrato->ap);
                        if(isset($ap_new)){
                            $descripcion .= ($contrato->ap == $ap_new->ap) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Access Point</b> de '.$ap_old->nombre.' a '.$ap_new->nombre.'<br>';
                            $contrato->ap   = $request->ap;
                        }
                    }

                    if($contrato->nodo){
                        $nodo_old = Nodo::find($contrato->nodo);

                        if(isset($ap_new->nodo)){
                            $nodo_new = Nodo::find($ap_new->nodo)->nombre;
                        }else{
                            $nodo_new = '';
                        }

                        if(isset($ap_new->nodo)){
                            $contrato->nodo = $ap_new->nodo;
                            $descripcion .= ($contrato->nodo == $ap_new->nodo) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Nodo</b> de '.$nodo_old->nombre.' a '.$nodo_new.'<br>';
                        }
                    }

                    $contrato->puerto_conexion    = $request->puerto_conexion;
                    $contrato->usuario            = $request->usuario;
                    $contrato->password           = $request->password;
                    $contrato->simple_queue       = $request->simple_queue;
                    $contrato->conexion           = $request->conexion;
                    $contrato->latitude           = $request->latitude;
                    $contrato->longitude          = $request->longitude;
                    if($request->factura_individual){
                        $contrato->factura_individual = $request->factura_individual;
                    }
                    $contrato->servicio_tv             = $request->servicio_tv;
                    $contrato->contrato_permanencia    = $request->contrato_permanencia;
                    $contrato->serial_onu              = $request->serial_onu;
                    $contrato->linea                   = $request->linea;
                    $contrato->servicio                = $this->normaliza($servicio).'-'.$request->nro;
                    $contrato->server_configuration_id = $mikrotik->id;
                    $contrato->descuento               = $request->descuento;
                    $contrato->vendedor                = $request->vendedor;
                    $contrato->canal                   = $request->canal;
                    $contrato->nro                     = $request->nro;
                    $contrato->address_street          = $request->address_street;
                    $contrato->tecnologia              = $request->tecnologia;
                    $contrato->tipo_contrato           = $request->tipo_contrato;
                    $contrato->observaciones           = $request->observaciones;

                    if($request->tipo_suspension_no == 1){
                        $contrato->tipo_nosuspension = 1;
                        $contrato->fecha_desde_nosuspension = $request->fecha_desde_nosuspension;
                        $contrato->fecha_hasta_nosuspension = $request->fecha_hasta_nosuspension;
                    }

                    if($request->oficina){
                        $contrato->oficina = $request->oficina;
                    }

                    if($request->contrato_permanencia_meses){
                        $contrato->contrato_permanencia_meses = $request->contrato_permanencia_meses;
                    }

                    if($request->costo_reconexion){
                        $contrato->costo_reconexion = $request->costo_reconexion;
                    }else{
                        $contrato->costo_reconexion = 0;
                    }

                    ### DOCUMENTOS ADJUNTOS ###

                    if($request->referencia_a) {
                        $contrato->referencia_a = $request->referencia_a;
                        if($request->adjunto_a){
                            $file = $request->file('adjunto_a');
                            $nombre =  $file->getClientOriginalName();
                            Storage::disk('documentos')->put($nombre, \File::get($file));
                            $contrato->adjunto_a = $nombre;
                        }
                    }
                    if($request->referencia_b) {
                        $contrato->referencia_b = $request->referencia_b;
                        if($request->adjunto_b){
                            $file = $request->file('adjunto_b');
                            $nombre =  $file->getClientOriginalName();
                            Storage::disk('documentos')->put($nombre, \File::get($file));
                            $contrato->adjunto_b = $nombre;
                        }
                    }
                    if($request->referencia_c) {
                        $contrato->referencia_c = $request->referencia_c;
                        if($request->adjunto_c){
                            $file = $request->file('adjunto_c');
                            $nombre =  $file->getClientOriginalName();
                            Storage::disk('documentos')->put($nombre, \File::get($file));
                            $contrato->adjunto_c = $nombre;
                        }
                    }
                    if($request->referencia_d) {
                        $contrato->referencia_d = $request->referencia_d;
                        if($request->adjunto_d){
                            $file = $request->file('adjunto_d');
                            $nombre =  $file->getClientOriginalName();
                            Storage::disk('documentos')->put($nombre, \File::get($file));
                            $contrato->adjunto_d = $nombre;
                        }
                    }

                    ## Otro tipo de servicio ingresa tenga o no tenga mk ##
                    if($request->servicio_otro){
                        $contrato->servicio_otro = $request->servicio_otro;
                        $contrato->save();
                    }

                    ### DOCUMENTOS ADJUNTOS ###

                    $contrato->save();

                    /*REGISTRO DEL LOG*/
                    if(!is_null($descripcion)){
                        $movimiento = new MovimientoLOG;
                        $movimiento->contrato    = $id;
                        $movimiento->modulo      = 5;
                        $movimiento->descripcion = $descripcion;
                        $movimiento->created_by  = Auth::user()->id;
                        $movimiento->empresa     = Auth::user()->empresa;
                        $movimiento->save();
                    }

                    $mensaje='SE HA MODIFICADO EL CONTRATO DE SERVICIOS SATISFACTORIAMENTE';
                    return redirect('empresa/contratos/'.$id)->with('success', $mensaje);
                }else{
                    return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA SIDO ACTUALIZADO');
                }
            }else{
                $contrato->servicio             = $this->normaliza($servicio).'-'.$request->nro;
                $contrato->grupo_corte          = $request->grupo_corte;
                $contrato->facturacion          = $request->facturacion;
                $contrato->latitude             = $request->latitude;
                $contrato->longitude            = $request->longitude;
                $contrato->contrato_permanencia = $request->contrato_permanencia;
                $contrato->servicio_tv          = $request->servicio_tv;
                $contrato->fecha_suspension     = $request->fecha_suspension;
                $contrato->descuento            = $request->descuento;
                $contrato->vendedor             = $request->vendedor;
                $contrato->canal                = $request->canal;
                $contrato->nro                  = $request->nro;
                $contrato->address_street       = $request->address_street;
                $contrato->tipo_contrato        = $request->tipo_contrato;
                $contrato->observaciones           = $request->observaciones;

                if($request->factura_individual){
                    $contrato->factura_individual   = $request->factura_individual;
                }

                if($request->oficina){
                    $contrato->oficina = $request->oficina;
                }

                if($request->contrato_permanencia_meses){
                    $contrato->contrato_permanencia_meses = $request->contrato_permanencia_meses;
                }

                if($request->costo_reconexion){
                    $contrato->costo_reconexion = $request->costo_reconexion;
                }else{
                    $contrato->costo_reconexion = 0;
                }

                ### DOCUMENTOS ADJUNTOS ###
                if($request->referencia_a) {
                    $contrato->referencia_a = $request->referencia_a;
                    if($request->adjunto_a){
                        $file = $request->file('adjunto_a');
                        $nombre =  $file->getClientOriginalName();
                        Storage::disk('documentos')->put($nombre, \File::get($file));
                        $contrato->adjunto_a = $nombre;
                    }
                }
                if($request->referencia_b) {
                    $contrato->referencia_b = $request->referencia_b;
                    if($request->adjunto_b){
                        $file = $request->file('adjunto_b');
                        $nombre =  $file->getClientOriginalName();
                        Storage::disk('documentos')->put($nombre, \File::get($file));
                        $contrato->adjunto_b = $nombre;
                    }
                }
                if($request->referencia_c) {
                    $contrato->referencia_c = $request->referencia_c;
                    if($request->adjunto_c){
                        $file = $request->file('adjunto_c');
                        $nombre =  $file->getClientOriginalName();
                        Storage::disk('documentos')->put($nombre, \File::get($file));
                        $contrato->adjunto_c = $nombre;
                    }
                }
                if($request->referencia_d) {
                    $contrato->referencia_d = $request->referencia_d;
                    if($request->adjunto_d){
                        $file = $request->file('adjunto_d');
                        $nombre =  $file->getClientOriginalName();
                        Storage::disk('documentos')->put($nombre, \File::get($file));
                        $contrato->adjunto_d = $nombre;
                    }
                }
                ### DOCUMENTOS ADJUNTOS ###

                $contrato->creador = Auth::user()->nombres;
                $contrato->save();

                return redirect('empresa/contratos/'.$contrato->id)->with('success', 'SE HA ACTUALIZADO SATISFACTORIAMENTE EL CONTRATO DE SERVICIOS');
            }
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['middel' => true]);
        $inventario = false;

        $contrato = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->select('contracts.*', 'contracts.status as cs_status', 'c.nombre', 'c.apellido1', 'c.apellido2', 'c.nit', 'c.celular', 'c.telefono1', 'c.direccion', 'c.barrio', 'c.email', 'c.id as id_cliente', 'contracts.marca_router', 'contracts.modelo_router', 'contracts.marca_antena', 'contracts.modelo_antena', 'contracts.ip', 'contracts.grupo_corte', 'contracts.adjunto_a', 'contracts.referencia_a', 'contracts.adjunto_b', 'contracts.referencia_b', 'contracts.adjunto_c', 'contracts.referencia_c', 'contracts.adjunto_d', 'contracts.referencia_d', 'contracts.simple_queue', 'contracts.latitude', 'contracts.longitude', 'contracts.servicio_tv', 'contracts.contrato_permanencia', 'contracts.contrato_permanencia_meses', 'contracts.serial_onu', 'contracts.descuento', 'contracts.vendedor', 'contracts.canal', 'contracts.address_street', 'contracts.tecnologia', 'contracts.costo_reconexion', 'contracts.tipo_contrato', 'contracts.observaciones')->where('contracts.id', $id)->first();

        if($contrato) {
            if($contrato->servicio_tv){
                $inventario =Inventario::where('id', $contrato->servicio_tv)->where('empresa',Auth::user()->empresa)->first();
            }
            view()->share(['icon'=>'fas fa-file-contract', 'title' => 'Detalles Contrato: '.$contrato->nro]);
            return view('contratos.show')->with(compact('contrato', 'inventario'));
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function destroy($id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        if ($contrato) {
            if($contrato->server_configuration_id){
                $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();
                $API = new RouterosAPI();
                $API->port = $mikrotik->puerto_api;
                //$API->debug = true;

                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    if($contrato->conexion == 1){
                        //OBTENEMOS AL CONTRATO MK
                        $mk_user = $API->comm("/ppp/secret/getall", array(
                            "?remote-address" => $contrato->ip,
                            )
                        );

                        if($mk_user){
                            // REMOVEMOS EL SECRET
                            $API->comm("/ppp/secret/remove", array(
                                ".id" => $mk_user[0][".id"],
                                )
                            );
                        }

                        //OBTENEMOS EL ID DEL NOMBRE DEL CLIENTE
                        $id_simple = $API->comm("/queue/simple/getall", array(
                            "?target" => $contrato->ip.'/32'
                            )
                        );

                        if($id_simple){
                            // REMOVEMOS LA COLA SIMPLE
                            $API->comm("/queue/simple/remove", array(
                                ".id" => $id_simple[0][".id"],
                                )
                            );
                        }
                    }

                    if($contrato->conexion == 2){
                        $name = $API->comm("/ip/dhcp-server/lease/getall", array(
                                "?address" => $contrato->ip,
                            )
                        );

                        if($name){
                            // REMOVEMOS EL IP DHCP
                            $API->comm("/ip/dhcp-server/lease/remove", array(
                                ".id" => $name[0][".id"],
                                )
                            );
                        }

                        //OBTENEMOS EL ID DEL NOMBRE DEL CLIENTE
                        $id_simple = $API->comm("/queue/simple/getall", array(
                            "?target" => $contrato->ip.'/32'
                            )
                        );
                        // REMOVEMOS LA COLA SIMPLE
                        if($id_simple){
                            $API->comm("/queue/simple/remove", array(
                                ".id" => $id_simple[0][".id"],
                                )
                            );
                        }
                    }

                    if($contrato->conexion == 3){
                        //OBTENEMOS AL CONTRATO MK
                        $mk_user = $API->comm("/ip/arp/getall", array(
                            "?address" => $contrato->ip,
                            )
                        );
                        if($mk_user){
                            // REMOVEMOS EL IP ARP
                            $API->comm("/ip/arp/remove", array(
                                ".id" => $mk_user[0][".id"],
                                )
                            );
                        }
                        //OBTENEMOS EL ID DEL NOMBRE DEL CLIENTE
                        $id_simple = $API->comm("/queue/simple/getall", array(
                            "?target" => $contrato->ip.'/32'
                            )
                        );
                        // REMOVEMOS LA COLA SIMPLE
                        if($id_simple){
                            $API->comm("/queue/simple/remove", array(
                                ".id" => $id_simple[0][".id"],
                                )
                            );
                        }

                        if($contrato->ip_new){
                            $mk_user = $API->comm("/ip/arp/getall", array(
                                "?comment" => $contrato->servicio.'-'.$contrato->nro,
                                )
                            );

                            if($mk_user){
                                // REMOVEMOS EL IP ARP
                                $API->comm("/ip/arp/remove", array(
                                    ".id" => $mk_user[0][".id"],
                                    )
                                );
                            }
                            //OBTENEMOS EL ID DEL NOMBRE DEL CLIENTE
                            $id_simple = $API->comm("/queue/simple/getall", array(
                                "?target" => $contrato->ip_new.'/32'
                                )
                            );
                            // REMOVEMOS LA COLA SIMPLE
                            if($id_simple){
                                $API->comm("/queue/simple/remove", array(
                                    ".id" => $id_simple[0][".id"],
                                    )
                                );
                            }
                        }
                    }

                    $API->write('/ip/firewall/address-list/print', TRUE);
                    $ARRAYS = $API->read();

                    $API->write('/ip/firewall/address-list/print', false);
                    $API->write('?address='.$contrato->ip, false);
                    $API->write('=.proplist=.id');
                    $ARRAYS = $API->read();

                    if(count($ARRAYS)>0){
                        //REMOVEMOS EL ID DE LA ADDRESS LIST
                        $API->write('/ip/firewall/address-list/remove', false);
                        $API->write('=.id='.$ARRAYS[0]['.id']);
                        $READ = $API->read();
                    }

                    $API->disconnect();
                    Ping::where('contrato', $contrato->id)->delete();

                    $cliente = Contacto::find($contrato->client_id);
                    $cliente->fecha_contrato = Carbon::now();
                    $cliente->save();
                    $contrato->delete();

                    $mensaje='SE HA ELIMINADO EL CONTRATO DE SERVICIOS SATISFACTORIAMENTE';
                    return redirect('empresa/contratos')->with('success', $mensaje);
                } else {
                    $mensaje='NO SE HA PODIDO ELIMINAR EL CONTRATO DE SERVICIOS';
                    return redirect('empresa/contratos')->with('danger', $mensaje);
                }
            }else{
                $cliente = Contacto::find($contrato->client_id);
                $cliente->fecha_contrato = Carbon::now();
                $cliente->save();
                $contrato->delete();
                $mensaje='SE HA ELIMINADO EL CONTRATO DE SERVICIOS SATISFACTORIAMENTE';
                return redirect('empresa/contratos')->with('success', $mensaje);
            }
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function destroy_to_networksoft($id){
        $contrato = Contrato::find($id);
        if ($contrato) {
            Ping::where('contrato', $contrato->id)->delete();
            $contrato->delete();
            $mensaje = 'SE HA ELIMINADO EL CONTRATO DE SERVICIOS SATISFACTORIAMENTE';
            return redirect('empresa/contratos')->with('success', $mensaje);
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function state($id){

        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

        //$API->debug = true;
        if($contrato){
                if($contrato->plan_id){
                    $API = new RouterosAPI();
                    $API->port = $mikrotik->puerto_api;
                    if ($contrato) {
                        if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                            $API->write('/ip/firewall/address-list/print', TRUE);
                            $ARRAYS = $API->read();
                            if($contrato->state == 'enabled'){
                                #AGREGAMOS A MOROSOS#
                                $API->comm("/ip/firewall/address-list/add", array(
                                    "address" => $contrato->ip,
                                    "comment" => $contrato->servicio,
                                    "list" => 'morosos'
                                    )
                                );

                                #AGREGAMOS A MOROSOS#
                                #ELIMINAMOS DE IP_AUTORIZADAS#
                                $API->write('/ip/firewall/address-list/print', false);
                                $API->write('?address='.$contrato->ip, false);
                                $API->write("?list=ips_autorizadas",false);
                                $API->write('=.proplist=.id');
                                $ARRAYS = $API->read();
                                if(count($ARRAYS)>0){
                                    $API->write('/ip/firewall/address-list/remove', false);
                                    $API->write('=.id='.$ARRAYS[0]['.id']);
                                    $READ = $API->read();
                                }
                                #ELIMINAMOS DE IP_AUTORIZADAS#
                                $contrato->state = 'disabled';
                                $descripcion = '<i class="fas fa-check text-success"></i> <b>Cambio de Status</b> de Habilitado a Deshabilitado<br>';
                            }else{
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
                                $contrato->state = 'enabled';
                                $descripcion = '<i class="fas fa-check text-success"></i> <b>Cambio de Status</b> de Deshabilitado a Habilitado<br>';
                            }
                            $API->disconnect();
                            $contrato->save();
                            /*REGISTRO DEL LOG*/
                            $movimiento = new MovimientoLOG;
                            $movimiento->contrato    = $id;
                            $movimiento->modulo      = 5;
                            $movimiento->descripcion = $descripcion;
                            $movimiento->created_by  = Auth::user()->id;
                            $movimiento->empresa     = Auth::user()->empresa;
                            $movimiento->save();
                            //crm registro
                            $crm = new CRM();
                            $crm->cliente = $contrato->cliente()->id;
                            $crm->servidor = isset($contrato->server_configuration_id) ? $contrato->server_configuration_id : '';
                            $crm->grupo_corte = isset($contrato->grupo_corte) ? $contrato->grupo_corte : '';
                            $crm->estado = 0;
                            if($lastFact = $contrato->lastFactura()){
                                $crm->factura = $lastFact->id;
                            }
                            $crm->save();
                            $mensaje='EL CONTRATO NRO. '.$contrato->nro.' HA SIDO '.$contrato->status();
                            $type = 'success';
                        } else {
                            $mensaje='EL CONTRATO NRO. '.$contrato->nro.' NO HA PODIDO SER ACTUALIZADO';
                            $type = 'danger';
                        }
                        return back()->with($type, $mensaje);
                    }
                }else{

                    if($contrato->state == 'enabled'){
                        $contrato->state = 'disabled';
                    }else{
                        $contrato->state = 'enabled';
                    }

                    //crm registro
                    $crm = new CRM();
                    $crm->cliente = $contrato->cliente()->id;
                    $crm->servidor = isset($contrato->server_configuration_id) ? $contrato->server_configuration_id : '';
                    $crm->grupo_corte = isset($contrato->grupo_corte) ? $contrato->grupo_corte : '';
                    $crm->estado = 0;
                    if($lastFact = $contrato->lastFactura()){
                        $crm->factura = $lastFact->id;
                    }
                    $crm->save();

                    $contrato->update();

                    return back()->with('success', 'EL CONTRATO NRO. '.$contrato->nro.' HA SIDO '.$contrato->status());
                }
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function exportar(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Contratos";
        $titulosColumnas = array(
            'Nro',
            'Cliente',
            'Identificacion',
            'Celular',
            'Correo Electronico',
            'Direccion',
            'Barrio',
            'Corregimiento/Vereda',
            'Estrato',
            'Plan TV',
            'Plan Internet',
            'Servidor',
            'Direccion IP',
            'Direccion MAC',
            'Interfaz',
            'Serial ONU',
            'Estado',
            'Grupo de Corte',
            'Facturacion',
            'Costo Reconexion',
            'Tipo Contrato'
        );

        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific�1�7�1�7�1�7
        ->setTitle("Reporte Excel Contactos") // Titulo
        ->setSubject("Reporte Excel Contactos") //Asunto
        ->setDescription("Reporte de Contactos") //Descripci�1�7�1�7�1�7n
        ->setKeywords("reporte Contactos") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah�1�7�1�7�1�7 el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:L1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:L2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2','Fecha '.date('d-m-Y')); // Titulo del reporte

        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:R3')->applyFromArray($estilo);

        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:U3')->applyFromArray($estilo);

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
        $objPHPExcel->getActiveSheet()->getStyle('A3:U3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i=4;
        $letra=0;

        $contratos = Contrato::query()
            ->select(
                'contracts.*',
                'contactos.id as c_id',
                'contactos.nombre as c_nombre',
                'contactos.apellido1 as c_apellido1',
                'contactos.apellido2 as c_apellido2',
                'contactos.nit as c_nit',
                'contactos.celular as c_celular',
                'contactos.email as c_email',
                'contactos.barrio as c_barrio',
                'contactos.vereda as c_vereda',
                'contactos.direccion as c_direccion',
                'contactos.estrato as c_estrato',
            )
            ->join('contactos', 'contracts.client_id', '=', 'contactos.id')
            ->where('contracts.empresa', Auth::user()->empresa);

	    if($request->client_id != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.client_id', $request->client_id);
            });
        }
        if($request->plan != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.plan_id', $request->plan);
            });
        }
        if($request->ip != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.ip', 'like', "%{$request->ip}%");
            });
        }
        if($request->mac != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.mac_address', 'like', "%{$request->mac}%");
            });
        }
        if($request->grupo_cort != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.grupo_corte', $request->grupo_cort);
            });
        }
        if($request->state != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.state', $request->state);
            });
        }
        if($request->conexion_s != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.conexion', $request->conexion_s);
            });
        }
        if($request->server_configuration_id_s != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.server_configuration_id', $request->server_configuration_id_s);
            });
        }
        if($request->nodo_s != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.nodo', $request->nodo_s);
            });
        }
        if($request->ap_s != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.ap', $request->ap_s);
            });
        }
        if($request->direccion != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contactos.direccion', 'like', "%{$request->direccion}%");
            });
        }
        if($request->direccion_precisa != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.address_street', 'like', "%{$request->c_direccion_precisa}%");
                $query->orWhere('contactos.direccion', 'like', "%{$request->c_direccion_precisa}%");
            });
        }
        if($request->barrio != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contactos.barrio', 'like', "%{$request->barrio}%");
            });
        }
        if($request->celular != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contactos.celular', 'like', "%{$request->celular}%");
            });
        }
        if($request->email != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contactos.email', 'like', "%{$request->email}%");
            });
        }
        if($request->vendedor != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.vendedor', $request->vendedor);
            });
        }
        if($request->canal != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.canal', $request->canal);
            });
        }
        if($request->tecnologia_s != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.tecnologia', $request->tecnologia_s);
            });
        }
        if($request->facturacion_s != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.facturacion', $request->facturacion_s);
            });
        }
        if($request->desde != null){
            $contratos->where(function ($query) use ($request) {
                $query->whereDate('contracts.created_at', '>=', Carbon::parse($request->desde)->format('Y-m-d'));
            });
        }
        if($request->hasta != null){
            $contratos->where(function ($query) use ($request) {
                $query->whereDate('contracts.created_at', '<=', Carbon::parse($request->hasta)->format('Y-m-d'));
            });
        }
        if($request->tipo_contrato != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.tipo_contrato', $request->tipo_contrato);
            });
        }
        if($request->nro != null){
            $contratos->where(function ($query) use ($request) {
                $query->orWhere('contracts.nro', 'like', "%{$request->nro}%");
            });
        }

        $contratos = $contratos->where('contracts.status', 1)->get();

        foreach ($contratos as $contrato) {

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $contrato->nro)
                ->setCellValue($letras[1].$i, $contrato->c_nombre.' '.$contrato->c_apellido1.' '.$contrato->c_apellido2)
                ->setCellValue($letras[2].$i, $contrato->c_nit)
                ->setCellValue($letras[3].$i, $contrato->c_celular)
                ->setCellValue($letras[4].$i, $contrato->c_email)
                ->setCellValue($letras[5].$i, $contrato->c_direccion)
                ->setCellValue($letras[6].$i, $contrato->c_barrio)
                ->setCellValue($letras[7].$i, $contrato->c_vereda)
                ->setCellValue($letras[8].$i, $contrato->c_estrato)
                ->setCellValue($letras[9].$i, ($contrato->servicio_tv) ? $contrato->plan(true)->producto : '')
                ->setCellValue($letras[10].$i, ($contrato->plan_id) ? $contrato->plan()->name : '')
                ->setCellValue($letras[11].$i, ($contrato->server_configuration_id) ? $contrato->servidor()->nombre : '')
                ->setCellValue($letras[12].$i, $contrato->ip)
                ->setCellValue($letras[13].$i, $contrato->mac_address)
                ->setCellValue($letras[14].$i, $contrato->interfaz)
                ->setCellValue($letras[15].$i, $contrato->serial_onu)
                ->setCellValue($letras[16].$i, $contrato->status())
                ->setCellValue($letras[17].$i, $contrato->grupo_corte('true'))
                ->setCellValue($letras[18].$i, $contrato->facturacion())
                ->setCellValue($letras[19].$i, $contrato->costo_reconexion)
                ->setCellValue($letras[20].$i, ucfirst($contrato->tipo_contrato));
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:U'.$i)->applyFromArray($estilo);

        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Lista de Contratos');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Contratos.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function grafica($id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        if ($contrato) {
            view()->share(['icon'=>'fas fa-chart-area', 'title' => 'Gráfica de Conexión | Contrato: '.$contrato->nro]);
            return view('contratos.grafica')->with(compact('contrato'));
        }
        return back()->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function graficajson($id){
        $this->getAllPermissions(Auth::user()->id);

        $contrato=Contrato::find($id);
        $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

        $API = new RouterosAPI();
        $API->port = $mikrotik->puerto_api;
        //$API->debug = true;

        if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
            $rows = array(); $rows2 = array(); $Type=1; $Interface='ether1';
            if ($Type==0) {  // Interfaces
                $API->write("/interface/monitor-traffic",false);
                $API->write("=interface=".$contrato->name_vlan,false);
                $API->write("=once=",true);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
                if(count($ARRAY)>0){
                    $rx = ($ARRAY[0]["rx-bits-per-second"]);
                    $tx = ($ARRAY[0]["tx-bits-per-second"]);
					$rows['name'] = 'Tx';
					$rows['data'][] = $tx;
					$rows2['name'] = 'Rx';
					$rows2['data'][] = $rx;
				}else{
					echo $ARRAY['!trap'][0]['message'];
				}
			}else if($Type==1){ //  Queues
			    $API->write("/queue/simple/print",false);
			    $API->write("=stats",false);
			    $API->write("?target=".$contrato->ip.'/32',true);
			    $READ = $API->read(false);
			    $ARRAY = $API->parseResponse($READ);
			    if(count($ARRAY)>0){
					$rx = explode("/",$ARRAY[0]["rate"])[0];
					$tx = explode("/",$ARRAY[0]["rate"])[1];
					$rows['name'] = 'Tx';
					$rows['data'][] = $tx;
					$rows2['name'] = 'Rx';
					$rows2['data'][] = $rx;
				}else{
                    return response()->json([
                        'success' => false,
                        'icon'    => 'error',
                        'title'   => 'ERROR',
                        'text'    => 'NO SE HA PODIDO REALIZAR LA GRÁFICA'
                    ]);
				}
			}

			$ConnectedFlag = true;

			if ($ConnectedFlag) {
			    $result = array();array_push($result,$rows);
			    array_push($result,$rows2);
			    echo json_encode($result, JSON_NUMERIC_CHECK);
			}
			$API->disconnect();
        }
    }

    public function conexion($id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        if ($contrato) {
            if(!$contrato->ip){
                return back()->with('danger', 'EL CONTRATO NO POSEE DIRECCIÓN IP ASOCIADA');
            }

            /*REGISTRO DEL LOG*/
            $movimiento = new MovimientoLOG;
            $movimiento->contrato    = $id;
            $movimiento->modulo      = 5;
            $movimiento->descripcion = '<i class="fas fa-check text-success"></i> <b>PROCESO DE PING REALIZADO</b><br>';;
            $movimiento->created_by  = Auth::user()->id;
            $movimiento->empresa     = Auth::user()->empresa;
            $movimiento->save();

            view()->share(['icon'=>'fas fa-plug', 'title' => 'Ping de Conexión: '.$contrato->nro]);
            return view('contratos.ping')->with(compact('contrato'));
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function ping_nuevo($id){
        $contrato = Contrato::find($id);

        if ($contrato) {
            if(!$contrato->ip){
                return response()->json([
                    'success' => false,
                    'icon'    => 'error',
                    'title'   => 'ERROR',
                    'text'    => 'EL CONTRATO NO POSEE DIRECCIÓN IP ASOCIADA'
                ]);
            }

            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            //$API->debug = true;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                // PING
                $API->write("/ping",false);
                $API->write("=address=".$contrato->ip,false);
                $API->write("=count=1",true);
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);

                if(count($ARRAY)>0){
                    if($ARRAY[0]["received"]!=$ARRAY[0]["sent"]){
                        $data = [
                            'contrato' => $contrato->id,
                            'ip' => $contrato->ip,
                            'fecha' => Carbon::parse(now())->format('Y-m-d')
                        ];

                        $ping = Ping::updateOrCreate(
                            ['contrato' => $contrato->id],
                            $data
                        );
                        return response()->json([
                            'success' => false,
                            'icon'    => 'error',
                            'title'   => 'ERROR',
                            'text'    => 'SE HA REALIZADO EL PING PERO NO SE TIENE CONEXIÓN (ERROR: '.strtoupper($ARRAY[0]["status"]).')',
                            'data'    => $ARRAY
                        ]);
					}else{
					    Ping::where('contrato', $contrato->id)->delete();
					    return response()->json([
                            'success' => true,
                            'icon'    => 'success',
                            'title'   => 'PROCESO EXITOSO',
                            'text'    => 'SE HA REALIZADO EL PING DE CONEXIÓN DE MANERA EXITOSA',
                            'data'    => $ARRAY
                        ]);
					}
                }else{
                    return response()->json([
                        'success' => false,
                        'icon'    => 'error',
                        'title'   => 'ERROR',
                        'text'    => 'SE HA REALIZADO EL PING PERO NO SE TIENE CONEXIÓN',
                        'data'    => $ARRAY
                    ]);
                }
                $API->disconnect();
            } else {
                $data = [
                    'contrato' => $contrato->id,
                    'ip' => $contrato->ip,
                    'fecha' => Carbon::parse(now())->format('Y-m-d')
                ];

                $ping = Ping::updateOrCreate(
                    ['contrato' => $contrato->id],
                    $data
                );

                return response()->json([
                    'success' => false,
                    'icon'    => 'error',
                    'title'   => 'ERROR',
                    'text'    => 'NO SE HA PODIDO REALIZAR EL PING. VERIFIQUE LA CONEXIÓN DE LA MIKROTIK <b><i><u>'.$mikrotik->nombre.'</u></i></b>'
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'icon'    => 'error',
            'title'   => 'ERROR',
            'text'    => 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO'
        ]);
    }

    public function destroy_to_mk($id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        if ($contrato) {
            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            //$API->debug = true;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                if($contrato->conexion == 1){
                    //OBTENEMOS AL CONTRATO MK
                    $mk_user = $API->comm("/ppp/secret/getall", array(
                        "?comment" => $contrato->id,
                        )
                    );
                    // REMOVEMOS EL SECRET
                    $API->comm("/ppp/secret/remove", array(
                        ".id" => $mk_user[0][".id"],
                        )
                    );

                    //OBTENEMOS EL ID DEL NOMBRE DEL CLIENTE
                    $id_simple = $API->comm("/queue/simple/getall", array(
                        "?comment" => $contrato->id,
                        )
                    );
                    // REMOVEMOS LA COLA SIMPLE
                    $API->comm("/queue/simple/remove", array(
                        ".id" => $id_simple[0][".id"],
                        )
                    );
                }

                if($contrato->conexion == 2){

                }

                if($contrato->conexion == 3){
                    //OBTENEMOS AL CONTRATO MK
                    $mk_user = $API->comm("/ip/arp/getall", array(
                        "?comment" => $contrato->servicio,
                        )
                    );

                    if($mk_user){
                        // REMOVEMOS EL IP ARP
                        $API->comm("/ip/arp/remove", array(
                            ".id" => $mk_user[0][".id"],
                            )
                        );
                        //OBTENEMOS EL ID DEL NOMBRE DEL CLIENTE
                        $id_simple = $API->comm("/queue/simple/getall", array(
                            "?comment" => $contrato->id,
                            )
                        );
                        // REMOVEMOS LA COLA SIMPLE
                        $API->comm("/queue/simple/remove", array(
                            ".id" => $id_simple[0][".id"],
                            )
                        );
                    }

                }

                $API->disconnect();

                $mensaje='SE HA ELIMINADO EL CONTRATO DEL MIKROTIK';
                return redirect('empresa/contratos')->with('success', $mensaje);
            } else {
                $mensaje='NO SE HA PODIDO ELIMINAR EL CONTRATO DE SERVICIOS';
                return redirect('empresa/contratos')->with('danger', $mensaje);
            }
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function log($id){

        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        $contrato_log = DB::table('log_movimientos')->where('contrato', $contrato->nro)->get();

        if ($contrato) {
            view()->share(['icon'=>'fas fa-chart-area', 'title' => 'Log | Contrato: '.$contrato->nro]);
            return view('contratos.log')->with(compact('contrato','contrato_log'));
        } else {
            $mensaje='NO SE HA PODIDO OBTENER EL LOG DEL CONTRATO DE SERVICIOS';
            return redirect('empresa/contratos/'.$contrato->id)->with('danger', $mensaje);
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function logs(Request $request, $contrato){
        $modoLectura = auth()->user()->modo_lectura();
        $contratos = MovimientoLOG::query();
        $contratos->where('log_movimientos.contrato', $contrato);

        return datatables()->eloquent($contratos)
            ->editColumn('created_at', function (MovimientoLOG $contrato) {
                return date('d-m-Y g:i:s A', strtotime($contrato->created_at));
            })
            ->editColumn('created_by', function (MovimientoLOG $contrato) {
                return $contrato->created_by();
            })
            ->editColumn('descripcion', function (MovimientoLOG $contrato) {
                return $contrato->descripcion;
            })
            ->rawColumns(['created_at', 'created_by', 'descripcion'])
            ->toJson();
    }

    public function grafica_consumo($id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        if ($contrato) {
            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            //$API->debug = true;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write("/tool/graph/interface/print",true);
                $ARRAYS = $API->read();
                if(count($ARRAYS)>0){
                    view()->share(['icon'=>'', 'title' => 'Gráfica de Consumo | Contrato: '.$contrato->nro]);
                    $url = $mikrotik->ip.':'.$mikrotik->puerto_web.'/graphs/queue/'.str_replace(' ','%20',$contrato->servicio);
                    return view('contratos.grafica-consumo')->with(compact('contrato', 'url'));
                }
                return redirect('empresa/contratos/'.$contrato->id)->with('danger', 'EL SERVIDOR NO TIENE HABILITADA LA VISUALIZACIÓN DE LOS GRÁFICOS');
            }
        }
        return back()->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function eliminarAdjunto($id, $archivo){
        $contrato = Contrato::where('id', $id)->where('empresa',Auth::user()->empresa)->first();
        if($contrato){
            switch ($archivo) {
                case 'adjunto_a':
                    $contrato->adjunto_a = NULL;
                    $contrato->referencia_a = NULL;
                    break;
                case 'adjunto_b':
                    $contrato->adjunto_b = NULL;
                    $contrato->referencia_b = NULL;
                    break;
                case 'adjunto_c':
                    $contrato->adjunto_c = NULL;
                    $contrato->referencia_c = NULL;
                    break;
                case 'adjunto_d':
                    $contrato->adjunto_d = NULL;
                    $contrato->referencia_d = NULL;
                    break;
                default:
                    break;
            }
            $contrato->save();
            return response()->json([
                'success' => true,
                'type'    => 'success',
                'title'   => 'Archivo Adjunto Eliminado',
                'text'    => ''
            ]);
        }
        return response()->json([
                'success' => false,
                'type'    => 'error',
                'title'   => 'Archivo no eliminado',
                'text'    => 'Inténtelo Nuevamente'
            ]);
    }

    public function enviar_mk(Request $request, $id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        if ($contrato) {
            $plan = PlanesVelocidad::where('id', $contrato->plan_id)->first();
            $mikrotik = Mikrotik::where('id', $plan->mikrotik)->first();
            $cliente = $contrato->cliente();
            $servicio = $cliente->nombre.' '. $cliente->apellido1.' '. $cliente->apellido2;

            if ($mikrotik) {
                $API = new RouterosAPI();
                $API->port = $mikrotik->puerto_api;
                //$API->debug = true;

                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    $rate_limit      = '';
                    $priority        = $plan->prioridad;
                    $burst_limit     = (strlen($plan->burst_limit_subida)>1) ? $plan->burst_limit_subida.'/'.$plan->burst_limit_bajada : '';
                    $burst_threshold = (strlen($plan->burst_threshold_subida)>1) ? $plan->burst_threshold_subida.'/'.$plan->burst_threshold_bajada : '';
                    $burst_time      = ($plan->burst_time_subida) ? $plan->burst_time_subida.'/'.$plan->burst_time_bajada: '';
                    $limit_at        = (strlen($plan->limit_at_subida)>1) ? $plan->limit_at_subida.'/'.$plan->limit_at_bajada : '';
                    $max_limit       = $plan->upload.'/'.$plan->download;

                    if($max_limit){ $rate_limit .= $max_limit; }
                    if(strlen($burst_limit)>3){ $rate_limit .= ' '.$burst_limit; }
                    if(strlen($burst_threshold)>3){ $rate_limit .= ' '.$burst_threshold; }
                    if(strlen($burst_time)>3){ $rate_limit .= ' '.$burst_time; }
                    if($priority){ $rate_limit .= ' '.$priority; }
                    if($limit_at){ $rate_limit .= ' '.$limit_at; }

                    /*PPPOE*/
                    if($contrato->conexion == 1){
                        $API->comm("/ppp/secret/add", array(
                            "name"           => $contrato->usuario,
                            "password"       => $contrato->password,
                            "profile"        => 'default',
                            "local-address"  => $contrato->ip,
                            "remote-address" => $contrato->ip,
                            "service"        => 'pppoe',
                            "comment"        => $this->normaliza($servicio).'-'.$contrato->nro
                            )
                        );

                        $getall = $API->comm("/ppp/secret/getall", array(
                            "?local-address" => $contrato->ip
                            )
                        );
                    }

                    /*DHCP*/
                    if($contrato->conexion == 2){
                        if(isset($plan->dhcp_server)){
                            if($contrato->simple_queue == 'dinamica'){
                                $API->comm("/ip/dhcp-server/set\n=name=".$plan->dhcp_server."\n=address-pool=static-only\n=parent-queue=".$plan->parenta);
                                $API->comm("/ip/dhcp-server/lease/add", array(
                                    "comment"     => $this->normaliza($servicio).'-'.$contrato->nro,
                                    "address"     => $contrato->ip,
                                    "server"      => $plan->dhcp_server,
                                    "mac-address" => $contrato->mac_address,
                                    "rate-limit"  => $rate_limit
                                    )
                                );
                            }elseif ($contrato->simple_queue == 'estatica') {
                                $API->comm("/ip/dhcp-server/lease/add", array(
                                    "comment"     => $this->normaliza($servicio).'-'.$contrato->nro,
                                    "address"     => $contrato->ip,
                                    "server"      => $plan->dhcp_server,
                                    "mac-address" => $contrato->mac_address
                                    )
                                );
                            }

                            $getall = $API->comm("/ip/dhcp-server/lease/getall", array(
                                "?address" => $contrato->ip
                                )
                            );
                        }else{
                            $mensaje='NO SE HA PODIDO EDITAR EL CONTRATO DE SERVICIOS, NO EXISTE UN SERVIDOR DHCP DEFINIDO PARA EL PLAN '.$plan->name;
                            return redirect('empresa/contratos')->with('danger', $mensaje);
                        }
                    }

                    /*IP ESTÁTICA*/
                    if($contrato->conexion == 3){
                        if($mikrotik->amarre_mac == 1){
                            $API->comm("/ip/arp/add", array(
                                "comment"     => $this->normaliza($servicio).'-'.$contrato->nro,
                                "address"     => $contrato->ip,
                                "interface"   => $contrato->interfaz,
                                "mac-address" => $contrato->mac_address
                                )
                            );

                            $getall = $API->comm("/ip/arp/getall", array(
                                "?address" => $contrato->ip
                                )
                            );
                        }
                    }

                    /*VLAN*/
                    if($contrato->conexion == 4){

                    }

                    //if($getall){
                        $registro = true;
                        $queue = $API->comm("/queue/simple/getall", array(
                            "?target" => $contrato->ip.'/32'
                            )
                        );

                        if($queue){
                            $API->comm("/queue/simple/set", array(
                                ".id"             => $queue[0][".id"],
                                "target"          => $contrato->ip,
                                "max-limit"       => $plan->upload.'/'.$plan->download,
                                "burst-limit"     => $burst_limit,
                                "burst-threshold" => $burst_threshold,
                                "burst-time"      => $burst_time,
                                "priority"        => $priority,
                                "limit-at"        => $limit_at
                                )
                            );
                        }else{
                            $API->comm("/queue/simple/add", array(
                                "name"            => $this->normaliza($servicio).'-'.$contrato->nro,
                                "target"          => $contrato->ip,
                                "max-limit"       => $plan->upload.'/'.$plan->download,
                                "burst-limit"     => $burst_limit,
                                "burst-threshold" => $burst_threshold,
                                "burst-time"      => $burst_time,
                                "priority"        => $priority,
                                "limit-at"        => $limit_at
                                )
                            );
                        }
                    //}
                    #AGREGAMOS A IP_AUTORIZADAS#
                    $API->comm("/ip/firewall/address-list/add", array(
                        "address" => $contrato->ip,
                        "list" => 'ips_autorizadas'
                        )
                    );
                    #AGREGAMOS A IP_AUTORIZADAS#
                }

                $API->disconnect();

                if($registro){
                    $contrato->mk = 1;
                    $contrato->state = 'enabled';
                    $contrato->servicio = $this->normaliza($servicio).'-'.$contrato->nro;
                    $contrato->save();
                    $mensaje='SE HA REGISTRADO SATISFACTORIAMENTE EN EL MIKROTIK EL CONTRATO DE SERVICIOS';
                    return redirect('empresa/contratos/'.$id)->with('success', $mensaje);
                }else{
                    $mensaje='NO SE HA PODIDO REGISTRAR EL CONTRATO EN LA MIKROTIK';
                    return redirect('empresa/contratos')->with('danger', $mensaje);
                }
            }
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function carga_adjuntos(Request $request, $id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);

        if ($contrato) {
            ### DOCUMENTOS ADJUNTOS ###
            if($request->referencia_a) {
                $contrato->referencia_a = $request->referencia_a;
                if($request->adjunto_a){
                    $file = $request->file('adjunto_a');
                    $nombre =  $file->getClientOriginalName();
                    Storage::disk('documentos')->put($nombre, \File::get($file));
                    $contrato->adjunto_a = $nombre;
                }
            }
            if($request->referencia_b) {
                $contrato->referencia_b = $request->referencia_b;
                if($request->adjunto_b){
                    $file = $request->file('adjunto_b');
                    $nombre =  $file->getClientOriginalName();
                    Storage::disk('documentos')->put($nombre, \File::get($file));
                    $contrato->adjunto_b = $nombre;
                }
            }
            if($request->referencia_c) {
                $contrato->referencia_c = $request->referencia_c;
                if($request->adjunto_c){
                    $file = $request->file('adjunto_c');
                    $nombre =  $file->getClientOriginalName();
                    Storage::disk('documentos')->put($nombre, \File::get($file));
                    $contrato->adjunto_c = $nombre;
                }
            }
            if($request->referencia_d) {
                $contrato->referencia_d = $request->referencia_d;
                if($request->adjunto_d){
                    $file = $request->file('adjunto_d');
                    $nombre =  $file->getClientOriginalName();
                    Storage::disk('documentos')->put($nombre, \File::get($file));
                    $contrato->adjunto_d = $nombre;
                }
            }

            ### DOCUMENTOS ADJUNTOS ###

            $contrato->save();
            return redirect('empresa/contactos/'.$request->contacto_id)->with('success', 'SE HA CARGADO SATISFACTORIAMENTE LOS ARCHIVOS ADJUNTOS');
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function state_lote($contratos, $state){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $contratos = explode(",", $contratos);

        for ($i=0; $i < count($contratos) ; $i++) {
            $contrato=Contrato::find($contratos[$i]);

            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            //$API->debug = true;

            if ($contrato) {
                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {

                    $API->write('/ip/firewall/address-list/print', TRUE);
                    $ARRAYS = $API->read();

                    if($state == 'disabled'){
                        #AGREGAMOS A MOROSOS#
                        $API->comm("/ip/firewall/address-list/add", array(
                            "address" => $contrato->ip,
                            "comment" => $contrato->servicio,
                            "list" => 'morosos'
                            )
                        );
                        #AGREGAMOS A MOROSOS#

                        #ELIMINAMOS DE IP_AUTORIZADAS#
                        $API->write('/ip/firewall/address-list/print', false);
                        $API->write('?address='.$contrato->ip, false);
                        $API->write("?list=ips_autorizadas",false);
                        $API->write('=.proplist=.id');
                        $ARRAYS = $API->read();

                        if(count($ARRAYS)>0){
                            $API->write('/ip/firewall/address-list/remove', false);
                            $API->write('=.id='.$ARRAYS[0]['.id']);
                            $READ = $API->read();
                        }
                        #ELIMINAMOS DE IP_AUTORIZADAS#

                        $contrato->state = $state;
                    }else{
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

                        $contrato->state = $state;
                    }
                    $API->disconnect();
                    $contrato->save();

                    $succ++;
                } else {
                    $fail++;
                }
            }
        }

        return response()->json([
            'success'   => true,
            'fallidos'  => $fail,
            'correctos' => $succ,
            'state'     => $state
        ]);
    }

    public function enviar_mk_lote($contratos){

        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0; $registro = false; $contracts_fallidos = ''; $contracts_correctos = '';

        $contratos = explode(",", $contratos);

        for ($i=0; $i < count($contratos) ; $i++) {

            if($i==0){
                $microtik = str_replace('m', '', $contratos[$i]);
            }else{
                $contrato = Contrato::find($contratos[$i]);

                if ($contrato) {

                    if($contrato->mk==1){
                        $plan = PlanesVelocidad::where('id', $contrato->plan_id)->first();
                        $mikrotik = Mikrotik::where('id', $microtik)->first();
                        $mikrotik_plan = ($plan) ? Mikrotik::where('id', $plan->mikrotik)->first() : false;


                        $cliente = $contrato->cliente();
                        $servicio = $cliente->nombre.' '. $cliente->apellido1.' '. $cliente->apellido2;

                        if ($mikrotik) {

                            $API = new RouterosAPI();
                            $API->port = $mikrotik->puerto_api;
                            //$API->debug = true;

                            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                                 ## ELIMINAMOS DE MK ##

                    if($contrato->conexion == 1){
                        //OBTENEMOS AL CONTRATO MK
                        $mk_user = $API->comm("/ppp/secret/getall", array(
                            "?remote-address" => $contrato->ip,
                            )
                        );
                        if($mk_user){
                            // REMOVEMOS EL SECRET
                            $API->comm("/ppp/secret/remove", array(
                                ".id" => $mk_user[0][".id"],
                                )
                            );
                        }
                    }

                    if($contrato->conexion == 2){
                        $name = $API->comm("/ip/dhcp-server/lease/getall", array(
                            "?address" => $contrato->ip
                            )
                        );
                        if($name){
                            // REMOVEMOS EL IP DHCP
                            $API->comm("/ip/dhcp-server/lease/remove", array(
                                ".id" => $name[0][".id"],
                                )
                            );
                        }
                    }

                    if($contrato->conexion == 3){
                        //OBTENEMOS AL CONTRATO MK
                        $mk_user = $API->comm("/ip/arp/getall", array(
                            "?address" => $contrato->ip // IP DEL CLIENTE
                            )
                        );
                        if($mk_user){
                            // REMOVEMOS EL IP ARP
                            $API->comm("/ip/arp/remove", array(
                                ".id" => $mk_user[0][".id"],
                                )
                            );
                        }
                    }

                    #ELMINAMOS DEL QUEUE#
                    $queue = $API->comm("/queue/simple/getall", array(
                        "?target" => $contrato->ip.'/32'
                        )
                    );

                    if($queue){
                        $API->comm("/queue/simple/remove", array(
                            ".id" => $queue[0][".id"],
                            )
                        );
                    }
                    #ELMINAMOS DEL QUEUE#

                    #ELIMINAMOS DE IP_AUTORIZADAS#
                    $API->write('/ip/firewall/address-list/print', TRUE);
                    $ARRAYS = $API->read();

                    $API->write('/ip/firewall/address-list/print', false);
                    $API->write('?address='.$contrato->ip, false);
                    $API->write("?list=ips_autorizadas",false);
                    $API->write('=.proplist=.id');
                    $ARRAYS = $API->read();

                    if(count($ARRAYS)>0){
                        $API->write('/ip/firewall/address-list/remove', false);
                        $API->write('=.id='.$ARRAYS[0]['.id']);
                        $READ = $API->read();
                    }
                    #ELIMINAMOS DE IP_AUTORIZADAS#
                    ## ELIMINAMOS DE MK ##

                                $rate_limit      = '';
                                $priority        = $plan->prioridad;
                                $burst_limit     = (strlen($plan->burst_limit_subida)>1) ? $plan->burst_limit_subida.'/'.$plan->burst_limit_bajada : '';
                                $burst_threshold = (strlen($plan->burst_threshold_subida)>1) ? $plan->burst_threshold_subida.'/'.$plan->burst_threshold_bajada : '';
                                $burst_time      = ($plan->burst_time_subida) ? $plan->burst_time_subida.'/'.$plan->burst_time_bajada: '';
                                $limit_at        = (strlen($plan->limit_at_subida)>1) ? $plan->limit_at_subida.'/'.$plan->limit_at_bajada : '';
                                $max_limit       = $plan->upload.'/'.$plan->download;

                                if($max_limit){ $rate_limit .= $max_limit; }
                                if(strlen($burst_limit)>3){ $rate_limit .= ' '.$burst_limit; }
                                if(strlen($burst_threshold)>3){ $rate_limit .= ' '.$burst_threshold; }
                                if(strlen($burst_time)>3){ $rate_limit .= ' '.$burst_time; }
                                if($priority){ $rate_limit .= ' '.$priority; }
                                if($limit_at){ $rate_limit .= ' '.$limit_at; }

                                /*PPPOE*/
                                if($contrato->conexion == 1){
                                    $API->comm("/ppp/secret/add", array(
                                        "name"           => $contrato->usuario,
                                        "password"       => $contrato->password,
                                        "profile"        => 'default',
                                        "local-address"  => $contrato->ip,
                                        "remote-address" => $contrato->ip,
                                        "service"        => 'pppoe',
                                        "comment"        => $this->normaliza($servicio).'-'.$contrato->nro
                                        )
                                    );

                                    $getall = $API->comm("/ppp/secret/getall", array(
                                        "?local-address" => $contrato->ip
                                        )
                                    );
                                }

                                /*DHCP*/
                                if($contrato->conexion == 2){
                                    if(isset($plan->dhcp_server)){
                                        if($contrato->simple_queue == 'dinamica'){
                                            $API->comm("/ip/dhcp-server/set\n=name=".$plan->dhcp_server."\n=address-pool=static-only\n=parent-queue=".$plan->parenta);
                                            $API->comm("/ip/dhcp-server/lease/add", array(
                                                "comment"     => $this->normaliza($servicio).'-'.$contrato->nro,
                                                "address"     => $contrato->ip,
                                                "server"      => $plan->dhcp_server,
                                                "mac-address" => $contrato->mac_address,
                                                "rate-limit"  => $rate_limit
                                                )
                                            );
                                        }elseif ($contrato->simple_queue == 'estatica') {
                                            $API->comm("/ip/dhcp-server/lease/add", array(
                                                "comment"     => $this->normaliza($servicio).'-'.$contrato->nro,
                                                "address"     => $contrato->ip,
                                                "server"      => $plan->dhcp_server,
                                                "mac-address" => $contrato->mac_address
                                                )
                                            );
                                        }

                                        $getall = $API->comm("/ip/dhcp-server/lease/getall", array(
                                            "?address" => $contrato->ip
                                            )
                                        );
                                    }else{
                                        $mensaje='NO SE HA PODIDO EDITAR EL CONTRATO DE SERVICIOS, NO EXISTE UN SERVIDOR DHCP DEFINIDO PARA EL PLAN '.$plan->name;
                                        return redirect('empresa/contratos')->with('danger', $mensaje);
                                    }
                                }

                                /*IP ESTÁTICA*/
                                if($contrato->conexion == 3){
                                    if($mikrotik->amarre_mac == 1){
                                        $API->comm("/ip/arp/add", array(
                                            "comment"     => $this->normaliza($servicio).'-'.$contrato->nro,
                                            "address"     => $contrato->ip,
                                            "interface"   => $contrato->interfaz,
                                            "mac-address" => $contrato->mac_address
                                            )
                                        );

                                        $getall = $API->comm("/ip/arp/getall", array(
                                            "?address" => $contrato->ip
                                            )
                                        );
                                    }
                                }

                                /*VLAN*/
                                if($contrato->conexion == 4){

                                }

                                $registro = true;
                                $queue = $API->comm("/queue/simple/getall", array(
                                    "?target" => $contrato->ip.'/32'
                                    )
                                );

                                if($queue){
                                    $API->comm("/queue/simple/set", array(
                                        ".id"             => $queue[0][".id"],
                                        "target"          => $contrato->ip,
                                        "max-limit"       => $plan->upload.'/'.$plan->download,
                                        "burst-limit"     => $burst_limit,
                                        "burst-threshold" => $burst_threshold,
                                        "burst-time"      => $burst_time,
                                        "priority"        => $priority,
                                        "limit-at"        => $limit_at
                                        )
                                    );
                                }else{
                                    $API->comm("/queue/simple/add", array(
                                        "name"            => $this->normaliza($servicio).'-'.$contrato->nro,
                                        "target"          => $contrato->ip,
                                        "max-limit"       => $plan->upload.'/'.$plan->download,
                                        "burst-limit"     => $burst_limit,
                                        "burst-threshold" => $burst_threshold,
                                        "burst-time"      => $burst_time,
                                        "priority"        => $priority,
                                        "limit-at"        => $limit_at
                                        )
                                    );
                                }

                                #AGREGAMOS A IP_AUTORIZADAS#
                                $API->comm("/ip/firewall/address-list/add", array(
                                    "address" => $contrato->ip,
                                    "list" => 'ips_autorizadas'
                                    )
                                );
                                #AGREGAMOS A IP_AUTORIZADAS#
                            }else{
                                $fail++;
                            }

                            $API->disconnect();

                            if($registro){
                                $contrato->mk = 1;
                                $contrato->state = 'enabled';
                                $contrato->servicio = $this->normaliza($servicio).'-'.$contrato->nro;
                                $contrato->server_configuration_id = $mikrotik->id;
                                $contrato->save();
                                $succ++;
                                $contracts_fallidos .= 'Nro '.$contrato->nro.'<br>';
                            }
                        }
                    }else{
                        $fail++;
                        $contracts_fallidos .= 'Nro '.$contrato->nro.'<br>';
                    }
                }
            }
        }

        return response()->json([
            'success'             => true,
            'fallidos'            => $fail,
            'correctos'           => $succ,
            'contracts_fallidos'  => $contracts_fallidos,
            'contracts_correctos' => $contracts_correctos
        ]);
    }

    public function importar(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Importar Contratos Internet desde Excel', 'full' => true]);

        $mikrotiks = Mikrotik::all();
        $planes = PlanesVelocidad::all();
        $grupos = GrupoCorte::all();
        return view('contratos.importar')->with(compact('mikrotiks', 'planes', 'grupos'));
    }

    public function ejemplo(){
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Archivo de Importación de Contratos Internet ".Auth::user()->empresa()->nombre;
        $titulosColumnas = array('Identificacion', 'Servicio', 'Serial ONU', 'Plan', 'Mikrotik', 'Estado', 'IP', 'MAC', 'Conexion', 'Interfaz', 'Segmento', 'Nodo', 'Access Point', 'Grupo de Corte', 'Facturacion', 'Descuento', 'Canal', 'Oficina', 'Tecnologia','Fecha del Contrato', 'Cliente en Mikrotik');
        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific���
        ->setTitle("Archivo Importacion Contratos") // Titulo
        ->setSubject("Archivo Importacion Contratos") //Asunto
        ->setDescription("Archivo Importacion Contratos") //Descripci���n
        ->setKeywords("Archivo Importacion Contratos") //Etiquetas
        ->setCategory("Archivo Importacion"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah��� el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:N1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:N2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2','Fecha '.date('d-m-Y')); // Titulo del reporte

        $estilo = array(
            'font'  => array(
                'bold'  => true,
                'size'  => 12,
                'name'  => 'Times New Roman'
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A1:U3')->applyFromArray($estilo);

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

        $objPHPExcel->getActiveSheet()->getStyle('A3:U3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $contratos = Contrato::all();
        $j=4;

        /*foreach($contratos as $contrato){
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($letras[0].$j, $contrato->nombre)
            ->setCellValue($letras[1].$j, $contrato->apellido1)
            ->setCellValue($letras[2].$j, $contrato->apellido2)
            ->setCellValue($letras[3].$j, $contrato->tip_iden())
            ->setCellValue($letras[4].$j, $contrato->nit)
            ->setCellValue($letras[5].$j, $contrato->dv)
            ->setCellValue($letras[6].$j, $contrato->pais()->nombre)
            ->setCellValue($letras[7].$j, $contrato->departamento()->nombre)
            ->setCellValue($letras[8].$j, $contrato->municipio()->nombre)
            ->setCellValue($letras[9].$j, $contrato->cod_postal)
            ->setCellValue($letras[10].$j, $contrato->telefono1)
            ->setCellValue($letras[11].$j, $contrato->celular)
            ->setCellValue($letras[12].$j, $contrato->direccion)
            ->setCellValue($letras[13].$j, $contrato->vereda)
            ->setCellValue($letras[14].$j, $contrato->barrio)
            ->setCellValue($letras[15].$j, $contrato->ciudad)
            ->setCellValue($letras[16].$j, $contrato->email)
            ->setCellValue($letras[17].$j, $contrato->observaciones)
            ->setCellValue($letras[18].$j, $contrato->tipo_contacto());
            $j++;
        }*/

        $objPHPExcel->getActiveSheet()->getComment('A3')->setAuthor('Integra Colombia')->getText()->createTextRun('Identificacion del Cliente ya registrado en el sistema');
        $objPHPExcel->getActiveSheet()->getComment('D3')->setAuthor('Integra Colombia')->getText()->createTextRun('Nombre del plan ya registrado en el sistema');
        $objPHPExcel->getActiveSheet()->getComment('E3')->setAuthor('Integra Colombia')->getText()->createTextRun('Nombre de la mikrotik ya registrado en el sistema');
        $objPHPExcel->getActiveSheet()->getComment('F3')->setAuthor('Integra Colombia')->getText()->createTextRun('Habilitado o Deshabilitado');
        $objPHPExcel->getActiveSheet()->getComment('I3')->setAuthor('Integra Colombia')->getText()->createTextRun('PPPOE, DHCP, IP Estatica o VLAN');
        $objPHPExcel->getActiveSheet()->getComment('L3')->setAuthor('Integra Colombia')->getText()->createTextRun('Nombre del nodo ya registrado en el sistema');
        $objPHPExcel->getActiveSheet()->getComment('M3')->setAuthor('Integra Colombia')->getText()->createTextRun('Nombre del access point ya registrado en el sistema');
        $objPHPExcel->getActiveSheet()->getComment('N3')->setAuthor('Integra Colombia')->getText()->createTextRun('Nombre del grupo de corte ya registrado en el sistema');
        $objPHPExcel->getActiveSheet()->getComment('O3')->setAuthor('Integra Colombia')->getText()->createTextRun('Estandar o Electronica');
        $objPHPExcel->getActiveSheet()->getComment('Q3')->setAuthor('Integra Colombia')->getText()->createTextRun('Nombre del canal ya registrado en el sistema');
        $objPHPExcel->getActiveSheet()->getComment('R3')->setAuthor('Integra Colombia')->getText()->createTextRun('Nombre de la oficina ya registrado en el sistema');
        $objPHPExcel->getActiveSheet()->getComment('S3')->setAuthor('Integra Colombia')->getText()->createTextRun('Fibra o Inalambrica');
        $objPHPExcel->getActiveSheet()->getComment('T3')->setAuthor('Integra Colombia')->getText()->createTextRun('Fecha en formato yyyy-mm-dd hh:mm:ss');
        $objPHPExcel->getActiveSheet()->getComment('U3')->setAuthor('Integra Colombia')->getText()->createTextRun('Indique son Si o No');

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle('A3:U'.$j)->applyFromArray($estilo);

        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Contratos');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,5);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Archivo_Importacion_Contratos.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function cargando(Request $request){
        $request->validate([
            'archivo' => 'required|mimes:xlsx',
        ],[
            'archivo.mimes' => 'El archivo debe ser de extensión xlsx'
        ]);

        $create=0;
        $modf=0;
        $imagen = $request->file('archivo');
        $nombre_imagen = 'archivo.'.$imagen->getClientOriginalExtension();
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

        for ($row = 4; $row <= $highestRow; $row++){
            $request= (object) array();
            //obtengo el A4 desde donde empieza la data
            $nit=$sheet->getCell("A".$row)->getValue();
            if (empty($nit)) {
                break;
            }

            $request->servicio      = $sheet->getCell("B".$row)->getValue();
            $request->serial_onu    = $sheet->getCell("C".$row)->getValue();
            $request->plan          = $sheet->getCell("D".$row)->getValue();
            $request->mikrotik      = $sheet->getCell("E".$row)->getValue();
            $request->state         = $sheet->getCell("F".$row)->getValue();
            $request->ip            = $sheet->getCell("G".$row)->getValue();
            $request->mac           = $sheet->getCell("H".$row)->getValue();
            $request->conexion      = $sheet->getCell("I".$row)->getValue();
            $request->interfaz      = $sheet->getCell("J".$row)->getValue();
            $request->local_address = $sheet->getCell("K".$row)->getValue();
            $request->nodo          = $sheet->getCell("L".$row)->getValue();
            $request->ap            = $sheet->getCell("M".$row)->getValue();
            $request->grupo_corte   = $sheet->getCell("N".$row)->getValue();
            $request->facturacion   = $sheet->getCell("O".$row)->getValue();
            $request->descuento     = $sheet->getCell("P".$row)->getValue();
            $request->canal         = $sheet->getCell("Q".$row)->getValue();
            $request->oficina       = $sheet->getCell("R".$row)->getValue();
            $request->tecnologia    = $sheet->getCell("S".$row)->getValue();
            $request->created_at    = $sheet->getCell("T".$row)->getValue();
            $request->mk            = $sheet->getCell("U".$row)->getValue();
            $error=(object) array();

            if($nit != ""){
                if(Contacto::where('nit', $nit)->where('status', 1)->count() == 0){
                    $error->nit = "La identificación indicada no se encuentra registrada para ningún cliente en el sistema";
                }
            }
            if (!$request->servicio) {
                $error->servicio="El campo Servicio es obligatorio";
            }
            if($request->mikrotik != ""){
                if(Mikrotik::where('nombre', $request->mikrotik)->count() == 0){
                    $error->mikrotik = "El mikrotik ingresado no se encuentra en nuestra base de datos";
                }
                $miko = Mikrotik::where('nombre', $request->mikrotik)->first();
                $mikoId = $miko->id;
            }
            if($request->plan != ""){
                // $miko = Mikrotik::where('nombre', $request->mikrotik)->first();

                // if ($miko) {
                //     // El objeto $miko es válido, puedes acceder a su propiedad 'id'
                //     $mikoId = $miko->id;
                // } else {
                //     // Manejar el caso en el que $miko no sea un objeto válido
                // }
                    $num = (PlanesVelocidad::where('name', $request->plan)->where('mikrotik', $mikoId)->get());
                if($num === 0){
                    $error->plan = "El plan de velocidad ".$request->plan." ingresado no se encuentra en nuestra base de datos";
                }
            }
            if (!$request->state) {
                $error->state = "El campo estado es obligatorio";
            }
            if (!$request->ip) {
                $error->ip = "El campo IP es obligatorio";
            }
            if (!$request->conexion) {
                $error->conexion = "El campo conexión es obligatorio";
            }
            if (!$request->interfaz) {
                $error->interfaz = "El campo interfaz es obligatorio";
            }
            if (!$request->local_address) {
                $error->local_address = "El campo segmento es obligatorio";
            }
            if($request->grupo_corte != ""){
                if(GrupoCorte::where('nombre', $request->grupo_corte)->where('status', 1)->count() == 0){
                    $error->grupo_corte = "El grupo de corte ingresado no se encuentra en nuestra base de datos";
                }
            }
            if (!$request->facturacion) {
                $error->facturacion = "El campo facturacion es obligatorio";
            }

            if (!$request->tecnologia) {
                $error->tecnologia = "El campo tecnologia es obligatorio";
            }
            if (!$request->mk) {
                $error->mk = "Debe indicar Si o No en el campo Cliente en Mikrotik";
            }

            if (count((array) $error)>0) {
                $fila["error"]='FILA '.$row;
                $error=(array) $error;
                var_dump($error);
                var_dump($fila);

                array_unshift ( $error ,$fila);
                $result=(object) $error;
                return back()->withErrors($result)->withInput();
            }
        }

        for ($row = 4; $row <= $highestRow; $row++){
            $nit = $sheet->getCell("A".$row)->getValue();
            if (empty($nit)) {
                break;
            }
            $request                = (object) array();
            $request->servicio      = $sheet->getCell("B".$row)->getValue();
            $request->serial_onu    = $sheet->getCell("C".$row)->getValue();
            $request->plan          = $sheet->getCell("D".$row)->getValue();
            $request->mikrotik      = $sheet->getCell("E".$row)->getValue();
            $request->state         = $sheet->getCell("F".$row)->getValue();
            $request->ip            = $sheet->getCell("G".$row)->getValue();
            $request->mac           = $sheet->getCell("H".$row)->getValue();
            $request->conexion      = $sheet->getCell("I".$row)->getValue();
            $request->interfaz      = $sheet->getCell("J".$row)->getValue();
            $request->local_address = $sheet->getCell("K".$row)->getValue();
            $request->nodo          = $sheet->getCell("L".$row)->getValue();
            $request->ap            = $sheet->getCell("M".$row)->getValue();
            $request->grupo_corte   = $sheet->getCell("N".$row)->getValue();
            $request->facturacion   = $sheet->getCell("O".$row)->getValue();
            $request->descuento     = $sheet->getCell("P".$row)->getValue();
            $request->canal         = $sheet->getCell("Q".$row)->getValue();
            $request->oficina       = $sheet->getCell("R".$row)->getValue();
            $request->tecnologia    = $sheet->getCell("S".$row)->getValue();
            $request->created_at    = $sheet->getCell("T".$row)->getValue();
            $request->mk            = $sheet->getCell("U".$row)->getValue();

            if($request->conexion ==  'PPPOE'){
                $request->conexion = 1;
            }elseif($request->conexion ==  'DHCP'){
                $request->conexion = 2;
            }elseif($request->conexion ==  'IP Estatica'){
                $request->conexion = 3;
            }elseif($request->conexion ==  'VLAN'){
                $request->conexion = 4;
            }

            if($request->mikrotik != ""){
                $request->mikrotik = Mikrotik::where('nombre', $request->mikrotik)->first()->id;
            }
            if($request->plan != ""){
                $planesVelocidad = PlanesVelocidad::where('name', $request->plan)->first();
                if ($planesVelocidad) {
                    $request->plan = $planesVelocidad->id;
                } else {
                    // Manejar el caso en el que no se encuentra el plan de velocidad
                    $error->plan = "El plan de velocidad " . $request->plan . " ingresado no se encuentra en nuestra base de datos";
                }
            }
            if($request->grupo_corte != ""){
                $request->grupo_corte = GrupoCorte::where('nombre', $request->grupo_corte)->first()->id;
            }

            if($request->facturacion == 'Estandar'){
                $request->facturacion = 1;
            }elseif($request->facturacion == 'Electronica'){
                $request->facturacion = 3;
            }

            if($request->tecnologia == 'Fibra'){
                $request->tecnologia = 1;
            }elseif($request->tecnologia == 'Inalambrica'){
                $request->tecnologia = 2;
            }

            if($request->state == 'Habilitado'){
                $request->state = 'enabled';
            }elseif($request->state == 'Deshabilitado'){
                $request->state = 'disabled';
            }

            $request->mk = (strtoupper($request->mk) == 'NO') ? 0 : 1;

            $contrato = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->select('contracts.*', 'c.id as client_id')->where('c.nit', $nit)->where('contracts.empresa', Auth::user()->empresa)->where('contracts.status', 1)->where('c.status', 1)->first();

            if (!$contrato) {
                $nro = Numeracion::where('empresa', 1)->first();
                $nro_contrato = $nro->contrato;

                while (true) {
                    $numero = Contrato::where('nro', $nro_contrato)->count();
                    if ($numero == 0) {
                        break;
                    }
                    $nro_contrato++;
                }

                $contrato = new Contrato;
                $contrato->empresa   = Auth::user()->empresa;
                $contrato->servicio  = $this->normaliza($request->servicio).'-'.$nro_contrato;
                $contrato->nro       = $nro_contrato;
                $contrato->client_id = Contacto::where('nit', $nit)->where('status', 1)->first()->id;
                $create = $create+1;

                $nro->contrato = $nro_contrato + 1;
                $nro->save();
            }else{
                $modf = $modf+1;
                $contrato->servicio  = $this->normaliza($request->servicio).'-'.$contrato->nro;
            }

            $contrato->plan_id                 = $request->plan;
            $contrato->server_configuration_id = $request->mikrotik;
            $contrato->state                   = $request->state;
            $contrato->ip                      = $request->ip;
            $contrato->conexion                = $request->conexion;
            $contrato->interfaz                = $request->interfaz;
            $contrato->local_address           = $request->local_address;
            $contrato->grupo_corte             = $request->grupo_corte;
            $contrato->facturacion             = $request->facturacion;
            $contrato->tecnologia              = $request->tecnologia;

            $contrato->descuento               = $request->descuento;
            $contrato->canal                   = $request->canal;
            $contrato->oficina                 = $request->oficina;
            $contrato->nodo                    = $request->nodo;
            $contrato->ap                      = $request->ap;
            $contrato->mac_address             = $request->mac;
            $contrato->serial_onu              = $request->serial_onu;
            $contrato->created_at              = $request->created_at;
            $contrato->mk                      = $request->mk;

            $contrato->save();
        }

        $mensaje = 'SE HA COMPLETADO EXITOSAMENTE LA CARGA DE DATOS DEL SISTEMA';

        if ($create>0) {
            $mensaje.=' CREADOS: '.$create;
        }
        if ($modf>0) {
            $mensaje.=' MODIFICADOS: '.$modf;
        }
        return redirect('empresa/contratos')->with('success', $mensaje);
    }

    public function importarMK(){
        $contratos = Contrato::
        join('planes_velocidad as p', 'p.id', '=', 'contracts.plan_id')->
        join('mikrotik as m', 'm.id', '=', 'contracts.server_configuration_id')->
        select('contracts.*', 'p.prioridad', 'p.burst_limit_subida', 'p.burst_limit_bajada', 'p.burst_threshold_subida', 'p.burst_threshold_bajada', 'p.burst_time_subida', 'p.burst_time_bajada', 'p.limit_at_subida', 'p.limit_at_bajada', 'p.upload', 'p.download', 'p.dhcp_server', 'm.amarre_mac')->
        where('contracts.status', 1)->
        where('contracts.mk', 0)->
        get();

        $filePath = "NetworkSoft".date('dmY').".rsc";
        $file = fopen($filePath, "w");
        foreach($contratos as $contrato){
            $priority        = $contrato->prioridad;
            $burst_limit     = (strlen($contrato->burst_limit_subida)>1) ? $contrato->burst_limit_subida.'/'.$contrato->burst_limit_bajada : '';
            $burst_threshold = (strlen($contrato->burst_threshold_subida)>1) ? $contrato->burst_threshold_subida.'/'.$contrato->burst_threshold_bajada : '';
            $burst_time      = ($contrato->burst_time_subida) ? $contrato->burst_time_subida.'/'.$contrato->burst_time_bajada: '';
            $limit_at        = (strlen($contrato->limit_at_subida)>1) ? $contrato->limit_at_subida.'/'.$contrato->limit_at_bajada : '';
            $max_limit       = $contrato->upload.'/'.$contrato->download;

            fputs($file, '/queue/simple/add name="'.$contrato->servicio.'" target='.$contrato->ip.' max-limit='.$contrato->upload.'/'.$contrato->download);

            if(strlen($burst_limit)>3){
                fputs($file, ' burst-limit='.$burst_limit);
            }
            if(strlen($burst_threshold)>3){
                fputs($file, ' burst-threshold='.$burst_threshold);
            }
            if(strlen($burst_time)>3){
                fputs($file, ' burst-time='.$burst_time);
            }
            if(strlen($priority)>0){
                fputs($file, ' priority='.$priority);
            }
            if(strlen($limit_at)>0){
                fputs($file, ' limit-at='.$limit_at);
            }

            fputs($file, PHP_EOL);
        }
        fclose($file);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($filePath));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;


        foreach($contratos as $contrato){
            /*PPPOE*/
            if($contrato->conexion == 1){
                $API->comm("/ppp/secret/add",
                    array(
                        "name"           => $contrato->usuario,
                        "password"       => $contrato->password,
                        "profile"        => 'default',
                        "local-address"  => $contrato->ip,
                        "remote-address" => $contrato->ip,
                        "service"        => 'pppoe',
                        "comment"        => $contrato->servicio
                    )
                );
            }
            /*DHCP*/
            if($contrato->conexion == 2){
                if(isset($contrato->dhcp_server)){
                    if($contrato->simple_queue == 'dinamica'){
                        $API->comm("/ip/dhcp-server/set\n=name=".$contrato->dhcp_server."\n=address-pool=static-only\n=parent-queue=".$contrato->parenta);
                        $API->comm("/ip/dhcp-server/lease/add",
                            array(
                                "comment"     => $contrato->servicio,
                                "address"     => $contrato->ip,
                                "server"      => $contrato->dhcp_server,
                                "mac-address" => $contrato->mac_address,
                                "rate-limit"  => $rate_limit
                            )
                        );
                    }elseif ($contrato->simple_queue == 'estatica') {
                        $API->comm("/ip/dhcp-server/lease/add",
                            array(
                                "comment"     => $contrato->servicio,
                                "address"     => $contrato->ip,
                                "server"      => $contrato->dhcp_server,
                                "mac-address" => $contrato->mac_address
                            )
                        );
                    }
                }
            }
            /*IP ESTÁTICA*/
            if($contrato->conexion == 3){
                if($contrato->amarre_mac == 1){
                    $API->comm("/ip/arp/add",
                        array(
                            "comment"     => $contrato->servicio,
                            "address"     => $contrato->ip,
                            "interface"   => $contrato->interfaz,
                            "mac-address" => $contrato->mac_address
                        )
                    );
                }
            }
            #QUEUE SIMPLE

            #AGREGAMOS A IP_AUTORIZADAS#
            $API->comm("/ip/firewall/address-list/add",
                array(
                    "address" => $contrato->ip,
                    "list" => 'ips_autorizadas'
                )
            );
        }
    }

    public function planes_lote($contratos, $server_configuration_id, $plan_id){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $contratos = explode(",", $contratos);

        for ($i=0; $i < count($contratos) ; $i++) {
            $descripcion = '';
            $contrato = Contrato::find($contratos[$i]);
            $plan     = PlanesVelocidad::find($plan_id);
            $mikrotik = Mikrotik::find($server_configuration_id);

            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($contrato) {
                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    #ELMINAMOS DEL QUEUE#
                    $queue = $API->comm("/queue/simple/getall", array(
                        "?target" => $contrato->ip.'/32'
                        )
                    );

                    if($queue){
                        $API->comm("/queue/simple/remove", array(
                            ".id" => $queue[0][".id"],
                            )
                        );
                    }
                    #ELMINAMOS DEL QUEUE#

                    $rate_limit      = '';
                    $priority        = $plan->prioridad;
                    $burst_limit     = (strlen($plan->burst_limit_subida)>1) ? $plan->burst_limit_subida.'/'.$plan->burst_limit_bajada : '';
                    $burst_threshold = (strlen($plan->burst_threshold_subida)>1) ? $plan->burst_threshold_subida.'/'.$plan->burst_threshold_bajada : '';
                    $burst_time      = ($plan->burst_time_subida) ? $plan->burst_time_subida.'/'.$plan->burst_time_bajada: '';
                    $limit_at        = (strlen($plan->limit_at_subida)>1) ? $plan->limit_at_subida.'/'.$plan->limit_at_bajada : '';
                    $max_limit       = $plan->upload.'/'.$plan->download;

                    if($max_limit){ $rate_limit .= $max_limit; }
                    if(strlen($burst_limit)>3){ $rate_limit .= ' '.$burst_limit; }
                    if(strlen($burst_threshold)>3){ $rate_limit .= ' '.$burst_threshold; }
                    if(strlen($burst_time)>3){ $rate_limit .= ' '.$burst_time; }
                    if($priority){ $rate_limit .= ' '.$priority; }
                    if($limit_at){ $rate_limit .= ' '.$limit_at; }

                    $API->comm("/queue/simple/add",
                        array(
                            "name"            => $contrato->servicio,
                            "target"          => $contrato->ip,
                            "max-limit"       => $plan->upload.'/'.$plan->download,
                            "burst-limit"     => $burst_limit,
                            "burst-threshold" => $burst_threshold,
                            "burst-time"      => $burst_time,
                            "priority"        => $priority,
                            "limit-at"        => $limit_at
                        )
                    );

                    $plan_old = ($contrato->plan_id) ? PlanesVelocidad::find($contrato->plan_id)->name : 'Ninguno';

                    $descripcion .= ($contrato->plan_id == $plan_id) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Plan</b> de '.$plan_old.' a '.$plan->name.'<br>';
                    $contrato->plan_id = $plan_id;
                    $contrato->save();

                    /*REGISTRO DEL LOG*/
                    if(!is_null($descripcion)){
                        $movimiento = new MovimientoLOG;
                        $movimiento->contrato    = $contrato->id;
                        $movimiento->modulo      = 5;
                        $movimiento->descripcion = $descripcion;
                        $movimiento->created_by  = Auth::user()->id;
                        $movimiento->empresa     = Auth::user()->empresa;
                        $movimiento->save();
                    }
                    $succ++;
                } else {
                    $fail++;
                }
                $API->disconnect();
            }
        }

        return response()->json([
            'success'   => true,
            'fallidos'  => $fail,
            'correctos' => $succ,
            'plan'      => $plan->name
        ]);
    }

    function opcion_dian(Request $request){
        $contrato = Contrato::find($request->contratoId);
        $contrato->opciones_dian = $request->opcionDian == 1 ? 0 : 1;
        $contrato->save();

        return true;
    }


    function morosos(){

        $allMorosos = [];
           $mikrotiks = Mikrotik::all();
          // dd($mikrotiks);
           foreach($mikrotiks as $mikrotik){
                    $API = new RouterosAPI();
                   $API->port = $mikrotik->puerto_api;

                   //$API->debug = true;

                       if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                           $API->write('ip/firewall/address get [find list=morosos]', true);
                           $ARRAYS = $API->read();

                           $allMorosos[] = $ARRAYS;
                       }
           }
           return $allMorosos;
       }



       public function forzarCrm($idContrato){

        $contrato = Contrato::find($idContrato);

        //crm registro
        $crm = new CRM();
        $crm->cliente = $contrato->cliente()->id;
        $crm->servidor = isset($contrato->server_configuration_id) ? $contrato->server_configuration_id : '';
        $crm->grupo_corte = isset($contrato->grupo_corte) ? $contrato->grupo_corte : '';
        $crm->estado = 0;
        if($lastFact = $contrato->lastFactura()){
            $crm->factura = $lastFact->id;
        }
        $crm->save();


        return back()->with('success', 'Se genero un registro CRM en la cartera');

       }



}
