<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Solicitud;
use App\Servicio;
use App\User;
use App\Contacto;
use App\TipoIdentificacion;
use App\Vendedor;
use App\Model\Inventario\ListaPrecios;
use App\TipoEmpresa;

use App\Funcion;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use Barryvdh\DomPDF\Facade as PDF;
use Mail; 

use App\Mail\SolicitudMailable;

class SolicitudesController extends Controller
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
    view()->share(['subseccion' => 'solicitudes', 'title' => 'Solicitudes', 'icon' =>'fas fa-file-invoice', 'seccion' => 'atencion_cliente', 'invert' => true]);
  }

  /**
  * Index para ver los radicado registrados
  * @return view
  */
  public function index(){
    $this->getAllPermissions(Auth::user()->id);

    $solicitudes = Solicitud::where('empresa', Auth::user()->empresa)->get();

    return view('solicitudes.index')->with(compact('solicitudes'));
  }

  public function create(){
    
  }

  public function store(Request $request){
      
  }

  public function edit($id){
      
  }

  public function update(Request $request, $id){
      
  }

  public function show($id){
    $this->getAllPermissions(Auth::user()->id);
    $solicitud=Solicitud::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

    if ($solicitud) {
      view()->share(['icon'=>'fas fa-file-invoice', 'title' => 'Detalles Solicitud: '.$solicitud->id]);
      return view('solicitudes.show')->with(compact('solicitud'));
    }
    return back()->with('success', 'No existe un registro con ese id');
  }

  public function destroy($id){
      
  }
  
  public function status($id){
    $solicitud=Solicitud::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
    if ($solicitud) {
        $solicitud->status = ($solicitud->status == 1) ? 0 : 1;
        $solicitud->save();
        
        $datos = array(
            'fecha'=> $solicitud->fecha,
            'nombres'=> $solicitud->nombre,
            'plan'=> $solicitud->plan,
            'direccion'=> $solicitud->direccion
        );
    
        $correo = new SolicitudMailable($datos);
        Mail::to($solicitud->email)->send($correo);
        return redirect('empresa/solicitudes')->with('success', 'Se ha registrado la respuesta a la solicitud de servicio satisfactoriamente.');
    }
    return back('empresa/solicitudes')->with('success', 'No existe un registro con ese id');
  }
}
