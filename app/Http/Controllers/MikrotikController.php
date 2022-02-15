<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Empresa; 

use Carbon\Carbon; use DB;
use App\User;  
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Validator;  
use Illuminate\Validation\Rule;  
use Auth;
use Session; 
use App\Rules\guion;
use App\Contacto;
use App\Contrato;
use App\Numeracion;
use App\Mikrotik;
use App\PlanesVelocidad;
use App\Segmento;

include_once(app_path() .'/../public/routeros_api.class.php');
include_once(app_path() .'/../public/api_mt_include2.php');

use routeros_api;
use RouterosAPI;
use StdClass;

class MikrotikController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['seccion' => 'mikrotik', 'subseccion' => 'gestion_mikrotik', 'title' => 'Gestión de Mikrotik', 'icon' =>'fas fa-server']);
    }
    
    public function index(){
      $this->getAllPermissions(Auth::user()->id);

      $mikrotiks = Mikrotik::all();
      return view('mikrotik.index')->with(compact('mikrotiks'));
    }
    
    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        
        return view('mikrotik.create');
    }
    
    public function store(Request $request){
        $request->validate([
            'nombre' => 'required',
            'ip' => 'required',
            'usuario' => 'required',
            'clave' => 'required',
            'puerto_api' => 'required',
            'segmento_ip' => 'required',
            'interfaz' => 'required'
        ]);
        
        $mikrotik = new Mikrotik;
        $mikrotik->nombre = $request->nombre;
        $mikrotik->ip = $request->ip;
        $mikrotik->puerto_api = $request->puerto_api;
        $mikrotik->puerto_web = $request->puerto_web;
        $mikrotik->usuario = $request->usuario;
        $mikrotik->clave = $request->clave;
        $mikrotik->interfaz = $request->interfaz;
        $mikrotik->created_by = Auth::user()->id;
        $mikrotik->save();
        
        for ($i = 0; $i < count($request->segmento_ip); $i++) {
            $segmento = new Segmento;
            $segmento->mikrotik = $mikrotik->id;
            $segmento->segmento = $request->segmento_ip[$i];
            $segmento->save();
        }
        
        $mensaje='Se ha creado satisfactoriamente el mikrotik';
        return redirect('empresa/mikrotik')->with('success', strtoupper($mensaje))->with('mikrotik_id', $mikrotik->id);
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::where('id', $id)->first();
        
        if ($mikrotik) {
            $segmentos = Segmento::where('mikrotik', $mikrotik->id)->get();
            return view('mikrotik.edit')->with(compact('mikrotik', 'segmentos'));
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
    
    public function update(Request $request, $id){
        $mikrotik = Mikrotik::where('id', $id)->first();
        if ($mikrotik) {
            $request->validate([
                'nombre' => 'required',
                'ip' => 'required',
                'usuario' => 'required',
                'clave' => 'required',
                'puerto_api' => 'required',
                'interfaz' => 'required'
            ]);
            
            $mikrotik->nombre = $request->nombre;
            $mikrotik->ip = $request->ip;
            $mikrotik->puerto_api = $request->puerto_api;
            $mikrotik->puerto_web = $request->puerto_web;
            $mikrotik->interfaz = $request->interfaz;
            $mikrotik->usuario = $request->usuario;
            $mikrotik->clave = $request->clave;
            $mikrotik->updated_by = Auth::user()->id;
            $mikrotik->save();
            
            $segmentos = Segmento::where('mikrotik', $mikrotik->id)->get();
            foreach($segmentos as $segmento){
                $segmento->delete();
            }
            
            for ($i = 0; $i < count($request->segmento_ip); $i++) {
                $segmento = new Segmento;
                $segmento->mikrotik = $mikrotik->id;
                $segmento->segmento = $request->segmento_ip[$i];
                $segmento->save();
            }
            
            $mensaje='Se ha modificado satisfactoriamente el Mikrotik';
            return redirect('empresa/mikrotik')->with('success', strtoupper($mensaje))->with('mikrotik_id', $mikrotik->id);
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
    
    public function destroy($id){
        $mikrotik = Mikrotik::where('id', $id)->first();
        if ($mikrotik) {
            $segmentos = Segmento::where('mikrotik', $mikrotik->id)->get();
            foreach($segmentos as $segmento){
                $segmento->delete();
            }
            $mikrotik->delete();
            
            return redirect('empresa/mikrotik')->with('success', strtoupper('Se ha eliminado correctamente el Mikrotik'));
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
    
    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::where('id', $id)->first();
        if ($mikrotik) {
            view()->share(['title' => 'Mikrotik: '.$mikrotik->nombre, 'icon' =>'fas fa-server']);
            $segmentos = Segmento::where('mikrotik', $mikrotik->id)->get();
            return view('mikrotik.show')->with(compact('mikrotik', 'segmentos'));
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
    
    public function conectar($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::where('id', $id)->first();
        if ($mikrotik) {
            $API = new RouterosAPI();
            
            $API->port = $mikrotik->puerto_api;
            
            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                //$API->write('/ip/route/print');
                //$API->write('/ip/address/print');
                //$API->write("/interface/ethernet/getall", true);
                //$API->write("/tool/user-manager/user/getall", true);
                //$API->write("/system/identity/getall", true);
                
                $API->write('/system/resource/print');
                $READ = $API->read(false);
                $ARRAY = $API->parseResponse($READ);
                
                $API->write("/system/identity/getall", true);
                $READ = $API->read(false);
                $ARRAYS = $API->parseResponse($READ);
            
                $API->disconnect();
                
                $mikrotik->nombre = $ARRAYS[0]['name'];
                $mikrotik->board = $ARRAY[0]['board-name'];
                $mikrotik->uptime = $ARRAY[0]['uptime'];
                $mikrotik->cpu = $ARRAY[0]['cpu-load'];
                $mikrotik->version = $ARRAY[0]['version'];
                $mikrotik->buildtime = $ARRAY[0]['build-time'];
                $mikrotik->freememory = $ARRAY[0]['free-memory'];
                $mikrotik->totalmemory = $ARRAY[0]['total-memory'];
                $mikrotik->cpucount = $ARRAY[0]['cpu-count'];
                $mikrotik->cpufrequency = $ARRAY[0]['cpu-frequency'].' MHz';
                $mikrotik->cpuload = $ARRAY[0]['cpu-load'].' %';
                $mikrotik->freehddspace = $ARRAY[0]['free-hdd-space'];
                $mikrotik->totalhddspace = $ARRAY[0]['total-hdd-space'];
                $mikrotik->architecturename = $ARRAY[0]['architecture-name'];
                $mikrotik->platform = $ARRAY[0]['platform'];
                $mikrotik->status = 1;
                $mikrotik->save();
                $mensaje='Conexión a la Mikrotik '.$mikrotik->nombre.' Realizada';
                $type = 'success';
            } else {
                $mikrotik->status = 0;
                $mikrotik->save();
                $mensaje='Conexión a la Mikrotik '.$mikrotik->nombre.' No Realizada';
                $type = 'danger';
            }
            return redirect('empresa/mikrotik')->with($type, $mensaje)->with('mikrotik_id', $mikrotik->id);
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
    
    public function reglas($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::where('id', $id)->first();
        if ($mikrotik) {
            $API = new RouterosAPI();
            
            $API->port = $mikrotik->puerto_api;
            
            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                
                $API->comm("/ip/firewall/nat/add\n=action=redirect\n=chain=dstnat\n=comment='Manager - Suspension de clientes (TCP)'\n=dst-port=!8291\n=protocol=tcp\n=src-address-list=morosos\n=to-ports=999");
                $API->comm("/ip/firewall/nat/add\n=action=redirect\n=chain=dstnat\n=comment='Manager - Suspender clientes(UDP)'\n=dst-port=!8291,53\n=protocol=udp\n=src-address-list=morosos\n=to-ports=999");
                $API->comm("/ip/proxy/set\n=enabled=yes\n=port=999");
                
                $API->disconnect();
                
                $mensaje='Reglas aplicadas satisfactoriamente a la Mikrotik '.$mikrotik->nombre;
                $type = 'success';
            } else {
                $mensaje='Reglas no aplicadas a la Mikrotik '.$mikrotik->nombre.', intente nuevamente.';
                $type = 'danger';
            }
            return redirect('empresa/mikrotik')->with($type, $mensaje)->with('mikrotik_id', $mikrotik->id);
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
    
    public function importar($id){
        return back()->with('danger', 'FUNCIONALIDAD EN DESARROLLO');
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::where('id', $id)->first();
        if ($mikrotik) {
            $API = new RouterosAPI();
            
            $API->port = $mikrotik->puerto_api;
            //$API->debug = true;
            
            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write('/interface/vlan/print');
                $READ = $API->read(false);
                $ARRAYS = $API->parseResponse($READ);
                
                $API->disconnect();
                $i=0;
                dd($ARRAYS);
                for ($i=0; $i <count($ARRAYS) ; $i++) {
                    dd($ARRAYS[$i]['mac-address']);
                }
                
                $plan = PlanesVelocidad::where('id', $request->plan_id)->first();
                $cliente = Contacto::find($request->client_id);
                
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
                $contrato->plan_id                 = $request->plan_id;
                $contrato->nro                     = $nro_contrato;
                $contrato->client_id               = $request->client_id;
                $contrato->server_configuration_id = $mikrotik->id;
                $contrato->mac_address             = $array->mac-address;
                $contrato->fecha_corte             = $request->fecha_corte;
                $contrato->fecha_suspension        = $request->fecha_suspension;
                $contrato->usuario                 = $request->usuario;
                $contrato->password                = $request->password;
                $contrato->conexion                = $request->conexion;
                $contrato->interfaz                = $request->interfaz;
                $contrato->local_address           = $request->local_address;
                $contrato->mac_address             = $request->mac_address;
                $contrato->creador                 = Auth::user()->nombres;
                $contrato->save();
                
                $nro->contrato = $nro_contrato + 1;
                $nro->save();
                
                $mensaje='Se han importado 2345 contratos al sistema desde la mikrotik '.$mikrotik->nombre;
                $type = 'success';
            } else {
                $mensaje='No hemos podido conectar con la mikrotik '.$mikrotik->nombre.', intente nuevamente.';
                $type = 'danger';
            }
            return redirect('empresa/mikrotik')->with($type, $mensaje)->with('mikrotik_id', $mikrotik->id);
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
    
    public function log($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::where('id', $id)->first();
        if ($mikrotik) {
            view()->share(['title' => 'LOG Mikrotik: '.$mikrotik->nombre, 'icon' =>'fas fa-server']);
            return view('mikrotik.log')->with(compact('mikrotik'));
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
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
                return $contrato->created_by()->nombres;
            })
            ->editColumn('descripcion', function (MovimientoLOG $contrato) {
                return $contrato->descripcion;
            })
            ->rawColumns(['created_at', 'created_by', 'descripcion'])
            ->toJson();
    }
    
    public function reiniciar($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::where('id', $id)->first();
        if ($mikrotik) {
            $API = new RouterosAPI();
            
            $API->port = $mikrotik->puerto_api;
            //$API->debug = true;
            
            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                $API->write("/system/reboot");
                $API->read();
                if($API){
                    $mensaje='El Mikrotik '.$mikrotik->nombre.' ha sido reiniciado';
                    $type = 'success';
                }else{
                    $mensaje='ERROR: No hemos podido reiniciar el Mikrotik '.$mikrotik->nombre;
                    $type = 'danger';
                }
                $API->disconnect();
            } else {
                $mensaje='ERROR: No hemos podido reiniciar el Mikrotik '.$mikrotik->nombre;
                $type = 'danger';
            }
            return redirect('empresa/mikrotik')->with($type, $mensaje)->with('mikrotik_id', $mikrotik->id);
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
    
    public function grafica($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::find($id);
        if ($mikrotik) {
            view()->share(['title' => 'Gráfica de Consumo', 'icon' =>'fas fa-chart-area']);
            $segmentos = Segmento::where('mikrotik', $mikrotik->id)->get();
            return view('mikrotik.grafica')->with(compact('mikrotik', 'segmentos'));
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
    
    public function graficajson($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::find($id);
        
        $API = new RouterosAPI();
        $API->port = $mikrotik->puerto_api;
        //$API->debug = true;
        
        if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
            $rows = array(); $rows2 = array(); $Type=0; $Interface='ether1';
            if ($Type==0) {  // Interfaces
                $API->write("/interface/monitor-traffic",false);
                $API->write("=interface=".$mikrotik->interfaz,false);  
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
					echo $ARRAY['!trap'][0]['message'];	 
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

    public function ips_autorizadas($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::find($id);
        if ($mikrotik) {
            $contratos = Contrato::where('server_configuration_id', $mikrotik->id)->get();
            view()->share(['title' => "IP's Autorizadas", 'icon' =>'fas fa-project-diagram', 'middel' => true]);
            return view('mikrotik.ips-autorizadas')->with(compact('contratos', 'mikrotik'));
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }

    public function autorizar_ips($id){
        $this->getAllPermissions(Auth::user()->id);
        $mikrotik = Mikrotik::find($id);
        if ($mikrotik) {
            $contratos = Contrato::where('server_configuration_id', $mikrotik->id)->where('status', 1)->get();

            /*$API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;
            $API->debug = true;

            if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                foreach($contratos as $contrato){
                    $API->comm("/ip/firewall\n=address\n=add\n=list=ips_autorizadas\n=address=".$contrato->ip);
                    $API->disconnect();

                    $contrato->ip_autorizada = 1;
                    $contrato->save();
                }
                $mensaje='Reglas aplicadas satisfactoriamente a la Mikrotik '.$mikrotik->nombre;
                $type = 'success';
            } else {
                $mensaje='Reglas no aplicadas a la Mikrotik '.$mikrotik->nombre.', intente nuevamente.';
                $type = 'danger';
            }*/
            $mensaje='Reglas no aplicadas a la Mikrotik '.$mikrotik->nombre.', intente nuevamente.';
            $type = 'danger';
            return redirect('empresa/mikrotik')->with($type, $mensaje);
        }
        return redirect('empresa/mikrotik')->with('danger', 'No existe un registro con ese id');
    }
}
