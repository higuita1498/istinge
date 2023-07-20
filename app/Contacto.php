<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TipoIdentificacion; use App\Vendedor;
use App\AsociadosContacto; use App\TipoEmpresa;
use App\Model\Inventario\ListaPrecios;
use App\Model\Ingresos\Factura;
use App\Model\Ingresos\Remision;
use App\Model\Ingresos\NotaCredito;
use App\Model\Gastos\NotaDedito;
use App\Model\Gastos\FacturaProveedores;
use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosFactura;
use DB; use Auth; use StdClass;
use App\Contrato;
use App\Model\Ingresos\Ingreso;
use App\Radicado;
use App\Oficina;

class Contacto extends Model
{
    protected $table = "contactos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa', 'nombre', 'apellido1', 'apellido2', 'nit', 'tip_iden', 'tipo_contacto', 'tipo_empresa', 'direccion', 'saldo_favor', 'ciudad', 'telefono1', 'telefono2', 'fax', 'celular', 'estrato', 'observaciones', 'email', 'status', 'created_at', 'updated_at' , 'vendedor', 'lista_precio','dv',
        'tipo_persona','responsableiva','plan','contrato', 'serial_onu', 'imgA', 'imgB', 'imgC', 'imgD', 'fecha_contrato', 'referencia_asignacion'
    ];

    protected $appends = ['usado', 'contract', 'details'];

    public function getUsadoAttribute(){
        return $this->usado();
    }

    public function getContractAttribute(){
        return $this->contract();
    }

    public function getDetailsAttribute(){
        return $this->details();
    }

    public function apellidos(){
        return $this->apellido1.' '.$this->apellido2;
    }

    public function lista_precios(){
        if (ListaPrecios::where('id',$this->lista_precio)->count() == 0) {
            return '';
        }
        return ListaPrecios::where('id',$this->lista_precio)->first()->nombre;
    }

    public function vendedor(){
        if (!$this->vendedor) {
            return '';
        }
        return Vendedor::where('id',$this->vendedor)->first()->nombre;
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

    public function telef($campo, $parte='tlfno'){
        $campo=$this->$campo;
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
                $codigo=explode('+', $prefijo);
                if (count($codigo)>1) {
                    $codigo=explode('+', $prefijo)[1];
                    $codigo=DB::table('prefijos_telefonicos')->where('phone_code', $codigo)->first();
                    return $prefijo;
                }
                return $partes;
            }
        }
        if ($parte<>'tlfno') {
            return Auth::user()->empresa()->codigo;
        }
        return $campo;
    }

    public function tipo_contacto(){
        $contacto='Cliente';
        if ($this->tipo_contacto==0) {
            $contacto='Cliente';
        }else if ($this->tipo_contacto==1) {
            $contacto='Proveedor';
        }else{
            $contacto='Cliente/Proveedor';
        }
        return $contacto;
    }

    public function tipo_empresa(){
        if (!$this->tipo_empresa) {
            return '';
        }
        return TipoEmpresa::where('id',$this->tipo_empresa)->first()->nombre;
    }

    public function usado(){
        $tmp        = 0;
        $tmp        += Factura::where('cliente', $this->id)->where('empresa', $this->empresa)->count();
        $tmp        += FacturaProveedores::where('proveedor', $this->id)->where('empresa', $this->empresa)->count();
        $tmp        += Contrato::where('client_id', $this->id)->where('status',1)->count();
        $tmp        += Radicado::where('cliente', $this->id)->count();
        return $tmp;
    }

    public function asociados($tipo=false){
        if ($tipo) {
            return AsociadosContacto::where('contacto', $this->id)->count();
        }
        return AsociadosContacto::where('contacto', $this->id)->get();
    }

    public function saldos(){
        $saldo=array('cobrar'=>0, 'cobrar_vencido'=>0, 'credito'=>0, 'por_pagar'=>0, 'debito'=>0, 'remisiones' => 0, 'remisiones_vencido' => 0, 'gasto' => 0);
        $facturas=Factura::where('cliente', $this->id)->where('tipo','!=', 2)->where('estatus', 1)->get();

        $remisiones = Remision::where('empresa',Auth::user()->empresa)->where('cliente',$this->id)->get();
        foreach ($remisiones as $remision) {
            $saldo['remisiones'] += $remision->porpagar();
            if($remision->vencimiento<date('Y-m-d')){
                $saldo['remisiones_vencido']+=$remision->porpagar();
            }
        }
        foreach ($facturas as $factura) {
           $saldo['cobrar']+=$factura->porpagar();
           if ($factura->vencimiento<date('Y-m-d')) {
                $saldo['cobrar_vencido']+=$factura->porpagar();
           }
        }
        $facturas=NotaCredito::where('cliente', $this->id)->get();
        foreach ($facturas as $factura) {
           $saldo['credito']+=$factura->por_aplicar();
        }

        $facturas=NotaDedito::where('proveedor', $this->id)->get();
        foreach ($facturas as $factura) {
           $saldo['debito']+=$factura->por_aplicar();
        }

        $gastos=Gastos::where('beneficiario','=',$this->id)->where('metodo_pago','=','7')->where('estatus','=','1')->get();
        foreach ($gastos as $gasto) {
          $detalles=GastosFactura::where('gasto',$gasto->id)->get();
          foreach ($detalles as $detalle) {
            $saldo['gasto']+=$detalle->pago;
          }
        }

        $facturas=FacturaProveedores::where('proveedor', $this->id)->whereIn('estatus', [1,5])
        ->where('tipo',1)
        ->where('empresa', Auth::user()->empresa)
        ->get();
        foreach ($facturas as $factura) {
           $saldo['por_pagar']+=$factura->porpagar();
        }

        return (object) $saldo;
    }

    public function tipo_venta(){
        /*............
        buscamos en la tabla de marcas para saber si el cliente vende por marcas
        ..............*/
        if (DB::table('prov_marca')->join('proveedor_marca','prov_marca.id_marca','=','proveedor_marca.id')->select('proveedor_marca.tipo_marca')->where('prov_marca.id_proveedor','=', $this->id)->count() > 0) {
            $tipomarca = DB::table('prov_marca')->join('proveedor_marca','prov_marca.id_marca','=','proveedor_marca.id')->select('proveedor_marca.tipo_marca')->where('prov_marca.id_proveedor','=', $this->id)->get();
            $vendev = ""; $vendem = ""; $vende = "";
            foreach ($tipomarca as $tipo) {
                if ($tipo->tipo_marca == 1) {
                    $vendev = 'Vehicular';
                }else if($tipo->tipo_marca == 2){
                    $vendem = 'Maquinaria';
                }
            }
            if ($vendev != "" && $vendem != "") {
                $vende = $vendev .  " - " . $vendem;
            } else if ($vendev != "" && $vendem == ""){
                $vende = $vendev;
            }else if($vendev == "" && $vendem != ""){
                $vende = $vendem;
            }
            return $vende;
        }else  return "no asociado";
    }

    public function pais(){
        if (DB::table('pais')->where('codigo',$this->fk_idpais)->count() > 0) {
            return DB::table('pais')->where('codigo',$this->fk_idpais)->first();
        }else{
            $pais = new stdClass;
            $pais->nombre = "";
            $pais->codigo_completo = "";
            return $pais;
        }
    }

    public function departamento(){
        if (DB::table('departamentos')->where('id',$this->fk_iddepartamento)->count() > 0) {
            return DB::table('departamentos')->where('id',$this->fk_iddepartamento)->first();
        }else{
            $depa = new stdClass;
            $depa->nombre = "";
            return $depa;
        }
    }

    public function municipio(){
        if (DB::table('municipios')->where('id',$this->fk_idmunicipio)->count() > 0) {
            return DB::table('municipios')->where('id',$this->fk_idmunicipio)->first();
        }else{
            $muni = new stdClass;
            $muni->nombre = "";
            $muni->codigo_completo = "";
            return $muni;
        }
    }

    public static function municipio_static($id){
        if (DB::table('municipios')->where('id',$id)->count() > 0) {
            return DB::table('municipios')->where('id',$id)->first();
        }else{
            $muni = new stdClass;
            $muni->nombre = "no seleccionado";
            $muni->codigo_completo = "null";
            return $muni;
        }
    }

    public static function departamento_static($id){
        if (DB::table('departamentos')->where('id',$id)->count() > 0) {
            return DB::table('departamentos')->where('id',$id)->first();
        }else{
            $depa = new stdClass;
            $depa->nombre = "no seleccionado";
            $depa->codigo = null;
            return $depa;
        }
    }

    public static function pais_static($id){
        if (DB::table('pais')->where('codigo',$id)->count() > 0) {
            return DB::table('pais')->where('codigo',$id)->first();
        }else{
            $pais = new stdClass;
            $pais->nombre = "no seleccionado";
            return $pais;
        }
    }

    public static function codigo_ident_static($id){
        return DB::table('tipos_identificacion')->where('id',$id)->select('codigo_dian')->first();
    }

    public function responsableIva(){
        return $this->responsableiva ? "Responsable de IVA" : 'No responsable de IVA';
    }

    public function tipo_persona(){

        return $this->tipo_persona ? 'Persona Juridica' : 'Persona Natural';

    }

    public function factura() {
        return (FacturaProveedores::where('proveedor', $this->id)->count() > 0) ;
    }

    public function facturas(){
        return (FacturaProveedores::where('proveedor', $this->id)->first());
    }

    public function getMunicipioNameAttribute()    {
        if (DB::table('municipios')->where('id',$this->fk_idmunicipio)->count() > 0) {
            return DB::table('municipios')->where('id',$this->fk_idmunicipio)->first()->nombre;
        }else
        {
            return "No seleccionado";
        }

    }

    public function getPaisNameAttribute()    {
        if (DB::table('pais')->where('codigo',$this->fk_idpais)->count() > 0) {
            return DB::table('pais')->where('codigo',$this->fk_idpais)->first()->nombre;
        }else
        {
            return "No seleccionado";
        }
    }

    public function getDepartamentoNameAttribute()    {

        if (DB::table('departamentos')->where('id',$this->fk_iddepartamento)->count() > 0) {
            return DB::table('departamentos')->where('id',$this->fk_iddepartamento)->first()->nombre;
        }else
        {
            return "No seleccionado";
        }

    }

    public function contrato($contractId = null)
    {
        if ($contractId) {
            return  Contrato::where('client_id', $this->id)->where('id', $contractId)->first();
        }

        return Contrato::where('client_id', $this->id)->where('status', 1)->first();
    }

    public function contract($details=false){
        $contrato = Contrato::where('client_id', $this->id)->where('status', 1)->first();
        if($contrato){
            if($details){
                return $contrato->ip;
            }
            return "<a href=" . route('contratos.show', $contrato->id) . " target='_blank'>".$contrato->nro."</div></a>";
        }
        return 'N/A';
    }

    public function details($contrato = null){
        if($contrato){
           $c = Contrato::where('client_id', $this->id)->where('id', $contrato)->first();
        }else{
           $c = Contrato::where('client_id', $this->id)->where('status', 1)->latest()->first();
        }

        return $c;
    }

    public function radicados(){
        $temp = 0;
        $temp += Radicado::leftjoin('contactos as c', 'radicados.identificacion', '=', 'c.nit')->where('radicados.empresa',Auth::user()->empresa)->where('radicados.identificacion', $this->nit)->count();
        return $temp;
    }

    public function oficina(){
        return Oficina::find($this->oficina);
    }

    public function asignacion($opt = false, $class = false){
        if($opt == 'firma'){
            if($class){
                return ($this->firma_isp) ? 'success' : 'danger';
            }
            return ($this->firma_isp) ? 'Firmado' : 'Pendiente por firmar';
        }

    }
}
