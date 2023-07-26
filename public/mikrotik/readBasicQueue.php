<?php 
	require('routeros_api.class.php');
	require('ip.php');

	$API = new RouterosAPI();
	$data = new StdClass();

	$hoy = date('Y-m-d');
	if ($API->connect($ip,$user,$pw)) {
		$API->write('/queue/simple/print');

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		$API->disconnect();

		$count = count($ARRAY);
		$data->c = $count;
		$data->estado = 1;

		if($count>0){
			for ($i=0; $i < $count; $i++) {
				$ip = explode('/', $ARRAY[$i]['target']);
				$speed = explode('/', $ARRAY[$i]['max-limit']);
				$data->id[] = $ARRAY[$i]['.id'];
				$data->ip[] = $ip[0];
				$data->name[] = $ARRAY[$i]['name'];
				$data->up[] = $speed[0]/1000;
				$data->down[] = $speed[1]/1000;
			}
		}
	} else {
		$data->estado = 0;
		$data->ip = $ip;
		$data->user = $user;
		$data->pw = $pw;
	}
	echo json_encode($data);
?>