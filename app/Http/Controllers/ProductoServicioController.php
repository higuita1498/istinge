<?php

namespace App\Http\Controllers;

use App\ProductoServicio;
use App\Puc;
use Auth;
use Illuminate\Http\Request;

use App\Model\Inventario\Inventario; 
use App\Impuesto; 
use App\ProductoCuenta; 

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

        $empresa = Auth::user()->empresa;
        $impuesto = Impuesto::where('porcentaje', 0)->first();

        //Primero creamos el producto en el inventario.
        $inventario = new Inventario;
        $inventario->producto = $request->nombre;
        $inventario->empresa = $empresa;
        $inventario->impuesto = isset($impuesto->porcentaje) ? $impuesto->porcentaje : 0;
        $inventario->id_impuesto = isset($impuesto->id) ? $impuesto->id : 2;
        $inventario->type='MATERIAL';
        $inventario->unidad=1;
        $inventario->nro=0;
        $inventario->tipo_producto=2;
        $inventario->save();

        //Registramos las cuentas en la tabla de producto_cuentas ya que hace referencia a las mismas
        if($request->inventario){
            $pr = new ProductoCuenta;
            $pr->cuenta_id = $request->inventario;
            $pr->inventario_id = $inventario->id;
            $pr->tipo = 1;
            $pr->save();
        }

        if($request->costo){
            $pr = new ProductoCuenta;
            $pr->cuenta_id = $request->costo;
            $pr->inventario_id = $inventario->id;
            $pr->tipo = 2;
            $pr->save();
        }

        if($request->venta){
            $pr = new ProductoCuenta;
            $pr->cuenta_id = $request->venta;
            $pr->inventario_id = $inventario->id;
            $pr->tipo = 3;
            $pr->save();
        }

        if($request->devolucion){
            $pr = new ProductoCuenta;
            $pr->cuenta_id = $request->devolucion;
            $pr->inventario_id = $inventario->id;
            $pr->tipo = 4;
            $pr->save();
        } 


        $producto = new ProductoServicio;
        $producto->en_uso = $request->checkForm;
        $producto->codigo = $request->codigo;
        $producto->nombre = $request->nombre;
        $producto->inventario_id = $request->inventario;
        $producto->costo_id = $request->costo;
        $producto->venta_id = $request->venta;
        $producto->devolucion_id = $request->devolucion;
        $producto->producto_id = $inventario->id;
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
        $producto = ProductoServicio::find($request->id);
        $empresa = Auth::user()->empresa;
        if($producto){

            //Primero creamos el producto en el inventario.
            $inventario = Inventario::where('id',$producto->producto_id)->first();
            if($inventario){
                $inventario->producto = $request->nombre;
                $inventario->save();

                //Actualizamos las cuentas en la tabla de producto_cuentas ya que hace referencia a las mismas
                if($request->inventario){
                    $pr = ProductoCuenta::where('inventario_id',$inventario->id)->where('tipo',1)->first();
                    if(!$pr){
                        $pr = new ProductoCuenta;
                    }
                    $pr->cuenta_id = $request->inventario;
                    $pr->inventario_id = $inventario->id;
                    $pr->tipo = 1;
                    $pr->save();
                }

                if($request->costo){
                    $pr = ProductoCuenta::where('inventario_id',$inventario->id)->where('tipo',2)->first();
                    if(!$pr){
                        $pr = new ProductoCuenta;
                    }
                    $pr->cuenta_id = $request->costo;
                    $pr->inventario_id = $inventario->id;
                    $pr->tipo = 2;
                    $pr->save();
                }

                if($request->venta){
                    $pr = ProductoCuenta::where('inventario_id',$inventario->id)->where('tipo',3)->first();
                    if(!$pr){
                        $pr = new ProductoCuenta;
                    }
                    $pr->cuenta_id = $request->venta;
                    $pr->inventario_id = $inventario->id;
                    $pr->tipo = 3;
                    $pr->save();
                }

                if($request->devolucion){
                    $pr = ProductoCuenta::where('inventario_id',$inventario->id)->where('tipo',4)->first();
                    if(!$pr){
                        $pr = new ProductoCuenta;
                    }
                    $pr->cuenta_id = $request->devolucion;
                    $pr->inventario_id = $inventario->id;
                    $pr->tipo = 4;
                    $pr->save();
                }

            }else{
                $inventario = new Inventario;
                $inventario->producto = $request->nombre;
                $inventario->empresa = $empresa;
                $inventario->impuesto = isset($impuesto->porcentaje) ? $impuesto->porcentaje : 0;
                $inventario->id_impuesto = $impuesto ? $impuesto : 2;
                $inventario->type='MATERIAL';
                $inventario->unidad=1;
                $inventario->nro=0;
                $inventario->tipo_producto=2;
                $inventario->save();  

                $producto->producto_id = $inventario->id;

                //Registramos las cuentas en la tabla de producto_cuentas ya que hace referencia a las mismas
                if($request->inventario){
                    $pr = new ProductoCuenta;
                    $pr->cuenta_id = $request->inventario;
                    $pr->inventario_id = $inventario->id;
                    $pr->tipo = 1;
                    $pr->save();
                }

                if($request->costo){
                    $pr = new ProductoCuenta;
                    $pr->cuenta_id = $request->costo;
                    $pr->inventario_id = $inventario->id;
                    $pr->tipo = 2;
                    $pr->save();
                }

                if($request->venta){
                    $pr = new ProductoCuenta;
                    $pr->cuenta_id = $request->venta;
                    $pr->inventario_id = $inventario->id;
                    $pr->tipo = 3;
                    $pr->save();
                }

                if($request->devolucion){
                    $pr = new ProductoCuenta;
                    $pr->cuenta_id = $request->devolucion;
                    $pr->inventario_id = $inventario->id;
                    $pr->tipo = 4;
                    $pr->save();
                } 
            }
         

            $producto->en_uso = $request->checkForm;
            $producto->codigo = $request->codigo;
            $producto->nombre = $request->nombre;
            $producto->inventario_id = $request->inventario;
            $producto->costo_id = $request->costo;
            $producto->venta_id = $request->venta; 
            $producto->devolucion_id = $request->devolucion; 
            $producto->save();
            return response()->json([
                'producto' => $producto
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
