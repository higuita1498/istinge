<?php

namespace App;

use App\Model\Gastos\NotaDedito;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\NotaCredito;
use App\Model\Nomina\NominaConfiguracionCalculos;
use Illuminate\Database\Eloquent\Model;
use App\User; use App\TipoIdentificacion;
use DB; use StdClass;
use App\Responsabilidad;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth as Auth;
use App\Instancia;

class Empresa extends Model
{
    protected $table = "empresas";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'logo', 'nit', 'direccion', 'telefono', 'email', 'tip_iden', 'tipo_persona', 'status', 'terminos_cond', 'notas_fact', 'edo_cuenta_fact', 'moneda', 'created_at', 'updated_at', 'web', 'precision', 'sep_dec', 'carrito', 'img_default', 'rol', 'dv', 'fk_idpais', 'fk_iddepartamento', 'fk_idmunicipio', 'cod_postal', 'json_test_creditnote', 'json_test_debitnote', 'json_test', 'tipo_fac', 'color', 'pageLength'
    ];

    public function usuario(){
        $usuario= User::where('email',auth()->user()->email)->where('empresa',auth()->user()->id)->first();
        if(!$usuario){
            $usuario= User::where('empresa',$this->id)->whereIn('rol',[2,45])->OrderBy('id', 'asc')->first();

        }
        return $usuario;
    }
    public function tip_iden($tipo='completa'){
        if ($tipo=='completa') {
            return TipoIdentificacion::where('id',$this->tip_iden)->first()->identificacion;
        }
        $identi=TipoIdentificacion::where('id',$this->tip_iden)->first()->identificacion;
        $identi=explode('(', $identi)[1];
        $identi=explode(')', $identi)[0];
        return $identi;

    }

    public function telef($parte='tlfno'){
        $campo=$this->telefono;
        $partes=explode(' ', $campo);
        if (count($partes)>1) {
            $prefijo=$partes[0];
            $campo='';
            foreach ($partes as $key => $value) {
                if ($key>0) {
                    $campo.=$value;
                }
            }

            if ($parte<>'tlfno') {

                $codigo=explode('+', $prefijo)[1];
                $codigo=DB::table('prefijos_telefonicos')->where('phone_code', $codigo)->first();
                if (!$codigo) {
                    $prefijo=Auth::user()->empresa()->codigo;
                }
                return $prefijo;
            }
        }

        return $campo;
    }

    public function whatsapp($parte = 'tlfno')
    {
        $campo = $this->whatsapp;
        $partes = explode(' ', $campo);
        if (count($partes) > 1) {
            $prefijo = $partes[0];
            $campo = '';
            foreach ($partes as $key => $value) {
                if ($key > 0) {
                    $campo .= $value;
                }
            }

            if ($parte <> 'tlfno') {
                $codigo = explode('+', $prefijo)[1];
                $codigo = DB::table('prefijos_telefonicos')->where('phone_code', $codigo)->first();
                if (!$codigo) {
                    $prefijo = Auth::user()->empresaObj->codigo;
                }
                return $prefijo;
            }
        }

        return $campo;
    }

    public function soporte($parte = 'tlfno'){
        $campo = $this->soporte;
        $partes = explode(' ', $campo);

        if (count($partes) > 1) {
            $prefijo = $partes[0];
            $campo = '';
            foreach ($partes as $key => $value) {
                if ($key > 0) {
                    $campo .= $value;
                }
            }

            if ($parte <> 'tlfno') {
                $explodedPrefijo = explode('+', $prefijo);
                if (count($explodedPrefijo) >= 2) {
               // $codigo = explode('+', $prefijo)[1];
                $codigo = $explodedPrefijo[1];
                $codigo = DB::table('prefijos_telefonicos')->where('phone_code', $codigo)->first();
                if (!$codigo) {
                    $prefijo = Auth::user()->empresaObj->codigo;
                }
            } else {
            }
                return $prefijo;
            }
        }

        return $campo;
    }

    public function ventas($parte = 'tlfno'){
        $campo = $this->ventas;
        $partes = explode(' ', $campo);
        if (count($partes) > 1) {
            $prefijo = $partes[0];
            $campo = '';
            foreach ($partes as $key => $value) {
                if ($key > 0) {
                    $campo .= $value;
                }
            }
            if ($parte <> 'tlfno') {
                $explodedPrefijo = explode('+', $prefijo);
                if (count($explodedPrefijo) >= 2) {
               // $codigo = explode('+', $prefijo)[1];
                $codigo = $explodedPrefijo[1];
                $codigo = DB::table('prefijos_telefonicos')->where('phone_code', $codigo)->first();
                if (!$codigo) {
                    $prefijo = Auth::user()->empresaObj->codigo;
                }
            } else {
            }
            return $prefijo;
            // if ($parte <> 'tlfno') {
            //     $codigo = explode('+', $prefijo)[1];
            //     $codigo = DB::table('prefijos_telefonicos')->where('phone_code', $codigo)->first();
            //     if (!$codigo) {
            //         $prefijo = Auth::user()->empresaObj->codigo;
            //     }
            //     return $prefijo;
            // }
        }
    }

        return $campo;
    }

    public function finanzas($parte = 'tlfno'){
        $campo = $this->finanzas;
        $partes = explode(' ', $campo);
        if (count($partes) > 1) {
            $prefijo = $partes[0];
            $campo = '';
            foreach ($partes as $key => $value) {
                if ($key > 0) {
                    $campo .= $value;
                }
            }
            if ($parte <> 'tlfno') {
                $explodedPrefijo = explode('+', $prefijo);
                if (count($explodedPrefijo) >= 2) {
               // $codigo = explode('+', $prefijo)[1];
                $codigo = $explodedPrefijo[1];
                $codigo = DB::table('prefijos_telefonicos')->where('phone_code', $codigo)->first();
                if (!$codigo) {
                    $prefijo = Auth::user()->empresaObj->codigo;
                }
            } else {
            }
            return $prefijo;
            // if ($parte <> 'tlfno') {
            //     $codigo = explode('+', $prefijo)[1];
            //     $codigo = DB::table('prefijos_telefonicos')->where('phone_code', $codigo)->first();
            //     if (!$codigo) {
            //         $prefijo = Auth::user()->empresaObj->codigo;
            //     }
            //     return $prefijo;
            // }
        }
            // if ($parte <> 'tlfno') {
            //     $codigo = explode('+', $prefijo)[1];
            //     $codigo = DB::table('prefijos_telefonicos')->where('phone_code', $codigo)->first();
            //     if (!$codigo) {
            //         $prefijo = Auth::user()->empresaObj->codigo;
            //     }
            //     return $prefijo;
            // }
        }

        return $campo;
    }

    public function tipo_persona(){
        return $this->tipo_persona=='n'?'Natural':'Jurídica';

    }


    public function municipio()
{
    if (DB::table('municipios')->where('id',$this->fk_idmunicipio)->count() > 0) {
        return DB::table('municipios')->where('id',$this->fk_idmunicipio)->first();
    }else
    {
        $muni = new stdClass;
        $muni->nombre = "no seleccionado";
        $muni->codigo_completo = null;
        return $muni;
    }

}

