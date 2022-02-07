<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Auth;

use App\Mikrotik;
use App\Funcion;
use DB;
use App\User;

class Segmento extends Model
{
    protected $table = "segmentos_ip";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mikrotik', 'segmento', 'created_at', 'updated_at'
    ];
    
    public function mikrotik(){
        return Mikrotik::find($this->mikrotik);
    }
}