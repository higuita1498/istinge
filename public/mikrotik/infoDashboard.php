<?php 
	require('routeros_api.class.php');
	require('ip.php');
	
	$API = new RouterosAPI();
	$data = new StdClass();

	if ($API->connect($ip,$user,$pw)) {
		$API->write('/system/resource/print');
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		$API->disconnect();

		$data->estado = 1;
		$data->boardname = $ARRAY[0]['board-name'];
		$data->uptime = $ARRAY[0]['uptime'];
		$data->cpuload = $ARRAY[0]['cpu-load'];
		$data->version = $ARRAY[0]['version'];
	} else {
		$data->estado = 0;
		$data->ip = $ip;
		$data->user = $user;
		$data->pw = $pw;
	}
		echo json_encode($data);
 ?>