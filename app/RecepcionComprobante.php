<?php

namespace App;

use App\Model\Gastos\FacturaProveedores;
use App\Contacto;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class RecepcionComprobante extends Model
{
    public static function emisor($nit){
        return Contacto::where('nit',$nit)->first();
    }

    public static function formatFecha($fecha){
        return Carbon::parse(strtotime($fecha))->format('Y-m-d');
    }

    public static function textoEventodian($documento){

        $texto = "";
        if($documento->acusado == 1){
            $texto = "Acusado";
        }
        if($documento->acusado == 1 && $documento->confirma_recepcion == 1){
            $texto = "Acusado, Confirmado";
        }

        if($documento->acusado == 1 && $documento->confirma_recepcion == 1 && $documento->acusado == 1 && $documento->aceptado == 1){
            $texto = "Acusado, Confirmado, Aceptado";
        }

        if($documento->acusado == 1 && $documento->confirma_recepcion == 1 && $documento->acusado == 1 && $documento->rechazado == 1){
            $texto = "Acusado, Confirmado, Rechazado";
        }

        return $texto;

    }
    
    public static function tipoDocumento($documento){
        $tipo = "";
        
        if($documento->documentTypeCode == "01"){$tipo = "FV";}
        else if($documento->documentTypeCode == "02"){$tipo = "FE";}
        else if($documento->documentTypeCode == "03"){$tipo = "FPCf";}
        else if($documento->documentTypeCode == "04"){$tipo = "FPCD";}
        else if($documento->documentTypeCode == "91"){$tipo = "NC";}
        else if($documento->documentTypeCode == "92"){$tipo = "ND";}
        
        
        return $tipo;
    }
}
