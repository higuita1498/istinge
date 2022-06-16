<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;

use App\MovimientoLOG;
use App\User;
use App\Contacto;

class AuditoriaController extends Controller
{
    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'auditoria', 'title' => 'Auditorías', 'icon' =>'fas fa-user-secret']);
    }

    public function contratos(){
        $this->getAllPermissions(Auth::user()->id);
        $usuarios = User::where('empresa',Auth::user()->empresa)->where('user_status', 1)->get();
        $clientes = (Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();

        view()->share(['subseccion' => 'auditoria-contratos', 'title' => 'Auditorías de Contratos', 'icon' =>'fas fa-user-secret']);
        return view('auditorias.index')->with(compact('usuarios', 'clientes'));
    }

    public function auditoria_contratos(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $movimientos = MovimientoLOG::query()
        ->join('contracts as cs', 'cs.id', '=', 'log_movimientos.contrato')
        ->join('contactos as c', 'c.id', '=', 'cs.client_id')
        ->select('log_movimientos.*', 'cs.id as cs_id', 'cs.nro as cs_nro', 'cs.ip as cs_ip', 'c.nombre', 'c.apellido1', 'c.apellido2');

        if ($request->filtro == true) {
            if($request->client_id){
                $movimientos->where(function ($query) use ($request) {
                    $query->orWhere('c.id', $request->client_id);
                });
            }
            if($request->contrato){
                $movimientos->where(function ($query) use ($request) {
                    $query->orWhere('log_movimientos.contrato', $request->contrato);
                });
            }
            if($request->ip){
                $movimientos->where(function ($query) use ($request) {
                    $query->orWhere('cs.ip', 'like', "%{$request->ip}%");
                });
            }
            if($request->created_by){
                $movimientos->where(function ($query) use ($request) {
                    $query->orWhere('log_movimientos.created_by', $request->created_by);
                });
            }

            if($request->desde){
                $movimientos->where(function ($query) use ($request) {
                    $query->whereDate('log_movimientos.created_at', '>=', Carbon::parse($request->desde)->format('Y-m-d'));
                });
            }
            if($request->hasta){
                $movimientos->where(function ($query) use ($request) {
                    $query->whereDate('log_movimientos.created_at', '<=', Carbon::parse($request->hasta)->format('Y-m-d'));
                });
            }
        }

        return datatables()->eloquent($movimientos)
            ->editColumn('contrato', function (MovimientoLOG $movimiento) {
                return $movimiento->cs_nro;
            })
            ->editColumn('ip', function (MovimientoLOG $movimiento) {
                return $movimiento->cs_ip;
            })
            ->editColumn('cliente', function (MovimientoLOG $movimiento) {
                return $movimiento->nombre.' '.$movimiento->apellido1.' '.$movimiento->apellido2;
            })
            ->editColumn('created_at', function (MovimientoLOG $movimiento) {
                return date('d-m-Y g:i:s A', strtotime($movimiento->created_at));
            })
            ->editColumn('created_by', function (MovimientoLOG $movimiento) {
                return $movimiento->created_by();
            })
            ->editColumn('descripcion', function (MovimientoLOG $movimiento) {
                return $movimiento->descripcion;
            })
            ->rawColumns(['contrato', 'cliente', 'created_at', 'created_by', 'descripcion'])
            ->toJson();
    }
}
