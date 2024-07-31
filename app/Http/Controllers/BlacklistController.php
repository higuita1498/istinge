<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Mail;
use Validator;
use Illuminate\Validation\Rule;
use Auth;
use DB;
use Session;

use App\Empresa;
use App\Blacklist;
use App\Campos;

class BlacklistController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'mikrotik', 'subseccion' => 'gestion_blacklist', 'title' => 'Monitor Blacklist', 'icon' =>'fas fa-server']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $tabla = Campos::join('campos_usuarios', 'campos_usuarios.id_campo', '=', 'campos.id')->where('campos_usuarios.id_modulo', 13)->where('campos_usuarios.id_usuario', Auth::user()->id)->where('campos_usuarios.estado', 1)->orderBy('campos_usuarios.orden', 'ASC')->get();
        view()->share(['middel' => true]);
        return view('monitor-blacklist.index', compact('tabla'));
    }

    public function blacklist(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $blacklists = Blacklist::query()
            ->where('empresa', Auth::user()->empresa);

        if ($request->filtro == true) {
            if($request->nombre){
                $blacklists->where(function ($query) use ($request) {
                    $query->orWhere('nombre', $request->nombre);
                });
            }
            if($request->ip){
                $blacklists->where(function ($query) use ($request) {
                    $query->orWhere('ip', $request->ip);
                });
            }
            if($request->estado >= 0){
                $blacklists->where(function ($query) use ($request) {
                    $query->orWhere('estado', $request->estado);
                });
            }
        }

        return datatables()->eloquent($blacklists)
            ->editColumn('id', function (Blacklist $blacklist) {
                return "<a href=" . route('monitor-blacklist.show', $blacklist->id) . ">{$blacklist->id}</a>";
            })
            ->editColumn('nombre', function (Blacklist $blacklist) {
                return $blacklist->nombre;
            })
            ->editColumn('ip', function (Blacklist $blacklist) {
                return $blacklist->ip;
            })
            ->editColumn('blacklisted_count', function (Blacklist $blacklist) {
                return $blacklist->blacklisted_count.' sitios';
            })
            ->editColumn('estado', function (Blacklist $blacklist) {
                return "<span class='text-{$blacklist->estado("true")}'><strong>{$blacklist->estado()}</strong></span>";
            })
            ->addColumn('acciones', $modoLectura ?  "" : "monitor-blacklist.acciones")
            ->rawColumns(['acciones', 'nombre', 'id', 'estado'])
            ->toJson();
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Nuevo Monitor Blacklist']);
        return view('monitor-blacklist.create');
    }

    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|max:250',
            'ip' => 'required|max:250',
        ]);

        $empresa = Empresa::find(Auth::user()->empresa);
        $api_key = $empresa->api_key_hetrixtools;
        $contact = $empresa->id_contacto_hetrixtools;

        if(!isset($api_key) || !isset($contact)){
            return redirect('empresa/monitor-blacklist/api')->with('danger', 'Disculpe, debe configurar el api key y el ID de la lista de contactos para hacer uso de monitor blacklist.');
        }

        $url = 'https://api.hetrixtools.com/v2/'.$api_key.'/blacklist/add/';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, ["target" => $request->ip, "label" => $request->nombre, "contact" => $contact]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($result, true);
        if($response['status'] == 'ERROR'){
            if($response['error_message'] == 'you are already monitoring this ip address'){
                $error = 'ERROR API: YA ESTÁS MONITOREANDO ESTA DIRECCIÓN IP';
            }else if($response['error_message'] == 'invalid ip address or range'){
                $error = 'ERROR API: DIRECCIÓN IP INVÁLIDA';
            }else if($response['error_message'] == 'invalid contact list'){
                $error = 'ERROR API: LISTA DE CONTACTO INVÁLIDA. VERIFIQUE LA CONFIGURACIÓN API.';
            }
            return back()->with('danger', $error);
        }else{
            $blacklist = new Blacklist;
            $blacklist->nombre = $request->nombre;
            $blacklist->ip = $request->ip;
            $blacklist->created_by = Auth::user()->id;
            $blacklist->empresa = Auth::user()->empresa;
            $blacklist->save();
            $mensaje='SE HA CREADO SATISFACTORIAMENTE EL MONITOR BLACKLIST';
            return redirect('empresa/monitor-blacklist')->with('success', $mensaje);
        }
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $blacklist = Blacklist::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($blacklist) {
            view()->share(['title' => $blacklist->nombre]);
            return view('monitor-blacklist.show')->with(compact('blacklist'));
        }
        return redirect('empresa/monitor-blacklist')->with('danger', 'MONITOR BLACKLIST NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $blacklist = Blacklist::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($blacklist) {
            view()->share(['title' => 'Editar: '.$blacklist->nombre]);
            return view('monitor-blacklist.edit')->with(compact('blacklist'));
        }
        return redirect('empresa/monitor-blacklist')->with('danger', 'MONITOR BLACKLIST NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $request->validate([
            'nombre' => 'required|max:250',
            'ip' => 'required|max:250',
        ]);

        $blacklist = Blacklist::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($blacklist) {
            $empresa = Empresa::find(Auth::user()->empresa);
            $api_key = $empresa->api_key_hetrixtools;
            $contact = $empresa->id_contacto_hetrixtools;

            if(!isset($api_key) || !isset($contact)){
                return redirect('empresa/monitor-blacklist/api')->with('danger', 'Disculpe, debe configurar el api key y el ID de la lista de contactos para hacer uso de monitor blacklist.');
            }

            $url = 'https://api.hetrixtools.com/v2/'.$api_key.'/blacklist/edit/';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, ["target" => $request->ip, "label" => $request->nombre, "contact" => $contact]);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($result, true);
            if($response['status'] == 'ERROR'){
                if($response['error_message'] == 'you are already monitoring this ip address'){
                    $error = 'ERROR API: YA ESTÁS MONITOREANDO ESTA DIRECCIÓN IP';
                }else if($response['error_message'] == 'invalid ip address or range'){
                    $error = 'ERROR API: DIRECCIÓN IP INVÁLIDA';
                }else if($response['error_message'] == 'invalid contact list'){
                    $error = 'ERROR API: LISTA DE CONTACTO INVÁLIDA. VERIFIQUE LA CONFIGURACIÓN API.';
                }
                return back()->with('danger', $error);
            }else{
                $blacklist->nombre = $request->nombre;
                $blacklist->ip = $request->ip;
                $blacklist->updated_by = Auth::user()->id;
                $blacklist->save();
                $mensaje='SE HA ACTUALIZADO SATISFACTORIAMENTE EL MONITOR BLACKLIST';
                return redirect('empresa/monitor-blacklist')->with('success', $mensaje);
            }
        }
        return redirect('empresa/monitor-blacklist')->with('danger', 'MONITOR BLACKLIST NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function destroy($id){
        $blacklist = Blacklist::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($blacklist) {
            $empresa = Empresa::find(Auth::user()->empresa);
            $api_key = $empresa->api_key_hetrixtools;
            $contact = $empresa->id_contacto_hetrixtools;

            if(!isset($api_key) || !isset($contact)){
                return redirect('empresa/monitor-blacklist/api')->with('danger', 'Disculpe, debe configurar el api key y el ID de la lista de contactos para hacer uso de monitor blacklist.');
            }

            $url = 'https://api.hetrixtools.com/v2/'.$api_key.'/blacklist/delete/';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, ["target" => $blacklist->ip]);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($result, true);
            if($response['status'] == 'ERROR'){
                if($response['error_message'] == 'you are already monitoring this ip address'){
                    $error = 'ERROR API: YA ESTÁS MONITOREANDO ESTA DIRECCIÓN IP';
                }else if($response['error_message'] == 'invalid ip address or range'){
                    $error = 'ERROR API: DIRECCIÓN IP INVÁLIDA';
                }else if($response['error_message'] == 'invalid contact list'){
                    $error = 'ERROR API: LISTA DE CONTACTO INVÁLIDA. VERIFIQUE LA CONFIGURACIÓN API.';
                }
                return back()->with('danger', $error);
            }else{
                $blacklist->delete();
                $mensaje = 'SE HA ELIMINADO EL MONITOR BLACKLIST CORRECTAMENTE';
                return redirect('empresa/monitor-blacklist')->with('success', $mensaje);
            }
        }else{
            return redirect('empresa/monitor-blacklist')->with('danger', 'MONITOR BLACKLIST NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }

    public function verificar($id){
        $blacklist = Blacklist::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($blacklist) {
            $empresa = Empresa::find(Auth::user()->empresa);
            $api_key = $empresa->api_key_hetrixtools;
            $contact = $empresa->id_contacto_hetrixtools;

            if(!isset($api_key) || !isset($contact)){
                return redirect('empresa/monitor-blacklist/api')->with('danger', 'Disculpe, debe configurar el api key y el ID de la lista de contactos para hacer uso de monitor blacklist.');
            }

            $url = 'https://api.hetrixtools.com/v2/'.$api_key.'/blacklist-check/ipv4/'.$blacklist->ip.'/';

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $result = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($result, true);
            if($response['status'] == 'ERROR'){
                if($response['error_message'] == 'you are already monitoring this ip address'){
                    $error = 'ERROR API: YA ESTÁS MONITOREANDO ESTA DIRECCIÓN IP';
                }else if($response['error_message'] == 'invalid ip address or range'){
                    $error = 'ERROR API: DIRECCIÓN IP INVÁLIDA';
                }else if($response['error_message'] == 'invalid contact list'){
                    $error = 'ERROR API: LISTA DE CONTACTO INVÁLIDA. VERIFIQUE LA CONFIGURACIÓN API.';
                }
                return back()->with('danger', $error);
            }else{
                $blacklist->blacklisted_count = $response['blacklisted_count'];
                $blacklist->estado = ($response['blacklisted_count'] == 0) ? 1:2;
                $blacklist->response = '';
                $blacklist->save();
                $mensaje = 'VERIFICACIÓN EXITOSA. LA IP APARECE BLOQUEADA EN '.$response['blacklisted_count'].' SITIOS';
                return redirect('empresa/monitor-blacklist')->with('success', $mensaje);
            }
        }else{
            return redirect('empresa/monitor-blacklist')->with('danger', 'MONITOR BLACKLIST NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }

    public function create_apikey(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['title' => 'Configuración API Monitor Blacklist']);
        $empresa = Empresa::find(Auth::user()->empresa);
        return view('monitor-blacklist.api', compact('empresa'));
    }

    public function store_api(Request $request){
        $request->validate([
            'api_key_hetrixtools' => 'required|max:250',
            'id_contacto_hetrixtools' => 'required|max:250',
        ]);

        $empresa = Empresa::find(Auth::user()->empresa);
        if($empresa){
            $empresa->api_key_hetrixtools = $request->api_key_hetrixtools;
            $empresa->id_contacto_hetrixtools = $request->id_contacto_hetrixtools;
            $empresa->save();
            $mensaje='SE HA RESGITRADO LA CONFIGURACIÓN SATISFACTORIAMENTE';
            return redirect('empresa/monitor-blacklist')->with('success', $mensaje);
        }
    }

    public function reporte($id){
        $this->getAllPermissions(Auth::user()->id);
        $blacklist = Blacklist::where('id', $id)->where('empresa', Auth::user()->empresa)->first();

        if ($blacklist) {
            $empresa = Empresa::find(Auth::user()->empresa);
            $api_key = $empresa->api_key_hetrixtools;
            $contact = $empresa->id_contacto_hetrixtools;

            if(!isset($api_key) || !isset($contact)){
                return redirect('empresa/monitor-blacklist/api')->with('danger', 'Disculpe, debe configurar el api key y el ID de la lista de contactos para hacer uso de monitor blacklist.');
            }

            $url = 'https://api.hetrixtools.com/v1/'.$api_key.'/blacklist/report/'.$blacklist->ip.'/';

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $result = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($result, true);

            if(!$response[0]['RBL'] == null){
                $blacklist->blacklisted_count = count($response);
                $blacklist->estado = (count($response) > 0) ? 2:1;
                $blacklist->save();
                view()->share(['title' => 'Reporte Monitor Blacklist', 'icon' =>'fas fa-file-contract']);
                return view('monitor-blacklist.reporte', compact('response', 'blacklist'));
            }else{
                $mensaje = 'LA IP NO APARECE BLOQUEADA EN NINGÚN SITIO';
                return redirect('empresa/monitor-blacklist')->with('success', $mensaje);
            }
        }else{
            return redirect('empresa/monitor-blacklist')->with('danger', 'MONITOR BLACKLIST NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }
}
