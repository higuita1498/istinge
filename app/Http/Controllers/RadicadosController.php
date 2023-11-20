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
use App\RadicadoLOG;

use Mail;
use Config;
use App\ServidorCorreo;
use App\Oficina;

include_once(app_path() .'/../public/PHPExcel/Classes/PHPExcel.php');
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Shared_ZipArchive;

class RadicadosController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['subseccion' => 'radicados', 'title' => 'Radicados', 'icon' =>'far fa-life-ring', 'seccion' => 'atencion_cliente']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);

        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $tecnicos = User::where('rol', 4)->where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        $responsables = User::where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servicios = Servicio::where('estatus', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre','asc')->get();
        $tipo = '';
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 12)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        view()->share(['invert' => true]);
        return view('radicados.indexnew', compact('clientes','tipo','servicios','tabla','tecnicos', 'responsables'));
    }

    public function indexNew(Request $request, $tipo){
        $this->getAllPermissions(Auth::user()->id);

        $clientes = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $tecnicos = User::where('rol', 4)->where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        $servicios = Servicio::where('estatus', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre','asc')->get();
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 12)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
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
                    $query->orWhere('radicados.identificacion', $request->cliente);
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
            if($request->tiempo_fin){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhereDate('radicados.tiempo_fin', 'like', "%{$request->tiempo_fin}%");
                });
            }
            if($request->direccion){

                $direccion = $request->c_direccion;
                $direccion = explode(' ', $direccion);
                $direccion = array_reverse($direccion);

                foreach($direccion as $dir){
                    $dir = strtolower($dir);
                    $dir = str_replace("#","",$dir);
                    //$dir = str_replace("-","",$dir);
                    //$dir = str_replace("/","",$dir);

                    $radicados->where(function ($query) use ($dir) {
                        $query->orWhere('direccion', 'like', "%{$dir}%");
                        $query->orWhere('direccion', 'like', "%{$dir}%");
                    });
                }

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

        $radicados = $radicados->orderby('radicados.creado', 'desc');

        return datatables()->eloquent($radicados)
        ->editColumn('codigo', function (Radicado $radicado) {
            return "<a href=".route('radicados.show', $radicado->id).">$radicado->codigo</a>";
        })
        ->editColumn('fecha', function (Radicado $radicado) {
            return date('d-m-Y g:i:s A', strtotime($radicado->created_at));
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
            return '<span class="font-weight-bold text-'.$radicado->prioridad(true).'">'.$radicado->prioridad().'</span>';
        })
        ->editColumn('tecnico', function (Radicado $radicado) {
            return ($radicado->tecnico) ? $radicado->tecnico()->nombres : 'N/A' ;
        })
        ->editColumn('ip', function (Radicado $radicado) {
            if($radicado->ip){
                /*if($radicado->contrato()){
                    if(isset($radicado->contrato()->puerto_conexion)){
                        return "<a href='http://".$radicado->ip.":".$radicado->cliente()->contrato()->puerto->nombre."' target='_blank'>{$radicado->ip}:{$radicado->cliente()->contrato()->puerto->nombre} <i class='fas fa-external-link-square-alt'></i></a>";
                    }
                }*/
                return "<a href='http://".$radicado->ip."' target='_blank'>{$radicado->ip} <i class='fas fa-external-link-square-alt'></i></a>";
            }else{
                return 'N/A' ;
            }
        })
        ->editColumn('mac_address', function (Radicado $radicado) {
            return ($radicado->mac_address) ? $radicado->mac_address : 'N/A' ;
        })
        ->editColumn('tiempo_est', function (Radicado $radicado) {
            return ($radicado->tiempo_est) ? $radicado->tiempo_est.' minutos' : 'N/A' ;
        })
        ->editColumn('tiempo_ini', function (Radicado $radicado) {
            return ($radicado->tiempo_ini) ? date('d-m-Y g:i:s A', strtotime($radicado->tiempo_ini)) : 'N/A' ;
        })
        ->editColumn('tiempo_fin', function (Radicado $radicado) {
            return ($radicado->tiempo_fin) ? date('d-m-Y g:i:s A', strtotime($radicado->tiempo_fin)) : 'N/A' ;
        })
        ->editColumn('duracion', function (Radicado $radicado) {
            return ($radicado->tiempo_ini && $radicado->tiempo_fin) ? $radicado->duracion() : 'N/A' ;
        })
        ->editColumn('barrio', function (Radicado $radicado) {
            return $radicado->barrio;
        })
        ->editColumn('solventado', function (Radicado $radicado) {
            return ($radicado->solventado) ? date('d-m-Y g:i:s A', strtotime($radicado->solventado)) : 'N/A' ;
        })
        ->editColumn('desconocido', function (Radicado $radicado) {
            return $radicado->desconocido;
        })
        ->editColumn('tiempo_fin', function (Radicado $radicado) {
            return ($radicado->tiempo_fin) ? date('d-m-Y g:i:s A', strtotime($radicado->tiempo_fin)):'N/A';
        })
        ->addColumn('acciones', $modoLectura ?  "" : "radicados.acciones")
        ->rawColumns(['ip', 'codigo', 'estatus', 'acciones', 'creado', 'prioridad', 'tecnico', 'desconocido', 'tiempo_fin'])
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
        $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
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

        $servicio = Servicio::find($request->servicio);

        if($servicio){

            if(!$request->contrato && $request->servicio != 4){

                $nombreServicio = trim(strtolower($servicio->nombre));

                if($nombreServicio != 'notificacion de data creditos' &&
                   $nombreServicio != 'notificacion de datacreditos' &&
                   $nombreServicio != 'notificacion datacredito' &&
                   $nombreServicio != 'notificacion de datacredito'
                   ){
                        $mensaje='El cliente no posee contrato asignado y no puede hacer uso de un servicio distinto a instalaciones o notificacion de datacredito';
                        return back()->withInput()->with('danger', $mensaje);
                    }

            }

        }else{
            return back()->withInput()->with('danger', 'No se encontro el tipo de servicio');
        }

        $radicado = new Radicado();
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
        $radicado->codigo = Radicado::getNextConsecutiveCodeNumber();
        $radicado->prioridad = $request->prioridad;
        $radicado->mac_address = $request->mac_address;
        $radicado->ip = $request->ip;
        $radicado->empresa = Auth::user()->empresa;
        $radicado->responsable = Auth::user()->id;
        $radicado->valor = ($request->servicio == 4) ? $request->valor : null;
        $radicado->oficina = $request->oficina;
        $radicado->barrio = $request->barrio;
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

        $log = new RadicadoLOG;
        $log->id_radicado = $radicado->id;
        $log->id_usuario = Auth::user()->id;
        $log->accion = 'Creación del radicado bajo el código #'.$radicado->codigo;
        $log->save();

        $mensaje='Se ha creado satisfactoriamente el radicado bajo el código #'.$radicado->codigo;
        return redirect('empresa/radicados')->with('success', $mensaje);
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $servicios = Servicio::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
        $tecnicos = User::where('empresa',Auth::user()->empresa)->where('rol', 4)->get();
        $oficinas = (Auth::user()->oficina && Auth::user()->empresa()->oficina) ? Oficina::where('id', Auth::user()->oficina)->get() : Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
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
                $radicado->update();
                $mensaje='SE HA CARGADO EL ARCHIVO ADJUNTO SATISFACTORIAMENTE.';

                $log = new RadicadoLOG;
                $log->id_radicado = $radicado->id;
                $log->id_usuario = Auth::user()->id;
                $log->accion = 'Carga de archivo adjunto.';
                $log->save();

                return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
            }

            if($request->adjunto1){

                $radicado->adjunto_1 = $request->adjunto1;
                $file = $request->file('adjunto1');
                $nombre = $radicado->codigo.'-'.'1'.date('Ymd').'.'.$file->extension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $radicado->adjunto_1 = $nombre;
                $radicado->update();
                $mensaje='SE HA CARGADO EL ARCHIVO ADJUNTO SATISFACTORIAMENTE.';

                $log = new RadicadoLOG;
                $log->id_radicado = $radicado->id;
                $log->id_usuario = Auth::user()->id;
                $log->accion = 'Carga de archivo adjunto.';
                $log->save();

                return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
            }

            if($request->adjunto2){


                $radicado->adjunto_2 = $request->adjunto2;
                $file = $request->file('adjunto2');
                $nombre = $radicado->codigo.'-'.'2'.date('Ymd').'.'.$file->extension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $radicado->adjunto_2 = $nombre;
                $radicado->update();
                $mensaje='SE HA CARGADO EL ARCHIVO ADJUNTO SATISFACTORIAMENTE.';

                $log = new RadicadoLOG;
                $log->id_radicado = $radicado->id;
                $log->id_usuario = Auth::user()->id;
                $log->accion = 'Carga de archivo adjunto.';
                $log->save();

                return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
            }

            if($request->adjunto3){

                $radicado->adjunto_3 = $request->adjunto3;
                $file = $request->file('adjunto3');
                $nombre = $radicado->codigo.'-'.'3'.date('Ymd').'.'.$file->extension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $radicado->adjunto_3 = $nombre;
                $radicado->update();
                $mensaje='SE HA CARGADO EL ARCHIVO ADJUNTO SATISFACTORIAMENTE.';

                $log = new RadicadoLOG;
                $log->id_radicado = $radicado->id;
                $log->id_usuario = Auth::user()->id;
                $log->accion = 'Carga de archivo adjunto.';
                $log->save();

                return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
            }

            if($request->adjunto4){

                $radicado->adjunto_4 = $request->adjunto4;
                $file = $request->file('adjunto4');
                $nombre = $radicado->codigo.'-'.'4'.date('Ymd').'.'.$file->extension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $radicado->adjunto_4 = $nombre;
                $radicado->update();
                $mensaje='SE HA CARGADO EL ARCHIVO ADJUNTO SATISFACTORIAMENTE.';

                $log = new RadicadoLOG;
                $log->id_radicado = $radicado->id;
                $log->id_usuario = Auth::user()->id;
                $log->accion = 'Carga de archivo adjunto.';
                $log->save();

                return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
            }

            if($request->archivo_de_audio){
                $radicado->adjunto_audio = $request->archivo_de_audio;
                $file = $request->file('archivo_de_audio');
                $nombre = $radicado->codigo.'-'.date('Ymd').'.'.$file->extension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $radicado->adjunto_audio = $nombre;
                $radicado->update();
                $mensaje='SE HA CARGADO EL ARCHIVO ADJUNTO SATISFACTORIAMENTE.';

                $log = new RadicadoLOG;
                $log->id_radicado = $radicado->id;
                $log->id_usuario = Auth::user()->id;
                $log->accion = 'Carga de archivo adjunto.';
                $log->save();

                return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
            }


            if ($request->reporte) {
                $radicado->reporte = $request->reporte;
                $radicado->update();
                $mensaje='SE HA REGISTRADO EL REPORTE DEL TÉCNICO SATISFACTORIAMENTE.';

                $log = new RadicadoLOG;
                $log->id_radicado = $radicado->id;
                $log->id_usuario = Auth::user()->id;
                $log->accion = 'Registro del reporte del técnico asociado.';
                $log->save();

                return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
            }
            $request->validate([
                'telefono' => 'required|numeric',
                'direccion' => 'required|max:200',
                'fecha' => 'required',
                'servicio' => 'required',
                'estatus' => 'required'
            ]);

            $servicio = Servicio::find($request->servicio);

            if($servicio){

                if(!$request->contrato && $request->servicio != 4){

                    $nombreServicio = trim(strtolower($servicio->nombre));

                    if($nombreServicio != 'notificacion de data creditos' &&
                       $nombreServicio != 'notificacion de datacreditos' &&
                       $nombreServicio != 'notificacion datacredito' &&
                       $nombreServicio != 'notificacion de datacredito'
                       ){
                            $mensaje='El cliente no posee contrato asignado y no puede hacer uso de un servicio distinto a instalaciones o notificacion de datacredito';
                            return back()->withInput()->with('danger', $mensaje);
                        }

                }

            }else{
                return back()->withInput()->with('danger', 'No se encontro el tipo de servicio');
            }

            $radicado->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $radicado->telefono = $request->telefono;
            $radicado->correo = $request->correo;
            $radicado->direccion = $request->direccion;
            if($request->desconocido){
                $radicado->desconocido = $radicado->desconocido.', '.$request->desconocido;
            }
            $radicado->servicio = $request->servicio;
            $radicado->tecnico = $request->tecnico;
            $radicado->estatus = $request->estatus;
            $radicado->prioridad = $request->prioridad;
            //$radicado->responsable = Auth::user()->id;
            $radicado->valor = ($request->servicio == 4) ? $request->valor : null;
            $radicado->oficina = $request->oficina;
            $radicado->barrio = $request->barrio;
            $radicado->update();

            $log = new RadicadoLOG;
            $log->id_radicado = $radicado->id;
            $log->id_usuario = Auth::user()->id;
            $log->accion = 'Actualización del caso radicado.';
            $log->save();

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

            if($radicado->adjunto_1){
                Storage::disk('documentos')->delete($radicado->adjunto_);
            }

            if($radicado->adjunto_2){
                Storage::disk('documentos')->delete($radicado->adjunto_2);
            }

            if($radicado->adjunto_3){
                Storage::disk('documentos')->delete($radicado->adjunto_3);
            }

            if($radicado->adjunto_4){
                Storage::disk('documentos')->delete($radicado->adjunto_4);
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
                $radicado->update();

                $log = new RadicadoLOG;
                $log->id_radicado = $radicado->id;
                $log->id_usuario = Auth::user()->id;
                $log->accion = 'Se ha escalado el caso radicado.';
                $log->save();

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
            }else if ($radicado->estatus==2 && $radicado->reporte) {
                $radicado->estatus=3;
            }else{
                return back()->with('danger', 'EL RADICADO NO PUEDE SER SOLVENTADO SIN TENER EL REPORTE DEL TÉCNICO');
            }
            $mensaje = 'SE HA SOLVENTADO EL CASO RADICADO';
            $radicado->solventado=Carbon::now()->toDateTimeString();
            $radicado->update();

            $log = new RadicadoLOG;
            $log->id_radicado = $radicado->id;
            $log->id_usuario = Auth::user()->id;
            $log->accion = 'Se ha solventado el caso radicado.';
            $log->save();

            if(isset($radicado->correo)){
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

                $emails = [$radicado->correo];
                $tituloCorreo = Auth::user()->empresa()->nombre.': Reporte de Radicado';

                // self::sendMail('emails.radicado', compact('radicado'), compact('radicado', 'emails', 'tituloCorreo'), function($message) use ($radicado){
                //     $message->to($radicado->correo)->subject(Auth::user()->empresa()->nombre.': Reporte de Radicado');
                // });
            }

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
            $radicado->update();
            $mensaje='Se ha registrado la firma del cliente.';

            $log = new RadicadoLOG;
            $log->id_radicado = $radicado->id;
            $log->id_usuario = Auth::user()->id;
            $log->accion = 'Se ha registrado la firma del cliente asociado al radicado.';
            $log->save();

            return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
        }
        return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function notificacionRadicado(){
        $radicado = Radicado::whereIn('estatus', [0,2])->where('tecnico', Auth::user()->id)->get();
        return json_encode($radicado);
    }

    public function datatable_cliente($contacto, Request $request){
        $requestData =  $request;

        $columns = array(
            0 => 'radicados.codigo',
            1 => 'radicados.fecha',
            2 => 'radicados.tipo',
            3 => 'radicados.status',
            4 => 'radicados.adjunto',
        );

        $requestData =  $request;

        $movimientos=Radicado::leftjoin('contactos as c', 'radicados.identificacion', '=', 'c.nit')
        ->select('radicados.*')
        ->where('radicados.empresa',Auth::user()->empresa);

        if ($contacto) {
            $movimientos=$movimientos->where('radicados.identificacion', $contacto);
         }
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
        $movimientos=$movimientos->distinct()->get();
        $data = array();
        foreach ($movimientos as $movimiento) {
            $nestedData = array();
            $nestedData[] = '<a href="'.$movimiento->show_url().'">'.$movimiento->codigo.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($movimiento->fecha));
            $nestedData[] = $movimiento->servicio()->nombre;
            $nestedData[] = '<strong><span class="text-'.$movimiento->estatus('true').'">'.$movimiento->estatus().'</span></strong>';
            $nestedData[] = '<a href="'.asset('../adjuntos/documentos/'.$movimiento->adjunto).'" target="_blank" class="btn btn-outline-success btn-sm btn-icons" style="border-radius: 50%;" title="Ver Adjunto"><i class="fas fa-eye"></i>';
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
                $msj = 'Iniciado el tiempo para solventar el radicado.';
            }else{
                $radicado->tiempo_fin = Carbon::now()->toDateTimeString();
                $inicio = Carbon::parse($radicado->tiempo_ini);
                $cierre = Carbon::parse($radicado->tiempo_fin);
                $duracion = $inicio->diffInMinutes($cierre);
                $mensaje = 'Radicado Finalizado, con una duración de '.$duracion.'min';
                $msj = 'Finalizado el tiempo para solventar el radicado.';
            }

            $radicado->update();

            $log = new RadicadoLOG;
            $log->id_radicado = $radicado->id;
            $log->id_usuario = Auth::user()->id;
            $log->accion = $msj;
            $log->save();

            return back()->with('success', $mensaje);
        }
        return back('empresa/radicados')->with('danger', 'No existe un registro con ese id');
    }

    public function eliminarAdjunto($id){

        $valores = explode(',', $id);
        dd($valores);
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if($radicado){
            Storage::disk('documentos')->delete($radicado->adjunto);
            $radicado->adjunto = NULL;
            $radicado->update();

            $log = new RadicadoLOG;
            $log->id_radicado = $radicado->id;
            $log->id_usuario = Auth::user()->id;
            $log->accion = 'Eliminando adjunto asociado al radicado';
            $log->save();

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
            $radicado->update();

            $log = new RadicadoLOG;
            $log->id_radicado = $radicado->id;
            $log->id_usuario = Auth::user()->id;
            $log->accion = 'Reabriendo el caso radicado.';
            $log->save();

            return back()->with('success', $mensaje);
        }
        return back('empresa/radicados')->with('success', 'NO EXISTE UN REGISTRO CON ESE ID');
    }

    public function exportar(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $objPHPExcel = new PHPExcel();
        $tituloReporte = "Reporte de Radicados";
        $titulosColumnas = array('Codigo', 'Fecha', 'Cliente', 'Identificacion', 'Celular', 'Correo Electronico', 'Direccion', 'Contrato', 'Direccion IP', 'Direccion MAC', 'Servicio', 'Tecnico', 'Estimado', 'Iniciado', 'Finalizado', 'Duracion', 'Prioridad', 'Estado', 'Observaciones', 'Reporte Tecnico');

        $letras= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        $objPHPExcel->getProperties()->setCreator("Sistema") // Nombre del autor
        ->setLastModifiedBy("Sistema") //Ultimo usuario que lo modific�1�7�1�7�1�7
        ->setTitle("Reporte Excel Radicados") // Titulo
        ->setSubject("Reporte Excel Radicados") //Asunto
        ->setDescription("Reporte de Radicados") //Descripci�1�7�1�7�1�7n
        ->setKeywords("reporte Radicados") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias
        // Se combinan las celdas A1 hasta D1, para colocar ah�1�7�1�7�1�7 el titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A1:T1');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1',$tituloReporte);
        // Titulo del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->mergeCells('A2:T2');
        // Se agregan los titulos del reporte
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2','Fecha '.date('d-m-Y')); // Titulo del reporte

        $estilo = array('font'  => array('bold'  => true, 'size'  => 12, 'name'  => 'Times New Roman' ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ));
        $objPHPExcel->getActiveSheet()->getStyle('A1:T3')->applyFromArray($estilo);

        $estilo =array('fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd08f50')));
        $objPHPExcel->getActiveSheet()->getStyle('A3:T3')->applyFromArray($estilo);

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

        $objPHPExcel->getActiveSheet()->getStyle('A3:T3')->applyFromArray($estilo);

        for ($i=0; $i <count($titulosColumnas) ; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letras[$i].'3', utf8_decode($titulosColumnas[$i]));
        }

        $i=4;
        $letra=0;

        $radicados = Radicado::query()
            ->where('radicados.empresa', Auth::user()->empresa);

        if(isset($request->codigo)){
            $radicados->where(function ($query) use ($request) {
                $query->orWhere('radicados.codigo', 'like', "%{$request->codigo}%");
            });
        }
        if(isset($request->fecha)){
            $fecha = date('Y-m-d', strtotime($request->fecha));
            $radicados->where(function ($query) use ($request, $fecha) {
                $query->orWhere('radicados.fecha', $fecha);
            });
        }
        if(isset($request->contrato)){
            $radicados->where(function ($query) use ($request) {
                $query->orWhere('radicados.contrato', 'like', "%{$request->contrato}%");
            });
        }
        if(isset($request->cliente)){
            $radicados->where(function ($query) use ($request) {
                $query->orWhere('radicados.nombre', 'like', "%{$request->cliente}%");
            });
        }
        if(isset($request->telefono)){
            $radicados->where(function ($query) use ($request) {
                $query->orWhere('radicados.telefono', 'like', "%{$request->telefono}%");
            });
        }
        if(isset($request->servicio)){
            $radicados->where(function ($query) use ($request) {
                $query->orWhere('radicados.servicio', $request->servicio);
            });
        }
        if(isset($request->estatus)){
            $estatus = ($request->estatus == 'A') ? 0 : $request->estatus;
            $radicados->where(function ($query) use ($request, $estatus) {
                $query->orWhere('radicados.estatus', $request->estatus);
            });
        }else{
            $radicados->where(function ($query) use ($request) {
                if($request->otp == 0){
                    $query->whereIn('radicados.estatus', [0, 2]);
                }elseif($request->otp == 1){
                    $query->whereIn('radicados.estatus', [1, 3]);
                }
            });
        }
        if(isset($request->prioridad)){
            $radicados->where(function ($query) use ($request) {
                $query->orWhere('radicados.prioridad', $request->prioridad);
            });
        }
        if(isset($request->tecnico)){
            $radicados->where(function ($query) use ($request) {
                $query->orWhere('radicados.tecnico', $request->tecnico);
            });
        }
        if($request->tiempo_fin){
            $radicados->where(function ($query) use ($request) {
                $query->orWhereDate('radicados.tiempo_fin', 'like', "%{$request->tiempo_fin}%");
            });
        }

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $radicados->where('radicados.oficina', auth()->user()->oficina);
            }
        }

        if(auth()->user()->rol == 4){
            $radicados = $radicados->where('radicados.tecnico',Auth::user()->id)->orderby('radicados.id','ASC');
        }

        $radicados = $radicados->get();

        foreach ($radicados as $radicado) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($letras[0].$i, $radicado->codigo)
                ->setCellValue($letras[1].$i, $radicado->fecha)
                ->setCellValue($letras[2].$i, $radicado->nombre)
                ->setCellValue($letras[3].$i, $radicado->identificacion)
                ->setCellValue($letras[4].$i, $radicado->telefono)
                ->setCellValue($letras[5].$i, $radicado->correo)
                ->setCellValue($letras[6].$i, $radicado->direccion)
                ->setCellValue($letras[7].$i, ($radicado->contrato) ? $radicado->contrato : '')
                ->setCellValue($letras[8].$i, ($radicado->ip) ? $radicado->ip : '')
                ->setCellValue($letras[9].$i, ($radicado->mac_address) ? $radicado->mac_address : '')
                ->setCellValue($letras[10].$i, ($radicado->servicio) ? $radicado->servicio()->nombre : '')
                ->setCellValue($letras[11].$i, ($radicado->tecnico) ? $radicado->tecnico()->nombres : '')
                ->setCellValue($letras[12].$i, ($radicado->tiempo_est) ? $radicado->tiempo_est.' min' : '')
                ->setCellValue($letras[13].$i, ($radicado->tiempo_ini) ? date('d-m-Y g:i:s A', strtotime($radicado->tiempo_ini)) : '')
                ->setCellValue($letras[14].$i, ($radicado->tiempo_fin) ? date('d-m-Y g:i:s A', strtotime($radicado->tiempo_fin)) : '')
                ->setCellValue($letras[15].$i, ($radicado->tiempo_ini && $radicado->tiempo_fin) ? $radicado->duracion() : '')
                ->setCellValue($letras[16].$i, $radicado->prioridad())
                ->setCellValue($letras[17].$i, $radicado->estatus())
                ->setCellValue($letras[18].$i, $radicado->desconocido)
                ->setCellValue($letras[19].$i, $radicado->reporte);
            $i++;
        }

        $estilo =array('font'  => array('size'  => 12, 'name'  => 'Times New Roman' ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
        $objPHPExcel->getActiveSheet()->getStyle('A3:T'.$i)->applyFromArray($estilo);

        for($i = 'A'; $i <= $letras[20]; $i++){
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Se asigna el nombre a la hoja
        $objPHPExcel->getActiveSheet()->setTitle('Lista de Radicados');

        // Se activa la hoja para que sea la que se muestre cuando el archivo se abre
        $objPHPExcel->setActiveSheetIndex(0);

        // Inmovilizar paneles
        $objPHPExcel->getActiveSheet(0)->freezePane('A5');
        $objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);
        $objPHPExcel->setActiveSheetIndex(0);
        header("Pragma: no-cache");
        header('Content-type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Radicados.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function notificacionTecnico(){
        $iniciados = Radicado::whereNotNull('tiempo_ini')->where('fecha', date("Y-m-d"))->get()->count();
        $finalizados = Radicado::whereNotNull('tiempo_ini')->whereNotNull('tiempo_fin')->where('fecha', date("Y-m-d"))->get()->count();
        $encurso = $iniciados-$finalizados;

        return response()->json([
            'success'     => true,
            'iniciados'   => $iniciados,
            'finalizados' => $finalizados,
            'encurso'     => $encurso,
        ]);
    }

    public function state_lote($radicados, $state){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $radicados = explode(",", $radicados);

        for ($i=0; $i < count($radicados) ; $i++) {
            $radicado = Radicado::find($radicados[$i]);
            if ($radicado) {
                if($state == 'solventar'){
                    if ($radicado->estatus==0) {
                        $radicado->estatus=1;
                    }else if ($radicado->estatus==2) {
                        $radicado->estatus=3;
                    }
                    $radicado->solventado=Carbon::now()->toDateTimeString();
                }elseif($state == 'reabrir'){
                    if ($radicado->estatus == 1) {
                        $radicado->estatus = 0;
                    }else if ($radicado->estatus == 3) {
                        $radicado->estatus = 2;
                    }
                }

                $radicado->update();

                $log = new RadicadoLOG;
                $log->id_radicado = $radicado->id;
                $log->id_usuario = Auth::user()->id;
                $log->accion = ($state == 'solventar') ? 'Se ha solventado el caso radicado.' : 'Reabriendo el caso radicado.';
                $log->save();

                if($state == 'solventar'){
                    if(isset($radicado->correo)){
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

                        // self::sendMail('emails.radicado', compact('radicado'), compact('radicado'), function($message) use ($radicado){
                        //     $message->to($radicado->correo)->subject(Auth::user()->empresa()->nombre.': Reporte de Radicado');
                        // });
                    }
                }
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

    public function destroy_lote($radicados){
        $this->getAllPermissions(Auth::user()->id);

        $succ = 0; $fail = 0;

        $radicados = explode(",", $radicados);

        for ($i=0; $i < count($radicados) ; $i++) {
            $radicado = Radicado::find($radicados[$i]);
            if ($radicado) {
                if($radicado->adjunto){
                    Storage::disk('documentos')->delete($radicado->adjunto);
                }
                $radicado->delete();
                $succ++;
            }else{
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

    public function log($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado = radicado::find($id);
        if ($radicado) {
            view()->share(['icon'=>'fas fa-clipboard-list', 'title' => 'Log | Radicado: '.$radicado->codigo]);
            return view('radicados.log')->with(compact('radicado'));
        } else {
            $mensaje='NO SE HA PODIDO OBTENER EL LOG DEL RADICADO';
            return redirect('empresa/radicados/'.$radicado->id)->with('danger', $mensaje);
        }
        return redirect('empresa/radicados')->with('danger', 'EL RADICADO NO SE HA ENCONTRADO');
    }

    public function logs(Request $request, $radicado){
        $modoLectura = auth()->user()->modo_lectura();
        $radicados = RadicadoLOG::query();
        $radicados->where('id_radicado', $radicado);

        return datatables()->eloquent($radicados)
            ->editColumn('created_at', function (RadicadoLOG $radicado) {
                return date('d-m-Y g:i:s A', strtotime($radicado->created_at));
            })
            ->editColumn('id_usuario', function (RadicadoLOG $radicado) {
                return $radicado->id_usuario();
            })
            ->editColumn('accion', function (RadicadoLOG $radicado) {
                return $radicado->accion;
            })
            ->rawColumns(['created_at', 'id_usuario', 'accion'])
            ->toJson();
    }
}
