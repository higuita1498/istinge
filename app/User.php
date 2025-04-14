<?php

namespace App;

use App\Model\Ingresos\Factura;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable; use App\Empresa;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth; use DB;
use App\Contrato;
use App\Oficina;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = "usuarios";
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombres', 'nro',  'cedula', 'observaciones','image', 'email', 'telefono', 'empresa', 'username', 'password', 'user_status','rol','saldo','created_at', 'updated_at', 'location'
    ];

    protected $appends = ['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function roles(){
         return $this->belongsTo('App\Roles', 'rol');

    }

    public function empresa(){
        return Empresa::where('id',$this->empresa)->first();

    }

    public function tipo_fac(){
        return Empresa::where('id',$this->empresa)->first()->tipo_fac;
    }

    public function estatus($clase=false){
        if ($clase) {
           return $this->user_status==1?'text-success':'text-danger';
        }
        else{
           return $this->user_status==1?'Activo':'Inactivo';

        }
    }

    public function suscripcion()
    {
        $suscripcion = Suscripcion::where('id_empresa',Auth::user()->empresa)->get()->first();
        return $suscripcion->ilimitado;
    }

    public function modo_lectura(){
        $suscripcion = Suscripcion::where('id_empresa',Auth::user()->empresa)->get()->first();
        if($suscripcion->ilimitado){
            return false;
        }

        if (Auth::user()->rol >= 2){
            return ($suscripcion->fec_corte < date('Y-m-d') || ($this->contratos()));
            return ($suscripcion->fec_corte < date('Y-m-d')) || ($this->facturasHechas()) || ($this->ingresosMaximos() || ($this->rechazado())) ;
        }

    }

    public function contador_responsabilidades(){
        return DB::table('empresa_responsabilidad')->where('id_empresa',Auth::user()->empresa)->count();
    }

    public function facturasHechas(){

        $suscripcion    = SuscripcionPago::where('id_empresa', Auth::user()->empresa)->get()->last();

        if (!$suscripcion){
            return (Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last())->facturasHechas();
        }

        return $suscripcion->facturasHechas();

    }

    public function ingresosMaximos(){

        $suscripcion        = SuscripcionPago::where('id_empresa', Auth::user()->empresa)->get()->last();
        $tmpSuscripcion     = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        if ($tmpSuscripcion->ilimitado){
            return false;
        }

        if (!$suscripcion){
            $ingresos   = (Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last())->ingresos()['ingresos'];
            if ($this->unlimited()){
                return false;
            }

            if(is_array($this->checkPlan()))
                return !$this->verifyLimitsPersonalPlan($ingresos, 'ingresos');

            return ($ingresos > 5000000) ? true : false;
        }

        return $suscripcion->ingresosLimit();

    }

    public function unlimited(){

        $suscripcionFree    = Suscripcion::where('id_empresa', Auth::user()->empresa)->get()->last();
        $suscripcionFree    = Carbon::parse($suscripcionFree->created_at);

        return Carbon::now()->diffInMonths($suscripcionFree) >= 1 ? false : true;
    }

    public function rechazado(){

        $suscripcion        = SuscripcionPago::where('id_empresa', Auth::user()->empresa)->get()->last();
        if(!$suscripcion){
            return false;
        }

        return $suscripcion->rechazado();
    }


    /**
     * @param String $action
     * @return bool|null
     */
    public function canDo(String $action)
    {
        $action         = DB::table('permisos_botones')->where('nombre_permiso', 'like', "%$action%")
                            ->get();
        if (count($action) == 0)
            return null;

        $action         = $action->last()->id;
        $can            = DB::table('permisos_usuarios')->where('id_usuario', $this->id)
                                ->where('id_permiso', $action)->get()->last();
        return $can != null;
    }

    /**
     * Verifica los limites del plan personalizado
     * -field: es el campo a comprobar (ej: facturas o ingresos)
     * -indexAssoc: es el campo a consultar dentro del plan personalizado, debe ser String (ej: 'facturas')
     * @param $field
     * @param String $indexAssoc
     * @return bool
     */
    public function verifyLimitsPersonalPlan($field, String $indexAssoc)
    {
        $checkPlan = $this->checkPlan();
        if(is_array($checkPlan)){
            return ($checkPlan['pago']) ? (($field > $checkPlan[$indexAssoc]) ? false : true) : false;
        }
    }

    /**
     * Verifica si la empresa posee un plan personalizado.
     * En caso de que sea asi, devuelve los datos relacionados al mismo.
     * @return array|bool
     */
    private function checkPlan()
    {
        $empresa = Empresa::find(Auth::user()->empresa);
        $plan = ($empresa->p_personalizado != 0) ? DB::table('planes_personalizados')->find($empresa->p_personalizado) : '' ;
        return ($empresa->p_personalizado == 0) ? true : array(
            'nombre' => $plan->nombre,
            'facturas' => $plan->facturas,
            'ingresos' => $plan->ingresos,
            'pago' => $this->payPersonalPlan()
        );

    }

    /**
     * Verificaci��n del pago de la suscripcion personalizada
     * @return bool
     */
    private function payPersonalPlan()
    {
        $empresa = Empresa::find(Auth::user()->empresa);
        $suscripcion = SuscripcionPago::where('id_empresa', $empresa)
            ->where('personalizado', 1)
            ->get();
        return count($suscripcion) > 0 ? true : false;
    }

    public function usado()
    {
        return Radicado::where('tecnico',$this->id)->orWhere('responsable',$this->id)->count();
    }

    public function rol($clase=false){
        if ($clase) {
            if($this->rol==2){
                return 'text-warning';
            }elseif($this->rol==3){
                return 'text-primary';
            }elseif($this->rol==4){
                return 'text-success';
            }elseif($this->rol==5){
                return 'text-danger';
            }elseif($this->rol>6){
                return 'text-info';
            }
        }else{
            if($this->rol){
                return Roles::find($this->rol)->rol;
            }
            else return "N/A";
        }
    }

    public function empresaObj()
    {
        return $this->belongsTo('App\Empresa', 'empresa');
    }

     /**
     * @return bool $fechaCorteSuscripcion
     * @return bool $cantidadPersonasValidas
     */
    public function modoLecturaNomina()
    {
        if(1==1){
            return [
                'success' => false,
                'message' => ''
            ];
        }else{
            $usuario = auth()->user();
            $suscripcion = SuscripcionNomina::where('id_empresa',  $usuario->empresa)->first();

            if (isset($suscripcion) && $usuario->rol >= 2) {
                switch ($suscripcion) {
                    case $suscripcion->fec_corte < date('Y-m-d'):
                        return [
                            'success' => true,
                            'message' => 'Tu plan actual ya ha finalizado'
                        ];
                        break;
                    case $this->personal():
                        return [
                            'success' => true,
                            'message' => 'Ha alcanzado el límite de personas para el plan suscrito'
                        ];
                        break;
                    default:
                        return [
                            'success' => false,
                            'message' => ''
                        ];
                        break;
                }
            }

            return [
                'success' => false,
                'message' => 'No existe ua suscripción activa'
            ];
        }

    }

      /**
    *
    * Método que recupera la cantidad de personas que se pueden obtener segun el plan escogido por la empresa.
    *
    */
    public function personal()
    {
        return (SuscripcionNomina::where('id_empresa', Auth::user()->empresa)->get()->last())->personal();
    }

    public function contratos($nro = false){
        $suscripcion = Suscripcion::where('id_empresa',Auth::user()->empresa)->get()->first();
        if($suscripcion->ilimitado == 0){
            return false;
        }
        $contratos = Contrato::where('empresa', Auth::user()->empresa)->where('status', 1)->count();
        if($nro){
            return ($contratos > 1500) ? true : false;
        }
        return $contratos;
    }

    public function oficina()
    {
        return Oficina::find($this->oficina);
    }

    public function cuentas(){

        $cuentas = [];

        if($this->cuenta){
            $cuentas[] = $this->cuenta;
        }

        if($this->cuenta_1){
            $cuentas[] = $this->cuenta_1;
        }

        if($this->cuenta_2){
            $cuentas[] = $this->cuenta_2;
        }

        if($this->cuenta_3){
            $cuentas[] = $this->cuenta_3;
        }

        if($this->cuenta_4){
            $cuentas[] = $this->cuenta_4;
        }

        if($this->cuenta_5){
            $cuentas[] = $this->cuenta_5;
        }

        return $cuentas;
    }

    public function modo_lecturaNomina()
    {
        return false;
    }

    public function servidores(){
        return $this->BelongsToMany('App\Mikrotik', 'usuario_servidor', 'usuario_id', 'servidor_id');
    }


}
