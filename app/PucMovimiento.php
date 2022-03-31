<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
    

}
