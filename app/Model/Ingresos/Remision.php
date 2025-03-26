<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Contacto; use App\Impuesto;
use App\Vendedor;
use App\Model\Ingresos\ItemsRemision;
use App\Model\Inventario\ListaPrecios;
use App\Model\Inventario\Bodega;
use Auth;
use DB;

class Remision extends Model
{
    protected $table = "remisiones";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'empresa', 'vendedor', 'documento', 'cliente', 'fecha', 'vencimiento', 'observaciones', 'estatus', 'notas', 'lista_precios', 'bodega', 'created_at', 'updated_at', 'lista_precios', 'bodega'
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

    public function parsear($valor){
        return number_format($valor, auth()->user()->empresa()->precision, auth()->user()->empresa()->sep_dec, (auth()->user()->empresa()->sep_dec == '.' ? ',' : '.'));
    }

    public function cliente(){
         return Contacto::where('id',$this->cliente)->first();
    }
    public function estatus($class=false, $index=false){

        if ($index) {
            if ($this->estatus==2) {
               return 'warning';
            }
            return $this->estatus==1?'primary':'success';
        }

        if ($class) {
            if ($this->estatus==2) {
               return 'Anulada';
            }
            return $this->estatus==1?'Abierta':'Cerrada';
        }

        if ($this->estatus==2) {
               return 'Anulada';
        }

        if ($this->estatus==1 && $this->pagado()==0) {
           return 'SIN FACTURAR';
        }
        else{
            return $this->estatus==1?'Abierta':'Cerrada';
        }
    }

    public function total(){
        $totales=array('total'=>0, 'subtotal'=>0, 'descuento'=>0, 'subsub'=>0, 'imp'=>Impuesto::where('empresa',Auth::user()->empresa)->orWhere('empresa', null)->Where('estado', 1)->get());
        $items=ItemsRemision::where('remision',$this->id)->get();
        $result=0; $desc=0; $impuesto=0;
        foreach ($items as $item) {
            $result=$item->precio*$item->cant;
            $totales['subtotal']+=$result;

            //SACAR EL DESCUENTO
            if ($item->desc>0) {
                $desc=($result*$item->desc)/100;
            }
            else{ $desc=0; }

            $totales['descuento']+=$desc;
            $result=$result-$desc;

            //SACAR EL IMPUESTO

            if ($item->impuesto>0) {
                foreach ($totales["imp"] as $key => $imp) {
                    if ($imp->id==$item->id_impuesto) {
                         $impuesto=($result*$imp->porcentaje)/100;
                        if (!isset($totales["imp"][$key]->total)) {
                            $totales["imp"][$key]->total=0;
                        }
                        $totales["imp"][$key]->total+=$impuesto;
                    }
                }
            }

        }
        $totales['total']=$totales['subsub']=$totales['subtotal']-$totales['descuento'];
        foreach ($totales["imp"] as $key => $imp) {
            $totales['total']+=$imp->total;
        }
        return (object) $totales;

    }


    public function vendedor(){
        if (!$this->vendedor) {
            return '';
        }
        return Vendedor::where('id',$this->vendedor)->first()->nombre;
    }

    public function pagado(){
        $ingresos=IngresosRemision::where('remision',$this->id)->whereRaw('(SELECT estatus FROM ingresosr where id = ingresosr_remisiones.ingreso) <> 2 ')->get();
        $total=0;
        foreach ($ingresos as $ingreso) {
            $total+=$ingreso->pago;
        }
        return $total;
    }


     public function porpagar(){
        return ($this->estatus == 2) ? 0 : $this->total()->total - $this->pagado();
    }

    public function pagos($cont=false){
        if ($cont) {
            return IngresosRemision::where('remision',$this->id)->count();
        }

        return IngresosRemision::where('remision',$this->id)->get();

    }
    public function lista_precios(){
        $lista=ListaPrecios::where('empresa',Auth::user()->empresa)->where('id', $this->lista_precios)->first();
        if (!$lista) { return ''; }
        return $lista->nombre();
    }
    public function bodega(){
        $bodega=Bodega::where('empresa',Auth::user()->empresa)->where('id', $this->bodega)->first();
        if (!$bodega) { return ''; }
        return $bodega->bodega;
    }

    public function getDateAttribute()
    {
        return [
            'primera' => Remision::where('empresa', Auth::user()->empresa)->whereNotNull('fecha')
                ->get()
                ->first()->fecha,
            'ultima' => Remision::where('empresa', Auth::user()->empresa)
                ->get()
                ->last()->fecha
        ];
    }

    public function itemsRemision()
    {
        return $this->hasMany(ItemsRemision::class,'remision','id');
    }

    public function itemsRemisionText()
    {
        $items = ItemsRemision::join('inventario as i','i.id','items_remision.producto')->where('remision',$this->id)->get();
        $text = "";
        $count = $items->count();
        $k = 0;
        $separator = "";
        foreach($items as $item){
            $k=$k+1;
            if($count != $k){$separator=" - ";}
            $text.= $item->producto . $separator;
        }
        return $text;
    }

}
