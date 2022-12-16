<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;

class ComprobanteLiquidacion extends Model
{

    protected $table = "ne_comprobante_liquidacion";
    protected $primaryKey = 'id';


    public function totalidad($nomina){

        $total = ['vacaciones' => 0, 'cesantias' => 0, 'interesesCesantias' => 0, 'prima' => 0, 'total' => 0];


        $total['vacaciones'] = ($this->dias_vacaciones * ($this->base_vacaciones / 30));
        $total['cesantias'] = ($this->base_cesantias * $this->dias_liquidar / 360);
        $total['interesesCesantias']  = ($total['cesantias'] * (12/100) * ($this->dias_liquidar / 360));
        $total['prima'] = ($this->base_prima * $this->dias_liquidar / 360);

        $diasSalario = 0;
        $empresa = auth()->user()->empresaObj;


        if($this->dias_liquidar <= 360){
            if($this->base_salario < ($empresa->getSalarioMinimo() * 10)){
                $diasSalario = 30;
            }else{
                $diasSalario = 20;
            }
        }else{
            if($this->base_salario < ($empresa->getSalarioMinimo() * 10)){
                $diasSalario = 30;
                $diaAdicional = 20;
            }else{
                $diasSalario = 20;
                $diaAdicional = 15;
            }

                $anosAdicionales = ($this->dias_liquidar - 360) / 360;
                if($anosAdicionales >= 1){
                   $anosAdicionales = intval($anosAdicionales);
                }else{
                    $anosAdicionales = 0;
                }

                $diasSalario += $diaAdicional * $anosAdicionales;
        }

        if(!$this->is_justa_causa){
            $total['indemnizacion'] = (($this->base_salario  / 30) * $diasSalario);
        }else{
            $total['indemnizacion'] = 0;
        }
        $total['otrosIngresos']  = floatval($this->otros_ingresos);

        $total['total'] = ($total['vacaciones'] + $total['cesantias'] + $total['interesesCesantias'] + $total['prima'] + $total['indemnizacion'] + $total['otrosIngresos']);

        if($this->total){
            $total['total'] = $this->total;
        }

        if($nomina){
            $total['nomina'] = floatval($this->total_nomina ?? 0);
            $total['total'] += $total['nomina'];
        }

        return $total;
    }

}
