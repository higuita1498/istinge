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

use App\User;
use App\Integracion;

class IntegracionWhatsAppController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'configuracion', 'title' => 'Integración WhatsApp (CallMEBot)', 'icon' => 'fab fa-whatsapp']);
    }

    public function index(Request $request){
        $this->getAllPermissions(Auth::user()->id);
        $servicios = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'WHATSAPP')->where('lectura', 1)->get();
        return view('configuracion.integracion_whatsapp.index')->with(compact('servicios'));
    }

    public function show($id){
        $this->getAllPermissions(Auth::user()->id);
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'WHATSAPP')->where('lectura', 1)->where('id', $id)->first();

        if ($servicio) {
            view()->share(['title' => $servicio->nombre, 'precice' => true]);
            return view('configuracion.integracion_whatsapp.show')->with(compact('servicio'));
        }
        return redirect('empresa/configuracion/integracion-whatsapp')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'WHATSAPP')->where('lectura', 1)->where('id', $id)->first();

        if ($servicio) {
            view()->share(['title' => $servicio->nombre, 'middel' => true]);
            return view('configuracion.integracion_whatsapp.edit')->with(compact('servicio'));
        }
        return redirect('empresa/configuracion/integracion-whatsapp')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function update(Request $request, $id){
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'WHATSAPP')->where('lectura', 1)->where('id', $id)->first();

        if ($servicio) {
            $servicio->api_key    = $request->api_key;
            $servicio->numero     = $request->numero;
            $servicio->updated_by = Auth::user()->id;
            $servicio->save();

            $mensaje='SE HA MODIFICADO SATISFACTORIAMENTE EL SERVICIO';
            return redirect('empresa/configuracion/integracion-whatsapp')->with('success', $mensaje)->with('id', $servicio->id);
        }
        return redirect('empresa/configuracion/integracion-whatsapp')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }

    public function act_desc(Request $request, $id){
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'WHATSAPP')->where('lectura', 1)->where('id', $id)->first();

        if($servicio){
            if($servicio->status == 0){
                if($servicio->api_key && $servicio->numero){
                    $servicio->status = 1;
                    $mensaje = 'SE HA HABILITADO EL SERVICIO CORRECTAMENTE';
                }else{
                    $mensaje = 'NO ES POSIBLE HABILITAR EL SERVICIO SIN TENER EL API KEY Y NÚMERO CONFIGURADO';
                    return redirect('empresa/configuracion/integracion-whatsapp')->with('danger', $mensaje)->with('id', $servicio->id);
                }
                $servicio->save();
            }else{
                $servicio->status = 0;
                $mensaje = 'SE HA DESHABILITADO EL SERVICIO CORRECTAMENTE';
            }
            return redirect('empresa/configuracion/integracion-whatsapp')->with('success', $mensaje)->with('id', $servicio->id);
        }else{
            return redirect('empresa/configuracion/integracion-whatsapp')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
        }
    }

    public function envio_prueba($id){
        $servicio = Integracion::where('empresa', Auth::user()->empresa)->where('tipo', 'WHATSAPP')->where('lectura', 1)->where('id', $id)->first();

        if($servicio){
            if($servicio->api_key && $servicio->numero){
                $mensaje = 'SMS Prueba (CallMEBot) | Network Soft - Software Administrativo de ISP';
                $url='https://api.callmebot.com/whatsapp.php?source=php&phone=+'.$servicio->numero.'&text='.urlencode($mensaje).'&apikey='.$servicio->api_key;

                if($ch = curl_init($url)){
                    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $html = curl_exec($ch);
                    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    return back()->with('success', 'EL MENSAJE VÍA WHATSAPP HA SIDO ENVIADO DE MANERA EXITOSA');
                }else{
                    return back()->with('danger', 'EL MENSAJE VÍA WHATSAPP NO HA PODIDO SER ENVIADO');
                }
            }
        }
        return redirect('empresa/configuracion/integracion-whatsapp')->with('danger', 'SERVICIO NO ENCONTRADO, INTENTE NUEVAMENTE');
    }
}
