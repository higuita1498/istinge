<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Barrios extends Model
{
    protected $table = "barrios";
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre','created_at', 'updated_at', 'status', 'created_by'
    ];

    public function nroClientes(){
        return DB::table('contactos')->where('barrio_id',$this->id)->count();
    }
}
