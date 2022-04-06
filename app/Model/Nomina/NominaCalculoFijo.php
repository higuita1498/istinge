<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;

class NominaCalculoFijo extends Model
{
    protected $table = 'ne_nomina_calculos_fijos';
    protected $fillable = ['tipo', 'valor', 'simbolo', 'fk_nominaperiodo'];
    //

    public function nominaPeriodo(){
        return $this->belongsTo(NominaPeriodos::class, 'fk_nominaperiodo');
    }
}
