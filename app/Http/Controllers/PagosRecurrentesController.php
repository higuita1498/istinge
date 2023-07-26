<?php

namespace App\Http\Controllers;

use App\Model\Gastos\GastosFactura;
use App\Model\Gastos\GastosRetenciones;
use App\Model\Inventario\ListaPrecios;
use App\Retencion;
use App\TipoEmpresa;
use App\TipoIdentificacion;
use App\Vendedor;
use Illuminate\Http\Request;
use App\Banco;
use App\Empresa; use App\Contacto;
use App\Model\Gastos\GastosRecurrentesCategoria;
use App\Model\Gastos\GastosRecurrentes;
use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosCategoria;
use App\Categoria; use App\Impuesto;
use Carbon\Carbon;  use Mail;  
use Validator; use Auth;
use bcrypt; use DB;
use Session;
use App\Funcion;
use Barryvdh\DomPDF\Facade as PDF;
use App\TiposGastos;

class PagosRecurrentesController extends Controller {

    public function __construct(){
        view()->share(['seccion' => 'gastos', 'subseccion' => 'pagosrecurrentes', 'title' => 'Pagos Recurrentes', 'icon' =>'fas fa-minus']);
    }

    public function index(){
        $this->getAllPermissions(Auth::user()->id);
        $this->comprobarpagosrecurrentes();
        $gastos = GastosRecurrentes::where('empresa',Auth::user()->empresa)->get();

        /*....................................................
        Obtener el total de lo que debe el lciente en los estados pendientes
        ......................................................*/

        $gasto_pendiente = GastosRecurrentes::where('empresa',Auth::user()->empresa)->where('estado_pago',2)->get();
        $total_pendiente = 0;

        foreach ($gasto_pendiente as $pendiente) {
            $total_pendiente = $total_pendiente + $pendiente->total()->total;
        }

        $total_pendiente = Auth::user()->empresa()->moneda . Funcion::Parsear($total_pendiente);
        /*....................................................
        Fin Obtener el total de lo que debe el lciente en los estados pendientes
        ......................................................*/
        return view('pagosrecurrentes.index')->with(compact('gastos','total_pendiente'));
 	}

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        $bancos = Banco::where('empresa',Auth::user()->empresa)->get();
        //$beneficiarios = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
        $metodos_pago =DB::table('metodos_pago')->get();
        $categorias=Categoria::where('empresa',Auth::user()->empresa)->whereNull('asociado')->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $tipos_gastos = TiposGastos::where('estado', 1)->get();

        view()->share(['icon' =>'', 'title' => 'Nuevo Pago Recurrente']);

