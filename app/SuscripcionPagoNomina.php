<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Empresa;

class SuscripcionPagoNomina extends Model
{

    protected $table = "ne_suscripciones_pagos";
    protected $fillable = [
        'id_empresa','plan','tipo_pago','referencia','meses','estado','monto','suplentepago_id','medio_pago',
    ];

    public function empresa()
    {
        $empresa = Empresa::where('id', $this->id_empresa)->first();

        return $empresa;
    }

    public function plan()
    {
        $plan='';
        if ($this->personalizado == 1) {
            $plan = DB::table('planes_personalizados')->find($this->plan);
            return $plan->nombre;
        }
        switch ($this->plan) {
            case 0:
                $plan = "Nómina Electrónica Básico";
                break;
            case 2:
                $plan = "Nómina Electrónica Emprendedor";
                break;
            case 1:
                $plan = "Nómina Electrónica Pyme";
                break;
            case 3:
                $plan = "Nómina Electrónica Avanzado";
                break;
        }

        return $plan;
    }

    public function tipoPago()
    {
        $tipoPago='';

        if ($this->tipo_pago == 1) {
            $tipoPago = 'Transferencia';
        } elseif ($this->tipo_pago == 2) {
            $tipoPago = 'Paypal';
        } else {
            $tipoPago = 'WOMPI';
        }

        return $tipoPago;
    }

    public function estado()
    {
        if ($this->estado == 0) {
            return 'Pendiente';
        } elseif ($this->estado == 1) {
            return 'Aprobado';
        } elseif ($this->estado == 2) {
            return 'Declinado';
        } elseif ($this->estado == 3) {
            return 'Anulado';
        }
    }

    /**
     * Retorna la fecha de vencimiento del pago de la suscripcion
     * @return Carbon
     */
    public function getExpirationAttribute()
    {
        if ($this->estado != 1) {
            return $this->created_at;
        }

        return Carbon::parse($this->created_at)->addMonth($this->meses);
    }

    /**
     * Determina si la suscripcion esta vigente o no
     * @return bool
     */
    public function getValidAttribute()
    {
        return !Carbon::now()->gte($this->expiration);
    }
}