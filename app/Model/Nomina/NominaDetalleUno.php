<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Model\Nomina\Nomina;
use App\Model\Nomina\NominaPeriodos;
use App\Categoria;
use App\Traits\Funciones;
use Carbon\Carbon;

class NominaDetalleUno extends Model
{
    use Funciones;

    protected $table = "ne_nomina_cuentas_detalle";
    protected $primaryKey = 'id';
    protected $appends = ['dias_vacaciones'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'numero_horas',
        'valor_hora_ordinaria',
        'valor_categoria',
        'pago_anticipado',
        'dias_compensados_dinero',
        'fk_nominaperiodo',
        'fk_nomina_cuenta_tipo',
        'fk_nomina_cuenta',
        'fk_nomina_cuenta_ddos',
        'fk_categoria',
        'tipo_incapacidad',
        'updated_at',
        'created_at'
    ];

    protected $arrNoRemunerado = ['AUSENCIA INJUSTIFICADA', 'SUSPENSION', 'LICENCIA NO REMUNERADA'];

    public function getValorHoraOrdinariaAttribute($value)
    {
        $valor = doubleval($value);
        return str_pad($valor, 2, '0');
    }

    public function getValorCategoriaAttribute($value)
    {
        $valor = doubleval($value);
        return str_pad($valor, 2, '0');
    }


    public function nominaPeriodo()
    {
        return $this->belongsTo(NominaPeriodos::class, 'fk_nominaperiodo');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'fk_categoria');
    }

    public function is_remunerado($categoria = null, $attr = true)
    {

        if ($categoria == null) {
            $categoria = $this->nombre;
        }
      
        if (in_array(strtoupper($categoria), $this->arrNoRemunerado)) {
            if($attr){
                $this->isRemunerado = false;
            }
            return false;
        } else {
            if($attr){
                $this->isRemunerado = true;
            }
            return true;
        }
    }

    public function horas()
    {
        if ($this->fecha_inicio) {
            $fechaEmision = Carbon::parse($this->fecha_inicio);
            $fechaExpiracion = Carbon::parse($this->fecha_fin);
            $dias = $fechaExpiracion->diffInDays($fechaEmision);
            $dias++;
            return $dias;
        } else {
            return 0;
        }
    }

    public function getDiasVacacionesAttribute()
    {
        $horas = $this->horas();

        if ($this->fecha_inicio) {
            $horas = $horas - NominaPeriodos::validar31($this->fecha_inicio, $this->fecha_fin);
        }

        return $horas;
    }

//    public function setValorHoraOrdinariaAttribute($value)
//    {
//        $this->attributes['valor_hora_ordinaria'] = round($value, 4);
//    }

}
