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
use App\Nodo;
use App\Ping;

class PingsController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['inicio' => 'master', 'seccion' => 'inicio', 'title' => 'Pings Fallidos', 'icon' => 'fas fa-plug']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $contratos = Contrato::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        return view('pings.index')->with(compact('contratos'));
    }

    public function pings(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $pings = Ping::query()
        ->where('fecha', date('Y-m-d'))
        ->where('empresa', Auth::user()->empresa);

        return datatables()->eloquent($pings)
            ->editColumn('contrato', function (Ping $ping) {
                return "<a href=" . route('contratos.show', $ping->contrato) . " target='_blank'>".$ping->contrato."</div></a>";
            })
            ->editColumn('ip', function (Ping $ping) {
                return $ping->ip;
            })
            ->editColumn('estado', function (Ping $ping) {
                return $ping->estado;
            })
            ->editColumn('created_at', function (Ping $ping) {
                return $ping->updated_at;
            })
            ->addColumn('acciones', $modoLectura ?  "" : "pings.acciones")
            ->rawColumns(['acciones', 'contrato'])
            ->toJson();
    }

    public function create(){

    }
    
    public function store(Request $request){

    }

    public function show($id){

    }
    
    public function edit($id){

    }

    public function update(Request $request, $id){

    }
    
    public function destroy($id){

    }
    
    public function act_des($id){

    }
}
