<?php

namespace App\Model\Nomina;

use Illuminate\Database\Eloquent\Model;
use App\TipoIdentificacion;
use DB;
use App\Model\Nomina\Nomina;
use App\Model\Nomina\NominaTerminoContrato;
use App\Traits\Funciones;
use Carbon\Carbon;
use Spatie\Activitylog\LogOptions;

class Persona extends Model
{
    use Funciones;


    protected $table = "ne_personas";

    protected $appends = ['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'];

    protected $fillable = [
        'nombre',
        'apellido',
        'fk_tipo_documento',
        'fk_empresa',
        'nro_documento',
        'correo',
        'nacimiento',
        'direccion',
        'nro_celular',
        'fk_clase_riesgo',
        'fk_salario_base',
        'valor',
        'subsidio',
        'dias_vacaciones',
        'dias_descanso',
        'fk_termino_contrato',
        'fecha_contratacion',
        'fk_tipo_contrato',
        'fk_metodo_pago',
        'fk_banco',
        'tipo_cuenta',
        'nro_cuenta',
        'fk_sede',
        'fk_area',
        'fk_cargo',
        'fk_centro_costo',
        'fk_eps',
        'fk_fondo_cesantia',
        'fk_fondo_pension',
        'fk_iddepartamento',
        'fk_idmunicipio',
        'status',
        'created_at',
        'updated_at',
        'is_liquidado'

    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }


    public function getValorAttribute($value)
    {
        $valor = doubleval($value);
        return round($valor, 4);
    }

    public function nombre()
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    public function tipo_documento($tipo = 'mini')
    {
        if ($tipo == 'completa') {
            return TipoIdentificacion::where('id', $this->fk_tipo_documento)->first()->identificacion;
        }

        if ($tipo == 'codigo') {
            return TipoIdentificacion::where('id', $this->fk_tipo_documento)->first()->codigo_dian;
        }


        $identi = TipoIdentificacion::where('id', $this->fk_tipo_documento)->first()->identificacion ?? '';
        if($identi){
            $exp = explode('(', $identi);
            if(isset($exp[1])){
                $identi = explode('(', $identi)[1];
            }
            if(isset($exp[0])){
                $identi = explode(')', $identi)[0];
            }
        }
        return $identi;
    }


    public function cargo()
    {
        return DB::table('ne_cargos')->where('id', $this->fk_cargo)->first();
    }

    public function area()
    {
        return DB::table('ne_areas')->where('id', $this->fk_area)->first();
    }

    public function centro_costo()
    {
        return DB::table('ne_centro_costos')->where('id', $this->fk_centro_costo)->first();
    }

    public function clase_riesgo()
    {
        if ($this->fk_clase_riesgo == null) {
            return '';
        }
        return DB::table('ne_clase_riesgos')->where('id', $this->fk_clase_riesgo)->first()->nombre;
    }

    public function departamento()
    {
        if (DB::table('departamentos')->where('id', $this->fk_iddepartamento)->count() > 0) {
            return DB::table('departamentos')->where('id', $this->fk_iddepartamento)->first();
        } else {
            $depa = new stdClass();
            $depa->nombre = "";
            return $depa;
        }
    }


    public function municipio()
    {
        if (DB::table('municipios')->where('id', $this->fk_idmunicipio)->count() > 0) {
            return DB::table('municipios')->where('id', $this->fk_idmunicipio)->first();
        } else {
            $muni = new stdClass();
            $muni->nombre = "";
            $muni->codigo_completo = "";
            return $muni;
        }
    }

    public function eps()
    {
        return DB::table('ne_eps')->where('id', $this->fk_eps)->first();
    }

    public function fondo_cesantias()
    {
        return DB::table('ne_fondo_cesantias')->where('id', $this->fk_fondo_cesantias)->first();
    }

    public function fondo_pensiones()
    {
        return DB::table('ne_fondo_pensiones')->where('id', $this->fk_fondo_pensiones)->first();
    }

    public function salario_base()
    {
        return DB::table('ne_salario_base')->where('id', $this->fk_salario_base)->first();
    }

