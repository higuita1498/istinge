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
use App\Radicado;
use App\Canal;

class VentasExternas extends Model
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
        'tipo_persona','responsableiva','plan','contrato', 'serial_onu', 'imgA', 'imgB', 'imgC', 'imgD', 'fecha_contrato'
    ];

    protected $appends = ['session'];

    public function getSessionAttribute(){
        return $this->getAllPermissions(Auth::user()->id);
    }

    public function getAllPermissions($id){
        if(Auth::user()->rol>=2){
            if (DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->count() > 0 ) {
                $permisos = DB::table('permisos_usuarios')->select('id_permiso')->where('id_usuario', $id)->get();
                foreach ($permisos as $key => $value) {
                    $_SESSION['permisos'][$permisos[$key]->id_permiso] = '1';
                }
                return $_SESSION['permisos'];
            }
            else return null;
        }
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

    public function nombre(){
        return $this->nombre.' '.$this->apellido1.' '.$this->apellido2;
    }

    public function vendedor_externa(){
        if($this->vendedor_externa){
            return Vendedor::where('id',$this->vendedor_externa)->first()->nombre;
        }
        return 'N/A';
    }

    public function canal_externa(){
        if($this->canal_externa){
            return Canal::where('id',$this->canal_externa)->first()->nombre;
        }
        return 'N/A';
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
}
