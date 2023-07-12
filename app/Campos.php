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
                return 'contactos/clientes';
            }else if($this->modulo == 2){
                return 'contratos';
            }else if($this->modulo == 3){
                return 'inventario';
            }else if($this->modulo == 4){
                return 'facturas';
            }else if($this->modulo == 5){
                return 'ingresos';
            }else if($this->modulo == 6){
                return 'facturasp';
            }else if($this->modulo == 7){
                return 'pagos';
            }else if($this->modulo == 8){
                return 'pagosrecurrentes';
            }else if($this->modulo == 9){
                return 'descuentos';
            }else if($this->modulo == 10){
                return 'planes-velocidad';
            }else if($this->modulo == 11){
                return 'promesas-pago';
            }else if($this->modulo == 12){
                return 'radicados';
            }else if($this->modulo == 13){
                return 'monitor-blacklist';
            }else if($this->modulo == 14){
                return 'ventas-externas';
            }else if($this->modulo == 15){
                return 'mikrotik';
            }else if($this->modulo == 16){
                return 'bancos';
            }else if($this->modulo == 17){
                return 'oficinas';
            }else if($this->modulo == 18){
                return 'notascredito';
            }else if($this->modulo == 19){
                return 'cotizaciones';
            }else if($this->modulo == 20){
                return 'remisiones';
            }else if($this->modulo == 21){
                return 'productos';
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
        }else if($this->modulo == 10){
            return 'Planes de Velocidad';
        }else if($this->modulo == 11){
            return 'Promesas de Pago';
        }else if($this->modulo == 12){
            return 'Radicados';
        }else if($this->modulo == 13){
            return 'Monitor Blacklist';
        }else if($this->modulo == 14){
            return 'Ventas Externas';
        }else if($this->modulo == 15){
            return 'Mikrotik';
        }else if($this->modulo == 16){
            return 'Bancos';
        }else if($this->modulo == 17){
            return 'Oficinas';
        }else if($this->modulo == 18){
            return 'Notas de Crédito';
        }else if($this->modulo == 19){
            return 'Cotizaciones';
        }else if($this->modulo == 20){
            return 'Remisiones';
        }else if($this->modulo == 21){
            return 'Productos (Asignación/Devolución)';
        }
    }
}
