<?php

namespace App\Model\Ingresos;

use Illuminate\Database\Eloquent\Model;
use App\Retencion;

class FacturaRetencion extends Model
{
    protected $table = "factura_retenciones";
    protected $primaryKey = 'id';

    protected $fillable = [
        'factura', 'valor', 'retencion', 'id_retencion'
    ];

    public $timestamps = false;

    public function retencion()
    {
        return Retencion::find($this->id_retencion);
    }

    public function tipo()
    {
        if ($this->retencion()->tipo == 2){
            return "RETENCIÓN FTE";
        }elseif ($this->retencion()->tipo == 1){
            return "RETENCIÓN IVA";
        }else{
            return $this->retencion()->tipo == 3 ? "RETENCIÓN ICO" : "RETENCIÓN";
        }
    }
}
