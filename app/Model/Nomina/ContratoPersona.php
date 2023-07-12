<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;
use DB;
class ContratoPersona extends Model
{

    protected $table = "ne_contratos_persona";
    protected $primaryKey = 'id';


    public function comprobanteLiquidacion(){
        return $this->belongsTo('App\Model\Nomina\ComprobanteLiquidacion', 'fk_idcomprobante_liquidacion');
    }


    public function persona(){
        return $this->belongsTo('App\Model\Nomina\Persona', 'fk_idpersona');
    }


    public function tipo_contrato()
    {
        return DB::table('ne_tipo_contrato')->where('id', $this->fk_tipo_contrato)->first();
    }

    public function clase_riesgo()
    {
        if ($this->fk_clase_riesgo == null) {
            return '';
        }
        return DB::table('ne_clase_riesgos')->where('id', $this->fk_clase_riesgo)->first()->nombre;
    }

}
