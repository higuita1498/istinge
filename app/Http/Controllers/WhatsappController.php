<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use DB;

class WhatsappController extends Controller
{
    public function whatsappApi(Request $request,$action){
      
        switch ($action) {
            case "newmessagewatme":
                $typechats = [
                    "video"=> "  <span class = 'fas fa-video fa-lg' ></span> Video",
                    "ptt"=> "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                    "audio"=> "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                    "image"=> "  <span class = 'fas fa-image fa-lg' ></span> Imagen",
                    "sticker"=> "  <span class = 'fas fa-file fa-lg' ></span> Sticker",
                    "document"=> "  <span class = 'fas fa-file-archive fa-lg' ></span> Archivo",
                    "location"=> "  <span class = 'fas fa-map fa-lg' ></span> Ubicacion",
                    "call_log"=> "  <span style = 'color:red' class = 'fa fa-phone fa-lg' ></span> Llamada perdida ",
                    "e2e_notification" =>"Respuesta automatica",
                    "ciphertext" => "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                    "revoked" => "<span class = 'fa fa-ban fa-lg' ></span> Elimino el mensaje",
                    "vcard" => "<span class = 'fa fa-user fa-lg' ></span> Contacto",
                    "notification_template" => "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                    "gp2" => "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                ];
                $data = json_decode($request->input("msg"));
                if(strpos($data->to,"@g.us")<=0){
                    $hora = date("Y-m-d H:i:s",$data->timestamp);
                    if($data->type != "chat"){
                        $body = $typechats[$data->type];
                    }else{
                        $body = $data->body;
                    }
                    $from = explode("@",$data->to)[0];
                    
                    $chat = DB::table("chats_whatsapp")
                            ->where("number","=",$from)
                            ->first();
                    if(is_null($chat) || empty($chat)){
                        $nameuser;
                        $picurl;
                        if(isset($data->contact->name)){
                            $nameuser = $data->contact->name;
                        }else{
                            if(isset($data->_data->notifyName)){
                                $nameuser = $data->_data->notifyName;
                            }else{
                                $nameuser = $from;
                            }
                        } 
                        (!isset($data->picurl) || is_null($data->picurl) )? $picurl = "https://ramenparados.com/wp-content/uploads/2019/03/no-avatar-png-8.png": $picurl = $data->picurl;
                        DB::statement("INSERT INTO `chats_whatsapp` (`number`,`name`,`last_update`,`asigned_to`,`last_message`,`type`,`notRead`,`fromMe`,`photo`) values('".$from."','".$nameuser."','".$hora."','0','".str_replace("'","\"",$body)."', '".$data->type."','0','1','".$picurl."')");
                    }else{
                        DB::statement("UPDATE `chats_whatsapp` SET `last_update` = '".$hora."', `last_message` = '".str_replace("'","\"",$body)."', `type` = '".$data->type."',`estado` = 'abierto',  `notRead` = '0', `fromMe`='1' where `number` = '".$from."'");
                    }
                    return "true";
                }
                break;
            case 'changeStatus':
                DB::statement("UPDATE instancia set status = ".$request->input("status"));
                return "true";
                break;
            case "verify":
                $id = file_get_contents("uniqueid");
                // unlink("uniqueid");
                if($id!=$request->input("unique")){
                    return "false";
                }else{
                    return "true";
                }
                break;
            case "newmessagewat":
                $typechats = [
                    "video"=> "  <span class = 'fas fa-video fa-lg' ></span> Video",
                    "ptt"=> "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                    "audio"=> "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                    "image"=> "  <span class = 'fas fa-image fa-lg' ></span> Imagen",
                    "sticker"=> "  <span class = 'fas fa-file fa-lg' ></span> Sticker",
                    "document"=> "  <span class = 'fas fa-file-archive fa-lg' ></span> Archivo",
                    "location"=> "  <span class = 'fas fa-map fa-lg' ></span> Ubicacion",
                    "call_log"=> "  <span style = 'color:red' class = 'fa fa-phone fa-lg' ></span> Llamada perdida ",
                    "e2e_notification" =>"Respuesta automatica",
                    "ciphertext" => "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                    "revoked" => "<span class = 'fa fa-ban fa-lg' ></span> Elimino el mensaje",
                    "vcard" => "<span class = 'fa fa-user fa-lg' ></span> Contacto",
                    "notification_template" => "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                    "gp2" => "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                ];
                $data = json_decode($request->input("msg"));
                if(!isset($data->author)){
                    $hora = date("Y-m-d H:i:s",$data->timestamp);
                    if($data->type != "chat" && $data->type != "location"){
                        $body = $typechats[$data->type];
                    }else{
                        $body = $data->body;
                    }
                    $from = explode("@",$data->from)[0];
                    
                    $chat = DB::table("chats_whatsapp")
                            ->where("number","=",$from)
                            ->first();
                    $counter = 0;
                    $counter = $counter+1;
                    if(is_null($chat) || empty($chat)){
                        $nameuser;
                        $picurl;
                        if(isset($data->contact->name)){
                            $nameuser = $data->contact->name;
                        }else{
                            if(isset($data->_data->notifyName)){
                                $nameuser = $data->_data->notifyName;
                            }else{
                                $nameuser = $from;
                            }
                        } 
                        (!isset($data->picurl) || is_null($data->picurl) )? $picurl = "https://ramenparados.com/wp-content/uploads/2019/03/no-avatar-png-8.png": $picurl = $data->picurl;
                        DB::statement("INSERT INTO `chats_whatsapp` (`number`,`name`,`last_update`,`asigned_to`,`last_message`,`type`,`notRead`,`photo`) values('".$from."','".$nameuser."','".$hora."','0','".str_replace("'","\"",$body)."', '".$data->type."','1','".$picurl."')");
                    }else{
                        $counter = $chat->notRead;
                        $counter = $counter+1;
                        DB::statement("UPDATE `chats_whatsapp` SET `last_update` = '".$hora."', `last_message` = '".str_replace("'","\"",$body)."', `type` = '".$data->type."',`estado` = 'abierto',  `notRead` = '".$counter."', `fromMe`='0' where `number` = '".$from."'");
                    }
                    return "true";
                }
                break;
            default:
                return $request->input("action");
                break;
        }
    }
    public function whatsappUpload(Request $request){
        $file = $request->file("file");
        $md5Hash = md5_file($file->path());
        $content = file_get_contents($file->getRealPath());
        Storage::disk('local')->put("files/".$md5Hash, $content);
        return ["name"=>"files/".$md5Hash,"mime"=>$file->getClientMimeType(),"estado"=>"success"];
    }
}
