<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Impuesto;
use App\Retencion;
use App\Contacto;
use App\FormaPago;
use App\Model\Ingresos\Ingreso;
use App\Puc;
use DB;

class PucMovimiento extends Model
{
    protected $table = "puc_movimiento";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'tipo_comprobante', 'consecutivo_comprobante', 'fecha_elaboracion', 'sigla_moneda', 'tasa_cambio', 'codigo_cuenta',
        'identificacion_tercero', 'sucursal', 'codigo_producto', 
        'codigo_bodega', 'accion', 'cantidad_producto', 'prefijo', 'consecutivo', 
        'no_cuota', 'fecha_vencimiento', 'codigo_impuesto', 
        'codigo_grupo', 'codigo_activo_fijo', 'descripcion', 'codigo_centro_costos', 'debito', 
       'credito', 'observaciones', 'base_gravable', 'base_exenta', 
       'mes_cierre', 'documento_id', 'cliente_id','cuenta_id','created_at', 'updated_at'
    ];

    public static function facturaVenta($factura, $opcion, $request){

        //opcion 1 es para guardar el movimientos, y miramos que no exista inngun movimiento sobre este documento
        $isGuardar = PucMovimiento::where('documento_id',$factura->id)->where('tipo_comprobante',3)->first();
        if($opcion == 1 && !$isGuardar){

            //ingresamos los valores del iva
            $totalFactura = $factura->total();
            // return response()->json($totalFactura->reten);    

            //1ro. registramos los movimientos contables de los items.
            foreach($factura->itemsFactura as $item){
                
                //iteramos sobre las cuentas contables a las que está asignado el producto.
                foreach($item->cuentasContable() as $cuentaItem){
                    
                    //si es tipo 3 (el tipo de producto o servicio que significa venta)
                    if($cuentaItem->tipo == 3 || $cuentaItem->tipo == 2 || $cuentaItem->tipo == 1){
                        $mov = new PucMovimiento;
                        $mov->tipo_comprobante = "03";
                        $mov->consecutivo_comprobante = $factura->codigo;
                        $mov->fecha_elaboracion = $factura->fecha;
                        $mov->documento_id = $factura->id;
                        $mov->codigo_cuenta = isset($cuentaItem->puc->codigo) ? $cuentaItem->puc->codigo : '';
                        $mov->cuenta_id = isset($cuentaItem->puc->id) ? $cuentaItem->puc->id : '';
                        $mov->identificacion_tercero = $factura->cliente()->nit;
                        $mov->cliente_id = $factura->cliente()->id;
                        $mov->prefijo = $factura->numeracionFactura->prefijo;
                        $mov->consecutivo = $factura->codigo;
                        $mov->fecha_vencimiento = $factura->vencimiento;
                        $mov->descripcion = $item->descripcion;
                        if($cuentaItem->tipo == 3){$mov->credito =  round($item->total());} //se hace sobre el total que se vendio del item
                        if($cuentaItem->tipo == 2){$mov->debito = $item->totalCompra();} // se hace sobre el total que se compro del item
                        if($cuentaItem->tipo == 1){$mov->credito = $item->totalCompra();}
                        $mov->enlace_a = 1;
                        $mov->save();
                    }
                    //buscamos si el item en inventario el tipo_producto es inventariable (tipo 1).
                    
            }
            }

            //2do. registramos el iva de la factura.
            foreach ($totalFactura->imp as $totalImp) {
                if (isset($totalImp->total)) {
                    $impuesto = Impuesto::find($totalImp->id);
                    if($impuesto){
                        $mov = new PucMovimiento;
                        $mov->tipo_comprobante = "03";
                        $mov->consecutivo_comprobante = $factura->codigo;
                        $mov->fecha_elaboracion = $factura->fecha;
                        $mov->documento_id = $factura->id;
                        $mov->codigo_cuenta = isset($impuesto->pucVenta()->codigo) ? $impuesto->pucVenta()->codigo : '';
                        $mov->cuenta_id = isset($impuesto->pucVenta()->id) ? $impuesto->pucVenta()->id : '';
                        $mov->identificacion_tercero = $factura->cliente()->nit;
                        $mov->cliente_id = $factura->cliente()->id;
                        $mov->prefijo = $factura->numeracionFactura->prefijo;
                        $mov->consecutivo = $factura->codigo;
                        $mov->fecha_vencimiento = $factura->vencimiento;
                        $mov->descripcion = $impuesto->descripcion;
                        $mov->credito =  round($totalImp->total);
                        $mov->codigo_impuesto = $impuesto->id;
                        $mov->enlace_a = 2;
                        $mov->save();
                    }
                }
            }        

            //3ro. Registramos las retenciones de la factura.
            foreach($totalFactura->reten as $ret){
                if(isset($ret->total)){
                    $retencion = Retencion::find($ret->id);

                    if($retencion){
                        $mov = new PucMovimiento;
                        $mov->tipo_comprobante = "03";
                        $mov->consecutivo_comprobante = $factura->codigo;
                        $mov->fecha_elaboracion = $factura->fecha;
                        $mov->documento_id = $factura->id;
                        $mov->codigo_cuenta = isset($retencion->pucVenta()->codigo) ? $retencion->pucVenta()->codigo : '';
                        $mov->cuenta_id = isset($retencion->pucVenta()->id) ? $retencion->pucVenta()->id : '';
                        $mov->identificacion_tercero = $factura->cliente()->nit;
                        $mov->cliente_id = $factura->cliente()->id;
                        $mov->prefijo = $factura->numeracionFactura->prefijo;
                        $mov->consecutivo = $factura->codigo;
                        $mov->fecha_vencimiento = $factura->vencimiento;
                        $mov->descripcion = $retencion->descripcion;
                        $mov->debito =  $ret->total;
                        $mov->codigo_impuesto = $retencion->id;
                        $mov->enlace_a = 3;
                        $mov->save();
                    }
                }
            }          

            //4to. Registramos el medio de pago de la factura.
            $i =0;
            if(isset($request->formapago)){
                foreach($request->formapago as $forma => $key){
                    
                    $idIngreso = null;
                    if(isset($request->selectanticipo[$i])){
                        $idIngreso = $request->selectanticipo[$i]; //selectanticipo trae clave primaria de recibos de caja.
                    }
    
                    $mov = new PucMovimiento;
                    $mov->tipo_comprobante = "03";
                    $mov->consecutivo_comprobante = $factura->codigo;
                    $mov->fecha_elaboracion = $factura->fecha;
                    $mov->documento_id = $factura->id;
                    $mov->codigo_cuenta = isset($factura->formaPagoRequest($key,$idIngreso)->codigo) ? $factura->formaPagoRequest($key,$idIngreso)->codigo : '';
                    $mov->cuenta_id = isset($factura->formaPagoRequest($key,$idIngreso)->id) ? $factura->formaPagoRequest($key,$idIngreso)->id : '';
                    $mov->identificacion_tercero = $factura->cliente()->nit;
                    $mov->cliente_id = $factura->cliente()->id;
                    $mov->prefijo = $factura->numeracionFactura->prefijo;
                    $mov->consecutivo = $factura->codigo;
                    $mov->fecha_vencimiento = $factura->vencimiento;
                    $mov->descripcion = $factura->descripcion;
                    $mov->debito =  round($request->precioformapago[$i]);
                    $mov->enlace_a = 4;
                    $mov->formapago_id = $key;
                    $mov->recibocaja_id = $request->selectanticipo[$i];
                    $mov->save();

                    //si hay un rc. Descontamos el saldo a favor tanto del cliente como del recibo de caja.
                    if($idIngreso){
                        $mov->restarAnticipo();
                    }
                    $i++;
                }
            }
        }

        //opcion 2 es para actualizar el movimiento
        else if($opcion == 2){

            /*
                Verificamos si los movimientos tienen asociado un recibo de caja, para posteriormente devolver la plata al 
                recibo de caja y al contacto
            */
            $movimientos = PucMovimiento::where('documento_id',$factura->id)->where('tipo_comprobante',3)->get();
            foreach($movimientos as $mov){
                if($mov->recibocaja_id != null || $mov->recibocaja_id != 0){
                    $mov->sumarAnticipo();
                }
            }
            //obtenemos los movimientos contables de la factura y los eliminamos.
            $movimientos->delete();
            PucMovimiento::facturaVenta($factura,1,$request);
            
        }
        
    }

    public static function facturaCompra($ingreso, $opcion, $request){
        
         //opcion 1 es para guardar el movimientos, y miramos que no exista inngun movimiento sobre este documento
         $isGuardar = PucMovimiento::where('documento_id',$factura->id)->where('tipo_comprobante',4)->first();
         if($opcion == 1 && !$isGuardar){
 
             //ingresamos los valores del iva
             $totalFactura = $factura->total();
             // return response()->json($totalFactura->reten);    
 
             //1ro. registramos los movimientos contables de los items.
             foreach($factura->itemsFactura as $item){
                 
                 //iteramos sobre las cuentas contables a las que está asignado el producto.
                 foreach($item->cuentasContable() as $cuentaItem){
                     
                     //si es tipo 3 (el tipo de producto o servicio que significa venta)
                     if($cuentaItem->tipo == 1){
                         $mov = new PucMovimiento;
                         $mov->tipo_comprobante = "04";
                         $mov->consecutivo_comprobante = $factura->codigo;
                         $mov->fecha_elaboracion = $factura->fecha;
                         $mov->documento_id = $factura->id;
                         $mov->codigo_cuenta = isset($cuentaItem->puc->codigo) ? $cuentaItem->puc->codigo : '';
                         $mov->cuenta_id = isset($cuentaItem->puc->id) ? $cuentaItem->puc->id : '';
                         $mov->identificacion_tercero = $factura->cliente()->nit;
                         $mov->cliente_id = $factura->cliente()->id;
                         $mov->consecutivo = $factura->nro;
                         $mov->fecha_vencimiento = $factura->vencimiento;
                         $mov->descripcion = $item->descripcion;
                         if($cuentaItem->tipo == 3){$mov->credito =  round($item->total());}
                         if($cuentaItem->tipo == 2){$mov->debito = $item->totalCompra();}
                         if($cuentaItem->tipo == 1){$mov->debito = $item->total();}
                         $mov->enlace_a = 1;
                         $mov->save();
                     }
                     //buscamos si el item en inventario el tipo_producto es inventariable (tipo 1).
                     
             }
             }
 
             //2do. registramos el iva de la factura.
             foreach ($totalFactura->imp as $totalImp) {
                 if (isset($totalImp->total)) {
                     $impuesto = Impuesto::find($totalImp->id);
                     if($impuesto){
                         $mov = new PucMovimiento;
                         $mov->tipo_comprobante = "04";
                         $mov->consecutivo_comprobante = $factura->codigo;
                         $mov->fecha_elaboracion = $factura->fecha;
                         $mov->documento_id = $factura->id;
                         $mov->codigo_cuenta = isset($impuesto->pucCompra()->codigo) ? $impuesto->pucCompra()->codigo : '';
                         $mov->cuenta_id = isset($impuesto->pucCompra()->id) ? $impuesto->pucCompra()->id : '';
                         $mov->identificacion_tercero = $factura->cliente()->nit;
                         $mov->cliente_id = $factura->cliente()->id;
                         $mov->consecutivo = $factura->nro;
                         $mov->fecha_vencimiento = $factura->vencimiento;
                         $mov->descripcion = $impuesto->descripcion;
                         $mov->debito =  round($totalImp->total);
                         $mov->codigo_impuesto = $impuesto->id;
                         $mov->enlace_a = 2;
                         $mov->save();
                     }
                 }
             }        
 
             //3ro. Registramos las retenciones de la factura.
             foreach($totalFactura->reten as $ret){
                 if(isset($ret->total)){
                     $retencion = Retencion::find($ret->id);
 
                     if($retencion){
                         $mov = new PucMovimiento;
                         $mov->tipo_comprobante = "04";
                         $mov->consecutivo_comprobante = $factura->codigo;
                         $mov->fecha_elaboracion = $factura->fecha;
                         $mov->documento_id = $factura->id;
                         $mov->codigo_cuenta = isset($retencion->pucCompra()->codigo) ? $retencion->pucCompra()->codigo : '';
                         $mov->cuenta_id = isset($retencion->pucCompra()->id) ? $retencion->pucCompra()->id : '';
                         $mov->identificacion_tercero = $factura->cliente()->nit;
                         $mov->cliente_id = $factura->cliente()->id;
                         $mov->consecutivo = $factura->nro;
                         $mov->fecha_vencimiento = $factura->vencimiento;
                         $mov->descripcion = $retencion->descripcion;
                         $mov->credito =  $ret->total;
                         $mov->codigo_impuesto = $retencion->id;
                         $mov->enlace_a = 3;
                         $mov->save();
                     }
                 }
             }          
 
             //4to. Registramos el medio de pago de la factura.
             $mov = new PucMovimiento;
             $mov->tipo_comprobante = "04";
             $mov->consecutivo_comprobante = $factura->codigo;
             $mov->fecha_elaboracion = $factura->fecha;
             $mov->documento_id = $factura->id;
             $mov->codigo_cuenta = isset($factura->formaPago()->codigo) ? $factura->formaPago()->codigo : '';
             $mov->cuenta_id = isset($factura->formaPago()->id) ? $factura->formaPago()->id : '';
             $mov->identificacion_tercero = $factura->cliente()->nit;
             $mov->cliente_id = $factura->cliente()->id;
             $mov->consecutivo = $factura->nro;
             $mov->fecha_vencimiento = $factura->vencimiento;
             $mov->descripcion = $factura->descripcion;
             $mov->credito =  round($factura->total()->total);
             $mov->enlace_a = 4;
             $mov->save();
 
         }
 
         //opcion 2 es para actualizar el movimiento
         else if($opcion == 2){
 
             //obtenemos los movimientos contables de la factura y los eliminamos.
             $movimientos = PucMovimiento::where('documento_id',$factura->id)->where('tipo_comprobante',4)->delete();
             PucMovimiento::facturaCompra($factura,1);
             
         }
         
    }

    /* 
        TIPO:
        0: cuando es un anticipo a una categoria
        1: cuando es un pago a una factura y se paga un poco mas.
        2: cuando es el proceso normal, sin saldos a favores ni anticipos

        OPCION:
        0: Actualizar el movimiento y borrar el anterior.
        1: guardar el movimiento, y miramos que no exista inngun movimiento sobre este documento
    */
    public static function ingreso($ingreso, $opcion, $tipo=0){

        $isGuardar = PucMovimiento::where('documento_id',$ingreso->id)->where('tipo_comprobante',1)->first();
        
        $totalIngreso = 0;
        
        //TIPO 0.
        if($opcion == 1 && !$isGuardar && $tipo == 0){  

            foreach($ingreso->ingresosCategorias() as $cat){
                $totalIngreso+=$cat->valor;
            }
            
            //1to. Registramos la forma de pago (caja o banco).
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "01";
            $mov->consecutivo_comprobante = $ingreso->nro;
            $mov->fecha_elaboracion = $ingreso->fecha;
            $mov->documento_id = $ingreso->id;
            $mov->codigo_cuenta = isset($ingreso->ingresoPuc()->codigo) ? $ingreso->ingresoPuc()->codigo : '';
            $mov->cuenta_id = isset($ingreso->ingresoPuc()->id) ? $ingreso->ingresoPuc()->id : '';
            $mov->identificacion_tercero = $ingreso->cliente()->nit;
            $mov->cliente_id = $ingreso->cliente()->id;
            $mov->consecutivo = $ingreso->nro;
            $mov->descripcion = $ingreso->observaciones;
            $mov->debito =  $totalIngreso;
            $mov->enlace_a = 4;
            $mov->save();

            //2do. Registramos el anticipo del cliente.
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "01";
            $mov->consecutivo_comprobante = $ingreso->nro;
            $mov->fecha_elaboracion = $ingreso->fecha;
            $mov->documento_id = $ingreso->id;
            $mov->codigo_cuenta = isset($ingreso->ingresoAnticipo()->codigo) ? $ingreso->ingresoAnticipo()->codigo : '';
            $mov->cuenta_id = isset($ingreso->ingresoAnticipo()->id) ? $ingreso->ingresoAnticipo()->id : '';
            $mov->identificacion_tercero = $ingreso->cliente()->nit;
            $mov->cliente_id = $ingreso->cliente()->id;
            $mov->consecutivo = $ingreso->nro;
            $mov->descripcion = $ingreso->observaciones;
            $mov->credito =  $totalIngreso;
            $mov->enlace_a = 5;
            $mov->save();
        }

        //TIPO 1
        else if($opcion == 1 && !$isGuardar && $tipo == 1){

            foreach($ingreso->ingresosFacturas() as $ingresoFactura){
                $totalIngreso+=$ingresoFactura->pago;

                $mov = new PucMovimiento;
                $mov->tipo_comprobante = "01";
                $mov->consecutivo_comprobante = $ingreso->nro;
                $mov->fecha_elaboracion = $ingreso->fecha;
                $mov->documento_id = $ingreso->id;
                $mov->codigo_cuenta = isset($ingresoFactura->factura()->formaPago()->codigo) ? $ingresoFactura->factura()->formaPago()->codigo : '';
                $mov->cuenta_id = isset($ingresoFactura->factura()->formaPago()->id) ? $ingresoFactura->factura()->formaPago()->id : '';
                $mov->identificacion_tercero = $ingreso->cliente()->nit;
                $mov->cliente_id = $ingreso->cliente()->id;
                $mov->consecutivo = $ingreso->nro;
                $mov->descripcion = $ingreso->observaciones;
                $mov->credito =  $ingresoFactura->factura()->total()->total;
                $mov->enlace_a = 1;
                $mov->save();
            }
            
            //1to. Registramos la forma de pago (caja o banco).
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "01";
            $mov->consecutivo_comprobante = $ingreso->nro;
            $mov->fecha_elaboracion = $ingreso->fecha;
            $mov->documento_id = $ingreso->id;
            $mov->codigo_cuenta = isset($ingreso->ingresoPucBanco()->codigo) ? $ingreso->ingresoPucBanco()->codigo : '';
            $mov->cuenta_id = isset($ingreso->ingresoPucBanco()->id) ? $ingreso->ingresoPucBanco()->id : '';
            $mov->identificacion_tercero = $ingreso->cliente()->nit;
            $mov->cliente_id = $ingreso->cliente()->id;
            $mov->consecutivo = $ingreso->nro;
            $mov->descripcion = $ingreso->observaciones;
            $mov->debito =  $totalIngreso;
            $mov->enlace_a = 4;
            $mov->save();

            //2do. Registramos el anticipo del cliente.
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "01";
            $mov->consecutivo_comprobante = $ingreso->nro;
            $mov->fecha_elaboracion = $ingreso->fecha;
            $mov->documento_id = $ingreso->id;
            $mov->codigo_cuenta = isset($ingreso->ingresoAnticipoFactura()->codigo) ? $ingreso->ingresoAnticipoFactura()->codigo : '';
            $mov->cuenta_id = isset($ingreso->ingresoAnticipoFactura()->id) ? $ingreso->ingresoAnticipoFactura()->id : '';
            $mov->identificacion_tercero = $ingreso->cliente()->nit;
            $mov->cliente_id = $ingreso->cliente()->id;
            $mov->consecutivo = $ingreso->nro;
            $mov->descripcion = $ingreso->observaciones;
            $mov->credito =  $ingreso->saldoFavorIngreso;
            $mov->enlace_a = 5;
            $mov->save();
            
        }

        //TIPO 2
        else if($opcion == 1 && !$isGuardar && $tipo == 2){

            foreach($ingreso->ingresosFacturas() as $ingresoFactura){
                $totalIngreso+=$ingresoFactura->pago;

                $mov = new PucMovimiento;
                $mov->tipo_comprobante = "01";
                $mov->consecutivo_comprobante = $ingreso->nro;
                $mov->fecha_elaboracion = $ingreso->fecha;
                $mov->documento_id = $ingreso->id;
                $mov->codigo_cuenta = isset($ingresoFactura->factura()->formaPago()->codigo) ? $ingresoFactura->factura()->formaPago()->codigo : '';
                $mov->cuenta_id = isset($ingresoFactura->factura()->formaPago()->id) ? $ingresoFactura->factura()->formaPago()->id : '';
                $mov->identificacion_tercero = $ingreso->cliente()->nit;
                $mov->cliente_id = $ingreso->cliente()->id;
                $mov->consecutivo = $ingreso->nro;
                $mov->descripcion = $ingreso->observaciones;
                $mov->credito =  $ingresoFactura->factura()->total()->total;
                $mov->enlace_a = 1;
                $mov->save();
            }
            
            //1to. Registramos la forma de pago (caja o banco).
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "01";
            $mov->consecutivo_comprobante = $ingreso->nro;
            $mov->fecha_elaboracion = $ingreso->fecha;
            $mov->documento_id = $ingreso->id;
            $mov->codigo_cuenta = isset($ingreso->ingresoPucBanco()->codigo) ? $ingreso->ingresoPucBanco()->codigo : '';
            $mov->cuenta_id = isset($ingreso->ingresoPucBanco()->id) ? $ingreso->ingresoPucBanco()->id : '';
            $mov->identificacion_tercero = $ingreso->cliente()->nit;
            $mov->cliente_id = $ingreso->cliente()->id;
            $mov->consecutivo = $ingreso->nro;
            $mov->descripcion = $ingreso->observaciones;
            $mov->debito =  $totalIngreso;
            $mov->enlace_a = 4;
            $mov->save();            
        }
        
    }

   /* 
        TIPO:
        0: cuando es un anticipo a una categoria
        1: cuando es un pago a una factura y se paga un poco mas.
        2: cuando es el proceso normal, sin saldos a favores ni anticipos

        OPCION:
        0: Actualizar el movimiento y borrar el anterior.
        1: guardar el movimiento, y miramos que no exista inngun movimiento sobre este documento
    */
    public static function gasto($gasto, $opcion, $tipo=0){

        $isGuardar = PucMovimiento::where('documento_id',$gasto->id)->where('tipo_comprobante',2)->first();
        
        $totalIngreso = 0;
        
        //TIPO 0.
        if($opcion == 1 && !$isGuardar && $tipo == 0){  

            foreach($gasto->gastosCategorias() as $cat){
                $totalIngreso+=$cat->valor;
            }
            
            //1to. Registramos la forma de pago (caja o banco).
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "02";
            $mov->consecutivo_comprobante = $gasto->nro;
            $mov->fecha_elaboracion = $gasto->fecha;
            $mov->documento_id = $gasto->id;
            $mov->codigo_cuenta = isset($gasto->gastoPuc()->codigo) ? $gasto->gastoPuc()->codigo : '';
            $mov->cuenta_id = isset($gasto->gastoPuc()->id) ? $gasto->gastoPuc()->id : '';
            $mov->identificacion_tercero = $gasto->beneficiario()->nit;
            $mov->cliente_id = $gasto->beneficiario()->id;
            $mov->consecutivo = $gasto->nro;
            $mov->descripcion = $gasto->observaciones;
            $mov->credito =  $totalIngreso;
            $mov->enlace_a = 4;
            $mov->save();

            //2do. Registramos el anticipo del cliente.
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "02";
            $mov->consecutivo_comprobante = $gasto->nro;
            $mov->fecha_elaboracion = $gasto->fecha;
            $mov->documento_id = $gasto->id;
            $mov->codigo_cuenta = isset($gasto->gastoAnticipo()->codigo) ? $gasto->gastoAnticipo()->codigo : '';
            $mov->cuenta_id = isset($gasto->gastoAnticipo()->id) ? $gasto->gastoAnticipo()->id : '';
            $mov->identificacion_tercero = $gasto->beneficiario()->nit;
            $mov->cliente_id = $gasto->beneficiario()->id;
            $mov->consecutivo = $gasto->nro;
            $mov->descripcion = $gasto->observaciones;
            $mov->debito =  $totalIngreso;
            $mov->enlace_a = 5;
            $mov->save();
        }

        //TIPO 1
        else if($opcion == 1 && !$isGuardar && $tipo == 1){

            foreach($gasto->gastosFacturas() as $gastoFactura){
                $totalIngreso+=$gastoFactura->pago;

                $mov = new PucMovimiento;
                $mov->tipo_comprobante = "02";
                $mov->consecutivo_comprobante = $gasto->nro;
                $mov->fecha_elaboracion = $gasto->fecha;
                $mov->documento_id = $gasto->id;
                $mov->codigo_cuenta = isset($gastoFactura->factura()->formaPago()->codigo) ? $gastoFactura->factura()->formaPago()->codigo : '';
                $mov->cuenta_id = isset($gastoFactura->factura()->formaPago()->id) ? $gastoFactura->factura()->formaPago()->id : '';
                $mov->identificacion_tercero = $gasto->beneficiario()->nit;
                $mov->cliente_id = $gasto->beneficiario()->id;
                $mov->consecutivo = $gasto->nro;
                $mov->descripcion = $gasto->observaciones;
                $mov->debito =  $gastoFactura->factura()->total()->total;
                $mov->enlace_a = 1;
                $mov->save();
            }
            
            //1to. Registramos la forma de pago (caja o banco).
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "02";
            $mov->consecutivo_comprobante = $gasto->nro;
            $mov->fecha_elaboracion = $gasto->fecha;
            $mov->documento_id = $gasto->id;
            $mov->codigo_cuenta = isset($gasto->gastoPucBanco()->codigo) ? $gasto->gastoPucBanco()->codigo : '';
            $mov->cuenta_id = isset($gasto->gastoPucBanco()->id) ? $gasto->gastoPucBanco()->id : '';
            $mov->identificacion_tercero = $gasto->beneficiario()->nit;
            $mov->cliente_id = $gasto->beneficiario()->id;
            $mov->consecutivo = $gasto->nro;
            $mov->descripcion = $gasto->observaciones;
            $mov->credito =  $totalIngreso;
            $mov->enlace_a = 4;
            $mov->save();

            //2do. Registramos el anticipo del cliente.
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "02";
            $mov->consecutivo_comprobante = $gasto->nro;
            $mov->fecha_elaboracion = $gasto->fecha;
            $mov->documento_id = $gasto->id;
            $mov->codigo_cuenta = isset($gasto->gastoAnticipoFactura()->codigo) ? $gasto->gastoAnticipoFactura()->codigo : '';
            $mov->cuenta_id = isset($gasto->gastoAnticipoFactura()->id) ? $gasto->gastoAnticipoFactura()->id : '';
            $mov->identificacion_tercero = $gasto->beneficiario()->nit;
            $mov->cliente_id = $gasto->beneficiario()->id;
            $mov->consecutivo = $gasto->nro;
            $mov->descripcion = $gasto->observaciones;
            $mov->debito =  $gasto->saldoFavorIngreso;
            $mov->enlace_a = 5;
            $mov->save();
            
        }

        //TIPO 2
        else if($opcion == 1 && !$isGuardar && $tipo == 2){

            foreach($gasto->gastosFacturas() as $gastoFactura){
                $totalIngreso+=$gastoFactura->pago;

                $mov = new PucMovimiento;
                $mov->tipo_comprobante = "02";
                $mov->consecutivo_comprobante = $gasto->nro;
                $mov->fecha_elaboracion = $gasto->fecha;
                $mov->documento_id = $gasto->id;
                $mov->codigo_cuenta = isset($gastoFactura->factura()->formaPago()->codigo) ? $gastoFactura->factura()->formaPago()->codigo : '';
                $mov->cuenta_id = isset($gastoFactura->factura()->formaPago()->id) ? $gastoFactura->factura()->formaPago()->id : '';
                $mov->identificacion_tercero = $gasto->beneficiario()->nit;
                $mov->cliente_id = $gasto->beneficiario()->id;
                $mov->consecutivo = $gasto->nro;
                $mov->descripcion = $gasto->observaciones;
                $mov->debito =  $gastoFactura->factura()->total()->total;
                $mov->enlace_a = 1;
                $mov->save();
            }
            
            //1to. Registramos la forma de pago (caja o banco).
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "02";
            $mov->consecutivo_comprobante = $gasto->nro;
            $mov->fecha_elaboracion = $gasto->fecha;
            $mov->documento_id = $gasto->id;
            $mov->codigo_cuenta = isset($gasto->gastoPucBanco()->codigo) ? $gasto->gastoPucBanco()->codigo : '';
            $mov->cuenta_id = isset($gasto->gastoPucBanco()->id) ? $gasto->gastoPucBanco()->id : '';
            $mov->identificacion_tercero = $gasto->beneficiario()->nit;
            $mov->cliente_id = $gasto->beneficiario()->id;
            $mov->consecutivo = $gasto->nro;
            $mov->descripcion = $gasto->observaciones;
            $mov->credito =  $totalIngreso;
            $mov->enlace_a = 4;
            $mov->save();
            
        }
    }

    public function cliente(){
        return $this->belongsTo(Contacto::class,'cliente_id');
    }

    public function cuenta(){
        return Puc::find($this->cuenta_id);
    }

    public function documento(){
        if($this->tipo_comprobante == 3){
            return $this->belongsTo(Factura::class,'documento_id');
        }
    }

    public function asociadoA(){
        switch ($this->enlace_a) {
            case 1:
                return "Asociado a un item.";
                break;
            case 2:
                return "Asociado a un iva.";
                break;
            case 3:
                return "Asociado a una retención.";
                break;
            case 4:
                return "Asociado a un medio de pago.";
                break;

            case 5:
                return "Asociado a un anticipo del cliente.";
                break;

            case 6:
                return "Asociado a una deuda del cliente.";
                break;
            
            default:
                return "";
                break;
        }
    }

    public function totalDebito(){
        return DB::table('puc_movimiento')->where('documento_id',$this->documento_id)->where('tipo_comprobante',$this->tipo_comprobante)
        ->select(DB::raw("SUM((`debito`)) as total"))->first();
    }
    public function totalCredito(){
        return DB::table('puc_movimiento')->where('documento_id',$this->documento_id)->where('tipo_comprobante',$this->tipo_comprobante)
        ->select(DB::raw("SUM((`credito`)) as total"))->first();
    } 
    
    public function restarAnticipo(){

        if($this->debito > 0){$valorUsado = $this->debito;}
        else{$valorUsado = $this->credito;}

        if($this->recibocaja_id != null || $this->recibocaja_id != 0){
            $ingreso = Ingreso::where('id',$this->recibocaja_id)->first();

            if($ingreso){
                $ingreso->valor_anticipo=$ingreso->valor_anticipo - $valorUsado;
                $ingreso->save();
            }   

            $contacto = Contacto::find($this->cliente_id);
            if($contacto){
                $contacto->saldo_favor = $contacto->saldo_favor - $valorUsado;
                $contacto->save();
            }
        }
    }

    public function sumarAnticipo(){

        if($this->debito > 0){$valorUsado = $this->debito;}
        else{$valorUsado = $this->credito;}

        if($this->recibocaja_id != null || $this->recibocaja_id != 0){
            $ingreso = Ingreso::where('id',$this->recibocaja_id)->first();

            if($ingreso){
                $ingreso->valor_anticipo=$ingreso->valor_anticipo + $valorUsado;
                $ingreso->save();
            }

            $contacto = Contacto::find($this->cliente_id);
            if($contacto){
                $contacto->saldo_favor = $contacto->saldo_favor + $valorUsado;
                $contacto->save();
            }
        }
    }

}
