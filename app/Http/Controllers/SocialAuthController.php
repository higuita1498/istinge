<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Empresa; use App\User;
use Socialite;
use Auth; use Alert; use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Model\Inventario\ListaPrecios;
use App\Banco;
use App\Model\Inventario\Bodega;
use App\Model\Inventario\Inventario;
use App\Numeracion;
use App\TerminosPago;
use App\Categoria;
use DB;
use App\CamposExtra;
use App\Impuesto; use App\Retencion;
use App\Contacto; use App\TipoEmpresa;
use Mail;
use App\NumeracionFactura;

class SocialAuthController extends Controller
{
 public function redirectToProvider($provider)
 {

  return Socialite::driver($provider)->redirect();
}

public function handlerProviderCallback($provider)
{
    //Datos que puedo obtener del driver:
  /*$user->getname() - $user->getId() - $user->getNickname() - $user->getEmail() - $user->getAvatar();*/
  try {
    //firstOrCreate verifica si el usuario está creado
   $user = Socialite::driver('google')->stateless()->user();
    //$user = Socialite::driver($provider)->user();
    $createUser = Empresa::firstOrCreate([
      'email' => $user->getEmail(),
    ],[
      'nombre'=> ucwords(mb_strtolower($user->getName())),
      'rol' => 2,
      'tip_iden' => 3,
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now(),
    ]);

    User::firstOrCreate([
      'email' => $user->getEmail(),
    ],[
      'nombres' => $user->getname(),
      'username' => $user->getEmail(),
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now(),
      'rol' => 2,
      'nro' => 1,
      'empresa' => $createUser->id,
    ]);

           // auth()->login($createUser); //iniciamos session
    return redirect('/Registrarse')->with('success',"Registrado Correctamente  ".$user->getname());
  } catch (\GuzzleHttp\Exception\ClientException $e) {
    dd($e);
  }
}

public function registronormal(Request $request)
{
  $this->validate($request,[
    'empresa' => 'string',
    'documento' => 'string|max:20',
    'email' => 'unique:empresas',
    'password' => 'string|min:6',
    'username'  => 'unique:usuarios',
  ]);

    $empresa = new Empresa;
    $empresa->tip_iden = $request->tip_iden;
    $empresa->nit = $request->documento;
    $empresa->telefono=$request->telefono?$request->prefijo." ".$request->telefono:$request->telefono;
    $empresa->nombre = $request->empresa = ucwords(mb_strtolower($request->empresa));
    $empresa->email = $request->email;
    $empresa->rol = 3; //Rol usuario con plan de prueba
    $empresa->created_at = Carbon::now();
    $empresa->updated_at = Carbon::now();
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
        $retencion->descripcion= 'Retenci��n en la fuente';
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
        $usuario->nombres = $request->empresa;
        $usuario->email = $request->email;
        $usuario->telefono=$request->telefono?$request->prefijo." ".$request->telefono:$request->telefono;
        $usuario->username = $request->username;
        $usuario->password = bcrypt($request->password);
        $usuario->nro = 1;
        $usuario->rol = 2;
        $usuario->empresa = $empresa->id;
        $usuario->created_at = Carbon::now();
        $usuario->updated_at = Carbon::now();
        $usuario->save();
        $data=$request;
        $data['tip_iden']=$empresa->tip_iden();
        //$data['tipo_persona']=$empresa->tipo_persona();

        /*self::sendMail('emails.welcomeEmpresa',  ['data' => $data], function($msj) use ($data){
            $msj->subject('Bienvenid@ '.$data->nombre);
            $msj->to($data->email);
        });*/

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

        /*
        * NUMERACIONES
        */
      $numeracion=new NumeracionFactura;
      $numeracion->nombre= 'Principal';
      $numeracion->inicio= 1;
      $numeracion->preferida= 1;
      $numeracion->empresa= $empresa->id;
      $numeracion->save();


    auth()->login($usuario);
    return redirect('/home')->with('success',"Registrado Correctamente  ".$request->nombre);
 }
}
