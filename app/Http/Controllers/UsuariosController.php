<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User; use App\Roles;  use Carbon\Carbon;  use Mail;
use Validator; use Illuminate\Validation\Rule;  use Auth; use DB;
use Illuminate\Support\Facades\Hash;
use App\Radicado;
use App\Oficina;
use App\Campos;

class UsuariosController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        view()->share(['seccion' => 'configuracion', 'title' => 'Usuarios', 'icon' =>'fas fa-users']);
    }

    public function index(){
        $this->getAllPermissions(Auth::user()->id);

        if(Auth::user()->rol == 1){
            $usuarios = User::where('user_status', 1)->get();
        }else{
            $usuarios = User::where('empresa',Auth::user()->empresa)->get();
        }

        $recarga = 0;
        return view('configuracion.usuarios.index')->with(compact('usuarios','recarga'));
    }

    public function saldo(){
        $this->getAllPermissions(Auth::user()->id);
        $usuarios = User::where('empresa',Auth::user()->empresa)->where('user_status',1)->where('rol', 8)->get();
        $recarga = 1;
        view()->share(['seccion' => 'saldo', 'title' => 'Recarga de Saldos', 'icon' =>'fas fa-dollar-sign']);
        return view('configuracion.usuarios.index')->with(compact('usuarios','recarga'));
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        $roles = Roles::where('id_empresa','=', Auth::user()->empresa)->where('id', '<>', 3)->get();
        $cuentas = DB::table('bancos')->where('empresa',Auth::user()->empresa)->get();
        $oficinas = Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
        view()->share(['title' => 'Nuevo Usuario']);
        return view('configuracion.usuarios.create')->with(compact('roles','cuentas', 'oficinas'));
    }

    public function store(Request $request){
        $request->validate([
            'email' => 'required|email|unique:usuarios',
            'username' => 'required|unique:usuarios',
            'password' => 'required',
            'rol'=>'required|numeric|exists:roles1,id',
        ],[
            'rol.exists' => 'Error con el rol',
            'email.unique'  => 'El correo electrónico ya se encuentra registrado en el sistema',
            'username.unique'  => 'El nombre de Usuario ya se encuentra registrado en el sistema'
        ]);


        $usuario = new User;
        $usuario->nro =  User::where('empresa',Auth::user()->empresa)->count()+1;
        $usuario->nombres = $request->nombres;
        $usuario->email = $request->email;
        $usuario->observaciones = $request->observaciones;
        $usuario->empresa = Auth::user()->empresa;
        $usuario->username = $request->username=strtolower($request->username);
        $usuario->password  = bcrypt($request->password);
        $usuario->rol = $request->rol;
        if(isset($request->cuenta[0])){ $usuario->cuenta = $request->cuenta[0]; }else{ $usuario->cuenta = null; }
        if(isset($request->cuenta[1])){ $usuario->cuenta_1 = $request->cuenta[1]; }else{ $usuario->cuenta_1 = null; }
        if(isset($request->cuenta[2])){ $usuario->cuenta_2 = $request->cuenta[2]; }else{ $usuario->cuenta_2 = null; }
        if(isset($request->cuenta[3])){ $usuario->cuenta_3 = $request->cuenta[3]; }else{ $usuario->cuenta_3 = null; }
        if(isset($request->cuenta[4])){ $usuario->cuenta_4 = $request->cuenta[4]; }else{ $usuario->cuenta_4 = null; }
        $usuario->oficina = ($request->oficina == 0) ? NULL : $request->oficina;
        $usuario->save();

        $campos = Campos::all();
        foreach ($campos as $campo) {
            if($campo->orden != null){
                DB::table('campos_usuarios')->insert([
                    'id_modulo'  => $campo->modulo,
                    'id_usuario' => $usuario->id,
                    'id_campo'   => $campo->id,
                    'orden'      => $campo->orden,
                    'estado'     => $campo->estado
                ]);
            }
        }

        $mensaje = 'SE HA CREADO SATISFACTORIAMENTE EL USUARIO';
        return redirect('empresa/configuracion/usuarios')->with('success', $mensaje)->with('usuario_id', $usuario->id);
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $usuario = User::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $rol = Roles::where('id_empresa',Auth::user()->empresa)->where('id', $usuario->rol)->first();
        $radicados = Radicado::where('empresa',Auth::user()->empresa)->where('responsable', $id)->count();
        if ($usuario) {
            view()->share(['title' => 'Ver Usuario']);
            return view('configuracion.usuarios.show')->with(compact('usuario','radicados','rol'));
        }
        return redirect('empresa/configuracion/usuarios')->with('success', 'No existe un registro con ese id');
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $roles = Roles::where('id','>',1)->where('id_empresa', Auth::user()->empresa)->where('id', '<>', 3)->get();
        $usuario = User::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $cuentas = DB::table('bancos')->where('empresa',Auth::user()->empresa)->get();
        $oficinas = Oficina::where('empresa', Auth::user()->empresa)->where('status', 1)->get();
        if ($usuario) {
            view()->share(['title' => 'Modificar Usuario']);
            return view('configuracion.usuarios.edit')->with(compact('usuario', 'roles','cuentas', 'oficinas'));
        }
        return redirect('empresa/configuracion/usuarios')->with('success', 'No existe un registro con ese id');
    }

    public function update(Request $request, $id){//dd($request->all());
        $usuario =User::where('empresa',Auth::user()->empresa)->where('id', $id)->first();

        if(!DB::table('campos_usuarios')->where('id_usuario',$usuario->id)->first()){
            $campos = Campos::all();
            foreach ($campos as $campo) {
                if($campo->orden != null){
                    DB::table('campos_usuarios')->insert([
                        'id_modulo'  => $campo->modulo,
                        'id_usuario' => $usuario->id,
                        'id_campo'   => $campo->id,
                        'orden'      => $campo->orden,
                        'estado'     => $campo->estado
                    ]);
                }
            }
        }

        if ($usuario) {
            $request->validate([
                'email' => 'required|email',
                'username' => 'required',
                //'rol'=>'required|numeric|exists:roles,id',
            ],[
                //'rol.exists' => 'Error con el rol',
            ]);
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
                /*if (!Hash::check($request->pass_actual, $usuario->password)) {
                    $errors->pass_actual='Error en la contraseña';
                    return back()->withErrors($errors)->withInput();
                }*/
                $usuario->password  = bcrypt($request->password);
            }

            $usuario->nombres  = $request->nombres;
            $usuario->email    = $request->email;
            $usuario->observaciones = $request->observaciones;
            $usuario->username = $request->username=strtolower($request->username);
            $usuario->rol      = $request->rol;
            if(isset($request->cuenta[0])){ $usuario->cuenta = $request->cuenta[0]; }else{ $usuario->cuenta = null; }
            if(isset($request->cuenta[1])){ $usuario->cuenta_1 = $request->cuenta[1]; }else{ $usuario->cuenta_1 = null; }
            if(isset($request->cuenta[2])){ $usuario->cuenta_2 = $request->cuenta[2]; }else{ $usuario->cuenta_2 = null; }
            if(isset($request->cuenta[3])){ $usuario->cuenta_3 = $request->cuenta[3]; }else{ $usuario->cuenta_3 = null; }
            if(isset($request->cuenta[4])){ $usuario->cuenta_4 = $request->cuenta[4]; }else{ $usuario->cuenta_4 = null; }
            $usuario->oficina = ($request->oficina == 0) ? NULL : $request->oficina;
            $usuario->save();

            $mensaje='Se ha modificado satisfactoriamente el usuario';
            return redirect('empresa/configuracion/usuarios')->with('success', $mensaje)->with('usuario_id', $usuario->id);
        }
        return redirect('empresa/configuracion/usuarios')->with('success', 'No existe un registro con ese id');
    }

    public function destroy($id){
        $usuario=User::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        $usuario->delete();
        return redirect('empresa/configuracion/usuarios')->with('success', 'Se ha eliminado el usuario');
    }

    public function act_desc($id){
        $usuario = User::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
        if ($usuario) {
            if ($usuario->user_status==1) {
                $mensaje='Se ha desactivado el usuario';
                $usuario->user_status=0;
                $usuario->save();
            }else{
                $mensaje='Se ha activado el usuario';
                $usuario->user_status=1;
                $usuario->save();
            }
            return redirect('empresa/configuracion/usuarios')->with('success', $mensaje)->with('usuario_id', $usuario->id);
        }
        return redirect('empresa/configuracion/usuarios')->with('success', 'No existe un registro con ese id');
    }

    public function verPermisos(Request $request){
        $idUsuario = $request->idUsuario;
        $modulos = DB::table('permisos_modulo')->select('id','nombre_modulo')->where('status',1)->orderBy('orden', 'asc')->get();

        $permisosUsuario = DB::select("SELECT
                                        b.id,b.id_modulo,b.nombre_permiso,p.id_permiso,p.id_usuario
                                    FROM
                                        permisos_botones as b
                                    LEFT JOIN permisos_usuarios AS p
                                    ON
                                        p.id_permiso = b.id
                                    AND
                                      p.id_usuario = '$idUsuario'
                                    ORDER BY b.id");

        return view('configuracion.permisos.permisosUsuario')->with(compact('modulos','permisosUsuario','idUsuario'));
    }

    public function guardarPermisos(Request $request){
        DB::table('permisos_usuarios')->where('id_usuario',$request->idUsuario)->delete();
        foreach ($request->permiso as $key => $permiso){
            $permisosNuevos = DB::table('permisos_usuarios')->insert(['id_usuario'=>$request->idUsuario, 'id_permiso'=>$key]);
        }

        if(!$permisosNuevos){
            $arrayPost['status']  = 'error';
            $arrayPost['mensaje'] = 'Ocurrio un error, por favor intente de nuevo!';
            echo json_encode($arrayPost);
            exit;
        }else{
            $arrayPost['status']  = 'OK';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function ingresar($email){
        $usuario = User::where('email', $email)->first();
        Auth::logout();
        auth()->login($usuario);
        return redirect('/home')->with('success',"Bienvenido ".$usuario->nombre);
    }

    public function verSaldo(Request $request){
        $usuario = User::find($request->id);
        return view('configuracion.usuarios.saldo')->with(compact('usuario'));
    }

    public function guardarSaldo(Request $request){
        $usuario = User::find($request->id);

        if($usuario){
            $usuario->saldo += $request->recarga;
            $usuario->save();

            DB::table('recargas_usuarios')->insert(['usuario' => $request->id, 'recarga' => $request->recarga, 'fecha' => date('Y-m-d')]);
        }

        if(!$usuario){
            $arrayPost['success']  = false;
            $arrayPost['mensaje'] = 'Ocurrio un error, por favor intente de nuevo!';
            echo json_encode($arrayPost);
            exit;
        }else{
            $arrayPost['success']  = true;
            $arrayPost['mensaje'] = 'Recarga de Saldo registrada satisfactoriamente';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function verGanancia(Request $request){
        $usuario = User::find($request->id);
        return view('configuracion.usuarios.ganancia')->with(compact('usuario'));
    }

    public function guardarGanancia(Request $request){
        $usuario = User::find($request->id);

        if($usuario){
            if($usuario->ganancia >= $request->recarga){
                $usuario->saldo += $request->recarga;
                $usuario->ganancia -= $request->recarga;
                $usuario->save();

                $arrayPost['type']     = 'success';
                $arrayPost['title']    = 'SATISFACTORIO';
                $arrayPost['mensaje']  = 'Intercambio de ganancia por saldo realizado.';
                $arrayPost['ganancia'] = $usuario->ganancia;
                $arrayPost['saldo']    = $usuario->saldo;
                echo json_encode($arrayPost);
                exit;
            }else{
                $arrayPost['type']    = 'error';
                $arrayPost['title']   = 'ERROR';
                $arrayPost['mensaje'] = 'Disculpe, esta intentando intercambiar un monto mayor al disponible en sus ganancias.';
                $arrayPost['ganancia'] = $usuario->ganancia;
                $arrayPost['saldo']    = $usuario->saldo;
                echo json_encode($arrayPost);
                exit;
            }
        }

        if(!$usuario){
            $arrayPost['success']  = 'false';
            $arrayPost['mensaje'] = 'Ocurrio un error, por favor intente de nuevo!';
            echo json_encode($arrayPost);
            exit;
        }
    }

    public function reiniciarSaldo($id){
        $this->getAllPermissions(Auth::user()->id);
        $usuario = User::where('id', $id)->first();
        if ($usuario) {
            $usuario->saldo = 0;
            $usuario->save();
            return redirect('empresa/configuracion/recarga-saldo')->with('success', 'SALDO DE '.strtoupper($usuario->nombres).' HA SIDO REINICIADO');
        }
        return redirect('empresa/configuracion/usuarios')->with('success', 'No existe un registro con ese id');
    }
}
