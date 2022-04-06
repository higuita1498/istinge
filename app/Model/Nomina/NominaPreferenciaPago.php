<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class NominaPreferenciaPago extends Model
{
    protected $table = 'ne_preferencia_pago';

    protected $fillable = [
        'frecuencia_pago',  'medio_pago', 'banco',
        'tipo_cuenta', 'nro_cuenta', 'operador_pago',
        'arl', 'fecha_constitucion', 'empresa'
    ];

    function periodo($periodo, $year, $tipo){

		if($tipo== 0 || $tipo == 1)
		{
			$day = 1;
		}
		else{
			$day = 16;
		}

		$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $fecha = $year. "-" . $periodo . "-" . $day;
        $date = Carbon::parse($fecha);
        $mes = $meses[($date->format('n')) - 1];

    	$date = Carbon::create($year, $periodo, $day);
    	$end = Carbon::create($year, $periodo, $day)->endOfMonth();
		// $end = date("Y-m-t", strtotime($fecha));
		// $end = Carbon::parse($end);


    	if($this->frecuencia_pago == 1){
    		if ($date->format('d') <= 15) {
    			return '1 - 15 de '.ucfirst($mes).' - '.$date->format('Y');
    		}else{
    			return $day . ' - '.$end->format('d').' de '.ucfirst($mes).' - '.$date->format('Y');
    		}
    	}elseif ($this->frecuencia_pago == 2) {
    		return '1 - '.$end->format('d').' de '.ucfirst($mes).' - '.$date->format('Y');
    	}
    	return '';
    }

    function periodoCompleto($periodo, $year){

		$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $fecha = $year. "-" . $periodo . "-1";
        $date = Carbon::parse($fecha);
        $mes = $meses[($date->format('n')) - 1];
        
    	$date = Carbon::create($year, $periodo, 1);
    	return ucfirst($mes).' - '.$date->format('Y');
    }
}
