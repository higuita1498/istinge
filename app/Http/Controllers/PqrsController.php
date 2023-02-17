<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Empresa; 
use App\User;
use App\PQRS;
use Session;
use Response;
use Carbon\Carbon;
use Validator; 
use Illuminate\Validation\Rule; 
use QrCode; 
use File;
use DOMDocument;
use Auth; 
use Mail; 
use bcrypt; 
use DB;
use Barryvdh\DomPDF\Facade as PDF;
use App\Mail\PQRSMailable;
use Config;
use App\ServidorCorreo;

class PqrsController extends Controller
{
    protected $url;
    
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['subseccion' => 'pqrs', 'title' => 'PQRS', 'icon' =>'far fa-life-ring', 'seccion' => 'atencion_cliente', 'invert' => true]);
    }
  
    public function index(Request $request)
    {
        $this->getAllPermissions(Auth::user()->id);
        $usuarios = User::where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        
        return view('pqrs.index', compact('usuarios'));
    }

    public function pqrs(Request $request)
    {
        $modoLectura = auth()->user()->modo_lectura();

        $pqrss = PQRS::query()
            ->where('empresa', Auth::user()->empresa);

        if ($request->filtro == true) {
            switch ($request) {
                case !empty($request->updated_by):
                    $pqrss->where('updated_by', $request->updated_by);
                    break;
                case !empty($request->estatus):
                    $pqrss->where('estatus', $request->estatus);
                    break;
                case !empty($request->solicitud):
                    $pqrss->where('solicitud', $request->solicitud);
                    break;
                case !empty($request->creacion):
                    $pqrss->where('fecha', $request->creacion);
                    break;
                default:
                    break;
            }
        }

        return datatables()->eloquent($pqrss)
        ->addColumn('solicitud', function (PQRS $pqrs) {
            return $pqrs->solicitud;
        })
        ->addColumn('nombres', function (PQRS $pqrs) {
            return $pqrs->nombres;
        })
        ->addColumn('email', function (PQRS $pqrs) {
            return $pqrs->email;
        })
        ->addColumn('fecha', function (PQRS $pqrs) {
            return date('d-m-Y', strtotime($pqrs->fecha));
        })
        ->editColumn('estatus', function (PQRS $pqrs) {
            return   '<span class="text-' . $pqrs->estatus(true) . '">' . $pqrs->estatus() . '</span>';
        })
        ->addColumn('acciones', $modoLectura ?  "" : "pqrs.acciones-pqrs")
        ->rawColumns(['estatus', 'acciones', 'vencimiento'])
        ->toJson();
    }
    
    public function show($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $pqrs = PQRS::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($pqrs) {
            view()->share(['icon'=>'far fa-life-ring', 'title' => 'Detalles PQRS: '.$pqrs->id]);
            return view('pqrs.show')->with(compact('pqrs'));
        }
        return redirect('empresa/pqrs')->with('success', 'No existe un registro con ese id');
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'respuesta' => 'required'
        ]);
        
        $pqrs = PQRS::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        
        if ($pqrs) {
            $pqrs->respuesta = $request->respuesta;
            $pqrs->estatus = 0;
            $pqrs->fecha_resp = Carbon::parse($request->fecha)->format('Y-m-d');
            $pqrs->updated_by = Auth::user()->id;
            $pqrs->save();
            
            $datos = array(
                'fecha'=> $pqrs->fecha,
                'solicitud'=> $pqrs->solicitud,
                'nombres'=> $pqrs->nombres,
                'email'=> $pqrs->email,
                'mensaje'=> $pqrs->mensaje,
                'fecha_resp'=> $pqrs->fecha_resp,
                'respuesta'=> $pqrs->respuesta,
                'updated_by'=> $pqrs->updated_by()->nombres,
            );
    
            $correo = new PQRSMailable($datos);

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
                        ],
                    ]
                );
                config(['mail'=>$new]);
            }
    
            // Mail::to($pqrs->email)->send($correo);
            
            return redirect('empresa/pqrs')->with('success', 'Se ha registrado la respuesta al PQRS satisfactoriamente.');
        }
        return redirect('empresa/pqrs')->with('success', 'No existe un registro con ese id');
    }
}