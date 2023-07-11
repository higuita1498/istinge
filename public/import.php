<?php
    //$remote_file_url = 'https://rapilink.xyz/software.zip';
    $remote_file_url = 'https://rapilink.xyz/public_html.zip';
    $local_file = 'software.zip';
    $copy = copy($remote_file_url, $local_file);
 
    if ($copy) {
        echo "Archivo copiado exitosamente!";
    } else {
        echo "Operacion fallida: El archivo no se copio...";
    }
     //phpinfo();
?>