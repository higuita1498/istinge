<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PromesaPago;
use App\User;
use App\Funcion;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;
use App\Funciones;
use App\Model\Ingresos\Factura;
use App\Contacto;
use App\Campos;

class PromesasPagoController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'facturas', 'subseccion' => 'promesaspago', 'title' => 'Promesas de Pago', 'icon' =>'fas fa-calendar']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = (Auth::user()->empresa()->oficina) ? Contacto::join('factura as f', 'contactos.id', '=', 'f.cliente')->where('contactos.status', 1)->where('contactos.oficina', Auth::user()->oficina)->groupBy('f.cliente')->select('contactos.*')->orderBy('contactos.nombre','asc')->get() : Contacto::join('factura as f', 'contactos.id', '=', 'f.cliente')->where('contactos.status', 1)->groupBy('f.cliente')->select('contactos.*')->orderBy('contactos.nombre','asc')->get();
        //$clientes = Contacto::join('factura as f', 'contactos.id', '=', 'f.cliente')->where('contactos.status', 1)->groupBy('f.cliente')->select('contactos.*')->orderBy('contactos.nombre','asc')->get();
        $usuarios = User::where('user_status', 1)->where('empresa', Auth::user()->empresa)->get();
        $tabla = Campos::where('modulo', 11)->where('estado', 1)->where('empresa', Auth::user()->empresa)->orderBy('orden', 'asc')->get();

        return view('promesas-pago.index', compact('clientes','usuarios','tabla'));
    }

    public function promesas(Request $request){
        $modoLectura = auth()->user()->modo_lectura();

        $promesas = PromesaPago::query()
            ->join('factura', 'factura.id', '=', 'promesa_pago.factura')
            ->join('contactos', 'contactos.id', '=', 'promesa_pago.cliente')
            ->select('promesa_pago.*')
            ->where('factura.estatus', 1);

        if ($request->filtro == true) {
            if($request->cliente){
                $promesas->where(function ($query) use ($request) {
                    $query->orWhere('promesa_pago.cliente', $request->cliente);
                });
            }
            if($request->created_by){
                $promesas->where(function ($query) use ($request) {
                    $query->orWhere('promesa_pago.created_by', $request->created_by);
                });
            }
        }

        if(Auth::user()->empresa()->oficina){
            if(auth()->user()->oficina){
                $promesas->where('contactos.oficina', auth()->user()->oficina);
            }
        }

        return datatables()->eloquent($promesas)
        ->editColumn('nro', function (PromesaPago $promesa) {
            return $promesa->nro;
        })
        ->editColumn('cliente', function (PromesaPago $promesa) {
            return  "<a href=" . route('contactos.show', $promesa->cliente) . ">{$promesa->cliente()->nombre} {$promesa->cliente()->apellidos()}</a>";
        })
        ->editColumn('factura', function (PromesaPago $promesa) {
            return  "<a href=" . route('facturas.show', $promesa->factura()->id) . ">{$promesa->factura()->codigo}</a>";
        })
        ->editColumn('fecha', function (PromesaPago $promesa) {
            return date('d-m-Y', strtotime($promesa->fecha));
        })
        ->editColumn('vencimiento', function (PromesaPago $promesa) {
            return (date('Y-m-d') > $promesa->vencimiento) ? '<span class="text-danger">' . date('d-m-Y', strtotime($promesa->vencimiento)) . '</span>' : date('d-m-Y', strtotime($promesa->vencimiento));
        })
        ->addColumn('acciones', $modoLectura ?  "" : "promesas-pago.acciones")
        ->rawColumns(['cliente', 'acciones', 'factura', 'vencimiento'])
        ->toJson();
    }
    
    public function json(Request $request, $id){
        $requestData =  $request;
        $columns = array(
            0 => 'nro',
            1 => 'factura',
            2 => 'fecha',
            3 => 'vencimiento',
            4 => 'created_by',
            5 => 'created_at'
        );
        
        $promesas=PromesaPago::join('factura', 'factura.id', '=', 'promesa_pago.factura')->
        join('contactos', 'contactos.id', '=', 'promesa_pago.cliente')->
        select('promesa_pago.*')->
        where('promesa_pago.cliente', $id);
        
        if (isset($requestData->search['value'])) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $promesas=$promesas->where(function ($query) use ($requestData) {
                $query->where('factura.codigo', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        
        $totalFiltered=$totalData=$promesas->count();
        
        $promesas=$promesas->OrderBy('factura.codigo', 'desc')->get();
        
        $data = array();
        foreach ($promesas as $promesa) {
            $nestedData = array();
            $nestedData[] = $promesa->nro;
            $nestedData[] = '<a href="'.route('facturas.show',$promesa->factura).'">'.$promesa->factura()->codigo.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($promesa->fecha));
            if(date('Y-m-d') > $promesa->vencimiento){
                $nestedData[] = '<spam class="text-danger">'.date('d-m-Y', strtotime($promesa->vencimiento)).'</spam>';
            }else{
                $nestedData[] = date('d-m-Y', strtotime($promesa->vencimiento));
            }
            $nestedData[] = $promesa->usuario()->nombres;
            $nestedData[] = '<a href="'.route('promesas.imprimir',['id' => $promesa->id, 'name'=> 'Promesa No. '.$promesa->nro.'.pdf']).'" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a>';
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
    
    public function imprimir($id){
        $promesa = PromesaPago::find($id);
        
        if($promesa) {
            $factura = Factura::find($promesa->factura);
            view()->share(['title' => 'Promesa de Pago Nro. '.$promesa->nro]);
            $itemscount = 1;
            $pdf = PDF::loadView('pdf.promesa_pago', compact('promesa', 'factura','itemscount'));
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
        }
    }
}