public function departamento()
{
  if (DB::table('departamentos')->where('id',$this->fk_iddepartamento)->count() > 0) {
    return DB::table('departamentos')->where('id',$this->fk_iddepartamento)->first();
}else
{
    $depa = new stdClass;
    $depa->nombre = "no seleccionado";
    $depa->codigo = null;
    return $depa;
}
}

public function pais()
{
  if (DB::table('pais')->where('codigo',$this->fk_idpais)->count() > 0) {
    return DB::table('pais')->where('codigo',$this->fk_idpais)->first();
}else
{
    $pais = new stdClass;
    $pais->nombre = "no seleccionado";
    return $pais;
}
}

public function firstuuidfact(){
    $empresa = Empresa::find(Auth::user()->empresa);
    $json_fact = json_decode($empresa->json_test,true);
    for($i = 0; $i < $json_fact['validos']; $i++) {
      return  $json_fact['data_validos'][$i]['uuid'];
  }
}

  /**
     * Obtiene el numero total de emiciones de una empresa (FV + NC + ND)
     * @return int
  */
public function totalEmissions(){
    $facturas = Factura::where('empresa',$this->id)->where('emitida',1)->count();
    $notasc   = NotaCredito::where('empresa',$this->id)->where('emitida',1)->count();
    $notasd   = NotaDedito::where('empresa',$this->id)->where('emitida',1)->count();

    return $total = $facturas + $notasc + $notasd;
}


    /**
     * Obtiene el nombre del plan contratado
     * @return string
     */
    public function getPlanAttribute()
    {
        $suscripcionPago    = SuscripcionPago::where('id_empresa', $this->id)->get()->first();
        $suscripcion        = Suscripcion::where('id_empresa', $this->id)->get()->first();
        if($suscripcion->ilimitado){
            return 'Ilimitado';
        }
        return $suscripcionPago ? $suscripcionPago->plan() : 'Gratuito';
    }

     /**
     * Verifica si la cuenta dispone de dos planes vigentes
     * @return bool
     */
    public function multSubscription()
    {
        $count = SuscripcionPago::where('id_empresa', Auth::user()->empresa)->count();
        if($count > 1){
            $subscriptions = SuscripcionPago::where('id_empresa', Auth::user()->empresa)
                ->where('estado', 1)
                ->orderBy('id', 'desc')->get()->take(2);
            return ($subscriptions->first()->valid && $subscriptions->last()->valid) ? $subscriptions : false;
        }
        return false;
    }

    /**
     * Verifica si la cuenta dispone de varias suscripciones vigentes.
     * Retornando falso cuando no sea asi.
     * @return bool|\Illuminate\Database\Eloquent\Collection
     */
    public function subscriptions()
    {
        $empresa = Auth::user()->empresa;
        $approvedSubscriptions = SuscripcionPago::where('id_empresa', $empresa)
            ->where('estado', 1)->orderBy('id', 'desc')->get();

        if (count($approvedSubscriptions) > 1){
            $subscription = Suscripcion::where('id_empresa', $empresa)->get()->last();
            $initialDate = Carbon::parse($subscription->fec_inicio);
            $tmpSubs = array();
            $approvedSubscriptions = SuscripcionPago::where('id_empresa', $empresa)
                ->where('estado', 1)->orderBy('id', 'desc')->get();
            foreach ($approvedSubscriptions as $approvedsubscription) {
                if ($approvedsubscription->expiration->gte($initialDate) && $approvedsubscription->valid)
                    $tmpSubs[] = $approvedsubscription;
            }
            return (count($tmpSubs) > 1) ? $this->newCollection($tmpSubs) : false;
        }
        return false;
    }


     public function nominaConfiguracionCalculos()
    {
        return $this->hasMany('App\Model\Nomina\NominaConfiguracionCalculos', 'fk_idempresa');
    }

    public function getSalarioMinimo()
    {
        $salario = NominaConfiguracionCalculos::where('fk_idempresa', $this->id)->where('nro', 4)->first();
        return $salario ? floatval($salario->valor) : 1160000;
    }

    public function suscripcion(){
        return Suscripcion::where('id_empresa',Auth::user()->empresa)->get()->first();
    }

    public function responsabilidades()
    {
        return $this->belongsToMany(Responsabilidad::class, 'empresa_responsabilidad', 'id_empresa', 'id_responsabilidad');
    }

        //Validamos que la empresa tenga alguna de las responsabilidades dictadas en al articulo 042 del 2020
        public function validateResp()
        {
            $resp = 0;

            $empresa  = Empresa::select('id')
                ->with('responsabilidades')
                ->where('id', auth()->user()->empresa)
                ->first();

            $listaResponsabilidadesDian = [5, 7, 12, 20, 29];

            foreach ($empresa->responsabilidades as $responsabilidad) {

                if (in_array($responsabilidad->pivot->id_responsabilidad, $listaResponsabilidadesDian)) {
                    $resp = 1;
                }
            }

            return $resp;
        }

        public function isWhatsapp(){

            if(DB::table('instancia')->where('status',1)->first()){
                return 1;
            }else return 0;

        }

}
