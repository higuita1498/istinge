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
USE App\ProductoCuenta;
use App\Mikrotik;
use App\PlanesVelocidad;
use App\Puc;
use App\Retencion;

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

        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 10)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        $mikrotiks = Mikrotik::where('empresa', Auth::user()->empresa)->get();
        $planes_velocidad = PlanesVelocidad::all();
        return view('planesvelocidad.index')->with(compact('mikrotiks','tabla','planes_velocidad'));
    }

    public function planes(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $moneda = auth()->user()->empresa()->moneda;
        $planes = PlanesVelocidad::query();

        if ($request->filtro == true) {
            if($request->nombre){
                $planes->where(function ($query) use ($request) {
                    $query->orWhere('name', 'like', "%{$request->nombre}%");
                });
            }
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
                $planes->where(function ($query) use ($request) {
                    $query->orWhere('name', 'like', "%{$request->name}%");
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
                $html = '';
                if ($plan->mikrotik() !== null) {
                    $html .= "<a href=" . route('mikrotik.show', $plan->mikrotik()->id) . " target='_blank'>{$plan->mikrotik()->nombre}</div></a>";
                }

                // Similarmente, verifica si mikrotik1 no es nulo antes de intentar acceder a sus propiedades
                // if ($plan->mikrotik1() !== null) {
                //     $html .= "; <a href=" . route('mikrotik.show', $plan->mikrotik1()->id) . " target='_blank'>{$plan->mikrotik1()->nombre}</div></a>";
                // }

                // if ($plan->mikrotik2() !== null) {
                //     $html .= "; <a href=" . route('mikrotik.show', $plan->mikrotik2()->id) . " target='_blank'>{$plan->mikrotik2()->nombre}</div></a>";
                // }

                // if ($plan->mikrotik3() !== null) {
                //     $html .= "; <a href=" . route('mikrotik.show', $plan->mikrotik3()->id) . " target='_blank'>{$plan->mikrotik3()->nombre}</div></a>";
                // }

                // if ($plan->mikrotik4() !== null) {
                //     $html .= "; <a href=" . route('mikrotik.show', $plan->mikrotik4()->id) . " target='_blank'>{$plan->mikrotik4()->nombre}</div></a>";
                // }
              //  return "<a href=" . route('mikrotik.show', $plan->mikrotik()->id) . " target='_blank'>{$plan->mikrotik()->nombre}</div></a>, <a href=" . route('mikrotik.show', $plan->mikrotik1()->id) . " target='_blank'>{$plan->mikrotik1()->nombre}</div></a>";
              return $html;
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
        $empresa = Auth::user()->empresa;

        //Tomar las categorias del puc que no son transaccionables.
        $cuentas = Puc::where('empresa',$empresa)
        ->where('estatus',1)
        ->whereRaw('length(codigo) > 6')
        ->get();
        $autoRetenciones = Retencion::where('empresa',Auth::user()->empresa)->where('estado',1)->where('modulo',2)->get();
        $type = '';

        return view('planesvelocidad.create')->with(compact('mikrotiks','cuentas','autoRetenciones','type'));
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

        if($request->mikrotik == null){
            return back()->with('error', 'Debe asociar la mikrotik');
        }
        $num_microtik = count($request->mikrotik);
        for ($i=0; $i < count($request->mikrotik) ; $i++) {
            $inventario                = new Inventario;
            $inventario->empresa       = Auth::user()->empresa;
            $inventario->producto      = strtoupper($request->name);
            $inventario->ref           = strtoupper($request->ref);
            $inventario->precio        = $this->precision($request->price);

            $inventario->id_impuesto   = ($request->tipo_plan == 2) ? 1 : 2;
            $inventario->impuesto      = ($request->tipo_plan == 2) ? 19 : 0;

            $inventario->tipo_producto = 2;
            $inventario->unidad        = 1;
            $inventario->nro           = 0;
            $inventario->categoria     = 116;
            $inventario->lista         = 0;
            $inventario->type_autoretencion = isset($request->tipo_autoretencion) ? $request->tipo_autoretencion : null;
            $inventario->type          = 'PLAN';
            $inventario->save();

            $plan = new PlanesVelocidad;
            $plan->mikrotik = $request->mikrotik[$i];

            // if ((!empty($request->mikrotik[1])) && (isset($request->mikrotik[1]))) {

            //     $plan->mikrotik1 = $request->mikrotik[1];
            // }
            // if (!empty($request->mikrotik[2]) && (isset($request->mikrotik[2]))) {
            //     $plan->mikrotik2 = $request->mikrotik[2];
            // }
            // if (!empty($request->mikrotik[3]) && (isset($request->mikrotik[3]))) {
            //     $plan->mikrotik3 = $request->mikrotik[3];
            // }
            // if (!empty($request->mikrotik[4]) && (isset($request->mikrotik[4]))) {
            //     $plan->mikrotik4 = $request->mikrotik[4];
            // }

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
            if(isset($request->inventario) && $request->inventario != 0){
                $pr = new ProductoCuenta;
                $pr->cuenta_id = $request->inventario;
                $pr->inventario_id = $inventario->id;
                $pr->tipo = 1;
                $pr->save();
            }

            if(isset($request->costo) && $request->costo != 0){
                $pr = new ProductoCuenta;
                $pr->cuenta_id = $request->costo;
                $pr->inventario_id = $inventario->id;
                $pr->tipo = 2;
                $pr->save();
            }

            if(isset($request->venta) && $request->venta != 0){
                $pr = new ProductoCuenta;
                $pr->cuenta_id = $request->venta;
                $pr->inventario_id = $inventario->id;
                $pr->tipo = 3;
                $pr->save();
            }

            if(isset($request->devolucion) && $request->devolucion != 0){
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
        $empresa = Auth::user()->empresa;
        $plan = PlanesVelocidad::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        $plan->ref = Inventario::Find($plan->item)->ref;
        $cuentas = Puc::where('empresa',$empresa)
        ->where('estatus',1)
        ->whereRaw('length(codigo) > 6')
        ->get();
        $autoRetenciones = Retencion::where('empresa',Auth::user()->empresa)->where('estado',1)->where('modulo',2)->get();

        if ($plan) {
            $inventario = Inventario::find($plan->item);
            $cuentasInventario = $inventario->cuentas();
            view()->share(['title' => 'Modificar Plan', 'icon' => 'fas fa-server']);
            $mikrotiks = Mikrotik::where('empresa', Auth::user()->empresa)->get();
            return view('planesvelocidad.edit')->with(compact('plan', 'mikrotiks', 'cuentas', 'autoRetenciones','cuentasInventario','inventario'));
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
            // if ((!empty($request->mikrotik[1])) && (isset($request->mikrotik[1]))) {

            //     $plan->mikrotik1 = $request->mikrotik[1];
            // }
            // if (!empty($request->mikrotik[2]) && (isset($request->mikrotik[2]))) {
            //     $plan->mikrotik2 = $request->mikrotik[2];
            // }
            // if (!empty($request->mikrotik[3]) && (isset($request->mikrotik[3]))) {
            //     $plan->mikrotik3 = $request->mikrotik[3];
            // }
            // if (!empty($request->mikrotik[4]) && (isset($request->mikrotik[4]))) {
            //     $plan->mikrotik4 = $request->mikrotik[4];
            // }
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
            $inventario->ref         = strtoupper($request->ref);
            $inventario->precio      = $this->precision($request->price);
            $inventario->id_impuesto = ($request->tipo_plan == 2) ? 1 : 2;
            $inventario->impuesto    = ($request->tipo_plan == 2) ? 19 : 0;
            $inventario->type_autoretencion = isset($request->tipo_autoretencion) ? $request->tipo_autoretencion : null;
            $inventario->save();

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
             if(isset($request->inventario) && $request->inventario != 0){
                $inven= ProductoCuenta::where('inventario_id',$inventario->id)->where('cuenta_id',$request->inventario)->first();
                if($inven){
                    $inven->tipo = 1;
                    $inven->save();
                }
            }

            if(isset($request->costo) && $request->costo != 0){
                $inven= ProductoCuenta::where('inventario_id',$inventario->id)->where('cuenta_id',$request->costo)->first();
                if($inven){
                    $inven->tipo = 2;
                    $inven->save();
                }
            }

            if(isset($request->venta) && $request->venta != 0){
                $inven= ProductoCuenta::where('inventario_id',$inventario->id)->where('cuenta_id',$request->venta)->first();
                if($inven){
                    $inven->tipo = 3;
                    $inven->save();
                }
            }

            if(isset($request->devolucion) && $request->devolucion != 0){
                $inven= ProductoCuenta::where('inventario_id',$inventario->id)->where('cuenta_id',$request->devolucion)->first();
                if($inven){
                    $inven->tipo = 4;
                    $inven->save();
                }
            }

            if(isset($request->autoretencion) && $request->autoretencion != 0){
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

            $mensaje = 'SE HA MODIFICADO SATISFACTORIAMENTE EL PLAN';
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

                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    $queue = $API->comm("/queue/simple/getall", array(
                        "?target" => $contrato->ip.'/32'
                        )
                    );

                    if(count($queue)>0){
                        $API->comm("/queue/simple/set", array(
                            ".id"             => $queue[0][".id"],
                            "max-limit"       => $plan->upload.'/'.$plan->download,
                            "burst-limit"     => $burst_limit,
                            "burst-threshold" => $burst_threshold,
                            "burst-time"      => $burst_time,
                            "priority"        => $priority,
                            "limit-at"        => $limit_at
                            )
                        );
                        $succ++;
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

    public function state_lote($planes, $state){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $planes = explode(",", $planes);

        for ($i=0; $i < count($planes) ; $i++) {
            $plan = PlanesVelocidad::find($planes[$i]);
            if ($plan) {
                $plan->status = ($state == 'disabled') ? 0:1;
                $plan->save();
                $succ++;
            } else {
                $fail++;
            }
        }

        return response()->json([
            'success'   => true,
            'fallidos'  => $fail,
            'correctos' => $succ,
            'state'     => $state
        ]);
    }

    public function destroy_lote($planes){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $planes = explode(",", $planes);

        for ($i=0; $i < count($planes) ; $i++) {
            $plan = PlanesVelocidad::find($planes[$i]);
            if ($plan->uso()==0) {
                $plan->delete();
                $succ++;
            } else {
                $fail++;
            }
        }

        return response()->json([
            'success'   => true,
            'fallidos'  => $fail,
            'correctos' => $succ,
            'state'     => 'eliminados'
        ]);
    }
}
