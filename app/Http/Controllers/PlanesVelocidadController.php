<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Empresa;
use Carbon\Carbon;
use DB;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Validator;
use Illuminate\Validation\Rule;
use Auth;
use Session;
use App\Rules\guion;
use App\Funcion;

use App\Mikrotik;
use App\PlanesVelocidad;

use App\Impuesto;  
use App\Model\Inventario\Inventario; 
use App\Contrato;

include_once(app_path() .'/../public/routeros_api.class.php');
include_once(app_path() .'/../public/api_mt_include2.php');

use routeros_api;
use RouterosAPI;
use StdClass;
use App\Campos;

class PlanesVelocidadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'mikrotik', 'subseccion' => 'gestion_planes', 'title' => 'Planes de Velocidad', 'icon' =>'fas fa-server']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);

        $tabla = Campos::where('modulo', 10)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
        $mikrotiks = Mikrotik::where('empresa', Auth::user()->empresa)->get();
        return view('planesvelocidad.index')->with(compact('mikrotiks','tabla'));
    }

    public function planes(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $moneda = auth()->user()->empresa()->moneda;
        $planes = PlanesVelocidad::query();

        if ($request->filtro == true) {
            if($request->name){
                $planes->where(function ($query) use ($request) {
                    $query->orWhere('name', 'like', "%{$request->name}%");
                });
            }
            if($request->price){
                $planes->where(function ($query) use ($request) {
                    $query->orWhere('price', 'like', "%{$request->price}%");
                });
            }
            if($request->download){
                $planes->where(function ($query) use ($request) {
                    $query->orWhere('download', 'like', "%{$request->download}%");
                });
            }
            if($request->upload){
                $planes->where(function ($query) use ($request) {
                    $query->orWhere('upload', 'like', "%{$request->upload}%");
                });
            }
            if($request->type){
                if($request->type == 'A'){
                    $type = 0;
                }else{
                    $type = $request->type;
                }
                $planes->where(function ($query) use ($type) {
                    $query->orWhere('type', $type);
                });
            }
            if($request->mikrotik_s){
                $planes->where(function ($query) use ($request) {
                    $query->orWhere('mikrotik', $request->mikrotik_s);
                });
            }
            if($request->status){
                if($request->status == 'A'){
                    $status = 0;
                }else{
                    $status = $request->status;
                }
                $planes->where(function ($query) use ($status) {
                    $query->orWhere('status', $status);
                });
            }
            if($request->tipo_plan){
                $planes->where(function ($query) use ($request) {
                    $query->orWhere('tipo_plan', $request->tipo_plan);
                });
            }
        }

        $planes->where('planes_velocidad.empresa', auth()->user()->empresa);

        return datatables()->eloquent($planes)
            ->editColumn('name', function (PlanesVelocidad $plan) {
                return "<div class='elipsis-short-300'><a href=" . route('planes-velocidad.show', $plan->id) . ">{$plan->name}</a></div>";
            })
            ->editColumn('price', function (PlanesVelocidad $plan) use ($moneda) {
                return "{$moneda} {$plan->parsear($plan->price)}";
            })
            ->editColumn('download', function (PlanesVelocidad $plan) {
                return $plan->download;
            })
            ->editColumn('upload', function (PlanesVelocidad $plan) {
                return $plan->upload;
            })
            ->editColumn('type', function (PlanesVelocidad $plan) {
                return '<span class="text-' . $plan->type(true) . '">' . $plan->type(). '</span>';
            })
            ->editColumn('mikrotik', function (PlanesVelocidad $plan) {
                return "<a href=" . route('mikrotik.show', $plan->mikrotik()->id) . " target='_blank'>{$plan->mikrotik()->nombre}</div></a>";
                return ;
            })
            ->editColumn('status', function (PlanesVelocidad $plan) {
                return   '<span class="text-' . $plan->status(true) . '">' . $plan->status(). '</span>';
            })
            ->editColumn('tipo_plan', function (PlanesVelocidad $plan) {
                return $plan->tipo();
            })
            ->editColumn('nro_clientes', function (PlanesVelocidad $plan) {
                return '<span class="badge badge-success">'.$plan->uso_state('enabled').'</span> Habilitados<br>
                            <span class="badge badge-danger mt-1">'.$plan->uso_state('disabled').'</span> Deshabilitados';
            })
            ->addColumn('acciones', $modoLectura ?  "" : "planesvelocidad.acciones")
            ->rawColumns(['acciones', 'name', 'status', 'type', 'mikrotik', 'nro_clientes'])
            ->toJson();
    }
    
    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Plan', 'icon' => 'fas fa-server']);
        $mikrotiks = Mikrotik::where('empresa', Auth::user()->empresa)->get();
        
        return view('planesvelocidad.create')->with(compact('mikrotiks'));
    }
    
    public function store(Request $request){
        $request->validate([
            'name' => 'required|max:200',
            'price' => 'required|max:200',
            'upload' => 'required|max:200',
            'download' => 'required|max:200',
            'type' => 'required|max:200',
            'tipo_plan' => 'required|max:200',
        ]);

        for ($i=0; $i < count($request->mikrotik) ; $i++) {
            $inventario                = new Inventario;
            $inventario->empresa       = Auth::user()->empresa;
            $inventario->producto      = strtoupper($request->name);
            $inventario->ref           = strtoupper($request->name);
            $inventario->precio        = $this->precision($request->price);

            $inventario->id_impuesto   = ($request->tipo_plan == 2) ? 1 : 2;
            $inventario->impuesto      = ($request->tipo_plan == 2) ? 19 : 0;

            $inventario->tipo_producto = 2;
            $inventario->unidad        = 1;
            $inventario->nro           = 0;
            $inventario->categoria     = 116;
            $inventario->lista         = 0;
            $inventario->type          = 'PLAN';
            $inventario->save();

            $plan = new PlanesVelocidad;
            $plan->mikrotik = $request->mikrotik[$i];
            $plan->name = $request->name;
            $plan->price = $request->price;
            $plan->upload = $request->upload.''.$request->inicial_download;
            $plan->download = $request->download.''.$request->inicial_upload;
            $plan->type = $request->type;
            $plan->address_list = $request->address_list;
            $plan->created_by = Auth::user()->id;
            $plan->tipo_plan = $request->tipo_plan;
            $plan->burst_limit_subida = $request->burst_limit_subida.''.$request->inicial_burst_limit_subida;
            $plan->burst_limit_bajada = $request->burst_limit_bajada.''.$request->inicial_burst_limit_bajada;
            $plan->burst_threshold_subida = $request->burst_threshold_subida.''.$request->inicial_burst_threshold_subida;
            $plan->burst_threshold_bajada = $request->burst_threshold_bajada.''.$request->inicial_burst_threshold_bajada;
            $plan->burst_time_subida = $request->burst_time_subida;
            $plan->burst_time_bajada = $request->burst_time_bajada;
            $plan->queue_type_subida = $request->queue_type_subida;
            $plan->queue_type_bajada = $request->queue_type_bajada;
            $plan->parenta = $request->parenta;
            $plan->prioridad = $request->prioridad;
            $plan->limit_at_subida = $request->limit_at_subida.''.$request->inicial_limit_at_subida;
            $plan->limit_at_bajada = $request->limit_at_bajada.''.$request->inicial_limit_at_bajada;
            $plan->item = $inventario->id;
            $plan->empresa = Auth::user()->empresa;
            $plan->dhcp_server = $request->dhcp_server;
            $plan->save();
        }
            
        $mensaje = 'SE HA CREADO SATISFACTORIAMENTE EL PLAN';
        return redirect('empresa/planes-velocidad')->with('success', $mensaje)->with('mikrotik_id', $plan->id);
    }

    public function storeBack(Request $request){
        if (!$request->mikrotik) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'Mikrotik Asociada no ha sido seleccionada';
            echo json_encode($arrayPost);
            exit;
        }
        if (!$request->name) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'El nombre del plan no ha sido definido';
            echo json_encode($arrayPost);
            exit;
        }
        if (!$request->price) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'El precio del plan no ha sido definido';
            echo json_encode($arrayPost);
            exit;
        }
        if (!$request->download) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'La Vel. de Descarga no ha sido definida';
            echo json_encode($arrayPost);
            exit;
        }
        if (!$request->upload) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'La Vel. de Subida no ha sido definida';
            echo json_encode($arrayPost);
            exit;
        }
        if (!$request->tipo_plan) {
            $arrayPost['status'] = 'error';
            $arrayPost['mensaje'] = 'El tipo de plan no ha sido definido';
            echo json_encode($arrayPost);
            exit;
        }

        for ($i=0; $i < count($request->mikrotik) ; $i++) {
            $inventario                   = new Inventario;
            $inventario->empresa          = Auth::user()->empresa;
            $inventario->producto         = strtoupper($request->name);
            $inventario->ref              = strtoupper($request->name);
            $inventario->precio           = $this->precision($request->price);

            $inventario->id_impuesto      = ($request->tipo_plan == 2) ? 1 : 2;
            $inventario->impuesto         = ($request->tipo_plan == 2) ? 19 : 0;

            $inventario->tipo_producto    = 2;
            $inventario->unidad           = 1;
            $inventario->nro              = 0;
            $inventario->categoria        = 116;
            $inventario->lista            = 0;
            $inventario->type             = 'PLAN';
            $inventario->save();

            $plan                         = new PlanesVelocidad;
            $plan->mikrotik               = $request->mikrotik[$i];
            $plan->name                   = $request->name;
            $plan->price                  = $request->price;
            $plan->upload                 = $request->upload.''.$request->inicial_download;
            $plan->download               = $request->download.''.$request->inicial_upload;
            $plan->type                   = $request->type;
            $plan->address_list           = $request->address_list;
            $plan->created_by             = Auth::user()->id;
            $plan->tipo_plan              = $request->tipo_plan;
            $plan->burst_limit_subida     = $request->burst_limit_subida.''.$request->inicial_burst_limit_subida;
            $plan->burst_limit_bajada     = $request->burst_limit_bajada.''.$request->inicial_burst_limit_bajada;
            $plan->burst_threshold_subida = $request->burst_threshold_subida.''.$request->inicial_burst_threshold_subida;
            $plan->burst_threshold_bajada = $request->burst_threshold_bajada.''.$request->inicial_burst_threshold_bajada;
            $plan->burst_time_subida      = $request->burst_time_subida;
            $plan->burst_time_bajada      = $request->burst_time_bajada;
            $plan->queue_type_subida      = $request->queue_type_subida;
            $plan->queue_type_bajada      = $request->queue_type_bajada;
            $plan->parenta                = $request->parenta;
            $plan->prioridad              = $request->prioridad;
            $plan->limit_at_subida        = $request->limit_at_subida.''.$request->inicial_limit_at_subida;
            $plan->limit_at_bajada        = $request->limit_at_bajada.''.$request->inicial_limit_at_bajada;
            $plan->item                   = $inventario->id;
            $plan->empresa                = Auth::user()->empresa;
            $plan->dhcp_server            = $request->dhcp_server;
            $plan->save();
        }

        if ($plan) {
            $arrayPost['success'] = true;
            $arrayPost['id']      = PlanesVelocidad::all()->last()->id;
            $arrayPost['name']    = PlanesVelocidad::all()->last()->name;
            $arrayPost['type']    = (PlanesVelocidad::all()->last()->type == 0) ? 'Plan Queue Simple' : 'Plan PCQ';
            echo json_encode($arrayPost);
            exit;
        }
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $plan = PlanesVelocidad::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($plan) {
            view()->share(['title' => 'Modificar Plan', 'icon' => 'fas fa-server']);
            $mikrotiks = Mikrotik::where('empresa', Auth::user()->empresa)->get();
            return view('planesvelocidad.edit')->with(compact('plan', 'mikrotiks'));
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
    
    public function update(Request $request, $id){
        $plan = PlanesVelocidad::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($plan) {
            $request->validate([
                'name' => 'required|max:200',
                'price' => 'required|max:200',
                'upload' => 'required|max:200',
                'download' => 'required|max:200',
                'type' => 'required|max:200',
                'mikrotik' => 'required|max:200',
            ]);
            
            $plan->mikrotik = $request->mikrotik;
            $plan->name = $request->name;
            $plan->price = $request->price;
            $plan->upload = $request->upload.''.$request->inicial_download;
            $plan->download = $request->download.''.$request->inicial_upload;
            $plan->type = $request->type;
            $plan->address_list = $request->address_list;
            $plan->updated_by = Auth::user()->id;
            $plan->tipo_plan = $request->tipo_plan;
            $plan->burst_limit_subida = $request->burst_limit_subida.''.$request->inicial_burst_limit_subida;
            $plan->burst_limit_bajada = $request->burst_limit_bajada.''.$request->inicial_burst_limit_bajada;
            $plan->burst_threshold_subida = $request->burst_threshold_subida.''.$request->inicial_burst_threshold_subida;
            $plan->burst_threshold_bajada = $request->burst_threshold_bajada.''.$request->inicial_burst_threshold_bajada;
            $plan->burst_time_subida = $request->burst_time_subida;
            $plan->burst_time_bajada = $request->burst_time_bajada;
            $plan->queue_type_subida = $request->queue_type_subida;
            $plan->queue_type_bajada = $request->queue_type_bajada;
            $plan->parenta = $request->parenta;
            $plan->prioridad = $request->prioridad;
            $plan->dhcp_server = $request->dhcp_server;
            $plan->limit_at_subida = $request->limit_at_subida.''.$request->inicial_limit_at_subida;
            $plan->limit_at_bajada = $request->limit_at_bajada.''.$request->inicial_limit_at_bajada;
            $plan->save();
            
            $inventario              = Inventario::find($plan->item);
            $inventario->producto    = strtoupper($request->name);
            $inventario->ref         = strtoupper($request->name);
            $inventario->precio      = $this->precision($request->price);
            $inventario->id_impuesto = ($request->tipo_plan == 2) ? 1 : 2;
            $inventario->impuesto    = ($request->tipo_plan == 2) ? 19 : 0;
            $inventario->save();
            
            $mensaje = 'SE HA MODIFICADO SATISFACTORIAMENTE EL PLAN';
            return redirect('empresa/planes-velocidad')->with('success', $mensaje)->with('plan_id', $plan->id);
            return redirect('empresa/planes-velocidad/'.$plan->id.'/aplicar-cambios')->with('success', $mensaje)->with('plan_id', $plan->id);
      }
      return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
    
    public function destroy($id){
        $plan = PlanesVelocidad::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($plan) {
            $plan->delete();
            return redirect('empresa/planes-velocidad')->with('success', 'Se ha eliminado correctamente el Plan');
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
    
    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $plan = PlanesVelocidad::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($plan) {
            $tabla = Campos::where('modulo', 2)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
            view()->share(['icon' => 'fas fa-server', 'title' => 'Plan: '.$plan->name]);
            return view('planesvelocidad.show')->with(compact('plan', 'tabla'));
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
    
    public function status($id){
        $this->getAllPermissions(Auth::user()->id);
        $plan = PlanesVelocidad::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($plan) {
            if ($plan->status == 1) {
                $mensaje = 'SE HA DESHABILITADO DE PLAN SATISFACTORIAMENTE';
                $plan->status = 0;
                $plan->save();
            } else {
                $mensaje = 'SE HA HABILITADO DE PLAN SATISFACTORIAMENTE';
                $plan->status = 1;
                $plan->save();
            }
            return redirect('empresa/planes-velocidad')->with('success', $mensaje);
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
    
    public function reglas($id){
        $this->getAllPermissions(Auth::user()->id);
        $plan = PlanesVelocidad::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($plan) {
            $mikrotik = $plan->mikrotik();
            $API = new RouterosAPI();
            
            $API->port = $mikrotik->puerto_api;
            $API->debug = true;
            
            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                #REGLA BAJADA
                $API->comm("/ip/firewall/mangle/add", array(
                    "chain" => "postrouting",
                    "dst-address-list" => $plan->name,
                    "action" => "mark-packet",
                    "new-packet-mark" => strtolower(str_replace(' ', '_', $plan->name))."_down",
                    "passthrough"=>"no",
                    "comment" => $plan->name
                    )
                );
                
                #REGLA SUBIDA
                $API->comm("/ip/firewall/mangle/add", array(
                    "chain" => "forward",
                    "src-address-list" => $plan->name,
                    "action" => "mark-packet",
                    "new-packet-mark" => strtolower(str_replace(' ', '_', $plan->name))."_up",
                    "passthrough"=>"no",
                    "comment" => $plan->name
                    )
                );
                
                #CREACI�0�7N PCQ SUBIDA
                $API->comm("/queue/type/add", array(
                    "name" => strtolower(str_replace(' ', '_', $plan->name))."_up",
                    "kind" => "pcq",
                    "pcq-rate" => $plan->upload,
                    "pcq-classifier" => "dst-address"
                    
                    )
                );
                
                #CREACI�0�7N PCQ BAJADA
                $API->comm("/queue/type/add", array(
                    "name" => strtolower(str_replace(' ', '_', $plan->name))."_down",
                    "kind" => "pcq",
                    "pcq-rate" => $plan->download,
                    "pcq-classifier" => "src-address"
                    )
                );
                
                #COLA PADRE DE BAJADA
                $API->comm("/queue/tree/add", array(
                    "name" => "DOWN-GLOBAL",
                    "parent" => "global"
                    )
                );
                
                #COLA HIJA DE BAJADA
                $API->comm("/queue/tree/add", array(
                    "name" => strtolower(str_replace(' ', '_', $plan->name))."_down",
                    "packet-mark" => strtolower(str_replace(' ', '_', $plan->name))."_down",
                    "queue" => strtolower(str_replace(' ', '_', $plan->name))."_down",
                    "parent" => "DOWN-GLOBAL"
                    )
                );
                
                #COLA PADRE DE SUBIDA
                $API->comm("/queue/tree/add", array(
                    "name" => "UP-GLOBAL",
                    "parent" => "global"
                    )
                );
                
                #COLA HIJA DE SUBIDA
                $API->comm("/queue/tree/add", array(
                    "name" => strtolower(str_replace(' ', '_', $plan->name))."_up",
                    "packet-mark" => strtolower(str_replace(' ', '_', $plan->name))."_up",
                    "queue" => strtolower(str_replace(' ', '_', $plan->name))."_up",
                    "parent" => "UP-GLOBAL"
                    )
                );
                
                $API->disconnect();
                
                $mensaje='Reglas aplicadas satisfactoriamente a la Mikrotik '.$mikrotik->nombre;
                $type = 'success';
            } else {
                $mensaje='Reglas no aplicadas a la Mikrotik '.$mikrotik->nombre.', intente nuevamente.';
                $type = 'danger';
            }
            return redirect('empresa/planes-velocidad')->with($type, $mensaje);
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }

    public function aplicar_cambios($id){
        $this->getAllPermissions(Auth::user()->id);
        $plan = PlanesVelocidad::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($plan) {
            $contratos = Contrato::where('plan_id', $plan->id)->where('status', 1)->where('empresa', Auth::user()->empresa)->get();
            view()->share(['title' => "Contratos con Plan", 'icon' =>'fas fa-project-diagram', 'middel' => true]);
            return view('planesvelocidad.aplicar_cambios')->with(compact('contratos', 'plan'));
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }

    public function aplicando_cambios($contratos){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $contratos = explode(",", $contratos);

        for ($i=0; $i < count($contratos) ; $i++) {
            $contrato =Contrato::find($contratos[$i]);
            $plan     = PlanesVelocidad::find($contrato->plan_id);

            if ($plan) {
                $mikrotik = Mikrotik::find($plan->mikrotik);
                $API = new RouterosAPI();
                $API->port = $mikrotik->puerto_api;

                $priority = ($plan->prioridad) ? $plan->prioridad.'/'.$plan->prioridad : '';
                $burst_limit = ($plan->burst_limit_subida) ? $plan->burst_limit_subida.'M/'.$plan->burst_limit_bajada.'M' : '';
                $burst_threshold = ($plan->burst_threshold_subida) ? $plan->burst_threshold_subida.'M/'.$plan->burst_threshold_bajada.'M': '';

                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    $API->write('/queue/simple/getall/print', TRUE);
                    $ARRAYS = $API->read();

                    $API->write('/ip/firewall/address-list/print', false);
                    $API->write('?target='.$contrato->ip, false);
                    $API->write('=.proplist=.id');

                    if(count($ARRAYS)>0){
                        $API->comm("/queue/simple/set", array(
                            ".id"             => $name[0][".id"],
                            "max-limit"       => $plan->upload.'/'.$plan->download,
                            "priority"        => $priority,
                            "burst-limit"     => $burst_limit,
                            "burst-threshold" => $burst_threshold
                            )
                        );
                    }else{
                        $fail++;
                    }
                    $API->disconnect();
                }
            }else{
                $fail++;
            }
        }

        return response()->json([
            'success'   => true,
            'fallidos'  => $fail,
            'correctos' => $succ
        ]);
    }
}
