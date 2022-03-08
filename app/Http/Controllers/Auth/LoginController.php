<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Auth; use Validator; use DB; use Illuminate\Http\Request; use App\User; use App\Empresa; use App\Vehiculo;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    public function showLoginForm()
    {
        // Verificamos si hay sesión activa
        if (Auth::check())
        {
            // Si tenemos sesión activa mostrará la página de inicio
            return Redirect::to('/');
        }
        // Si no hay sesión activa mostramos el formulario
        $empresa = Empresa::find(1);
        return view('auth.login')->with(compact('empresa'));
    }

    public function showLoginFormConductor()
    {
        // Verificamos si hay sesión activa
        if (Auth::check())
        {
            // Si tenemos sesión activa mostrará la página de inicio
            return Redirect::to('conductor/');
        }
        // Si no hay sesión activa mostramos el formulario        
        return view('auth.Conductorlogin');
    }


    public function login(Request $request)
    {         
        $this->validateLogin($request);
        $request->remember=$request->remember=='on'?1:0;
        $data = array(
            'username'     => $request->username,
            'password'  => $request->password
        );    
        

        if (Auth::attempt($data, $request->remember)) {   
            $user =User::where('username', $request->username)->first(); 
            if ($user->user_status==0) {
                Auth::logout();
                $errors=(object) array();
                $errors->error_message="Su usuario no se encuentra activo en el sistema";
                return Redirect::back()->withInput($request->all())->withErrors($errors);
            }
            if ($user->empresa) {
                $empresa =Empresa::where('id',$user->empresa)->first();
                if ($empresa->status==0) {
                    Auth::logout();
                    $errors=(object) array();
                    $errors->error_message="La empresa que usted está asignado se encuentra inactiva";
                    return Redirect::back()->withInput($request->all())->withErrors($errors);
                }  
            }
            $user->online = 1;
            $user->save();
            return Redirect::to('/home');
        } else {        
            // validation not successful, send back to form 
            $errors=(object) array();
            $errors->password="Clave inválida";
            return Redirect::back()->withInput($request->all())->withErrors($errors);
        }     
    }

    public function loginConductor(Request $request)
    {         
        $this->validateLogin($request);
        $request->validate([
            'placa'=> 'required|string|exists:vehiculos'
        ], [
            'placa.exists' => 'La placa que ingresó no se encuentra en nuestros registros',
        ]);
        $errors=(object) array();
        $usuario =User::where('username', $request->username)->first();
        if ($usuario->rol<>3) {
            $errors->error_message="Usted no es conductor en el sistema";
            return Redirect::back()->withInput($request->all())->withErrors($errors);
        }
        $error =Vehiculo::where('placa', $request->placa)->where('empresa', '<>', $usuario->empresa)->get();
        if (count($error)>0) {
            $errors->placa="La placa que ingresó no esta asociada a su empresa";
            return Redirect::back()->withInput($request->all())->withErrors($errors);
        }
        $error =Vehiculo::where('placa', $request->placa)->where('empresa', '=', $usuario->empresa)->where('conductor', '=', $usuario->id)->get();
        if (count($error)==0) {
            $errors->placa="La placa que ingresó no la tiene asociada";
            return Redirect::back()->withInput($request->all())->withErrors($errors);
        }
        if ($usuario->user_status==0) {
            $errors=(object) array();
            $errors->error_message="Su usuario no se encuentra activo en el sistema";
            return Redirect::back()->withInput($request->all())->withErrors($errors);
        }
        if ($usuario->empresa) {
            $empresa =Empresa::where('id',$usuario->empresa)->first();
            if ($empresa->status==0) {
                Auth::logout();
                $errors=(object) array();
                $errors->error_message="La empresa que usted está asignado se encuentra inactiva";
                return Redirect::back()->withInput($request->all())->withErrors($errors);
            }  
        }

        $request->remember=$request->remember=='on'?1:0;
        $data = array(
            'username'     => $request->username,
            'password'  => $request->password
        );    

        if (Auth::attempt($data, $request->remember)) { 
            $vehiculo =Vehiculo::where('placa', $request->placa)->first();
            $usuario->vehiculo=$vehiculo->id;
            $usuario->save();
            $request->session()->put('placa', $request->placa);
            return Redirect::to('/');
        } else {        
            // validation not successful, send back to form 
            $errors=(object) array();
            $errors->password="Clave inválida";
            return Redirect::back()->withInput($request->all())->withErrors($errors);
        }     
    }

    

    protected function validateLogin(Request $request)
    {
        $messages = [
            'username.exists' => 'El Nombre de Usuario que ingresó no se encuentra en nuestros registros',
        ];

        $this->validate($request, [
            'username'=> 'required|string|exists:usuarios',
            'password' => 'required|string',
        ], $messages);

    }

    public function logout()
    {
        if (Auth::check())
        {
            // Cerramos la sesión
            $rol=Auth::user()->rol;
            Auth::logout();
        }      
        return Redirect::to('login');
    }


}
