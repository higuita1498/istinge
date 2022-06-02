<?php

namespace App\Http\Controllers;
use DB;
use Auth;
use App\Puc;
use App\Contacto;
use App\Numeracion;

use Illuminate\Http\Request;

class SaldosInicialesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        view()->share(['seccion' => 'saldosiniciales', 'title' => 'Saldos Iniciales', 'icon' =>'fas fa-list-ul']);
        $this->getAllPermissions(Auth::user()->id);
        
        $tipos = DB::table('tipo_comprobante')->get();
        $puc = Puc::where('empresa',auth()->user()->empresa)
        ->whereRaw('length(codigo) > 6')
        ->get();
        $contactos = Contacto::where('empresa',auth()->user()->empresa)->get();

        return view('saldosiniciales.create',compact('tipos','puc','contactos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $request;

        

        $empresa = Auth::user()->empresa;
        $numeracion = Numeracion::where('empresa',$empresa)->first();
        $siguienteNumero = $numeracion->contabilidad+1;


    }

    public function validateCartera(Request $request){

        if(isset($request->pucId)){ 
            $puc = Puc::find($request->pucId);

            $codigo = substr($puc->codigo, 0, 4);
            
            $grupo = Puc::where('codigo',$codigo)->first();

            if($grupo){
                if($grupo->id_grupo == 38 || $grupo->id_grupo == 39){
                    return response()->json(true);
                }else{
                    return response()->json(false);
                }
            }else{
                return response()->json(false);  
            }
            
        }else{
            return response()->json(false);
        }
    }
}
