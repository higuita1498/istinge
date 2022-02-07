<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
class Servidor extends Model
{
    protected $table = "servidores";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $timestamps = false;
    protected $fillable = [
        'public_id','name','state','type'
    ];

    public function state(){
        return $this->state == 'linked' ? 'Conectado' : 'Desconectado';
    }
    
    public function enabled(){
        $tmp        = 0;
        $tmp        += Contrato::where('server_configuration_id', $this->id)->where('state','enabled')->where('status',1)->count();
        return $tmp;
    }
    
    public function disabled(){
        $tmp        = 0;
        $tmp        += Contrato::where('server_configuration_id', $this->id)->where('state','disabled')->where('status',1)->count();
        return $tmp;
    }
}