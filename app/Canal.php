<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contrato;

class Canal extends Model
{
    protected $table = "canales";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'empresa', 'nombre', 'observaciones'
    ];

    public function usado(){
        return Contrato::where('canal', $this->id)->count();
    }

    public function status($class=false){
        if($class){
            return $this->status == '1' ? 'success' : 'danger';
        }
        return $this->status == '1' ? 'Habilitado' : 'Deshabilitado';
    }
}