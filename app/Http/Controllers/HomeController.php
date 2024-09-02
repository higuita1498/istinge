<?php

namespace App\Http\Controllers;

use App\Model\Gastos\GastosRecurrentes;
use App\SuscripcionPago;
use Cassandra\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use App\Empresa;
use App\Categoria;
use App\Soporte;
use App\NumeracionFactura; use App\Impuesto;
use App\Banco; use App\Retencion;
use App\Contacto; use App\Model\Ingresos\Factura;
use App\Model\Inventario\Inventario;
use App\Model\Gastos\FacturaProveedores;
use Auth; use Session; use DB; use App\Planes; use App\Suscripcion; use App\Radicado; use App\Solicitud; use App\Contrato;

class HomeController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        view()->share(['inicio' => 'empresa', 'seccion' => 'inicio', 'title' => 'Inicio', 'icon' =>'fa fa-building', 'full' => true]);

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        if (!Auth::check()){
            return Redirect::to('/login');
        }

        if (Auth::user()->rol==1) {
           return redirect('master/usuarios');
        }else if (Auth::user()->rol==2 || Auth::user()->rol >= 2 ) {
            $this->getAllPermissions(Auth::user()->id);
           return redirect()->route('empresa');
        }
        return view('welcome');
    }

    public function inicio()
    {
      return redirect('/login');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->getAllPermissions(Auth::user()->id);

        $radicados = Radicado::all()->where('empresa', Auth::user()->empresa)->count();
        $radicados_pendiente = Radicado::whereIn('estatus',[0,2])->where('empresa', Auth::user()->empresa)->count();
        $radicados_solventado = Radicado::whereIn('estatus',[1,3])->where('empresa', Auth::user()->empresa)->count();

        $contra_ena = Contrato::where('state','enabled')->where('status', 1)->where('empresa', Auth::user()->empresa)->count();
        $contra_disa = Contrato::where('state','disabled')->where('status', 1)->where('empresa', Auth::user()->empresa)->count();
        $contra_factura = Contrato::whereIn('fecha_corte',[15,30])->where('status', 1)->where('empresa', Auth::user()->empresa)->count();

        $factura = Factura::whereIn('estatus',[1,0])->where('lectura',1)->where('empresa', Auth::user()->empresa)->count();
        $factura_cerrada = Factura::where('estatus',0)->where('lectura',1)->where('empresa', Auth::user()->empresa)->count();
        $factura_abierta = Factura::where('estatus',1)->where('lectura',1)->where('empresa', Auth::user()->empresa)->count();

        $contratosCatv = Contrato::where('olt_sn_mac','<>',null)->count();
        $contratosCatvEnabled = Contrato::where('olt_sn_mac','<>',null)->where('state_olt_catv',1)->count();
        $contratosCatvDisabled = Contrato::where('olt_sn_mac','<>',null)->where('state_olt_catv',0)->count();

        view()->share(['inicio' => 'empresa', 'seccion' => 'inicio', 'title' => Auth::user()->empresa()->nombre , 'icon' =>'fa fa-building']);
        return view('welcome')->with(compact('radicados','contra_ena','contra_disa','contra_factura','factura','factura_cerrada',
        'factura_abierta','radicados_pendiente','radicados_solventado',
        'contratosCatv', 'contratosCatvEnabled', 'contratosCatvDisabled'
        ));

        if (!Auth::check())
        {
            // Si tenemos sesión activa mostrará la página de inicio
            return Redirect::to('/login');
        }

        if (Auth::user()->rol==1) {
              $usuarios = User::select('nombres','username','created_at')->orderBy('created_at','DESC')->paginate(5);
            $empresas = Empresa::select('nombre','created_at')->get();

            $facturas = DB::table('empresas')->join('factura','factura.empresa','=','empresas.id')
                                            ->select('empresas.nombre',DB::raw('COUNT(factura.id) as facturas'))
                                            ->groupBy('factura.empresa')
                                            ->orderBy('facturas','DESC')
                                            ->paginate(5);

            $remisiones = DB::table('empresas')->join('remisiones','remisiones.empresa','=','empresas.id')
                                            ->select('empresas.nombre',DB::raw('COUNT(remisiones.id) as remisiones'))
                                            ->groupBy('remisiones.empresa')
                                            ->orderBy('remisiones','DESC')
                                            ->paginate(5);
            $pagos = DB::table('empresas')->join('gastos','gastos.empresa','=','empresas.id')
                                            ->select('empresas.nombre',DB::raw('COUNT(gastos.id) as gastos'))
                                            ->groupBy('gastos.empresa')
                                            ->orderBy('gastos','DESC')
                                            ->paginate(5);
            $totalProductos = DB::table('empresas')->join('inventario','inventario.empresa','=','empresas.id')
                ->select('empresas.nombre',DB::raw('COUNT(inventario.id) as inventario'))
                ->groupBy('inventario.empresa')
                ->orderBy('inventario','ASC')
                ->paginate(10);


            $fecha = date('Y-m-d');

            return view('master')->with(compact('totalProductos','empresas','usuarios','fecha','facturas','remisiones','pagos'));

        }
        else if (Auth::user()->rol==2 || Auth::user()->rol >= 2 ) {
            $optimo=array();
            $consejo=1;
            $consejo*=$optimo["conf_factura"]=NumeracionFactura::where('empresa',Auth::user()->empresa)->count();
            $consejo*=$optimo["contactos"]=Contacto::where('empresa',Auth::user()->empresa)->count();
            $consejo*=$optimo["facturas"]=Factura::where('empresa',Auth::user()->empresa)->count();
            $consejo*=$optimo["inventario"]=Inventario::where('empresa',Auth::user()->empresa)->count();
            $consejo*=$optimo["inpuesto"]=Impuesto::where('empresa',Auth::user()->empresa)->count();
            $consejo*=$optimo["bancos"]=Banco::where('empresa',Auth::user()->empresa)->count();
            $consejo*=$optimo["retenciones"]=Retencion::where('empresa',Auth::user()->empresa)->count();
            $consejo*=$optimo["empresa"]=Auth::user()->empresa()->moneda==''?0:1;
            $optimo=(object) $optimo;

            $fecha = date('Y-m-d');
            $nuevafecha = strtotime ( '-6 month' , strtotime ( $fecha ) ) ;
            $meses=array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiempre', 'Octubre', 'Noviembre', 'Diciembre');
            $ingresos_gastos=array();
            for ($i=1; $i <7 ; $i++) {

              $fecha = new \DateTime(date ( 'Y-m-d' , $nuevafecha ));
              $fin=$fecha->modify('last day of this month')->format('Y-m-d');
              $inicio=$fecha->modify('first day of this month')->format('Y-m-d');


              $datos=array('x'=>$meses[$fecha->format('n')], 'y'=>0, 'z'=>0);
              //Ingresos
              $select=DB::table('ingresos_factura as if')->select(DB::raw('SUM(pago) as total'))->WhereRaw('ingreso in (SELECT id from ingresos where empresa=? and fecha>=? and fecha <=? and estatus=1)', [Auth::user()->empresa, $inicio, $fin])->first();
              $datos['y']+= $select->total;

              $select=DB::table('ingresos_categoria as ic')->select(DB::raw('SUM(valor) as total'))->WhereRaw('ingreso in (SELECT id from ingresos where empresa=? and fecha>=? and fecha <=? and estatus=1)', [Auth::user()->empresa, $inicio, $fin])->first();
              $datos['y']+=$select->total;

              $select=DB::table('ingresosr_remisiones as irr')->select(DB::raw('SUM(pago) as total'))->WhereRaw('ingreso in (SELECT id from ingresosr where empresa=? and fecha>=? and fecha <=? and estatus=1)', [Auth::user()->empresa, $inicio, $fin])->first();
              $datos['y']+=$select->total;

              /*$select=DB::table('ingresos')->select(DB::raw('SUM(total_credito) as total'))->where('empresa', Auth::user()->empresa)->where('fecha', '>=', $inicio)->where('fecha', '<=', $fin)->first();
              $datos['y']+=$select->total;*/

              //Gastos
              $select=DB::table('gastos_factura as gf')->select(DB::raw('SUM(pago) as total'))->WhereRaw('gasto in (SELECT id from gastos where empresa=? and fecha>=? and fecha <=? and estatus=1)', [Auth::user()->empresa, $inicio, $fin])->first();
              $datos['z']+=$select->total;
              $select=DB::table('gastos_categoria as gc')->select(DB::raw('SUM(valor) as total'))->WhereRaw('gasto in (SELECT id from gastos where empresa=? and fecha>=? and fecha <=? and estatus=1)', [Auth::user()->empresa, $inicio, $fin])->first();
              $datos['z']+=$select->total;

              $select=DB::table('gastos')->select(DB::raw('SUM(total_credito) as total'))->where('empresa', Auth::user()->empresa)->where('fecha', '>=', $inicio)->where('fecha', '<=', $fin)->first();
              $datos['z']+=$select->total;

              $nuevafecha=date ( 'Y-m-d' , $nuevafecha );
              $nuevafecha = strtotime ( '+1 month' , strtotime ( $nuevafecha ) ) ;
              $ingresos_gastos[]=$datos;


            }

            $fecha = date('Y-m-d');
            $nuevafecha = strtotime ( '-6 month' , strtotime ( $fecha ) ) ;
            $nuevafecha = date ( 'Y-m-d' , $nuevafecha );

            $gastos['categorias']=DB::table('gastos_categoria as gc')->select(DB::raw('SUM(valor) as total'),  DB::raw('(SELECT nombre from categorias where id=gc.categoria) as nombre'))->WhereRaw('gasto in (SELECT id from gastos where empresa=? and fecha>?)', [Auth::user()->empresa, $nuevafecha])->groupBy('gc.categoria')->orderBy('total', 'desc')->LIMIT(10)->get();
            $gastos['proveedores']=FacturaProveedores::leftjoin('contactos as c', 'factura_proveedores.proveedor', '=', 'c.id')
            ->join('items_factura_proveedor as if', 'factura_proveedores.id', '=', 'if.factura')
            ->select(DB::raw('c.nombre as nombre'),
            DB::raw('SUM(
                (if.cant*if.precio)-(if.precio*(if(if.desc,if.desc,0)/100)*if.cant)+(if.precio-(if.precio*(if(if.desc,if.desc,0)/100)))*(if.impuesto/100)*if.cant) as total'))
            ->where('factura_proveedores.empresa',Auth::user()->empresa)->where('factura_proveedores.tipo',1)
            ->where("factura_proveedores.fecha", '>',$nuevafecha)->groupBy('factura_proveedores.proveedor')->OrderBy('total', 'desc')->LIMIT(10)->get();

            $ventas['cliente']=Factura::join('contactos as c', 'factura.cliente', '=', 'c.id')
            ->join('items_factura as if', 'factura.id', '=', 'if.factura')
            ->select(DB::raw('c.nombre as nombre'),
            DB::raw('SUM( if.precio - (if.precio* if(if.desc, (if.desc/100) ,0) )) as total'))
            ->where('factura.empresa',Auth::user()->empresa)->where('factura.tipo',1)->where("factura.fecha", '>',$nuevafecha)->groupBy('factura.cliente')->orderBy('total', 'desc')->LIMIT(10)->get();

            $ventas['items']=DB::table('items_factura as if')->select(DB::raw('SUM(precio - (if.precio*(if(if.desc,(if.desc/100),0) ) ) + (if.precio*(if(if.impuesto,(if.impuesto/100),0) ) ) ) as total'),
                DB::raw('(SELECT producto from inventario where id=if.producto) as nombre'))

            ->WhereRaw('factura in (SELECT id from factura where empresa=? and tipo= 1 and fecha>?)', [Auth::user()->empresa, $nuevafecha])->groupBy('if.producto')->orderBy('total', 'desc')->LIMIT(10)->get();

            $gastosR = GastosRecurrentes::where('empresa', Auth::user()->empresa)->get();

            $empresa = User::join('empresas','empresas.id','=','usuarios.empresa')
                            ->select('empresas.created_at')
                            ->where('usuarios.empresa', Auth::user()->empresa)
                            ->get();


            $fecha_inicio = $empresa[0]->created_at;
            $fecha_final = date('Y-m-d', strtotime($empresa[0]->created_at."+ 2 month"));
            $dias_1 = $fecha_inicio->diffInDays($fecha_final);
            $dias_2 = $fecha_inicio->diffInDays(\Carbon\Carbon::now());
            $tot_dias = $dias_1 - $dias_2;

            $soportes = Soporte::where('empresa',Auth::user()->empresa)->whereNull('asociada')->get();
            //dd($soportes[0]->estatus);

            foreach($soportes as $soporte){
                if($soporte->estatus == 2){
                session()->put('soporte', 'Tiene Tickets de Soporte Resueltos <a class="text-black" href='.route('soporte.index').'> <b>Click Aqui.</b></a>');
                }
            }


            /*
            if($tot_dias<=3){
                session()->put('suscripcion', 'Se ha Terminado su Periodo Gratuito de 2 Meses, Para seguir disfrutando de Nuestro Sistema Cancelar Alguno de Nuestros Planes<a class="text-black" href="PlanesPagina"> <b>Click Aqui.</b></a>');
            }*/

            if (count($gastosR) > 0)
            {
                foreach ($gastosR as $gastoR)
                {
                    session()->put('notify', 'Tiene pagos recurrentes próximos a ejecutar. <a class="text-black" href='.route('pagosrecurrentes.index').'><b>Revisar</b></a>');
                }
            }


              //Programación para validar la tabla suplente_pago ya que si pasado un dia el transactionId sigue vacio es
              //porque la persona no completo el pago y se salió en algun  momento, entonces vamos a borrar ese registro que nos crea basura

            $suplente_pago = Planes::where('transactionId',null)->where('plazo','<',Carbon::now())->get();

            foreach ($suplente_pago as $suplente) {
              $suplente->delete();
            }


            $ingresosLimit      = $this->checkIngresos();
            $fechasLimit        = $this->checkFechas();
            $facturasLimit      = $this->checkFacturas();
            $suscripcionPago    = SuscripcionPago::where('id_empresa', Auth::user()->empresa)->orderBy('id', 'desc')->get()->take(2);
            $payPersonalPlan    = $this->payPersonalPlan();
            $price              = false;
            $idPlan             = false;
            if(is_array($this->checkPlan())){
                $price              = $this->checkPlan()['precio'];
                $idPlan             = $this->checkPlan()['id'];
            }

            $abort              = false;
            if($fechasLimit['limit'])
                $abort = true;
            if(count($suscripcionPago) >0){
               if ($suscripcionPago->first()->estado != 1 && $suscripcionPago->last()->estado != 1)
                   $abort = true;
            }
            //
            $ventas        = (new ReportesController)->ventasExport(true, 0);
            $compras       = (new ReportesController)->comprasExport(true, 0);
            $gastos        = (new ReportesController)->gastosExport(true, 0);
            //
            $ventas3          = (new ReportesController)->ventasExport(false, 3);
            $compras3         = (new ReportesController)->comprasExport(false, 3);
            $gastos3          = (new ReportesController)->gastosExport(false, 3);
            //
            $ventas2          = (new ReportesController)->ventasExport(false, 2);
            $compras2         = (new ReportesController)->comprasExport(false, 2);
            $gastos2          = (new ReportesController)->gastosExport(false, 2);
            //
            $ventas1          = (new ReportesController)->ventasExport(false, 1);
            $compras1         = (new ReportesController)->comprasExport(false, 1);
            $gastos1          = (new ReportesController)->gastosExport(false, 1);


            view()->share(['inicio' => 'empresa', 'seccion' => 'inicio', 'title' => 'Inicio', 'icon' =>'fa fa-building']);


            return view('welcome')->with(compact('optimo', 'consejo', 'gastos', 'ventas',
                'ingresos_gastos', 'ingresosLimit', 'fechasLimit', 'facturasLimit', 'ventas',
                'compras', 'abort', 'payPersonalPlan', 'price', 'idPlan'));
        }
    }

    public function checkIngresos(){
        $suscripcion            = SuscripcionPago::where('id_empresa', Auth::user()->empresa)
            ->where('estado', 1)
            ->get()->last();
        if (!$suscripcion){
            $ingresosMax        = (Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last())->ingresos()['ingresos'];
            if($this->unlimited()){
                $infoIngresos = array(
                    'limit'     => false,
                    'restante'  => null);
                return $infoIngresos;
            }
            $limit = 5000000;
            $checkPlan = $this->checkPlan();
            if(is_array($checkPlan))
                $limit = $checkPlan['ingresos'];

            $infoIngresos = array(  'limit'     => $ingresosMax >= $limit,
                                    'restante'  => $limit);

            return $infoIngresos;
        }
        // Verificación de existencia de mas de un vigente
        $empresa = Empresa::find(Auth::user()->empresa);
        if($empresa->subscriptions() != false)
            return array('limit' => $suscripcion->restanteIngresos(true) <= 0, 'restante'  => $suscripcion->restanteIngresos(true));

        $limit = $suscripcion->ingresosLimit();
        $checkPlan = $this->checkPlan();
        if(is_array($checkPlan))
            $limit = $checkPlan['ingresos'];

        return array('limit' => $suscripcion->restanteIngresos() <= 0, 'restante'  => $suscripcion->restanteIngresos());
    }

    public function checkFechas(){

        $suscripcionPago    = SuscripcionPago::where("id_empresa")->get()->last();
        $fechaLimit         = true;
        $fechaActual        = Carbon::now();
        if (!$suscripcionPago){
            $suscripcion    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
            $fechaVenc      = Carbon::parse($suscripcion->fec_vencimiento);
            $fechaLimit     = ($fechaActual->greaterThan($fechaVenc)) ? $fechaLimit : false;
        }else{
            $fechaVenc      =SuscripcionPago::where('id_empresa', Auth::user()->empresa)->get()->last();
            $fechaLimit     = ($fechaActual->greaterThan($fechaVenc)) ? $fechaLimit : false;
        }
        $infoFechas = array(  'limit'     => $fechaLimit,
                              'venc'      => $fechaVenc);
        return $infoFechas;

    }

    public function checkFacturas(){

        $suscripcionPago    = SuscripcionPago::where("id_empresa", Auth::user()->empresa)
            ->where('estado', 1)
            ->get()->last();
        if(!$suscripcionPago){
            $suscripcion    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
            $facturasHechas = $suscripcion->facturasHechas();
            $numeroFacturas = $suscripcion->numeroFacturas();
            return array( 'limit' => $facturasHechas,'restante'  => $numeroFacturas);
        }else{
            $facturasHechas = $suscripcionPago->facturasHechas();
            $numeroFacturas = $suscripcionPago->numeroFacturas();
            $empresa = Empresa::find(Auth::user()->empresa);
            if($empresa->subscriptions() != false)
                $facturasHechas = $suscripcionPago->facturasHechas(true);
        }

        // Verificación de existencia de mas de un vigente

            if(is_array($this->checkPlan()))
                $facturasHechas == $this->checkPlan()['facturas'];

        $infoFacturas = array( 'limit'      => $facturasHechas,
                                'restante'  => $numeroFacturas);
        return $infoFacturas;
    }

    private function unlimited(){

        $suscripcionFree    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $suscripcionFree    = Carbon::parse($suscripcionFree->created_at);

        return Carbon::now()->diffInMonths($suscripcionFree) >= 1 ? false : true;
    }

    /**
      * Funcion para mostrar un json uno o todos los productos del inventario
      * @param int $id
      * @return json_encode
      */
      public function carrito($empresa, $id=false){
        if (!$id) {
          $inventario =Inventario::where('status',1)->where('empresa',$empresa)->get();
          return json_encode($inventario);

        }
        else{

        }
      }

      public function terminoscondiciones()
      {
          view()->share(['inicio' => 'empresa', 'seccion' => 'Términos y Condiciones', 'title' => 'Términos y Condiciones', 'icon' =>'fa fa-building']);
          return view('documentacion.terminos-condiciones');
      }

             public function contactanos()
    {
        return view('PaginaInicio.footer.contactanos.index');
    }

         public function modulos()
    {
        return view('PaginaInicio.footer.sic.modulos');
    }

    public function planes()
    {
        return view('PaginaInicio.footer.sic.planes');
    }

    public function registrarse()
    {
        if (url()->full() == 'https://gestordepartes.net/Registrarse'){
            return redirect('https://gestoru.com/Registrarse');
        }else{
            $prefijos=DB::table('prefijos_telefonicos')->get();
            return view('PaginaInicio.footer.sic.registrarse',compact('prefijos'));
        }
    }

    /**
     * Verifica si la empresa posee un plan personalizado.
     * En caso de que sea asi, devuelve los datos relacionados al mismo.
     * @return array|bool
     */
    private function checkPlan()
    {
        $empresa = Empresa::find(Auth::user()->empresa);
        $plan = ($empresa->p_personalizado != 0) ? DB::table('planes_personalizados')->find($empresa->p_personalizado) : '' ;
        return ($empresa->p_personalizado == 0) ? true : array(
            'id' => $plan->id,
            'nombre' => $plan->nombre,
            'facturas' => $plan->facturas,
            'ingresos' => $plan->ingresos,
            'precio' => $plan->precio,
            'pago' => $this->payPersonalPlan()
        );

    }

    /**
     * Verificación del pago de la suscripcion personalizada
     * @return bool
     */
    private function payPersonalPlan()
    {
        $suscripcion = SuscripcionPago::where('id_empresa', Auth::user()->empresa)
            ->where('personalizado', 1)
            ->get();
        return count($suscripcion) > 0 ? true : false;
    }

       /**
     * Cerrar todas las sesiones iniciadas con un usuario
     * @return bool
     */

    public function peticionCloseAllSesions(){
        $user = User::find(Auth::user()->id);
        $user->online = 0;
        $user->save();
        Auth::logout();
        return redirect('/Inicio');
    }

    public function createCategoryMassive(){
        //Programacion de insercion de nuevas categorias en todas las empresas dle sistema contable
      $categorias = Categoria::whereNull('empresa')->where('nomina',1)->orWhere('fk_catgral',0)->whereNull('empresa')->get();
      $empresas = Empresa::where('status',1)->get();

      foreach($empresas as $empresa)
      {
          foreach ($categorias->chunk(10) as $chunk) {

              foreach($chunk as $categoria){
                  if(Categoria::where('empresa',$empresa->id)->where('codigo',$categoria->codigo)->where('nombre',$categoria->nombre)->count() == 0)
                  {
                              $cat = new Categoria;
                                  $cat->nombre = $categoria->nombre;
                                  $cat->empresa = $empresa->id;
                                  $cat->nro = $categoria->nro;
                                  $cat->descripcion = $categoria->descripcion;
                                  $cat->asociado = $categoria->asociado;
                                  $cat->codigo = $categoria->codigo;
                                  $cat->nomina = 1;
                                  $cat->fk_catgral = $categoria->fk_catgral;
                                  $cat->fk_nomcuenta_tipo = $categoria->fk_nomcuenta_tipo;
                                  $cat->valor_hora_ordinaria = $categoria->valor_hora_ordinaria;
                                  $cat->save();
                  }
              }
            }
      }

      return "importacion de cateogiras completado";
  }

  public function subirArchivo(Request $request)
    {
        // Validar el archivo, por ejemplo, asegurándote de que sea una imagen válida.
        $request->validate([
            'archivo' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Subir el archivo a una ubicación específica, por ejemplo, en storage.
        $archivo = $request->file('archivo');
        $rutaArchivo = $archivo->store('archivos');

        // Envía el archivo por correo electrónico
        Mail::to('juanjtuiran@gmail.com')->send(new ArchivoEnviado($rutaArchivo));

        return redirect()->back()->with('success', 'El archivo se ha enviado por correo correctamente.');
    }
}
