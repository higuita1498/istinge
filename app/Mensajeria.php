<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
class Mensajeria extends Model
{
    protected $table = "mensajeria";
    protected $primaryKey = 'id';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contenido','tipo','fecha', 'hora', 'user'
    ];
}