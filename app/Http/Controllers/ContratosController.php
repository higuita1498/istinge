<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Solicitud;
use App\Empresa;
use App\Contrato;
use App\Servicio;
use App\User;
use App\AP;
use App\Contacto;
use App\TipoIdentificacion;
use App\Vendedor;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\Inventario;
use App\TipoEmpresa;
use App\Numeracion;
use App\Impuesto;
use App\Model\Ingresos\Ingreso;
use App\Model\Ingresos\IngresosCategoria;
use App\Categoria;
use App\Movimiento;
use App\MovimientoLOG;
use App\Servidor;
use App\Mikrotik;
use App\Funcion;
use App\PlanesVelocidad;
use App\Interfaz;
use App\Ping;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use Barryvdh\DomPDF\Facade as PDF;
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

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;
use App\Nodo;
use App\GrupoCorte;
use App\Segmento;
use App\Campos;
use App\Puerto;

class ContratosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'contratos', 'subseccion' => 'listado', 'title' => 'Contratos de Servicio', 'icon' =>'fas fa-file-contract']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = Contacto::where('status',1)->where('empresa', Auth::user()->empresa)->whereIn('tipo_contacto', [0,2])->get();
        $planes = PlanesVelocidad::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servidores = Mikrotik::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $grupos = GrupoCorte::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        view()->share(['title' => 'Contratos', 'invert' => true]);
        $tipo = false;
        $tabla = Campos::where('modulo', 2)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
        $nodos = Nodo::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        return view('contratos.indexnew', compact('clientes','planes','servidores','grupos','tipo','tabla','nodos','aps'));
    }

    public function disabled(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = Contacto::where('status',1)->where('empresa', Auth::user()->empresa)->whereIn('tipo_contacto', [0,2])->get();
        $planes = PlanesVelocidad::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servidores = Mikrotik::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $grupos = GrupoCorte::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        view()->share(['title' => 'Contratos', 'invert' => true]);
        $tipo = 'disabled';
        $tabla = Campos::where('modulo', 2)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
        $nodos = Nodo::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        return view('contratos.indexnew', compact('clientes','planes','servidores','grupos','tipo','tabla','nodos','aps'));
    }

    public function enabled(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = Contacto::where('status',1)->where('empresa', Auth::user()->empresa)->whereIn('tipo_contacto', [0,2])->get();
        $planes = PlanesVelocidad::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servidores = Mikrotik::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $grupos = GrupoCorte::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        view()->share(['title' => 'Contratos', 'invert' => true]);
        $tipo = 'enabled';
        $tabla = Campos::where('modulo', 2)->where('estado', 1)->orderBy('orden', 'asc')->where('empresa', Auth::user()->empresa)->get();
        $nodos = Nodo::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status',1)->where('empresa', Auth::user()->empresa)->get();
        return view('contratos.indexnew', compact('clientes','planes','servidores','grupos','tipo','tabla','nodos','aps'));
    }

    public function contratos(Request $request, $nodo){
        $modoLectura = auth()->user()->modo_lectura();
        $contratos = Contrato::query()
			->select('contracts.*', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.nit as c_nit', 'contactos.celular as c_telefono', 'contactos.email as c_email', 'contactos.barrio as c_barrio', 'contactos.direccion as c_direccion', 'contactos.celular as c_celular', 'contactos.email as c_email', 'contactos.id as c_id', 'contactos.firma_isp')
			->join('contactos', 'contracts.client_id', '=', 'contactos.id');

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
                $contratos->where(function ($query) use ($request) {
                    $query->orWhere('contactos.direccion', 'like', "%{$request->c_direccion}%");
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

        return datatables()->eloquent($contratos)
            ->editColumn('nro', function (Contrato $contrato) {
                return $contrato->nro ? "<span class='badge badge-".$contrato->plug('true')."' data-toggle='tooltip' data-placement='top' title='".$contrato->plug()."' style='border-radius: 50% !important;padding: 0.3rem 0.4rem !important;'><i class='fa fa-plug'></i></span>   <a href=" . route('contratos.show', $contrato->id) . "><strong>$contrato->nro</strong></a>" : "";
            })
            ->editColumn('client_id', function (Contrato $contrato) {
                return  "<a href=" . route('contactos.show', $contrato->c_id) . ">{$contrato->c_nombre}</a>";
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
            ->editColumn('plan', function (Contrato $contrato) {
                //return $contrato->plan()->name;
                return "<div class='elipsis-short-325'><a href=" . route(
                    'planes-velocidad.show',
                    $contrato->plan()->id
                ) . " target='_blank'>{$contrato->plan()->name}</a></div>";
            })
            ->editColumn('mac', function (Contrato $contrato) {
                return $contrato->mac_address;
            })
            ->editColumn('ip', function (Contrato $contrato) {
                return $contrato->ip;
            })
			->editColumn('grupo_corte', function (Contrato $contrato) {
                return $contrato->grupo_corte('true');
            })
            ->editColumn('state', function (Contrato $contrato) {
                return '<span class="text-'.$contrato->status('true').' font-weight-bold">'.$contrato->status().'</span>';
            })
            ->editColumn('pago', function (Contrato $contrato) {
                return ($contrato->pago($contrato->c_id)) ? '<a href='.route('ingresos.show', $contrato->pago($contrato->c_id)->id).' target="_blank">Nro. '.$contrato->pago($contrato->c_id)->nro.' | '.date('d-m-Y', strtotime($contrato->pago($contrato->c_id)->fecha)).'</a>' : '- - - -';
            })
            ->editColumn('servicio', function (Contrato $contrato) {
                return '- - - -';
            })
            ->editColumn('conexion', function (Contrato $contrato) {
                return $contrato->conexion();
            })
            ->editColumn('server_configuration_id', function (Contrato $contrato) {
                return $contrato->servidor()->nombre;
            })
            ->editColumn('interfaz', function (Contrato $contrato) {
                return $contrato->interfaz;
            })
            ->editColumn('nodo', function (Contrato $contrato) {
                return ($contrato->nodo)?$contrato->nodo()->nombre:$contrato->nodo();
            })
            ->editColumn('ap', function (Contrato $contrato) {
                return ($contrato->ap)?$contrato->ap()->nombre:$contrato->ap();
            })
            ->editColumn('direccion', function (Contrato $contrato) {
                return $contrato->c_direccion;
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
            ->editColumn('acciones', $modoLectura ?  "" : "contratos.acciones")
            ->rawColumns(['nro', 'client_id', 'nit', 'telefono', 'email', 'barrio', 'plan', 'mac', 'ip', 'grupo_corte', 'state', 'pago', 'servicio', 'factura', 'acciones'])
            ->toJson();
    }
    
    public function create($cliente = false){
        $this->getAllPermissions(Auth::user()->id);
        $empresa = Auth::user()->empresa;
        $sql = "SELECT * FROM contactos AS c WHERE c.status = 1 AND c.id NOT IN (SELECT cs.client_id FROM contracts AS cs) AND tipo_contacto = 0 AND c.empresa = $empresa ORDER BY c.nombre ASC";
        $clientes = DB::select($sql);
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
        
        view()->share(['icon'=>'fas fa-file-contract', 'title' => 'Nuevo Contrato']);
        return view('contratos.create')->with(compact('clientes', 'planes', 'servidores', 'identificaciones', 'paises', 'departamentos','nodos', 'aps', 'marcas', 'grupos', 'cliente', 'puertos', 'empresa'));
    }
    
    public function store(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $request->validate([
            'client_id' => 'required',
            'plan_id' => 'required',
            'server_configuration_id' => 'required',
            'ip' => 'required',
            'grupo_corte' => 'required',
            /*'fecha_corte' => 'required',
            'fecha_suspension' => 'required',*/
            'conexion' => 'required',
            'facturacion' => 'required',
            /*'costo_instalacion' => 'required',
            'caja' => 'required'*/
        ]);

        if($request->interfaz == 3){
            $request->validate([
                //'mac_address' => 'required'
            ]);
        }
        

        $mikrotik = Mikrotik::where('id', $request->server_configuration_id)->first();
        $plan = PlanesVelocidad::where('id', $request->plan_id)->first();
        $cliente = Contacto::find($request->client_id);
        
        if ($mikrotik) {
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            $registro = false;
            $API->debug = true;
            
            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $nro = Numeracion::where('empresa', 1)->first();
                $nro_contrato = $nro->contrato;
                
                while (true) {
                    $numero = Contrato::where('nro', $nro_contrato)->count();
                    if ($numero == 0) {
                        break;
                    }
                    $nro_contrato++;
                }
                
                if($request->local_address){ 
                    $segmento = explode("/", $request->local_address);
                    $prefijo = '/'.$segmento[1];
                }else{
                    $prefijo = '';
                }
                
                if($request->local_address_new){ 
                    $segmento = explode("/", $request->local_address_new);
                    $prefijo = '/'.$segmento[1];
                }else{
                    $prefijo = '';
                }

                $rate_limit = '';
                $priority        = $plan->prioridad;
                $burst_limit     = ($plan->burst_limit_subida) ? $plan->burst_limit_subida.'/'.$plan->burst_limit_bajada : '';
                $burst_threshold = ($plan->burst_threshold_subida) ? $plan->burst_threshold_subida.'/'.$plan->burst_threshold_bajada : '';
                $burst_time      = ($plan->burst_time_subida) ? $plan->burst_time_subida.'/'.$plan->burst_time_bajada : '';
                $limit_at        = ($plan->limit_at_subida) ? $plan->limit_at_subida.'/'.$plan->limit_at_bajada  : '';
                $max_limit       = $plan->upload.'/'.$plan->download;

                if($max_limit){
                    $rate_limit .= $max_limit;
                }
                if(strlen($burst_limit)>3){
                    $rate_limit .= ' '.$burst_limit;
                }
                if(strlen($burst_threshold)>3){
                    $rate_limit .= ' '.$burst_threshold;
                }
                if(strlen($burst_time)>3){
                    $rate_limit .= ' '.$burst_time;
                }
                if($priority){
                    $rate_limit .= ' '.$priority;
                }
                if(strlen($limit_at)>3){
                    $rate_limit .= ' '.$limit_at;
                }

                /*PPPOE*/
                if($request->conexion == 1){
                    $API->comm("/ppp/secret/add", array(
                        "name"           => $request->usuario,       //USER
                        "password"       => $request->password,      //CLAVE
                        "profile"        => 'default',               //PERFIL
                        "local-address"  => $request->ip,            //IP LOCAL
                        "remote-address" => $request->ip,            // IP CLIENTE
                        "service"        => 'pppoe',                 // SERVICIO
                        "comment"        => $nro_contrato            //NRO DEL CONTATO
                        )
                    );
                    
                    $API->comm("/queue/simple/add", array(
                        "name"            => $this->normaliza($cliente->nombre),
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
                
                /*DHCP*/
                if($request->conexion == 2){
                    if($plan->dhcp_server){
                        if($request->simple_queue == 'dinamica'){
                            $API->comm("/ip/dhcp-server/set\n=name=".$plan->dhcp_server."\n=address-pool=static-only\n=parent-queue=".$plan->parenta);

                            $API->comm("/ip/dhcp-server/lease/add", array(
                                "comment"     => $this->normaliza($cliente->nombre),
                                "address"     => $request->ip,
                                "server"      => $plan->dhcp_server,
                                "mac-address" => $request->mac_address,
                                "rate-limit"  => $rate_limit
                                )
                            );

                            $name = $API->comm("/ip/dhcp-server/lease/getall", array(
                                "?comment" => $this->normaliza($cliente->nombre),  // NOMBRE CLIENTE
                                )
                            );
                        }elseif ($request->simple_queue == 'estatica') {
                            $API->comm("/ip/dhcp-server/lease/add", array(
                                "comment"     => $this->normaliza($cliente->nombre),
                                "address"     => $request->ip,
                                "server"      => $plan->dhcp_server,
                                "mac-address" => $request->mac_address
                                )
                            );

                            $name = $API->comm("/ip/dhcp-server/lease/getall", array(
                                "?comment" => $this->normaliza($cliente->nombre),  // NOMBRE CLIENTE
                                )
                            );

                            if($name){
                                $registro = true;
                                $API->comm("/queue/simple/add", array(
                                    "name"            => $this->normaliza($cliente->nombre),
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
                    $API->comm("/ip/arp/add", array(
                        "comment"     => $this->normaliza($cliente->nombre),  // NOMBRE CLIENTE
                        "address"     => $request->ip,                        // IP DEL CLIENTE
                        "interface"   => $request->interfaz,                  // INTERFACE DEL CLIENTE
                        "mac-address" => $request->mac_address                // DIRECCION MAC
                        )
                    );

                    $name = $API->comm("/ip/arp/getall", array(
                        "?address" => $request->ip
                        )
                    );

                    if($name){
                        $registro = true;
                        $API->comm("/queue/simple/add", array(
                            "name"            => $this->normaliza($cliente->nombre),
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

                    if($request->ip_new){
                        $API->comm("/ip/arp/add", array(
                            "comment"     => $this->normaliza($cliente->nombre).'-'.$nro_contrato, // NOMBRE MAS ID DEL CONTRATO
                            "address"     => $request->ip_new, // IP DEL CLIENTE
                            "interface"   => $request->interfaz, // INTERFACE DEL CLIENTE
                            "mac-address" => $request->mac_address // DIRECCION MAC
                            )
                        );
                        
                        $API->comm("/queue/simple/add", array(
                                "name"            => $this->normaliza($cliente->nombre),
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
                        "name"        => $request->name_vlan,   // NOMBRE VLAN
                        "vlan-id"     => $request->id_vlan,     // ID VLAN
                        "interface"   => $request->interfaz     // INTERFACE DEL CLIENTE
                        )
                    );
                    
                    $API->comm("/ip/address/add", array(
                        "address"     => $request->local_address, // SEGMENTO DE IP
                        "interface"   => $request->name_vlan      // NOMBRE DEL VLAN
                        )
                    );
                    
                    $API->comm("/queue/simple/add", array(
                            "name"            => $this->normaliza($cliente->nombre),
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
                    $r_ip = ($request->local_address) ? $request->ip.''.$prefijo : $request->ip;
                    $API->comm("/ip/firewall/address-list/add\n=list=ips_autorizadas\n=address=".$r_ip);
                    $ip_autorizada = 1;
                }
                
                $API->disconnect();
                
                $contrato = new Contrato();
                $contrato->plan_id                 = $request->plan_id;
                $contrato->nro                     = $nro_contrato;
                $contrato->servicio                = $this->normaliza($cliente->nombre);
                $contrato->client_id               = $request->client_id;
                $contrato->server_configuration_id = $request->server_configuration_id;
                $contrato->ip                      = ($request->local_address) ? $request->ip.''.$prefijo : $request->ip;
                $contrato->ip_new                  = ($request->local_address_new) ? $request->ip_new.''.$prefijo : $request->ip_new;
                //$contrato->fecha_corte             = $request->fecha_corte;
                //$contrato->fecha_suspension        = $request->fecha_suspension;
                $contrato->usuario                 = $request->usuario;
                $contrato->password                = $request->password;
                $contrato->conexion                = $request->conexion;
                $contrato->simple_queue            = $request->simple_queue;
                $contrato->interfaz                = $request->interfaz;
                $contrato->local_address           = $request->local_address;
                $contrato->local_address_new       = $request->local_address_new;
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
                
                if($request->ap){
                    $ap = AP::find($request->ap);
                    $contrato->nodo    = $ap->nodo;
                    $contrato->ap      = $request->ap;
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
        }
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->join('planes_velocidad as p', 'p.id', '=', 'contracts.plan_id')->select('contracts.plan_id','contracts.id','contracts.nro','contracts.state','contracts.interfaz','c.nombre','c.nit','c.celular','c.telefono1','p.name as plan','p.price','contracts.ip','contracts.mac_address','contracts.server_configuration_id','contracts.conexion','contracts.marca_router','contracts.modelo_router','contracts.marca_antena','contracts.modelo_antena','contracts.nodo','contracts.ap','contracts.interfaz','contracts.local_address','contracts.local_address_new','contracts.ip_new','contracts.grupo_corte', 'contracts.facturacion', 'contracts.fecha_suspension', 'contracts.usuario', 'contracts.password', 'contracts.adjunto_a', 'contracts.referencia_a', 'contracts.adjunto_b', 'contracts.referencia_b', 'contracts.adjunto_c', 'contracts.referencia_c', 'contracts.adjunto_d', 'contracts.referencia_d', 'contracts.simple_queue')->where('contracts.id', $id)->where('contracts.empresa', Auth::user()->empresa)->first();
        $planes = PlanesVelocidad::where('status', 1)->where('mikrotik', $contrato->server_configuration_id)->get();
        $nodos = Nodo::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $aps = AP::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $marcas = DB::table('marcas')->get();
        $servidores = Mikrotik::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $interfaces = Interfaz::all();
        $grupos = GrupoCorte::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $puertos = Puerto::where('empresa', Auth::user()->empresa)->get();
        
        if ($contrato) {
            view()->share(['icon'=>'fas fa-file-contract', 'title' => 'Editar Contrato: '.$contrato->nro]);
            return view('contratos.edit')->with(compact('contrato','planes','nodos','aps', 'marcas', 'servidores', 'interfaces', 'grupos', 'puertos'));
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }
    
    public function update(Request $request, $id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        $descripcion = '';
        if ($contrato) {
            $request->validate([
                'server_configuration_id' => 'required',
                'plan_id' => 'required',
                //'interfaz' => 'required',
                'ip' => 'required',
                'grupo_corte' => 'required',
                /*'fecha_corte' => 'required',
                'fecha_suspension' => 'required'*/
            ]);
            
            $plan = PlanesVelocidad::where('id', $request->plan_id)->first();
            $mikrotik = Mikrotik::where('id', $plan->mikrotik)->first();
            $cliente = $contrato->cliente();
            
            if ($mikrotik) {
                $API = new RouterosAPI();
                $API->port = $mikrotik->puerto_api;
                //$API->debug = true;

                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    $rate_limit = '';
                    $priority        = $plan->prioridad;
                    $burst_limit     = ($plan->burst_limit_subida) ? $plan->burst_limit_subida.'/'.$plan->burst_limit_bajada : '';
                    $burst_threshold = ($plan->burst_threshold_subida) ? $plan->burst_threshold_subida.'/'.$plan->burst_threshold_bajada : '';
                    $burst_time      = ($plan->burst_time_subida) ? $plan->burst_time_subida.'/'.$plan->burst_time_bajada: '';
                    $limit_at        = ($plan->limit_at_subida) ? $plan->limit_at_subida.'/'.$plan->limit_at_bajada : '';
                    $max_limit       = $plan->upload.'/'.$plan->download;

                    if($max_limit){
                        $rate_limit .= $max_limit;
                    }
                    if(strlen($burst_limit)>3){
                        $rate_limit .= ' '.$burst_limit;
                    }
                    if(strlen($burst_threshold)>3){
                        $rate_limit .= ' '.$burst_threshold;
                    }
                    if(strlen($burst_time)>3){
                        $rate_limit .= ' '.$burst_time;
                    }
                    if($priority){
                        $rate_limit .= ' '.$priority;
                    }
                    if($limit_at){
                        $rate_limit .= ' '.$limit_at;
                    }

                    /*PPPOE*/
                    if($request->conexion == 1){
                        $API->comm("ppp/secrets\n=find\n=name=$contrato->servicio\n=[set\n=remote-address=$request->ip\n=name=$request->usuario\n=password=$request->password]");

                        $name_new = $API->comm("/queue/simple/getall", array(
                                "?target" => $contrato->ip.'/32'
                            )
                        );

                        if($name_new){
                            $API->comm("/queue/simple/set", array(
                                    ".id"             => $name_new[0][".id"],
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

                    /*DHCP*/
                    if($request->conexion == 2){
                        if(isset($plan->dhcp_server)){
                            $name = $API->comm("/ip/dhcp-server/lease/getall", array(
                                "?address" => $contrato->ip // IP DEL CLIENTE
                                )
                            );

                            if($name){
                                $API->comm("/ip/dhcp-server/lease/set", array(
                                    ".id"             => $name[0][".id"],
                                    "address"         => $request->ip,
                                    "server"          => $plan->dhcp_server,
                                    "mac-address"     => $request->mac_address,
                                    "rate-limit"      => $rate_limit
                                    )
                                );
                            }

                            $name_new = $API->comm("/queue/simple/getall", array(
                                    "?target" => $contrato->ip.'/32'
                                )
                            );

                            if($name_new){
                                $API->comm("/queue/simple/set", array(
                                        ".id"             => $name_new[0][".id"],
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
                        }else{
                            $mensaje='NO SE HA PODIDO CREAR EL CONTRATO DE SERVICIOS, NO EXISTE UN SERVIDOR DHCP DEFINIDO PARA EL PLAN '.$plan->name;
                            return redirect('empresa/contratos')->with('danger', $mensaje);
                        }
                    }

                    /*IP ESTÁTICA*/
                    if($request->conexion == 3){
                        //EDITANDO IP E INTERFACE
                        if($request->local_address){
                            $segmento = explode("/", $request->local_address);
                            $prefijo = '/'.$segmento[1];
                        }else{
                            $prefijo = '';
                        }
                        if($request->local_address_new){
                            $segmento = explode("/", $request->local_address_new);
                            $prefijo = '/'.$segmento[1];
                        }else{
                            $prefijo = '';
                        }

                        //OBTENEMOS EL CONTRATO ARP
                        $mk_user = $API->comm("/ip/arp/getall", array(
                            "?address" => $contrato->ip,
                            )
                        );

                        //ACTUALIZAMOS EL ARP
                        if($mk_user){
                            $API->comm("/ip/arp/set", array(
                                ".id" => $mk_user[0][".id"],
                                "address"   => $request->ip,            // IP DEL CLIENTE
                                "interface" => $request->interfaz,      // INTERFAZ DEL CLIENTE
                                "mac-address" => $request->mac_address  // DIRECCION MAC
                                )
                            );
                        }

                        if($request->ip_new){
                            //OBTENEMOS AL CONTRATO MK
                            $mk_id = $API->comm("/ip/arp/getall", array(
                                "?address" => $contrato->ip_new,
                                )
                            );

                            //ACTUALIZAMOS IP
                            if($mk_id){
                                $API->comm("/ip/arp/set", array(
                                    ".id" => $mk_id[0][".id"],
                                    "address"   => $request->ip_new, // IP DEL CLIENTE
                                    "interface" => $request->interfaz, // INTERFACE DEL CLIENTE
                                    "mac-address" => $request->mac_address // DIRECCION MAC
                                    )
                                );
                            }else{
                                $API->comm("/ip/arp/add", array(
                                    "comment"   => $contrato->servicio.'-'.$contrato->id,// NOMBRE CLIENTE
                                    "address"   => $request->ip_new, // IP DEL CLIENTE
                                    "interface" => $request->interfaz, // INTERFACE DEL CLIENTE
                                    "mac-address" => $request->mac_address // DIRECCION MAC
                                    )
                                );
                            }
                        }else{
                            if($contrato->ip_new){
                                //OBTENEMOS AL CONTRATO MK
                                $id_simple = $API->comm("/ip/arp/getall", array(
                                    "?address" => $contrato->ip_new,
                                    )
                                );

                                //ELIMINAMOS IP
                                if($id_simple){
                                    $API->comm("/ip/arp/remove", array(
                                        ".id" => $id_simple[0][".id"],
                                        )
                                    );
                                }
                            }
                        }

                        //EDITANDO PLAN
                        //BUSCAMOS CLIENTE POR ID
                        $name = $API->comm("/queue/simple/getall", array(
                            "?target" => $contrato->ip.'/32'
                            )
                        );

                        if($name){
                            $API->comm("/queue/simple/set", array(
                                ".id"       => $name[0][".id"],
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

                        if($request->ip_new){
                            $dos = $API->comm("/queue/simple/getall", array(
                                "?target" => $contrato->ip_new.'/32'
                                )
                            );

                            if(!$dos){
                                $API->comm("/queue/simple/add", array(
                                    "name"            => $contrato->servicio.'-'.$contrato->id,
                                    "target"          => $request->ip,
                                    "max-limit"       => $plan->upload.'/'.$plan->download,
                                    "parent"          => $plan->parenta,
                                    "priority"        => $priority,
                                    "burst-limit"     => $burst_limit,
                                    "burst-threshold" => $burst_threshold,
                                    "burst-time"      => $burst_time,
                                    "limit-at"        => $limit_at
                                    )
                                );
                            }
                        }else{
                            $dos = $API->comm("/queue/simple/getall", array(
                                "?target" => $contrato->ip_new.'/32'
                                )
                            );

                            if($dos){
                                $API->comm("/queue/simple/remove", array(
                                    ".id" => $dos[0][".id"],
                                    )
                                );
                            }
                        }
                    }

                    /*VLAN*/
                    if($request->conexion == 4){

                    }
                }

                $API->disconnect();
                
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
                
                $plan_old = PlanesVelocidad::find($contrato->plan_id);
                $plan_new = PlanesVelocidad::find($request->plan_id);
                
                $descripcion .= ($contrato->plan_id == $request->plan_id) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Plan</b> de '.$plan_old->name.' a '.$plan_new->name.'<br>';
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
                    $descripcion .= ($contrato->ap == $ap_new->ap) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Access Point</b> de '.$ap_old->nombre.' a '.$ap_new->nombre.'<br>';
                    $contrato->ap   = $request->ap;
                }
                
                if($contrato->nodo){
                    $nodo_old = Nodo::find($contrato->nodo);
                    $nodo_new = Nodo::find($ap_new->nodo);
                    
                    $descripcion .= ($contrato->nodo == $ap_new->nodo) ? '' : '<i class="fas fa-check text-success"></i> <b>Cambio Nodo</b> de '.$nodo_old->nombre.' a '.$nodo_new->nombre.'<br>';
                    $contrato->nodo = $ap_new->nodo;
                }

                $contrato->puerto_conexion = $request->puerto_conexion;
                $contrato->usuario  = $request->usuario;
                $contrato->password = $request->password;
                $contrato->simple_queue = $request->simple_queue;

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
            }
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::join('contactos as c', 'c.id', '=', 'contracts.client_id')->join('planes_velocidad as p', 'p.id', '=', 'contracts.plan_id')->select('contracts.*', 'contracts.status as cs_status', 'c.nombre', 'c.nit', 'c.celular', 'c.telefono1', 'c.direccion', 'c.barrio', 'c.email', 'c.id as id_cliente', 'p.name as plan', 'p.price', 'contracts.marca_router', 'contracts.modelo_router', 'contracts.marca_antena', 'contracts.modelo_antena', 'contracts.ip', 'contracts.grupo_corte', 'contracts.adjunto_a', 'contracts.referencia_a', 'contracts.adjunto_b', 'contracts.referencia_b', 'contracts.adjunto_c', 'contracts.referencia_c', 'contracts.adjunto_d', 'contracts.referencia_d', 'contracts.simple_queue')->where('contracts.id', $id)->first();
        
        if ($contrato) {
            view()->share(['icon'=>'fas fa-file-contract', 'title' => 'Detalles Contrato: '.$contrato->nro]);
            return view('contratos.show')->with(compact('contrato'));
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }
    
    public function destroy($id){
        $this->getAllPermissions(Auth::user()->id);
        $contrato = Contrato::find($id);
        if ($contrato) {
            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();
            
            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            
            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                if($contrato->conexion == 1){
                    //OBTENEMOS AL CONTRATO MK
                    $mk_user = $API->comm("/ppp/secret/getall", array(
                        "?comment" => $contrato->id,
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
                            "?comment" => $contrato->servicio,  // NOMBRE CLIENTE
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
                        "?comment" => $contrato->servicio,
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
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }
    
    public function state($id){
        $this->getAllPermissions(Auth::user()->id);
        
        $contrato=Contrato::find($id);
        $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();
        
        $API = new RouterosAPI();
        $API->port = $mikrotik->puerto_api;
        
        if ($contrato) {
            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                
                $API->write('/ip/firewall/address-list/print', TRUE);
                $ARRAYS = $API->read();
                
                if($contrato->state == 'enabled'){
                    $API->comm("/ip/firewall/address-list/add", array(
                        "address" => $contrato->ip,
                        "comment" => $contrato->servicio,
                        "list" => 'morosos'
                        )
                    );
                    $contrato->state = 'disabled';
                    $descripcion = '<i class="fas fa-check text-success"></i> <b>Cambio de Status</b> de Habilitado a Deshabilitado<br>';
                }else{
                    //BUSCAMOS EL ID POR LA IP DEL CONTRATO
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
                
                $mensaje='EL CONTRATO NRO. '.$contrato->nro.' HA SIDO '.$contrato->status();
                $type = 'success';
            } else {
                $mensaje='EL CONTRATO NRO. '.$contrato->nro.' NO HA PODIDO SER ACTUALIZADO';
                $type = 'danger';
            }
            return back()->with($type, $mensaje);
        }
        return redirect('empresa/contratos')->with('danger', 'EL CONTRATO DE SERVICIOS NO HA ENCONTRADO');
    }
  
    public function exportar(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Contratos";
        $titulosColumnas = array('Nro', 'Cliente', 'Identificacion', 'Celular', 'Correo Electronico', 'Plan', 'Direccion IP', 'Direccion MAC', 'Estado', 'Grupo de Corte');
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


        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i=4;
        $letra=0;
        
        $contratos = Contrato::query()
			->select('contracts.*', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.nit as c_nit', 'contactos.celular as c_telefono', 'contactos.email as c_email', 'contactos.barrio as c_barrio')
			->join('contactos', 'contracts.client_id', '=', 'contactos.id');
			
		if(isset($request->client_id)){
            $contratos->where('contracts.client_id', $request->client_id);
        }
        if(isset($request->plan)){
            $contratos->where('contracts.plan_id', $request->plan);
        }
        if(isset($request->ip)){
            $contratos->where('contracts.ip', $request->ip);
        }
        if(isset($request->mac)){
            $contratos->where('contracts.mac_address', $request->mac);
        }
        if(isset($request->state)){
            $contratos->where('contracts.state', $request->state);
        }
        if(isset($request->grupo_cort)){
            $contratos->where('contracts.grupo_corte', $request->grupo_cort);
        }
        $contratos = $contratos->where('contracts.status', 1)->get();
        
        foreach ($contratos as $contrato) {

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $contrato->nro)
                ->setCellValue($letras[1].$i, $contrato->c_nombre)
                ->setCellValue($letras[2].$i, $contrato->c_nit)
                ->setCellValue($letras[3].$i, $contrato->c_telefono)
                ->setCellValue($letras[4].$i, $contrato->c_email)
                ->setCellValue($letras[5].$i, $contrato->plan()->name)
                ->setCellValue($letras[6].$i, $contrato->ip)
                ->setCellValue($letras[7].$i, $contrato->mac_address)
                ->setCellValue($letras[8].$i, $contrato->status())
                ->setCellValue($letras[9].$i, $contrato->grupo_corte('true'));
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
			    $API->write("?name=".$contrato->servicio,true);  
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
                return response()->json([
                    'success' => false,
                    'icon'    => 'error',
                    'title'   => 'ERROR',
                    'text'    => 'NO SE HA PODIDO REALIZAR EL PING AL CONTRATO DE SERVICIOS'
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
        if ($contrato) {
            view()->share(['icon'=>'fas fa-chart-area', 'title' => 'Log | Contrato: '.$contrato->nro]);
            return view('contratos.log')->with(compact('contrato'));
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
                return date('d-m-Y h:m:s A', strtotime($contrato->created_at));
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
}
