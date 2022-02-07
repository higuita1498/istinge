<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Auth;

use App\Contrato;
use App\Funcion;
use DB;

class Ping extends Model
{
    protected $table = "pings";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contrato', 'ip', 'fecha', 'estado', 'created_at', 'updated_at'
    ];
}