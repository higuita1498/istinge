<?php
    $remote_file_url = 'https://movistic.co/software_import.zip';
    $local_file = 'software_import.zip';
    /*$copy = copy($remote_file_url, '$local_file');*/

    $contextOptions = array(
	"ssl" => array(
		"verify_peer"      => false,
		"verify_peer_name" => false,
	),
);

// the copy or upload shebang
$copy = copy( $remote_file_url, $local_file, stream_context_create( $contextOptions ) );

    if ($copy) {
        echo "Archivo copiado exitosamente!";
    } else {
        echo "Operacion fallida: El archivo no se copio...";
    }
     //phpinfo();
?>