<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB; 
use Auth;

class Campos extends Model{
    protected $table = "campos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa', 'campo', 'nombre', 'varchar', 'tipo', 'default', 'status', 'tabla', 'descripcion', 'autocompletar'
    ];

    public function modulo($url = false){
        if($url){
            if($this->modulo == 1){
                return 'clientes.index';
            }else if($this->modulo == 2){
                return 'contratos.index';
            }else if($this->modulo == 3){
                return 'inventario.index';
            }else if($this->modulo == 4){
                return 'facturas.index';
            }else if($this->modulo == 5){
                return 'ingresos.index';
            }else if($this->modulo == 6){
                return 'facturasp.index';
            }else if($this->modulo == 7){
                return 'pagos.index';
            }else if($this->modulo == 8){
                return 'pagosrecurrentes.index';
            }else if($this->modulo == 9){
                return 'descuentos.index';
            }
        }

        if($this->modulo == 1){
            return 'Contactos';
        }else if($this->modulo == 2){
            return 'Contratos';
        }else if($this->modulo == 3){
            return 'Inventario';
        }else if($this->modulo == 4){
            return 'Factura de Venta';
        }else if($this->modulo == 5){
            return 'Pagos / Ingresos';
        }else if($this->modulo == 6){
            return 'Factura de Proveedores';
        }else if($this->modulo == 7){
            return 'Pagos / Egresos';
        }else if($this->modulo == 8){
            return 'Pagos Recurrentes';
        }else if($this->modulo == 9){
            return 'Descuentos';
        }
    }
}
