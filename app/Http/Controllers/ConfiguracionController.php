<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Empresa; use App\Banco;
use App\TerminosPago; use App\Numeracion;
use App\NumeracionFactura; use App\TipoIdentificacion;
use Carbon\Carbon; use DB;
use App\User;  use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Validator;  use Illuminate\Validation\Rule;  use Auth;
use Session; use App\Rules\guion;
use App\Contacto;
use App\SuscripcionPago;
use App\Servicio;
use App\Mikrotik;
use App\PlanesVelocidad;

use App\Http\Controllers\Nomina\NominaController;
use App\Http\Controllers\Nomina\NominaDianController;
use App\Model\Nomina\Nomina;
use App\Model\Nomina\NominaPeriodos;
use App\SuscripcionNomina;
use App\Model\Nomina\NominaConfiguracionCalculos;
use App\Model\Nomina\Persona;
use App\Http\Controllers\Nomina\PersonasController;

include_once(app_path() .'/../public/routeros_api.class.php');
include_once(app_path() .'/../public/api_mt_include2.php');

use routeros_api;
use RouterosAPI;
use StdClass;

class ConfiguracionController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    view()->share(['seccion' => 'configuracion', 'title' => 'Configuración', 'icon' =>'fas fa-cogs']);
  }

  public function index(){
      $this->getAllPermissions(Auth::user()->id);
      $personalPlan = Empresa::find(Auth::user()->empresa);
      $personalPlan = $personalPlan->p_personalizado;
      if($personalPlan > 0){
          $personalPlan = true;
      }else{
        $personalPlan = false;    
      }
      $empresa = auth()->user()->empresaObj;
 	  return view('configuracion.index')->with(compact('personalPlan','empresa'));
 	}

  /**
  * Vita para ver las numeraciones
  */
  public function numeraciones(){
    $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Numeraciones de Documentos', 'icon' =>'']);
    $numeracion = Numeracion::where('empresa',Auth::user()->empresa)->first();
    $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('tipo',1)->get();
    return view('configuracion.numeraciones')->with(compact('numeracion', 'numeraciones'));

  }

  public function numeraciones_dian(){
    $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Numeraciones de Documentos DIAN', 'icon' =>'']);
    $numeracion = Numeracion::where('empresa',Auth::user()->empresa)->first();
    $numeraciones=NumeracionFactura::where('empresa',Auth::user()->empresa)->where('tipo',2)->get();
    return view('configuracion.numeraciones-dian')->with(compact('numeracion', 'numeraciones'));
  }

  /**
  * Vita para ver la empresa
    */
  public function create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Modificar Información de la Empresa', 'icon' =>'']);
    $identificaciones=TipoIdentificacion::all();
    $empresa = Empresa::find(Auth::user()->empresa);
    $responsabilidades = DB::table('responsabilidades_facturacion')->get();
    $prefijos=DB::table('prefijos_telefonicos')->get();
    $paises  =DB::table('pais')->get();
    $departamentos = DB::table('departamentos')->get();
    $empresa_resp = DB::table('empresa_responsabilidad')->where('id_empresa','=',$empresa->id)->get();
    Log::debug($empresa_resp);

    return view('configuracion.empresa')->with(compact('empresa', 'identificaciones', 'prefijos','responsabilidades','paises','departamentos','empresa_resp'));

  }
  /**
  * Modificar los datos de la empresa
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
    $empresa =Empresa::find(Auth::user()->empresa);
    $FeTest  = $empresa->fe_resolucion;
    $request->validate([
        'nombre' => 'required',
        'nit' => 'required|numeric',
        'telefono' => 'required',
        'email' => 'required|email',
        'direccion' => 'required',
        'tip_iden' => 'required|exists:tipos_identificacion,id',
        'tipo_persona'=>'required',
         'sep_dec'=> new guion,
    ]);
      $errors= (object) array();
      $error =Empresa::where('nombre', $request->nombre)->where('id', '<>', $empresa->id)->get();
      if (count($error)>0) {
        $errors->nombre='El nombre de la empresa ya se encuentra registrado en otra empresa';
        return back()->withErrors($errors)->withInput();
      }

      if ($request->logo) {
        $request->validate([
            'logo'=>'mimes:jpeg,jpg,png| max:200'
        ],['logo.mimes' => 'La extensión del logo debe ser jpeg, jpg, png',
          'logo.max' => 'El peso máximo para el logo es de 200KB',
        ]);

        if ($empresa->logo) {
          $path = public_path() .'/images/Empresas/Empresa'.$empresa->id."/".$empresa->logo;
          if (file_exists($path) ) {
            unlink($path);
          }
        }
        $imagen = $request->file('logo');
        $nombre_imagen = 'logo.'.$imagen->getClientOriginalExtension();
        $empresa->logo=$nombre_imagen;
        $path = public_path() .'/images/Empresas/Empresa'.$empresa->id;
        $imagen->move($path,$nombre_imagen);
      }

      if ($request->img_default) {
        if ($empresa->img_default) {
          $path = public_path() .'/images/Empresas/Empresa'.$empresa->id."/".$empresa->img_default;
          if (file_exists($path) ) {
            unlink($path);
          }
        }
        $imagen = $request->file('img_default');
        $nombre_imagen = 'imagen_default.jpg';
        $empresa->img_default=$nombre_imagen;
        $path = public_path() .'/images/Empresas/Empresa'.$empresa->id;
        $imagen->move($path,$nombre_imagen);
      }


      $empresa->nombre = $request->nombre = $request->nombre;
      $empresa->nit = $request->nit;
      $empresa->telefono=$request->telefono?$request->pref." ".$request->telefono:$request->telefono;
      $empresa->moneda = $request->moneda;
      $empresa->codigo = $request->codigo;
      $empresa->web = $request->web;
      $empresa->precision = $request->precision;
      $empresa->sep_dec = $request->sep_dec;
      $empresa->tipo_persona = $request->tipo_persona;
      $empresa->tip_iden = $request->tip_iden;
      $empresa->direccion = $request->direccion;
      $empresa->email = $request->email=strtolower($request->email);
      $empresa->sms_gateway = $request->sms_gateway;
      $empresa->device_id = $request->device_id;
      $empresa->updated_at  = Carbon::now();
      
      $empresa->dv = $request->dvoriginal;
      $empresa->fe_resolucion = $request->test_resolucion;

      $empresa->fk_idpais = $request->pais;
      $empresa->fk_iddepartamento = $request->departamento;
      $empresa->fk_idmunicipio    = $request->municipio;
      $empresa->cod_postal        = $request->cod_postal;
      $empresa->tipo_fac = ($request->tipofactura) ? $request->tipofactura : '';

      //Comprobacion de activación factura electronica
      //En caso que no hayan datos dentro del campo fe_resolucion de la BD
      //Se le asignará el valor del formulario y se emitirá la notificación
      $modalFe = false;
      if(isset($request->test_resolucion)){
          if(empty($FeTest)){
              $this->enviar($request->test_resolucion);
              $modalFe = true;
          }
      }

      $resp_empresa = DB::table('empresa_responsabilidad')->where('id_empresa','=',$empresa->id)->get();
              if(count($resp_empresa) >= 1){
                DB::table('empresa_responsabilidad')->where('id_empresa','=',$empresa->id)->delete();
              }

      if($request->tip_responsabilidad){
        Log::debug('RESPON');
        Log::debug($request->tip_responsabilidad);
          foreach($request->tip_responsabilidad as $key => $valor){
              
                Log::debug($valor);
               DB::table('empresa_responsabilidad')->insert(
                   [
                   'id_empresa' => $empresa->id,
                   'id_responsabilidad' => $valor
                   ]
               );
          }

      }


      $empresa->save();
      Log::debug($empresa);

   if ($empresa->estado_dian == 0) {
        //$this->getGenerarValidacion();
        //$this->generateCreditNote();
        //$this->generateDebitNote();
   }

    $mensaje='Se ha modificado exitosamente los datos de la empresa';

    return redirect('empresa/configuracion/create')->with('success', $mensaje)->with('modalFe', $modalFe );
  }

  /**
  * Vita para ver los datos
  */
  public function miusuario(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Editar mi Usuario', 'icon' =>'']);
    $usuario = User::find(Auth::user()->id);
    return view('configuracion.miusuario')->with(compact('usuario'));

  }

  /**
  * Modificar las numeraciones registradas
  * @param Request $request
  * @return view
  */
  public function miusuario_store(Request $request){

    $usuario =User::find(Auth::user()->id);
    if ($request->imagenperfil) {
      $img = $request->input('imagenperfil');
      $baseFromJavascript = $img;
      // Remover la parte de la cadena de texto que no necesitamos (data:image/png;base64,)
      // y usar base64_decode para obtener la información binaria de la imagen
      $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $baseFromJavascript));

      //CREAR LA CARPETA
      $destinationPath = public_path('images/Empresas/Empresa'.Auth::user()->empresa."/usuarios");
      // echo $destinationPath;
      // die;
      $filepath = $destinationPath . '/' . $usuario->nro . '.png'; // or image.jpg
      $usuario->image = $usuario->nro . '.png';
      $usuario->save();
      // Finalmente guarda la imágen en el directorio especificado y con la informacion dada
      if (!file_exists($destinationPath) ) {
         mkdir($destinationPath, 0777, true);
      }

      file_put_contents($filepath, $data);
      return redirect('empresa/configuracion/miusuario');

    }
    if ($request->nombres) {
      $errors= (object) array();
      $error =User::where('email', $request->email)->where('id', '<>', $usuario->id)->get();
      if (count($error)>0) {
        $errors->email='El correo electrónico ya se encuentra registrado para otro usuario';
        return back()->withErrors($errors)->withInput();
      }

      $error =User::where('username', $request->username)->where('id', '<>', $usuario->id)->get();
      if (count($error)>0) {
        $errors->username='El Nombre de Usuario ya se encuentra registrado para otro usuario';
        return back()->withErrors($errors)->withInput();
      }

      if ($request->changepass) {
        if (!Hash::check($request->pass_actual, Auth::user()->password)) {
          $errors->pass_actual='Error en la contraseña';
          return back()->withErrors($errors)->withInput();
        }
        $usuario->password  = bcrypt($request->password);
      }
      $usuario->email = $request->email;
      $usuario->nombres = $request->nombres;
      $usuario->username = $request->username=strtolower($request->username);
      $usuario->save();
      $mensaje='Se ha modificado satisfactoriamente el usuario';
      return redirect('empresa/configuracion/miusuario')->with('success', $mensaje);
    }

    return redirect('empresa/configuracion/miusuario');
  }



  /**
  * Vita para ver los datos
  */
  public function datos(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Configuración de Facturas de Venta', 'icon' =>'']);
    $empresa = Empresa::find(Auth::user()->empresa);
    return view('configuracion.datos')->with(compact('empresa'));

  }

  /**
  * Modificar las numeraciones registradas
  * @param Request $request
  * @return view
  */
  public function datos_store(Request $request){
     if (!$request->edo_cuenta_fact) {
       $request->edo_cuenta_fact=0;
     }
             if(Auth::user()->empresa == 28){
            //dd($request->all());
        }
    $empresa = Empresa::find(Auth::user()->empresa);
    $empresa->terminos_cond=$request->terminos_cond;
    $empresa->notas_fact=$request->notas_fact;
    $empresa->edo_cuenta_fact=$request->edo_cuenta_fact;
    $empresa->tirilla = !$request->tirilla ? 0 : 1;
    $empresa->save();
    $mensaje='Se ha modificado satisfactoriamente la configuración de facturas de venta';
    return redirect('empresa/configuracion/datos')->with('success', $mensaje);
  }


  /**
  * Modificar las numeraciones registradas
  * @param Request $request
  * @return view
  */
  public function numeraciones_store(Request $request){

      //Tomamos el tiempo en el que se crea el registro
        Session::put('posttimer', Numeracion::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
       return redirect('empresa/configuracion/numeraciones')->with('success', $mensaje);
  }

    if ($request->caja) {
      $request->validate([
        'caja' => 'required|numeric',
        'pago' => 'required|numeric',
        'credito' => 'required|numeric',
        'remision' => 'required|numeric',
        'cotizacion' => 'required|numeric',
        'orden' => 'required|numeric',
      ]);

      $numeracion = Numeracion::where('empresa',Auth::user()->empresa)->first();
      $numeracion->caja=$request->caja;
      $numeracion->cajar=$request->cajar;
      $numeracion->pago=$request->pago;
      $numeracion->credito=$request->credito;
      $numeracion->remision=$request->remision;
      $numeracion->cotizacion=$request->cotizacion;
      $numeracion->orden=$request->orden;
      $numeracion->save();
    }
    else{
      $request->validate([
        'nombre' => 'required',
        'inicio' => 'required|numeric',
        'preferida' => 'required|numeric'
      ]);

      //Tipo de numeracion_factura, 1=estandar, 2=DIAN
      $tipo = 1;
      if($request->tipo == 2){$tipo=2;}
      
      if ($request->preferida==1) {
        DB::table('numeraciones_facturas')
        ->where('empresa', Auth::user()->empresa)
        ->where('tipo',$tipo)
        ->update(['preferida' => 0]);
      }


      $numeracion=new NumeracionFactura;
      $numeracion->nombre=$request->nombre;
      $numeracion->prefijo=$request->prefijo;
      $numeracion->inicio=$request->inicio;
      $numeracion->inicioverdadero = $request->inicio;
      $numeracion->final=$request->final;
      if ($request->desde) {
        $numeracion->desde=Carbon::parse($request->desde)->format('Y-m-d');
      }
      if ($request->hasta) {
        $numeracion->hasta=Carbon::parse($request->hasta)->format('Y-m-d');
      }
      $numeracion->preferida=$request->preferida;
      $numeracion->nroresolucion=$request->nroresolucion;
      $numeracion->resolucion=$request->resolucion;
      $numeracion->empresa=Auth::user()->empresa;
      $numeracion->tipo = $tipo;
      $numeracion->save();

      $mensaje='Se ha creado satisfactoriamente la numeración';
      return redirect('empresa/configuracion/numeraciones')->with('success', $mensaje)->with('numeracion_id', $numeracion->id);
    }

    $mensaje='Se ha creado satisfactoriamente la numeración';
    return redirect('empresa/configuracion/numeraciones')->with('success', $mensaje);
  }

  /**
  * Formulario para crear un nueva numeracion
  * @return view
  */
  public function numeraciones_create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nueva Numeración', 'icon' =>'']);
    return view('configuracion.numeracion.create');
  }

    /**
  * Formulario para modificar los datos de una numeracion
  * @param int $id
  * @return view
  */
  public function numeraciones_edit($id){
    $this->getAllPermissions(Auth::user()->id);
  $numeracion = NumeracionFactura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
  if ($numeracion) {
    view()->share(['title' => 'Modificar Numeración', 'icon' =>'']);
    return view('configuracion.numeracion.edit')->with(compact('numeracion'));
  }
  return redirect('empresa/configuracion/numeraciones/dian')->with('success', 'No existe un registro con ese id');
}

  /**
  * Modificar los datos de la numeracion
  * @param Request $request
  * @return redirect
  */
  public function numeraciones_update(Request $request, $id){
    $numeracion = NumeracionFactura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($numeracion) {
      $request->validate([
        'nombre' => 'required',
        'inicio' => 'required|numeric',
        'preferida' => 'required|numeric'
      ]);
      if ($request->preferida==1) {
        DB::table('numeraciones_facturas')
        ->where('empresa', Auth::user()->empresa)
        ->where('tipo',1)
        ->where('id', '<>',$numeracion->id)
        ->update(['preferida' => 0]);
      }
      $numeracion->nombre=$request->nombre;
      $numeracion->prefijo=$request->prefijo;
      $numeracion->inicioverdadero=$request->inicioverdadero;
      $numeracion->inicio=$request->inicio;
      $numeracion->final=$request->final;

      if ($request->desde) { $numeracion->desde=Carbon::parse($request->desde)->format('Y-m-d');  }
      else{ $numeracion->desde=null; }
      if ($request->hasta) { $numeracion->hasta=Carbon::parse($request->hasta)->format('Y-m-d'); }
      else{ $numeracion->hasta=null; }
      $numeracion->preferida=$request->preferida;
      $numeracion->nroresolucion=$request->nroresolucion;
      $numeracion->resolucion=$request->resolucion;
      $numeracion->save();

      $mensaje='Se ha modificado satisfactoriamente la numeración';
      return redirect('empresa/configuracion/numeraciones')->with('success', $mensaje)->with('numeracion_id', $numeracion->id);

    }
    return redirect('empresa/configuracion/numeraciones')->with('success', 'No existe un registro con ese id');
  }

  public function numeraciones_dian_store(Request $request){
    //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', Numeracion::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
      return redirect('empresa/configuracion/numeraciones')->with('success', $mensaje);
    }

    $request->validate([
        'nombre' => 'required',
        'inicio' => 'required|numeric',
        'preferida' => 'required|numeric'
      ]);

      //Tipo de numeracion_factura, 1=estandar, 2=DIAN
      $tipo = 1;
      if($request->tipo == 2){$tipo=2;}
      
    if ($request->preferida==1) {
      DB::table('numeraciones_facturas')
      ->where('empresa', Auth::user()->empresa)
      ->where('tipo',$tipo)
      ->update(['preferida' => 0]);
    }


      $numeracion=new NumeracionFactura;
      $numeracion->nombre=$request->nombre;
      $numeracion->prefijo=$request->prefijo;
      $numeracion->inicio=$request->inicio;
      $numeracion->inicioverdadero = $request->inicio;
      $numeracion->final=$request->final;
      if ($request->desde) {
        $numeracion->desde=Carbon::parse($request->desde)->format('Y-m-d');
      }
      if ($request->hasta) {
        $numeracion->hasta=Carbon::parse($request->hasta)->format('Y-m-d');
      }
      $numeracion->preferida=$request->preferida;
      $numeracion->nroresolucion=$request->nroresolucion;
      $numeracion->resolucion=$request->resolucion;
      $numeracion->empresa=Auth::user()->empresa;
      $numeracion->tipo = $tipo;
      $numeracion->save();

      $mensaje='Se ha creado satisfactoriamente la numeración';
      return redirect('empresa/configuracion/numeraciones/dian')->with('success', $mensaje)->with('numeracion_id', $numeracion->id);

      $mensaje='Se ha creado satisfactoriamente la numeración';
      return redirect('empresa/configuracion/numeraciones/dian')->with('success', $mensaje);
  }

   /**
  * Formulario para crear un nueva numeracion dian
  * @return view
  */
  public function numeraciones_dian_create(){
    $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nueva Numeración', 'icon' =>'']);
    return view('configuracion.numeracion-dian.create');
  }

  /**
  * Formulario para modificar los datos de una numeracion DIAN
  * @param int $id
  * @return view
  */
  public function numeraciones_dian_edit($id){
    $this->getAllPermissions(Auth::user()->id);
    $numeracion = NumeracionFactura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($numeracion) {
      view()->share(['title' => 'Modificar Numeración', 'icon' =>'']);
      return view('configuracion.numeracion-dian.edit')->with(compact('numeracion'));
    }
    return redirect('empresa/configuracion/numeraciones/dian')->with('success', 'No existe un registro con ese id');
}

  /**
  * Modificar los datos de la numeracion DIAN
  * @param Request $request
  * @return redirect
  */
  public function numeraciones_dian_update(Request $request, $id){
    $numeracion = NumeracionFactura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($numeracion) {
      $request->validate([
        'nombre' => 'required',
        'inicio' => 'required|numeric',
        'preferida' => 'required|numeric'
      ]);
      if ($request->preferida==1) {
        DB::table('numeraciones_facturas')
        ->where('empresa', Auth::user()->empresa)
        ->where('tipo',2)
        ->where('id', '<>',$numeracion->id)
        ->update(['preferida' => 0]);
      }
      $numeracion->nombre=$request->nombre;
      $numeracion->prefijo=$request->prefijo;
      $numeracion->inicioverdadero=$request->inicioverdadero;
      $numeracion->inicio=$request->inicio;
      $numeracion->final=$request->final;

      if ($request->desde) { $numeracion->desde=Carbon::parse($request->desde)->format('Y-m-d');  }
      else{ $numeracion->desde=null; }
      if ($request->hasta) { $numeracion->hasta=Carbon::parse($request->hasta)->format('Y-m-d'); }
      else{ $numeracion->hasta=null; }
      $numeracion->preferida=$request->preferida;
      $numeracion->nroresolucion=$request->nroresolucion;
      $numeracion->resolucion=$request->resolucion;
      $numeracion->save();

      $mensaje='Se ha modificado satisfactoriamente la numeración';
      return redirect('empresa/configuracion/numeraciones/dian')->with('success', $mensaje)->with('numeracion_id', $numeracion->id);

    }
    return redirect('empresa/configuracion/numeraciones/dian')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Formulario para eliminar la numeracion
  * @param int $id
  * @return view
  */
  public function numeraciones_destroy($id){
    $numeracion = NumeracionFactura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($numeracion) {

      if ($numeracion->usado()==0) {
        $numeracion->delete();
        return redirect('empresa/configuracion/numeraciones')->with('success', 'Se ha eliminado correctamente la numeración');
      }
    }
    return redirect('empresa/configuracion/numeraciones')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Formulario para cambiar el estatus de la numeracion
  * @param int $id
  * @return view
  */
  public function numeraciones_act_desc($id){

    $numeracion = NumeracionFactura::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($numeracion) {
        if ($numeracion->estado==1) {

          $mensaje='Se ha desactivado la numeración';
          $numeracion->estado=0;
          $numeracion->update();
        }
        else{
          $mensaje='Se ha activado la numeración';
          $numeracion->estado=1;
          $numeracion->update();
        }
      return redirect('empresa/configuracion/numeraciones')->with('success', $mensaje)->with('numeracion_id', $numeracion->id);
    }
    return redirect('empresa/configuracion/numeraciones')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Vita para ver los terminos de pago
  */
  public function terminos(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Términos de Pago']);
    $terminos=TerminosPago::where('empresa',Auth::user()->empresa)->get();
    return view('configuracion.terminos.index')->with(compact('terminos'));
  }

  /**
  * Formulario para crear un nuevo términos de pago
  * @return view
  */
  public function terminos_create(){
      $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nuevo Término de Pago', 'icon' =>'']);
    return view('configuracion.terminos.create');
  }

  /**
  * Registrar un nuevo términos de pago
  * @param Request $request
  * @return redirect
  */
  public function terminos_store(Request $request){

      //Tomamos el tiempo en el que se crea el registro
    Session::put('posttimer', TerminosPago::where('empresa',auth()->user()->empresa)->get()->last()->created_at);
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
    return redirect('empresa/configuracion/terminos')->with('success', $mensaje);
    }

    $request->validate([
          'nombre' => 'required|max:200',
          'dias' => 'required|numeric'
    ]);
    $termino = new TerminosPago;
    $termino->empresa=Auth::user()->empresa;
    $termino->nombre=$request->nombre;
    $termino->dias=$request->dias;
    $termino->save();

    $mensaje='Se ha creado satisfactoriamente el término de pago';
    return redirect('empresa/configuracion/terminos')->with('success', $mensaje)->with('termino_id', $termino->id);
  }

   /**
  * Formulario para modificar los datos de un  términos de pago
  * @param int $id
  * @return view
  */
  public function terminos_edit($id){
      $this->getAllPermissions(Auth::user()->id);
    $termino = TerminosPago::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($termino) {
      view()->share(['title' => 'Modificar Término de Pago', 'icon' =>'']);
      return view('configuracion.terminos.edit')->with(compact('termino'));
    }
    return redirect('empresa/configuracion/terminos')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos del banco
  * @param Request $request
  * @return redirect
  */
  public function terminos_update(Request $request, $id){
    $termino = TerminosPago::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($termino) {
      $request->validate([
            'nombre' => 'required|max:200',
            'dias' => 'required|numeric'
      ]);
      $termino->nombre=$request->nombre;
      $termino->dias=$request->dias;
      $termino->save();
      $mensaje='Se ha modificado satisfactoriamente el término de pago';
      return redirect('empresa/configuracion/terminos')->with('success', $mensaje)->with('termino_id', $termino->id);

    }
    return redirect('empresa/configuracion/terminos')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Formulario para eliminar el término de pago
  * @param int $id
  * @return view
  */
  public function terminos_destroy($id){
    $termino = TerminosPago::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    if ($termino) {

      if ($termino->usado()==0) {
        $termino->delete();
        return redirect('empresa/configuracion/terminos')->with('success', 'Se ha eliminado correctamente el término de pago');
      }
    }
    return redirect('empresa/configuracion/terminos')->with('success', 'No existe un registro con ese id');
  }

   public function listabusquedaproveedor($prov = null)
  {
    view()->share(['seccion' => 'configuracion', 'title' => 'Proveedores/Clientes', 'icon' =>'fas fa-cogs']);

    if ($prov == null) {
      $proveedores = Contacto::where('tipo_contacto',1)->where('empresa', Auth::user()->empresa)->where('status',1)->get();

      return view('busquedas.proveedores.lista',compact('proveedores'));
    }
    else
    {
      $prov = $this->toarray($prov);
      $proveedores = Contacto::whereIn('id', $prov)->orderBy('id', 'desc')->get();
      //$proveedores = Contacto::whereIn('id', $prov->id)->get();
      return view('busquedas.proveedores.lista',compact('proveedores'));
    }
  }


  public function autocomplete_linea(Request $request)
  {

    $lineas = DB::table('pais')->where('nombre','like', '%'.$request['query'].'%')->get();

        if ($lineas->count() > 0) {
            foreach ($lineas as $linea) {
                echo "<a class='list-group-item list-group-item-action border-1' value='".$linea->codigo."'>".$linea->nombre."</a>";
            }
        }
        else{
            echo "<p class='list-group-item border-1' id='ocultplacaobyclickout'>No hay resultados pero se creará el vehiculo</p>";
        }
  }

  public function form_facturacion(){

      $empresa=Empresa::find(Auth::user()->empresa);

        if ($empresa) {
            if($empresa->form_fe == 0){
                $empresa->form_fe = 1;
                $empresa->save();
                $arrayPost['status']  = 'OK';
                echo json_encode($arrayPost);
                exit;
            }else{
                $empresa->form_fe = 0;
                $empresa->save();
            }
        }

  }

  public function getGenerarValidacion()
  {
      $xml_ = '<?xml version="1.0" encoding="UTF-8"?>
      <Invoice xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sts="dian:gov:co:facturaelectronica:Structures-2-1" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd">
      <ext:UBLExtensions>
      <ext:UBLExtension>
      <ext:ExtensionContent>
      <sts:DianExtensions>
      <sts:InvoiceControl>
      <sts:InvoiceAuthorization>18760000001</sts:InvoiceAuthorization>
      <sts:AuthorizationPeriod>
      <cbc:StartDate>2019-01-19</cbc:StartDate>
      <cbc:EndDate>2030-01-19</cbc:EndDate>
      </sts:AuthorizationPeriod>
      <sts:AuthorizedInvoices>
      <sts:Prefix>SETT</sts:Prefix>
      <sts:From>1</sts:From>
      <sts:To>5000000</sts:To>
      </sts:AuthorizedInvoices>
      </sts:InvoiceControl>
      </sts:DianExtensions>
      </ext:ExtensionContent>
      </ext:UBLExtension>
      </ext:UBLExtensions>
      <cbc:CustomizationID>10</cbc:CustomizationID>
      <cbc:ProfileExecutionID>2</cbc:ProfileExecutionID>
      <cbc:ID>--numf--</cbc:ID>
      <cbc:UUID schemeID="2" schemeName="CUFE-SHA384">--cufe--</cbc:UUID>
      <cbc:IssueDate>--fecha--</cbc:IssueDate>
      <cbc:IssueTime>--horafecha--</cbc:IssueTime>
      <cbc:InvoiceTypeCode>01</cbc:InvoiceTypeCode>
      <cbc:Note>--nota--</cbc:Note>
      <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>
      <cbc:LineCountNumeric>1</cbc:LineCountNumeric>

      <!-- Informacion Emisor  -->
      <cac:AccountingSupplierParty>
      <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
      <cac:Party>
      <cac:PartyName>
      <cbc:Name>--nombre--</cbc:Name>
      </cac:PartyName>
      <cac:PhysicalLocation>
      <cac:Address>
      <cbc:ID>--codeciudad--</cbc:ID>
      <cbc:CityName>--nameciudad--</cbc:CityName>
      <cbc:PostalZone>--codepostal--</cbc:PostalZone>
      <cbc:CountrySubentity>--departamento--</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>--codigodep--</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>--dir--</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:Address>
      </cac:PhysicalLocation>
      <cac:PartyTaxScheme>
      <cbc:RegistrationName>--nombre--</cbc:RegistrationName>
      <cbc:CompanyID schemeID="--dv--" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">--nit--</cbc:CompanyID>
      <cbc:TaxLevelCode listName="05">R-99-PN</cbc:TaxLevelCode>
      <cac:RegistrationAddress>
      <cbc:ID>--codeciudad--</cbc:ID>
      <cbc:CityName>--nameciudad--</cbc:CityName>
      <cbc:PostalZone>--codepostal--</cbc:PostalZone>
      <cbc:CountrySubentity>--departamento--</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>--codigodep--</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>--dir--</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:RegistrationAddress>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:PartyTaxScheme>
      <cac:PartyLegalEntity>
      <cbc:RegistrationName>--nombre--</cbc:RegistrationName>
      <cbc:CompanyID schemeID="--dv--" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">--nit--</cbc:CompanyID>
      <cac:CorporateRegistrationScheme>
      <cbc:ID>SETT</cbc:ID>
      <cbc:Name>1485596</cbc:Name>
      </cac:CorporateRegistrationScheme>
      </cac:PartyLegalEntity>
      <cac:Contact>
      <cbc:ElectronicMail>--mail--</cbc:ElectronicMail>
      </cac:Contact>
      </cac:Party>
      </cac:AccountingSupplierParty>
      <!-- /Informacion Emisor  -->

      <!-- Informacion Receptor  -->
      <cac:AccountingCustomerParty>
      <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
      <cac:Party>
      <cac:PartyName>
      <cbc:Name>Julian Rios</cbc:Name>
      </cac:PartyName>
      <cac:PhysicalLocation>
      <cac:Address>
      <cbc:ID>05001</cbc:ID>
      <cbc:CityName>Medellín</cbc:CityName>
      <cbc:PostalZone>050030</cbc:PostalZone>
      <cbc:CountrySubentity>Antioquia</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>05</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>CR 9 A N0 99 - 07 OF 802</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:Address>
      </cac:PhysicalLocation>
      <cac:PartyTaxScheme>
      <cbc:RegistrationName>Julian SAS</cbc:RegistrationName>
      <cbc:CompanyID schemeID="1" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">1128431331</cbc:CompanyID>
      <cbc:TaxLevelCode listName="05">R-99-PN</cbc:TaxLevelCode>
      <cac:RegistrationAddress>
      <cbc:ID>05001</cbc:ID>
      <cbc:CityName>Medellin</cbc:CityName>
      <cbc:PostalZone>050030</cbc:PostalZone>
      <cbc:CountrySubentity>Antioquia</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>05</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>CR 9 A N0 99 - 07 OF 802</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:RegistrationAddress>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:PartyTaxScheme>
      <cac:PartyLegalEntity>
      <cbc:RegistrationName>Julian SAS</cbc:RegistrationName>
      <cbc:CompanyID schemeID="1" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">1128431231</cbc:CompanyID>
      <cac:CorporateRegistrationScheme>
      <cbc:Name>1485596</cbc:Name>
      </cac:CorporateRegistrationScheme>
      </cac:PartyLegalEntity>
      <cac:Contact>
      <cbc:ElectronicMail>julianrp89@gmail.com</cbc:ElectronicMail>
      </cac:Contact>
      </cac:Party>
      </cac:AccountingCustomerParty>
      <!-- /Informacion Receptor  -->
      <!-- Medio de pago -->
      <cac:PaymentMeans>
      <cbc:ID>1</cbc:ID>
      <cbc:PaymentMeansCode>10</cbc:PaymentMeansCode>
      <cbc:PaymentID>Efectivo</cbc:PaymentID>
      </cac:PaymentMeans>
      <!-- /Medio de pago -->
      <cac:TaxTotal>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cbc:RoundingAmount currencyID="COP">0.0</cbc:RoundingAmount>
      <cac:TaxSubtotal>
      <cbc:TaxableAmount currencyID="COP">100000.00</cbc:TaxableAmount>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cac:TaxCategory>
      <cbc:Percent>19.00</cbc:Percent>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:TaxCategory>
      </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:LegalMonetaryTotal>
      <cbc:LineExtensionAmount currencyID="COP">100000.00</cbc:LineExtensionAmount>
      <cbc:TaxExclusiveAmount currencyID="COP">100000.00</cbc:TaxExclusiveAmount>
      <cbc:TaxInclusiveAmount currencyID="COP">119000.00</cbc:TaxInclusiveAmount>
      <cbc:PayableAmount currencyID="COP">119000.00</cbc:PayableAmount>
      </cac:LegalMonetaryTotal>
      <!-- Linea de Detalles -->
      <cac:InvoiceLine>
      <cbc:ID>1</cbc:ID>
      <cbc:InvoicedQuantity unitCode="94">1.00</cbc:InvoicedQuantity>
      <cbc:LineExtensionAmount currencyID="COP">100000.00</cbc:LineExtensionAmount>
      <cac:TaxTotal>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cbc:RoundingAmount currencyID="COP">0.0</cbc:RoundingAmount>
      <cac:TaxSubtotal>
      <cbc:TaxableAmount currencyID="COP">100000.00</cbc:TaxableAmount>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cac:TaxCategory>
      <cbc:Percent>19.00</cbc:Percent>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:TaxCategory>
      </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:Item>
      <cbc:Description>Producto Prueba</cbc:Description>
      <cac:StandardItemIdentification>
      <cbc:ID schemeID="999">prueba123456</cbc:ID>
      </cac:StandardItemIdentification>
      </cac:Item>
      <cac:Price>
      <cbc:PriceAmount currencyID="COP">100000.00</cbc:PriceAmount>
      <cbc:BaseQuantity unitCode="EA">1.00</cbc:BaseQuantity>
      </cac:Price>
      </cac:InvoiceLine>
      <!-- Linea de Detalles -->
      <!-- Información de la Dian -->
      <DATA>
      <UBL21>true</UBL21>
      <Partnership>
      <ID>1128464945</ID>
      <TechKey>fc8eac422eba16e22ffd8c6f94b3f40a6e38162c</TechKey>
      <SetTestID>--fe_resolucion--</SetTestID>
      </Partnership>
      </DATA>
      <!-- /Información de la Dian -->
      </Invoice>';
          $fecha = date('Y-m-d');
          $fechahora = date('H:i:s') . "-05:00";
          $empresa = Empresa::find(auth()->user()->empresa);
          if ($empresa->estado_dian == 0 && preg_match('/^([0-9a-zA-Z]{8}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{12})$/', $empresa->fe_resolucion) == 1) {
              $departamentos  = DB::table('departamentos')->where('id', $empresa->fk_iddepartamento)->first();
              $municipios  = DB::table('municipios')->where('id', $empresa->fk_idmunicipio)->first();
              $validos = [];
              $fallidos = [];
              for ($i = 1; $i <= 5; $i++) {
                  $xml = $xml_;
                  $cu = 'SETT' . $i . $fecha . $fechahora . '100000.000119000.00040.00030.00119000.00' . $empresa->nit . '1128431331fc8eac422eba16e22ffd8c6f94b3f40a6e38162c2';
                  $cufe = hash('sha384', $cu);
                  $numfactura = 'SETT' . $i;
                  $nota = 'Fac-Sett-' . $i;
                  $xml = str_replace('--numf--', $numfactura, $xml);
                  $xml = str_replace('--cufe--', $cufe, $xml);
                  $xml = str_replace('--fe_resolucion--', $empresa->fe_resolucion, $xml);
                  $xml = str_replace('--nit--', $empresa->nit, $xml);
                  $xml = str_replace('--dv--', $empresa->dv, $xml);
                  $xml = str_replace('--mail--', $empresa->email, $xml);
                  $xml = str_replace('--nombre--', $empresa->nombre, $xml);
                  $xml = str_replace('--dir--', $empresa->direccion, $xml);
                  $xml = str_replace('--codepostal--', $empresa->cod_postal, $xml);
                  $xml = str_replace('--fecha--', $fecha, $xml);
                  $xml = str_replace('--horafecha--', $fechahora, $xml);
                  $xml = str_replace('--nota--', $nota, $xml);
                  $xml = str_replace('--codeciudad--', $municipios->codigo_completo, $xml);
                  $xml = str_replace('--nameciudad--', $municipios->nombre, $xml);
                  $xml = str_replace('--departamento--', $departamentos->nombre, $xml);
                  $xml = str_replace('--codigodep--', $departamentos->codigo, $xml);
                  $res = $this->getEnviarDatos($xml);
                  $res = json_decode($res, true);

                  if (!isset($res['statusCode'])) {
                      return 'la DIAN esta presentando problemas';
                  }
                  if ($res['statusCode'] == '200') {
                      $validos[] = ([
                          "statusCode" => $res['statusCode'],
                          "trackId" => $res['trackId'],
                          "uuid" => $res['uuid'],
                          "statusMessage" => $res['statusMessage'],
                          "statusDescription" => $res['statusDescription'],
                          "warnings" => $res['warnings']
                      ]);
                  } else {
                      $fallidos[] = ([

                          "statusCode" => $res['statusCode'],
                          "trackId" => $res['trackId'] ?? null,
                          "uuid" => $res['uuid'] ?? null,
                          "errorMessage" => $res['errorMessage'],
                          "errorReason" => $res['errorReason']
                      ]);
                  }
              }
              $resp = array('validos' => count($validos), 'data_validos' => $validos, 'fallidos' => count($fallidos), 'data_fallidos' => $fallidos);
              $empresa->json_test = json_encode($resp);
              //if(count($validos)>10)$empresa->estado_dian=1;
              $empresa->save();
          } else {
              $resp = array('status' => 200, 'msj' => 'Cliente ya se encuenta validado');
          }
          //$this->generateCreditNote();
  }

  public function generateCreditNote()
  {
      $xml_ = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
      <CreditNote xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sts="dian:gov:co:facturaelectronica:Structures-2-1" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-CreditNote-2.1.xsd">
      <cbc:CustomizationID>10</cbc:CustomizationID>
      <cbc:ProfileExecutionID>2</cbc:ProfileExecutionID>
      <cbc:ID>--numf--</cbc:ID> <!-- Prefijo + Número de documento -->
      <cbc:UUID schemeID="2" schemeName="CUDE-SHA384">--cude--</cbc:UUID>
      <cbc:IssueDate>--fecha--</cbc:IssueDate>
      <cbc:IssueTime>--horafecha--</cbc:IssueTime>
      <cbc:CreditNoteTypeCode>91</cbc:CreditNoteTypeCode>
      <cbc:Note>--nota--</cbc:Note>
      <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>
      <cbc:LineCountNumeric>1</cbc:LineCountNumeric>
      <cac:DiscrepancyResponse>
      <cbc:ReferenceID>Sección de la factura la cual se le aplica la correción</cbc:ReferenceID>
      <cbc:ResponseCode>2</cbc:ResponseCode>
      <cbc:Description>Anulación de factura electrónica</cbc:Description>
      </cac:DiscrepancyResponse>

      <!-- Factura Relacionada -->
      <cac:BillingReference>
      <cac:InvoiceDocumentReference>
      <cbc:ID>SETT6</cbc:ID>
      <cbc:UUID schemeName="CUFE-SHA384">--uuidfact--</cbc:UUID>
      <cbc:IssueDate>--fecharelacionada--</cbc:IssueDate>
      </cac:InvoiceDocumentReference>
      </cac:BillingReference>
      <!-- /Factura Relacionada -->

      <!-- Datos del Emisor -->
      <cac:AccountingSupplierParty>
      <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
      <cac:Party>
      <cac:PartyName>
      <cbc:Name>--nombre--</cbc:Name>
      </cac:PartyName>
      <cac:PhysicalLocation>
      <cac:Address>
      <cbc:ID>--codeciudad--</cbc:ID>
      <cbc:CityName>--nameciudad--</cbc:CityName>
      <cbc:PostalZone>--codepostal--</cbc:PostalZone>
      <cbc:CountrySubentity>--departamento--</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>--codigodep--</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>--dir--</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:Address>
      </cac:PhysicalLocation>
      <cac:PartyTaxScheme>
      <cbc:RegistrationName>--nombre--</cbc:RegistrationName>
      <cbc:CompanyID schemeID="--dv--" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">--nit--</cbc:CompanyID>
      <cbc:TaxLevelCode listName="05">R-99-PN</cbc:TaxLevelCode>
      <cac:RegistrationAddress>
      <cbc:ID>--codeciudad--</cbc:ID>
      <cbc:CityName>--nameciudad--</cbc:CityName>
      <cbc:PostalZone>--codepostal--</cbc:PostalZone>
      <cbc:CountrySubentity>--departamento--</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>--codigodep--</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>--dir--</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:RegistrationAddress>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:PartyTaxScheme>
      <cac:PartyLegalEntity>
      <cbc:RegistrationName>--nombre--</cbc:RegistrationName>
      <cbc:CompanyID schemeID="1" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">--nit--</cbc:CompanyID>
      </cac:PartyLegalEntity>
      <cac:Contact>
      <cbc:ElectronicMail>--mail--</cbc:ElectronicMail>
      </cac:Contact>
      </cac:Party>
      </cac:AccountingSupplierParty>
      <!-- /Datos del Emisor -->

      <!-- Informacion Receptor  -->
      <cac:AccountingCustomerParty>
      <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
      <cac:Party>
      <cac:PartyName>
      <cbc:Name>Julian Rios</cbc:Name>
      </cac:PartyName>
      <cac:PhysicalLocation>
      <cac:Address>
      <cbc:ID>05001</cbc:ID>
      <cbc:CityName>Medellín</cbc:CityName>
      <cbc:PostalZone>050030</cbc:PostalZone>
      <cbc:CountrySubentity>Antioquia</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>05</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>CR 9 A N0 99 - 07 OF 802</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:Address>
      </cac:PhysicalLocation>
      <cac:PartyTaxScheme>
      <cbc:RegistrationName>Julian SAS</cbc:RegistrationName>
      <cbc:CompanyID schemeID="1" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">1128431331</cbc:CompanyID>
      <cbc:TaxLevelCode listName="05">R-99-PN</cbc:TaxLevelCode>
      <cac:RegistrationAddress>
      <cbc:ID>05001</cbc:ID>
      <cbc:CityName>Medellin</cbc:CityName>
      <cbc:PostalZone>050030</cbc:PostalZone>
      <cbc:CountrySubentity>Antioquia</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>05</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>CR 9 A N0 99 - 07 OF 802</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:RegistrationAddress>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:PartyTaxScheme>
      <cac:PartyLegalEntity>
      <cbc:RegistrationName>Julian SAS</cbc:RegistrationName>
      <cbc:CompanyID schemeID="1" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">1128431231</cbc:CompanyID>
      <cac:CorporateRegistrationScheme>
      <cbc:Name>1485596</cbc:Name>
      </cac:CorporateRegistrationScheme>
      </cac:PartyLegalEntity>
      <cac:Contact>
      <cbc:ElectronicMail>julianrp89@gmail.com</cbc:ElectronicMail>
      </cac:Contact>
      </cac:Party>
      </cac:AccountingCustomerParty>
      <!-- /Informacion Receptor  -->

      <!-- Froma de pago -->
      <cac:PaymentMeans>
      <cbc:ID>1</cbc:ID>
      <cbc:PaymentMeansCode>10</cbc:PaymentMeansCode>
      <cbc:PaymentID>Efectivo</cbc:PaymentID>
      </cac:PaymentMeans>
      <!-- /Froma de pago -->

      <!-- Impuestos -->
      <cac:TaxTotal>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cbc:RoundingAmount currencyID="COP">0.0</cbc:RoundingAmount>
      <cac:TaxSubtotal>
      <cbc:TaxableAmount currencyID="COP">100000.00</cbc:TaxableAmount>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cac:TaxCategory>
      <cbc:Percent>19.00</cbc:Percent>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:TaxCategory>
      </cac:TaxSubtotal>
      </cac:TaxTotal>
      <!-- /Impuestos -->

      <!-- Totales de la factura -->
      <cac:LegalMonetaryTotal>
      <cbc:LineExtensionAmount currencyID="COP">100000.00</cbc:LineExtensionAmount>
      <cbc:TaxExclusiveAmount currencyID="COP">100000.00</cbc:TaxExclusiveAmount>
      <cbc:TaxInclusiveAmount currencyID="COP">119000.00</cbc:TaxInclusiveAmount>
      <cbc:PayableAmount currencyID="COP">119000.00</cbc:PayableAmount>
      </cac:LegalMonetaryTotal>
      <!-- /Totales de la factura -->
      <!-- Linea de Detalles -->
      <cac:CreditNoteLine>
      <cbc:ID>1</cbc:ID>
      <cbc:CreditedQuantity unitCode="EA">1.00</cbc:CreditedQuantity>
      <cbc:LineExtensionAmount currencyID="COP">100000.00</cbc:LineExtensionAmount>
      <cac:TaxTotal>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cbc:RoundingAmount currencyID="COP">0.0</cbc:RoundingAmount>
      <cac:TaxSubtotal>
      <cbc:TaxableAmount currencyID="COP">100000.00</cbc:TaxableAmount>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cac:TaxCategory>
      <cbc:Percent>19.00</cbc:Percent>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:TaxCategory>
      </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:Item>
      <cbc:Description>Frambuesas</cbc:Description>
      <cac:StandardItemIdentification>
      <cbc:ID schemeID="999">03222314-7</cbc:ID>
      </cac:StandardItemIdentification>
      </cac:Item>
      <cac:Price>
      <cbc:PriceAmount currencyID="COP">100000.00</cbc:PriceAmount>
      <cbc:BaseQuantity unitCode="EA">1.00</cbc:BaseQuantity>
      </cac:Price>
      </cac:CreditNoteLine>
      <!-- /Linea de Detalles -->

      <!-- Información Provieniente de la DIAN -->
      <DATA>
      <UBL21>true</UBL21>
      <Partnership>
      <ID>1128464945</ID>
      <TechKey>fc8eac422eba16e22ffd8c6f94b3f40a6e38162c</TechKey>
      <SetTestID>--fe_resolucion--</SetTestID>
      </Partnership>
      </DATA>
      <!-- /Información Provieniente de la DIAN -->
      </CreditNote>';

          $fecha = date('Y-m-d');
          $fechahora = date('H:i:s') . "-05:00";
          $empresa = Empresa::find(auth()->user()->empresa);

          if ($empresa->estado_dian == 0 && preg_match('/^([0-9a-zA-Z]{8}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{12})$/', $empresa->fe_resolucion) == 1) {
              $departamentos  = DB::table('departamentos')->where('id', $empresa->fk_iddepartamento)->first();
              $municipios  = DB::table('municipios')->where('id', $empresa->fk_idmunicipio)->first();
              $validos = [];
              $fallidos = [];
              for ($i = 1; $i <= 5; $i++) {
                  $xml = $xml_;
                  $cu = 'NC' . $i . $fecha . $fechahora . '100000.000119000.00040.00030.00119000.00' . $empresa->nit . '1128431331753152';
                  $cude = hash('sha384', $cu);
                  $numfactura = 'NC' . $i;
                  $nota = 'Fac-Sett-' . $i;
                  $xml = str_replace('--numf--', $numfactura, $xml);
                  $xml = str_replace('--cude--', $cude, $xml);
                  $xml = str_replace('--fe_resolucion--', $empresa->fe_resolucion, $xml);
                  $xml = str_replace('--nit--', $empresa->nit, $xml);
                  $xml = str_replace('--dv--', $empresa->dv, $xml);
                  $xml = str_replace('--mail--', $empresa->email, $xml);
                  $xml = str_replace('--nombre--', $empresa->nombre, $xml);
                  $xml = str_replace('--dir--', $empresa->direccion, $xml);
                  $xml = str_replace('--codepostal--', $empresa->cod_postal, $xml);
                  $xml = str_replace('--fecha--', $fecha, $xml);
                  $xml = str_replace('--fecharelacionada--', $fecha, $xml);
                  $xml = str_replace('--uuidfact--', Auth()->user()->empresaObj->firstuuidfact(), $xml);
                  $xml = str_replace('--horafecha--', $fechahora, $xml);
                  $xml = str_replace('--nota--', $nota, $xml);
                  $xml = str_replace('--codeciudad--', $municipios->codigo_completo, $xml);
                  $xml = str_replace('--nameciudad--', $municipios->nombre, $xml);
                  $xml = str_replace('--departamento--', $departamentos->nombre, $xml);
                  $xml = str_replace('--codigodep--', $departamentos->codigo, $xml);
                  $res = $this->getEnviarDatos($xml);
                  $res = json_decode($res, true);
                  if ($res['statusCode'] == '200') {
                      $validos[] = ([
                          "statusCode" => $res['statusCode'],
                          "trackId" => $res['trackId'],
                          "uuid" => $res['uuid'],
                          "statusMessage" => $res['statusMessage'],
                          "statusDescription" => $res['statusDescription'],
                          "warnings" => $res['warnings']
                      ]);
                  } else {
                      $fallidos[] = ([
                          "statusCode" => $res['statusCode'],
                          "trackId" => isset($res['trackId']) ? $res['trackId'] : null,
                          "uuid" => isset($res['uuid']) ? $res['uuid'] : null,
                          "errorMessage" => isset($res['errorMessage']) ? $res['errorMessage'] : null,
                          "errorReason" =>  isset($res['errorReason']) ? $res['errorReason'] : null
                      ]);
                  }
                  //return response()->json($xml);
              }
              $resp = array('validos' => count($validos), 'data_validos' => $validos, 'fallidos' => count($fallidos), 'data_fallidos' => $fallidos);
              $empresa->json_test_creditnote = json_encode($resp);
              //if(count($validos)>5)$empresa->estado_dian=1;
              $empresa->save();
          } else {
              $resp = array('status' => 200, 'msj' => 'Cliente ya se encuenta validado');
          }
          //$this->generateDebitNote();
  }


  public function generateDebitNote()
  {
      $xml_ = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
      <DebitNote xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sts="dian:gov:co:facturaelectronica:Structures-2-1" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-DebitNote-2.1.xsd">
      <cbc:CustomizationID>10</cbc:CustomizationID>
      <cbc:ProfileExecutionID>2</cbc:ProfileExecutionID>
      <cbc:ID>--numf--</cbc:ID>
      <cbc:UUID schemeID="2" schemeName="CUDE-SHA384">--cude--</cbc:UUID>
      <cbc:IssueDate>--fecha--</cbc:IssueDate>
      <cbc:IssueTime>--horafecha--</cbc:IssueTime>
      <cbc:Note>--nota--</cbc:Note>
      <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>
      <cbc:LineCountNumeric>1</cbc:LineCountNumeric>
      <cac:DiscrepancyResponse>
      <cbc:ReferenceID>Sección de la factura la cual se le aplica la correción</cbc:ReferenceID>
      <cbc:ResponseCode>1</cbc:ResponseCode>
      <cbc:Description>Intereses</cbc:Description>
      </cac:DiscrepancyResponse>

      <!-- Factura Relacionada -->
      <cac:BillingReference>
      <cac:InvoiceDocumentReference>
      <cbc:ID>SETT6</cbc:ID>
      <cbc:UUID schemeName="CUFE-SHA384">--uuidfact--</cbc:UUID>
      <cbc:IssueDate>--fecharelacionada--</cbc:IssueDate>
      </cac:InvoiceDocumentReference>
      </cac:BillingReference>
      <!-- /Factura Relacionada -->

      <!-- Datos del Emisor -->
      <cac:AccountingSupplierParty>
      <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
      <cac:Party>
      <cac:PartyName>
      <cbc:Name>--nombre--</cbc:Name>
      </cac:PartyName>
      <cac:PhysicalLocation>
      <cac:Address>
      <cbc:ID>--codeciudad--</cbc:ID>
      <cbc:CityName>--nameciudad--</cbc:CityName>
      <cbc:PostalZone>--codepostal--</cbc:PostalZone>
      <cbc:CountrySubentity>--departamento--</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>--codigodep--</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>--dir--</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:Address>
      </cac:PhysicalLocation>
      <cac:PartyTaxScheme>
      <cbc:RegistrationName>--nombre--</cbc:RegistrationName>
      <cbc:CompanyID schemeID="--dv--" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">--nit--</cbc:CompanyID>
      <cbc:TaxLevelCode listName="05">R-99-PN</cbc:TaxLevelCode>
      <cac:RegistrationAddress>
      <cbc:ID>--codeciudad--</cbc:ID>
      <cbc:CityName>--nameciudad--</cbc:CityName>
      <cbc:PostalZone>--codepostal--</cbc:PostalZone>
      <cbc:CountrySubentity>--departamento--</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>--codigodep--</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>--dir--</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:RegistrationAddress>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:PartyTaxScheme>
      <cac:PartyLegalEntity>
      <cbc:RegistrationName>--nombre--</cbc:RegistrationName>
      <cbc:CompanyID schemeID="1" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">--nit--</cbc:CompanyID>
      </cac:PartyLegalEntity>
      <cac:Contact>
      <cbc:ElectronicMail>--mail--</cbc:ElectronicMail>
      </cac:Contact>
      </cac:Party>
      </cac:AccountingSupplierParty>
      <!-- /Datos del Emisor -->

      <!-- Informacion Receptor  -->
      <cac:AccountingCustomerParty>
      <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID>
      <cac:Party>
      <cac:PartyName>
      <cbc:Name>Julian Rios</cbc:Name>
      </cac:PartyName>
      <cac:PhysicalLocation>
      <cac:Address>
      <cbc:ID>05001</cbc:ID>
      <cbc:CityName>Medellín</cbc:CityName>
      <cbc:PostalZone>050030</cbc:PostalZone>
      <cbc:CountrySubentity>Antioquia</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>05</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>CR 9 A N0 99 - 07 OF 802</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:Address>
      </cac:PhysicalLocation>
      <cac:PartyTaxScheme>
      <cbc:RegistrationName>Julian SAS</cbc:RegistrationName>
      <cbc:CompanyID schemeID="1" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">1128431331</cbc:CompanyID>
      <cbc:TaxLevelCode listName="05">R-99-PN</cbc:TaxLevelCode>
      <cac:RegistrationAddress>
      <cbc:ID>05001</cbc:ID>
      <cbc:CityName>Medellin</cbc:CityName>
      <cbc:PostalZone>050030</cbc:PostalZone>
      <cbc:CountrySubentity>Antioquia</cbc:CountrySubentity>
      <cbc:CountrySubentityCode>05</cbc:CountrySubentityCode>
      <cac:AddressLine>
      <cbc:Line>CR 9 A N0 99 - 07 OF 802</cbc:Line>
      </cac:AddressLine>
      <cac:Country>
      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
      <cbc:Name languageID="es">Colombia</cbc:Name>
      </cac:Country>
      </cac:RegistrationAddress>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:PartyTaxScheme>
      <cac:PartyLegalEntity>
      <cbc:RegistrationName>Julian SAS</cbc:RegistrationName>
      <cbc:CompanyID schemeID="1" schemeName="31" schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)">1128431231</cbc:CompanyID>
      <cac:CorporateRegistrationScheme>
      <cbc:Name>1485596</cbc:Name>
      </cac:CorporateRegistrationScheme>
      </cac:PartyLegalEntity>
      <cac:Contact>
      <cbc:ElectronicMail>julianrp89@gmail.com</cbc:ElectronicMail>
      </cac:Contact>
      </cac:Party>
      </cac:AccountingCustomerParty>
      <!-- /Informacion Receptor  -->

      <!-- Froma de pago -->
      <cac:PaymentMeans>
      <cbc:ID>1</cbc:ID>
      <cbc:PaymentMeansCode>10</cbc:PaymentMeansCode>
      <cbc:PaymentID>Efectivo</cbc:PaymentID>
      </cac:PaymentMeans>
      <!-- /Froma de pago -->

      <!-- Impuestos -->
      <cac:TaxTotal>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cbc:RoundingAmount currencyID="COP">0.0</cbc:RoundingAmount>
      <cac:TaxSubtotal>
      <cbc:TaxableAmount currencyID="COP">100000.00</cbc:TaxableAmount>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cac:TaxCategory>
      <cbc:Percent>19.00</cbc:Percent>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:TaxCategory>
      </cac:TaxSubtotal>
      </cac:TaxTotal>
      <!-- Impuestos -->

      <!-- Totales de la nota debito -->
      <cac:RequestedMonetaryTotal>
      <cbc:LineExtensionAmount currencyID="COP">100000.00</cbc:LineExtensionAmount>
      <cbc:TaxExclusiveAmount currencyID="COP">100000.00</cbc:TaxExclusiveAmount>
      <cbc:TaxInclusiveAmount currencyID="COP">119000.00</cbc:TaxInclusiveAmount>
      <cbc:PayableAmount currencyID="COP">119000.00</cbc:PayableAmount>
      </cac:RequestedMonetaryTotal>
      <!-- /Totales de la nota debito -->

      <!-- Linea de Detalles -->
      <cac:DebitNoteLine>
      <cbc:ID>1</cbc:ID>
      <cbc:DebitedQuantity unitCode="EA">1.00</cbc:DebitedQuantity>
      <cbc:LineExtensionAmount currencyID="COP">100000.00</cbc:LineExtensionAmount>
      <cac:TaxTotal>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cbc:RoundingAmount currencyID="COP">0.0</cbc:RoundingAmount>
      <cac:TaxSubtotal>
      <cbc:TaxableAmount currencyID="COP">100000.00</cbc:TaxableAmount>
      <cbc:TaxAmount currencyID="COP">19000.00</cbc:TaxAmount>
      <cac:TaxCategory>
      <cbc:Percent>19.00</cbc:Percent>
      <cac:TaxScheme>
      <cbc:ID>01</cbc:ID>
      <cbc:Name>IVA</cbc:Name>
      </cac:TaxScheme>
      </cac:TaxCategory>
      </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:Item>
      <cbc:Description>Frambuesas</cbc:Description>
      <cac:StandardItemIdentification>
      <cbc:ID schemeID="999">03222314-7</cbc:ID>
      </cac:StandardItemIdentification>
      </cac:Item>
      <cac:Price>
      <cbc:PriceAmount currencyID="COP">100000.00</cbc:PriceAmount>
      <cbc:BaseQuantity unitCode="EA">1.00</cbc:BaseQuantity>
      </cac:Price>
      </cac:DebitNoteLine>
      <!-- /Linea de Detalles -->

      <!-- Información Provieniente de la DIAN -->
      <DATA>
      <UBL21>true</UBL21>
      <Partnership>
      <ID>1128464945</ID>
      <TechKey>fc8eac422eba16e22ffd8c6f94b3f40a6e38162c</TechKey>
      <SetTestID>--fe_resolucion--</SetTestID>
      </Partnership>
      </DATA>
      <!-- /Información Provieniente de la DIAN -->
      </DebitNote>';
      $fecha = date('Y-m-d');
      $fechahora = date('H:i:s') . "-05:00";
      $empresa = Empresa::find(auth()->user()->empresa);

      if ($empresa->estado_dian == 0 && preg_match('/^([0-9a-zA-Z]{8}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{12})$/', $empresa->fe_resolucion) == 1) {
          $departamentos  = DB::table('departamentos')->where('id', $empresa->fk_iddepartamento)->first();
          $municipios  = DB::table('municipios')->where('id', $empresa->fk_idmunicipio)->first();
          $validos = [];
          $fallidos = [];
          for ($i = 1; $i <= 2; $i++) {
              $xml = $xml_;
              $cu = 'NC' . $i . $fecha . $fechahora . '100000.000119000.00040.00030.00119000.00' . $empresa->nit . '1128431331753152';
              $cude = hash('sha384', $cu);
              $numfactura = 'NC' . $i;
              $nota = 'Fac-Sett-' . $i;
              $xml = str_replace('--numf--', $numfactura, $xml);
              $xml = str_replace('--cude--', $cude, $xml);
              $xml = str_replace('--fe_resolucion--', $empresa->fe_resolucion, $xml);
              $xml = str_replace('--nit--', $empresa->nit, $xml);
              $xml = str_replace('--dv--', $empresa->dv, $xml);
              $xml = str_replace('--mail--', $empresa->email, $xml);
              $xml = str_replace('--nombre--', $empresa->nombre, $xml);
              $xml = str_replace('--dir--', $empresa->direccion, $xml);
              $xml = str_replace('--codepostal--', $empresa->cod_postal, $xml);
              $xml = str_replace('--fecha--', $fecha, $xml);
              $xml = str_replace('--fecharelacionada--', $fecha, $xml);
              $xml = str_replace('--uuidfact--', Auth()->user()->empresaObj->firstuuidfact(), $xml);
              $xml = str_replace('--horafecha--', $fechahora, $xml);
              $xml = str_replace('--nota--', $nota, $xml);
              $xml = str_replace('--codeciudad--', $municipios->codigo_completo, $xml);
              $xml = str_replace('--nameciudad--', $municipios->nombre, $xml);
              $xml = str_replace('--departamento--', $departamentos->nombre, $xml);
              $xml = str_replace('--codigodep--', $departamentos->codigo, $xml);
              $res = $this->getEnviarDatos($xml);
              $res = json_decode($res, true);
              if ($res['statusCode'] == '200') {
                  $validos[] = ([
                      "statusCode" => $res['statusCode'],
                      "trackId" => $res['trackId'],
                      "uuid" => $res['uuid'],
                      "statusMessage" => $res['statusMessage'],
                      "statusDescription" => $res['statusDescription'],
                      "warnings" => $res['warnings']
                  ]);
              } else {
                  $fallidos[] = ([
                      "statusCode" => $res['statusCode'],
                      "trackId" => isset($res['trackId']) ? $res['trackId'] : '',
                      "uuid" => $res['uuid'],
                      "errorMessage" => $res['errorMessage'],
                      "errorReason" => $res['errorReason']
                  ]);
              }
          }
          $resp = array('validos' => count($validos), 'data_validos' => $validos, 'fallidos' => count($fallidos), 'data_fallidos' => $fallidos);
          $empresa->json_test_debitnote = json_encode($resp);
          if (count($validos) > 0 && count($fallidos) > 0) {
              $empresa->estado_dian = 1;
          }
          $empresa->save();

          //Como la ultima tanda que se hace es la de notas debito y silos validos es mayor a cero entonces mandaremos un correo
          //diciendo que el cliente ya ha sido activado
          $emails = $empresa->email;
          $tituloCorreo = "Set de Pruebas Habilitado";
          Mail::send('emails.dian.settestid', compact('empresa'), function ($message) use ($emails, $tituloCorreo) {
              $message->from('info@gestordepartes.net', 'Facturación Electrónica - Network Soft');
              $message->to($emails)->subject($tituloCorreo);
          });
      } else {
          $resp = array('status' => 200, 'msj' => 'Cliente ya se encuenta validado');
      }
  }

  public function checkStatusDian()
  {
      $empresa = Empresa::find(auth()->user()->empresa)->first();

      $contador = 0;
      $jsonfact = json_decode($empresa->json_test, true);
      $jsondebitnote = json_decode($empresa->json_test_creditnote, true);
      $jsoncreditnote = json_decode($empresa->json_test_debitnote, true);


      $contador = $jsonfact['validos'] + $jsoncreditnote['validos'] + $jsondebitnote['validos'];

      if ($contador > 9) {
          $empresa->estado_dian = 1; //Autorizado frente a la Dian;
      } else {
          $empresa->estado_dian = 0; //No autorizado.
      }
  }

  public function getEnviarDatos($xml)
  {
      $xml_base = base64_encode($xml);
      $json = json_encode($xml_base);
      $curl = curl_init();
      curl_setopt_array($curl, array(

          CURLOPT_URL => "https://apivp.efacturacadena.com/staging/vp-hab/documentos/proceso/alianzas",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $json,
          CURLOPT_HTTPHEADER => array(
              "Content-Type: application/json",
              "Postman-Token: 13e97781-32ef-49b7-ad05-3461f465d410",
              "cache-control: no-cache",
              "efacturaAuthorizationToken: 5c6bc925-a4c5-4f72-b398-88b31def04da"
          ),
      ));
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      unset($curl);
      if ($err) {
          return "cURL Error #:" . $err;
      } else {
          return $response;
      }
  }

    public function enviar($clave){

        $empresa    = Empresa::find(Auth::user()->empresa);
        $email      = $empresa->email;
        $data = array(
            'email'=> 'info@gestordepartes.net',
        );
        $tituloCorreo = "Solicitud de facturación electrónica en proceso";
        Mail::send('emails.notificacionFe', compact("clave", "empresa"), function($message) use ($email,$tituloCorreo)
        {
            $message->to($email)->subject($tituloCorreo);
        });

        return "Enviado";
    }
    
    /**
     * * VISTA DE SERVICIOS
     * * @return view
     */
    public function servicios(){
      $this->getAllPermissions(Auth::user()->id);

      view()->share(['title' => 'Servicios']);

      $servicios=Servicio::where('empresa',Auth::user()->empresa)->get();
      return view('configuracion.servicios.index')->with(compact('servicios'));
    }

    /**
    * Formulario para crear un nuevo servicio
    * * @return view
    * */
    public function servicios_create(){
      $this->getAllPermissions(Auth::user()->id);
      view()->share(['title' => 'Nuevo Servicio']);
      return view('configuracion.servicios.create');
    }

    /**
    * Registrar un nuevo servicio
    * @param Request $request
    * @return redirect
    */
    public function servicios_store(Request $request){$request->validate([
        'nombre' => 'required|max:200'
      ]);
      $servicio = new Servicio;
      $servicio->empresa=Auth::user()->empresa;
      $servicio->nombre=$request->nombre;
      $servicio->tiempo=$request->tiempo;
      $servicio->save();

      $mensaje='Se ha creado satisfactoriamente el servicio';
      return redirect('empresa/configuracion/servicios')->with('success', $mensaje)->with('servicio_id', $servicio->id);
    }

    /**
    * Formulario para modificar los datos de un servicio
    * @param int $id
    * @return view
    */
    public function servicios_edit($id){
      $this->getAllPermissions(Auth::user()->id);
      $servicio = Servicio::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
      if ($servicio) {
        view()->share(['title' => 'Modificar Servicio']);
        return view('configuracion.servicios.edit')->with(compact('servicio'));
      }
      return redirect('empresa/configuracion/terminos')->with('danger', 'No existe un registro con ese id');
    }

    /**
    * Modificar los datos de un servicio
    * @param Request $request
    * @return redirect
    */
    public function servicios_update(Request $request, $id){
      $servicio = Servicio::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
      if ($servicio) {
        $request->validate([
              'nombre' => 'required|max:200'
        ]);
        $servicio->nombre=$request->nombre;
        $servicio->tiempo=$request->tiempo;
        $servicio->save();
        $mensaje='Se ha modificado satisfactoriamente el servicio';
        return redirect('empresa/configuracion/servicios')->with('success', $mensaje)->with('servicio_id', $servicio->id);
      }
      return redirect('empresa/configuracion/servicios')->with('danger', 'No existe un registro con ese id');
    }

    /**
    * Formulario para eliminar un servicio
    * @param int $id
    * @return view
    */
    public function servicios_destroy($id){
      $servicio = Servicio::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
      if ($servicio) {
        if ($servicio->usado()==0) {
          $servicio->delete();
          return redirect('empresa/configuracion/servicios')->with('success', 'Se ha eliminado correctamente el servicio');
        }else{
          return redirect('empresa/configuracion/servicios')->with('danger', 'El servicio se encuentra asociado, no es posible eliminarlo.');
        }
      }
      return redirect('empresa/configuracion/servicios')->with('danger', 'No existe un registro con ese id');
    }

    /**
     * Crear Facturación Automática
     */
  public function facturacionAutomatica(Request $request){
    $empresa = Empresa::find(auth()->user()->empresa);

    if ($request->status == 0) {
      $empresa->factura_auto = 1;
      $empresa->save();
      return 1;
    } else {
      $empresa->factura_auto = 0;
      $empresa->save();
      return 0;
    }
  }

  public function limpiarCache(Request $request){
    $empresa = Empresa::find($request->empresa);

    if ($empresa) {
      $empresa->cache = rand();
      $empresa->save();

      $exitCode = Artisan::call('config:clear');
      $exitCode = Artisan::call('cache:clear');
      $exitCode = Artisan::call('view:clear');
      return 1;
    }
  }

  public function configurarOLT(Request $request){
    $empresa = Empresa::find(Auth::user()->empresa);

    if ($empresa) {
      $empresa->adminOLT = $request->adminOLT;
      $empresa->smartOLT = $request->smartOLT;
      $empresa->save();
      return 1;
    }
  }

  public function actDescProrrateo(Request $request){

    $empresa = Empresa::find(Auth::user()->empresa);

    if($empresa){
        if($request->prorrateo == 0){
          $empresa->prorrateo = 1;  
        }else{
          $empresa->prorrateo = 0;
        }
        $empresa->save();
        return $empresa->prorrateo;
    }
  }

  public function actDescEfecty(Request $request){

    $empresa = Empresa::find(Auth::user()->empresa);

    if($empresa){
        if($request->efecty == 0){
          $empresa->efecty = 1;
        }else{
          $empresa->efecty = 0;
        }
        $empresa->save();
        return $empresa->efecty;
    }
  }

  // cambia el estado de la nomina electronica de una empresa
  public function estadoNomina()
  {
      $empresa = auth()->user()->empresaObj;
      $user_master = User::where('empresa', $empresa->id)->first()->id;
      $text = '';

      

      if ($empresa->nomina == 1) {
        DB::table('empresas')->where('id',$empresa->id)->update(['nomina' => 0]);
          return response()->json([
              'success' => true,
              'message' => 'La nómina ha sido desactivada',
              'text'    => $text,
              'nomina' => 0
          ]);
      } elseif ($empresa->fresh()->nomina == 0) {
        DB::table('empresas')->where('id',$empresa->id)->update(['nomina' => 1]);

          // $permisos = DB::table('permisos_usuarios')->where('id_permiso', 157)->where('id_usuario', $user_master)->get()->count();
          // if ($permisos == 0) {
          //     $permisosAccesos = DB::table('permisos_botones')->where('id_modulo', 17)->select('id')->get();

          //     foreach ($permisosAccesos as $permiso) {
          //         DB::table('permisos_usuarios')->insert(['id_usuario' => $user_master, 'id_permiso' => $permiso->id]);
          //     }
          // }

          $suscripcion = SuscripcionNomina::where('id_empresa', $empresa->id)->first();

          if (!$suscripcion) {
              $suscripcion                  = new SuscripcionNomina();
              $suscripcion->id_empresa      = $empresa->id;
              $suscripcion->fec_inicio      = date('Y-m-d');
              $suscripcion->fec_vencimiento = date('Y-m-d', strtotime(Carbon::now() . "+ 15 days"));
              $suscripcion->fec_corte       = date('Y-m-d', strtotime(Carbon::now() . "+ 15 days"));
              $suscripcion->created_at      = Carbon::now();
              $suscripcion->save();
              $text = 'RECUERDE: La nómina estará habilitada por 15 días de manera gratuita.';
          }

          return response()->json([
              'success' => true,
              'message' => 'La nómina ha sido activada',
              'text'    => 'Recuerde habilitar los persmisos a través del módulo configuración > usuario y en el candado de permisos',
              'nomina'  => 1
          ]);
      }
  }

  public function numeracion_nomina_create()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Crear numeración Nomina Electrónica']);
        return view('configuracion.numeracion-nomina.create');
    }

    public function numeracion_nomina_store(Request $request)
    {
        $empresa = auth()->user()->empresaObj;

        $request->validate([
            'nombre' => 'required',
            'inicio' => 'required',
        ]);

        if (NumeracionFactura::where('nombre', $request->nombre)
            ->where('nomina', 1)
            ->where('empresa', $empresa->id)
            ->first() 
        ) {
            return back()
            ->withInput()
            ->withErrors(['nombre' => 'La numeración ya existe. Cambie el nombre o borre la anterior numeración']);
        }

        $numeracion = new NumeracionFactura();
        $numeracion->nombre = $request->nombre;
        $numeracion->prefijo = $request->prefijo;
        $numeracion->inicio = $request->inicio;
        $numeracion->inicioverdadero = $request->inicio;
        $numeracion->preferida = $request->preferida;
        $numeracion->empresa = $empresa->id;
        $numeracion->nomina = 1;
        if ($numeracion->preferida) {
            DB::table('numeraciones_facturas')->where('nomina', 1)->where('tipo_nomina',$request->tipo_nomina)->where('preferida', 1)->update(['preferida' => 0]);
        }
        $numeracion->tipo_nomina = $request->tipo_nomina;
        $numeracion->save();

        $mensaje = 'Se ha creado satisfactoriamente la numeración';
        return redirect()->route('numeraciones_nomina.index')->with('success', $mensaje);
    }

    public function numeracion_nomina_index()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Numeraciones de la nomina electrónica']);

        $numeraciones = NumeracionFactura::where('empresa', auth()->user()->empresa)->where('nomina', 1)->get();
        return view('configuracion.numeracion-nomina.index')->with(compact('numeraciones'));
    }

    public function numeracion_nomina_edit($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Editar numeracion Nomina Electrónica']);

        $empresa = auth()->user()->empresaObj;
        $numeracion = NumeracionFactura::where('id', $id)->where('empresa', $empresa->id)->first();

        return view('configuracion.numeracion-nomina.edit', compact('numeracion', 'empresa'));
    }

    public function numeracion_nomina_update(Request $request)
    {

        $empresa = $request->user()->empresaObj;
        $numeracion = NumeracionFactura::where('id', $request->numeracion)->where('empresa', $empresa->id)->first();

        if (NumeracionFactura::where('nombre', $request->nombre)
            ->where('nomina', 1)
            ->where('id', '<>', $numeracion->id)
            ->where('empresa', $empresa->id)
            ->first()
        ) {
            return back()
            ->withErrors(['nombre' => 'La numeración ya existe. Cambie el nombre o borre la anterior numeración']);
        }

        if ((bool) $request->preferida) {

            NumeracionFactura::where('preferida', 1)->where('empresa', $empresa->id)
                ->where('id', '<>',  $numeracion->id)
                ->where('tipo_nomina',$request->tipo_nomina)
                ->where('nomina',1)
                ->update(['preferida' => 0]);

            $numeracion->fresh();
            $numeracion->preferida = 1;
        }

        
        $numeracion->nombre = $request->nombre;
        $numeracion->prefijo = $request->prefijo;
        $numeracion->inicio = $request->inicio;
        $numeracion->tipo_nomina = $request->tipo_nomina;
        $numeracion->inicioverdadero = $request->inicioverdadero;
        $numeracion->empresa = $empresa->id;

        $numeracion->update();


        $mensaje = 'Se actualizó la numeración correctamente';
        return redirect()->route('numeraciones_nomina.index')->with('success', $mensaje);
    }

    public function numeracion_nomina_destroy($id)
    {
        $numeracion = NumeracionFactura::find($id);
        $numeracion->delete();
        $mensaje = 'Se elminó la numeración correctamente';
        return redirect()->route('numeraciones_nomina.index')->with('success', $mensaje)->with('numeracion_id', $numeracion->id);
    }

    public function numeraciones_nomina_act_desc($id)
    {
        $numeracion = NumeracionFactura::where('empresa', auth()->user()->empresa)->where('id', $id)->first();
        if ($numeracion) {
            if ($numeracion->estado == 1) {
                $mensaje = 'Se ha desactivado la numeración';
                $numeracion->estado = 0;
                $numeracion->update();
            } else {
                $mensaje = 'Se ha activado la numeración';
                $numeracion->estado = 1;
                $numeracion->update();
            }
            return redirect()->route('numeraciones_nomina.index')->with('success', $mensaje)->with('numeracion_id', $numeracion->id);
        }
        return redirect()->route('numeraciones_nomina.index')->with('success', 'No existe un registro con ese id');
    }

    /**
     * Tabla principal para configuración de calculos fijos.
     *
     * @return view
     */
    function calculos_nomina()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Configuración de cálculos fijos']);

        $calculos = NominaConfiguracionCalculos::where('fk_idempresa', auth()->user()->empresa)->get();

        /* >>> SCRIPT PARA AGREGAR LOS CALCULOS FIJOS A TODAS LAS EMPRESAS <<< */
        // $empresas = Empresa::all();

        // foreach($empresas as $empresa){
        //     foreach($calculos as $calculo){
        //         $nuevoCalculo = new NominaConfiguracionCalculos;
        //         $nuevoCalculo->nro = $calculo->nro;
        //         $nuevoCalculo->nombre = $calculo->nombre;
        //         $nuevoCalculo->tipo = $calculo->tipo;
        //         $nuevoCalculo->simbolo = $calculo->simbolo;
        //         $nuevoCalculo->valor = $calculo->valor;
        //         $nuevoCalculo->observaciones = $calculo->observaciones;
        //         $nuevoCalculo->fk_idempresa = $calculo->fk_idempresa;
        //         $nuevoCalculo->save();
        //     }
        // }

        return view('configuracion.calculos-nomina.index')->with(compact('calculos'));
    }

      function calculos_nomina_editcalculo($id)
    {
        $calculo = NominaConfiguracionCalculos::find($id);
        if ($calculo) {
            $calculo->valor = round($calculo->valor);
            return response()->json($calculo);
        }
    }

    function storecalculo(Request $request)
    {

        $calculo = NominaConfiguracionCalculos::find($request->id);

        if ($calculo) {
            $calculo->valor = floatval($request->valor);
            $calculo->save();
            $calculo->valorFormateado = $calculo->valor();

            /* actualizar nomina de las personas en su ultimo periodo */
            $personas = Persona::where('fk_empresa', auth()->user()->empresa)->get();
            foreach ($personas as $persona) {
                //PersonasController::nominaPersona($persona);
                $persona->refrescarUltimaNomina();
            }

            return response()->json($calculo);
        } else {
            return response()->json(false);
        }
    }

    /**
     * Habilitar/Deshabilitar Emisión de Nómina por la DIAN
     */
    public function nominaDian(Request $request)
    {
        $empresa = Empresa::find(auth()->user()->empresa);

        if ($request->status == 0) {
            $empresa->nomina_dian = 1;
            $empresa->save();
            return 1;
        } else {
            $empresa->nomina_dian = 0;
            $empresa->save();
            return 0;
        }
    }
}
