<?php
namespace App\Http\Controllers;

use App\Contacto;
use App\Impuesto;
use App\Retencion;
use App\SuscripcionPago;
use App\TipoEmpresa;
use Illuminate\Http\Request;
use App\Empresa; use App\User; use App\TipoIdentificacion;  use Carbon\Carbon;  use Mail;
use Validator; use Illuminate\Validation\Rule;  use Auth; use App\TerminosPago; use App\Numeracion;
use bcrypt; use App\Categoria;  use DB;
use App\Model\Inventario\ListaPrecios;
use App\Banco;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\Inventario;
use App\CamposExtra;
use App\NumeracionFactura;
use App\SuscripcionNomina;


class EmpresasController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['inicio' => 'master', 'seccion' => 'empresas', 'title' => 'Empresas', 'icon' =>'fa fa-building']);
    }

    public function index(){
        $empresas = Empresa::where('status',1)->get();
        return view('empresas.index')->with(compact('empresas'));
    }

    public function inactivas(){
        view()->share(['migatres' => 'Inactivas', 'migatres_route'=>'master/empresas/inactivas', 'title' => 'Empresas Inactivas']);
        $empresas = Empresa::where('status',0)->get();
        $inactivas=1;
        return view('empresas.index')->with(compact('empresas', 'inactivas'));
    }

    public function show(){}

    public function create(){
        $identificaciones=TipoIdentificacion::all();
        $prefijos=DB::table('prefijos_telefonicos')->get();
        view()->share(['migatres' => 'Nueva Empresa', 'icon' =>'', 'title' => 'Nueva Empresa']);

        return view('empresas.create')->with(compact('identificaciones', 'prefijos'));
    }

    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|unique:empresas',
            'tip_iden' => 'required|exists:tipos_identificacion,id',
            'nit' => 'required',
            'telefono' => 'required',
            'email' => 'required|email|unique:usuarios',
            'direccion' => 'required',
            'username' => 'required|unique:usuarios',
            'password' => 'required',
            'tipo_persona'=>'required',
        ],['tip_iden.exists' => 'Error con el tipo de identificación',
            'nombre.unique' => 'El nombre de la empresa ya se encuentra registrado',
            'email.unique'  => 'El correo electrónico ya se encuentra registrado en el sistema',
            'username.unique'  => 'El nombre de Usuario ya se encuentra registrado en el sistema'
        ]);

        if ($request->logo) {
            $request->validate(
                ['logo'=>'mimes:jpeg,jpg,png| max:100'],
                ['logo.mimes' => 'La extensión del logo debe ser jpeg, jpg, png',
                    'logo.max' => 'El peso máximo para el logo es de 100KB',]
            );

            $imagen = $request->file('logo');
            $nombre_imagen = 'logo.'.$imagen->getClientOriginalExtension();
            $request->logo=$nombre_imagen;
        }

        $empresa = new Empresa;
        $empresa->nombre = $request->nombre;
        $empresa->nit = $request->nit;
        $empresa->logo = $request->logo;
        $empresa->tipo_persona = $request->tipo_persona;
        $empresa->tip_iden = $request->tip_iden;
        $empresa->telefono=$request->telefono?$request->pref." ".$request->telefono:$request->telefono;
        $empresa->direccion = $request->direccion;
        $empresa->email = $request->email=strtolower($request->email);
        $empresa->status = 1;
        $empresa->created_at  = Carbon::now();
        $empresa->updated_at  = Carbon::now();
        $empresa->carrito = $request->carrito;
        $empresa->web = $request->web;
        $empresa->save();

        $numeracion=new Numeracion;
        $numeracion->empresa= $empresa->id;
        $numeracion->save();

        $terminos=TerminosPago::whereNull('empresa')->get();
        foreach ($terminos as $termino) {
            $pago=new TerminosPago;
            $pago->nombre=$termino->nombre;
            $pago->dias=$termino->dias;
            $pago->empresa= $empresa->id;
            $pago->save();
        }

        $categorias=Categoria::whereNull('empresa')->get();
        foreach ($categorias as $categoria) {
            $cat=new Categoria;
            $cat->nombre=$categoria->nombre;
            $cat->empresa=$empresa->id;
            $cat->nro= $categoria->nro;
            $cat->descripcion= $categoria->descripcion;
            $cat->save();
        }

        /*
        * SUSCRIPCION
        */

        $fecha_inicio = date('Y-m-d');
        $fecha_final = date('Y-m-d', strtotime($fecha_inicio."+ 1 month"));

        DB::table('suscripciones')->insert([
            'id_empresa'     => $empresa->id,
            'fec_inicio'     => $fecha_inicio,
            'fec_vencimiento'=> $fecha_final,
            'fec_corte'      => $fecha_final,
            'created_at'     => Carbon::now()
        ]);

        /*
         * BANCO
         * */
        $tiposBancos = ['BANCO 1','CAJA GENERAL', 'CAJA MENOR'];
        $i = 0;
        foreach($tiposBancos as $tipoBanco){
            $banco = new Banco;
            $banco->nro = Banco::where('empresa',$empresa->id)->count()+1;
            $banco->empresa= $empresa->id;
            if($i == 0){ $banco->tipo_cta= 1; }else { $banco->tipo_cta= 3; }
            $banco->nombre= $tipoBanco;
            $banco->nro_cta= 0000000000000;
            $banco->saldo= 0;
            $banco->fecha= date('Y-m-d');
            if($i == 1){$banco->descripcion = 'caja general de la empresa';}else{$banco->descripcion = "";}
            $i = $i + 1;
            $banco->save();
        }

        /*
         * IMPUESTOS
         * */

        $impuesto = new Impuesto();
        $impuesto->empresa= $empresa->id;
        $impuesto->nombre= 'IVA';
        $impuesto->porcentaje= 19;
        $impuesto->tipo= 1;
        $impuesto->descripcion= 'IMPUESTO DE IVA';
        $impuesto->save();

        /*
         * RETENCIONES
         * */

        $retencion = new Retencion();
        $retencion->empresa= $empresa->id;
        $retencion->nombre= 'RETENCION EN FUENTE';
        $retencion->porcentaje= 2.5;
        $retencion->tipo= 2;
        $retencion->descripcion= 'Retención en la fuente';
        $retencion->save();

        /*
         * BODEGA
         * */
        $bodega = new Bodega;
        $bodega->nro = Bodega::where('empresa',$empresa->id)->count()+1;
        $bodega->empresa = $empresa->id;
        $bodega->bodega = 'PRINCIPAL';
        $bodega->direccion = 'BODEGA PREDETERMINADA';
        $bodega->observaciones = '';
        $bodega->save();
        /*
         * INVENTARIO
         * */

        $categoriaInventario = Categoria::where('empresa',$empresa->id)->where('nro','=',1)->get();
        $inventario = new Inventario;
        $inventario->empresa= $empresa->id;
        $inventario->producto= 'Prueba';
        $inventario->ref= '123456';
        $inventario->descripcion= 'PRODUCTO PRUEBA';
        $inventario->precio= 0;
        $inventario->tipo_producto= 2;
        $inventario->unidad=1;$inventario->nro=0;
        $inventario->categoria = $categoriaInventario[0]->id;
        $inventario->lista = 0;
        $inventario->save();


        if ($request->logo) {
            $path = public_path() .'/images/Empresas/Empresa'.$empresa->id;
            $imagen->move($path,$nombre_imagen);
        }


        $tipoContacto = new TipoEmpresa();
        $tipoContacto->empresa= $empresa->id;
        $tipoContacto->nombre= 'VARIOS';
        $tipoContacto->descripcion= 'Tipo de Contactos Predeterminado';
        $tipoContacto->save();

        /*
         * CAMPOS EXTRAS
         * */
        $camposExtras = ['marca','linea', 'modelo','version','serie'];
        foreach($camposExtras as $camposExtra){
            $camExtra = new CamposExtra;
            $camExtra->campo= $camposExtra;
            $camExtra->empresa= $empresa->id;
            $camExtra->nombre= ucwords($camposExtra);
            $camExtra->tipo = 0;
            $camExtra->status = 1;
            $camExtra->autocompletar = 1;
            $camExtra->tabla = 0;
            $camExtra->save();
        }

        $contacto = new Contacto();
        $contacto->empresa= $empresa->id;
        $contacto->tip_iden= 2;
        $contacto->tipo_empresa= $tipoContacto->id;
        $contacto->nit= '000000000';
        $contacto->direccion = 'Calle 1 1 1';
        $contacto->ciudad = 'Medellin';
        $contacto->fk_idpais = 'CO';
        $contacto->fk_iddepartamento = 1;
        $contacto->fk_idmunicipio= 1;
        $contacto->observaciones= 'Lo recomendo Rafael Gomez';
        $contacto->fax = '+57 3014255353';
        $contacto->nombre=ucwords(mb_strtolower('CONTACTO PREDETERMINADO'));
        $contacto->ciudad=ucwords(mb_strtolower('PREDETERMINADO'));
        $contacto->telefono1= '+57 3014255353';
        $contacto->tipo_contacto= '2';
        $contacto->save();


        $usuario = new User;
        $usuario->nombres = $request->nombre;
        $usuario->email = $request->email;
        $usuario->telefono=$request->telefono?$request->pref." ".$request->telefono:$request->telefono;
        $usuario->empresa = $empresa->id;
        $usuario->username = $request->username=strtolower($request->username);
        $usuario->password  = bcrypt($request->password);
        $usuario->nro = 1;
        $usuario->rol = 2;
        $usuario->created_at  = Carbon::now();
        $usuario->updated_at  = Carbon::now();
        $usuario->save();
        $data=$request;
        $data['tip_iden']=$empresa->tip_iden();
        $data['tipo_persona']=$empresa->tipo_persona();

         /*
        * NUMERACIONES
        */
      $numeracion=new NumeracionFactura;
      $numeracion->nombre= 'Principal';
      $numeracion->inicio= 1;
      $numeracion->preferida= 1;
      $numeracion->empresa= $empresa->id;
      $numeracion->save();

        Mail::send('emails.welcomeEmpresa',  ['data' => $data], function($msj) use ($data){
            $msj->subject('Bienvenid@ '.$data->nombre);
            $msj->to($data->email);
        });

        $permisosAccesos = DB::table('permisos_botones')->select('id')->get();

        foreach($permisosAccesos as $permiso){
            DB::table('permisos_usuarios')->insert(['id_usuario'=>$usuario->id, 'id_permiso'=>$permiso->id]);
        }

        $lista = new ListaPrecios;
        $lista->nro=1;
        $lista->empresa=$empresa->id;
        $lista->nombre='General';
        $lista->tipo=0;
        $lista->save();

        $mensaje='Registro creado satisfactoriamente';
        return redirect('master/empresas')->with('success', $mensaje)->with('empresa_id', $empresa->id);
    }

    public function edit($id){
        $empresa =Empresa::where('id',$id)->first();
        if ($empresa) {
            $subscriptions = SuscripcionPago::where('id_empresa', $empresa->id)->whereIn('estado',[1,10])
            ->orderBy('id', 'desc')->get();
            $planes = DB::table('planes_personalizados')->get();
            $identificaciones=TipoIdentificacion::all();
            $prefijos=DB::table('prefijos_telefonicos')->get();
            return view('empresas.edit')->with(compact('empresa', 'identificaciones', 'prefijos', 'planes', 'subscriptions'));
        }
        return redirect('master/empresas')->with('success', 'No existe un registro con ese id');
    }

    public function update(Request $request, $id){
        $empresa =Empresa::find($id);
        if ($empresa) {
            $request->validate([
                'nombre' => 'required',
                'nit' => 'required',
                'telefono' => 'required',
                'email' => 'required|email',
                'direccion' => 'required',
                'username' => 'required',
                'tip_iden' => 'required|exists:tipos_identificacion,id',
                'tipo_persona'=>'required'
            ]);
            $errors= (object) array();
            $error =Empresa::where('nombre', $request->nombre)->where('id', '<>', $id)->get();
            if (count($error)>0) {
                $errors->nombre='El nombre de la empresa ya se encuentra registrado en otra empresa';
                return back()->withErrors($errors)->withInput();
            }

            $usuario =User::where('email',$empresa->email)->where('empresa',$id )->first();
            if(!$usuario){
                $usuario =User::where('empresa',$id )->first();
            }

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


            if ($request->logo) {
                $request->validate([
                    'logo'=>'mimes:jpeg,jpg,png| max:100'
                ],['logo.mimes' => 'La extensión del logo debe ser jpeg, jpg, png',
                    'logo.max' => 'El peso máximo para el logo es de 100KB',
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
            $empresa->nombre = $request->nombre;
            $empresa->nit = $request->nit;
            $empresa->telefono=$request->telefono?$request->pref." ".$request->telefono:$request->telefono;
            $empresa->tipo_persona = $request->tipo_persona;
            $empresa->tip_iden = $request->tip_iden;
            $empresa->direccion = $request->direccion;
            $empresa->email = $request->email=strtolower($request->email);
            $empresa->updated_at  = Carbon::now();
            $empresa->carrito = $request->carrito;
            $empresa->web = $request->web;
            $empresa->p_personalizado = $request->p_personalizado;
            $empresa->save();
            $usuario->nombres = $request->nombre;
            if ($request->changepass) {
                $usuario->password  = bcrypt($request->password);
            }
            $usuario->email = $request->email;
            $empresa->telefono=$request->telefono?$request->pref." ".$request->telefono:$request->telefono;
            $usuario->username = $request->username=strtolower($request->username);
            $empresa->whatsapp=$request->whatsapp?$request->pref."".$request->whatsapp:$request->whatsapp;
            $usuario->updated_at  = Carbon::now();
            $usuario->save();

        }
        return redirect('master/empresas')->with('success', 'Se ha modificado los datos de la empresa')->with('empresa_id', $id);
    }

    public function desactivar(Request $request, $id){
        $empresa=Empresa::find($id);
        if ($empresa) {
            $empresa->status = 0;
            $empresa->save();
        }
        return redirect('master/empresas/inactivas')->with('success', 'Se ha desactivado la empresa')->with('empresa_id', $id);
    }

    public function activar(Request $request, $id){
        $empresa=Empresa::find($id);
        if ($empresa) {
            $empresa->status = 1;
            $empresa->save();
        }
        return redirect('master/empresas')->with('success', 'Se ha activado la empresa')->with('empresa_id', $id);
    }

     public function ingresar($email)
    {
        $usuario = User::where('email', $email)->first();
        Auth::logout();
        auth()->login($usuario);
        return redirect('/home')->with('success',"Bienvenido ".$usuario->nombre);
    }

    public function nomina($id)
    {
        $suscripcion = SuscripcionNomina::where('id_empresa', $id)->first();
        if ($suscripcion) {
            $suscripcion->fec_vencimiento = date('Y-m-d', strtotime(Carbon::now() . "+ 15 days"));
            $suscripcion->fec_corte = date('Y-m-d', strtotime(Carbon::now() . "+ 15 days"));
            $suscripcion->save();

            return response()->json([
                'success' => true,
                'type'    => 'success',
                'message' => 'Suscripción Ampliada con éxito'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'message' => 'Ha ocurrido un error, intente de nuevo'
            ]);
        }
    }

    public function storePageLength(Request $request) {
        $empresa = Empresa::find(Auth::user()->empresa);
        if ($empresa) {
            $empresa->pageLength = $request->pageLength;
            $empresa->save();

            return response()->json([
                'success' => true,
                'type'    => 'success',
                'message' => 'Configuración Realizada con Éxito'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'message' => 'Ha ocurrido un error, intente de nuevo'
            ]);
        }
    }

    public function storePeriodoFacturacion(Request $request) {
        $empresa = Empresa::find(Auth::user()->empresa);
        if ($empresa) {
            $empresa->periodo_facturacion = $request->periodo_facturacion;
            $empresa->save();

            return response()->json([
                'success' => true,
                'type'    => 'success',
                'title'   => 'CONFIGURACIÓN REALIZADA CON ÉXITO',
                'message' => ($empresa->periodo_facturacion==1)?'PERIODO DE FACTURACIÓN - MES ANTICIPADO':'PERIODO DE FACTURACIÓN - MES VENCIDO',

            ]);
        } else {
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'title'   => 'CONFIGURACIÓN NO REALIZADA',
                'message' => 'Ha ocurrido un error, intente de nuevo',
            ]);
        }
    }

    public function storeFormatoImpresion(Request $request) {
        $empresa = Empresa::find(Auth::user()->empresa);
        if ($empresa) {
            $empresa->formato_impresion = $request->formato_impresion;
            $empresa->save();

            return response()->json([
                'success' => true,
                'type'    => 'success',
                'title'   => 'CONFIGURACIÓN REALIZADA CON ÉXITO',
                'message' => ($empresa->formato_impresion==1)?'FORMATO IMPRESIÓN - CRC':'FORMATO DE IMPRESIÓN - ESTÁNDAR',

            ]);
        } else {
            return response()->json([
                'success' => false,
                'type'    => 'error',
                'title'   => 'CONFIGURACIÓN NO REALIZADA',
                'message' => 'Ha ocurrido un error, intente de nuevo',
            ]);
        }
    }
}
