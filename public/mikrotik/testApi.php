<?php 
	require('routeros_api.class.php');
	require('ip.php');
	
	$API = new RouterosAPI();
	$data = new StdClass();

	if ($API->connect($ip,$user,$pw)) {
		$API->write('/ip/route/print');
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		$API->disconnect();
		$data->estado = 1;
	} else {
		$data->estado = 0;
		$data->ip = $ip;
		$data->user = $user;
		$data->pw = $pw;
	}
	echo json_encode($data);
 ?>