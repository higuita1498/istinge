<?php

namespace App\Traits;


trait Funciones
{
    public static function parsear($valor)
    {
        $empresa = auth()->user()->empresaObj;
        return  isset($empresa) ? (number_format($valor, $empresa->precision, $empresa->sep_dec, ($empresa->sep_dec == '.' ? ',' : '.'))) : '';
    }

    public static function precision($valor)
    {
        return round($valor,  auth()->user()->empresaObj->precision);
    }
}
