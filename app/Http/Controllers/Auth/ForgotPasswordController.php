<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use DB; use App\User; use Mail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validateEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email|exists:usuarios'],[
        'email.exists' => 'La dirección de correo electrónico no se encuentra registrado en el sistema'
      ]);
    }
    
    
     public function enviar(Request $request){

        $this->validateEmail($request);
        $token = str_random(64);
        $sql="SELECT email, token, created_at FROM password_resets where email='".$request->email."' ";
        $result =DB::select($sql);
        if(count($result)==0){
          DB::table('password_resets')->insert(['email'=>$request->email, 'token'=>$token, 'created_at'=>Carbon::parse(date('Y-m-d H:i:s'))]);
        }
        
        $sql="SELECT pr.email, pr.token, wu.nombres, wu.email FROM usuarios wu, password_resets pr where pr.email=wu.email and pr.email='".$request->email."' ";
        $result =DB::select($sql)[0];
        $data =(array) $result;
        Mail::send('emails.forgotpass',  ['data' => $data], function($msj) use ($data){
            $msj->subject('olvido su contraseña');
            $msj->to($data['email']);
        });
        
        return redirect('login')->with('success', 'Se ha enviado un mensaje a su correo para recuperar su contraseña');

         
          

     }

}
