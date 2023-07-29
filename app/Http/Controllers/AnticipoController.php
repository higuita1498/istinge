<?php

namespace App\Http\Controllers;

use App\ProductoServicio;
use App\Puc;
use Auth;
use Illuminate\Http\Request;

use App\Model\Inventario\Inventario; 
use App\Impuesto; 
use App\ProductoCuenta; 
use App\Anticipo; 
use App\FormaPagoMedio;


class AnticipoController extends Controller
{
    public function index(){
        view()->share(['seccion' => 'categorias', 'title' => 'Anticipos', 'icon' =>'fas fa-list-ul']);
        $this->getAllPermissions(Auth::user()->id);

        $formasPago = Anticipo::all();

        //Tomar las categorias del puc que no son transaccionables.
        $categorias = Puc::where('empresa',auth()->user()->empresa)
        ->whereRaw('length(codigo) > 6')
        ->get();

        $mediosPago = FormaPagoMedio::all();

        return view('anticipo.index',compact('formasPago','categorias','mediosPago'));
    }

    public function store(Request $request){

        $pago = new Anticipo;
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

        $forma = Anticipo::find($id);

        return response()->json([
            'forma' => $forma,
            'response' => true,
        ]);
    }

    public function update(Request $request){
        $pago = Anticipo::find($request->id);

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

        $forma = Anticipo::find($request->id);
        if($forma){
            $forma->delete();
        }
        
        return response()->json(['forma' => true]);
    }
}
