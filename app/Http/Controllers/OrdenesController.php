<?php

namespace App\Http\Controllers;
use App\CamposExtra;
use App\Model\Inventario\ListaPrecios;
use App\TipoEmpresa;
use App\TipoIdentificacion;
use App\Vendedor;
use Illuminate\Http\Request;
use App\Empresa; use App\Contacto; 
use App\Model\Inventario\Bodega;
use App\Model\Inventario\ProductosBodega;
use App\Model\Inventario\Inventario;
use App\Model\Gastos\Ordenes_Compra;
use App\Model\Gastos\ItemsFacturaProv;
use App\Impuesto;
use App\Categoria;  
use App\Numeracion; 
use App\Funcion; 
use Session;


use Validator; use Illuminate\Validation\Rule; use Auth;
use Carbon\Carbon; use Mail; use DB;
use Barryvdh\DomPDF\Facade as PDF;
use Config;
use App\ServidorCorreo;

class OrdenesController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'gastos', 'title' => 'Órdenes de Compra', 'icon' =>'fas fa-minus', 'subseccion' => 'ordenes']);
  }

  /**
  * Vista Principal de las ordenes de compra
  */
  public function index(Request $request){
      $this->getAllPermissions(Auth::user()->id);
      $request->busqueda = 0;
    $campos=array('', 'factura_proveedores.id', 'nombrecliente', 'factura_proveedores.fecha','factura_proveedores.vencimiento',  'factura_proveedores.estatus', 'total');
    if (!$request->orderby) {
      $request->orderby=1; $request->order=1;
    }
    $orderby=$campos[$request->orderby];
    $order=$request->order==1?'DESC':'ASC';
    $ordenes=Ordenes_Compra::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
    ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
     ->select('factura_proveedores.id', 'factura_proveedores.tipo',  'factura_proveedores.codigo', 'factura_proveedores.orden_nro', DB::raw('c.nombre as nombrecliente'), 'factura_proveedores.proveedor', 'factura_proveedores.fecha', 'factura_proveedores.vencimiento', 'factura_proveedores.estatus',
      DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'),
    DB::raw('((Select SUM(pago) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id)) as pagado'),
    DB::raw('SUM(
      (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant)-((Select if(SUM(pago), SUM(pago), 0) from ingresos_factura where factura=factura_proveedores.id) + (Select if(SUM(valor), SUM(valor), 0) from ingresos_retenciones where factura=factura_proveedores.id))  as porpagar'))
     ->where('factura_proveedores.empresa',Auth::user()->empresa)
        ->where('factura_proveedores.tipo',2)
        ->where(function($query) use ($request){

            if($request->search_code){
                $request->busqueda = 1;
                $query->where('orden_nro', $request->search_code);
            }

            if($request->search_client){
                $request->busqueda = 1;
                $contactos = Contacto::where('nombre','like', "$request->search_client%")->whereIn('tipo_contacto', [1,2])->get();
                $contactosArry = array();
                foreach ($contactos as $contacto){
                    $contactosArry[] = $contacto->id;
                }
                $query->whereIn('proveedor', $contactosArry);
            }

            if($request->search_date){
                $request->busqueda = 1;
                $query->where('fecha', date('Y-m-d', strtotime($request->search_date)));
            }

            if($request->search_status){
                $request->busqueda = 1;
                $query->whereIn('estatus', $request->search_status);
            }
        })
        ->WhereNotNull('factura_proveedores.orden_nro')->groupBy('if.factura')->OrderBy($orderby, $order)->paginate(25)->appends(['orderby'=>$request->orderby, 'order'=>$request->order, 'search_code' => $request->search_code, 'search_date' => $request->search_date, 'search_client' => $request->search_client, 'busqueda' => $request->busqueda]);

 		return view('ordenes.index')->with(compact('ordenes', 'request'));
 	}

  /**
  * Formulario para crear un nueva orden de compra
  * @return view
  */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
     view()->share(['icon' =>'', 'title' => 'Nueva Orden de Compra', 'subseccion' => 'ordenes']); 
    $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();
    $inventario = Inventario::select('inventario.*',
    DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
    ->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();   
    $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
    $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();    
    $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();
    $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
      $vendedores =  Vendedor::where('empresa',Auth::user()->empresa)->where('estado',1)->get();
      $identificaciones=  TipoIdentificacion::all();
      $tipos_empresa=  TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
      $prefijos=DB::table('prefijos_telefonicos')->get();


      $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id','>',1)->get();
      $medidas=DB::table('medidas')->get();
      $unidades=DB::table('unidades_medida')->get();
      $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();

      $identificaciones=TipoIdentificacion::all();
      $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
      $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
      $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
      $prefijos=DB::table('prefijos_telefonicos')->get();


    $dataPro = (new InventarioController)->create();
    $medidas2 = $dataPro->medidas;
    $unidades2 = $dataPro->unidades;
    $extras2 = $dataPro->extras;
    $listas2 = $dataPro->listas;
    $bodegas2 = $dataPro->bodegas;
    view()->share(['icon' =>'', 'title' => 'Nueva Orden de Compra', 'subseccion' => 'ordenes']);
    return view('ordenes.create')->with(compact('inventario', 'bodegas', 'clientes', 'impuestos', 'categorias','listas',
        'prefijos', 'medidas', 'unidades', 'extras', 'listas','tipos_empresa','identificaciones','vendedores'));
  }

  /**
  * Registrar una orden de compra
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
      
      if( Ordenes_Compra::where('empresa',auth()->user()->empresa)->count() > 0){
      //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', Ordenes_Compra::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
     return redirect('empresa/ordenes')->with('success', $mensaje);
    }
      }
      
    $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();
    $caja=$nro->orden;
    while (true) {
      $numero=Ordenes_Compra::where('empresa', Auth::user()->empresa)->where('orden_nro', $caja)->count();
      if ($numero==0) {
        break;
      }
      $caja++;
    }
    $orden = new Ordenes_Compra;
    $orden->proveedor =$request->proveedor;
    $orden->tipo =2;
    $orden->comprador = $request->comprador;
    $orden->orden_nro=$caja;
    $orden->empresa=Auth::user()->empresa;
    $orden->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
    $orden->vencimiento=Carbon::parse($request->vencimiento)->format('Y-m-d');
    $orden->observaciones=mb_strtolower($request->observaciones);
    $orden->notas=mb_strtolower($request->notas);
    $orden->term_cond=mb_strtolower($request->term_cond);
    $orden->bodega=$request->bodega;  
    $orden->save();

     //Ciclo para registrar los itemas de la factura
    for ($i=0; $i < count($request->item) ; $i++) { 
      $items = new ItemsFacturaProv;
      $items->factura=$orden->id;
      if (is_numeric($request->item[$i])) {
        $producto = Inventario::where('id', $request->item[$i])->first();
        $items->producto=$producto->id;
        $items->tipo_item=1;
      }
      else{ 
        $item=explode('_', $request->item[$i])[1];
        $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('id',  $item)->first();
        $items->producto=$categorias->id;
        $items->tipo_item=2;
      }
      $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();
      if ($impuesto) {
        $items->id_impuesto=$request->impuesto[$i];
        $items->impuesto=$impuesto->porcentaje;
      }      
      $items->ref=$request->ref[$i];
      $items->precio=$this->precision($request->precio[$i]); 
      $items->descripcion=$request->descripcion[$i];
      $items->cant=$request->cant[$i];
      $items->desc=$request->desc[$i];
      $items->save();
    }
    $nro->orden=$caja+1;
    $nro->save();

    $mensaje='Se ha creado satisfactoriamente la cotización';

    return redirect('empresa/ordenes')->with('success', $mensaje)->with('codigo', $orden->id);
  }


  /**
  * Ver los datos de una orden de compra
  * @param int $id
  * @return view
  */
  public function show($id){
      $this->getAllPermissions(Auth::user()->id);
    $orden = Ordenes_Compra::where('empresa',Auth::user()->empresa)->where('orden_nro', $id)->first();
    if ($orden) {        
      view()->share(['title' => 'Orden de Compra: '.$orden->orden_nro, 'invert'=>true, 'icon' =>'']);
      $items = ItemsFacturaProv::where('factura',$orden->id)->get();
      return view('ordenes.show')->with(compact('orden', 'items'));
    }
    return redirect('empresa/ordenes')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Imprimir una orden de compra
  * @param int $id
  * @return pdf
  */
  public function Imprimir($id){
    /**
     * toma en cuenta que para ver los mismos 
     * datos debemos hacer la misma consulta
    **/
    view()->share(['title' => 'Imprimir Orden de Compra']);
    $orden = Ordenes_Compra::where('empresa',Auth::user()->empresa)->where('orden_nro', $id)->first();
    if ($orden) {
      
      $items = ItemsFacturaProv::where('factura',$orden->id)->get();
      $itemscount = ItemsFacturaProv::where('factura',$orden->id)->count();
      $pdf = PDF::loadView('pdf.orden_compra', compact('items', 'orden', 'itemscount'));
        return  response ($pdf->stream())->withHeaders([
                'Content-Type' =>'application/pdf',]);

    } 
  }

  /**
  * Formulario para modificar los datos de una orden de compra
  * @param int $id
  * @return view
  */
  public function edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $orden = Ordenes_Compra::where('empresa',Auth::user()->empresa)->where('orden_nro', $id)->where('tipo',2)->first();
    if ($orden) {        
      view()->share(['title' => 'Modificar Orden de Compra: '.$orden->orden_nro, 'icon' =>'']);
      $items = ItemsFacturaProv::where('factura',$orden->id)->get();
      $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $orden->bodega)->first();
      if (!$bodega) {$bodega = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->first();}
      
      $inventario = Inventario::select('inventario.*', 
      DB::raw('(Select nro from productos_bodegas where bodega='.$bodega->id.' and producto=inventario.id) as nro'))
      ->where('empresa',Auth::user()->empresa)->where('status', 1)->havingRaw('if(inventario.tipo_producto=1, id in (Select producto from productos_bodegas where bodega='.$bodega->id.'), true)')->get();   
      $bodegas = Bodega::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
      $clientes = Contacto::where('empresa',Auth::user()->empresa)->whereIn('tipo_contacto',[1,2])->get();
      $impuestos = Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();    
      $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('estatus', 1)->whereNull('asociado')->get();
      
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->where('id','>',1)->get();
        $medidas=DB::table('medidas')->get();
        $unidades=DB::table('unidades_medida')->get();
        view()->share(['icon' =>'', 'title' => 'Nuevo Producto']);
        $extras = CamposExtra::where('empresa',Auth::user()->empresa)->where('status', 1)->get();

        $identificaciones=TipoIdentificacion::all();
        $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
        $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
        $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
        $prefijos=DB::table('prefijos_telefonicos')->get();
      
        return view('ordenes.edit')->with(compact('orden', 'items', 'inventario', 'bodegas', 'clientes',
          'impuestos', 'categorias', 'prefijos', 'tipos_empresa', 'listas', 'vendedores', 'identificaciones', 'extras',
          'unidades', 'medidas'));
    }
    return redirect('empresa/ordenes')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos de orden de compra
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $orden =Ordenes_Compra::find($id);
    if ($orden) {
      if ($orden->estatus==1 && $orden->tipo==2) {
        $orden->proveedor =$request->proveedor;
        $orden->comprador = $request->comprador;
        $orden->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
        $orden->vencimiento=Carbon::parse($request->vencimiento)->format('Y-m-d');
        $orden->observaciones=mb_strtolower($request->observaciones);
        $orden->notas=mb_strtolower($request->notas);
        $orden->term_cond=mb_strtolower($request->term_cond);
        $orden->bodega=$request->bodega;  
        $orden->save();

        $inner=array();
        //Ciclo para registrar y/o modificar los itemas de la factura
        for ($i=0; $i < count($request->item) ; $i++) { 
          $cat='id_item'.($i+1);
          if($request->$cat){
            $items = ItemsFacturaProv::where('id', $request->$cat)->first();
          }
          else{
            $items = new ItemsFacturaProv;
          }

          $impuesto = Impuesto::where('id', $request->impuesto[$i])->first();

          if (is_numeric($request->item[$i])) {
            $producto = Inventario::where('empresa',Auth::user()->empresa)->where('id', $request->item[$i])->first();
            $items->producto=$producto->id;
            $items->tipo_item=1;
          }
          else{ 
            $item=explode('_', $request->item[$i])[1];
            $categorias=Categoria::where('empresa',Auth::user()->empresa)->where('id',  $item)->first();
            $items->producto=$categorias->id;
            $items->tipo_item=2;
          }

          $items->factura=$orden->id;
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
          DB::table('items_factura_proveedor')->where('factura', $orden->id)->whereNotIn('id', $inner)->delete();
        }

        $mensaje='Se ha modificado satisfactoriamente la orden de compra';
        return redirect('empresa/ordenes')->with('success', $mensaje)->with('codigo', $orden->id);


      }
      return redirect('empresa/ordenes')->with('success', 'La orden de compra '.$orden->orden_nro.' ya esta cerrada');

    }
    return redirect('empresa/ordenes')->with('success', 'No existe un registro con ese id');
  }



  /**
  * Funcion para eliminar la orden de compra
  */
  public function destroy($id, Request $request){
    $orden =Ordenes_Compra::find($id);
    if ($orden) {
      if ($orden->estatus>0 && empty($orden->nro)  && $orden->tipo==2) {
        $ult =Ordenes_Compra::where('empresa',Auth::user()->empresa)->OrderBy('id', 'desc')->first();

        if ($orden->id == $ult->id) {        
          $nro=Numeracion::where('empresa',Auth::user()->empresa)->first();
          $nro->orden-=1;
          $nro->save();
        }

        ItemsFacturaProv::where('factura', $orden->id)->delete();
        $orden->delete();
      }
    }
    return redirect('empresa/ordenes')->with('success', 'Se ha eliminado la orden de compra exitosamente');
  }


  /**
  * Funcion para anular la orden de compra
  */
  public function anular($id, Request $request){
    $orden =Ordenes_Compra::find($id);
    if ($orden) {
      if ($orden->estatus==1 && empty($orden->nro) && $orden->tipo==2) {
        $orden->estatus=2; $orden->save();    

        return back()->with('success', 'Se ha anulado la orden de compra '.$orden->orden_nro)->with('codigo', $orden->id);   
      }

      if ($orden->estatus==2 && empty($orden->nro) && $orden->tipo==2) {
        $orden->estatus=1; $orden->save();    

        return back()->with('success', 'Se ha convertido a abierta la orden de compra '.$orden->orden_nro)->with('codigo', $orden->id);   
      }
    }
  }


  public function enviar($id, $emails=null, $redireccionar=true){
    /**
     * toma en cuenta que para ver los mismos 
     * datos debemos hacer la misma consulta
    **/
    view()->share(['title' => 'Enviando Orden de Compra']);
    $orden = Ordenes_Compra::where('empresa',Auth::user()->empresa)->where('orden_nro', $id)->first();
    if ($orden) {
      if (!$emails) {
        $emails=$orden->proveedor()->email;
        if ($orden->proveedor) {
          if ($orden->proveedor()->asociados('number')>0) {
            $email=$emails;
            $emails=array();
            if ($email) {$emails[]=$email;}
            foreach ($orden->proveedor()->asociados() as $asociado) {
              if ($asociado->notificacion==1 && $asociado->email) {
                $emails[]=$asociado->email;
              }
            }
          }
        
        }
      }
      if (!$emails || count($emails)==0) {
        if ($redireccionar) {
          return back()->with('error', 'El Proveedor ni sus contactos asociados tienen correo electrónico registrado');
        }
        return false;
      }
      $items = ItemsFacturaProv::where('factura',$orden->id)->get();
      $itemscount = ItemsFacturaProv::where('factura',$orden->id)->count();
      $pdf = PDF::loadView('pdf.orden_compra', compact('items', 'orden', 'itemscount'))->stream();
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
      self::sendMail('emails.orden_compra', compact('orden'), function($message) use ($pdf, $emails, $orden)
      {
        $message->from(Auth::user()->empresa()->email, Auth::user()->empresa()->nombre);
        $message->to($emails)->subject('Orden de Compra #'.$orden->orden_nro);
        $message->attachData($pdf, 'cotizacion.pdf', ['mime' => 'application/pdf']);
      });
    } 


    if ($redireccionar) {
      return redirect('empresa/ordenes/'.$orden->orden_nro)->with('success', 'Se ha enviado el correo');
    }
  }


  
  /**
  * Funcion para anular la orden de compra
  */
  public function facturar($id, Request $request){
    $orden =Ordenes_Compra::find($id);
    if ($orden) {
      if ($orden->estatus==1 && empty($orden->nro) && $orden->tipo==2) {
        $orden->nro=Ordenes_Compra::where('empresa', Auth::user()->empresa)->where('tipo', 1)->count()+1;

        $orden->fecha_factura=$orden->fecha;
        $orden->vencimiento_factura=$orden->vencimiento;
        $orden->tipo=1; $orden->save();

        //se restaran todos los items al inventario
        $bodega = Bodega::where('empresa',Auth::user()->empresa)->where('id', $orden->bodega)->first();
        $items = ItemsFacturaProv::join('inventario as inv', 'inv.id', '=', 'items_factura_proveedor.producto')->select('items_factura_proveedor.*')->where('items_factura_proveedor.factura',$orden->id)->where('items_factura_proveedor.tipo_item', 1)->where('inv.tipo_producto', 1)->get();
        foreach ($items as $item) {
          $ajuste=ProductosBodega::where('empresa', Auth::user()->empresa)->where('bodega', $bodega->id)->where('producto', $item->producto)->first();
          if ($ajuste) {
            $ajuste->nro+=$item->cant;
            $ajuste->save();
          }
        }

        
      }
    }
    return back()->with('success', 'Se ha facturado la orden de compra '.$orden->orden_nro)->with('codigo', $orden->id);
  }


  public function datatable_producto(Request $request, $producto){
    // storing  request (ie, get/post) global array to a variable
    $requestData =  $request;
    $columns = array(
    // datatable column index  => database column name
        0 => 'factura_proveedores.codigo',
        1 => 'nombrecliente',
        2 => 'factura_proveedores.fecha',
        3 => 'factura_proveedores.vencimiento',
        4 => 'factura_proveedores.estatus'
    );    
    $facturas=Ordenes_Compra::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')->select('factura_proveedores.*', DB::raw('c.nombre as nombrecliente'))->where('factura_proveedores.empresa',Auth::user()->empresa)->where('factura_proveedores.tipo', 2)->orWhereNotNull('orden_nro')->whereRaw('factura_proveedores.id in (Select distinct(factura) from items_factura_proveedor where producto='.$producto.')');

    if ($requestData->search['value']) {   
      // if there is a search parameter, $requestData['search']['value'] contains search parameter
       $facturas=$facturas->where(function ($query) use ($requestData) {
          $query->where('factura_proveedores.codigo', 'like', '%'.$requestData->search['value'].'%')
          ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
        });
    }
    $totalFiltered=$totalData=$facturas->count();
    $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);


    $facturas=$facturas->get();

    $data = array();
    foreach ($facturas as $factura) {
       $nestedData = array();
        $nestedData[] = '<a href="'.route('ordenes.show',$factura->orden_nro).'">'.$factura->orden_nro.'</a>';
        $nestedData[] = '<a href="'.route('contactos.show',$factura->proveedor).'" target="_blanck">'.$factura->nombrecliente.'</a>';
        $nestedData[] = date('d-m-Y', strtotime($factura->fecha_factura));
        $nestedData[] = date('d-m-Y', strtotime($factura->vencimiento_factura));
        $nestedData[] ='<spam class="text-'.$factura->estatus(true).'">'.$factura->estatus().'</spam>';
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
        $boton = '<a  href="'.route('ordenes.show',$factura->orden_nro).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
              <a   href="'.route('ordenes.imprimir.nombre',['id' => $factura->orden_nro, 'name'=> 'Orden Compra No. '.$factura->orden_nro.'.pdf']).'" target="_black" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>';

        if($factura->tipo ==2 && empty($factura->nro) && $factura->estatus>0) 
        {
          $boton.='<a href="'.route('ordenes.edit', $factura->orden_nro).'" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>';
          $boton.=' <form action="'.route('ordenes.anular',$factura->id).'" method="POST" class="delete_form" style="display: none;" id="anular-ordenes'.$factura->id.'">'.csrf_field().'</form>';

            if($factura->estatus==1){
              $boton .= '<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('."'anular-ordenes".$factura->id."', '¿Está seguro de que desea anular la orden de compra?', ' ');".'"><i class="fas fa-minus"></i></button> ';
            }
            else if($factura->estatus==2){
              $boton.='<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('."'anular-ordenes".$factura->id."', '¿Está seguro de que desea abrir la orden de compra?', ' ');".'"><i class="fas fa-unlock-alt"></i></button>';
            } 
            $boton.='<form action="'. route('ordenes.destroy',$factura->id) .' method="post" class="delete_form" style="margin: 0;display: inline-block;" id="eliminar-orden'.$factura->id.'>'. csrf_field() .' <input name="_method" type="hidden" value="DELETE"></form>
              <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('."'eliminar-orden".$factura->id."', '¿Estas seguro que deseas eliminar la orden de compra?', 'Se borrara de forma permanente'".');"><i class="fas fa-times"></i></button>';             
       
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
        0 => 'factura_proveedores.codigo',
        1 => 'nombrecliente',
        2 => 'factura_proveedores.fecha',
        3 => 'factura_proveedores.vencimiento',
        4 => 'factura_proveedores.estatus',
        5 => 'total'
    );
    $facturas = Ordenes_Compra::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
        ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
        ->select('factura_proveedores.*', DB::raw('c.nombre as nombrecliente'),
            DB::raw('SUM((if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'))
        ->where('factura_proveedores.empresa',Auth::user()->empresa)
        ->where('factura_proveedores.tipo', 2)
        ->orWhereNotNull('orden_nro')
        ->where('factura_proveedores.proveedor',$cliente)
        ->WhereNotNull('factura_proveedores.orden_nro');

    if ($requestData->search['value']) {
      // if there is a search parameter, $requestData['search']['value'] contains search parameter
       $facturas=$facturas->where(function ($query) use ($requestData) {
          $query->where('factura_proveedores.codigo', 'like', '%'.$requestData->search['value'].'%')
          ->orwhere('c.nombre', 'like', '%'.$requestData->search['value'].'%');
        });
    }
    $facturas = $facturas->groupBy('if.factura');
    $totalFiltered=$totalData=$facturas->count();
    $facturas->orderby($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir'])->skip($requestData['start'])->take($requestData['length']);


    $facturas=$facturas->get();

    $data = array();
    foreach ($facturas as $factura) {
       $nestedData = array();
        $nestedData[] = '<a href="'.route('ordenes.show',$factura->orden_nro).'">'.$factura->orden_nro.'</a>';
        $nestedData[] = '<a href="'.route('contactos.show',$factura->proveedor).'" target="_blanck">'.$factura->nombrecliente.'</a>';
        $nestedData[] = date('d-m-Y', strtotime($factura->fecha_factura));
        $nestedData[] = date('d-m-Y', strtotime($factura->vencimiento_factura));
        $nestedData[] ='<spam class="text-'.$factura->estatus(true).'">'.$factura->estatus().'</spam>';
        $nestedData[] = Auth::user()->empresa()->moneda.Funcion::Parsear($factura->total()->total);
        $boton = '<a  href="'.route('ordenes.show',$factura->orden_nro).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
              <a   href="'.route('ordenes.imprimir.nombre',['id' => $factura->orden_nro, 'name'=> 'Orden Compra No. '.$factura->orden_nro.'.pdf']).'" target="_black" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>';

        if($factura->tipo ==2 && empty($factura->nro) && $factura->estatus>0) 
        {
          $boton.='<a href="'.route('ordenes.edit', $factura->orden_nro).'" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>';
          $boton.=' <form action="'.route('ordenes.anular',$factura->id).'" method="POST" class="delete_form" style="display: none;" id="anular-ordenes'.$factura->id.'">'.csrf_field().'</form>';

            if($factura->estatus==1){
              $boton .= '<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('."'anular-ordenes".$factura->id."', '¿Está seguro de que desea anular la orden de compra?', ' ');".'"><i class="fas fa-minus"></i></button> ';
            }
            else if($factura->estatus==2){
              $boton.='<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('."'anular-ordenes".$factura->id."', '¿Está seguro de que desea abrir la orden de compra?', ' ');".'"><i class="fas fa-unlock-alt"></i></button>';
            } 
            $boton.='<form action="'. route('ordenes.destroy',$factura->id) .' method="post" class="delete_form" style="margin: 0;display: inline-block;" id="eliminar-orden'.$factura->id.'>'. csrf_field() .' <input name="_method" type="hidden" value="DELETE"></form>
              <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('."'eliminar-orden".$factura->id."', '¿Estas seguro que deseas eliminar la orden de compra?', 'Se borrara de forma permanente'".');"><i class="fas fa-times"></i></button>';             
       
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
}
