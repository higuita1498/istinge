<?php

namespace App\Http\Controllers;

use App\Model\Gastos\FacturaProveedoresRetenciones;
use App\Model\Gastos\ItemsFacturaProv;
use App\Retencion;
use Illuminate\Http\Request;
use App\Empresa; use App\Contacto; use App\Banco;
use App\Numeracion; use App\Categoria;
use App\Model\Gastos\DevolucionesDebito; use App\Model\Gastos\NotaDedito;
use App\Model\Gastos\ItemsNotaDedito; use App\Model\Gastos\NotaDeditoFactura;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Ingresos\Factura; use App\Impuesto; use App\Model\Inventario\Inventario;
use App\Model\Inventario\Bodega; use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\ProductosBodega;
use App\Model\Ingresos\Ingreso;
use Session;
use App\Funcion;
use App\NotaRetencion;
use Auth;  use DB;  use Carbon\Carbon;  use Mail;
use Validator; use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade as PDF;
use App\NumeracionFactura;
use DOMDocument;
use App\Model\Ingresos\FacturaRetencion; use QrCode; use File;
use Config;
use App\ServidorCorreo;

class NotasdebitoController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'gastos', 'title' => 'Notas de Débito', 'icon' =>'fas fa-minus', 'subseccion' => 'debito']);
  }

  /**
  * Vista Principal de las notas de credito
  */
  public function index(){
    $this->getAllPermissions(Auth::user()->id);
    $notas = NotaDedito::where('empresa',Auth::user()->empresa)->orderBy('nro', 'ASC')->get();
    return view('notasdebito.index')->with(compact('notas'));
  }

  /**
  * Formulario para crear un nueva nota de credito
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['icon' =>'', 'title' => 'Nueva Nota de Débito', 'subseccion' => 'debito']);
    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
    $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
    $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();

    $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();
    $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
    $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
    $proveedores = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
      $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();

    return view('notasdebito.create')->with(compact('proveedores', 'inventario', 'impuestos', 'bancos', 'bodegas', 'categorias','retenciones'));
  }

  /**
  * Registrar una nueva nota de credito
  * Si hay items inventariable sumar los valores al inventario
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){

      if(NotaDedito::where('empresa',auth()->user()->empresa)->count() > 0){
        //Tomamos el tiempo en el que se crea el registro
        Session::put('posttimer', NotaDedito::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
            return redirect('empresa/notasdebito')->with('success', $mensaje);
        }
      }
      $notaOld = NotaDedito::where('empresa', Auth::user()->empresa)->get()->last();
      $notaOld = $notaOld->nro;
      $notaOld += 1;
      $notac = new NotaDedito;
      $notac->nro=$notaOld;
      $notac->empresa=Auth::user()->empresa;
      $notac->proveedor=$request->proveedor;
      $notac->codigo=$request->factura;
      $notac->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
      $notac->observaciones=mb_strtolower($request->observaciones);
      $notac->bodega=$request->bodega;
      $notac->save();

      $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
      if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
      }
      $montoFactura = 0;
      $montoRetenciones = 0;

    //for ($i=0; $i < count($request->item) ; $i++) {
      foreach($request->item as $i => $valor) {
          $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();

          $items = new ItemsNotaDedito;
          $items->nota = $notac->id;

          if (is_numeric($request->item[$i])) {
              $producto = Inventario::where('id', $request->item[$i])->first();
              $items->producto = $producto->id;
              $items->tipo_item = 1;
              //Si el producto es inventariable y existe esa bodega, agregara el valor registrado
              $ajuste = ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)
                  ->where('producto', $producto->id)
                  ->first();
              if ($ajuste) {
                  $ajuste->nro -= $request->cant[$i];
                  $ajuste->save();
              }
          } else {
              $item = explode('_', $request->item[$i])[1];
              $categorias = Categoria::where('empresa', Auth::user()->empresa)->where('id', $item)->first();
              $items->producto = $categorias->id;
              $items->tipo_item = 2;
          }

          if ($request->descuento[$i]) {
              $descuento = ($request->precio[$i] * $request->cant[$i]) * $request->descuento[$i] / 100;
              $precioItem = ($request->precio[$i] * $request->cant[$i]) - $descuento;

              $impuestoItem = ($precioItem * $impuesto['porcentaje']) / 100;
              $tmp = $precioItem + $impuestoItem;
              $montoFactura += $tmp;
          } else {
              $precioItem = $request->precio[$i] * $request->cant[$i];
              $impuestoItem = ($precioItem * $impuesto['porcentaje']) / 100;
              $montoFactura += $precioItem + $impuestoItem;
          }


          $items->precio = $this->precision($request->precio[$i]);
          $items->descripcion = $request->descripcion[$i];
          $items->id_impuesto = $request->impuesto[$i];
          $items->impuesto = $impuesto['porcentaje'];
          $items->cant = $request->cant[$i];
          $items->desc = $request->descuento[$i];
          $items->save();
          // }
      }


      if ($request->retencion) {
          foreach ($request->retencion as $key => $value) {
              if ($request->precio_reten[$key]) {
                  $retencion = Retencion::where('id', $request->retencion[$key])->first();
                  $reten = new NotaRetencion();
                  $reten->notas = $notac->id;
                  $reten->valor = $this->precision($request->precio_reten[$key]);
                  $reten->retencion = $retencion->porcentaje;
                  $reten->id_retencion = $retencion->id;
                  $reten->save();
              }
              $montoRetenciones += $request->precio_reten[$key];
          }
          $montoFactura = $this->precision($montoFactura) - $this->precision($montoRetenciones);
      }


    if ($request->factura) {

        $factura = FacturaProveedores::find($request->factura);
        $items = new NotaDeditoFactura;
        $items->nota=$notac->id;
        $items->factura=$factura->id;
        //dd($this->precision($montoFactura));
        //$items->pago =$this->precision($montoFactura) ;
        //dd($factura->porpagar());
        if($factura->pagado()){
            $items->pago = $factura->pagado();
        }else{
            $items->pago = 0;
        }

        $items->save();
        if ($this->precision($factura->porpagar())<=0) {
            $factura->estatus=0;
            $factura->save();
        }

      /*for ($i=0; $i < count($request->factura) ; $i++) {
          if ($request->monto_fact[$i]) {
            $monto=$this->precision($request->monto_fact[$i]);
            $factura = FacturaProveedores::find($request->factura[$i]);
            $items = new NotaDeditoFactura;
            $items->nota=$notac->id;
            $items->factura=$factura->id;
            $items->pago=$this->precision($request->monto_fact[$i]);
            $items->save();
            if ($this->precision($factura->porpagar())<=0) {
              $factura->estatus=0;
              $factura->save();
            }
          }
      } */
  }




   /* for ($i=0; $i < count($request->fecha_dev);  $i++) {
      if ($request->montoa_dev[$i]) {
        $items = new DevolucionesDebito;
        $items->nota=$notac->id;
        $items->empresa=Auth::user()->empresa;
        $items->fecha=Carbon::parse($request->fecha_dev[$i])->format('Y-m-d');
        $items->monto=$this->precision($request->montoa_dev[$i]);
        $items->cuenta=$request->cuentaa_dev[$i];
        $items->observaciones=$request->descripciona_dev[$i];
        $items->save();

        $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();
        $caja=$nro->caja;
        while (true) {
          $numero=Ingreso::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
          if ($numero==0) {
            break;
          }
          $caja++;
        }

        $ingreso = new Ingreso;
        $ingreso->nro=$caja;
        $ingreso->empresa=Auth::user()->empresa;
        $ingreso->cliente=$request->proveedor;
        $ingreso->cuenta=$request->cuentaa_dev[$i];
        $ingreso->metodo_pago=$request->metodo_pago;
        $ingreso->notas=$request->notas;
        $ingreso->nota_debito=$notac->id;
        $ingreso->total_debito=$this->precision($request->montoa_dev[$i]);
        $ingreso->nro_devolucion=$items->id;
        $ingreso->tipo=3;
        $ingreso->fecha=Carbon::parse($request->fecha_dev[$i])->format('Y-m-d');
        $ingreso->observaciones=$request->descripciona_dev[$i];
        $ingreso->save();
        $nro->caja=$caja+1;
        $nro->save();

        $ingreso=Ingreso::find($ingreso->id);
        //ingresos
        $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);

      }
    }    */
    $mensaje='Se ha creado satisfactoriamente la nota de débito';
    return redirect('empresa/notasdebito')->with('success', $mensaje)->with('nota_id', $notac->id);
  }

  public function show($id){
      $this->getAllPermissions(Auth::user()->id);
    $nota =NotaDedito::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
      $retencionesNotas = NotaRetencion::where('notas', $nota->id)->get();
    if ($nota) {

      view()->share(['title' => 'Nota Débito:  '.($nota->codigo?$nota->codigo:$nota->nro), 'invert'=>true, 'icon' =>'']);
      $items = ItemsNotaDedito::where('nota',$nota->id)->get();
      $facturas = NotaDeditoFactura::where('nota',$nota->id)->get();
      $DevolucionesDebito = DevolucionesDebito::where('nota',$nota->id)->get();
      return view('notasdebito.show')->with(compact('nota', 'retencionesNotas' ,'items', 'facturas', 'DevolucionesDebito'));
    }
    return redirect('empresa/notasdebito')->with('success', 'No existe un registro con ese id');
  }

  public function Imprimir($id){
    /**
     * toma en cuenta que para ver los mismos
     * datos debemos hacer la misma consulta
    **/
    view()->share(['title' => 'Imprimir Nota de Debito']);
    $nota = NotaDedito::where('empresa', Auth::user()->empresa)->where('id', $id)->first();
    if ($nota) {

      $items = ItemsNotaDedito::where('nota',$nota->id)->get();
      $itemscount = ItemsNotaDedito::where('nota',$nota->id)->count();
      $facturas = NotaDeditoFactura::where('nota',$nota->id)->get();
      $retenciones = FacturaRetencion::join('notas_factura as nf','nf.factura','=','factura_retenciones.factura')
            ->join('retenciones','retenciones.id','=','factura_retenciones.id_retencion')
            ->where('nf.nota',$nota->id)->get();
      $pdf = PDF::loadView('pdf.debito', compact('nota', 'items', 'facturas', 'itemscount','retenciones'));
        return  response($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
    }
  }

  /**
  * Formulario para modificar los datos de una  nota de debito
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $nota =NotaDedito::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($nota) {
      view()->share(['title' => 'Editar Nota de Crédito ', 'icon' =>'']);

      $facturas = NotaDeditoFactura::where('nota',$nota->id)->get();
        $retencionesNotas = NotaRetencion::where('notas', $nota->id)->get();
      $factura=array();
      foreach ($facturas as $key => $value) {
        $factura[]=$value->factura;
      }
      $facturas=FacturaProveedores::where('empresa',Auth::user()->empresa)->where('tipo',1);
      $facturas=$facturas->where(function ($query) use ($factura){
        $query->where('estatus',1)
          ->orWhereIn('id', $factura);
        });
      $facturas=$facturas->where('proveedor',  $nota->proveedor)->OrderBy('id', 'desc')->select(DB::raw('if(codigo, codigo, (CONCAT("Factura ", DATE_FORMAT(fecha_factura, "%e-%m-%Y")) ) ) as codigo'), 'id')->get();

      $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();
      $proveedores = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
      $items = ItemsNotaDedito::where('nota',$nota->id)->get();
      $facturas_reg = NotaDeditoFactura::where('nota',$nota->id)->get();
      $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $nota->bodega)->first();

        $retenciones = Retencion::where('empresa',Auth::user()->empresa)->where('modulo',1)->get();
      $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
      $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
      $bancos = Banco::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
      $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
      $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();
      $DevolucionesDebito = DevolucionesDebito::where('nota',$nota->id)->get();

      return view('notasdebito.edit')->with(compact('nota','retenciones','retencionesNotas', 'items', 'facturas', 'clientes', 'inventario', 'impuestos', 'bancos', 'bodegas', 'DevolucionesDebito', 'categorias', 'proveedores', 'facturas_reg'));
    }
    return redirect('empresa/notasdebito')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos de la nota de debito
  * @param Request $request
  * @return redirect
  */
  public function update($id, Request $request){
    $nota =NotaDedito::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
      $montoFactura = 0;
      $montoRetenciones = 0;
    if ($nota) {
      //Recoloco los items los productos en la bodega
      $items = ItemsNotaDedito::join('inventario as inv', 'inv.id', '=', 'items_notas_debito.producto')->select('items_notas_debito.*')->where('items_notas_debito.nota',$nota->id)->where('inv.tipo_producto', 1)->get();
      foreach ($items as $item) {
        $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $request->bodega)->where('producto', $item->producto)->first();

        if ($ajuste) {
          $ajuste->nro+=$item->cant;
          $ajuste->save();
        }
      }

      //Coloco el estatus de la factura en abierta
      $facturas_reg = NotaDeditoFactura::where('nota',$nota->id)->get();
      foreach ($facturas_reg as $factura) {
        $dato=$factura->factura();
        $dato->estatus=1;
        $dato->save();
      }

      //Modifico los datos de la nota
      $nota->empresa=Auth::user()->empresa;
      $nota->proveedor=$request->proveedor;
      $nota->codigo=$request->codigo;
      $nota->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
      $nota->observaciones=mb_strtolower($request->observaciones);
      $nota->bodega=$request->bodega;
      $nota->save();

      //Compruebo que existe la bodega y la uso
      $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
      if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
      }

      $inner=array();
      //Recorro los Categoría/Ítem
      for ($i=0; $i < count($request->item) ; $i++) {

        $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
        $cat='id_item'.($i+1);
        $items=array();
        if($request->$cat){ //Comprobar que exixte ese id
          $items = ItemsNotaDedito::where('id', $request->$cat)->first();
        }

        if (!$items) {
          $items = new ItemsNotaDedito;
          $items->nota=$nota->id;
        }

        //Comprobar que el nro que se guarda el item
        //si es numerico es producto
        //si no es categoria ya que llega con el prefijo cat_
        if (is_numeric($request->item[$i])) {
          $producto = Inventario::where('id', $request->item[$i])->first();
          if (!$producto) { continue; }
          $items->producto=$producto->id;
          $items->tipo_item=1;
          if ($producto->tipo_producto==1) {
            //Si el producto es inventariable y existe esa bodega, agregara el valor registrado
            $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
            if ($ajuste) {
              $ajuste->nro-=$request->cant[$i];
              $ajuste->save();
            }
          }
        }
        else{
          $item=explode('_', $request->item[$i])[1];
          $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('id',  $item)->first();
          if (!$categorias) { continue; }
          $items->producto=$categorias->id;
          $items->tipo_item=2;
        }

          /*INICIO DE CALCULOS PARA SACAR EL SALDO A FAVOR AL CONTACTO*/
          if($request->descuento[$i]){
              $descuento = ($request->precio[$i] * $request->cant[$i]) * $request->descuento[$i] / 100;
              $precioItem = ($request->precio[$i] * $request->cant[$i]) - $descuento;

              $impuestoItem = ($precioItem * $impuesto->porcentaje) / 100;
              $tmp = $precioItem + $impuestoItem;

              $montoFactura += $tmp;

          }else{
              $precioItem = $request->precio[$i] * $request->cant[$i];
              $impuestoItem = ($precioItem * $impuesto->porcentaje) / 100;
              $montoFactura += $precioItem + $impuestoItem;
          }

          if ($request->retencion) {
              foreach ($request->retencion as $key => $value) {
                  if ($request->precio_reten[$key]) {
                      $retencion = Retencion::where('id', $request->retencion[$key])->first();
                      $reten = new NotaRetencion();
                      $reten->notas = $nota->id;
                      $reten->valor = $this->precision($request->precio_reten[$key]);
                      $reten->retencion = $retencion->porcentaje;
                      $reten->id_retencion = $retencion->id;
                      $reten->save();
                  }
                  $montoRetenciones += $request->precio_reten[$key];
              }
              $montoFactura = $this->precision($montoFactura) - $this->precision($montoRetenciones);
          }

        $items->precio=$this->precision($request->precio[$i]);
        $items->descripcion=$request->descripcion[$i];
        $items->id_impuesto=$request->impuesto[$i];
        $items->impuesto=$impuesto->porcentaje;
        $items->cant=$request->cant[$i];
        $items->desc=$request->desc[$i];
        $items->save();
        $inner[]=$items->id;
      }

      if (count($inner)>0) {
        ItemsNotaDedito::where('nota', $nota->id)->whereNotIn('id', $inner)->delete();
      }

      //Pregunto si hay facturas asociadas
      if ($request->factura) {
          $factura = FacturaProveedores::find($request->factura);
          $items = new NotaDeditoFactura;
          $items->nota=$nota->id;
          $items->factura=$factura->id;
          //dd($this->precision($montoFactura));
          //$items->pago =$this->precision($montoFactura) ;
          if($this->precision($factura->porpagar()) == $montoFactura){
              $items->pago = 0;
          }else{
              $items->pago = $factura->pagado();
          }
          $items->save();
          if ($this->precision($factura->porpagar())<=0) {
              $factura->estatus=0;
              $factura->save();
          }
        /*$inner=array();
        for ($i=0; $i < count($request->factura) ; $i++) {
          if ($request->monto_fact[$i]) {
            $cat='id_facturacion'.($i+1);
            $items=array();
            if($request->$cat){ //Comprobar que exixte ese id
              $items = NotaDeditoFactura::where('id', $request->$cat)->first();
            }

            if (!$items) {
              $items = new NotaDeditoFactura;
              $items->nota=$nota->id;
            }
            $factura = FacturaProveedores::find($request->factura[$i]);
            if ($factura) {
              $inner[]=$factura->id;
              $items->factura=$factura->id;
              $items->pago=$this->precision($request->monto_fact[$i]);
              $items->save();
              if ($this->precision($factura->porpagar())<=0) {
                $factura->estatus=0;
                $factura->save();
              }
            }
          }
        }
        if (count($inner)>0) {
          NotaDeditoFactura::where('nota', $nota->id)->whereNotIn('factura', $inner)->delete();
        }*/
      }/*
      else{
        NotaDeditoFactura::where('nota', $nota->id)->delete();
      }*/



     /* $inner=array();
      //Recorro las devoluciones
      for ($i=0; $i < count($request->fecha_dev);  $i++) {
        if ($request->montoa_dev[$i]) {
          $cat='id_devolucion'.($i+1);
          $editar=false;
          $items=array();
          if($request->$cat){ //Comprobar que exixte ese id
            $items = DevolucionesDebito::where('id', $request->$cat)->first();
            $editar=true;
          }

          if (!$items) {
            $items = new DevolucionesDebito;
            $items->nota=$nota->id;
          }
          $items->empresa=Auth::user()->empresa;
          $items->fecha=Carbon::parse($request->fecha_dev[$i])->format('Y-m-d');
          $items->monto=$this->precision($request->montoa_dev[$i]);
          $items->cuenta=$request->cuentaa_dev[$i];
          $items->observaciones=$request->descripciona_dev[$i];
          $items->save();

          $inner[]=$items->id;
          if ($editar) {
            $ingreso = Ingreso::where('empresa', Auth::user()->empresa)->where('nro_devolucion', $items->id)->first();
          }
          else{
            $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();
            $caja=$nro->caja;
            while (true) {
              $numero=Ingreso::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
              if ($numero==0) {
                break;
              }
              $caja++;
            }
            $ingreso = new Ingreso;
            $ingreso->nro=$caja;
          }

          $ingreso->empresa=Auth::user()->empresa;
          $ingreso->cliente=$request->proveedor;
          $ingreso->cuenta=$request->cuentaa_dev[$i];
          $ingreso->metodo_pago=$request->metodo_pago;
          $ingreso->notas=$request->notas;
          $ingreso->nota_debito=$nota->id;
          $ingreso->total_debito=$this->precision($request->montoa_dev[$i]);
          $ingreso->nro_devolucion=$items->id;
          $ingreso->tipo=3;
          $ingreso->fecha=Carbon::parse($request->fecha_dev[$i])->format('Y-m-d');
          $ingreso->observaciones=$request->descripciona_dev[$i];
          $ingreso->save();

          $ingreso=Ingreso::find($ingreso->id);
          //ingresos
          $this->up_transaccion(1, $ingreso->id, $ingreso->cuenta, $ingreso->cliente, 1, $ingreso->pago(), $ingreso->fecha, $ingreso->descripcion);
          if (!$editar) {
            $nro->caja=$caja+1;
            $nro->save();
          }

        }
      }


      $items=DevolucionesDebito::where('nota', $nota->id);
      if (count($inner)>0) { $items=$items->whereNotIn('id', $inner);  }
      $items=$items->get();
      foreach ($items as $key => $value) {
        $ingreso = Ingreso::where('empresa', Auth::user()->empresa)->where('nro_devolucion', $value->id)->delete();
      }
      $items=DevolucionesDebito::where('nota', $nota->id);
      if (count($inner)>0) { $items=$items->whereNotIn('id', $inner);  }
      $items=$items->delete();*/

      $mensaje='Se ha modificado satisfactoriamente la nota de débito';
      return redirect('empresa/notasdebito')->with('success', $mensaje)->with('nota_id', $nota->id);
    }
  }


  public function datatable_producto(Request $request, $producto){
    // storing  request (ie, get/post) global array to a variable
    $requestData =  $request;
    $columns = array(
    // datatable column index  => database column name
        0 => 'notas_debito.nro',
        1 => 'nombrecliente',
        2 => 'notas_debito.fecha',
        3 => 'total',
        4 => 'porpagar',
        5=>'acciones'
    );
    $facturas=NotaDedito::leftjoin('contactos as c', 'notas_debito.proveedor', '=', 'c.id')->select('notas_debito.*', DB::raw('c.nombre as nombrecliente'))->where('notas_debito.empresa',Auth::user()->empresa)->whereRaw('notas_debito.id in (Select distinct(nota) from items_notas_debito where producto='.$producto.')');

    if ($requestData->search['value']) {
      // if there is a search parameter, $requestData['search']['value'] contains search parameter
       $facturas=$facturas->where(function ($query) use ($requestData) {
          $query->where('notas_debito.nro', 'like', '%'.$requestData->search['value'].'%')
          ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
        });
    }
    $totalFiltered=$totalData=$facturas->count();
    $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);
    $facturas=$facturas->get();

    $data = array();
    foreach ($facturas as $factura) {
       $nestedData = array();
        $nestedData[] = '<a href="'.route('notasdebito.show',$factura->nro).'">'.$factura->nro.'</a>';
        $nestedData[] = '<a href="'.route('contactos.show',$factura->proveedor).'" target="_blanck">'.$factura->nombrecliente.'</a>';
        $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->por_aplicar());
        $boton = '<a href="'.route('notasdebito.show',$factura->nro).'"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
          <a href="'.route('notasdebito.edit',$factura->nro).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
          <form action="'.route('notasdebito.destroy',$factura->id).'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-notasdebito'.$factura->id.'">
              '.csrf_field().'
            <input name="_method" type="hidden" value="DELETE">
          </form>
          <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('."'eliminar-notasdebito".$factura->id."', '¿Estas seguro que deseas eliminar nota de débito?', 'Se borrara de forma permanente');".'"><i class="fas fa-times"></i></button>
              ';

        $nestedData[]=$boton;
        $data[] = $nestedData;
    }
     $json_data = array(
        "draw" => intval($requestData->draw),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
        "recordsTotal" => intval($totalData),  // total number of records
        "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
        "data" => $data   // total data array
    );

     return json_encode($json_data);
  }

  public function datatable_cliente(Request $request, $contacto){// storing  request (ie, get/post) global array to a variable
    $requestData =  $request;
    $columns = array(
    // datatable column index  => database column name
        0 => 'notas_debito.nro',
        1 => 'nombrecliente',
        2 => 'notas_debito.fecha',
        3 => 'total',
        4 => 'porpagar',
        5=>'acciones'
    );
    $facturas=NotaDedito::leftjoin('contactos as c', 'notas_debito.proveedor', '=', 'c.id')->select('notas_debito.*', DB::raw('c.nombre as nombrecliente'))->where('notas_debito.empresa',Auth::user()->empresa)->where('notas_debito.proveedor', $contacto);

    if ($requestData->search['value']) {
      // if there is a search parameter, $requestData['search']['value'] contains search parameter
       $facturas=$facturas->where(function ($query) use ($requestData) {
          $query->where('notas_debito.nro', 'like', '%'.$requestData->search['value'].'%')
          ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
        });
    }
    $totalFiltered=$totalData=$facturas->count();
    $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);
    $facturas=$facturas->get();

    $data = array();
    foreach ($facturas as $factura) {
       $nestedData = array();
        $nestedData[] = '<a href="'.route('notasdebito.show',$factura->nro).'">'.$factura->nro.'</a>';
        $nestedData[] = '<a href="'.route('contactos.show',$factura->proveedor).'" target="_blanck">'.$factura->nombrecliente.'</a>';
        $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->por_aplicar());
        $boton = '<a href="'.route('notasdebito.show',$factura->nro).'"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
          <a href="'.route('notasdebito.edit',$factura->nro).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
          <form action="'.route('notasdebito.destroy',$factura->id).'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-notasdebito'.$factura->id.'">
              '.csrf_field().'
            <input name="_method" type="hidden" value="DELETE">
          </form>
          <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('."'eliminar-notasdebito".$factura->id."', '¿Estas seguro que deseas eliminar nota de débito?', 'Se borrara de forma permanente');".'"><i class="fas fa-times"></i></button>
              ';


        $nestedData[]=$boton;
        $data[] = $nestedData;
    }
     $json_data = array(
        "draw" => intval($requestData->draw),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
        "recordsTotal" => intval($totalData),  // total number of records
        "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
        "data" => $data   // total data array
    );

    return json_encode($json_data);
  }


  public function destroy($id){
    $nota = NotaDedito::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($nota) {
      //Recoloco los items los productos en la bodega
      $items = ItemsNotaDedito::join('inventario as inv', 'inv.id', '=', 'items_notas_debito.producto')->select('items_notas_debito.*')->where('items_notas_debito.nota',$nota->id)->where('inv.tipo_producto', 1)->get();
      foreach ($items as $item) {
        $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $nota->bodega)->where('producto', $item->producto)->first();
        if ($ajuste) {
          $ajuste->nro-=$item->cant;
          $ajuste->save();
        }
      }
      ItemsNotaDedito::where('nota', $nota->id)->delete();

      //Coloco el estatus de la factura en abierta
      $facturas_reg = NotaDeditoFactura::where('nota',$nota->id)->get();
      foreach ($facturas_reg as $factura) {
        $dato=$factura->factura();
        $dato->estatus=1;
        $dato->save();
      }
      NotaDeditoFactura::where('nota', $nota->id)->delete();

      $items=DevolucionesDebito::where('nota', $nota->id)->get();
      foreach ($items as $key => $value) {
        $ingreso=Ingreso::where('empresa', Auth::user()->empresa)->where('nro_devolucion', $value->id)->first();
        //ingresos
        $this->destroy_transaccion(1, $ingreso->id);
        $ingreso->delete();
      }
      DevolucionesDebito::where('nota', $nota->id)->delete();
      $nota->delete();
      $mensaje='Se ha eliminado satisfactoriamente la nota de crédito';
      return back()->with('success', $mensaje);

    }
    return redirect('empresa/notasdebito')->with('success', 'No existe un registro con ese id');

  }


    /*public function items_fact($id){

        $factura = FacturaProveedores::where('empresa',Auth::user()->empresa)->where('id', $id)->where('tipo',1)->first();
        if ($factura) {

            $items = ItemsFacturaProv::where('factura',$factura->id)->get();
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $factura->bodega)->first();
            if (!$bodega) {$bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();}
            $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
                ->where('empresa',Auth::user()->empresa)
                ->where('status', 1)
                ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
                ->get();

            $retencionesFacturas=FacturaProveedoresRetenciones::where('factura', $factura->id)->get();
            $retenciones = Retencion::where('empresa',Auth::user()->empresa)->get();
            $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
            $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();

            return view('notasdebito.items_fact')->with(compact('factura', 'items', 'inventario',  'impuestos',
                'categorias', 'retencionesFacturas', 'retenciones'
                ));
        }
    }*/

    public function items_fact($id){

        $factura = FacturaProveedores::where('empresa',Auth::user()->empresa)->where('id', $id)->first();

        //$retencionesFacturas = FacturaProveedoresRetenciones::where('factura', $factura->id)->get();
        //$retenciones = Retencion::where('empresa',Auth::user()->empresa)->get();

        if ($factura) {
            $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $factura->bodega)->first();
            if (!$bodega) {
                $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
            }
            $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
                ->where('empresa',Auth::user()->empresa)->where('status', 1)
                ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
            //

            $items = ItemsFacturaProv::select('items_factura_proveedor.*','inventario.producto as nombre','inventario.ref as refer')
                ->join('inventario','inventario.id','=','items_factura_proveedor.producto')
                ->where('factura',$factura->id)->get();
            //dd($items);
            $impuestos = Impuesto::where('empresa',Auth::user()->empresa)
                ->orWhere('empresa', null)
                ->Where('estado', 1)->get();

        }
        return json_encode($items);

    }

     public function xmlNotaDebito($id){

        $ResolucionNumeracion = NumeracionFactura::where('empresa',Auth::user()->empresa)->where('preferida',1)->first();

        $infoEmpresa = Empresa::find(Auth::user()->empresa);
        $data['Empresa'] = $infoEmpresa->toArray();

        $NotaDebito = NotaDedito::find($id);

        $retenciones = NotaRetencion::where('notas', $NotaDebito->id)->get();

        //-------------- Factura Relacionada -----------------------//
        $nroFacturaRelacionada =  NotaDeditoFactura::where('nota',$id)->first()->factura;
        $FacturaRelacionada    = FacturaProveedores::find($nroFacturaRelacionada);
        
         $impTotal = 0;
        if($FacturaRelacionada->total()->imp ){
          foreach ($FacturaRelacionada->total()->imp as $totalImp){
            if(isset($totalImp->total)){
              $impTotal = $totalImp->total;
            }else{
              $impTotal = '0.00';
            }
          }
        }
        $CufeFactRelacionada  = $FacturaRelacionada->info_cufe($nroFacturaRelacionada, $impTotal);

         //--------------Fin Factura Relacionada -----------------------//

         $impTotal = 0;

        foreach ($NotaDebito->total()->imp as $totalImp){
          if(isset($totalImp->total)){
            $impTotal = $totalImp->total;
          }
        }

        $items = ItemsNotaDedito::where('nota',$id)->get();

        $infoCude = [
      'Numfac' => $NotaDebito->nro,
      'FecFac' => Carbon::parse($NotaDebito->created_at)->format('Y-m-d'),
      'HorFac' => Carbon::parse($NotaDebito->created_at)->format('H:i:s').'-05:00',
      'ValFac' => number_format($NotaDebito->total()->subtotal - $NotaDebito->total()->descuento,2,'.',''),
      'CodImp' => '01',
      'ValImp' => number_format($impTotal,2,'.',''),
      'CodImp2'=> '04',
      'ValImp2'=> '0.00',
      'CodImp3'=> '03',
      'ValImp3'=> '0.00',
      'ValTot' => number_format($NotaDebito->total()->subtotal + $NotaDebito->impuestos_totales() - $NotaDebito->total()->descuento, 2, '.', ''),
      'NitFE'  => $data['Empresa']['nit'],
      'NumAdq' => $NotaDebito->cliente()->nit,
      'pin'    => 75315,
      'TipoAmb'=> 1,
  ];

        $CUDE = $infoCude['Numfac'].$infoCude['FecFac'].$infoCude['HorFac'].$infoCude['ValFac'].$infoCude['CodImp'].$infoCude['ValImp'].$infoCude['CodImp2'].$infoCude['ValImp2'].$infoCude['CodImp3'].$infoCude['ValImp3'].$infoCude['ValTot'].$infoCude['NitFE'].$infoCude['NumAdq'].$infoCude['pin'].$infoCude['TipoAmb'];

        $CUDEvr = hash('sha384',$CUDE);

        $infoCliente = Contacto::find($NotaDebito->proveedor);
        $data['Cliente'] = $infoCliente->toArray();

        $DocumentXML = new DOMDocument();
        $DocumentXML->preserveWhiteSpace = false;
        $DocumentXML->formatOutput = true;


        $responsabilidades_empresa = DB::table('empresa_responsabilidad as er')
      ->join('responsabilidades_facturacion as rf','rf.id','=','er.id_responsabilidad')
      ->select('rf.*')
      ->where('er.id_empresa',Auth::user()->empresa)
      ->get();

        /*return response()->view('templates.xml.92',compact('CUDEvr','ResolucionNumeracion','NotaDebito', 'data','items','retenciones','FacturaRelacionada','CufeFactRelacionada','responsabilidades_empresa'))
        ->header('Cache-Control', 'public')
        ->header('Content-Description', 'File Transfer')
        ->header('Content-Disposition', 'attachment; filename=ND-'.$NotaDebito->nro.'.xml')
        ->header('Content-Transfer-Encoding', 'binary')
        ->header('Content-Type', 'text/xml');*/

        $xml = view('templates.xml.92',compact('CUDEvr','ResolucionNumeracion','NotaDebito', 'data','items','retenciones','FacturaRelacionada','CufeFactRelacionada','responsabilidades_empresa'));

      //-- Envío de datos a la DIAN --//
  $res = $this->EnviarDatosDian($xml);

      //-- Guardamos la respuesta de la dian --//
  $NotaDebito->dian_response = $res;
  $NotaDebito->save();

      //-- Decodificación de respuesta de la DIAN --//
  $res=json_decode($res,true);

  $statusCode=$res['statusCode'];//200

  //-- Validación 1 del status code (Cuando hay un error) --//
  if ($statusCode != 200) {
      $message = $res['errorMessage'];
      $errorReason = $res['errorReason'];
      return back()->with('message_denied',$message)->with('errorReason',$errorReason);
  }


  $document=$res['document'];
    //-- estátus de que la factura ha sido aprobada --//
  if($statusCode == 200)
  {
      //$message = $res['statusMessage'];
      $message = "Nota Débito emitida correctamente";
      $NotaDebito->emitida = 1;
      $NotaDebito->fecha_expedicion = Carbon::now();
      $NotaDebito->save();

      $document=base64_decode($document);

    //-- Generación del archivo .xml mas el lugar donde se va a guardar --//
      $path = public_path() . '/xml/empresa' . auth()->user()->empresa;

      if (!File::exists($path)) {
        File::makeDirectory($path);
        $path = $path."/ND";
        File::makeDirectory($path);
    }else
    {
        $path = public_path() . '/xml/empresa' . auth()->user()->empresa . "/ND";
        if (!File::exists($path)) {
        File::makeDirectory($path);
    }
    }

    $namexml ='ND-'.$NotaDebito->nro . ".xml";
    $ruta_xmlresponse = $path."/".$namexml;
    $file = fopen($ruta_xmlresponse, "w");
    fwrite($file, $document. PHP_EOL);
    fclose($file);

      //-- Construccion del pdf a enviar con el código qr + el envío del archivo xml --//
    if ($NotaDebito) {

        $emails=$NotaDebito->cliente()->email;
        if ($NotaDebito->cliente()->asociados('number')>0) {
          $email=$emails;
          $emails=array();
          if ($email) {$emails[]=$email;}
          foreach ($NotaDebito->cliente()->asociados() as $asociado) {
            if ($asociado->notificacion==1 && $asociado->email) {
              $emails[]=$asociado->email;
          }
      }
  }


  if (!$emails || count($emails)==0) {
    return redirect('empresa/notasdebito/'.$NotaDebito->nro)->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
  }


    /*..............................
    Construcción del código qr a la factura
    ................................*/
    $impuesto = 0;
    foreach ($NotaDebito->total()->imp as $key => $imp) {
      if(isset($imp->total))
      {
        $impuesto = $imp->total;
    }
}

$codqr = "NumFac:" . $NotaDebito->codigo . "\n" .
"NitFac:"  . $data['Empresa']['nit']   . "\n" .
"DocAdq:" .  $data['Cliente']['nit'] . "\n" .
"FecFac:" . Carbon::parse($NotaDebito->created_at)->format('Y-m-d') .  "\n" .
"HoraFactura" . Carbon::parse($NotaDebito->created_at)->format('H:i:s').'-05:00' . "\n" .
"ValorFactura:" .  number_format($NotaDebito->total()->subtotal, 2, '.', '') . "\n" .
"ValorIVA:" .  number_format($impuesto, 2, '.', '') . "\n" .
"ValorOtrosImpuestos:" .  0.00 . "\n" .
"ValorTotalFactura:" .  number_format($NotaDebito->total()->subtotal + $NotaDebito->impuestos_totales(), 2, '.', '') . "\n" .
"CUDE:" . $CUDEvr;

    /*..............................
    Construcción del código qr a la factura
    ................................*/

    $itemscount= $items->count();
    $nota = $NotaDebito;
    $facturas = NotaDebitoFactura::where('nota',$nota->id)->get();
    $retenciones = FacturaRetencion::join('notas_factura as nf','nf.factura','=','factura_retenciones.factura')
            ->join('retenciones','retenciones.id','=','factura_retenciones.id_retencion')
            ->where('nf.nota',$nota->id)->get();

    $pdf = PDF::loadView('pdf.debito', compact('nota', 'items', 'facturas', 'retenciones','itemscount','CUDEvr'))->stream();

    /*..............................
    Construcción del envío de correo electrónico
    ................................*/

    $data = array(
      'email'=> 'info@gestordepartes.net',
  );
    $total = Funcion::Parsear($nota->total()->total);
    $cliente = $nota->cliente()->nombre;
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
    self::sendMail('emails.notasdebito', compact('nota','total','cliente'), function($message) use ($pdf, $emails,$ruta_xmlresponse,$nota)
    {
      $message->attachData($pdf, 'NotaCredito.pdf', ['mime' => 'application/pdf']);
      $message->attach($ruta_xmlresponse);
      $message->from('info@gestordepartes.net', Auth::user()->empresa()->nombre);
      $message->to($emails)->subject(Auth::user()->empresa()->nombre . " Nota Débito Electrónica " . $nota->nro);
  });
}
return back()->with('message_success',$message);
}
}






}
