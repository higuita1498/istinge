<?php

namespace App\Model\Gastos;
use App\Contacto; 
use App\Banco;
use App\Impuesto;
use App\Model\Ingresos\NotaCredito;
use App\Retencion;
use App\Funcion;

use App\Model\Gastos\GastosFactura; 
use App\Model\Gastos\GastosRecurrentesCategoria; 
use App\Model\Gastos\GastosRetenciones;
use DateTime;
use function GuzzleHttp\Psr7\str;
use Illuminate\Database\Eloquent\Model;

use Auth; use DB;
use App\TiposGastos;

class GastosRecurrentes extends Model
{
    protected $table = "gastos_recurrentes";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'empresa', 'beneficiario', 'cuenta', 'metodo_pago', 'fecha', 'observaciones', 'notas', 'tipo', 'created_at', 'updated_at', 'frecuencia', 'proxima','estado_pago', 'estado'
    ];

    public function beneficiario(){
         return Contacto::where('id',$this->beneficiario)->first();
    }

    public function tipo(){
         return TiposGastos::find($this->tipo_gasto);
    }

    public function metodo_pago($id=false){
       if ($this->metodo_pago) {
            $metodo =DB::table('metodos_pago')->where('id',$this->metodo_pago)->first();
            return $id ? $metodo->id : $metodo->metodo;
       }
    }

    public function detalle($pdf=false){dd
            $gastos=GastosRecurrentesCategoria::where('gasto_recurrente', $this->id)->select('categoria')->distinct()->get();
            $Factura='';
            foreach ($gastos as $gasto) {
                $Factura.=" ".($gasto->categoria(true)).",";
            }
            return 'Categorías:'.substr($Factura, 0, -1);
    }

    public function cuenta(){
        return Banco::where('id',$this->cuenta)->first();
    }


    public function total(){
        $totales=array('total'=>0, 'ivas'=>0, 'subtotal'=>0, 'totalreten'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get(), 'reten'=>array());
        $totales["reten"]=Retencion::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get();
        
        $items=GastosRecurrentesCategoria::where('gasto_recurrente',$this->id)->get();
        $result=0; $desc=0; $impuesto=0;
        foreach ($items as $item) {
            $result=$item->valor*$item->cant;
            $totales['subtotal']+=$result;
            //SACAR EL IMPUESTO
            if ($item->impuesto>0) {
                foreach ($totales["imp"] as $key => $imp) {
                    if ($imp->id==$item->id_impuesto) {
                         $impuesto=($result*$imp->porcentaje)/100;
                        if (!isset($totales["imp"][$key]->total)) {
                            $totales["imp"][$key]->total=0;
                        }

                        if ($imp->tipo==1) {
                            $totales["ivas"]+=$impuesto;
                        }
                        $totales["imp"][$key]->total+=$impuesto;
                    }
                }
            }
        }
        $totales['total']=$totales['subtotal'];
        foreach ($totales["imp"] as $key => $imp) {
            $totales['total']+=$imp->total;
        }           

        return (object) $totales;

    }

    public function estatus()
    {
        $ingresos = Gastos::where('empresa', Auth::user()->empresa)
            ->where('nro', $this->id)
            ->where('tipo', 5)
            ->get()
            ->last();

        return $ingresos ? $this->calculateStatus($ingresos) : false;
    }

    private function calculateStatus($ingresos)
    {

        if($ingresos->estatus == 2){
            return false;
        }
        $proxima = strtotime($this->proxima);
        $actual = strtotime(date('Y-m-d'));

        return $actual >= $proxima ? false : true;

    }

    public function proximo()
    {

        $actual     = new DateTime(date("Y-m-d"));
        $proxima    = new DateTime($this->proxima);

        return $actual->diff($proxima)->days <= 3;

    }
    public function estado($class=false){
        if ($class) {
            return ($this->estado == 0) ? 'danger' : 'success';
        }
        return ($this->estado == 0) ? 'SIN APROBAR' : 'APROBADO';
    }

    public function uso(){
        $cont=0;
        $cont+=GastosRecurrentesCategoria::where('gasto_recurrente', $this->id)->count();
        return $cont;
    }


}
