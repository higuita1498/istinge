<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Movimiento;
use App\Model\Gastos\GastosRecurrentes; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Auth;
class Banco extends Model
{
    protected $table = "bancos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nro', 'empresa', 'tipo_cta', 'nombre', 'nro_cta', 'saldo', 'fecha', 'descripcion', 'estatus', 'lectura'
    ];

    static function tipos(){
        $tipos = array(array('nro'=>1, 'nombre'=>'Banco'), array('nro'=>2, 'nombre'=>'Tarjeta de crédito'), array('nro'=>3, 'nombre'=>'Efectivo'), array('nro'=>4, 'nombre'=>'Punto de Venta'));
        $cont=0;
        $nuevos=array();
        foreach ($tipos as $tipo) {
            $cont=Banco::where('empresa', Auth::user()->empresa)->where('tipo_cta', $tipo["nro"])->where('estatus', 1)->count();
            if ($cont>0) {
               $nuevos[]=$tipo;
            }
        }
        return (object) $nuevos;
    }

    public function uso(){
        $cont=0;
        $cont+=Movimiento::where('empresa', Auth::user()->empresa)->where('banco', $this->id)->count();
        $cont+=GastosRecurrentes::where('empresa', Auth::user()->empresa)->where('cuenta', $this->id)->count();
        return $cont;
    }

    public function saldo(){
        $saldo= $this->saldo;
        $saldo +=Movimiento::where('empresa', Auth::user()->empresa)->where('banco', $this->id)->where('tipo', 1)->where('estatus', 1)->sum('saldo');
        $saldo -=Movimiento::where('empresa', Auth::user()->empresa)->where('banco', $this->id)->where('tipo', 2)->where('estatus', 1)->sum('saldo');
        return $saldo;
    }
    
    public function banco(){
        return Banco::find($this->banco);
    }
}