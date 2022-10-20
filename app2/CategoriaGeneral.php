<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class CategoriaGeneral extends Model
{
    protected $table = "categorias_general";
    protected $primaryKey = 'id';

    protected $fillable = ['nombre'];

    public function hijos()
    {
        return Categoria::where('fk_catgral', $this->id)->where('empresa', Auth::user()->empresa)->count();
    }

    public function asociado()
    {
        return Categoria::where('fk_catgral', $this->id)->where('empresa', Auth::user()->empresa)->get();
    }
}
