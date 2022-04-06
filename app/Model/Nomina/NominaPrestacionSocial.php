<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;

class NominaPrestacionSocial extends Model
{
    protected $table = "ne_nomina_prestaciones_sociales";
    protected $primaryKey = 'id';
    //


    public function nomina(){
        return $this->belongsTo(Nomina::class, 'fk_idnomina');
    }

}
