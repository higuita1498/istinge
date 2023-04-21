<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contacto;
use App\Contrato;
use App\Funcion;
use Illuminate\Support\Facades\Storage;
use App\Empresa;
use App\ServidorCorreo;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class AsignacionesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        set_time_limit(300);
        view()->share(['seccion' => 'contratos', 'subseccion' => 'asignaciones', 'title' => 'Asignaciones', 'icon' => 'fas fa-file-contract']);
    }

    public function index()
    {
        $this->getAllPermissions(Auth::user()->id);
        view()->share(['invert' => true]);
        if (auth()->user()->empresa()->oficina) {
            $contratos = Contacto::where('fecha_isp', '<>', null)->where('empresa', Auth::user()->empresa)->where('status', 1)->OrderBy('nombre')->where('contactos.oficina', auth()->user()->oficina)->get();
        } else {
            $contratos = Contacto::where('fecha_isp', '<>', null)->where('empresa', Auth::user()->empresa)->where('status', 1)->OrderBy('nombre')->get();
        }
        return view('asignaciones.index')->with(compact('contratos'));
    }

    public function create()
    {
        $this->getAllPermissions(Auth::user()->id);
        $clientes = Contacto::where('fecha_isp', null)->where('empresa', Auth::user()->empresa)->OrderBy('nombre')->get();
        $clientes = (Auth::user()->empresa()->oficina) ? Contacto::whereIn('tipo_contacto', [0, 2])->where('status', 1)->where('empresa', Auth::user()->empresa)->where('oficina', Auth::user()->oficina)->orderBy('nombre', 'ASC')->get() : Contacto::whereIn('tipo_contacto', [0, 2])->where('status', 1)->where('empresa', Auth::user()->empresa)->orderBy('nombre', 'ASC')->get();
        $empresa = Empresa::find(Auth::user()->empresa);
        $contrato = Contrato::where('id', request()->contrato)->where('empresa', Auth::user()->empresa)->first();
        $idCliente = $contrato->client_id ?? '';
        view()->share(['title' => 'Asignación de Contrato de Internet']);
        return view('asignaciones.create')->with(compact('clientes', 'empresa', 'contrato', 'idCliente'));
    }

    public function store(Request $request)
    {

        $idContrato = null;

        if ($request->contrato) {
            $idContrato = $request->contrato;
        }

        $ext_permitidas = array('image/jpeg', 'image/png', 'image/gif');
        if (!$request->id) {
            $mensaje = 'Debe seleccionar un cliente para la asignación del contrato digital';
            return back()->with('danger', $mensaje);
        }
        if (!$request->id || !$request->file('documento')) {
            $mensaje = 'Debe adjuntar la documentación para la asignación del contrato digital';
            return back()->with('danger', $mensaje);
        }
        $contrato = Contacto::where('id', $request->id)->where('empresa', Auth::user()->empresa)->first();
        if ($contrato) {
            if ($request->firma_isp) {
                $contrato->firma_isp = $request->firma_isp;
            }

            $contrato->fecha_isp = date('Y-m-d');
            $file = $request->file('documento');
            $nombre =  $idContrato . 'doc_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
            Storage::disk('documentos')->put($nombre, \File::get($file));
            $contrato->documento = $nombre;

            $xmax = 1080;
            $ymax = 720;

            if (in_array($file->getMimeType(), $ext_permitidas)) {
                switch ($file->getMimeType()) {
                    case 'image/jpeg':
                        $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                        break;
                    case 'image/png':
                        $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                        break;
                    case 'image/gif':
                        $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                        break;
                }

                $x = imagesx($imagen);
                $y = imagesy($imagen);

                if ($x <= $xmax && $y <= $ymax) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                            break;
                        case 'image/png':
                            imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                            break;
                        case 'image/gif':
                            imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                            break;
                    }
                } else {
                    if ($x >= $y) {
                        $nuevax = $xmax;
                        $nuevay = $nuevax * $y / $x;
                    } else {
                        $nuevay = $ymax;
                        $nuevax = $x / $y * $nuevay;
                    }
                    $img2 = imagecreatetruecolor($nuevax, $nuevay);
                    imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                            break;
                        case 'image/png':
                            imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                            break;
                        case 'image/gif':
                            imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                            break;
                    }
                }
            }

            if ($request->file('imgA')) {
                $file = $request->file('imgA');
                $nombre =  $idContrato . 'imgA_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgA = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgB')) {
                $file = $request->file('imgB');
                $nombre =  $idContrato . 'imgB_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgB = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgC')) {
                $file = $request->file('imgC');
                $nombre =  $idContrato . 'imgC_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgC = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgD')) {
                $file = $request->file('imgD');
                $nombre =  $idContrato . 'imgD_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgD = $nombre;
                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgE')) {
                $file = $request->file('imgE');
                $nombre =  $idContrato . 'imgE_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgE = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgF')) {
                $file = $request->file('imgF');
                $nombre =  $idContrato . 'imgF_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgF = $nombre;
                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgG')) {
                $file = $request->file('imgG');
                $nombre =  $idContrato . 'imgG_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgG = $nombre;
                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgH')) {
                $file = $request->file('imgH');
                $nombre =  $idContrato . 'imgH_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgH = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            $contrato->save();
            return redirect('empresa/asignaciones')->with('success', 'SE HA REGISTRADO SATISFACTORIAMENTE LA ASIGNACIÓN DEL CONTRATO DIGITAL.');
        }
        return redirect('empresa/asignaciones')->with('success', 'No existe un registro con ese id');
    }

    public function edit($id)
    {
        $this->getAllPermissions(Auth::user()->id);
        $contacto = Contacto::find($id);
        $empresa = Empresa::find(Auth::user()->empresa);
        view()->share(['title' => 'Editar Asignación de Contrato de Internet']);
        return view('asignaciones.edit')->with(compact('contacto', 'empresa'));
    }

    public function update(Request $request, $id)
    {
        $ext_permitidas = array('image/jpeg', 'image/png', 'image/gif');
        $xmax = 1080;
        $ymax = 720;
        $contrato = Contacto::find($id);
        if ($contrato) {
            if ($request->firma_isp) {
                $contrato->firma_isp = $request->firma_isp;
                $contrato->fecha_isp = date('Y-m-d');
            }

            if ($request->file('documento')) {
                $file = $request->file('documento');
                $nombre =  'doc_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->documento = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgA')) {
                $file = $request->file('imgA');
                $nombre =  'imgA_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgA = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgB')) {
                $file = $request->file('imgB');
                $nombre =  'imgB_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgB = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgC')) {
                $file = $request->file('imgC');
                $nombre =  'imgC_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgC = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgD')) {
                $file = $request->file('imgD');
                $nombre =  'imgD_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgD = $nombre;
                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgE')) {
                $file = $request->file('imgE');
                $nombre =  'imgE_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgE = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgF')) {
                $file = $request->file('imgF');
                $nombre =  'imgF_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgF = $nombre;
                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgG')) {
                $file = $request->file('imgG');
                $nombre =  'imgG_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgG = $nombre;
                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                        }
                    }
                }
            }

            if ($request->file('imgH')) {
                $file = $request->file('imgH');
                $nombre =  'imgH_' . $contrato->nit . '.' . $file->getClientOriginalExtension();
                Storage::disk('documentos')->put($nombre, \File::get($file));
                $contrato->imgH = $nombre;

                if (in_array($file->getMimeType(), $ext_permitidas)) {
                    switch ($file->getMimeType()) {
                        case 'image/jpeg':
                            $imagen = imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/png':
                            $imagen = imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                        case 'image/gif':
                            $imagen = imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre);
                            break;
                    }

                    $x = imagesx($imagen);
                    $y = imagesy($imagen);

                    if ($x <= $xmax && $y <= $ymax) {
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg(imagecreatefromjpeg(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/png':
                                imagepng(imagecreatefrompng(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                            case 'image/gif':
                                imagegif(imagecreatefromgif(public_path('../../public_html/adjuntos/documentos') . '/' . $nombre), public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 5);
                                break;
                        }
                    } else {
                        if ($x >= $y) {
                            $nuevax = $xmax;
                            $nuevay = $nuevax * $y / $x;
                        } else {
                            $nuevay = $ymax;
                            $nuevax = $x / $y * $nuevay;
                        }
                        $img2 = imagecreatetruecolor($nuevax, $nuevay);
                        imagecopyresized($img2, $imagen, 0, 0, 0, 0, floor($nuevax), floor($nuevay), $x, $y);
                        switch ($file->getMimeType()) {
                            case 'image/jpeg':
                                imagejpeg($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
                                break;
                            case 'image/png':
                                imagepng($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 9);
                                break;
                            case 'image/gif':
                                imagegif($img2, public_path('../../public_html/adjuntos/documentos') . '/' . $nombre, 90);
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
        try {
            /** @var User $company */
            $company = ((object) Auth::user())->empresa();
        } catch (ModelNotFoundException $e) {
            return back()->with('danger', 'El usuario no tiene una compañía asociada.');
        }

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
        if ($empresa) {
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
        $contrato = Contacto::where('id', $id)->where('empresa', Auth::user()->empresa)->first();
        if ($contrato) {
            if (!$contrato->email) {
                return back()->with('danger', 'EL CLIENTE NO TIENE UN CORREO ELECTRÓNICO REGISTRADO');
            }
            $host = ServidorCorreo::where('estado', 1)->where('empresa', Auth::user()->empresa)->first();
            if ($host) {
                $existing = config('mail');
                $new = array_merge(
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
                config(['mail' => $new]);
            }
            $idContrato = request()->idContrato;
            $pdf = PDF::loadView('pdf.contrato', compact('contrato', 'idContrato'))->stream();
            $email = $contrato->email;
            $cliente = $contrato->nombre;
            self::sendMail('emails.contrato', compact('contrato'), compact('pdf', 'contrato', 'email', 'cliente'), function ($message) use ($pdf, $contrato) {
                $message->attachData($pdf, 'contrato_digital_servicios.pdf', ['mime' => 'application/pdf']);
                $message->to($contrato->email)->subject("Contrato Digital de Servicios - " . Auth::user()->empresa()->nombre);
            });
            return back()->with('success', strtoupper('EL CONTRATO DIGITAL DE SERVICIOS HA SIDO ENVIADO CORRECTAMENTE A ' . $contrato->nombre . ' ' . $contrato->apellidos()));
        }
        return back()->with('danger', 'CONTRATO DIGITAL NO ENVIADO');
    }

    public function generar_link($id)
    {
        $contacto = Contacto::find($id);
        if ($contacto) {
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

            $link = config('app.url') . "/api/contrato-digital/" . $ref;

            return response()->json([
                'success'  => true,
                'contacto' => $contacto->id,
                'text'     => "<a href='" . config('app.url') . "/api/contrato-digital/" . $ref . "' target='_blank'>" . config('app.url') . "/api/contrato-digital/" . $ref . "</a><br><br><button class='btn btn-primary btn-lg' data-clipboard-text='" . $link . "'>COPIAR URL</button>
                ",
                'link'     => config('app.url') . "/api/contrato-digital/" . $ref,
                'type'     => 'success'
            ]);
        }
        return response()->json(['success' => false, 'text' => 'Algo falló, intente nuevamente', 'type' => 'error']);
    }
}
