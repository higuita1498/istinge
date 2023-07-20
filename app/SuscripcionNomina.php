<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Empresa;
use App\Model\Nomina\Persona;

class SuscripcionNomina extends Model
{
    protected $table = "ne_suscripciones";

    protected $fillable = [
        'fec_vencimiento','prorroga','fec_inicio','id_empresa','created_at','updated_at',
        'fec_corte'
    ];

    public function empresa()
    {
        $empresa = Empresa::where('id', $this->id_empresa)->first();
        return $empresa;
    }

     /**
     * Verficia la cantidad personas que están usando la nómina, para validar que si la puedan usar
     * .
     * @return array|bool
     */
    public function personal()
    {
        $suscripcion = SuscripcionPagoNomina::where('id_empresa', Auth::user()->empresa)->where('estado', 1)->get()->last();
        $fechaAnt    = (Carbon::parse($this->fec_corte))->subMonth();
        $fechaCorte  = Carbon::parse($this->fec_corte);

        if($suscripcion){
            switch ($suscripcion->plan) {
                case 0:
                    $cant =  6;
                    break;
                case 1:
                    $cant =  15;
                    break;
                case 2:
                    $cant =  25;
                    break;
                case 3:
                    $cant =  50;
                    break;
            }
        }else{
            //Cantidad ilimitada de personas para el plan gratuito
            $cant =  300;
        }

        //$personal = Persona::where('created_at', '>=', $fechaAnt)->where('created_at', '<=', $fechaCorte)->where('fk_empresa', $this->id_empresa)->get();
        $personal = Persona::where('fk_empresa', $this->id_empresa)->where('status',1)->get();
        $personal = count($personal);

        return ($personal <= $cant) ? false : true;
    }
}
