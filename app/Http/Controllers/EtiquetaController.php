<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Etiqueta;
use Auth;

class EtiquetaController extends Controller
{
    //

    public function index(){
        $modoLectura = auth()->user()->modo_lectura();
        $this->getAllPermissions(Auth::user()->id);
        //$etiquetas = Etiqueta::where('empresa_id', auth()->user()->empresa)->get();
        $etiquetas = [];
        view()->share(['invert' => true, 'title' => 'Etiquetas', 'seccion' => 'etiquetas', 'subseccion' => 'etiquetas', 'icon' => '']);
        return view('etiquetas.index', compact('etiquetas'));
    }


    public function etiquetas(Request $request){
        $modoLectura = auth()->user()->modo_lectura();
        $etiquetas = Etiqueta::query()
            ->select('etiquetas.*')
            ->where('etiquetas.empresa', Auth::user()->empresa);

        if ($request->filtro == true) {
            if($request->nombre){
                $etiquetas->where(function ($query) use ($request) {
                    $query->orWhere('etiquetas.nombre', 'like', "%{$request->nombre}%");
                });
            }
            if($request->color){
                $etiquetas->where(function ($query) use ($request) {
                    $query->orWhere('etiquetas.color', 'like', "%{$request->color}%");
                });
            }
        }

        $etiquetas = $etiquetas->orderby('etiquetas.created_at', 'desc');

        return datatables()->eloquent($etiquetas)
        ->editColumn('created_at', function (Etiqueta $etiqueta) {
            return ($etiqueta->created_at) ? date('d-m-Y g:i:s A', strtotime($etiqueta->created_at)):'N/A';
        })
        ->addColumn('acciones', $modoLectura ?  "" : "etiquetas.acciones")
        ->rawColumns(['created_at', 'acciones'])
        ->toJson();
    }


}
