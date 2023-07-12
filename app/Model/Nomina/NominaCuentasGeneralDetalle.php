<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;
use App\Model\Nomina\NominaCuentasTipo;

class NominaCuentasGeneralDetalle extends Model
{
    protected $table = "ne_nomina_cuentas_generales_detalle";
    protected $primaryKey = ['fk_nominaperiodo', 'fk_nomina_cuenta'];
    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['total_hora'];

    public function nominaCuentasTipo()
    {                   //Foreign key       // primary key
        return  $this->hasMany(NominaCuentasTipo::class, 'fk_nomina_cuenta', 'fk_nomina_cuenta');
    }
}
