<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mensajeria;
use App\Funcion;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use Barryvdh\DomPDF\Facade as PDF;

class MensajeriaController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    set_time_limit(300);
    view()->share(['seccion' => 'mensajeria', 'title' => 'Mensajería', 'icon' =>'fas fa-envelope-open-text']);
  }

  /**
  * Index para ver los radicado registrados
  * @return view
  */
  public function index(){
    $this->getAllPermissions(Auth::user()->id);
    $mensajes = Mensajeria::where('empresa', Auth::user()->empresa)->get();

    return view('mensajeria.index')->with(compact('mensajes'));
  }

  /**
  * Formulario para crear un nuevo radicado
  * @return view
  */
  public function create(){
    $this->getAllPermissions(Auth::user()->id);
    view()->share(['title' => 'Nuevo Mensaje']);
    return view('mensajeria.create');
  }

  /**
  * Registrar un nuevo radicado
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
      
  }

  /**
  * Formulario para modificar los datos de un radicado
  * @param int $id
  * @return view
  */
  public function edit($id){
      
  }

  /**
  * Modificar los datos de un radicado
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
      
  }

  /**
  * Ver un radicado
  * @param int $id
  * @return view
  */
  public function show($id){
      
  }

  /**
  * Eliminar un radicado
  * @param int $id
  * @return redirect
  */
  public function destroy($id){
      
  }

  /**
  * Funcion para estacalar un radicado a soporte técnico
  * @param int $id
  * @return redirect
  */
  public function status($id){
      
  }
  
    public function enviar(){
        $post['to'] = array('573505555571');
        $post['text'] = "Prueba";
        $post['from'] = "Intercar Net S.A.S";
        $user ="APIColombiared";
        $password = '12345678';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://masivos.colombiared.com.co/Api/rest/message");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_HTTPHEADER,
        array(
            "Accept: application/json",
            "Authorization: Basic ".base64_encode($user.":".$password)));
        $result = curl_exec ($ch);
        $err  = curl_error($ch);
        curl_close($ch);
        
        if ($err) {
            return redirect('empresa/mensajeria')->with('danger', $err);
        }else{
            $response = json_decode($result, true);
            //return $response;
            if($response['error']){
                if($response['error']['code'] == 102){
                    $msj = "No hay destinatarios válidos";
                }else if($response['error']['code'] == 103){
                    $msj = "Nombre de usuario o contraseña desconocidos";
                }else if($response['error']['code'] == 104){
                    $msj = "Falta el mensaje de texto";
                }else if($response['error']['code'] == 105){
                    $msj = "Mensaje de texto demasiado largo";
                }else if($response['error']['code'] == 106){
                    $msj = "Falta el remitente";
                }else if($response['error']['code'] == 107){
                    $msj = "Remitente demasiado tiempo";
                }else if($response['error']['code'] == 108){
                    $msj = "No hay fecha y hora válida para enviar";
                }else if($response['error']['code'] == 109){
                    $msj = "URL de notificación incorrecta";
                }else if($response['error']['code'] == 110){
                    $msj = "Se superó el número máximo de piezas permitido o número incorrecto de piezas";
                }else if($response['error']['code'] == 111){
                    $msj = "Crédito/Saldo insuficiente";
                }else if($response['error']['code'] == 113){
                    $msj = "Codificación no válida";
                }
                return redirect('empresa/mensajeria')->with('danger', 'Envío Fallido: '.$msj);
            }else{
                return redirect('empresa/mensajeria')->with('success', 'Mensajes enviados correctamente.');
            }
        }
    }
}
