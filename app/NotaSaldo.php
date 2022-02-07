<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotaSaldo extends Model
{
    protected $table = "notas_saldos";
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_nota','saldo_nota', 'created_at', 'updated_at'
    ];
}
