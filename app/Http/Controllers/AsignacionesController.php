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
use App\PlanesVelocidad;
use Validator;
use Auth;
use DB;
use Carbon\Carbon;
use Session;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;
use App\Empresa;
use App\ServidorCorreo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Mail;

class AsignacionesController extends Controller
{
    public function __construct()
    {
      //  $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'contratos', 'subseccion' => 'asignaciones', 'title' => 'Asignaciones', 'icon' =>'fas fa-file-contract']);
    }

    public function index()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['invert' => true]);
        if(auth()->user()->empresa()->oficina) {
            $contratos = Contacto::where('fecha_isp', '<>', null)->where('empresa', Auth::user()->empresa)->where('status', 1)->OrderBy('nombre')->where('contactos.oficina', auth()->user()->oficina)->get();
        } else {
            $contratos = Contacto::where('fecha_isp', '<>', null)->where('empresa', Auth::user()->empresa)->where('status', 1)->OrderBy('nombre')->get();
        }
        return view('asignaciones.index')->with(compact('contratos'));
    }

    public function create()
    {
        $this->getAllPermissions(Auth::user()->id);
        $planes = PlanesVelocidad::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $clientes = Contacto::where('fecha_isp', null)->where('empresa', Auth::user()->empresa)->OrderBy('nombre')->get();
        $clientes = (Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $empresa = Empresa::find(Auth::user()->empresa);
        $contrato = Contrato::where('id', request()->contrato)->where('empresa', Auth::user()->empresa)->first();
        $idCliente = $contrato->client_id ?? '';
        view()->share(['title' => 'Asignación de Contrato de Internet']);
        return view('asignaciones.create')->with(compact('clientes', 'empresa', 'contrato', 'idCliente','planes'));
    }

    public function store(Request $request)
    {

        // $num = count(Contrato::where('client_id',$request->contrato)->get());
        $num = Contrato::where('client_id',$request->id)->first();
        $cliente = Contacto::where('id', $request->id)->where('empresa', Auth::user()->empresa)->first();
        $servicio = $cliente->nombre.' '.$cliente->apellido1.' '.$cliente->apellido2;
        // if($num == 2){
            $idContrato = null;
            if(!empty($num)) {

                // $idContrato = $request->contrato;
                $idContrato = $num->id;
            }else{

                $contrato_nuevo = new Contrato();
                $ultimoRegistro = Contrato::latest()->first();

                $contrato_nuevo->client_id = $request->id;
                $contrato_nuevo->nro = $ultimoRegistro->nro + 1;
                $contrato_nuevo->contrato_permanencia_meses = $request->contrato_permanencia_meses + 1;
                $contrato_nuevo->plan_id = $request->plan;
                $contrato_nuevo->server_configuration_id = 1;
                $contrato_nuevo->servicio = $this->normaliza($servicio).'-'.($ultimoRegistro->nro + 1);
                $contrato_nuevo->save();
            }

            $ext_permitidas = array('jpeg','png','gif');
            if (!$request->id) {
                $mensaje='Debe seleccionar un cliente para la asignación del contrato digital';
                return back()->with('danger', $mensaje);
            }
            if (!$request->id || !$request->file('documento')) {
                $mensaje='Debe adjuntar la documentación para la asignación del contrato digital';
                return back()->with('danger', $mensaje);
            }
            $contrato = Contacto::where('id', $request->id)->where('empresa', Auth::user()->empresa)->first();

            if ($contrato) {
                if($request->firma_isp) {
                    $contrato->firma_isp = $request->firma_isp;
                }

                // $contrato->fecha_isp = date('Y-m-d');
                // $file = $request->file('documento');
                // $nombre =  $idContrato.'doc_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                //   $ruta = public_path('/adjuntos/documentos/');
                    // $file->move($ruta, $nombre);
                // $contrato->documento = $nombre;

                try {

                    $contrato->fecha_isp = date('Y-m-d');
                    $file = $request->file('documento');
                    $nombre =  $idContrato . 'doc_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->documento = $nombre;

                } catch (\Exception $e) {

                    // Manejar el error, por ejemplo, registrar un mensaje de error o mostrarlo al usuario.
                    \Log::error($e->getMessage());
                }


                $xmax = 1080;
                $ymax = 720;

                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }

                if($request->file('imgA')) {
                    $file = $request->file('imgA');
                    $nombre =  $idContrato.'imgA_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->imgA = $nombre;

                    if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'png':
                                $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'gif':
                                $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                        }

                        $x = imagesx($imagen);
                        $y = imagesy($imagen);

                        if($x <= $xmax && $y <= $ymax) {
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'png':
                                    imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'gif':
                                    imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                            }
                        } else {
                            if($x >= $y) {
                                $nuevax = $xmax;
                                $nuevay = $nuevax * $y / $x;
                            } else {
                                $nuevay = $ymax;
                                $nuevax = $x / $y * $nuevay;
                            }
                            $img2 = imagecreatetruecolor($nuevax, $nuevay);
                            imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                                case 'png':
                                    imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                    break;
                                case 'gif':
                                    imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                            }
                        }
                    }
                }

                if($request->file('imgB')) {
                    $file = $request->file('imgB');
                    $nombre =  $idContrato.'imgB_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->imgB = $nombre;

                    if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'png':
                                $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'gif':
                                $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                        }

                        $x = imagesx($imagen);
                        $y = imagesy($imagen);

                        if($x <= $xmax && $y <= $ymax) {
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'png':
                                    imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'gif':
                                    imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                            }
                        } else {
                            if($x >= $y) {
                                $nuevax = $xmax;
                                $nuevay = $nuevax * $y / $x;
                            } else {
                                $nuevay = $ymax;
                                $nuevax = $x / $y * $nuevay;
                            }
                            $img2 = imagecreatetruecolor($nuevax, $nuevay);
                            imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                                case 'png':
                                    imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                    break;
                                case 'gif':
                                    imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                            }
                        }
                    }
                }

                if($request->file('imgC')) {
                    $file = $request->file('imgC');
                    $nombre =  $idContrato.'imgC_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->imgC = $nombre;

                    if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'png':
                                $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'gif':
                                $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                        }

                        $x = imagesx($imagen);
                        $y = imagesy($imagen);

                        if($x <= $xmax && $y <= $ymax) {
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'png':
                                    imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'gif':
                                    imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                            }
                        } else {
                            if($x >= $y) {
                                $nuevax = $xmax;
                                $nuevay = $nuevax * $y / $x;
                            } else {
                                $nuevay = $ymax;
                                $nuevax = $x / $y * $nuevay;
                            }
                            $img2 = imagecreatetruecolor($nuevax, $nuevay);
                            imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                                case 'png':
                                    imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                    break;
                                case 'gif':
                                    imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                            }
                        }
                    }
                }

                if($request->file('imgD')) {
                    $file = $request->file('imgD');
                    $nombre =  $idContrato.'imgD_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->imgD = $nombre;
                    if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'png':
                                $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'gif':
                                $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                        }

                        $x = imagesx($imagen);
                        $y = imagesy($imagen);

                        if($x <= $xmax && $y <= $ymax) {
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'png':
                                    imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'gif':
                                    imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                            }
                        } else {
                            if($x >= $y) {
                                $nuevax = $xmax;
                                $nuevay = $nuevax * $y / $x;
                            } else {
                                $nuevay = $ymax;
                                $nuevax = $x / $y * $nuevay;
                            }
                            $img2 = imagecreatetruecolor($nuevax, $nuevay);
                            imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                                case 'png':
                                    imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                    break;
                                case 'gif':
                                    imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                            }
                        }
                    }
                }

                if($request->file('imgE')) {
                    $file = $request->file('imgE');
                    $nombre =  $idContrato.'imgE_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->imgE = $nombre;

                    if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'png':
                                $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'gif':
                                $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                        }

                        $x = imagesx($imagen);
                        $y = imagesy($imagen);

                        if($x <= $xmax && $y <= $ymax) {
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'png':
                                    imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'gif':
                                    imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                            }
                        } else {
                            if($x >= $y) {
                                $nuevax = $xmax;
                                $nuevay = $nuevax * $y / $x;
                            } else {
                                $nuevay = $ymax;
                                $nuevax = $x / $y * $nuevay;
                            }
                            $img2 = imagecreatetruecolor($nuevax, $nuevay);
                            imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                                case 'png':
                                    imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                    break;
                                case 'gif':
                                    imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                            }
                        }
                    }
                }

                if($request->file('imgF')) {
                    $file = $request->file('imgF');
                    $nombre =  $idContrato.'imgF_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->imgF = $nombre;
                    if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'png':
                                $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'gif':
                                $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                        }

                        $x = imagesx($imagen);
                        $y = imagesy($imagen);

                        if($x <= $xmax && $y <= $ymax) {
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'png':
                                    imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'gif':
                                    imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                            }
                        } else {
                            if($x >= $y) {
                                $nuevax = $xmax;
                                $nuevay = $nuevax * $y / $x;
                            } else {
                                $nuevay = $ymax;
                                $nuevax = $x / $y * $nuevay;
                            }
                            $img2 = imagecreatetruecolor($nuevax, $nuevay);
                            imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                                case 'png':
                                    imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                    break;
                                case 'gif':
                                    imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                            }
                        }
                    }
                }

                if($request->file('imgG')) {
                    $file = $request->file('imgG');
                    $nombre =  $idContrato.'imgG_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->imgG = $nombre;
                    if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'png':
                                $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'gif':
                                $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                        }

                        $x = imagesx($imagen);
                        $y = imagesy($imagen);

                        if($x <= $xmax && $y <= $ymax) {
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'png':
                                    imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'gif':
                                    imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                            }
                        } else {
                            if($x >= $y) {
                                $nuevax = $xmax;
                                $nuevay = $nuevax * $y / $x;
                            } else {
                                $nuevay = $ymax;
                                $nuevax = $x / $y * $nuevay;
                            }
                            $img2 = imagecreatetruecolor($nuevax, $nuevay);
                            imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                                case 'png':
                                    imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                    break;
                                case 'gif':
                                    imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                            }
                        }
                    }
                }

                if($request->file('adjunto_audio')){
                    $file = $request->file('adjunto_audio');
                    $nombre =  $idContrato.'adjunto_audio'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->adjunto_audio = $nombre;

                }

                if($request->file('imgH')) {

                    $file = $request->file('imgH');
                    $nombre =  $idContrato.'imgH_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                    $ruta = public_path('/adjuntos/documentos/');
                    $file->move($ruta, $nombre);
                    $contrato->imgH = $nombre;

                    if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'png':
                                $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                            case 'gif':
                                $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                                break;
                        }

                        $x = imagesx($imagen);
                        $y = imagesy($imagen);

                        if($x <= $xmax && $y <= $ymax) {
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'png':
                                    imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                                case 'gif':
                                    imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                    break;
                            }
                        } else {
                            if($x >= $y) {
                                $nuevax = $xmax;
                                $nuevay = $nuevax * $y / $x;
                            } else {
                                $nuevay = $ymax;
                                $nuevax = $x / $y * $nuevay;
                            }
                            $img2 = imagecreatetruecolor($nuevax, $nuevay);
                            imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                            switch($file->getClientOriginalExtension()) {
                                case 'jpeg':
                                    imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                                case 'png':
                                    imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                    break;
                                case 'gif':
                                    imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                    break;
                            }
                        }
                    }

                 }
                //  else{

                // }

                $contrato->save();
                return redirect('empresa/asignaciones')->with('success', 'SE HA REGISTRADO SATISFACTORIAMENTE LA ASIGNACIÓN DEL CONTRATO DIGITAL.');
            }

        return redirect('empresa/asignaciones')->with('danger', 'No existe un registro con ese id');
    }

    public function edit($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $planes = PlanesVelocidad::where('status', 1)->where('empresa', Auth::user()->empresa)->get();
        $contacto = Contacto::find($id);
        $contrato = Contrato::where('client_id',$id)->first();
        $empresa = Empresa::find(Auth::user()->empresa);
        view()->share(['title' => 'Editar Asignación de Contrato de Internet']);
        return view('asignaciones.edit')->with(compact('contacto', 'empresa','planes','contrato'));
    }

    public function update(Request $request, $id)
    {
        $ext_permitidas = array('jpeg','png','gif');
        $xmax = 1080;
        $ymax = 720;
        $contrato = Contacto::find($id);

        if ($contrato) {
            if($request->firma_isp) {
                $contrato->firma_isp = $request->firma_isp;
                $contrato->fecha_isp = date('Y-m-d');
            }

            if($request->file('documento')) {
                $file = $request->file('documento');
                $nombre =  'doc_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                  $ruta = public_path('/adjuntos/documentos/');
                $file->move($ruta, $nombre);
                $contrato->documento = $nombre;

                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }
            }

            if($request->file('imgA')) {
                $file = $request->file('imgA');
                $nombre =  'imgA_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                  $ruta = public_path('/adjuntos/documentos/');
                $file->move($ruta, $nombre);
                $contrato->imgA = $nombre;

                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }
            }

            if($request->file('imgB')) {
                $file = $request->file('imgB');
                $nombre =  'imgB_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                  $ruta = public_path('/adjuntos/documentos/');
                $file->move($ruta, $nombre);
                $contrato->imgB = $nombre;

                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }
            }

            if($request->file('imgC')) {
                $file = $request->file('imgC');
                $nombre =  'imgC_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                  $ruta = public_path('/adjuntos/documentos/');
                $file->move($ruta, $nombre);
                $contrato->imgC = $nombre;

                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }
            }

            if($request->file('imgD')) {
                $file = $request->file('imgD');
                $nombre =  'imgD_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                  $ruta = public_path('/adjuntos/documentos/');
                $file->move($ruta, $nombre);
                $contrato->imgD = $nombre;
                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }
            }

            if($request->file('imgE')) {
                $file = $request->file('imgE');
                $nombre =  'imgE_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                  $ruta = public_path('/adjuntos/documentos/');
                $file->move($ruta, $nombre);
                $contrato->imgE = $nombre;

                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }
            }

            if($request->file('imgF')) {
                $file = $request->file('imgF');
                $nombre =  'imgF_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                  $ruta = public_path('/adjuntos/documentos/');
                $file->move($ruta, $nombre);
                $contrato->imgF = $nombre;
                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }
            }

            if($request->file('imgG')) {
                $file = $request->file('imgG');
                $nombre =  'imgG_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                  $ruta = public_path('/adjuntos/documentos/');
                $file->move($ruta, $nombre);
                $contrato->imgG = $nombre;
                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }
            }

            if($request->file('imgH')) {
                $file = $request->file('imgH');
                $nombre =  'imgH_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                  $ruta = public_path('/adjuntos/documentos/');
                $file->move($ruta, $nombre);
                $contrato->imgH = $nombre;

                if(in_array($file->getClientOriginalExtension(), $ext_permitidas)) {
                    switch($file->getClientOriginalExtension()) {
                        case 'jpeg':
                            $imagen = imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'png':
                            $imagen = imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                        case 'gif':
                            $imagen = imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax) {
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'png':
                                imagepng(imagecreatefrompng(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                            case 'gif':
                                imagegif(imagecreatefromgif(public_path('/adjuntos/documentos').'/'.$nombre), public_path('/adjuntos/documentos').'/'.$nombre, 5);
                                break;
                        }
                    } else {
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getClientOriginalExtension()) {
                            case 'jpeg':
                                imagejpeg($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                            case 'png':
                                imagepng($img2, public_path('/adjuntos/documentos').'/'.$nombre, 9);
                                break;
                            case 'gif':
                                imagegif($img2, public_path('/adjuntos/documentos').'/'.$nombre, 90);
                                break;
                        }
                    }
                }
            }

            $contrato->save();
            return redirect('empresa/asignaciones')->with('success', 'SE HA ACTUALIZADO SATISFACTORIAMENTE LA DOCUMENTACIÓN DEL CONTRATO DIGITAL.');
        }
        return redirect('empresa/asignaciones')->with('success', 'No existe un registro con ese id');
    }

    public function destroy($id)
    {
        $contrato = Contacto::find($id);
        if ($contrato) {
            $contrato->firma_isp = null;
            $contrato->fecha_isp = null;
            $contrato->documento = null;
            $contrato->imgA = null;
            $contrato->imgB = null;
            $contrato->imgC = null;
            $contrato->imgD = null;
            $contrato->imgE = null;
            $contrato->imgF = null;
            $contrato->imgG = null;
            $contrato->imgH = null;
            $contrato->save();

            return redirect('empresa/asignaciones')->with('success', 'SE HA ELIMINADO SATISFACTORIAMENTE LA ASIGNACIÓN DEL CONTRATO DIGITAL.');
        }
        return redirect('empresa/asignaciones')->with('success', 'No existe un registro con ese id');
    }

    public function imprimir($id)
    {
        // TODO: we should really test this method, as the generation of the PDF
        // can sometimes go wrong. Or make a better error. The following can be
        // upgraded to use something like Go or Rust, but I feel this is fine
        // for now.
        /** @var User $company */
        $company = ((object) FacadesAuth::user())->empresa();

        try {
            /** @var Contacto $contact */
            $contact = Contacto::where('id', $id)
                ->where('empresa', $company->id)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return back()->with('danger', 'Revisa el contacto, no se encuentran los contratos relacionados');
        }

        try {
            $contract = $contact->contrato();
            // TODO: This should be within the contract method, but right now it
            // will break other things, so it will stay here.
            if (is_null($contract)) {
                throw new ModelNotFoundException();
            }
        } catch (ModelNotFoundException $e) {
            return back()->with('danger', 'El contacto no tiene un contrato asociado.');
        }

        try {
            $contractDetails = $contact->details($contract->id);
        } catch (ModelNotFoundException $e) {
            return back()->with('danger', 'Los detalles del contrato no fueron encontrados.');
        }

        // what is this for?
        $idContrato = request()->idContrato;

        view()->share(['title' => 'Contrato de Internet']);
        $pdf = Pdf::loadView('pdf.contrato', compact([
            'contact',
            'company',
            'contract',
            'contractDetails',
        ]));
        return response($pdf->stream())->withHeaders(['Content-Type' => 'application/pdf',]);
    }

    public function show_campos_asignacion()
    {
        $empresa = Empresa::find(Auth::user()->empresa);
        return json_encode($empresa);
    }

    public function campos_asignacion(Request $request)
    {
        $empresa = Empresa::find(Auth::user()->empresa);
        if($empresa) {
            $empresa->campo_a = $request->campo_a;
            $empresa->campo_b = $request->campo_b;
            $empresa->campo_c = $request->campo_c;
            $empresa->campo_d = $request->campo_d;
            $empresa->campo_e = $request->campo_e;
            $empresa->campo_f = $request->campo_f;
            $empresa->campo_g = $request->campo_g;
            $empresa->campo_h = $request->campo_h;
            $empresa->campo_1 = $request->campo_1;
            $empresa->contrato_digital = $request->contrato_digital;
            $empresa->anexo_1 = $request->anexo_1;
            $empresa->anexo_2 = $request->anexo_2;
            $empresa->anexo_3 = $request->anexo_3;
            $empresa->anexo_4 = $request->anexo_4;
            $empresa->save();
            return response()->json([
                'success'          => true,
                'campo_a'          => $empresa->campo_a,
                'campo_b'          => $empresa->campo_b,
                'campo_c'          => $empresa->campo_c,
                'campo_d'          => $empresa->campo_d,
                'campo_e'          => $empresa->campo_e,
                'campo_f'          => $empresa->campo_f,
                'campo_g'          => $empresa->campo_g,
                'campo_h'          => $empresa->campo_h,
                'campo_1'          => $empresa->campo_1,
                'contrato_digital' => $empresa->contrato_digital,
                'anexo_1'          => $empresa->anexo_1,
                'anexo_2'          => $empresa->anexo_2,
                'anexo_3'          => $empresa->anexo_3,
                'anexo_4'          => $empresa->anexo_4
            ]);
        }
        return response()->json(['success' => false]);
    }

    public function enviar($id)
    {
        view()->share(['title' => 'Contrato de Internet']);
        $contact = Contacto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if($contact) {
            if (!$contact->email) {
                return back()->with('danger', 'EL CLIENTE NO TIENE UN CORREO ELECTRÓNICO REGISTRADO');
            }
            $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
            if($host) {
                $existing = config('mail');
                $new =array_merge(
                    $existing,
                    [
                        'host' => $host->servidor,
                        'port' => $host->puerto,
                        'encryption' => $host->seguridad,
                        'username' => $host->usuario,
                        'password' => $host->password,
                        'from' => [
                            'address' => $host->address,
                            'name' => $host->name
                        ],
                    ]
                );
                config(['mail'=>$new]);
            }
            $idContrato = request()->idContrato;

            $company = ((object) FacadesAuth::user())->empresa();

            try {
                $contract = $contact->contrato();
                // TODO: This should be within the contract method, but right now it
                // will break other things, so it will stay here.
                if (is_null($contract)) {
                    throw new ModelNotFoundException();
                }
            } catch (ModelNotFoundException $e) {
                return back()->with('danger', 'El contacto no tiene un contrato asociado.');
            }

            try {
                $contractDetails = $contact->details($contract->id);
            } catch (ModelNotFoundException $e) {
                return back()->with('danger', 'Los detalles del contrato no fueron encontrados.');
            }

            $pdf = Pdf::loadView('pdf.contrato', compact([
                'contact',
                'company',
                'contract',
                'contractDetails',
            ]))->stream();

            $email = $contact->email;
            $cliente = $contact->nombre;
            self::sendMail('emails.contrato', compact('contact'), compact('pdf', 'contact', 'email', 'cliente'), function ($message) use ($pdf, $contact) {
                $message->attachData($pdf, 'contrato_digital_servicios.pdf', ['mime' => 'application/pdf']);
                $message->to($contact->email)->subject("Contrato Digital de Servicios - ".Auth::user()->empresa()->nombre);
            });
            return back()->with('success', strtoupper('EL CONTRATO DIGITAL DE SERVICIOS HA SIDO ENVIADO CORRECTAMENTE A '.$contact->nombre.' '.$contact->apellidos()));
        }
        return back()->with('danger', 'CONTRATO DIGITAL NO ENVIADO');
    }

    public function generar_link($id)
    {
        $contacto = Contacto::find($id);
        $empresa = Empresa::find($id);
        if($contacto) {
            $sw = 1;
            while ($sw == 1) {
                $ref = Funcion::generateRandomString();
                if (Contacto::where('referencia_asignacion', $ref)->first()) {
                    $ref = Funcion::generateRandomString();
                } else {
                    $sw = 0;
                    $contacto->referencia_asignacion = $ref;
                    $contacto->save();
                }
            }

            $link = config('app.url')."/api/contrato-digital/".$ref;

            return response()->json([
                'success'  => true,
                'contacto' => $contacto->id,
                'empresa' => $empresa,
                'text'     => "<a href='".config('app.url')."/api/contrato-digital/".$ref."' target='_blank'>".config('app.url')."/api/contrato-digital/".$ref."</a><br><br><button class='btn btn-primary btn-lg' data-clipboard-text='".$link."'>COPIAR URL</button>
                ",
                'link'     => config('app.url')."/api/contrato-digital/".$ref,
                'type'     => 'success'
            ]);
        }
        return response()->json(['success' => false,'text' => 'Algo falló, intente nuevamente', 'type' => 'error']);
    }
}
