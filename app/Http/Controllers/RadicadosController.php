<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Radicado;
use App\Servicio;
use App\User;
use App\Contacto;
use App\TipoIdentificacion;
use App\Vendedor;
use App\Model\Inventario\ListaPrecios;
use App\TipoEmpresa;
use App\PlanesVelocidad;
use App\Empresa;
use App\Funcion;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use App\Campos;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\MovimientoLOG;

use Mail;
use Config;
use App\ServidorCorreo;
use App\Oficina;

class RadicadosController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['subseccion' => 'radicados', 'title' => 'Radicados', 'icon' =>'far fa-life-ring', 'seccion' => 'atencion_cliente']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);

        $clientes = (Auth::user()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $tecnicos = User::where('rol', 4)->where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        $responsables = User::where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servicios = Servicio::where('estatus', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre','asc')->get();
        $tipo = '';
        $tabla = Campos::where('modulo', 12)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
        view()->share(['invert' => true]);
        return view('radicados.indexnew', compact('clientes','tipo','servicios','tabla','tecnicos', 'responsables'));
    }

    public function indexNew(Request $request, $tipo){
        $this->getAllPermissions(Auth::user()->id);

        $clientes = (Auth::user()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $tecnicos = User::where('rol', 4)->where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servicios = Servicio::where('estatus', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre','asc')->get();
        $tabla = Campos::where('modulo', 12)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();
        $responsables = User::where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        if($tipo == 'solventados'){
            $tipo = 1;
        }elseif($tipo == 'pendientes'){
            $tipo = 0;
        }else{
            $tipo = 'all';
        }

        view()->share(['invert' => true]);
        return view('radicados.indexnew', compact('clientes','tipo','servicios','tabla','tecnicos', 'responsables'));
    }

    public function radicados(Request $request, $estado){
        $modoLectura = auth()->user()->modo_lectura();
        $radicados = Radicado::query()
            ->join('servicios as s', 's.id','=','radicados.servicio')
            ->select('radicados.*', 's.nombre as nombre_servicio')
            ->where('radicados.empresa', Auth::user()->empresa);

        if ($request->filtro == true) {
            if($request->codigo){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.codigo', 'like', "%{$request->codigo}%");
                });
            }
            if($request->fecha){
                $fecha = date('Y-m-d', strtotime($request->fecha));
                $radicados->where(function ($query) use ($request, $fecha) {
                    $query->orWhere('radicados.fecha', $fecha);
                });
            }
            if($request->contrato){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.contrato', 'like', "%{$request->contrato}%");
                });
            }
            if($request->cliente){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.nombre', 'like', "%{$request->cliente}%");
                });
            }
            if($request->telefono){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.telefono', 'like', "%{$request->telefono}%");
                });
            }
            if($request->servicio){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.servicio', $request->servicio);
                });
            }
            if($request->direccion){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.direccion', 'like', "%{$request->direccion}%");
                });
            }
            if($request->estatus){
                $estatus = ($request->estatus == 'A') ? 0 : $request->estatus;
                $radicados->where(function ($query) use ($request, $estatus) {
                    $query->orWhere('radicados.estatus', $request->estatus);
                });
            }
            if($request->creado){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.creado', $request->creado);
                });
            }
            if($request->prioridad){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.prioridad', $request->prioridad);
                });
            }
            if($request->tecnico){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.tecnico', $request->tecnico);
                });
            }
            if($request->responsable){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.responsable', $request->responsable);
                });
            }
        }

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $radicados->where('radicados.oficina', auth()->user()->oficina);
            }
        }

        if(auth()->user()->rol == 4){
            $radicados = $radicados->where('radicados.tecnico',Auth::user()->id)->orderby('radicados.id','ASC');
        }

        if($estado == 0){
            $radicados->where(function ($query) use ($estado) {
                $query->whereIn('radicados.estatus', [0,2]);
            });
        }elseif($estado == 1){
            $radicados->where(function ($query) use ($estado) {
                $query->whereIn('radicados.estatus', [1,3]);
            });
        }

        return datatables()->eloquent($radicados)
        ->editColumn('codigo', function (Radicado $radicado) {
            return "<a href=".route('radicados.show', $radicado->id).">$radicado->codigo</a>";
        })
        ->editColumn('fecha', function (Radicado $radicado) {
            return date('d-m-Y', strtotime($radicado->fecha));
        })
        ->editColumn('contrato', function (Radicado $radicado) {
            return  $radicado->contrato;
        })
        ->editColumn('cliente', function (Radicado $radicado) {
            return  $radicado->nombre;
        })
        ->editColumn('telefono', function (Radicado $radicado) {
            return  $radicado->telefono;
        })
        ->editColumn('servicio', function (Radicado $radicado) {
            return  $radicado->nombre_servicio;
        })
        ->editColumn('direccion', function (Radicado $radicado) {
            return  $radicado->direccion;
        })
        ->addColumn('estatus', function (Radicado $radicado) {
            return   '<span class="font-weight-bold text-' . $radicado->estatus(true) . '">' . $radicado->estatus() . '</span>';
        })
        ->addColumn('nro_radicados', function (Radicado $radicado) {
            return $radicado->nro_radicados();
        })
        ->editColumn('creado', function (Radicado $radicado) {
            return  $radicado->creado();
        })
        ->editColumn('responsable', function (Radicado $radicado) {
            return $radicado->responsable ? $radicado->responsable()->nombres : 'N/A';
        })

        ->editColumn('prioridad', function (Radicado $radicado) {
            return  $radicado->prioridad();
        })
        ->editColumn('tecnico', function (Radicado $radicado) {
            return ($radicado->tecnico) ? $radicado->tecnico()->nombres : 'N/A' ;
        })
        ->addColumn('acciones', $modoLectura ?  "" : "radicados.acciones")
        ->rawColumns(['codigo', 'estatus', 'acciones', 'creado', 'prioridad', 'tecnico'])
        ->toJson();
    }

    public function create($cliente = false){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = Contacto::where('status',1)->where('empresa',Auth::user()->empresa)->orderBy('nombre','asc')->get();
        $identificaciones = TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        $paises  =DB::table('pais')->get();
        $departamentos = DB::table('departamentos')->get();
        $planes = PlanesVelocidad::where('empresa', Auth::user()->empresa)->get();
        $servicios = Servicio::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
        $tecnicos = User::where('empresa',Auth::user()->empresa)->where('rol', 4)->get();
        $oficinas = (Auth::user()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
        view()->share(['icon'=>'far fa-life-ring', 'title' => 'Nuevo Caso']);
        return view('radicados.create')->with(compact('clientes','identificaciones','paises','departamentos', 'tipos_empresa', 'prefijos', 'vendedores', 'listas','planes','servicios','tecnicos', 'cliente', 'oficinas'));
    }

    public function store(Request $request){
        $request->validate([
            'cliente' => 'required',
            'fecha' => 'required',
            'desconocido' => 'required',
            'servicio' => 'required',
            'estatus' => 'required',
            'telefono' => 'required',
            'direccion' => 'required',
            'prioridad' => 'required'
        ]);

        if(!$request->contrato && $request->servicio != 4){
            $mensaje='El cliente no posee contrato asignado y no puede hacer uso de un servicio distinto a instalaciones';
            return back()->withInput()->with('danger', $mensaje);
        }

        $radicado = new Radicado;
        $radicado->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
        $radicado->identificacion = $request->ident;
        $radicado->cliente = $request->id_cliente;
        $radicado->nombre = $request->nombre;
        $radicado->telefono = $request->telefono;
        $radicado->correo = $request->correo;
        $radicado->direccion = $request->direccion;
        $radicado->contrato = $request->contrato;
        $radicado->desconocido = $request->desconocido;
        $radicado->servicio = $request->servicio;
        $radicado->tecnico = $request->tecnico;
        $radicado->estatus = $request->estatus;
        $radicado->codigo = rand(0, 99999);
        $radicado->prioridad = $request->prioridad;
        $radicado->mac_address = $request->mac_address;
        $radicado->ip = $request->ip;
        $radicado->empresa = Auth::user()->empresa;
        $radicado->responsable = Auth::user()->id;
        $radicado->valor = ($request->servicio == 4) ? $request->valor : null;
        $radicado->oficina = $request->oficina;
        $radicado->save();

        if($request->contrato){
            $movimiento = new MovimientoLOG;
            $movimiento->contrato    = $request->contrato;
            $movimiento->modulo      = 5;
            $movimiento->descripcion = '<i class="fas fa-check text-success"></i> <b>Generación de Radicado</b> Servicio '.$radicado->servicio()->nombre.' N° '.$radicado->codigo;
            $movimiento->created_by  = Auth::user()->id;
            $movimiento->empresa     = Auth::user()->empresa;
            $movimiento->save();
        }

        $mensaje='Se ha creado satisfactoriamente el radicado bajo el código #'.$radicado->codigo;
        return redirect('empresa/radicados')->with('success', $mensaje);
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $servicios = Servicio::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
        $tecnicos = User::where('empresa',Auth::user()->empresa)->where('rol', 4)->get();
        $oficinas = (Auth::user()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
        if ($radicado) {
            view()->share(['icon'=>'far fa-life-ring', 'title' => 'Modificar: N° '.$radicado->codigo, 'middel' => true]);
            return view('radicados.edit')->with(compact('radicado','servicios','tecnicos','oficinas'));
        }
        return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function update(Request $request, $id){
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($radicado) {
            if($request->adjunto){
                $radicado->adjunto = $request->adjunto;
                $file = $request->file('adjunto');
                $nombre = $radicado->codigo.'-'.date('Ymd').'.'.$file->extension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $radicado->adjunto = $nombre;
                $radicado->save();
                $mensaje='SE HA CARGADO EL ARCHIVO ADJUNTO SATISFACTORIAMENTE.';
                return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
            }

            if ($request->reporte) {
                $radicado->reporte = $request->reporte;
                $radicado->save();
                $mensaje='SE HA REGISTRADO EL REPORTE DEL TÉCNICO SATISFACTORIAMENTE.';
                return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
            }
            $request->validate([
                'telefono' => 'required|numeric',
                'direccion' => 'required|max:200',
                'fecha' => 'required',
                'servicio' => 'required',
                'estatus' => 'required',
                'desconocido' => 'required'
            ]);
            if(!$request->contrato && $request->servicio != 4){
                $mensaje='El cliente no posee contrato asignado y no puede hacer uso de un servicio distinto a instalaciones';
                return back()->withInput()->with('danger', $mensaje);
            }

            $radicado->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $radicado->telefono = $request->telefono;
            $radicado->correo = $request->correo;
            $radicado->direccion = $request->direccion;
            $radicado->desconocido = $request->desconocido;
            $radicado->servicio = $request->servicio;
            $radicado->tecnico = $request->tecnico;
            $radicado->estatus = $request->estatus;
            $radicado->prioridad = $request->prioridad;
            $radicado->responsable = Auth::user()->id;
            $radicado->valor = ($request->servicio == 4) ? $request->valor : null;
            $radicado->oficina = $request->oficina;
            $radicado->save();

            $mensaje='Se ha modificado satisfactoriamente el radicado #'.$radicado->codigo;
            return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
        }
        return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($radicado) {
            view()->share(['icon'=>'far fa-life-ring', 'title' => 'Detalles: N° '.$radicado->codigo, 'precice' => true]);
            $inicio = Carbon::parse($radicado->tiempo_ini);
            $cierre = Carbon::parse($radicado->tiempo_fin);
            $duracion = $inicio->diffInMinutes($cierre);
            return view('radicados.show')->with(compact('radicado','duracion'));
        }
        return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function destroy($id){
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($radicado) {
            if($radicado->adjunto){
                Storage::disk('documentos')->delete($radicado->adjunto);
            }
            $radicado->delete();
        }
        return redirect('empresa/radicados')->with('success', 'El radicado ha sido eliminado satisfactoriamente');
    }

    public function escalar($id){
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($radicado) {
            if ($radicado->estatus==0) {
                $radicado->estatus=2;
                $mensaje='Se ha escalado el caso a soporte técnico';
                $radicado->save();
                return back()->with('success', $mensaje);
            }
        }
        return back('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function solventar($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($radicado) {
            if ($radicado->estatus==0) {
                $radicado->estatus=1;
            }else if ($radicado->estatus==2) {
                $radicado->estatus=3;
            }
            $mensaje = 'SE HA RESUELTO EL CASO RADICADO';
            $radicado->save();

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
                        ]
                    ]
                );
                config(['mail'=>$new]);
            }

            Mail::send('emails.radicado', compact('radicado'), function($message) use ($radicado){
                $message->to($radicado->correo)->subject(Auth::user()->empresa()->nombre.': Reporte de Radicado');
            });

            return back()->with('success', $mensaje);
        }
        return back('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function imprimir($id){
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id',$id)->first();
        if($radicado) {
            view()->share(['title' => 'Caso Radicado N° '.$radicado->codigo]);
            $pdf = PDF::loadView('pdf.radicados', compact('radicado'));
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
        }
    }

    public function firmar($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        view()->share(['icon'=>'far fa-life-ring', 'title' => 'Firma Radicado: N° '.$radicado->codigo, 'invertfalse' => true]);
        return view('radicados.firma')->with(compact('radicado'));
    }

    public function storefirma(Request $request, $id){
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($radicado) {
            $radicado->firma = $request->dataImg;
            $radicado->save();
            $mensaje='Se ha registrado la firma del cliente.';
            return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
        }
        return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function notificacionRadicado(){
        $radicado=Radicado::whereIn('estatus', [0,2])->where('creado', 2)->get();
        return json_encode($radicado);
    }
  
    public function datatable_cliente($contacto, Request $request){
        $requestData =  $request;
        
        $columns = array(
            0 => 'radicados.codigo',
            1 => 'radicados.fecha',
            2 => 'radicados.tipo',
            3 => 'radicados.status'
        );
        
        $requestData =  $request;
        
        $movimientos=Radicado::leftjoin('contactos as c', 'radicados.identificacion', '=', 'c.nit')    
        ->select('radicados.*')
        ->where('radicados.empresa',Auth::user()->empresa);
        
        if ($contacto) { $movimientos=$movimientos->where('radicados.identificacion', $contacto); }
        if (isset($requestData->search['value'])) {
            $movimientos=$movimientos->where(function ($query) use ($requestData) {
                $query->where('radicados.identificacion', 'like', '%'.$requestData->search['value'].'%')
                ->orwhere('radicados.nombre', 'like', '%'.$requestData->search['value'].'%')
                ->orwhere('radicados.fecha', 'like', '%'.$requestData->search['value'].'%');
            });
        }

        $totalFiltered=$totalData=$movimientos->count();
        
        $movimientos=$movimientos->skip($requestData['start'])->take($requestData['length']);
        $movimientos=$movimientos->orderBy('fecha', 'desc');
        $movimientos=$movimientos->get();
        $data = array();
        foreach ($movimientos as $movimiento) {
            $nestedData = array();
            $nestedData[] = '<a href="'.$movimiento->show_url().'">'.$movimiento->codigo.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($movimiento->fecha));
            $nestedData[] = $movimiento->servicio()->nombre;
            $nestedData[] = '<strong><span class="text-'.$movimiento->estatus('true').'">'.$movimiento->estatus().'</span></strong>';
            $data[] = $nestedData;
        }
        
        $json_data = array(
            "draw" => intval($requestData->draw),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        );
        return json_encode($json_data);
    }
    
    public function proceder($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($radicado) {
            if ($radicado->tiempo_ini == null) {
                $radicado->tiempo_ini = Carbon::now()->toDateTimeString();
                $radicado->tiempo_est = $radicado->servicio()->tiempo;
                $mensaje = 'Radicado Iniciado, recuerde que tiene un tiempo de '.$radicado->tiempo_est.'min para solventarlo';
            }else{
                $radicado->tiempo_fin = Carbon::now()->toDateTimeString();
                $inicio = Carbon::parse($radicado->tiempo_ini);
                $cierre = Carbon::parse($radicado->tiempo_fin);
                $duracion = $inicio->diffInMinutes($cierre);
                
                $mensaje = 'Radicado Finalizado, con una duración de '.$duracion.'min';
            }
            
            $radicado->save();
            return back()->with('success', $mensaje);
        }
        return back('empresa/radicados')->with('danger', 'No existe un registro con ese id');
    }

    public function eliminarAdjunto($id){
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if($radicado){
            Storage::disk('documentos')->delete($radicado->adjunto);
            $radicado->adjunto = NULL;
            $radicado->save();

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

    public function reabrir($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($radicado) {
            if ($radicado->estatus == 1) {
                $radicado->estatus = 0;
            }else if ($radicado->estatus == 3) {
                $radicado->estatus = 2;
            }

            $mensaje = 'EL RADICADO HA SIDO REABIERTO SATISFACTORIAMENTE';
            $radicado->save();
            return back()->with('success', $mensaje);
        }
        return back('empresa/radicados')->with('success', 'NO EXISTE UN REGISTRO CON ESE ID');
    }
}
