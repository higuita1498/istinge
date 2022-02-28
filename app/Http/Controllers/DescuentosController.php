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

use App\Descuento;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\ItemsFactura;
use App\User;
use App\Contacto;
use App\Campos;

class DescuentosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'facturas', 'subseccion' => 'descuento', 'title' => 'Descuentos', 'icon' => 'fas fa-percentage']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = Contacto::where('tipo_contacto', 0)->get();
        $usuarios = User::where('user_status', 1)->get();
        $tabla = Campos::where('modulo', 9)->where('estado', 1)->orderBy('orden', 'asc')->get();

        return view('descuentos.index')->with(compact('clientes', 'usuarios', 'tabla'));
    }

    public function descuentos(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $descuentos = Descuento::query()
                        ->join('factura', 'factura.id', '=', 'descuentos.factura')
                        ->select('descuentos.*', 'factura.codigo', 'factura.cliente');

        if ($request->filtro == true) {
            if($request->codigo){
                $descuentos->where(function ($query) use ($request) {
                    $query->orWhere('factura.codigo', 'like', "%{$request->factura}%");
                });
            }
            if($request->cliente){
                $descuentos->where(function ($query) use ($request) {
                    $query->orWhere('factura.cliente', $request->cliente);
                });
            }
            if($request->estado){
                $descuentos->where(function ($query) use ($request) {
                    $query->orWhere('descuentos.estado', $request->estado);
                });
            }/*else{
                $descuentos->where(function ($query) use ($request) {
                    $query->orWhere('descuentos.estado', 2);
                });
            }*/
            if($request->created_by){
                $descuentos->where(function ($query) use ($request) {
                    $query->orWhere('descuentos.created_by', $request->created_by);
                });
            }
            if($request->updated_by){
                $descuentos->where(function ($query) use ($request) {
                    $query->orWhere('descuentos.updated_by', $request->updated_by);
                });
            }
        }

        return datatables()->eloquent($descuentos)
            ->editColumn('id', function (Descuento $descuento) {
                return "<a href=" . route('descuentos.show', $descuento->id) . ">{$descuento->id}</div></a>";
            })
            ->editColumn('cliente', function (Descuento $descuento) {
                return $descuento->factura()->cliente()->nombre;
            })
            ->editColumn('factura', function (Descuento $descuento) {
                return "<a href=" . route('facturas.show', $descuento->factura) . ">{$descuento->factura()->codigo}</div></a>";
            })
            ->editColumn('descuento', function (Descuento $descuento) {
                return $descuento->descuento.'%';
            })
            ->editColumn('estado', function (Descuento $descuento) {
                return "<span class='text-{$descuento->estado("true")}'><strong>{$descuento->estado()}</strong></span>";
            })
            ->editColumn('created_by', function (Descuento $descuento) {
                return $descuento->created_by()->nombres;
            })
            ->editColumn('updated_by', function (Descuento $descuento) {
                return $descuento->updated_by();
            })
            ->addColumn('acciones', $modoLectura ?  "" : "descuentos.acciones")
            ->rawColumns(['id', 'factura', 'estado', 'acciones'])
            ->toJson();
    }

    public function create(){
    }
    
    public function store(Request $request){
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $descuento = Descuento::find($id);

        if ($descuento) {
            $factura = Factura::find($descuento->factura);
            $items = ItemsFactura::where('factura',$factura->id)->get();
            view()->share(['title' => 'Descuento Nro: '.$descuento->id]);
            return view('descuentos.show')->with(compact('descuento', 'factura', 'items'));
        }
        return redirect('empresa/descuentos')->with('danger', 'DESCUENTO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
    
    public function edit($id){
    }

    public function update(Request $request, $id){
    }
    
    public function destroy($id){
    }
    
    public function aprobar(Request $request){
        $descuento = Descuento::find($request->id);
        
        if($descuento){
            if($descuento->estado == 2){
                $descuento->estado = 1;
                $descuento->updated_by = Auth::user()->id;

                $items = ItemsFactura::where('factura', $descuento->factura)->get();

                foreach($items as $item){
                    $item->desc=$descuento->descuento;
                    $item->save();
                }

                $descuento->save();

                $title = 'EL DESCUENTO HA SIDO APROBADO';
                $text  = 'Verifique que el descuento ya ha sido aplicado a la factura';
                $icon  = 'success';

            }else{
                $title = 'EL DESCUENTO YA HA SIDO APROBADO';
                $text  = '';
                $icon  = 'error';
            }

            return response()->json([
                'success' => true,
                'title'   => $title,
                'text'    => $text,
                'icon'    => $icon
            ]);
        }else{
            return response()->json([
                'success' => false,
                'title'   => 'DESCUENTO NO ENCONTRADO, INTENTE NUEVAMENTE',
                'icon'    => 'error'
            ]);
        }
    }
}
