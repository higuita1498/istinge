<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductoCuenta extends Model
{
    protected $table = "producto_cuentas";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cuenta_id', 'inventario_id', 'created_at', 'updated_at', 'tipo'
    ];

    public function nombreProductoServicio(){
 
        switch ($this->tipo) {
          case 1:
            return "Inventario";
            break;
  
          case 2:
            return "Costo";
            break;
  
          case 3:
            return "Venta";
            break;
  
          case 4:
            return "Devolución";
            break;
          
          default:
            return " ";
            break;
        }
      }

    public function puc(){
      return $this->belongsTo('App\Puc','cuenta_id');
    }

      //tipo: [1 = credito, 2 = débito] en facturas de venta
      //tipo: [1 = debito, 2 = credito] en notas crédito
      //modo 1 facturas de venta, modo 2 notas crédito
      public function autoretencionPuc($tipo, $modo=1){

        if($modo == 1){
          if($tipo == 1){
            $cuenta = Retencion::join('puc as p', 'p.id','=','retenciones.puc_venta')
            ->where('retenciones.id',$this->cuenta_id)
            ->select('p.*','retenciones.porcentaje')->first();
          }else if($tipo == 2){
              $cuenta = Retencion::join('puc as p', 'p.id','=','retenciones.puc_compra')
              ->where('retenciones.id',$this->cuenta_id)
              ->select('p.*','retenciones.porcentaje')->first();
          }
        }else{
          if($tipo == 1){
            $cuenta = Retencion::join('puc as p', 'p.id','=','retenciones.puc_compra')
            ->where('retenciones.id',$this->cuenta_id)
            ->select('p.*','retenciones.porcentaje')->first();
          }else if($tipo == 2){
            $cuenta = Retencion::join('puc as p', 'p.id','=','retenciones.puc_venta')
            ->where('retenciones.id',$this->cuenta_id)
            ->select('p.*','retenciones.porcentaje')->first();
          }
        }
        
        if(isset($cuenta)){
            return $cuenta;
        }
    }
}
