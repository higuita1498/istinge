<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormaPagoMedio extends Model
{
    protected $table = "forma_pago_medio";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'codigo', 'nombre', 'updated_at', 'created_at'
    ];

    public function formaPago(){
        return $this->hasOne('App\FormaPago','medio_pago_id');
    }
}
