<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use App\Model\Nomina\Persona;

class CentroCostos extends Model
{
    protected $table = "ne_centro_costos";
    protected $primaryKey = 'id';

    protected $fillable = ['nombre','prefijo','codigo','created_at','updated_at'];


    public function uso(){
       return DB::table('ne_personas')->where('fk_empresa', Auth::user()->empresa)->where('fk_centro_costo', $this->id)->count();
    }

    public function personas(){
        return Persona::where('fk_centro_costo',$this->id)->get();
    }
}
