<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;  
use Mail; 
use Validator;
use Illuminate\Validation\Rule;  
use Auth; 
use DB;
use Session;

use App\Mikrotik;
use App\User;
use App\Contrato;
use App\GrupoCorte;
use App\Campos;
use App\Model\Ingresos\Factura;
use App\Contacto;

class GruposCorteController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'zonas', 'subseccion' => 'grupo_corte', 'title' => 'Grupos de Corte', 'icon' => 'fas fa-project-diagram']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        return view('grupos-corte.index');
    }

    public function grupos(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $grupos = GrupoCorte::query()
            ->where('empresa', Auth::user()->empresa);
        if ($request->filtro == true) {
            if($request->nombre){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('nombre', 'like', "%{$request->nombre}%");
                });
            }
            if($request->fecha_factura){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_factura', 'like', "%{$request->fecha_factura}%");
                });
            }
            if($request->fecha_pago){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_pago', 'like', "%{$request->fecha_pago}%");
                });
            }
            if($request->fecha_corte){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_corte', 'like', "%{$request->fecha_corte}%");
                });
            }
            if($request->fecha_suspension){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_suspension', 'like', "%{$request->fecha_suspension}%");
                });
            }
            if($request->status >= 0){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('status', 'like', "%{$request->status}%");
                });
            }
        }

        return datatables()->eloquent($grupos)
            ->editColumn('id', function (GrupoCorte $grupo) {
                return $grupo->id;
            })
            ->editColumn('nombre', function (GrupoCorte $grupo) {
                return "<a href=" . route('grupos-corte.show', $grupo->id) . ">{$grupo->nombre}</div></a>";
            })
            ->editColumn('fecha_factura', function (GrupoCorte $grupo) {
                return ($grupo->fecha_factura == 0) ? 'No aplica' : $grupo->fecha_factura;
            })
            ->editColumn('fecha_pago', function (GrupoCorte $grupo) {
                return ($grupo->fecha_pago == 0) ? 'No aplica' : $grupo->fecha_pago;
            })
            ->editColumn('fecha_corte', function (GrupoCorte $grupo) {
                return ($grupo->fecha_corte == 0) ? 'No aplica' : $grupo->fecha_corte;
            })
            ->editColumn('fecha_suspension', function (GrupoCorte $grupo) {
                return ($grupo->fecha_suspension == 0) ? 'No aplica' : $grupo->fecha_suspension;
            })
            ->editColumn('hora_suspension', function (GrupoCorte $grupo) {
                return date('g:i A', strtotime($grupo->hora_suspension));
            })
            ->editColumn('status', function (GrupoCorte $grupo) {
                return "<span class='text-{$grupo->status("true")}'><strong>{$grupo->status()}</strong></span>";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "grupos-corte.acciones")
            ->rawColumns(['acciones', 'nombre', 'id', 'status'])
            ->toJson();
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Grupo de Corte']);
        return view('grupos-corte.create');
    }
    
    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|max:250',
            'fecha_corte' => 'required|numeric',
            'fecha_suspension' => 'required|numeric',
            'fecha_factura' => 'required|numeric',
            'fecha_pago' => 'required|numeric',
            'hora_suspension' => 'required',
        ]);

        $hora_suspension = explode(":", $request->hora_suspension);
        $hora_suspension_limit = $hora_suspension[0]+4;
        $hora_suspension_limit = $hora_suspension_limit.':'.$hora_suspension[1];
        
        $grupo = new GrupoCorte;
        $grupo->nombre = $request->nombre;
        $grupo->fecha_factura = $request->fecha_factura;
        $grupo->fecha_pago = $request->fecha_pago;
        $grupo->fecha_corte = $request->fecha_corte;
        $grupo->fecha_suspension = $request->fecha_suspension;
        $grupo->hora_suspension = $request->hora_suspension;
        $grupo->hora_suspension_limit = $hora_suspension_limit;
        $grupo->status = $request->status;
        $grupo->created_by = Auth::user()->id;
        $grupo->empresa = Auth::user()->empresa;
        $grupo->save();

        $mensaje='SE HA CREADO SATISFACTORIAMENTE EL GRUPO DE CORTE';
        return redirect('empresa/grupos-corte')->with('success', $mensaje);
    }

    public function storeBack(Request $request){
        $hora_suspension = explode(":", $request->hora_suspension);
        $hora_suspension_limit = $hora_suspension[0]+4;
        $hora_suspension_limit = $hora_suspension_limit.':'.$hora_suspension[1];

        $grupo                   = new GrupoCorte;
        $grupo->nombre           = $request->nombre;
        $grupo->fecha_factura    = $request->fecha_factura;
        $grupo->fecha_pago       = $request->fecha_pago;
        $grupo->fecha_corte      = $request->fecha_corte;
        $grupo->fecha_suspension = $request->fecha_suspension;
        $grupo->hora_suspension  = $request->hora_suspension;
        $grupo->hora_suspension_limit = $hora_suspension_limit;
        $grupo->status           = $request->status;
        $grupo->created_by       = Auth::user()->id;
        $grupo->empresa          = Auth::user()->empresa;
        $grupo->save();

        if ($grupo) {
            $arrayPost['success']    = true;
            $arrayPost['id']         = GrupoCorte::all()->last()->id;
            $arrayPost['suspension'] = GrupoCorte::all()->last()->fecha_suspension;
            $arrayPost['corte']      = GrupoCorte::all()->last()->fecha_corte;
            $arrayPost['nombre']     = GrupoCorte::all()->last()->nombre;
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $grupo = GrupoCorte::find($id);

        if ($grupo) {
            $contratos = Contrato::where('grupo_corte', $grupo->id)->where('empresa', Auth::user()->empresa)->count();
            $tabla = Campos::where('modulo', 2)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
            view()->share(['title' => $grupo->nombre]);
            return view('grupos-corte.show')->with(compact('grupo', 'contratos', 'tabla'));
        }
        return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $grupo = GrupoCorte::find($id);
        
        if ($grupo) {
            view()->share(['title' => 'Editar: '.$grupo->nombre]);
            return view('grupos-corte.edit')->with(compact('grupo'));
        }
        return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $request->validate([
            'nombre' => 'required|max:250',
            'fecha_corte' => 'required|numeric',
            'fecha_suspension' => 'required|numeric',
            'fecha_factura' => 'required|numeric',
            'fecha_pago' => 'required|numeric',
            'hora_suspension' => 'required',
        ]);
        
        $grupo = GrupoCorte::find($id);        
        
        if ($grupo) {
            $hora_suspension = explode(":", $request->hora_suspension);
            $hora_suspension_limit = $hora_suspension[0]+4;
            $hora_suspension_limit = $hora_suspension_limit.':'.$hora_suspension[1];

            //Si es diferente es por que hubo un cambio y vamos a actualizar la fecha de suspension de las ultimas facturas creadas
            if($grupo->fecha_suspension != $request->fecha_suspension){

                $mesActual = date('m');
                $yearActual = date('Y');

                $facturasGrupo = Factura::join('contracts as c', 'c.id', '=' ,'factura.contrato_id')
                ->join('grupos_corte as gc','gc.id','=','c.grupo_corte')
                ->select('factura.*','gc.id as grupo_id')
                ->whereRaw("DATE_FORMAT(factura.vencimiento, '%m')=" .$mesActual)
                ->whereRaw("DATE_FORMAT(factura.vencimiento, '%Y')=" .$yearActual)
                ->where('gc.id',$grupo->id)
                ->get();

                foreach($facturasGrupo as $fg){
                    $fg->vencimiento = $yearActual . "-" . $mesActual . "-" . $request->fecha_suspension;
                    $fg->save();
                }
            }

            $grupo->nombre           = $request->nombre;
            $grupo->fecha_factura    = $request->fecha_factura;
            $grupo->fecha_pago       = $request->fecha_pago;
            $grupo->fecha_corte      = $request->fecha_corte;
            $grupo->fecha_suspension = $request->fecha_suspension;
            $grupo->hora_suspension  = $request->hora_suspension;
            $grupo->hora_suspension_limit = $hora_suspension_limit;
            $grupo->status           = $request->status;
            $grupo->updated_by       = Auth::user()->id;
            $grupo->save();
            
            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE EL GRUPO DE CORTE';
            return redirect('empresa/grupos-corte')->with('success', $mensaje);
        }
        return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function destroy($id){
        $grupo = GrupoCorte::find($id);
        
        if($grupo){
            $grupo->delete();
            $mensaje = 'SE HA ELIMINADO EL GRUPO DE CORTE CORRECTAMENTE';
            return redirect('empresa/grupos-corte')->with('success', $mensaje);
        }else{
            return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
    
    public function act_des($id){
        $grupo = GrupoCorte::find($id);
        
        if($grupo){
            if($grupo->status == 0){
                $grupo->status = 1;
                $mensaje = 'SE HA HABILITADO EL GRUPO DE CORTE CORRECTAMENTE';
            }else{
                $grupo->status = 0;
                $mensaje = 'SE HA DESHABILITADO EL GRUPO DE CORTE CORRECTAMENTE';
            }
            $grupo->save();
            return redirect('empresa/grupos-corte')->with('success', $mensaje);
        }else{
            return redirect('empresa/grupos-corte')->with('danger', 'GRUPO DE CORTE NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }

    public function state_lote($grupos, $state){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $grupos = explode(",", $grupos);

        for ($i=0; $i < count($grupos) ; $i++) {
            $grupo = GrupoCorte::find($grupos[$i]);

            if($grupo){
                if($state == 'disabled'){
                    $grupo->status = 0;
                }elseif($state == 'enabled'){
                    $grupo->status = 1;
                }
                $grupo->save();
                $succ++;
            }else{
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

    public function destroy_lote($grupos){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $grupos = explode(",", $grupos);

        for ($i=0; $i < count($grupos) ; $i++) {
            $grupo = GrupoCorte::find($grupos[$i]);
            if ($grupo->uso()==0) {
                $grupo->delete();
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

    public function opcion_masiva(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Opciones Masivas a Contratos']);
        $grupos_corte = GrupoCorte::get();
        return view('grupos-corte.opcionmasiva',compact('grupos_corte'));
    }

    public function gruposOpcionesMasivas(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $grupos = GrupoCorte::query()
            ->where('empresa', Auth::user()->empresa);
        if ($request->filtro == true) {
            if($request->nombre){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('nombre', 'like', "%{$request->nombre}%");
                });
            }
            if($request->fecha_factura){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_factura', 'like', "%{$request->fecha_factura}%");
                });
            }
            if($request->fecha_pago){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_pago', 'like', "%{$request->fecha_pago}%");
                });
            }
            if($request->fecha_corte){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_corte', 'like', "%{$request->fecha_corte}%");
                });
            }
            if($request->fecha_suspension){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('fecha_suspension', 'like', "%{$request->fecha_suspension}%");
                });
            }
            if($request->status >= 0){
                $grupos->where(function ($query) use ($request) {
                    $query->orWhere('status', 'like', "%{$request->status}%");
                });
            }
        }

        return datatables()->eloquent($grupos)
            ->editColumn('id', function (GrupoCorte $grupo) {
                return $grupo->id;
            })
            ->editColumn('nombre', function (GrupoCorte $grupo) {
                return "<a href=" . route('grupos-corte.show', $grupo->id) . ">{$grupo->nombre}</div></a>";
            })
            ->editColumn('fecha_factura', function (GrupoCorte $grupo) {
                return ($grupo->fecha_factura == 0) ? 'No aplica' : $grupo->fecha_factura;
            })
            ->editColumn('fecha_pago', function (GrupoCorte $grupo) {
                return ($grupo->fecha_pago == 0) ? 'No aplica' : $grupo->fecha_pago;
            })
            ->editColumn('fecha_corte', function (GrupoCorte $grupo) {
                return ($grupo->fecha_corte == 0) ? 'No aplica' : $grupo->fecha_corte;
            })
            ->editColumn('fecha_suspension', function (GrupoCorte $grupo) {
                return ($grupo->fecha_suspension == 0) ? 'No aplica' : $grupo->fecha_suspension;
            })
            ->editColumn('hora_suspension', function (GrupoCorte $grupo) {
                return date('g:i A', strtotime($grupo->hora_suspension));
            })
            ->editColumn('status', function (GrupoCorte $grupo) {
                return "<span class='text-{$grupo->status("true")}'><strong>{$grupo->status()}</strong></span>";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "grupos-corte.acciones")
            ->rawColumns(['acciones', 'nombre', 'id', 'status'])
            ->toJson();
    }

    public function estadosGruposCorte($grupo = null, $fecha = null){

        $this->getAllPermissions(Auth::user()->id);
        
        view()->share(['inicio' => 'master', 'seccion' => 'zonas', 'subseccion' => 'estados_corte', 'title' => 'Estados de corte', 'icon' => 'fas fa-project-diagram']);

        if($grupo == 'all'){
            $grupo = null;
        }

        if(!$fecha){
            $fecha = date('Y-m-d');
        }

        if($grupo != null){
            $grupoSeleccionado = GrupoCorte::find($grupo);
            $fecha =  date('Y-m').'-'.$grupoSeleccionado->fecha_suspension;
        }

        $swGrupo = 1; //masivo
        // $grupos_corte = GrupoCorte::where('fecha_suspension', date('d') * 1)->where('hora_suspension','<=', date('H:i'))->where('hora_suspension_limit','>=', date('H:i'))->where('status', 1)->count();
        $grupos_corte = GrupoCorte::where('hora_suspension','<=', date('H:i'))->where('hora_suspension_limit','>=', date('H:i'))->where('status', 1)->where('fecha_suspension','!=',0)->get();
        $perdonados = 0;


        if($grupos_corte->count() > 0){
            $grupos_corte_array = array();    
            foreach($grupos_corte as $grupo){
                array_push($grupos_corte_array,$grupo->id);
            }
            
            $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
                join('contracts as cs','cs.client_id','=','contactos.id')->
                join('grupos_corte as gp', 'gp.id', '=', 'cs.grupo_corte')->
                select('gp.nombre as grupo', 'gp.id as idGrupo', 'contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.codigo', 'f.estatus', 'f.suspension', 'cs.state', 'f.contrato_id')->
                where('f.estatus',1)->
                whereIn('f.tipo', [1,2])->
                where('f.vencimiento', $fecha)->
                where('contactos.status',1)->
                where('cs.state','enabled')->
                whereIn('cs.grupo_corte',$grupos_corte_array)->
                where('cs.fecha_suspension', null);
               
                if($grupo){
                    $contactos->where('gp.id', $grupo);
                }

                $contactos = $contactos->get()->all(); 
                $swGrupo = 1; //masivo
        }else{
            $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            join('grupos_corte as gp', 'gp.id', '=', 'cs.grupo_corte')->
            select('gp.nombre as grupo', 'gp.id as idGrupo', 'contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'f.codigo', 'cs.state', 'f.contrato_id')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('f.vencimiento', $fecha)->
            where('contactos.status',1)->
            where('cs.state','enabled')->
            where('cs.fecha_suspension','!=', null);
            
            if($grupo){
                $contactos->where('gp.id', $grupo);
            }
            

            $contactos = $contactos->get()->all(); 
           // dd($contactos);
            $swGrupo = 0; // personalizado
        }

        if($contactos){
            foreach ($contactos as $key => $contacto) {
                $contrato = Contrato::find($contacto->contrato_id);
                $promesaExtendida = DB::table('promesa_pago')->where('factura', $contacto->factura)->where('vencimiento', '>=', $fecha)->count();
                if($promesaExtendida > 0){
                    unset($contactos[$key]);
                    $perdonados++;
                }
            }
        }
    
        $contactos = collect($contactos);
        $totalFacturas = $contactos->count();
        $contactos = $contactos->groupBy('idGrupo');
        $gruposFaltantes = GrupoCorte::whereIn('id', $contactos->keys())->get();

        $grupos_corte = GrupoCorte::get();

        $facturasCortadas = Factura::select('factura.*', 'contactos.nombre as nombreCliente', 'gp.nombre as nombreGrupo', 'gp.hora_suspension', 'gp.id as idGrupo')->
                                     join('contactos', 'contactos.id', '=', 'factura.cliente')->
                                     join('contracts as cs','cs.client_id','=','contactos.id')->
                                     join('grupos_corte as gp', 'gp.id', '=', 'cs.grupo_corte')->
                                     where('vencimiento', $fecha)->
                                     where('estatus', 1)->
                                     whereIn('tipo', [1,2])->
                                     where('cs.state','disabled');

        if($grupo){
            $facturasCortadas = $facturasCortadas->where('gp.id', $grupo);
        }


        $facturasCortadas = $facturasCortadas->groupBy('factura.id')->
                                     orderby('id', 'desc')->
                                     get();


        
        $facturasGeneradas = Factura::select('factura.*', 'contactos.nombre as nombreCliente', 'gp.nombre as nombreGrupo', 'gp.hora_suspension', 'gp.id as idGrupo')->
                                     join('contactos', 'contactos.id', '=', 'factura.cliente')->
                                     join('contracts as cs','cs.client_id','=','contactos.id')->
                                     join('grupos_corte as gp', 'gp.id', '=', 'cs.grupo_corte')->
                                     where('vencimiento', $fecha)->
                                     whereIn('tipo', [1,2])->
                                     where('factura.facturacion_automatica', 1);

        if($grupo){
            $facturasGeneradas = $facturasGeneradas->where('gp.id', $grupo);
        }


        $facturasGeneradas =  $facturasGeneradas->groupBy('factura.id')->
                                     orderby('id', 'desc')->
                                     get();

        

        $request = request();

        $cantidadContratos = Contrato::select('contracts.id')
                                        ->join('grupos_corte', 'grupos_corte.id', '=', 'contracts.grupo_corte')
                                        ->where('grupos_corte.fecha_suspension', Carbon::create($fecha)->format('d'))
                                        ->where('grupos_corte.status', 1)
                                        ->count();
                                     
        return view('grupos-corte.estados', compact('contactos', 'gruposFaltantes', 'perdonados', 'grupo', 'fecha', 'totalFacturas', 'grupos_corte', 'facturasCortadas', 'request', 'facturasGeneradas', 'cantidadContratos'));
    }


}
