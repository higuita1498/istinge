<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Radicado;
class Servicio extends Model
{
    protected $table = "servicios";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'nombre', 'tiempo', 'empresa', 'estatus'
    ];

    public function usado()
    {
        return Radicado::where('servicio',$this->id)->count();
    }
    
    public function estatus(){
        return $this->estatus==1?'Activo':'Inactivo';
    }
}
