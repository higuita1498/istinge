<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anticipo extends Model
{
    protected $table = "anticipo";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'codigo', 'nombre', 'relacion', 'cuenta_id', 'medio_pago_id', 'updated_at', 'created_at'
    ];


    //Formateo de relacion.
    public function relacion(){
          if($this->relacion == 1){
            return  "Solo cartera";
        }else if($this->relacion == 2){
            return  "Solo proveedores";
        }else if($this->relacion == 3){
            return  "Cartera / Proveedores";
        }
    }

    public function categoria(){
        return $this->belongsTo(Puc::class,'cuenta_id');
    }

    public function formaPagoMedio(){
        return $this->belongsTo('App\FormaPagoMedio','medio_pago_id');
    }
}
