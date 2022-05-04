<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use stdClass;
use Auth; 
use DB;
use App\Empresa;
use Carbon\Carbon; 
use App\Plantilla;
use App\Contrato;
use App\Mikrotik;

include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;

class PlantillasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
        view()->share(['inicio' => 'master', 'seccion' => 'avisos', 'subseccion' => 'plantillas', 'title' => 'Gestión de Plantillas', 'icon' =>'fas fa-file-code']);
    }

    
    public function index()
    {
        $this->getAllPermissions(Auth::user()->id);
        $plantillas = Plantilla::all();
        return view('plantillas.index')->with(compact('plantillas'));
    }
    
    public function create()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nueva Plantilla']);
        $name = '{{ $name }}'; $company = '{{ $company }}'; $nit = '{{ $nit }}'; $date = '{{ $date }}';
        return view('plantillas.create')->with(compact('name', 'company', 'nit', 'date'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $request->validate([
            'title' => 'required',
            'tipo' => 'required',
            'clasificacion' => 'required'
        ]);

        if (Plantilla::where('empresa', Auth::user()->empresa)->count() > 0) {
            $nro = Plantilla::where('empresa', Auth::user()->empresa)->get()->last()->nro;
        }else{
            $nro = 0;
        }
        
        $plantilla = new Plantilla();
        $plantilla->nro = $nro + 1;
        $plantilla->tipo = $request->tipo;
        $plantilla->clasificacion = $request->clasificacion;
        $plantilla->title = $request->title;

        if($request->tipo==0){
            $plantilla->contenido = strip_tags($request->contenido_sms);
        }elseif($request->tipo==1){
            $plantilla->contenido = $request->contenido;
        }elseif($request->tipo==2){
            $plantilla->contenido = $request->contenido_whatsapp;
        }

        $plantilla->created_by = Auth::user()->id;
        $plantilla->status = 0;
        $plantilla->save();

        if($plantilla->tipo==1){
            $plantilla->archivo = 'plantilla'.$plantilla->id;
            $plantilla->save();
            Storage::disk('emails')->put($plantilla->archivo.'.blade.php', $plantilla->contenido);
        }

        $mensaje = 'SE HA CREADO SATISFACTORIAMENTE LA PLANTILLA';
        return redirect('empresa/plantillas')->with('success', $mensaje)->with('plantilla_id', $plantilla->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $plantilla = Plantilla::find($id);
        
        if($plantilla){
            view()->share(['title' => $plantilla->title]);
            return view('plantillas.show')->with(compact('plantilla'));
        }
        return redirect('empresa/plantillas')->with('danger', 'No existe un registro con ese id');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $plantilla = Plantilla::find($id);
        
        if($plantilla){
            view()->share(['title' => 'Editar Plantilla: '.$plantilla->nro]);
            $name = '{{ $name }}'; $company = '{{ $company }}'; $nit = '{{ $nit }}'; $date = '{{ $date }}';
            return view('plantillas.edit')->with(compact('name', 'company', 'nit', 'date', 'plantilla'));
        }
        return redirect('empresa/plantillas')->with('danger', 'No existe un registro con ese id');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'tipo' => 'required',
            'clasificacion' => 'required'
        ]);
        
        $plantilla = Plantilla::find($id);
        
        if($plantilla){
            if($plantilla->tipo==1){
                Storage::disk('emails')->delete($plantilla->archivo.'.blade.php');
            }

            $plantilla->tipo = $request->tipo;
            $plantilla->clasificacion = $request->clasificacion;
            $plantilla->title = $request->title;

            if($request->tipo==0){
                $plantilla->contenido = strip_tags($request->contenido_sms);
            }elseif($request->tipo==1){
                $plantilla->contenido = $request->contenido;
            }elseif($request->tipo==2){
                $plantilla->contenido = $request->contenido_whatsapp;
            }

            $plantilla->updated_by = Auth::user()->id;
            $plantilla->save();

            if($plantilla->tipo==1){
                Storage::disk('emails')->put($plantilla->archivo.'.blade.php', $plantilla->contenido);
            }
            
            $mensaje = 'SE HA ACTUALIZADO SATISFACTORIAMENTE LA PLANTILLA';
            return redirect('empresa/plantillas')->with('success', $mensaje)->with('plantilla_id', $plantilla->id);
        }
        return redirect('empresa/plantillas')->with('danger', 'No existe un registro con ese id');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $plantilla = Plantilla::find($id);
        
        if($plantilla){
            if($plantilla->tipo==1){
                Storage::disk('emails')->delete($plantilla->archivo.'.blade.php');
            }
            $plantilla->delete();
            $mensaje = 'SE HA ELIMINADO SATISFACTORIAMENTE LA PLANTILLA';
            return redirect('empresa/plantillas')->with('success', $mensaje);
        }
        return redirect('empresa/plantillas')->with('danger', 'No existe un registro con ese id');
    }
    
    public function act_desc($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $plantilla = Plantilla::find($id);
        
        if($plantilla){
            if($plantilla->status == 0){
                $plantilla->status = 1;
                $mensaje = 'SE HA ACTIVADO SATISFACTORIAMENTE LA PLANTILLA';
                $search = Plantilla::where('status', 1)->where('tipo', $plantilla->tipo)->where('clasificacion', 'Bienvenida')->count();

                if($search>0){
                    $mensaje='YA EXISTE UNA PLANTILLA DE BIENVENIDA HABILITADA.';
                    return redirect('empresa/plantillas')->with('danger', $mensaje);
                }
            }else{
                $plantilla->status = 0;
                $mensaje = 'SE HA DESACTIVADO SATISFACTORIAMENTE LA PLANTILLA';
            }

            $plantilla->save();
            return redirect('empresa/plantillas')->with('success', $mensaje);
        }
        return redirect('empresa/plantillas')->with('danger', 'No existe un registro con ese id');
    }
    
    public function envio()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Envío de Aviso', 'icon' => 'fas fa-paper-plane']);
        $plantillas = Plantilla::where('status', 1)->get();
        $contratos = Contrato::select('contracts.*', 'contactos.id as c_id', 'contactos.nombre as c_nombre', 'contactos.nit as c_nit', 'contactos.telefono1 as c_telefono', 'contactos.email as c_email', 'contactos.barrio as c_barrio')
			->join('contactos', 'contracts.client_id', '=', 'contactos.id')
			->where('contracts.status', 1)->get();
			
        return view('plantillas.envio')->with(compact('plantillas','contratos'));
    }
    
    public function envio_aviso(Request $request)
    {
        $posi = 0;$nega = 0;
        for ($i = 0; $i < count($request->contrato); $i++) {
            $contrato = Contrato::find($request->contrato[$i]);
            
            if ($contrato) {
                $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();
                
                $API = new RouterosAPI();
                $API->port = $mikrotik->puerto_api;
                
                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    $API->comm("/ip/proxy/access/add", array(
                        "src-address" => $contrato->ip,
                        "action"      => "deny",
                        "redirect-to" => '',
                        "comment"     => $contrato->cliente()->nombre.' - Aviso'
                        )
                    );
                     
                    $API->disconnect();
                    $posi++;
                }else{
                    $nega++;
                }
            }
        }
        
        return redirect('empresa/plantillas')->with('success', 'AVISOS ENVIADOS '.$posi.' | NO ENVIADOS '.$nega);
    }
    
    
}
