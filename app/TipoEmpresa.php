<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contacto;
class TipoEmpresa extends Model
{
    protected $table = "tipos_empresa";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'nombre', 'empresa'
    ];

    public function usado(){
        return Contacto::where('tipo_empresa',$this->id)->count();
    }
}