<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Banco; use App\Empresa;
use App\Contacto;
use App\Numeracion;  use App\Impuesto;
use App\Categoria; use App\Retencion;
use App\Model\Ingresos\Remision;
use App\Model\Inventario\Inventario;
use App\Model\Ingresos\ItemsFactura;
use App\Model\Ingresos\IngresosCategoria;
use App\Model\Ingresos\IngresoR;
use App\Model\Ingresos\IngresosRemision;
use Mail; use Validator; use Illuminate\Validation\Rule;  use Auth;
use bcrypt; use DB; use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Session;
use Config;
use App\ServidorCorreo;

class IngresosRController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'facturas', 'subseccion' => 'ingresosr', 'title' => 'Pagos Recibidos de Remisiones', 'icon' =>'fas fa-plus']);
  }

  public function index(Request $request){
      $this->getAllPermissions(Auth::user()->id);
    $busqueda=false;
    $campos=array('', 'ingresosr.nro', 'nombrecliente', 'detalle', 'ingresosr.fecha', 'banco', 'ingresosr.estatus', 'monto');
    if (!$request->orderby) {
      $request->orderby=1; $request->order=1;
    }
    $orderby=$campos[$request->orderby];
    $order=$request->order==1?'DESC':'ASC';
    $ingresos = IngresoR::join('contactos as c', 'c.id', '=', 'ingresosr.cliente')
    ->leftjoin('ingresosr_remisiones as ir', 'ir.ingreso', '=', 'ingresosr.id')
    ->join('bancos as b', 'b.id', '=', 'ingresosr.cuenta')
    ->select('ingresosr.*', DB::raw('group_concat(ir.remision)
       as detalle'), 'c.nombre as nombrecliente', 'b.nombre as banco',
      DB::raw('SUM(ir.pago)  as monto'))
    ->where('ingresosr.empresa',Auth::user()->empresa);

    $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);
    if ($request->name_1) {
      $busqueda=true; $appends['name_1']=$request->name_1; $ingresos=$ingresos->where('ingresosr.nro', 'like', '%' .$request->name_1.'%');
    }
    if ($request->name_2) {
      $busqueda=true; $appends['name_2']=$request->name_2; $ingresos=$ingresos->where('c.nombre', 'like', '%' .$request->name_2.'%');
    }
    if ($request->name_3) {
      $busqueda=true; $appends['name_3']=$request->name_3; $ingresos=$ingresos->where('ingresosr.fecha', date('Y-m-d', strtotime($request->name_3)));
    }
    if ($request->name_4) {
      $busqueda=true; $appends['name_4']=$request->name_4; $ingresos=$ingresos->where('ingresosr.cuenta', $request->name_4);
    }
    $ingresos =$ingresos->groupBy('ingresosr.id')->OrderBy($orderby, $order)->paginate(25)->appends($appends);

    $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
 		return view('ingresosr.index')->with(compact('ingresos', 'request', 'busqueda', 'bancos'));
 	}

  public function create($cliente=false, $remision=false){
      $this->getAllPermissions(Auth::user()->id);
     $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
    $clientes = Contacto::where('empresa',Auth::user()->empresa)->get();
    $metodos_pago =DB::table('metodos_pago')->get();
    $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
    view()->share(['icon' =>'', 'title' => 'Nuevo Ingreso de Remisión']);
    return view('ingresosr.create')->with(compact('clientes', 'cliente', 'remision', 'bancos', 'metodos_pago', 'retenciones'));
  }

  public function pendiente($cliente, $id=false){
      $this->getAllPermissions(Auth::user()->id);
    $remisiones=Remision::where('cliente', $cliente)->where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
    $total=Remision::where('cliente', $cliente)->where('empresa',Auth::user()->empresa)->where('estatus', 1)->count();
    return view('ingresosr.pendiente')->with(compact('remisiones', 'id', 'total'));
  }

  public function ingpendiente($cliente, $id=false){
      $this->getAllPermissions(Auth::user()->id);
    $remisiones=Remision::where('cliente', $cliente)->where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
    $entro=false;
    $ingreso = IngresoR::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    $items = IngresosRemision::where('ingreso',$ingreso->id)->get();
    $new=$remisiones;
    foreach ($items as $item) {
      foreach ($remisiones as $remision) {
        if ($remision->id==$item->remision) {
          $entro=true;
        }
      }
      if (!$entro) {
        $new[]=Remision::where('id', $item->remision)->first();
      }
      $entro=false;
    }

    return view('ingresosr.ingpendiente')->with(compact('remisiones', 'id', 'items', 'ingreso'));

  }

  public function store(Request $request){

      if( IngresoR::where('empresa',auth()->user()->empresa)->count() > 0){
        //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', IngresoR::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
    return redirect('empresa/ingresosr')->with('success', $mensaje);
    }
      }

    $request->validate([
        'cuenta' => 'required|numeric'
    ]);

    $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();
    $caja=$nro->cajar;
    while (true) {
      $numero=IngresoR::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
      if ($numero==0) {
        break;
      }
      $caja++;
    }
    $ingreso = new IngresoR;
    $ingreso->nro=$caja;
    $ingreso->empresa=Auth::user()->empresa;
    $ingreso->cliente=$request->cliente;
    $ingreso->cuenta=$request->cuenta;
    $ingreso->metodo_pago=$request->metodo_pago;
    $ingreso->notas=$request->notas;
    $ingreso->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
    $ingreso->observaciones=mb_strtolower($request->observaciones);
    $ingreso->save();

    foreach ($request->factura_pendiente as $key => $value) {
        if ($request->precio[$key]) {
          $precio=$this->precision($request->precio[$key]);
          $remision = Remision::find($request->factura_pendiente[$key]);
          $retencion='fact'.$remision->id.'_retencion';
          $precio_reten='fact'.$remision->id.'_precio_reten';
          $items = new IngresosRemision;
          $items->ingreso=$ingreso->id;
          $items->remision=$remision->id;
          $items->pagado=$remision->pagado();
          $items->pago=$this->precision($request->precio[$key]);
          if ($precio==$this->precision($remision->porpagar())) {
            $remision->estatus=0;
            $remision->save();
          }
          $items->save();
        }
      }

    $nro->cajar=$caja+1;
    $nro->save();
    $ingreso=IngresoR::find($ingreso->id);
    //ingresos
    $this->up_transaccion(2, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);

    $mensaje='Se ha creado satisfactoriamente el ingreso';
    return redirect('empresa/ingresosr')->with('success', $mensaje)->with('ingreso_id', $ingreso->nro);
  }

  public function show($id){
      $this->getAllPermissions(Auth::user()->id);

    $ingreso = IngresoR::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($ingreso) {
      view()->share(['icon' =>'', 'title' => 'Pago a Remisiones', 'middel'=>true]);
      $items = IngresosRemision::where('ingreso',$ingreso->id)->get();
      return view('ingresosr.show')->with(compact('ingreso', 'items'));
    }
    return redirect('master/ingresosr')->with('success', 'No existe un registro con ese id');
  }


  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $ingreso = IngresoR::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($ingreso) {
          $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
      $clientes = Contacto::where('empresa',Auth::user()->empresa)->get();
      $metodos_pago =DB::table('metodos_pago')->get();
      $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();

      view()->share(['icon' =>'', 'title' => 'Modificar Ingreso (Recibo de Caja) #'.$ingreso->nro]);
      return view('ingresosr.edit')->with(compact('ingreso', 'clientes', 'bancos', 'metodos_pago', 'retenciones'));
    }
    return redirect('master/ingresosr')->with('success', 'No existe un registro con ese id');
  }


  public function update(Request $request, $id){
    $ingreso = IngresoR::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($ingreso) {
      $request->validate([
        'cuenta' => 'required|numeric'
      ]);

      $ingreso->cliente=$request->cliente;
      $ingreso->cuenta=$request->cuenta;
      $ingreso->metodo_pago=$request->metodo_pago;
      $ingreso->notas=$request->notas;
      $ingreso->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
      $ingreso->observaciones=mb_strtolower($request->observaciones);
      $ingreso->save();
      foreach ($request->factura_pendiente as $key => $value) {
        $remision = Remision::find($request->factura_pendiente[$key]);
        $items = IngresosRemision::where('ingreso',$ingreso->id)->where('remision', $remision->id)->first();
        $porpagar=$this->precision($remision->porpagar());
        if ($request->precio[$key]) {
          if (!$items) {
            $items = new IngresosRemision;
            $items->remision=$request->factura_pendiente[$key];
            $items->pagado=$remision->pagado();
            $items->ingreso=$ingreso->id;
          }
          else{
            $porpagar+=$this->precision($items->pago);
          }
          $items->pago=$this->precision($request->precio[$key]);
          $items->save();
          $precio=$this->precision($request->precio[$key]);
          if ((float) $precio==(float) $porpagar) {
            $remision->estatus=0;
          }
          else{
            $remision->estatus=1;
          }
          $remision->save();



        }
        else{
          if($items){
            $items->delete();
            $remision->estatus=1;
            $remision->save();
          }
        }
      }

      //ingresos
      $this->up_transaccion(2, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);
      $mensaje='Se ha modificado satisfactoriamente el ingreso';
      return redirect('empresa/ingresosr')->with('success', $mensaje)->with('ingreso_id', $ingreso->nro);
    }
    return redirect('empresa/ingresosr')->with('success', 'No existe un registro con ese id');

  }


  public function Imprimir($id){
    /**
     * toma en cuenta que para ver los mismos
     * datos debemos hacer la misma consulta
    **/
    view()->share(['title' => 'Imprimir Ingreso de Remisi��n']);
    $ingreso = IngresoR::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($ingreso) {
      view()->share(['icon' =>'', 'title' => 'Pago a Facturas de Venta', 'middel'=>true]);
      $itemscount=IngresosRemision::where('ingreso',$ingreso->id)->count();
      $items = IngresosRemision::where('ingreso',$ingreso->id)->get();
      $pdf = PDF::loadView('pdf.ingresor', compact('ingreso', 'items', 'itemscount'));
      return  response ($pdf->stream())->withHeaders([
                'Content-Type' =>'application/pdf',]);
    }


  }

  public function enviar($id, $emails=null, $redireccionar=true){
    /**
     * toma en cuenta que para ver los mismos
     * datos debemos hacer la misma consulta
    **/
    view()->share(['title' => 'Enviando Recibo de Caja']);

    $ingreso = IngresoR::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();

    if ($ingreso) {

      if (!$emails) {
        $emails=$ingreso->cliente()->email;

        if ($ingreso->cliente()->asociados('number')>0) {
          $email=$emails;
          $emails=array();
          if ($email) {$emails[]=$email;}
          foreach ($ingreso->cliente()->asociados() as $asociado) {
            if ($asociado->notificacion==1 && $asociado->email) {
              $emails[]=$asociado->email;
            }
          }
        }
      }
      if (!$emails || count($emails)==0) {
        return redirect('empresa/ingresosr/'.$ingreso->nro)->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
      }

      $items = IngresosRemision::where('ingreso',$ingreso->id)->get();
      $itemscount=IngresosRemision::where('ingreso',$ingreso->id)->count();

      $pdf = PDF::loadView('pdf.ingresor', compact('ingreso', 'items', 'itemscount'))->stream();

      $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
      if($host){
        $existing = config('mail');
        $new =array_merge(
          $existing, [
            'host' => $host->servidor,
            'port' => $host->puerto,
            'encryption' => $host->seguridad,
            'username' => $host->usuario,
            'password' => $host->password,
            'from' => [
              'address' => $host->address,
              'name' => $host->name
            ],
          ]
        );
        config(['mail'=>$new]);
      }

      self::sendMail('emails.ingreso', compact('ingreso'), compact('pdf', 'emails', 'ingreso'), function($message) use ($pdf, $emails, $ingreso)
      {
        $message->from(Auth::user()->empresa()->email, Auth::user()->empresa()->nombre);
        $message->to($emails)->subject('Recibo de Caja #'.$ingreso->nro);
        $message->attachData($pdf, 'recibo.pdf', ['mime' => 'application/pdf']);
      });


    }

    if ($redireccionar) {
      return redirect('empresa/ingresosr/'.$ingreso->id)->with('success', 'Se ha enviado el correo');
    }
  }



  //Eliminar el ingreso
  public function destroy($id){
    $ingreso = IngresoR::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($ingreso) {

      if ($ingreso->estatus!=2) {
        $ids=DB::table('ingresosr_remisiones')->where('ingreso', $ingreso->id)->select('remision')->get();
        $factura=array();
        foreach ($ids as $id) {
          $factura[]=$id->remision;
        }
        DB::table('remisiones')->where('empresa',Auth::user()->empresa)->whereIn('id', $factura)->update(['estatus'=>1]);
      }
      IngresosRemision::where('ingreso', $ingreso->id)->delete();
      //ingresos
      $this->destroy_transaccion(2, $ingreso->id);

      $ingreso->delete();
      $mensaje='Se ha eliminado satisfactoriamente el ingreso';
      return back()->with('success', $mensaje);

    }
    return redirect('empresa/pagos')->with('success', 'No existe un registro con ese id');

  }

  //Anular o Convertir a abierta
  public function anular($id){
    $ingreso = IngresoR::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($ingreso) {
      if ($ingreso->estatus==1) {
        $ingreso->estatus=2;
        $mensaje='Se ha anulado satisfactoriamente el pago';
      }
      else{
        $items = IngresosRemision::where('ingreso',$ingreso->id)->get();
            foreach ($items as $item) {
              $factura= $item->remision();
              if ($factura->porpagar()<$item->pago) {
                return back()->with('error', 'El monto es mayor que lo que falta por pagar en la venta')->with('ingreso_id', $ingreso->nro);
              }
            }
        $ingreso->estatus=1;
        $mensaje='Se ha abierto satisfactoriamente el pago';
      }
      $ingreso->save();


      $items=IngresosRemision::where('ingreso',$ingreso->nro)->get();
        foreach ($items as $item)
        {
          $factura= $item->remision();
          if ($this->precision($factura->porpagar())<=0) {
            $factura->estatus=0;
          }
          else{ $factura->estatus=1; }
          $factura->save();
        }

      $this->chage_status_transaccion(2, $ingreso->id, $ingreso->estatus);
      return back()->with('success', $mensaje)->with('ingreso_id', $ingreso->nro);
    }
    return back()->with('success', 'No existe un registro con ese id');

  }


}
