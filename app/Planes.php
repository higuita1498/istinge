<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Planes;
class Planes extends Model
{
    protected $table = "suplente_pago";
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'nombre', 'empresa','id_empresa','monto','meses','plan','tipo_pago','lapPaymentMethod','transactionState','EstadoTransaccion','referencia_pago','  api_key','merchant_id','account_id','transactionId','firm','type_currency','signature','description','estado','created_at','updated_at',
       'plazo', 'personalizado',
   ];

   public static function generateRandomString($length = 10) { 
    return substr(str_shuffle("0123456789"), 0, $length);
  }

   public static function ConsultaEstado($referencecode)
    {

      $array=array('test'=>false,
        "language"=>"es",
        "command"=>"ORDER_DETAIL_BY_REFERENCE_CODE",
        "merchant"=>array("apiLogin"=>"Edj3M6F7zTwQQmo",
          "apiKey"=>"tZdIpXl9HrE9hrzVncOv8UO0Fd"),
        "details"=>array("referenceCode"=>$referencecode)
      );
      $datos=json_encode($array);
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.payulatam.com/reports-api/4.0/service.cgi",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>$datos,
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/json"
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return "cURL Error #:" . $err;
      } else {
    //return json_encode($response);
        $response = simplexml_load_string($response);
        return json_encode($response);
      }
    }
}