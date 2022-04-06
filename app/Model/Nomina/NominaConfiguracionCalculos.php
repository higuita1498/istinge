<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Funcion;

class NominaConfiguracionCalculos extends Model
{
    protected $table = 'ne_nomina_configuracion_calculos';
    protected $fillable = ['nombre', 'tipo', 'simbolo', 'valor', 'observaciones', 'fk_idempresa', 'updated_at', 'created_at'];
    //
    
    /**
     * Retorna el nombre del tipo (1= porcentual, 2 =numerico)
     *
     * @return varchar
     */
    public function tipo(){
        return $this->tipo == 1 ? '%' : '$';
    }

    /**
     * Retorna el valor parseado
     *
     * @return varchar
     */
    public function valor(){
        if($this->tipo == 1){
        return "%" . Funcion::parsear($this->valor);
        }
        return "$" . Funcion::parsear($this->valor);
    }

    /**
     * Retorna el porcentaje en decimal (de entero a decimal)
     *
     * @return int
     */

    public function porcDecimal(){
        if($this->tipo == 1){
            return $this->valor / 100;
        }
    }

}
