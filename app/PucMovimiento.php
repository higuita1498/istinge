<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Impuesto;
use App\Retencion;

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
       'mes_cierre', 'documento_id', 'created_at', 'updated_at'
    ];

    public static function facturaVenta($factura, $opcion){

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
                if($cuentaItem->tipo == 3){
                    $mov = new PucMovimiento;
                    $mov->tipo_comprobante = "03";
                    $mov->consecutivo_comprobante = $factura->codigo;
                    $mov->fecha_elaboracion = $factura->fecha;
                    $mov->documento_id = $factura->id;
                    $mov->codigo_cuenta = isset($cuentaItem->puc->codigo) ? $cuentaItem->puc->codigo : '';
                    $mov->identificacion_tercero = $factura->cliente()->nit;
                    $mov->prefijo = $factura->numeracionFactura->prefijo;
                    $mov->consecutivo = $factura->codigo;
                    $mov->fecha_vencimiento = $factura->vencimiento;
                    $mov->descripcion = $item->descripcion;
                    $mov->credito = $item->precio;
                    $mov->enlace_a = 1;
                    $mov->save();
                }

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
                        $mov->codigo_cuenta = isset($impuesto->puc()->codigo) ? $impuesto->puc()->codigo : '';
                        $mov->identificacion_tercero = $factura->cliente()->nit;
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
                        $mov->identificacion_tercero = $factura->cliente()->nit;
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
            $mov = new PucMovimiento;
            $mov->tipo_comprobante = "03";
            $mov->consecutivo_comprobante = $factura->codigo;
            $mov->fecha_elaboracion = $factura->fecha;
            $mov->documento_id = $factura->id;
            $mov->codigo_cuenta = isset($factura->formaPago()->codigo) ? $factura->formaPago()->codigo : '';
            $mov->identificacion_tercero = $factura->cliente()->nit;
            $mov->prefijo = $factura->numeracionFactura->prefijo;
            $mov->consecutivo = $factura->codigo;
            $mov->fecha_vencimiento = $factura->vencimiento;
            $mov->descripcion = $factura->descripcion;
            $mov->debito =  round($factura->total()->total);
            $mov->enlace_a = 4;
            $mov->save();

        }

        //opcion 2 es para actualizar el movimiento
        else if($opcion == 2){

        }
        
    }
    

}
