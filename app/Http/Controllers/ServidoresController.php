<?php

namespace App\Http\Controllers;
use App\Empresa;
use Illuminate\Http\Request;
use App\Contrato;
use App\Servidor;
use App\User;
use App\Funcion;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;

class ServidoresController extends Controller
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
    view()->share(['seccion' => 'servidores', 'title' => 'Servidores', 'icon' =>'fas fa-server']);
  }

  /**
  * Index para ver los contratos
  * @return view
  */
  public function index(){
    $this->getAllPermissions(Auth::user()->id);
    $servidores = Servidor::all();

    return view('servidores.index')->with(compact('servidores'));
  }

  public function aplicar_cambios($id){
    $sql = "SELECT * FROM servidores WHERE public_id = $id";
    $servidores = DB::select($sql);
    $empresa = Empresa::find(1);
    foreach ($servidores as $servidor){
        if($servidor->type == 'Bmu'){
            $path = 'https://www.cloud.wispro.co/api/v1/bmus/'.$servidor->id.'/apply_changes';
        }else{
            $path = 'https://www.cloud.wispro.co/api/v1/mikrotiks/'.$servidor->id.'/apply_changes';
        }
    }
    
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "PUT",
      CURLOPT_HTTPHEADER => array(
        "Authorization: ".$empresa->wispro
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return back()->with('danger', $err);
    } else {
        $response = json_decode($response, true);
        if($response['status'] == 200){
            $mensaje = 'Cambios al servidor aplicados correctamente';
            $alert = 'success';
        }else if($response['status'] == 404){
            $mensaje = 'Ha ocurrido un error, registro no encontrado. Intente nuevamente';
            $alert = 'danger';
        }else if($response['status'] == 412){
            $mensaje = 'No existen cambios para sincronizar hacia el BMU';
            $alert = 'danger';
        }else{
            $mensaje = 'Ha ocurrido un error, cambios no aplicados. Intente nuevamente';
            $alert = 'danger';
        }
      return back()->with($alert, $mensaje);
    }
  }
}
