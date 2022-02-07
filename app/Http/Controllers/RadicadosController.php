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
use App\PlanesVelocidad;
use App\Empresa;
use App\Funcion;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use Barryvdh\DomPDF\Facade as PDF;

class RadicadosController extends Controller
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
    view()->share(['subseccion' => 'radicados', 'title' => 'Radicados', 'icon' =>'far fa-life-ring', 'seccion' => 'atencion_cliente']);
  }

  /**
  * Index para ver los radicado registrados
  * @return view
  */
  public function index(){
    $this->getAllPermissions(Auth::user()->id);
    $con=0;
    foreach ($_SESSION['permisos'] as $key => $value) {
      ($key==208) ? $con++ : $con=0;
    }
    $radicados = (User::where('id',Auth::user()->id)->first()->rol == 43) ? Radicado::join('servicios as s', 's.id','=','radicados.servicio')->select('radicados.*', 's.nombre as nombre_servicio')->where('tecnico',Auth::user()->id)->orderby('direccion','ASC')->get() : Radicado::join('servicios as s', 's.id','=','radicados.servicio')->select('radicados.*', 's.nombre as nombre_servicio')->get();

    return view('radicados.index')->with(compact('radicados'));
  }

  /**
  * Formulario para crear un nuevo radicado
  * @return view
  */
  public function create(){
    $this->getAllPermissions(Auth::user()->id);
    $clientes = Contacto::where('status',1)->orderBy('nombre','asc')->get();

    $identificaciones = TipoIdentificacion::all();
    $vendedores = Vendedor::where('empresa',Auth::user()->empresa)->where('estado', 1)->get();
    $listas = ListaPrecios::where('empresa',Auth::user()->empresa)->where('status', 1)->get();
    $tipos_empresa=TipoEmpresa::where('empresa',Auth::user()->empresa)->get();
    $prefijos=DB::table('prefijos_telefonicos')->get();
    $paises  =DB::table('pais')->get();
    $departamentos = DB::table('departamentos')->get();
    $planes = PlanesVelocidad::all();
    $servicios = Servicio::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
    $tecnicos = User::where('empresa',Auth::user()->empresa)->where('rol', 4)->get();

    view()->share(['icon'=>'far fa-life-ring', 'title' => 'Nuevo Caso']);
    return view('radicados.create')->with(compact('clientes','identificaciones','paises','departamentos', 'tipos_empresa', 'prefijos', 'vendedores', 'listas','planes','servicios','tecnicos'));
  }

  /**
  * Registrar un nuevo radicado
  * @param Request $request
  * @return redirect
  */
  public function store(Request $request){
    $request->validate([
          'cliente' => 'required',
          'fecha' => 'required',
          'desconocido' => 'required',
          'servicio' => 'required',
          'estatus' => 'required',
          'telefono' => 'required',
          'direccion' => 'required'
    ]);
    
    if(!$request->contrato && $request->servicio != 4){
        $mensaje='El cliente no posee contrato asignado y no puede hacer uso de un servicio distinto a instalaciones';
        return back()->withInput()->with('danger', $mensaje);
    }

    $radicado = new Radicado;
    $radicado->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
    $radicado->identificacion = $request->ident;
    $radicado->cliente = $request->id_cliente;
    $radicado->nombre = $request->nombre;
    $radicado->telefono = $request->telefono;
    $radicado->correo = $request->correo;
    $radicado->direccion = $request->direccion;
    $radicado->contrato = $request->contrato;
    $radicado->desconocido = $request->desconocido;
    $radicado->servicio = $request->servicio;
    $radicado->tecnico = $request->tecnico;
    $radicado->estatus = $request->estatus;
    $radicado->codigo = rand(0, 99999);
    $radicado->mac_address = $request->mac_address;
    $radicado->ip = $request->ip;
    $radicado->empresa = Auth::user()->empresa;
    $radicado->responsable = Auth::user()->id;
    $radicado->valor = ($request->servicio == 4) ? $request->valor : null;
    $radicado->save();

    $mensaje='Se ha creado satisfactoriamente el radicado bajo el código #'.$radicado->codigo;
    return redirect('empresa/radicados')->with('success', $mensaje);
  }

  /**
  * Formulario para modificar los datos de un radicado
  * @param int $id
  * @return view
  */
  public function edit($id){
    $this->getAllPermissions(Auth::user()->id);
    $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id', $id)->first();
    $servicios = Servicio::where('empresa',Auth::user()->empresa)->where('estatus', 1)->get();
    $tecnicos = User::where('empresa',Auth::user()->empresa)->where('rol', 4)->get();

    if ($radicado) {
      view()->share(['icon'=>'far fa-life-ring', 'title' => 'Modificar Radicado: '.$radicado->codigo]);
      return view('radicados.edit')->with(compact('radicado','servicios','tecnicos'));
    }
    return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Modificar los datos de un radicado
  * @param Request $request
  * @return redirect
  */
  public function update(Request $request, $id){
    $radicado =Radicado::find($id);
    if ($radicado) {
        if ($request->reporte) {
            $radicado->reporte = $request->reporte;
            $radicado->save();
            $mensaje='Se ha registrado el reporte del técnico satisfactoriamente.';
            return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
        }
        $request->validate([
            'telefono' => 'required|numeric',
            'direccion' => 'required|max:200',
            'fecha' => 'required',
            'servicio' => 'required',
            'estatus' => 'required',
            'desconocido' => 'required'
        ]);
      
        if(!$request->contrato && $request->servicio != 4){
            $mensaje='El cliente no posee contrato asignado y no puede hacer uso de un servicio distinto a instalaciones';
            return back()->withInput()->with('danger', $mensaje);
        }

      $radicado->fecha=Carbon::parse($request->fecha)->format('Y-m-d');
      $radicado->telefono = $request->telefono;
      $radicado->correo = $request->correo;
      $radicado->direccion = $request->direccion;
      $radicado->desconocido = $request->desconocido;
      $radicado->servicio = $request->servicio;
      $radicado->tecnico = $request->tecnico;
      $radicado->estatus = $request->estatus;
      $radicado->responsable = Auth::user()->id;
      $radicado->valor = ($request->servicio == 4) ? $request->valor : null;
      $radicado->save();

      $mensaje='Se ha modificado satisfactoriamente el radicado #'.$radicado->codigo;
      return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
    }
    return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Ver un radicado
  * @param int $id
  * @return view
  */
  public function show($id){
    $this->getAllPermissions(Auth::user()->id);
    $radicado=Radicado::find($id);

    if ($radicado) {
      view()->share(['icon'=>'far fa-life-ring', 'title' => 'Detalles Radicado: '.$radicado->codigo]);
      $inicio = Carbon::parse($radicado->tiempo_ini);
      $cierre = Carbon::parse($radicado->tiempo_fin);
      $duracion = $inicio->diffInMinutes($cierre);
      return view('radicados.show')->with(compact('radicado','duracion'));
    }
    return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Eliminar un radicado
  * @param int $id
  * @return redirect
  */
  public function destroy($id){
    $radicado=Radicado::find($id);
    if ($radicado) {
      $radicado->delete();
    }
    return redirect('empresa/radicados')->with('success', 'El radicado ha sido eliminado satisfactoriamente');
  }

  /**
  * Funcion para estacalar un radicado a soporte técnico
  * @param int $id
  * @return redirect
  */
  public function escalar($id){
    $radicado=Radicado::find($id);
    if ($radicado) {
      if ($radicado->estatus==0) {
        $radicado->estatus=2;
        $mensaje='Se ha escalado el caso a soporte técnico';
        $radicado->save();
        return back()->with('success', $mensaje);
      }
    }
    return back('empresa/radicados')->with('success', 'No existe un registro con ese id');
  }

  /**
  * Funcion para dar por solventado un radicado
  * @param int $id
  * @return redirect
  */
  public function solventar($id){
      $this->getAllPermissions(Auth::user()->id);
    $radicado=Radicado::find($id);
    if ($radicado) {
      if ($radicado->estatus==0) {
        $radicado->estatus=1;
      }else if ($radicado->estatus==2) {
        $radicado->estatus=3;
      }
      $mensaje='Se ha resuelto el caso radicado';
      $radicado->save();
      return back()->with('success', $mensaje);
    }
    return back('empresa/radicados')->with('success', 'No existe un registro con ese id');
  }

  public function imprimir($id){

    $radicado = Radicado::where('empresa',Auth::user()->empresa)->where('id',$id)->first();

    if($radicado) {
      view()->share(['title' => 'Caso Radicado #'.$radicado->codigo]);
      $pdf = PDF::loadView('pdf.radicados', compact('radicado'));
      return  response ($pdf->stream())->withHeaders([
        'Content-Type' =>'application/pdf',]);
    }
  }

  public function firmar($id){
      $this->getAllPermissions(Auth::user()->id);
    $radicado=Radicado::find($id);
    view()->share(['icon'=>'far fa-life-ring', 'title' => 'Firma Radicado: '.$radicado->codigo]);
    return view('radicados.firma')->with(compact('radicado'));
  }

  public function storefirma(Request $request, $id){
    $radicado =Radicado::find($id);
    if ($radicado) {
      $radicado->firma = $request->dataImg;
      $radicado->save();
      $mensaje='Se ha registrado la firma del cliente.';
      return redirect('empresa/radicados/'.$id)->with('success', $mensaje);
    }
    return redirect('empresa/radicados')->with('success', 'No existe un registro con ese id');
  }

  public function contacts_wispro($clienteApi){
    $curl = curl_init();
    
    //$clienteApi = str_replace("%20", " ", $clienteApi)."&per_page=100";
    $empresa = Empresa::find(1);

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/clients/?name_unaccent_cont=".$clienteApi,
      //CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/clients/?name_unaccent_cont=".str_replace("%20", " ", $clienteApi)."&per_page=100",
      //CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/clients/?name_unaccent_cont=".$clienteApi."&per_page=100",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: ".$empresa->wispro
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }
  }

  public function contact_wispro($clienteApi){
    $curl = curl_init();
    $empresa = Empresa::find(1);

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/clients/".$clienteApi,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: ".$empresa->wispro
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }
  }

  public function contract_wispro($id){
    $curl = curl_init();
    $empresa = Empresa::find(1);

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/contracts?client_id_eq=".$id,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: ".$empresa->wispro
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }
  }

  public function plan_wispro($id){
    $curl = curl_init();
    $empresa = Empresa::find(1);

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/plans/".$id,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: ".$empresa->wispro
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }
  }

  public function plans_wispro(){
    $curl = curl_init();
    $empresa = Empresa::find(1);

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/plans/",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: ".$empresa->wispro
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $response;
    }
  }

    public function notificacion(){
        $radicado=Radicado::where('tecnico',Auth::user()->id)->where('estatus',2)->get();
        return json_encode($radicado);
    }
  
    public function datatable_cliente($contacto, Request $request){
        $requestData =  $request;
        
        $columns = array(
            // datatable column index  => database column name
            0 => 'radicados.codigo',
            1 => 'radicados.fecha',
            2 => 'radicados.tipo',
            3 => 'radicados.status'
        );
        
        $requestData =  $request;
        
        $movimientos=Radicado::leftjoin('contactos as c', 'radicados.identificacion', '=', 'c.nit')    
        ->select('radicados.*')
        ->where('radicados.empresa',Auth::user()->empresa);
        
        if ($contacto) { $movimientos=$movimientos->where('radicados.identificacion', $contacto); }
        //Busca los campos saldo, fecha y nombre del cliente
        if ($requestData->search['value']) {
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $movimientos=$movimientos->where(function ($query) use ($requestData) {
                $query->where('radicados.identificacion', 'like', '%'.$requestData->search['value'].'%')
                ->orwhere('radicados.nombre', 'like', '%'.$requestData->search['value'].'%')
                ->orwhere('radicados.fecha', 'like', '%'.$requestData->search['value'].'%');
            });
        }
        $totalFiltered=$totalData=$movimientos->count();
        
        $movimientos=$movimientos->skip($requestData['start'])->take($requestData['length']);
        $movimientos=$movimientos->orderBy('fecha', 'desc');
        $movimientos=$movimientos->get();
        $data = array();
        foreach ($movimientos as $movimiento) {
            $nestedData = array();
            $nestedData[] = '<a href="'.$movimiento->show_url().'">'.$movimiento->codigo.'</a>';
            $nestedData[] = date('d-m-Y', strtotime($movimiento->fecha));
            $nestedData[] = $movimiento->servicio()->nombre;
            $nestedData[] = '<strong><span class="text-'.$movimiento->estatus('true').'">'.$movimiento->estatus().'</span></strong>';
            $data[] = $nestedData;
        }
        
        $json_data = array(
            "draw" => intval($requestData->draw),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($totalData),  // total number of records
            "recordsFiltered" => intval($totalFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );
        return json_encode($json_data);
    }
    
    public function proceder($id){
        $this->getAllPermissions(Auth::user()->id);
        $radicado=Radicado::find($id);
        if ($radicado) {
            if ($radicado->tiempo_fin) {
                $radicado->tiempo_ini = Carbon::now()->toDateTimeString();
                $radicado->tiempo_est = $radicado->servicio()->tiempo;
                $mensaje = 'Radicado Iniciado, recuerde que tiene un tiempo de '.$radicado->tiempo_est.'min para solventarlo';
            }else{
                $radicado->tiempo_fin = Carbon::now()->toDateTimeString();
                
                $inicio = Carbon::parse($radicado->tiempo_ini);
                $cierre = Carbon::parse($radicado->tiempo_fin);
                $duracion = $inicio->diffInMinutes($cierre);
                
                $mensaje = 'Radicado Finalizado, con una duración de '.$duracion.'min';
            }
            
            $radicado->save();
            return back()->with('success', $mensaje);
        }
        return back('empresa/radicados')->with('danger', 'No existe un registro con ese id');
    }
}
