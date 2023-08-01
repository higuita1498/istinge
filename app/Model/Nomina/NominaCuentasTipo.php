<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;
use App\Model\Nomina\NominaCuentasGeneralDetalle;

class NominaCuentasTipo extends Model
{
    protected $table = "ne_nomina_cuentas_tipo";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre','fk_nomina_cuenta'
    ];

    public function nominaCuentasGeneralDetalle()
    {
        return $this->belongsTo(NominaCuentasGeneralDetalle::class, 'fk_nomina_cuenta');
    }
}
