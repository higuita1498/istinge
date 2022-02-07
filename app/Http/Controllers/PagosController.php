<?php

namespace App\Http\Controllers;

use App\Empresa;
use App\Model\Gastos\GastosRecurrentes;
use App\Model\Inventario\ListaPrecios;
use App\Movimiento;
use App\TipoEmpresa;
use App\TipoIdentificacion;
use App\Vendedor;
use Illuminate\Http\Request;
use App\Banco;
use App\Contacto;
use App\Retencion;
use App\Categoria;
use App\Impuesto;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosFactura;
use App\Model\Gastos\GastosCategoria;
use App\Model\Gastos\FacturaProveedoresRetenciones;
use App\Model\Gastos\GastosRetenciones;
use App\Model\Gastos\GastosRecurrentesCategoria;
use App\Model\Inventario\Inventario;
use Session;
use Auth; use DB; use Carbon\Carbon;
use Mail;
use Barryvdh\DomPDF\Facade as PDF;
class PagosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        view()->share(['seccion' => 'gastos', 'title' => 'Pagos / Egresos', 'icon' =>'fas fa-minus', 'subseccion' => 'pagos']);
    }
    
    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $busqueda = false;
        $gastos = Gastos::where('empresa',Auth::user()->empresa);
        
        if($request->search_code){
            $busqueda = true;
            $gastos->where('nro', $request->input('search_code'));
        }
        
        if($request->search_client){
            $busqueda = true;
            $contactos = Contacto::where('nombre','like', "$request->search_client%")->get();
            $contactosArry = array();
            foreach ($contactos as $contacto){
                $contactosArry[] = $contacto->id;
            }
            $gastos->whereIn('beneficiario', $contactosArry);
        }
        
        if($request->search_date){
            $busqueda = true;
            $gastos->where('fecha', date('Y-m-d', strtotime($request->search_date)));
        }
        
        if($request->search_status){
            $busqueda = true;
            $gastos->whereIn('estatus', $request->search_status);
        }
        
        $gastos = $gastos->OrderBy('nro', 'DESC')->paginate(25);
        return view('pagos.index')->with(compact('gastos' ,'busqueda', 'request' ));
    }
    
    public function create($cliente=false, $factura=false, $banco=false){
        $this->getAllPermissions(Auth::user()->id);
        if ($cliente && !$factura) {
            $banco=$cliente;
            $cliente=false;
        }
        
        view()->share(['icon' =>'', 'title' => 'Nuevo Gasto']);
        $bancos = Banco::where('empresa',Auth::user()->empresa)->get();
        $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
        $metodos_pago =DB::table('metodos_pago')->get();
        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->get();
        $categorias=Categoria::where('empresa',Auth::user()->empresa)->orWhere('empresa', 1)->whereNull('asociado')->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        
        //Datos necesarios para hacer funcionar la ventana modal
        $identificaciones=TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        
        return view('pagos.create')->with(compact('clientes', 'categorias', 'cliente', 'factura', 'bancos', 'metodos_pago', 'impuestos', 'retenciones', 'banco', 'identificaciones', 'vendedores', 'listas', 'tipos_empresa','prefijos'));
    }
    
    public function pendiente($proveedor, $id=false){
        $this->getAllPermissions(Auth::user()->id);
        $facturas=FacturaProveedores::where('proveedor', $proveedor)->where('empresa',Auth::user()->empresa)->where('tipo',1)->where('estatus', 1)->orderBy('created_at','asc')->get();
        $total=FacturaProveedores::where('proveedor', $proveedor)->where('empresa',Auth::user()->empresa)->where('tipo',1)->where('estatus', 1)->count();
        return view('pagos.pendiente')->with(compact('facturas', 'id', 'total'));
    }
    
    public function store(Request $request){
        if( Gastos::where('empresa',auth()->user()->empresa)->count() > 0){
            //Tomamos el tiempo en el que se crea el registro
            Session::put('posttimer', Gastos::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
            $sw = 1;
            //Recorremos la sesion para obtener la fecha
            foreach (Session::get('posttimer') as $key) {
                if ($sw == 1) {
                    $ultimoingreso = $key;
                    $sw=0;
                }
            }
            
            //Tomamos la diferencia entre la hora exacta acutal y hacemos una diferencia con la ultima creación
            $diasDiferencia = Carbon::now()->diffInseconds($ultimoingreso);
            //Si el tiempo es de menos de 30 segundos mandamos al listado general
            if ($diasDiferencia <= 10) {
                $mensaje = "El formulario ya ha sido enviado.";
                return redirect('empresa/pagos')->with('success', $mensaje);
            }
        }
        
        $request->validate([
            'cuenta' => 'required|numeric'
        ]);
        
        if (Gastos::where('empresa', Auth::user()->empresa)->orderby('created_at', 'DESC')->take(1)->first()) {
            $nroGasto = Gastos::where('empresa', Auth::user()->empresa)->orderby('created_at', 'DESC')->take(1)->first()->nro + 1;
        } else {
            $nroGasto = 1;
        }

        $gasto = new Gastos();
        $gasto->nro           = $nroGasto;
        $gasto->empresa       = Auth::user()->empresa;
        $gasto->beneficiario  = $request->beneficiario;
        $gasto->cuenta        = $request->cuenta;
        $gasto->metodo_pago   = $request->metodo_pago;
        $gasto->notas         = $request->notas;
        $gasto->tipo          = $request->tipo;
        $gasto->fecha         = Carbon::parse($request->fecha)->format('Y-m-d');
        $gasto->observaciones = mb_strtolower($request->observaciones);
        $gasto->save();
        
        //Registrar los pagos por factura
        if ($gasto->tipo==1) {
            foreach ($request->factura_pendiente as $key => $value) {
        if ($request->precio[$key]) {
          $precio=$this->precision($request->precio[$key]);
          $factura = FacturaProveedores::find($request->factura_pendiente[$key]);
          if (!$factura) { continue; }
          $retencion='fact'.$factura->id.'_retencion';
          $precio_reten='fact'.$factura->id.'_precio_reten';

          //Retenciones
          if ($request->$retencion) {
            foreach ($request->$retencion as $key2 => $value2) {
              if ($request->$precio_reten[$key2]) {
                $retencion = Retencion::where('id', $value2)->first();
                $items = new FacturaProveedoresRetenciones;
                $items->factura=$factura->id;
                $items->valor=$this->precision($request->$precio_reten[$key2]);
                $items->retencion=$retencion->porcentaje;
                $items->id_retencion=$retencion->id;
                $items->save();
              }
            }
          }
          $items = new GastosFactura;
          $items->gasto=$gasto->id;
          $items->factura=$factura->id;
          $items->pagado=$factura->pagado();
          $items->pago=$this->precision($request->precio[$key]);
          $items->save();
          
          if ($this->precision($factura->porpagar())<=0) {
            $factura->estatus=0;
            $factura->save();
          }
        }
      }
    }
    else{
      foreach ($request->categoria as $key => $value) {
        if ($request->precio_categoria[$key]) {
          $impuesto = Impuesto::where('id', $request->impuesto_categoria[$key])->first();
          if (!$impuesto) {
            $impuesto = Impuesto::where('id', 0)->first();
          }
          $items = new GastosCategoria;
          $items->valor=$this->precision($request->precio_categoria[$key]);
          $items->id_impuesto=$request->impuesto_categoria[$key];
          $items->gasto=$gasto->id;
          $items->categoria=$request->categoria[$key];
          $items->cant=$request->cant_categoria[$key];
          $items->descripcion=$request->descripcion_categoria[$key];
          $items->impuesto=$impuesto->porcentaje;
          $items->save();
        }
      }
      if ($request->retencion) {
        foreach ($request->retencion as $key => $value) {
          if ($request->precio_reten[$key]) {
            $retencion = Retencion::where('id', $request->retencion[$key])->first();
            $items = new GastosRetenciones;
            $items->gasto=$gasto->id;
            $items->valor=$this->precision($request->precio_reten[$key]);
            $items->retencion=$retencion->porcentaje;
            $items->id_retencion=$retencion->id;
            $items->save();
          }
        }
      }
    }

    $gasto=Gastos::find($gasto->id);
    //ingresos
    $this->up_transaccion(3, $gasto->id, $gasto->cuenta, $gasto->beneficiario, 2, $gasto->pago(), $gasto->fecha, $gasto->descripcion);
    $mensaje='Se ha creado satisfactoriamente el pago';
    return redirect('empresa/pagos')->with('success', $mensaje)->with('gasto_id', $gasto->id);
  }
  
    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($gasto) {
            $titulo='Pago a factura de proveedor';
            $items=GastosFactura::where('gasto',$gasto->id)->get();
            if($gasto->tipo==2 || $gasto->tipo==4){
                $titulo='Egreso';
                $items=GastosCategoria::where('gasto',$gasto->id)->get();
            }else if($gasto->tipo==3){
                $titulo=$gasto->detalle(true);
            }else if($gasto->tipo == 5){
                $titulo = 'Pago Recurrente';
            }
            view()->share(['icon' =>'', 'title' => $titulo, 'invert'=>true ]);
            return view('pagos.show')->with(compact('gasto', 'items'));
        }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }
    
    public function ingpendiente($proveedor, $id=false){
        $this->getAllPermissions(Auth::user()->id);
        $facturas=FacturaProveedores::where('proveedor', $proveedor)->where('empresa',Auth::user()->empresa)->where('tipo',1)->where('estatus', 1)->get();
        $entro=false;
        $retencioness = Retencion::where('empresa',Auth::user()->empresa)->get();
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
        $items = GastosFactura::where('gasto',$gasto->id)->get();
        $new=$facturas;
        foreach ($items as $item) {
            foreach ($facturas as $factura) {
                if ($factura->id==$item->factura) {
                    $entro=true;
                }
            }
            if (!$entro) {
                $new[]=FacturaProveedores::where('id', $item->factura)->first();
            }
            $entro=false;
        }
        return view('pagos.edit_pendiente')->with(compact('facturas', 'id', 'items', 'gasto', 'retencioness'));
    }
    
    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($gasto) {
            if ($gasto->tipo==3) {
                return redirect('empresa/pagos')->with('success', 'No puede editar un pago de nota de crédito');
            }
            
            $bancos = Banco::where('empresa',Auth::user()->empresa)->get();
            $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
            $metodos_pago =DB::table('metodos_pago')->get();
            $retenciones = Retencion::where('empresa',Auth::user()->empresa)->get();
            $categorias=Categoria::where('empresa',Auth::user()->empresa)->whereNull('asociado')->get();
            $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
            $items= $retencionesIngreso=array();
            $items = GastosFactura::where('gasto',$gasto->id)->get();
            $retencionesGasto = GastosRetenciones::where('gasto',$gasto->id)->get();
            
            if ($gasto->tipo==2) {
                $items = GastosCategoria::where('gasto',$gasto->id)->get();
            }
            return view('pagos.edit')->with(compact('clientes', 'categorias','bancos', 'metodos_pago', 'impuestos', 'retenciones', 'gasto', 'items', 'retencionesGasto'));
        }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }
    
    public function update($id, Request $request){
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($gasto) {
            if ($gasto->tipo==3) {
                return redirect('empresa/pagos')->with('success', 'No puede editar un pago de nota de credito');
            }
            $gasto->beneficiario=$request->beneficiario;
            $gasto->cuenta=$request->cuenta;
            $gasto->metodo_pago=$request->metodo_pago;
            $gasto->notas=$request->notas;
            $gasto->tipo=$request->tipo;
            $gasto->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
            $gasto->observaciones=mb_strtolower($request->observaciones);
            $gasto->save();
            
            if ($gasto->tipo!=$request->tipo) {
                if ($gasto->tipo==1) {
                    GastosFactura::where('gasto', $gasto->id)->delete();
                }else{
                    GastosCategoria::where('gasto', $gasto->id)->delete();
                }
            }
            
            //Registrar los pagos por factura
            if ($gasto->tipo==1) {
                $inner=array();
                foreach ($request->factura_pendiente as $key => $value) {
                    if ($request->precio[$key]) {
                        $precio=$this->precision($request->precio[$key]);
                        $factura = FacturaProveedores::find($request->factura_pendiente[$key]);
                        if (!$factura) { continue; }   //Si la factura no existe paso al siguiente registro
                        $items = GastosFactura::where('gasto',$gasto->id)->where('factura', $factura->id)->first();
                        if (!$items) { //Si no existe el registro de gasto para esa factura creo uno
                        $items = new GastosFactura;
                        $items->gasto=$gasto->id;
                        $items->factura=$request->factura_pendiente[$key];
                        $items->pagado=$factura->pagado();
                        $items->gasto=$gasto->id;
                    }
                    $items->pagado=$factura->pagado();
                    $items->pago=$this->precision($request->precio[$key]);
                    $items->save();
                    $inner[]=$items->id; //agrego al array
                    $retencion='fact'.$factura->id.'_retencion';
                    $precio_reten='fact'.$factura->id.'_precio_reten';
                    //Retenciones
                    if ($request->$retencion) {
                        $inner_retencion=array();
                        foreach ($request->$retencion as $key2 => $value2) {
                            if ($request->$precio_reten[$key2]) {
                                $retencion = Retencion::where('id', $value2)->first();
                                $items = new GastosRetenciones;
                                $items->factura=$factura->id;
                                $items->gasto=$gasto->id;
                                
                                $cat='fact'.$factura->id."_".($key2+1);
                                if($request->$cat){ //Consultar que exista el id de ese item
                                    $item = GastosRetenciones::where('id', $request->$cat)->where('factura', $factura->id)->where('gasto', $gasto->id)->first();
                                    if ($item) { $items = $item; }
                                }
                                $items->valor=$this->precision($request->$precio_reten[$key2]);
                                $precio+=$this->precision($request->$precio_reten[$key2]);
                                $items->retencion=$retencion->porcentaje;
                                $items->id_retencion=$retencion->id;
                                $items->save();
                                $inner_retencion[]=$items->id;
                            }
                        }
                        if (count($inner_retencion)>0) { //Borro las retenciones no modificadas
                            DB::table('gastos_retenciones')->where('gasto', $gasto->id)->where('factura', $factura->id)->whereNotIn('id', $inner_retencion)->delete();
                        }
                    }else{//Borro las retenciones en general
                        DB::table('gastos_retenciones')->where('factura', $factura->id)->where('gasto', $gasto->id)->delete();
                    }
                    
                    //Cambiar el estatus factura
                    if ($this->precision($factura->porpagar())<=0) {
                        $factura->estatus=0;
                    }else{ $factura->estatus=1; }
                    $factura->save();
                }
            }
            if (count($inner)>0) {
                DB::table('gastos_factura')->where('gasto', $gasto->id)->whereNotIn('id', $inner)->delete();
            }
        }else{
            $inner=array();
        foreach ($request->categoria as $key => $value) {
          if ($request->precio_categoria[$key]) {
            $impuesto = Impuesto::where('id', $request->impuesto_categoria[$key])->first();
            if (!$impuesto) { $impuesto = Impuesto::where('id', 0)->first(); }
             $cat='id_cate'.($key+1);

            $items = new GastosCategoria;
            $items->gasto=$gasto->id;
            if($request->$cat){ //Consultar que exista el id de ese item
              $item = GastosCategoria::where('id', $request->$cat)->where('gasto', $gasto->id)->first();
              if ($item) { $items = $item; }
            }
            $items->valor=$this->precision($request->precio_categoria[$key]);
            $items->id_impuesto=$request->impuesto_categoria[$key];
            $items->categoria=$request->categoria[$key];
            $items->cant=$request->cant_categoria[$key];
            $items->descripcion=$request->descripcion_categoria[$key];
            $items->impuesto=$impuesto->porcentaje;
            $items->save();
            $inner[]=$items->id;
          }
        }
        //Eliminar los items que no se hayan modificado
        if (count($inner)>0) {
          DB::table('gastos_categoria')->where('gasto', $gasto->id)->whereNotIn('id', $inner)->delete();
        }

        //Registro de retenciones
        if ($request->retencion) {
          $inner=array();
          foreach ($request->retencion as $key => $value) {
            if ($request->precio_reten[$key]) {
              $retencion = Retencion::where('id', $request->retencion[$key])->first();
              $items = new GastosRetenciones;
              $items->gasto=$gasto->id;

              $cat='reten'.($key+1);
              if($request->$cat){ //Consultar que exista el id de ese item
                $item = GastosRetenciones::where('id', $request->$cat)->where('gasto', $gasto->id)->first();
                if ($item) { $items = $item; }
              }
              $items->valor=$this->precision($request->precio_reten[$key]);
              $items->retencion=$retencion->porcentaje;
              $items->id_retencion=$retencion->id;
              $items->save();
              $inner[]=$items->id;
            }
          }
          if (count($inner)>0) {
            DB::table('gastos_retenciones')->where('gasto', $gasto->id)->whereNotIn('id', $inner)->delete();
          }
        }
        else {
          DB::table('gastos_retenciones')->where('gasto', $gasto->id)->delete();
        }
      }

      $this->up_transaccion(3, $gasto->id, $gasto->cuenta, $gasto->beneficiario, 2, $gasto->pago(), $gasto->fecha, $gasto->descripcion);
      $mensaje='Se ha modificado satisfactoriamente el pago';
      return redirect('empresa/pagos')->with('success', $mensaje)->with('gasto_id', $gasto->id);

    }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }
    
    public function imprimir($id){
        view()->share(['title' => 'Imprimir Pagos']);
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($gasto) {
            $titulo='Pago a factura de proveedor';
            $items=GastosFactura::where('gasto',$gasto->id)->get();
            $itemscount=1;
            $items = Gastos::where('empresa',Auth::user()->empresa)->where('nro', $id)->get();
            if ($gasto->tipo==1) {
                $itemscount=GastosFactura::where('gasto',$gasto->id)->count();
                $items = GastosFactura::where('gasto',$gasto->id)->get();
            }else if ($gasto->tipo==2){
                $itemscount=GastosCategoria::where('gasto',$gasto->id)->count();
                $items = GastosCategoria::where('gasto',$gasto->id)->get();
            }
            
            $retenciones = GastosRetenciones::where('gasto',$gasto->id)->get();
            $pdf = PDF::loadView('pdf.pago', compact('gasto', 'items', 'retenciones', 'itemscount'));
            return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
        }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }
    
    public function enviar($id){
        view()->share(['title' => 'Enviar Pagos']);
        $emails=array();
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($gasto) {
            if (!$emails) {
                $emails[]=$gasto->beneficiario()->email;
                if ($gasto->beneficiario()->asociados('number')>0) {
                    foreach ($gasto->beneficiario()->asociados() as $asociado) {
                        if ($asociado->notificacion==1 && $asociado->email) {
                            $emails[]=$asociado->email;
                        }
                    }
                }
            }
            if (!$emails || count($emails)==0) {
                return redirect('empresa/pagos/'.$gasto->id)->with('error', 'El Beneficiario ni sus contactos asociados tienen correo registrado');
            }
            
            $titulo='Pago a factura de proveedor';
            $items=GastosFactura::where('gasto',$gasto->id)->get();
            $itemscount=1;
            $items = Gastos::where('empresa',Auth::user()->empresa)->where('nro', $id)->get();
            if ($gasto->tipo==1) {
                $itemscount=GastosFactura::where('gasto',$gasto->id)->count();
                $items = GastosFactura::where('gasto',$gasto->id)->get();
            }else if ($gasto->tipo==2){
                $itemscount=GastosCategoria::where('gasto',$gasto->id)->count();
                $items = GastosCategoria::where('gasto',$gasto->id)->get();
            }
            
            $retenciones = GastosRetenciones::where('gasto',$gasto->id)->get();
            $pdf = PDF::loadView('pdf.pago', compact('gasto', 'items', 'retenciones', 'itemscount'))->stream();
            Mail::send('emails.pago', compact('gasto'), function($message) use ($pdf, $emails, $gasto){
                $message->from(Auth::user()->empresa()->email, Auth::user()->empresa()->nombre);
                $message->to($emails)->subject('Envío de comprobante de egreso #'.$gasto->nro);
                $message->attachData($pdf, 'gasto.pdf', ['mime' => 'application/pdf']);
            });
            return redirect('empresa/pagos/'.$gasto->id)->with('success', 'Se ha enviado el correo');
        }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }
    
    public function anular($id){
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($gasto) {
            if ($gasto->tipo==3) {
                return redirect('empresa/pagos')->with('success', 'No puede editar un pago de nota de crédito');
            }
            if ($gasto->tipo==4) {
                return redirect('empresa/pagos')->with('success', 'No puede editar una transferencia');
            }
            
            if ($gasto->estatus==1) {
                $gasto->estatus=2;
                $gasto->save();
                $pagos = GastosFactura::where('gasto', $gasto->id)->get();
                foreach ($pagos as $pago){
                    $factura = FacturaProveedores::find($pago->factura);
                    $factura->estatus = 1;
                    $factura->save();
                }
                $this->change_out_in(3, $gasto->id, 1);
                $mensaje='Se ha anulado satisfactoriamente el pago';
            }else{
                $gasto->estatus =1;
                $gasto->save();
                $items=GastosFactura::where('gasto',$gasto->id)->get();
                foreach ($items as $factura) {
                    $tmp = $factura->factura();
                    if ($this->precision($tmp->porpagar())<=0) {
                        $tmp->estatus=0;
                    }else{ $factura->estatus=1; }
                    $tmp->save();
                }
                $this->change_out_in(3, $gasto->id, 2);
                $mensaje='Se ha abierto satisfactoriamente el pago';
            }
            
            if ($gasto->tipo==2) {
                $items=GastosFactura::where('gasto',$gasto->id)->get();
                foreach ($items as $factura) {
                    if ($this->precision($factura->porpagar())<=0) {
                        $factura->estatus=0;
                    }else{ $factura->estatus=1; }
                    $factura->save();
                }
            }
            return back()->with('success', $mensaje)->with('gasto_id', $gasto->id);
        }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }
    
    public function destroy($id){
        $gasto = Gastos::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($gasto) {
            if ($gasto->tipo==3) {
                return redirect('empresa/pagos')->with('success', 'No puede editar un pago de nota de crédito');
            }
            if ($gasto->tipo==1) {
                $tmpGasto = GastosFactura::where('gasto', $gasto->id)->first();
                $this->cambiarStatus($tmpGasto->factura);
                $tmpGasto->delete();
                $this->destroy_transaccion(3, $gasto->id);
            }else if ($gasto->tipo==2){
                $item = GastosCategoria::where('gasto', $gasto->id)->first();
                $item->delete();
                $this->destroy_transaccion(3, $gasto->id);
            }else if($gasto->tipo==4){
                GastosCategoria::where('gasto', $gasto->id)->delete();
                $mov1=Movimiento::where('modulo', 3)->where('id_modulo', $gasto->id)->first();
                if ($mov1) {
                    $ingreso=Ingreso::where('id', $mov1->id_modulo)->first();
                    if ($ingreso) {
                        IngresosCategoria::where('ingreso', $ingreso->id)->delete();
                        $ingreso->delete();
                    }
                    Movimiento::where('transferencia', $mov1->id)->delete();
                    $mov1->delete();
                }
            }else if($gasto->tipo == 5){
                $movimiento = Movimiento::where('empresa', Auth()->user()->empresa)->where('id_modulo',$gasto->nro)->first();
                $movimiento->delete();
            }
            
            DB::table('gastos_retenciones')->where('gasto', $gasto->id)->delete();
            $gasto->delete();
            $mensaje='Se ha eliminado satisfactoriamente el pago';
            return back()->with('success', $mensaje);
        }
        return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');
    }
    
    private function cambiarStatus($id){
        $facturap = FacturaProveedores::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $facturap->estatus = 1;
        $facturap->save();
    }
}
