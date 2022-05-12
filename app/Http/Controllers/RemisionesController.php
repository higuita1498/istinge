<?php

namespace App\Http\Controllers;
use App\CamposExtra;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\ItemsFactura;
use App\TipoEmpresa;
use App\TipoIdentificacion;
use Illuminate\Http\Request;
use App\Empresa; use App\Contacto;
use App\Categoria; use App\Numeracion;
use App\Funcion; use App\Vendedor;
use App\Impuesto;
use App\Model\Ingresos\Remision;
use App\Model\Ingresos\ItemsRemision;
use App\Model\Inventario\Inventario;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\ProductosBodega;
use Carbon\Carbon;  use Mail; use DB;
use Validator; use Illuminate\Validation\Rule;  use Auth;
use Barryvdh\DomPDF\Facade as PDF;
use Session;
use Config;
use App\ServidorCorreo;

class RemisionesController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'facturas', 'title' => 'Remisiones', 'icon' =>'fas fa-plus', 'subseccion' => 'remisiones']);
  }

  /**
  * Vista Principal de las remisiones
  * La consulta es tan grande para hacer funcionar las flechas, ya que hay valores qe no estan en la tabla
  */
  public function index(Request $request){
      $this->getAllPermissions(Auth::user()->id);
    $busqueda=false;
   $campos=array('', 'remisiones.id', 'nombrecliente','nombreVendedor', 'remisiones.fecha', 'remisiones.vencimiento', 'total', 'pagado', 'porpagar', 'remisiones.estatus');
    if (!$request->orderby) {
      $request->orderby=1; $request->order=1;
    }
    $orderby=$campos[$request->orderby];
    $order=$request->order==1?'DESC':'ASC';

    $facturas=Remision::join('contactos as c', 'remisiones.cliente', '=', 'c.id')
                      ->join('items_remision as if', 'remisiones.id', '=', 'if.remision')
                      ->leftjoin('vendedores as v','remisiones.vendedor','=','v.id')
                      ->select('remisiones.id', 'remisiones.nro', DB::raw('c.nombre as nombrecliente'), DB::raw('v.nombre as nombreVendedor'),'remisiones.cliente', 'remisiones.fecha', 'remisiones.vencimiento', 'remisiones.estatus',
                          DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
                          DB::raw('((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) as pagado'),
                          DB::raw('(SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) )    as porpagar'))
                      ->where('remisiones.empresa',Auth::user()->empresa);
      $appends=array('orderby'=>$request->orderby, 'order'=>$request->order);

    if ($request->name_1) {
      $busqueda=true; $appends['name_1']=$request->name_1; $facturas=$facturas->where('remisiones.nro', 'like', '%' .$request->name_1.'%');
    }
    if ($request->name_2) {
      $busqueda=true; $appends['name_2']=$request->name_2; $facturas=$facturas->where('c.nombre', 'like', '%' .$request->name_2.'%');
    }
    if ($request->name_3) {
      $busqueda=true; $appends['name_3']=$request->name_3; $facturas=$facturas->where('remisiones.fecha', date('Y-m-d', strtotime($request->name_3)));
    }
    if ($request->name_4) {
      $busqueda=true; $appends['name_4']=$request->name_4; $facturas=$facturas->where('remisiones.vencimiento', date('Y-m-d', strtotime($request->name_4)));
    }
    if ($request->name_8) {
      $busqueda=true; $appends['name_8']=$request->name_8; $facturas=$facturas->whereIn('remisiones.estatus', $request->name_8);
    }

    if ($request->name_9) {
      $busqueda=true; $appends['name_9']=$request->name_9; $facturas=$facturas->where('v.nombre', 'like','%' .$request->name_9.'%');
    }


      if ($request->name_7) {
      $busqueda=true; $appends['name_7']=$request->name_7; $appends['name_7_simb']=$request->name_7_simb; $facturas=$facturas->whereRaw(DB::raw('((Select SUM(pago) from ingresos_factura where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) '.$request->name_7_simb.' ?'), [$request->name_7]);
    }
    if ($request->name_6) {
      $busqueda=true;$appends['name_6_simb']=$request->name_6_simb;  $appends['name_6']=$request->name_6; $facturas=$facturas->whereRaw('(Select SUM(pago) from ingresos_factura where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id) '.$request->name_6_simb.' ? ', [$request->name_6]);
    }

    $facturas=$facturas->groupBy('if.remision');

    if ($request->name_5) {
      $busqueda=true; $appends['name_5']=$request->name_5; $appends['name_5_simb']=$request->name_5_simb; $facturas=$facturas->havingRaw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) '.$request->name_5_simb.' ?', [$request->name_5]);
    }
    $facturas=$facturas->OrderBy($orderby, $order)->paginate(25)->appends($appends);


 		return view('remisiones.index')->with(compact('facturas', 'request', 'busqueda'));
 	}

  /**
  * Formulario para crear un nueva remision
  * @return view
  */

  public function create(){
    $this->getAllPermissions(Auth::user()->id);
    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
    $inventario =
      Inventario::select('inventario.id','inventario.tipo_producto','inventario.producto','inventario.ref',
        DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
      ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
      ->where('empresa',Auth::user()->empresa)->where('status', 1)
      ->where('descripcion', '!=','caterpillar')
      ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
      ->orderBy('inventario.producto', 'asc')
      ->get();

    $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $clientes = Contacto::where('empresa',Auth::user()->empresa)->get();
    $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
    $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
    $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();

    $categorias=Categoria::where('empresa',Auth::user()->empresa)->orWhere('empresa', 1)->whereNull('asociado')->get();

    $dataPro = (new InventarioController)->create();
    $categorias2 = $dataPro->categorias;
    $unidades2 = $dataPro->unidades;
    $medidas2 = $dataPro->medidas;
    $impuestos2 = $dataPro->impuestos;
    $extras2 = $dataPro->extras;
    $listas2 = $dataPro->listas;
    $bodegas2 = $dataPro->bodegas;
    $identificaciones = $dataPro->identificaciones;
    $tipos_empresa = $dataPro->tipos_empresa;
    $prefijos = $dataPro->prefijos;
    $vendedores = $dataPro->vendedores;
    view()->share(['icon' =>'', 'title' => 'Nueva Remisión', 'seccion' => 'facturas', 'subseccion' => 'remisiones']);

    return view('remisiones.create')->with(compact('clientes', 'inventario', 'vendedores', 'impuestos', 'nro', 'bodegas', 'listas','categorias2', 'unidades2','medidas2', 'impuestos2', 'extras2', 'listas2', 'bodegas2', 'identificaciones','tipos_empresa', 'prefijos', 'vendedores','categorias', 'extras'));
  }

  public function create_item($item){
    $inventario =Inventario::where('id',$item)->where('empresa',Auth::user()->empresa)->first();
        if ($inventario) {
            return $this->create($inventario, false);
        }
        abort(404);
    }
    
    public function cotizacionARemision($nroR,$producto=false, $cliente=false){
        $this->getAllPermissions(Auth::user()->id);
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $cotizacion = Factura::where('empresa',Auth::user()->empresa)
            ->where('tipo',2)
            ->where('cot_nro',$nroR)->first();
        $itemsCotizacion = ItemsFactura::where('factura',$cotizacion->id)->get();
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        $inventario =
            Inventario::select('inventario.id','inventario.tipo_producto','inventario.producto','inventario.ref',
                DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
                ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
                ->where('empresa',Auth::user()->empresa)->where('status', 1)
                ->where('descripcion', '!=','caterpillar')
                ->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')
                ->get();
        $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[0,2])->get();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
        $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();

        $categorias=Categoria::where('empresa',Auth::user()->empresa)
            ->orWhere('empresa', 1)
            ->whereNull('asociado')->get();

        view()->share(['icon' =>'', 'title' => 'Nueva Remisión', 'subseccion' => 'venta']);

        //se obtiene la fecha de hoy
        $fecha = date('d-m-Y');

        //Datos necesarios para hacer funcionar la ventana modal
        $dataPro = (new InventarioController)->create();
        $medidas2 = $dataPro->medidas;
        $unidades2 = $dataPro->unidades;
        $extras2 = $dataPro->extras;
        $listas2 = $dataPro->listas;
        $bodegas2 = $dataPro->bodegas;
        $categorias=Categoria::where('empresa',Auth::user()->empresa)
            ->orWhere('empresa', 1)
            ->whereNull('asociado')->get();
        $identificaciones=TipoIdentificacion::all();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        // /Datos necesarios para hacer funcionar la ventana modal

                view()->share(['icon' =>'', 'title' => 'Nueva Remisión', 'subseccion' => 'venta']);

        return view('remisiones.cotizacion')->with(compact('clientes', 'inventario', 'numeraciones', 'nro',
            'vendedores', 'terminos', 'impuestos', 'cliente', 'bodegas', 'listas', 'producto', 'fecha', 'categorias', 'identificaciones', 'tipos_empresa', 'prefijos', 'medidas2', 'unidades2', 'extras2', 'listas2',
            'bodegas2','cotizacion','itemsCotizacion', 'extras'));
    }

  /**
  * Registrar una nueva remision
  * Si hay items inventariable resta los valores al inventario
  * @param Request $request
  * @return redirect
  */

  public function store(Request $request){

       if( Remision::where('empresa',auth()->user()->empresa)->count() > 0){
      //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', Remision::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
     return redirect('empresa/remisiones')->with('success', $mensaje);
    }
       }

    $remision = new Remision;
    $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();
    $caja=$nro->remision;
    while (true) {
      $numero=Remision::where('empresa', Auth::user()->empresa)->where('nro', $caja)->count();
      if ($numero==0) {
        break;
      }
      $caja++;
    }
    $remision->nro=$caja;
    $remision->empresa=Auth::user()->empresa;
    $remision->vendedor=$request->vendedor;
    $remision->documento=$request->documento;
    $remision->cliente=$request->cliente;
    $remision->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
    $remision->vencimiento=Carbon::parse($request->vencimiento)->format('Y-m-d');
    $remision->observaciones=$request->observaciones;
    $remision->notas=$request->notas;
    $remision->lista_precios=$request->lista_precios;
    $remision->bodega=$request->bodega;
    $remision->save();

    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
    if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
      $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
    }


    for ($i=0; $i < count($request->ref) ; $i++) {
      $producto = Inventario::where('id', $request->item[$i])->first();
      //Si el producto es inventariable y existe esa bodega, restará el valor registrado
      if ($producto->tipo_producto==1) {
        $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
        if ($ajuste) {
          $ajuste->nro-=$request->cant[$i];
          $ajuste->save();
        }
      }

      $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
      $items = new ItemsRemision;
      $items->remision=$remision->id;
      $items->producto=$request->item[$i];
      $items->ref=$request->ref[$i];
      $items->precio=$this->precision($request->precio[$i]);
      $items->descripcion=$request->descripcion[$i];
      $items->id_impuesto=$request->impuesto[$i];
      $items->impuesto=$impuesto->porcentaje;
      $items->cant=$request->cant[$i];
      $items->desc=$request->desc[$i];
      $items->save();
    }
    $nro->remision=$caja+1;
    $nro->save();

      //Creo la variable para el mensaje final, y la variable print (imprimir)
      $mensaje='Se ha creado satisfactoriamente la remisión';
      $print=false;

      //Si se selecciono imprimir, para enviarla y que se abra la ventana emergente con el pdf
      if ($request->print) {
          $print=$remision->nro;
      }

      if ($request->send) {
          //$this->enviar($factura->nro, null, false);
      }

      //Se redirecciona a la vista Nuevo ingreso, si se selecciono la opcion "Agregar Pago"
      if ($request->pago) {
          return redirect('empresa/remisiones/'.$remision->nro)->with('print', $print)->with('success', $mensaje);
      }
      //Se redirecciona a la vista Nuevo Factura, si se selecciono la opcion "Crear una nueva"
      else if ($request->new) {
          return redirect('empresa/remisiones/create')->with('success', $mensaje)->with('print', $print);
      }

    return redirect('empresa/remisiones')->with('success', $mensaje)->with('remision_id', $remision->id);
  }

  public function show($id){

      $this->getAllPermissions(Auth::user()->id);
    $remision = Remision::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($remision) {
        if($remision->documento == 1){
            view()->share(['title' => 'Remisión '.$remision->nro, 'invert'=>true, 'icon' =>'']);
        }else{
            view()->share(['title' => 'Orden de Servicio '.$remision->nro, 'invert'=>true, 'icon' =>'']);
        }


      $items = ItemsRemision::where('remision',$remision->id)->get();
      return view('remisiones.show')->with(compact('remision', 'items'));
    }
    return redirect('empresa/remisiones')->with('success', 'No existe un registro con ese id');
  }

  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);

    $remision = Remision::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();

    if ($remision) {
        view()->share(['title' => 'Modificar Remisión '.$remision->nro, 'icon' =>'']);
    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $remision->bodega)->first();

    if (!$bodega) {
      $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
    }

