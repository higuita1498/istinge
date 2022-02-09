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
use Barryvdh\DomPDF\Facade as PDF;

class RadicadosController extends Controller{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['subseccion' => 'radicados', 'title' => 'Radicados', 'icon' =>'far fa-life-ring', 'seccion' => 'atencion_cliente']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);

        $clientes = Contacto::where('status', 1)->orderBy('nombre','asc')->get();
        $servicios = Servicio::where('estatus', 1)->orderBy('nombre','asc')->get();
        $tipo = '';
        return view('radicados.indexnew', compact('clientes','tipo','servicios'));
    }

    public function indexNew(Request $request, $tipo){
        $this->getAllPermissions(Auth::user()->id);

        $clientes = Contacto::where('status', 1)->orderBy('nombre','asc')->get();
        $servicios = Servicio::where('estatus', 1)->orderBy('nombre','asc')->get();
        if($tipo == 'solventados'){
            $tipo = 1;
        }elseif($tipo == 'pendientes'){
            $tipo = 0;
        }else{
            $tipo = 'all';
        }
        return view('radicados.indexnew', compact('clientes','tipo','servicios'));
    }

    public function radicados(Request $request, $tipo){
        $modoLectura = auth()->user()->modo_lectura();
        $radicados = Radicado::query()
            ->join('servicios as s', 's.id','=','radicados.servicio')
            ->select('radicados.*', 's.nombre as nombre_servicio');

        if ($request->filtro == true) {
            if($request->codigo){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.codigo', 'like', "%{$request->codigo}%");
                });
            }
            if($request->fecha){
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.fecha', $request->fecha);
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
                $radicados->where(function ($query) use ($request) {
                    $query->orWhere('radicados.estatus', $request->estatus);
                });
            }
        }

        if(auth()->user()->rol > 3){
            $radicados = $radicados->where('tecnico',Auth::user()->id)->orderby('radicados.direccion','ASC');
        }

        if($tipo == 0){
            $radicados->where(function ($query) use ($tipo) {
                $query->whereIn('radicados.estatus', [0,2]);
            });
        }elseif($tipo == 1){
            $radicados->where(function ($query) use ($tipo) {
                $query->whereIn('radicados.estatus', [1,3]);
            });
        }elseif($tipo == 'all'){
            $radicados->where(function ($query) {
                $query->whereIn('radicados.estatus', [0,1,2,3]);
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
        ->addColumn('acciones', $modoLectura ?  "" : "radicados.acciones")
        ->rawColumns(['codigo', 'estatus', 'acciones'])
        ->toJson();
    }

    /*public function index(){
        $this->getAllPermissions(Auth::user()->id);
        $con=0;
        foreach ($_SESSION['permisos'] as $key => $value) {
            ($key==208) ? $con++ : $con=0;
        }
        $radicados = (User::where('id',Auth::user()->id)->first()->rol == 43) ? Radicado::join('servicios as s', 's.id','=','radicados.servicio')->select('radicados.*', 's.nombre as nombre_servicio')->where('tecnico',Auth::user()->id)->orderby('direccion','ASC')->get() : Radicado::join('servicios as s', 's.id','=','radicados.servicio')->select('radicados.*', 's.nombre as nombre_servicio')->get();
        return view('radicados.index')->with(compact('radicados'));
    }*/

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = Contacto::where('status',1)->orderBy('nombre','asc')->get();
        $identificaciones = TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        $paises  =DB::table('pais')->get();
        $departamentos = DB::table('departamentos')->get();
        $planes = PlanesVelocidad::all();
        $servicios = Servicio::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
        $tecnicos = User::where('empresa',Auth::user()->empresa)->where('rol', 4)->get();
        view()->share(['icon'=>'far fa-life-ring', 'title' => 'Nuevo Caso']);
        return view('radicados.create')->with(compact('clientes','identificaciones','paises','departamentos', 'tipos_empresa', 'prefijos', 'vendedores', 'listas','planes','servicios','tecnicos'));
    }

    public function store(Request $request){
        $request->validate([
            'cliente' => 'required',
            'fecha' => 'required',
            'desconocido' => 'required',
            'servicio' => 'required',
            'estatus' => 'required',
            'telefono' => 'required',
            'direccion' => 'required'
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
        $radicado->mac_address = $request->mac_address;
        $radicado->ip = $request->ip;
        $radicado->empresa = Auth::user()->empresa;
        $radicado->responsable = Auth::user()->id;
        $radicado->valor = ($request->servicio == 4) ? $request->valor : null;
        $radicado->save();

        $mensaje='Se ha creado satisfactoriamente el radicado bajo el código #'.$radicado->codigo;
        return redirect('empresa/radicados')->with('success', $mensaje);
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $servicios = Servicio::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
        $tecnicos = User::where('empresa',Auth::user()->empresa)->where('rol', 4)->get();
        if ($radicado) {
            view()->share(['icon'=>'far fa-life-ring', 'title' => 'Modificar Radicado: '.$radicado->codigo]);
            return view('radicados.edit')->with(compact('radicado','servicios','tecnicos'));
        }
        return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function update(Request $request, $id){
        $radicado =Radicado::find($id);
        if ($radicado) {
            if ($request->reporte) {
                $radicado->reporte = $request->reporte;
                $radicado->save();
                $mensaje='Se ha registrado el reporte del técnico satisfactoriamente.';
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
            $radicado->responsable = Auth::user()->id;
            $radicado->valor = ($request->servicio == 4) ? $request->valor : null;
            $radicado->save();

            $mensaje='Se ha modificado satisfactoriamente el radicado #'.$radicado->codigo;
            return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
        }
        return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado=Radicado::find($id);
        if ($radicado) {
            view()->share(['icon'=>'far fa-life-ring', 'title' => 'Detalles Radicado: '.$radicado->codigo]);
            $inicio = Carbon::parse($radicado->tiempo_ini);
            $cierre = Carbon::parse($radicado->tiempo_fin);
            $duracion = $inicio->diffInMinutes($cierre);
            return view('radicados.show')->with(compact('radicado','duracion'));
        }
        return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function destroy($id){
        $radicado=Radicado::find($id);
        if ($radicado) {
            $radicado->delete();
        }
        return redirect('empresa/radicados')->with('success', 'El radicado ha sido eliminado satisfactoriamente');
    }

    public function escalar($id){
        $radicado=Radicado::find($id);
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
        $radicado=Radicado::find($id);
        if ($radicado) {
            if ($radicado->estatus==0) {
                $radicado->estatus=1;
            }else if ($radicado->estatus==2) {
                $radicado->estatus=3;
            }
            $mensaje='Se ha resuelto el caso radicado';
            $radicado->save();
            return back()->with('success', $mensaje);
        }
        return back('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function imprimir($id){
        $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id',$id)->first();
        if($radicado) {
            view()->share(['title' => 'Caso Radicado #'.$radicado->codigo]);
            $pdf = PDF::loadView('pdf.radicados', compact('radicado'));
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
        }
    }

    public function firmar($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado=Radicado::find($id);
        view()->share(['icon'=>'far fa-life-ring', 'title' => 'Firma Radicado: '.$radicado->codigo]);
        return view('radicados.firma')->with(compact('radicado'));
    }

    public function storefirma(Request $request, $id){
        $radicado =Radicado::find($id);
        if ($radicado) {
            $radicado->firma = $request->dataImg;
            $radicado->save();
            $mensaje='Se ha registrado la firma del cliente.';
            return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
        }
        return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
    }

    public function notificacion(){
        $radicado=Radicado::where('tecnico',Auth::user()->id)->where('estatus',2)->get();
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
        if ($requestData->search['value']) {
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
        $radicado=Radicado::find($id);
        if ($radicado) {
            if ($radicado->tiempo_fin) {
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
}
