<?php

namespace App\Http\Controllers;

use App\ProductoServicio;
use App\Puc;
use Auth;
use Illuminate\Http\Request;

class ProductoServicioController extends Controller
{
    public function index(){
        view()->share(['seccion' => 'categorias', 'title' => 'Productos y Servicios', 'icon' =>'fas fa-list-ul']);
        $this->getAllPermissions(Auth::user()->id);

        $productos = ProductoServicio::all();

        //Tomar las categorias del puc que no son transaccionables.
        $categorias = Puc::where('empresa',auth()->user()->empresa)
        ->whereRaw('length(codigo) > 6')
        ->get();

        return view('productoservicio.index',compact('productos','categorias'));
    }

    public function store(Request $request){

        $producto = new ProductoServicio;
        $producto->en_uso = $request->checkForm;
        $producto->codigo = $request->codigo;
        $producto->nombre = $request->nombre;
        $producto->inventario_id = $request->inventario;
        $producto->costo_id = $request->costo;
        $producto->venta_id = $request->venta;
        $producto->devolucion_id = $request->devolucion;
        $producto->save();

        //Obtenemos el nombre de la categoria seleccionada.
        $producto->inventario = Puc::where('id',$request->inventario)->first()->nombre;
        $producto->costo = Puc::where('id',$request->costo)->first()->nombre;
        $producto->venta = Puc::where('id',$request->venta)->first()->nombre;
        $producto->devolucion = Puc::where('id',$request->devolucion)->first()->nombre;

        
        return response()->json($producto);
    }

    public function edit(Request $request){

        $id = $request->id;

        $producto = ProductoServicio::find($id);

        return response()->json([
            'producto' => $producto,
            'response' => true,
        ]);
    }

    public function update(Request $request){
        $prodcuto = ProductoServicio::find($request->id);

        if($prodcuto){
            $prodcuto->en_uso = $request->checkForm;
            $prodcuto->codigo = $request->codigo;
            $prodcuto->nombre = $request->nombre;
            $prodcuto->inventario_id = $request->inventario;
            $prodcuto->costo_id = $request->costo;
            $prodcuto->venta_id = $request->venta; 
            $prodcuto->devolucion_id = $request->devolucion; 
            $prodcuto->save();
            return response()->json([
                'producto' => $prodcuto
            ]);
        }else{
            return reposnse()->json(['producto' => false]);
        }
    }

    public function delete(Request $request){

        $producto = ProductoServicio::find($request->id);
        if($producto){
            $producto->delete();
        }
        
        return response()->json(['producto' => true]);
    }
}
