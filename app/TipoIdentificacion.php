<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoIdentificacion extends Model
{
    protected $table = "tipos_identificacion";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'identificacion'
    ];

    public function mini(){
        $identi=$this->identificacion;
        $identi=explode('(', $identi)[1];
        $identi=explode(')', $identi)[0];
        return $identi;
    }

    public function media(){
        $identi=$this->identificacion;
        $identi=explode('(', $identi)[0];
        return $identi;
    }
}
