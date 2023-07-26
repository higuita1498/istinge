<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use DB;
use Session; use Validate;
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    
    public function recuperar_pass($token){
        $sql="SELECT email, token, created_at FROM password_resets where token='".$token."' ";        
        $result =DB::select($sql);
        if(count($result)>0){
            $result=$result[0];
        }
        else{
            Session::flash('msj', 'è¢¬Error! El token ya no existe en el sistema');

        }
        
        return view('auth.passwords.reset')->with(compact('result'));

        
    }
    
    protected function validatePass(Request $request)
    {
        $this->validate($request, [ 'password'=> 'required']);
        
    }
    
    public function cambiar_pass(Request $request){
           $this->validate($request, [
                'inputPasswordConfirm' => 'same:password',
                'password' => 'min:6',
            ]);
       // $this->validatePass($request);        
        $sql="SELECT email, token, created_at, (SELECT id FROM usuarios WHERE email='".$request->email."') as ID FROM password_resets where token='".$request->token."' and email='".$request->email."'  ";        
        $result =DB::select($sql);
        if(count($result)){
            $result=$result[0];
            DB::table('usuarios')
                ->where('ID', $result->ID)
                    ->update(['password' => bcrypt($request->password) ]);
                    
            DB::table('password_resets')->where('email', '=', $request->email)->delete();
        }
       return redirect('login')->with('success_pass', 'Se ha modificado correctamente su contrase√±a');
    }
}