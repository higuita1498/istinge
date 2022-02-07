<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Modulo; use App\Banco; 
use App\Model\Ingresos\Ingreso; 
use App\Model\Ingresos\IngresoR;
use App\Model\Gastos\Gastos;
use App\Model\Gastos\GastosRecurrentes;
use Auth;


class Movimiento extends Model
{
    protected $table = "movimientos";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'empresa', 'banco', 'contacto', 'tipo', 'saldo', 'fecha', 'estatus', 'conciliado', 'modulo', 'id_modulo', 'transferencia', 'descripcion', 'created_at', 'updated_at'    
    ];

    public function banco(){
        return Banco::where('id',$this->banco)->first();
    }

    public function modulo(){
        return Modulo::find($this->modulo);
    }

    public function show_url(){
        if ($this->modulo==1) {
            return route($this->modulo()->modulo.'.show', Ingreso::find($this->id_modulo)->id);
        }
        else if ($this->modulo==2) {
            return route($this->modulo()->modulo.'.show', IngresoR::find($this->id_modulo)->id);
        }
        else if ($this->modulo==3) {
            return route($this->modulo()->modulo.'.show', Gastos::find($this->id_modulo)->id);
        }
    }

    public function conciliado(){
        return $this->conciliado==0?'No':'Si';
    }

    public function estatus($class=false){
        if ($class) {
            return $this->estatus==2?'warning':'';
        }
        if ($this->estatus==2) {
            return 'Anulado';
        }
    }

    public function categoria(){
        if ($this->modulo==1) {
            return Ingreso::find($this->id_modulo)->detalle();
        }
        else if ($this->modulo==2) {
            return IngresoR::find($this->id_modulo)->detalle();
        }
        else if ($this->modulo==3) {
            return Gastos::find($this->id_modulo)->detalle();
        }
        if (GastosRecurrentes::find($this->id_modulo)) {
                return GastosRecurrentes::find($this->id_modulo)->detalle();    
            }

    }

    public function boton(){
        $boton='';
        if ($this->modulo>3 && $this->modulo<5) { return ''; }

        if ($this->modulo==1) { $modulo=Ingreso::find($this->id_modulo); }
        elseif ($this->modulo==2 || $this->modulo==4) { $modulo=IngresoR::find($this->id_modulo); }
        elseif ($this->modulo==3) { $modulo=Gastos::find($this->id_modulo); }
        else if($this->modulo==5) {
         $gasto=Gastos::where('empresa',Auth::user()->empresa)->where('nro',$this->id_modulo)->first(); 
         if ($gasto) {
             $boton.= '<a  href="'.route('pagos.show',$gasto->id).'" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
                <a href="'.route('pagos.edit',$gasto->id).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
                <a   href="'.route('pagos.imprimir',$gasto->id).'" target="_black" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
                 <form action="'.route('pagos.anular',$gasto->id).'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="anular-gasto'.$gasto->id.'">
                                        '.csrf_field().'
                 </form>
                 <form action="'.route('pagos.destroy', $gasto->id).'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-gasto'.$gasto->id.'">'.csrf_field().'<input name="_method" type="hidden" value="DELETE"></form>

            <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('."'eliminar-gasto".$gasto->id."', '¿Estas seguro que deseas eliminar el gasto?', 'Se borrara de forma permanente');".'"><i class="fas fa-times"></i></button>
                 ';
         }
         
            return $boton;   
    }


        $boton.='<a href="'.route($this->modulo()->modulo.'.show', $modulo->nro).'"   class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>';

        if($modulo->tipo!=3 && $modulo->tipo!=4){
            $boton.='<a href="'.route($this->modulo()->modulo.'.edit', $modulo->nro).'"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>';
        }
        $boton.='<a href="'.route($this->modulo()->modulo.'.imprimir', $modulo->nro).'" target="_blanck"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>';

        if($modulo->tipo!=3 && $modulo->tipo!=4){
            $boton.='<form action="'.route($this->modulo()->modulo.'.anular', $modulo->nro).'" method="post" class="delete_form" style="display: none;" id="anular-ingreso'.$modulo->id.'">'.csrf_field().' </form>';
            if($modulo->estatus==1){
                $boton.='<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('."'anular-ingreso'".$modulo->id."', '¿Está seguro de que desea anular el ingreso?', ' ');".'"><i class="fas fa-minus"></i></button>';
            }
            else{
                $boton.='<button class="btn btn-outline-success  btn-icons" type="button" title="Abrir" onclick="confirmar('."'anular-ingreso'".$modulo->id."', '¿Está seguro de que desea abrir el ingreso?', ' ');".'><i class="fas fa-unlock-alt"></i></button>';
            }   
        }
        if ($modulo->tipo!=3) {
           $boton.='<form action="'.route($this->modulo()->modulo.'.destroy', $modulo->nro).'" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-ingreso'.$modulo->id.'">'.csrf_field().'<input name="_method" type="hidden" value="DELETE"></form>

            <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('."'eliminar-ingreso".$modulo->id."', '¿Estas seguro que deseas eliminar el ingreso?', 'Se borrara de forma permanente');".'"><i class="fas fa-times"></i></button>';
        }
        return $boton;
    }
    
    public function getDateAttribute()
    {
        return [
            'primera' => Movimiento::where('empresa', Auth::user()->empresa)
                ->whereNotNull('fecha')
                ->get()
                ->first()
                ->fecha,
            'ultima' => Movimiento::where('empresa', Auth::user()->empresa)
                ->whereNotNull('fecha')
                ->get()
                ->last()
                ->fecha,
        ];
    }
    
    public function saldo($recaudo = false){
        $movimientos = Movimiento::where('fecha', $this->fecha)->where('banco', $this->banco)->get();
        $saldo = 0;
        foreach($movimientos as $movimiento){
            if($recaudo){
                $saldo += $movimiento->saldo;
            }else{
                $saldo += 900;
            }
        }
        return $saldo;
    }
}