        return view('pagosrecurrentes.create')->with(compact('tipos_gastos', 'categorias', 'impuestos', 'bancos', 'metodos_pago'));
    }

    public function comprobarpagosrecurrentes(){
        $fecha_actual = Carbon::now();
        $fecha_actual = Carbon::parse($fecha_actual)->format('Y-m-d');

        $gastos = GastosRecurrentes::where('empresa',Auth::user()->empresa)->get();

        foreach ($gastos as $gasto) {
            if ($gasto->proxima <= $fecha_actual) {
                $gasto->estado_pago = 2;
            }else if($gasto->proxima > $fecha_actual){
                $gasto->estado_pago = 1;
            }
            $gasto->save();
        }
    }

    public function store(Request $request){
        //dd($request->all());
        if( GastosRecurrentes::where('empresa',auth()->user()->empresa)->count() > 0){
            Session::put('posttimer', GastosRecurrentes::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
            $sw = 1;
            //Recorremos la sesion para obtener la fecha
            foreach (Session::get('posttimer') as $key) {
                if ($sw == 1) {
                    $ultimoingreso = $key;
                    $sw=0;
                }
            }

            //Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creaci¨®n
            $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);

            //Si el tiempo es de menos de 10 segundos mandamos al listado general
            if ($diasDiferencia <= 10) {
                $mensaje = "El formulario ya ha sido enviado.";
                return redirect('empresa/pagosrecurrentes')->with('success', $mensaje);
            }
        }

        $gasto = new GastosRecurrentes;
        if(GastosRecurrentes::where('empresa', Auth::user()->empresa)->get()->last()){
            $last = GastosRecurrentes::where('empresa', Auth::user()->empresa)->get()->last()->nro;
            $last++;
        }else{
            $last = 1;
        }

        $gasto->nro = $last;
        $gasto->empresa=Auth::user()->empresa;
        $gasto->tipo_gasto=$request->tipo_gasto;
        $gasto->cuenta=$request->cuenta;
        $gasto->metodo_pago=$request->metodo_pago;
        $gasto->notas=$request->notas;
        $gasto->observaciones=mb_strtolower($request->observaciones);
        $gasto->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
        $gasto->frecuencia = $request->frecuencia;
        //$gasto->proxima = date('Y-m-d', strtotime($gasto->fecha."+ $gasto->frecuencia month"));
        $gasto->proxima = $gasto->fecha;
        $diferencia = Carbon::now()->diffInDays($gasto->fecha);

        /*.....................................
        Validación de si es la fecha de hoy debe estar el estatus en pendiente por pagar
        ......................................*/

        $fecha_actual = Carbon::now();
        $fecha_actual = Carbon::parse($fecha_actual)->format('Y-m-d');
        if ($fecha_actual == $gasto->fecha) {
            //"es la misma fecha de hoy, por ende estatus pendiente";
            $gasto->estado_pago = 2;
            $gasto->proxima = $fecha_actual;
        }else{
            //"No es la misma fecha";
            $gasto->estado_pago = 1;
        }
        /*.....................................
        Fin Validación de si es la fecha de hoy debe estar el estatus en pendiente por pagar
        ......................................*/

        $gasto->save();

        /*........................................................
        Llenado en la tabla de Gastos recurrentes x categoria
        ..........................................................*/

        foreach ($request->categoria as $key => $value) {
            if ($request->precio_categoria[$key]) {
                $impuesto = Impuesto::where('id', $request->impuesto_categoria[$key])->first();
                if (!$impuesto) {
                    $impuesto = Impuesto::where('id', 0)->first();
                }
                $items = new GastosRecurrentesCategoria;
                $items->valor=$this->precision($request->precio_categoria[$key]);
                $items->id_impuesto=$request->impuesto_categoria[$key];
                $items->gasto_recurrente=$gasto->id;
                $items->categoria=$request->categoria[$key];
                $items->cant=$request->cant_categoria[$key];
                $items->descripcion=$request->descripcion_categoria[$key];
                $items->impuesto=$impuesto->porcentaje;
                $items->save();
            }
        }

        /*........................................................
        Fin Llenado en la tabla de Gastos recurrentes x categoria
        ..........................................................*/

        $mensaje = 'Se ha creado satisfactoriamente el pago recurrente';
        return redirect('empresa/pagosrecurrentes')->with('success', $mensaje)->with('gasto_id', $gasto->id);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $gasto = GastosRecurrentes::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        if ($gasto) {
            view()->share(['icon' =>'', 'title' => 'Pago Recurrente', 'middel'=>true]);
            $items = GastosRecurrentesCategoria::where('gasto_recurrente',$gasto->id)->get();
            return view('pagosrecurrentes.show')->with(compact('gasto', 'items'));
        }
        return redirect('empresa/pagosrecurrentes')->with('success', 'No existe un registro con ese id');
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $gasto = GastosRecurrentes::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();

        if ($gasto) {
            view()->share(['icon' =>'', 'title' => 'Modificar Pago Recurrente']);
            $items=GastosRecurrentesCategoria::where('gasto_recurrente',$gasto->id)->get();
            $bancos = Banco::where('empresa',Auth::user()->empresa)->get();
            $beneficiarios = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
            $metodos_pago =DB::table('metodos_pago')->get();
            $categorias=Categoria::where('empresa',Auth::user()->empresa)->whereNull('asociado')->get();
            $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
            $tipos_gastos = TiposGastos::where('estado', 1)->get();
            return view('pagosrecurrentes.edit')->with(compact('gasto', 'items', 'beneficiarios', 'categorias', 'impuestos', 'bancos', 'metodos_pago', 'tipos_gastos'));
        }
        return redirect('empresa/pagosrecurrentes')->with('success', 'No existe un registro con ese id');
    }

    public function update(Request $request, $id){
        $gasto = GastosRecurrentes::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($gasto) {
            $gasto->empresa=Auth::user()->empresa;
            $gasto->tipo_gasto=$request->tipo_gasto;
            $gasto->cuenta=$request->cuenta;
            $gasto->metodo_pago=$request->metodo_pago;
            $gasto->notas=$request->notas;
            $gasto->observaciones=mb_strtolower($request->observaciones);
            $gasto->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $gasto->frecuencia=$request->frecuencia;
            $gasto->proxima = $gasto->fecha;

            /*.....................................
            Validación de si es la fecha de hoy debe estar el estatus en pendiente por pagar
            ......................................*/
            $fecha_actual = Carbon::now();
            $fecha_actual = Carbon::parse($fecha_actual)->format('Y-m-d');

            if ($fecha_actual >= $gasto->fecha) {
                $gasto->estado_pago = 2;
                $gasto->proxima = $gasto->fecha;
            }else{
                $gasto->estado_pago = 1;
            }
            /*.....................................
            Fin Validación de si es la fecha de hoy debe estar el estatus en pendiente por pagar
            ......................................*/

            $gasto->save();
            $inner=array();

            foreach ($request->categoria as $key => $value) {
                if ($request->precio_categoria[$key]) {
                    $cat='id_cate'.($key+1);
                    if($request->$cat){
                        $items = GastosRecurrentesCategoria::where('id', $request->$cat)->first();
                    }else{
                        $items = new GastosRecurrentesCategoria;
                    }

                    $impuesto = Impuesto::where('id', $request->impuesto_categoria[$key])->first();
                    if (!$impuesto) {
                        $impuesto = Impuesto::where('id', 0)->first();
                    }
                    $items->valor=$request->precio_categoria[$key];
                    $items->id_impuesto=$request->impuesto_categoria[$key];
                    $items->gasto_recurrente=$gasto->id;
                    $items->categoria=$request->categoria[$key];
                    $items->cant=$request->cant_categoria[$key];
                    $items->descripcion=$request->descripcion_categoria[$key];
                    $items->impuesto=$impuesto->porcentaje;
                    $items->save();
                    $inner[]=$items->id;
                }
            }

            if (count($inner)>0) {
                DB::table('gastos_recurrentes_categoria')->where('gasto_recurrente', $gasto->id)->whereNotIn('id', $inner)->delete();
            }

            $mensaje='Se ha modificado satisfactoriamente el gasto recurrente';
            return redirect('empresa/pagosrecurrentes')->with('success', $mensaje)->with('gasto_id', $gasto->nro);
        }
        return redirect('empresa/pagosrecurrentes')->with('success', 'No existe un registro con ese id');
    }

    public function destroy($id){
        $gasto = GastosRecurrentes::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($gasto) {
            $items = GastosRecurrentesCategoria::where('gasto_recurrente',$gasto->id)->delete();
            $gasto->delete();
            $mensaje='Se ha eliminado satisfactoriamente gasto recurrente recurrente';
            return back()->with('success', $mensaje);
        }
        return redirect('empresa/pagosrecurrentes')->with('success', 'No existe un registro con ese id');
    }

    public function ingreso($gasto){
        $this->getAllPermissions(Auth::user()->id);
        $gasto    = GastosRecurrentes::findOrFail($gasto);
        view()->share(['icon' =>'', 'title' => 'Nuevo Gasto Recurrente']);

        $items=GastosRecurrentesCategoria::where('gasto_recurrente',$gasto->id)->get();
        $bancos = Banco::where('empresa',Auth::user()->empresa)->get();
        $beneficiarios = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
        $tipos_gastos = TiposGastos::where('estado', 1)->get();
        $metodos_pago =DB::table('metodos_pago')->get();
        $categorias=Categoria::where('empresa',Auth::user()->empresa)->whereNull('asociado')->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();

        $fecha_actual = Carbon::now();
        $fecha_actual = Carbon::parse($fecha_actual)->format('Y-m-d');
        return view('pagos.recurrente.create')->with(compact('gasto', 'items', 'beneficiarios', 'categorias', 'impuestos', 'bancos', 'metodos_pago', 'retenciones', 'fecha_actual', 'tipos_gastos'));
    }

    public function pagar(Request $request){
        $gastoRecurrente = GastosRecurrentes::findOrFail($request->idgasto);

        $gasto = new Gastos;
        $gasto->nro= Gastos::where('empresa',Auth::user()->empresa)->count()+1;
        $gasto->empresa=Auth::user()->empresa;
        $gasto->tipo_gasto=$request->tipo_gasto;
        $gasto->cuenta=$request->cuenta;
        $gasto->metodo_pago=$request->metodo_pago;
        $gasto->notas=$request->notas;
        $gasto->tipo=5;
        $gasto->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
        $gasto->observaciones=mb_strtolower($request->observaciones);
        $gasto->created_by = Auth::user()->id;
        $gasto->recurrente = $gastoRecurrente->id;
        $gasto->save();

        $recurrentes = GastosRecurrentesCategoria::where('gasto_recurrente', $gastoRecurrente->id)->get();

        foreach($recurrentes as $recurrente){
            $items              = new GastosCategoria;
            $items->valor       = $this->precision($recurrente->valor);
            $items->id_impuesto = $recurrente->id_impuesto;
            $items->gasto       = $gasto->id;
            $items->categoria   = $recurrente->categoria;
            $items->cant        = $recurrente->cant;
            $items->descripcion = $recurrente->descripcion;
            $items->impuesto    = $recurrente->impuesto;
            $items->save();
        }

        $meses = $gastoRecurrente->frecuencia;
        $proxima = strtotime("+$meses month", strtotime($gastoRecurrente->proxima));
        $gastoRecurrente->proxima = date('Y-m-d',$proxima);
        $gastoRecurrente->estado = 1;
        $gastoRecurrente->save();

        $this->up_transaccion(5, $gastoRecurrente->id, $gastoRecurrente->cuenta, $gastoRecurrente->beneficiario, 2, $gastoRecurrente->total()->total, $gasto->fecha, 0);
        return redirect('empresa/pagos/'.$gasto->id)->with('success', 'Pago realizado correctamente');
    }

    public function imprimir($id){
        view()->share(['title' => 'Imprimir Pagos Recurrentes']);
        $gastoR = GastosRecurrentes::select('*')->whereIn('id', function ($query) use ($id){
            $query->select('nro')
                ->from(with(new Gastos)->getTable())
                ->where('tipo', 5)
                ->where('nro', $id)
                ->where('empresa', Auth::user()->empresa);
            })->first();
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('tipo', 5)->where('nro', $id)->first();

        if ($gasto) {
            $titulo='Pago a factura de proveedor';
            $itemscount=1;
            $items = array();

            $retenciones = GastosRetenciones::where('gasto',$gasto->id)->get();
            $gasto = $gastoR;
            $pdf = PDF::loadView('pdf.pagoR', compact('gasto', 'items', 'retenciones', 'itemscount'));
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
        }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }

    public function anular($id){
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('tipo', 5)->where('nro', $id)->first();
        $gastoR = GastosRecurrentes::select('*')->whereIn('id', function ($query) use ($id){
            $query->select('nro')
            ->from(with(new Gastos)->getTable())
            ->where('tipo', 5)
            ->where('nro', $id)
            ->where('empresa', Auth::user()->empresa);
        })->first();

        if ($gasto) {
            if ($gastoR->metodo_pago==3) {
                return redirect('empresa/pagos')->with('success', 'No puede editar un pago de nota de crÃ©dito');
            }
            if ($gasto->metodo_pago==4) {
                return redirect('empresa/pagos')->with('success', 'No puede editar una transferencia');
            }

            if ($gasto->estatus==1) {
                $gasto->estatus=2;
                $gasto->save();
                $this->change_out_in(5, $gasto->nro, 1);
                $mensaje='Se ha anulado satisfactoriamente el pago';
            }else{
                $gasto->estatus=1;
                $gasto->save();
                $this->change_out_in(5, $gasto->nro, 2);
                $mensaje='Se ha abierto satisfactoriamente el pago';
            }
            return back()->with('success', $mensaje)->with('gasto_id', $gasto->id);
        }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }

    public function destroy_pago($id){
        $gasto = Gastos::where('empresa', Auth::user()->empresa)->where('tipo', 5)->where('nro', $id)->first();

        if ($gasto){
            $this->destroy_transaccion(5, $gasto->nro);
            $gasto->delete();
            $mensaje='Se ha eliminado satisfactoriamente el pago';
            return redirect('empresa/pagos')->with('success', $mensaje);
        }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }

    public function act_des($id){
        $gasto = GastosRecurrentes::find($id);

        if($gasto){
            if($gasto->estado == 0){
                $gasto->estado = 1;
                $mensaje = 'SE HA APROBADO/ACTIVADO EL PAGO RECURRENTE';
            }else{
                $gasto->estado = 0;
                $mensaje = 'SE HA DESACTIVADO EL PAGO RECURRENTE';
            }
            $gasto->save();
            return redirect('empresa/pagosrecurrentes')->with('success', $mensaje);
        }else{
            return redirect('empresa/pagosrecurrentes')->with('danger', 'PAG RECURRENTE NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
}
