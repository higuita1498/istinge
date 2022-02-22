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

class PlanesVelocidadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'mikrotik', 'subseccion' => 'gestion_planes', 'title' => 'Gestión de Planes de Velocidad', 'icon' =>'fas fa-server']);
    }
    
    public function index(){
      $this->getAllPermissions(Auth::user()->id);
      $planes = PlanesVelocidad::all();
      return view('planesvelocidad.index')->with(compact('planes'));
    }
    
    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Plan', 'icon' => 'fas fa-server']);
        $mikrotiks = Mikrotik::all();
        
        return view('planesvelocidad.create')->with(compact('mikrotiks'));
    }
    
    public function store(Request $request){
        $request->validate([
            'name' => 'required|max:200',
            'price' => 'required|max:200',
            'upload' => 'required|max:200',
            'download' => 'required|max:200',
            'type' => 'required|max:200',
            'mikrotik' => 'required|max:200',
        ]);
        
        $inventario = new Inventario;
        $inventario->empresa=Auth::user()->empresa;
        $inventario->producto=strtoupper($request->name);
        $inventario->ref=strtoupper($request->name);
        $inventario->precio=$this->precision($request->price);
        $inventario->id_impuesto=2;
        $inventario->impuesto=0;
        $inventario->tipo_producto=2;
        $inventario->unidad=1;
        $inventario->nro=0;
        $inventario->categoria=116;
        $inventario->lista = 0;
        $inventario->type = 'PLAN';
        $inventario->save();
        
        $plan = new PlanesVelocidad;
        $plan->mikrotik = $request->mikrotik;
        $plan->name = $request->name;
        $plan->price = $request->price;
        $plan->upload = $request->upload;
        $plan->download = $request->download;
        $plan->type = $request->type;
        $plan->address_list = $request->address_list;
        $plan->created_by = Auth::user()->id;
        $plan->burst_limit_subida = $request->burst_limit_subida;
        $plan->burst_limit_bajada = $request->burst_limit_bajada;
        $plan->burst_threshold_subida = $request->burst_threshold_subida;
        $plan->burst_threshold_bajada = $request->burst_threshold_bajada;
        $plan->burst_time_subida = $request->burst_time_subida;
        $plan->burst_time_bajada = $request->burst_time_bajada;
        $plan->queue_type_subida = $request->queue_type_subida;
        $plan->queue_type_bajada = $request->queue_type_bajada;
        $plan->parenta = $request->parenta;
        $plan->prioridad = $request->prioridad;
        $plan->item = $inventario->id;
        $plan->save();
            
        $mensaje='Se ha creado satisfactoriamente el plan';
        return redirect('empresa/planes-velocidad')->with('success', $mensaje)->with('mikrotik_id', $plan->id);
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $plan = PlanesVelocidad::where('id', $id)->first();
        if ($plan) {
            view()->share(['title' => 'Modificar Plan', 'icon' => 'fas fa-server']);
            $mikrotiks = Mikrotik::all();
            return view('planesvelocidad.edit')->with(compact('plan', 'mikrotiks'));
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
    
    public function update(Request $request, $id){
        $plan = PlanesVelocidad::where('id', $id)->first();
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
            $plan->upload = $request->upload;
            $plan->download = $request->download;
            $plan->type = $request->type;
            $plan->address_list = $request->address_list;
            $plan->updated_by = Auth::user()->id;
            $plan->burst_limit_subida = $request->burst_limit_subida;
            $plan->burst_limit_bajada = $request->burst_limit_bajada;
            $plan->burst_threshold_subida = $request->burst_threshold_subida;
            $plan->burst_threshold_bajada = $request->burst_threshold_bajada;
            $plan->burst_time_subida = $request->burst_time_subida;
            $plan->burst_time_bajada = $request->burst_time_bajada;
            $plan->queue_type_subida = $request->queue_type_subida;
            $plan->queue_type_bajada = $request->queue_type_bajada;
            $plan->parenta = $request->parenta;
            $plan->prioridad = $request->prioridad;
            $plan->save();
            
            $inventario = Inventario::find($plan->item);
            $inventario->producto = strtoupper($request->name);
            $inventario->ref      = strtoupper($request->name);
            $inventario->precio   = $this->precision($request->price);
            $inventario->save();
            
            $mensaje = 'SE HA MODIFICADO SATISFACTORIAMENTE EL PLAN';
            return redirect('empresa/planes-velocidad/'.$plan->id.'/aplicar-cambios')->with('success', $mensaje)->with('plan_id', $plan->id);
      }
      return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
    
    public function destroy($id){
        $plan = PlanesVelocidad::find($id);
        if ($plan) {
            $plan->delete();
            return redirect('empresa/planes-velocidad')->with('success', 'Se ha eliminado correctamente el Plan');
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
    
    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $plan = PlanesVelocidad::where('id', $id)->first();
        if ($plan) {
            view()->share(['icon' => 'fas fa-server', 'title' => 'Plan: '.$plan->name]);
            return view('planesvelocidad.show')->with(compact('plan'));
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
    
    public function status($id){
        $this->getAllPermissions(Auth::user()->id);
        $plan = PlanesVelocidad::find($id);
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
        $plan = PlanesVelocidad::find($id);
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
        $plan = PlanesVelocidad::find($id);
        if ($plan) {
            $contratos = Contrato::where('plan_id', $plan->id)->where('status', 1)->get();
            view()->share(['title' => "Contratos con Plan", 'icon' =>'fas fa-project-diagram', 'middel' => true]);
            return view('planesvelocidad.aplicar_cambios')->with(compact('contratos', 'plan'));
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }

    public function aplicando_cambios($id){
        $this->getAllPermissions(Auth::user()->id);
        $plan = PlanesVelocidad::find($id);
        if ($plan) {
            $contratos = Contrato::where('plan_id', $plan->id)->where('status', 1)->get();
            $mikrotik = Mikrotik::find($plan->mikrotik);

            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            $API->debug = true;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                foreach($contratos as $contrato){
                    $name = $API->comm("/queue/simple/getall", array(
                        "?name" => $contrato->servicio,
                        )
                    );

                    if($name){
                        $API->comm("/queue/simple/set", array(
                            ".id"       => $name[0][".id"],
                            "max-limit"   => $plan->upload.'/'.$plan->download,     // VELOCIDAD PLAN
                            "priority"    => $plan->prioridad.'/'.$plan->prioridad, // PRIORIDAD PLAN
                            "burst-limit" => $plan->burst_limit_subida.'M/'.$plan->burst_limit_bajada.'M', //
                            "burst-threshold" => $plan->burst_threshold_subida.'M/'.$plan->burst_threshold_bajada.'M',
                            )
                        );
                    }
                    $API->disconnect();
                }
                $mensaje='Cambios aplicados satisfactoriamente en la Mikrotik '.$mikrotik->nombre;
                $type = 'success';
            } else {
                $mensaje='Cambios no aplicados en la Mikrotik '.$mikrotik->nombre.', intente nuevamente.';
                $type = 'danger';
            }
            $mensaje='Cambios no aplicados en la Mikrotik '.$mikrotik->nombre.', intente nuevamente.';
            $type = 'danger';
            return redirect('empresa/planes-velocidad')->with($type, $mensaje);
        }
        return redirect('empresa/planes-velocidad')->with('danger', 'No existe un registro con ese id');
    }
}
