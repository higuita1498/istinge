<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Radicado;
use App\Servicio;
use App\User;
use App\Contacto;
use App\TipoIdentificacion;
use App\Vendedor;
use App\Model\Inventario\ListaPrecios;
use App\TipoEmpresa;
use App\Contrato;
use App\Funcion;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class AsignacionesController extends Controller
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
    view()->share(['seccion' => 'contratos', 'subseccion' => 'asignaciones', 'title' => 'Asignaciones', 'icon' =>'fas fa-file-contract', 'invert' => true]);
  }

  public function index(){
    $this->getAllPermissions(Auth::user()->id);
    $contratos = Contacto::where('firma_isp','<>',null)->where('empresa', Auth::user()->empresa)->OrderBy('nombre')->get();

    return view('asignaciones.index')->with(compact('contratos'));
  }

  public function create(){
    $this->getAllPermissions(Auth::user()->id);
    $clientes = Contacto::where('firma_isp',null)->where('empresa', Auth::user()->empresa)->OrderBy('nombre')->get();

    view()->share(['title' => 'Asignación de Contrato de Internet']);
    return view('asignaciones.create')->with(compact('clientes'));
  }

  public function store(Request $request){
    if (!$request->id){
        $mensaje='Debe seleccionar un cliente para la asignación del contrato digital';
        return back()->with('danger', $mensaje);
    }
    if (!$request->id || !$request->firma_isp || !$request->file('documento')){
        $mensaje='Debe adjuntar la documentación para la asignación del contrato digital';
        return back()->with('danger', $mensaje);
    }
    if (!$request->id || !$request->firma_isp || !$request->file('documento')){
        $mensaje='Debe realizar la firma de aceptación de contrato para la asignación';
        return back()->with('danger', $mensaje);
    }
    
    $contrato = Contacto::where('id',$request->id)->where('empresa', Auth::user()->empresa)->first();
    if ($contrato) {
      $contrato->firma_isp = $request->firma_isp;
      $contrato->fecha_isp = date('Y-m-d');
      
      $file = $request->file('documento');
      $nombre =  $file->getClientOriginalName();
      Storage::disk('documentos')->put($nombre, \File::get($file));
      $contrato->documento = $nombre;
      $contrato->save();
      $mensaje='Se ha registrado satisfactoriamente la asignación del contrato digital.';
      return redirect('empresa/asignaciones')->with('success', $mensaje);
    }
    return redirect('empresa/asignaciones')->with('success', 'No existe un registro con ese id');
  }

  public function imprimir($id){
    $contrato = Contacto::where('id',$id)->where('empresa', Auth::user()->empresa)->first();

    if($contrato) {
      view()->share(['title' => 'Contrato de Internet']);
      $pdf = PDF::loadView('pdf.contrato', compact('contrato'));
      return  response ($pdf->stream())->withHeaders(['Content-Type' =>'application/pdf',]);
    }
  }
}
