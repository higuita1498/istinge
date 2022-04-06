<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\Impuesto; use Carbon\Carbon; 
use Validator; use Illuminate\Validation\Rule;  use Auth; 
use Session;
use App\Puc;

class ImpuestosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        view()->share(['seccion' => 'configuracion', 'title' => 'Tipos de Impuestos', 'icon' =>'']);
    }

    public function index(){
        $this->getAllPermissions(Auth::user()->id);
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->get();
 		return view('configuracion.impuestos.index')->with(compact('impuestos'));
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Tipo de Impuesto']);
        $cuentas = Puc::cuentasTransaccionables();
        return view('configuracion.impuestos.create',compact('cuentas'));
    }

    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|max:250',
            'porcentaje' => 'required|numeric',
            'tipo' => 'required|numeric'
        ]);

        $impuesto = new Impuesto;
        $impuesto->empresa=Auth::user()->empresa;
        $impuesto->nombre=$request->nombre;
        $impuesto->porcentaje=$request->porcentaje;
        $impuesto->tipo=$request->tipo;
        $impuesto->descripcion=$request->descripcion;
        $impuesto->puc_venta = $request->venta;
        $impuesto->save();

        $mensaje='Se ha creado satisfactoriamente el tipo de impuesto';
        return redirect('empresa/configuracion/impuestos')->with('success', $mensaje)->with('impuesto_id', $impuesto->id);
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $impuesto = Impuesto::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $cuentas = Puc::cuentasTransaccionables();
        if ($impuesto) {
            view()->share(['title' => 'Modificar Tipo de Impuesto']);
            return view('configuracion.impuestos.edit')->with(compact('impuesto','cuentas'));
        }
        return redirect('empresa/configuracion/impuestos')->with('success', 'No existe un registro con ese id');
    }

    public function update(Request $request, $id){
        $impuesto =Impuesto::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($impuesto) {
            $request->validate([
                'nombre' => 'required|max:250',
                'porcentaje' => 'required|numeric',
                'tipo' => 'required|numeric'
            ]);
            $impuesto->nombre=$request->nombre;
            $impuesto->porcentaje=$request->porcentaje;
            $impuesto->tipo=$request->tipo;
            $impuesto->descripcion=$request->descripcion;
            $impuesto->puc_venta = $request->venta;
            $impuesto->save();

            $mensaje='Se ha modificado satisfactoriamente el tipo de impuesto';
            return redirect('empresa/configuracion/impuestos')->with('success', $mensaje)->with('impuesto_id', $impuesto->id);
        }
        return redirect('empresa/configuracion/impuestos')->with('success', 'No existe un registro con ese id');
    }

    public function destroy($id){
        $impuesto=Impuesto::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($impuesto->usado()==0) {
            $impuesto->delete();
        }
        return redirect('empresa/configuracion/impuestos')->with('success', 'Se ha eliminado el tipo de impuesto');
    }

    public function act_desc($id){
        $impuesto = Impuesto::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($impuesto) {
            if ($impuesto->estado==1) {
                $mensaje='Se ha desactivado el tipo de impuesto';
                $impuesto->estado=0;
                $impuesto->save();
            }else{
                $mensaje='Se ha activado el tipo de impuesto';
                $impuesto->estado=1;
                $impuesto->save();
            }
            return redirect('empresa/configuracion/impuestos')->with('success', $mensaje)->with('impuesto_id', $impuesto->id);
        }
        return redirect('empresa/configuracion/impuestos')->with('success', 'No existe un registro con ese id');
    }
}
