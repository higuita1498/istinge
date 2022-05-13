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
use App\Empresa;

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
    view()->share(['seccion' => 'contratos', 'subseccion' => 'asignaciones', 'title' => 'Asignaciones', 'icon' =>'fas fa-file-contract']);
  }

  public function index(){
    $this->getAllPermissions(Auth::user()->id);
    $contratos = Contacto::where('firma_isp','<>',null)->where('empresa', Auth::user()->empresa)->where('status', 1)->OrderBy('nombre')->get();
    view()->share(['middel' => true]);

    return view('asignaciones.index')->with(compact('contratos'));
  }

  public function create(){
    $this->getAllPermissions(Auth::user()->id);
    $clientes = Contacto::where('firma_isp',null)->where('empresa', Auth::user()->empresa)->OrderBy('nombre')->get();
    $empresa = Empresa::find(Auth::user()->empresa);

    view()->share(['title' => 'Asignación de Contrato de Internet']);
    return view('asignaciones.create')->with(compact('clientes', 'empresa'));
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

      if($request->file('imgA')){
        $file = $request->file('imgA');
        $nombre =  $file->getClientOriginalName();
        Storage::disk('documentos')->put($nombre, \File::get($file));
        $contrato->imgA = $nombre;
      }

      if($request->file('imgB')){
        $file = $request->file('imgB');
        $nombre =  $file->getClientOriginalName();
        Storage::disk('documentos')->put($nombre, \File::get($file));
        $contrato->imgB = $nombre;
      }

      if($request->file('imgC')){
        $file = $request->file('imgC');
        $nombre =  $file->getClientOriginalName();
        Storage::disk('documentos')->put($nombre, \File::get($file));
        $contrato->imgC = $nombre;
      }

      if($request->file('imgD')){
        $file = $request->file('imgD');
        $nombre =  $file->getClientOriginalName();
        Storage::disk('documentos')->put($nombre, \File::get($file));
        $contrato->imgD = $nombre;
      }

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

  public function show_campos_asignacion(){
    $empresa = Empresa::find(Auth::user()->empresa);
    return json_encode($empresa);
  }

  public function campos_asignacion(Request $request){
    $empresa = Empresa::find(Auth::user()->empresa);
    if($empresa){
      $empresa->campo_a = $request->campo_a;
      $empresa->campo_b = $request->campo_b;
      $empresa->campo_c = $request->campo_c;
      $empresa->campo_d = $request->campo_d;
      $empresa->save();

      return response()->json([
        'success' => true,
        'campo_a' => $empresa->campo_a,
        'campo_b' => $empresa->campo_b,
        'campo_c' => $empresa->campo_c,
        'campo_d' => $empresa->campo_d
      ]);
    }
    return response()->json(['success' => false]);
  }
}