$categorias=Categoria::where('empresa',Auth::user()->empresa)
          ->orWhere('empresa', 1)
          ->whereNull('asociado')->get();
    $dataPro = (new InventarioController)->create();
    $categorias2 = $dataPro->categorias;
    $unidades2 = $dataPro->unidades;
    $medidas2 = $dataPro->medidas;
    $impuestos2 = $dataPro->impuestos;
    $extras2 = $dataPro->extras;
    $listas2 = $dataPro->listas;
    $bodegas2 = $dataPro->bodegas;
    $identificaciones2 = $dataPro->identificaciones;
    $tipos_empresa2 = $dataPro->tipos_empresa;
    $prefijos2 = $dataPro->prefijos;
    $vendedores2 = $dataPro->vendedores;
    
    $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();

    $inventario = Inventario::select('inventario.*', DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
        ->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();
    $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $clientes = Contacto::where('empresa',Auth::user()->empresa)->get();
    $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
    $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
    $items = ItemsRemision::where('remision',$remision->id)->get();
        view()->share(['title' => 'Modificar Remisión '.$remision->nro, 'icon' =>'']);

    return view('remisiones.edit')->with(compact('remision', 'clientes', 'inventario', 'vendedores', 'impuestos', 'items', 'bodegas', 'listas','categorias2', 'unidades2','medidas2', 'impuestos2', 'extras2',
        'listas2', 'bodegas2', 'identificaciones2','tipos_empresa2', 'prefijos2', 'vendedores2','categorias', 'extras'));
    }
    return redirect('empresa/remisiones')->with('success', 'No existe un registro con ese id');
  }
  public function update(Request $request, $id){
    $remision =Remision::find($id);
    if ($remision) {
      if ($remision->estatus==1) {
        //se devolveran todos los items al inventario
        // Asi evitar que no exista la posibilidad de error en el momento de restar los items abajo
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $remision->bodega)->first();
        $items = ItemsRemision::join('inventario as inv', 'inv.id', '=', 'items_remision.producto')->select('items_remision.*')->where('items_remision.remision',$remision->id)->where('inv.tipo_producto', 1)->get();
        foreach ($items as $item) {
          $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $item->producto)->first();
          if ($ajuste) {
            $ajuste->nro+=$item->cant;
            $ajuste->save();
          }
        }

        $remision->vendedor=$request->vendedor;
        $remision->documento=$request->documento;
        $remision->cliente=$request->cliente;
        $remision->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
        $remision->vencimiento=Carbon::parse($request->vencimiento)->format('Y-m-d');
        $remision->observaciones=$request->observaciones;
        $remision->notas=$request->notas;
        $remision->lista_precios=$request->lista_precios;
        $remision->bodega=$request->bodega;
        $remision->save();
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id', $request->bodega)->first();
        if (!$bodega) { //Si el valor seleccionado para bodega no existe, tomara la primera activa registrada
          $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
        }

        $inner=array();
        for ($i=0; $i < count($request->ref) ; $i++) {
          $cat='id_item'.($i+1);
          if($request->$cat){
            $items = ItemsRemision::where('id', $request->$cat)->first();
          }
          else{
            $items = new ItemsRemision;
          }
          $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
          $producto = Inventario::where('id', $request->item[$i])->first();

         //Si el producto es inventariable y existe esa bodega, restará el valor registrado
          if ($producto->tipo_producto==1) {
            $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $producto->id)->first();
            if ($ajuste) {
              $ajuste->nro-=$request->cant[$i];
              $ajuste->save();
            }
          }

          $items->remision=$remision->id;
          $items->producto=$request->item[$i];
          $items->ref=$request->ref[$i];
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
          DB::table('items_remision')->where('remision', $remision->id)->whereNotIn('id', $inner)->delete();
        }
        $mensaje='Se ha modificado satisfactoriamente la remisión';
        return redirect('empresa/remisiones')->with('success', $mensaje)->with('remision_id', $remision->id);


      }
      return redirect('empresa/remisiones')->with('success', 'La remisión '.$factura->nro.' ya esta cerrada');

    }
    return redirect('empresa/remisiones')->with('success', 'No existe un registro con ese id');
  }


  public function Imprimir($id){
    /**
     * toma en cuenta que para ver los mismos
     * datos debemos hacer la misma consulta
    **/

    $remision = Remision::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();

     if($remision->documento == 1){
         view()->share(['title' => 'Imprimir Remisión']);
     }else{
         view()->share(['title' => 'Imprimir Orden de Servicio']);
     }

    if ($remision) {
      $items = ItemsRemision::where('remision',$remision->id)->get();
      $itemscount=ItemsRemision::where('remision',$remision->id)->count();
      $pdf = PDF::loadView('pdf.remision', compact('items', 'remision', 'itemscount'));
        return  response ($pdf->stream())->withHeaders([
                'Content-Type' =>'application/pdf',]);
    }
  }

  public function enviar($id, $emails=null, $redireccionar=true){
    /**
     * toma en cuenta que para ver los mismos
     * datos debemos hacer la misma consulta
    **/
    view()->share(['title' => 'Enviando Remisión']);

    $remision = Remision::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($remision) {
      if (!$emails) {
        $emails=$remision->cliente()->email;
        if ($remision->cliente()->asociados('number')>0) {
          $email=$emails;
          $emails=array();
          if ($email) {$emails[]=$email;}
          foreach ($remision->cliente()->asociados() as $asociado) {
            if ($asociado->notificacion==1 && $asociado->email) {
              $emails[]=$asociado->email;
            }
          }
        }
      }
      if (!$emails || count($emails)==0) {
        return redirect('empresa/remisiones/'.$remision->nro)->with('error', 'El Cliente ni sus contactos asociados tienen correo registrado');
      }

      $items = ItemsRemision::where('remision',$remision->id)->get();
      $itemscount=ItemsRemision::where('remision',$remision->id)->count();

      $pdf = PDF::loadView('pdf.remision', compact('remision', 'items', 'retenciones', 'itemscount'))->stream();
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
      Mail::send('emails.remision', compact('remision'), function($message) use ($pdf, $emails, $remision)
      {
        $message->from(Auth::user()->empresa()->email, Auth::user()->empresa()->nombre);
        $message->to($emails)->subject('Remisión #'.$remision->nro);
        $message->attachData($pdf, 'remision.pdf', ['mime' => 'application/pdf']);
      });


    }
    if ($redireccionar) {

      return redirect('empresa/remisiones/'.$remision->id)->with('success', 'Se ha enviado el correo');
    }
  }

  public function datatable_producto(Request $request, $producto){
    // storing  request (ie, get/post) global array to a variable
    $requestData =  $request;
    $columns = array(
    // datatable column index  => database column name
        0 => 'remisiones.nro',
        1 => 'nombrecliente',
        2 => 'remisiones.fecha',
        3 => 'remisiones.vencimiento',
        4=>'remisiones.estatus',
    );
    $facturas=Remision::join('contactos as c', 'remisiones.cliente', '=', 'c.id')->select('remisiones.*', DB::raw('c.nombre as nombrecliente'))->where('remisiones.empresa',Auth::user()->empresa)->whereRaw('remisiones.id in (Select distinct(remision) from items_remision where producto='.$producto.')');

    if ($requestData->search['value']) {
      // if there is a search parameter, $requestData['search']['value'] contains search parameter
       $facturas=$facturas->where(function ($query) use ($requestData) {
          $query->where('remisiones.nro', 'like', '%'.$requestData->search['value'].'%')
          ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
        });
    }
    $totalFiltered=$totalData=$facturas->count();
    $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);


    $facturas=$facturas->get();

    $data = array();
    foreach ($facturas as $factura) {
       $nestedData = array();
        $nestedData[] = '<a href="'.route('remisiones.show',$factura->nro).'">'.$factura->nro.'</a>';
        $nestedData[] = '<a href="'.route('contactos.show',$factura->cliente).'" target="_blanck">'.$factura->nombrecliente.'</a>';
        $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
        $nestedData[] = date('d-m-Y', strtotime($factura->vencimiento));
        $nestedData[] = '<spam class="text-'.$factura->estatus(true, true).'">'.$factura->estatus().'</spam>';
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);

        $boton = '<a href="'.route('remisiones.show',$factura->nro).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a> 
              <a href="'.route('remisiones.imprimir',['id' => $factura->nro, 'name'=> 'Remision No. '.$factura->nro.'.pdf']).'" target="_black"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>';

          if($factura->estatus==1){
            $boton .= '<a  href="'.route('ingresosr.create_id', ['cliente'=>$factura->cliente()->id, 'factura'=>$factura->nro]).'" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>           
                <a href="'.route('remisiones.edit',$factura->nro).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
                <a href="#"  class="btn btn-outline-danger btn-icons" title="Anular"><i class="fas fa-minus"></i></a>';
          }



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

  public function datatable_cliente(Request $request, $cliente){
    // storing  request (ie, get/post) global array to a variable
    $requestData =  $request;
    $columns = array(
    // datatable column index  => database column name
        0 => 'remisiones.nro',
        1 => 'nombrecliente',
        2 => 'remisiones.fecha',
        3 => 'remisiones.vencimiento',
        4 => 'remisiones.estatus',
        5 => 'total',
        6 => 'pagado',
        7 => 'porpagar',
    );
    $facturas = Remision::join('contactos as c', 'remisiones.cliente', '=', 'c.id')
        ->join('items_remision as if', 'remisiones.id', '=', 'if.remision')
        ->select('remisiones.*', DB::raw('c.nombre as nombrecliente'),
            DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
            DB::raw('((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) as pagado'),
            DB::raw('(SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) -  ((Select SUM(pago) from ingresosr_remisiones where remision=remisiones.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where remision=remisiones.id)) ) as porpagar'))
        ->where('remisiones.empresa',Auth::user()->empresa)
        ->where('remisiones.cliente',$cliente);

    if ($requestData->search['value']) {
      // if there is a search parameter, $requestData['search']['value'] contains search parameter
       $facturas=$facturas->where(function ($query) use ($requestData) {
          $query->where('remisiones.nro', 'like', '%'.$requestData->search['value'].'%')
          ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
        });
    }
    $facturas=$facturas->groupBy('if.remision');
    $totalFiltered=$totalData=$facturas->count();
    $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);

    $facturas=$facturas->get();

    $data = array();
    foreach ($facturas as $factura) {
       $nestedData = array();
        $nestedData[] = '<a href="'.route('remisiones.show',$factura->id).'">'.$factura->nro.'</a>';
        $nestedData[] = '<a href="'.route('contactos.show',$factura->cliente).'" target="_blanck">'.$factura->nombrecliente.'</a>';
        $nestedData[] = date('d-m-Y', strtotime($factura->fecha));
        $nestedData[] = date('d-m-Y', strtotime($factura->vencimiento));
        $nestedData[] = '<spam class="text-'.$factura->estatus(true, true).'">'.$factura->estatus().'</spam>';
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->pagado());
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->porpagar());

        $boton = '<a href="'.route('remisiones.show',$factura->id).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a> 
              <a href="'.route('remisiones.imprimir',['id' => $factura->nro, 'name'=> 'Remision No. '.$factura->nro.'.pdf']).'" target="_black"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>';

          if($factura->estatus==1){
            $boton .= '<a  href="'.route('ingresosr.create_id', ['cliente'=>$factura->cliente()->id, 'factura'=>$factura->nro]).'" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>           
                <a href="'.route('remisiones.edit',$factura->nro).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>        ';
          }

          $boton.=' <form action="'.route('remisiones.anular',$factura->nro).'" method="POST" class="delete_form" style="display: none;" id="anular-factura'.$factura->id.'">'.csrf_field().'</form>';
          if($factura->estatus==1){
            $boton .= '<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('."'anular-factura".$factura->id."', '¿Está seguro de que desea anular la remisión?', ' ');".'"><i class="fas fa-minus"></i></button> ';
          }
          else if($factura->estatus==2){
            $boton.='<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('."'anular-factura".$factura->id."', '¿Está seguro de que desea abrir la remisión?', ' ');".'"><i class="fas fa-unlock-alt"></i></button>';
          }



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

  public function anular($id){
    $factura = Remision::where('empresa',Auth::user()->empresa)->where('nro', $id)->first();
    if ($factura) {
      if ($factura->estatus==1) {
        $factura->estatus=2;
        $factura->save();
        return back()->with('success', 'Se ha anulado la remisión')->with('remision_id', $factura->id);
      }
      else if($factura->estatus==2){
        $factura->estatus=1;
        $factura->save();
        return back()->with('success', 'Se cambiado a abierta la remisión')->with('remision_id', $factura->id);

      }

      return back()->with('success', 'La remisión no esta abierta');
    }

    return back()->with('success', 'No existe un registro con ese id');
  }

}
