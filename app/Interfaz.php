<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Auth;

use App\Contrato;

class Interfaz extends Model
{
    protected $table = "interfaces";
    protected $primaryKey = 'id';
    protected $fillable = [
        'name', 'type', 'created_at', 'updated_at'
    ];
}