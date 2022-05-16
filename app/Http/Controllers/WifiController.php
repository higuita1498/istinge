<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;
use App\Wifi;
use App\Contacto;
use Mail;
use App\Mail\WifiMailable;
use Config;
use App\ServidorCorreo;
use App\Empresa;

class WifiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        set_time_limit(0);
        view()->share(['subseccion' => 'wifi', 'title' => 'Cambios WIFI', 'icon' =>'fas fa-wifi', 'seccion' => 'atencion_cliente', 'invert' => true]);
    }
    
    public function index(){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = Contacto::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        
        //return view('wifi.index')->with(compact('solicitudes'));
        return view('wifi.indexnew')->with(compact('clientes'));
    }
    
    public function solicitudes(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $solicitudes = Wifi::query()
            ->where('empresa', Auth::user()->empresa);

        if ($request->filtro == true) {
            switch ($request) {
                case !empty($request->id_cliente):
                    $solicitudes->where('id_cliente', $request->id_cliente);
                    break;
                case !empty($request->status):
                    $solicitudes->where('status', $request->status);
                    break;
                default:
                    break;
            }
        }

        return datatables()->eloquent($solicitudes)
            ->editColumn('id', function (Wifi $solicitud) {
                return $solicitud->id;
            })
            ->editColumn('id_cliente', function (Wifi $solicitud) {
                return "<a href=" . route('contactos.show', $solicitud->id_cliente) . ">{$solicitud->cliente()->nombre} {$solicitud->cliente()->apellidos()}</div></a>";
            })
            ->editColumn('red_antigua', function (Wifi $solicitud) {
                return "{$solicitud->red_antigua}";
            })
            ->editColumn('red_nueva', function (Wifi $solicitud) {
                return "<span style='display: inline-flex;'><div id='".$solicitud->id."'>".$solicitud->red_nueva."</div> <a href='javascript:copiarData({$solicitud->id})'> <i class='far fa-copy'></i></a></span>";
            })
            ->editColumn('pass_antigua', function (Wifi $solicitud) {
                return "{$solicitud->pass_antigua}";
            })
            ->editColumn('pass_nueva', function (Wifi $solicitud) {
                return $solicitud->pass_nueva;
                return "<span style='display: inline-flex;'><div id='".$solicitud->id."'>".$solicitud->pass_nueva."</div> <a href='javascript:copiarData({$solicitud->id})'> <i class='far fa-copy'></i></a></span>";
            })
            ->editColumn('ip', function (Wifi $solicitud) {
                if(isset($solicitud->cliente()->contrato()->puerto_conexion)){
                    return "<a href='http://".$solicitud->ip.":".$solicitud->cliente()->contrato()->puerto_conexion."' target='_blank'>{$solicitud->ip} <i class='fas fa-external-link-square-alt'></i></a>";
                }
                return "<a href='http://".$solicitud->ip."' target='_blank'>{$solicitud->ip} <i class='fas fa-external-link-square-alt'></i></a>";
            })
            ->editColumn('mac', function (Wifi $solicitud) {
                return "{$solicitud->mac}";
            })
            ->editColumn('oculto', function (Wifi $solicitud) {
                return $solicitud->oculta == 0 ? 'No' : 'Si';
            })
            ->editColumn('status', function (Wifi $solicitud) {
                return  '<span class="font-weight-bold text-' . $solicitud->estatus(true) . '">' . $solicitud->estatus() . ' </span>';
            })
            ->editColumn('created_by', function (Wifi $solicitud) {
                return  $solicitud->created_by ? $solicitud->created_by()->nombres : '';
            })
            ->addColumn('created_at', function (Wifi $solicitud) {
                return  date('d-m-Y', strtotime($solicitud->fecha));
            })
            ->addColumn('updated_at', function (Wifi $solicitud) {
                return  $solicitud->status == 0 ? date('d-m-Y h:m:s', strtotime($solicitud->updated_at)) : '';
            })
            ->addColumn('acciones', $modoLectura ?  "" : "wifi.acciones-wifi")
            ->rawColumns(['acciones', 'id_cliente', 'status', 'oculto', 'ip', 'red_nueva', 'pass_nueva'])
            ->toJson();
    }
  
    public function status($id)
    {
        $solicitud = Wifi::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($solicitud) {
            if ($solicitud->status == 1) {
                $mensaje = 'Cambio de contraseña realizado';
                $solicitud->status = 0;
                $solicitud->created_by = Auth::user()->id;
                $solicitud->save();
                
                $datos = array(
                    'nombres' => $solicitud->cliente()->nombre.' '.$solicitud->cliente()->apellidos(),
                    'red_nueva' => $solicitud->red_nueva,
                    'pass_nueva' => $solicitud->pass_nueva,
                    'oculta' => $solicitud->oculta(),
                    'ip' => $solicitud->ip,
                    'fecha'=> $solicitud->fecha,
                );
                
                $correo = new WifiMailable($datos);
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
                Mail::to($solicitud->cliente()->email)->send($correo);
            } else {
                $mensaje = 'Cambio de contraseña cancelada';
                $solicitud->status = 1;
                $solicitud->created_by = Auth::user()->id;
                $solicitud->save();
            }
            return redirect('empresa/wifi')->with('success', $mensaje);
        }
        return redirect('empresa/wifi')->with('success', 'No existe un registro con ese id');
    }
    
    public function notificacionWifi(){
        $notificaciones = Wifi::where('status', 1)->get();
        return json_encode($notificaciones);
    }
}
