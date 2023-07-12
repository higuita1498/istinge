<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;

class NominaTerminoContrato extends Model
{
    protected $table = "ne_termino_contrato";

    protected $fillable = ['nombre', 'codigo'];
}