    public function sede()
    {
        return DB::table('ne_sede_trabajo')->where('id', $this->fk_sede)->first();
    }

    public function terminoContrato()
    {
        return $this->belongsTo(NominaTerminoContrato::class, 'fk_termino_contrato');
    }

    public function tipo_contrato()
    {
        return DB::table('ne_tipo_contrato')->where('id', $this->fk_tipo_contrato)->first();
    }

    public function nomina_tipo_contrato()
    {
        return $this->belongsTo(NominaTipoContrato::class, 'fk_tipo_contrato');
    }


    public function metodo_pago()
    {
        return DB::table('metodos_pago')->where('id', $this->fk_metodo_pago)->first()->metodo;
    }

    public function metodo_pago_codigo()
    {
        return DB::table('metodos_pago')->where('id', $this->fk_metodo_pago)->first()->codigo;
    }

    public function banco()
    {
        return DB::table('ne_bancos')->where('id', $this->fk_banco)->first()->nombre;
    }

    public function estatus()
    {
        return ($this->tipo_contrato == 0) ? 'Deshabilitado' : 'Habilitado';
    }

    public function subsidio()
    {
        return ($this->subsidio == 0) ? 'No' : 'Si';
    }

    public function tipo_cuenta()
    {
        return ($this->tipo_cuenta == 1) ? 'Ahorro' : 'Corriente';
    }

    public function nominas()
    {
        return $this->hasMany(Nomina::class, 'fk_idpersona', 'id');
    }

    public function uso()
    {
        $uso = Nomina::where('fk_idpersona', $this->id)->where('emitida', 1)->exists();
        return $uso;
    }

    public function status($class = false)
    {
        if ($class) {
            return $this->status == 0 ? 'danger' : 'success';
        }

        return $this->status == 0 ? 'Deshabilitado' : 'Habilitado';
    }

    public function empresa()
    {
        return $this->belongsTo('App\Empresa', 'fk_empresa');
    }


    /**=================================
     *           ACCESSORS
     *================================**/


    public function getPrimerNombreAttribute()
    {
        $primerNombre = $this->nombre;
        $nombres = explode(" ", $this->nombre);
        if (count($nombres) == 2) {
            $primerNombre = $nombres[0];
        }
        return $primerNombre;
    }


    public function getSegundoNombreAttribute()
    {
        $segundoNombre = " ";
        $nombres = explode(" ", $this->nombre);
        if (count($nombres) == 2) {
            $segundoNombre = $nombres[1];
        }
        return $segundoNombre;
    }


    public function getPrimerApellidoAttribute()
    {
        $primerApellido = $this->apellido;
        $apellidos = explode(" ", $this->apellido);
        if (count($apellidos) == 2) {
            $primerApellido = $apellidos[0];
        }
        return $primerApellido;
    }


    public function getSegundoApellidoAttribute()
    {
        $segundoApellido = " ";
        $apellidos = explode(" ", $this->apellido);
        if (count($apellidos) == 2) {
            $segundoApellido = $apellidos[1];
        }
        return $segundoApellido;
    }

    public function ultimaLiquidacion(){
        return ContratoPersona::where('fk_idpersona', $this->id)->latest()->first();
    }

    public function contratos(){
        return $this->hasMany('App\Model\Nomina\ContratoPersona', 'fk_idpersona');
    }

    public function refrescarUltimaNomina(){

        $year = date("Y", strtotime(Carbon::now()));
        $mes  = date("m", strtotime(Carbon::now()));

        $periodoActual = Nomina::where('fk_idempresa', $this->fk_empresa)
            ->where('year', $year)
            ->where('periodo',$mes)
            ->where('fk_idpersona', $this->id)
            ->whereIn('emitida', [2,4])
            ->orderBy(
                'periodo',
                'DESC'
            )->first();

        if ($periodoActual) {
            foreach($periodoActual->nominaperiodos as $nominaPeriodo){
                $nominaPeriodo->editValorTotal();
            }
        }
    }

}
