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
use App\ServidorCorreo;
use Mail;

class AsignacionesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'contratos', 'subseccion' => 'asignaciones', 'title' => 'Asignaciones', 'icon' =>'fas fa-file-contract']);
    }

    public function index(){
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['invert' => true]);
        if(auth()->user()->empresa()->oficina){
            $contratos = Contacto::where('fecha_isp','<>',null)->where('empresa', Auth::user()->empresa)->where('status', 1)->OrderBy('nombre')->where('contactos.oficina', auth()->user()->oficina)->get();
        }else{
            $contratos = Contacto::where('fecha_isp','<>',null)->where('empresa', Auth::user()->empresa)->where('status', 1)->OrderBy('nombre')->get();
        }
        return view('asignaciones.index')->with(compact('contratos'));
    }

    public function create(){
        $this->getAllPermissions(Auth::user()->id);
        $clientes = Contacto::where('fecha_isp',null)->where('empresa', Auth::user()->empresa)->OrderBy('nombre')->get();
        $clientes = (Auth::user()->empresa()->oficina) ? Contacto::where('fecha_isp',null)->whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::where('fecha_isp',null)->whereIn('tipo_contacto', [0,2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $empresa = Empresa::find(Auth::user()->empresa);
        view()->share(['title' => 'Asignaci??n de Contrato de Internet']);
        return view('asignaciones.create')->with(compact('clientes', 'empresa'));
    }

    public function store(Request $request){
        $ext_permitidas = array('image/jpeg','image/png','image/gif');
        if (!$request->id){
            $mensaje='Debe seleccionar un cliente para la asignaci??n del contrato digital';
            return back()->with('danger', $mensaje);
        }
        if (!$request->id || !$request->file('documento')){
            $mensaje='Debe adjuntar la documentaci??n para la asignaci??n del contrato digital';
            return back()->with('danger', $mensaje);
        }
        $contrato = Contacto::where('id',$request->id)->where('empresa', Auth::user()->empresa)->first();
        if ($contrato) {
            if($request->firma_isp){
                $contrato->firma_isp = $request->firma_isp;
            }

            $contrato->fecha_isp = date('Y-m-d');
            $file = $request->file('documento');
            $nombre =  'doc_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
            Storage::disk('documentos')->put($nombre, \File::get($file));
            $contrato->documento = $nombre;

            $xmax = 1080;
            $ymax = 720;

            if(in_array($file->getMimeType(), $ext_permitidas)){
                switch($file->getMimeType()){
                    case 'image/jpeg':
                    $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                    break;
                    case 'image/png':
                    $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                    break;
                    case 'image/gif':
                    $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                    break;
                }

                $x = imagesx($imagen);
                $y = imagesy($imagen);

                if($x <= $xmax && $y <= $ymax){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                        break;
                        case 'image/png':
                        imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                        break;
                        case 'image/gif':
                        imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                        break;
                    }
                }else{
                    if($x >= $y) {
                        $nuevax = $xmax;
                        $nuevay = $nuevax * $y / $x;
                    }else{
                        $nuevay = $ymax;
                        $nuevax = $x / $y * $nuevay;
                    }
                    $img2 = imagecreatetruecolor($nuevax, $nuevay);
                    imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                        break;
                        case 'image/png':
                        imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                        break;
                        case 'image/gif':
                        imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                        break;
                    }
                }
            }

            if($request->file('imgA')){
                $file = $request->file('imgA');
                $nombre =  'imgA_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgA = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgB')){
                $file = $request->file('imgB');
                $nombre =  'imgB_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgB = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgC')){
                $file = $request->file('imgC');
                $nombre =  'imgC_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgC = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgD')){
                $file = $request->file('imgD');
                $nombre =  'imgD_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgD = $nombre;
                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgE')){
                $file = $request->file('imgE');
                $nombre =  'imgE_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgE = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgF')){
                $file = $request->file('imgF');
                $nombre =  'imgF_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgF = $nombre;
                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgG')){
                $file = $request->file('imgG');
                $nombre =  'imgG_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgG = $nombre;
                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgH')){
                $file = $request->file('imgH');
                $nombre =  'imgH_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgH = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            $contrato->save();
            return redirect('empresa/asignaciones')->with('success', 'SE HA REGISTRADO SATISFACTORIAMENTE LA ASIGNACI??N DEL CONTRATO DIGITAL.');
        }
        return redirect('empresa/asignaciones')->with('success', 'No existe un registro con ese id');
    }

    public function edit($id){
        $this->getAllPermissions(Auth::user()->id);
        $contacto = Contacto::find($id);
        $empresa = Empresa::find(Auth::user()->empresa);
        view()->share(['title' => 'Editar Asignaci??n de Contrato de Internet']);
        return view('asignaciones.edit')->with(compact('contacto', 'empresa'));
    }

    public function update(Request $request, $id){
        $ext_permitidas = array('image/jpeg','image/png','image/gif'); $xmax = 1080; $ymax = 720;
        $contrato = Contacto::find($id);
        if ($contrato) {
            if($request->firma_isp){
                $contrato->firma_isp = $request->firma_isp;
                $contrato->fecha_isp = date('Y-m-d');
            }

            if($request->file('documento')){
                $file = $request->file('documento');
                $nombre =  'doc_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->documento = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgA')){
                $file = $request->file('imgA');
                $nombre =  'imgA_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgA = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgB')){
                $file = $request->file('imgB');
                $nombre =  'imgB_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgB = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgC')){
                $file = $request->file('imgC');
                $nombre =  'imgC_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgC = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgD')){
                $file = $request->file('imgD');
                $nombre =  'imgD_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgD = $nombre;
                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgE')){
                $file = $request->file('imgE');
                $nombre =  'imgE_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgE = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgF')){
                $file = $request->file('imgF');
                $nombre =  'imgF_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgF = $nombre;
                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgG')){
                $file = $request->file('imgG');
                $nombre =  'imgG_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgG = $nombre;
                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            if($request->file('imgH')){
                $file = $request->file('imgH');
                $nombre =  'imgH_'.$contrato->nit.'.'.$file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgH = $nombre;

                if(in_array($file->getMimeType(), $ext_permitidas)){
                    switch($file->getMimeType()){
                        case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                        case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre);
                        break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if($x <= $xmax && $y <= $ymax){
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                            case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos').'/'.$nombre), public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 5);
                            break;
                        }
                    }else{
                        if($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        }else{
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch($file->getMimeType()){
                            case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                            case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos').'/'.$nombre, 100);
                            break;
                        }
                    }
                }
            }

            $contrato->save();
            return redirect('empresa/asignaciones')->with('success', 'SE HA ACTUALIZADO SATISFACTORIAMENTE LA DOCUMENTACI??N DEL CONTRATO DIGITAL.');
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
            $empresa->campo_e = $request->campo_e;
            $empresa->campo_f = $request->campo_f;
            $empresa->campo_g = $request->campo_g;
            $empresa->campo_h = $request->campo_h;
            $empresa->campo_1 = $request->campo_1;
            $empresa->contrato_digital = $request->contrato_digital;
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
                'contrato_digital' => $empresa->contrato_digital
            ]);
        }
        return response()->json(['success' => false]);
    }

    public function enviar($id){
        view()->share(['title' => 'Contrato de Internet']);
        $contrato = Contacto::where('id',$id)->where('empresa', Auth::user()->empresa)->first();
        if($contrato) {
            if (!$contrato->email) {
                return back()->with('danger', 'EL CLIENTE NO TIENE UN CORREO ELECTR??NICO REGISTRADO');
            }
            $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
            if($host){
                $existing = config('mail');
                $new =array_merge(
                    $existing, [
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

            $pdf = PDF::loadView('pdf.contrato', compact('contrato'))->stream();
            Mail::send('emails.contrato', compact('contrato'), function($message) use ($pdf, $contrato){
                $message->attachData($pdf, 'contrato_digital_servicios.pdf', ['mime' => 'application/pdf']);
                $message->to($contrato->email)->subject("Contrato Digital de Servicios - ".Auth::user()->empresa()->nombre);
            });
            return back()->with('success', strtoupper('EL CONTRATO DIGITAL DE SERVICIOS HA SIDO ENVIADO CORRECTAMENTE A '.$contrato->nombre.' '.$contrato->apellidos()));
        }
        return back()->with('danger', 'CONTRATO DIGITAL NO ENVIADO');
    }

    public function generar_link($id){
        $contacto = Contacto::find($id);
        if($contacto){
            $sw = 1;
            while ($sw == 1) {
                $ref = Funcion::generateRandomString();
                if (Contacto::where('referencia_asignacion', $ref)->first()) {
                    $ref = Funcion::generateRandomString();
                }else{
                    $sw = 0;
                    $contacto->referencia_asignacion = $ref;
                    $contacto->save();
                }
            }

            $link = config('app.url')."/api/contrato-digital/".$ref;

            return response()->json([
                'success'  => true,
                'contacto' => $contacto->id,
                'text'     => "<a href='".config('app.url')."/api/contrato-digital/".$ref."' target='_blank'>".config('app.url')."/api/contrato-digital/".$ref."</a><br><br><button class='btn btn-primary btn-lg' data-clipboard-text='".$link."'>COPIAR URL</button>
                ",
                'link'     => config('app.url')."/api/contrato-digital/".$ref,
                'type'     => 'success'
            ]);
        }
        return response()->json(['success' => false,'text' => 'Algo fall??, intente nuevamente', 'type' => 'error']);
    }
}
