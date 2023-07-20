<?php

namespace App\Http\Controllers;

use App\FormaPago;
use App\FormaPagoMedio;
use App\Puc;

use Illuminate\Http\Request;
use Auth;

class FormaPagoController extends Controller
{
    public function index(){
        view()->share(['seccion' => 'categorias', 'title' => 'Formas de Pago', 'icon' =>'fas fa-list-ul']);
        $this->getAllPermissions(Auth::user()->id);

        $formasPago = FormaPago::all();

        //Tomar las categorias del puc que no son transaccionables.
        $categorias = Puc::where('empresa',auth()->user()->empresa)
        ->whereRaw('length(codigo) > 6')
        ->get();

        $mediosPago = FormaPagoMedio::all();

        return view('formapago.index',compact('formasPago','categorias','mediosPago'));
    }

    public function store(Request $request){

        $pago = new Formapago;
        $pago->en_uso = $request->checkForm;
        $pago->codigo = $request->codigo;
        $pago->nombre = $request->nombre;
        $pago->relacion = $request->relacion;
        $pago->cuenta_id = $request->cuenta_id;
        $pago->medio_pago_id = $request->medio_pago_id; 
        $pago->save();


        //Formateo de relacion.
        if($request->relacion == 1){
            $pago->relacion = "Solo cartera";
        }else if($request->relacion == 2){
            $pago->relacion = "Solo proveedores";
        }else if($request->relacion == 3){
            $pago->relacion = "Cartera / Proveedores";
        }

        //Obtenemos el nombre de la categoria seleccionada.
        $pago->cuenta = Puc::where('id',$request->cuenta_id)->first()->nombre;

        //Obtenemos el medio de pago
        $pago->medioPago = FormaPagoMedio::where('id',$request->medio_pago_id)->first()->nombre;

        
        return response()->json($pago);
    }

    public function edit(Request $request){

        $id = $request->id;

        $forma = FormaPago::find($id);

        return response()->json([
            'forma' => $forma,
            'response' => true,
        ]);
    }

    public function update(Request $request){
        $pago = Formapago::find($request->id);

        if($pago){
            $pago->en_uso = $request->checkForm;
            $pago->codigo = $request->codigo;
            $pago->nombre = $request->nombre;
            $pago->relacion = $request->relacion;
            $pago->cuenta_id = $request->cuenta_id;
            $pago->medio_pago_id = $request->medio_pago_id; 
            $pago->save();
            return response()->json([
                'forma' => $pago
            ]);
        }else{
            return reposnse()->json(['forma' => false]);
        }
    }

    public function delete(Request $request){

        $forma = Formapago::find($request->id);
        if($forma){
            $forma->delete();
        }
        
        return response()->json(['forma' => true]);
    }
}
