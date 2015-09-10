<?php
include('lib/inc.php');

$socket = stream_socket_server("udp://0.0.0.0:8008", $errno, $errstr, STREAM_SERVER_BIND);
if(!$socket) {
  die("$errstr ($errno)\n");
}

echo "listening\n";

do {
  $pkt = stream_socket_recvfrom($socket, 1500, 0, $peer);
  echo "received from $peer\n";
  echo $pkt."\n";
  
  $loc = json_decode($pkt);
	$data = array(
		'type' => 'Feature',
		'geometry' => array(
			'type' => 'Point',
			'coordinates' => array((double)$loc->longitude, (double)$loc->latitude)
		),
		'properties' => array(
			'date' => date('Y-m-d\TH:i:s\Z', $loc->timestamp),
			'accuracy' => (int)$loc->horizontal_accuracy,
			'speed' => (int)$loc->speed,
			'altitude' => (int)$loc->altitude,
/*
			'walking' => (in_array('walking', $loc->motion) ? 1 : 0),
			'running' => (in_array('running', $loc->motion) ? 1 : 0),
			'driving' => (in_array('driving', $loc->motion) ? 1 : 0),
			'stationary' => (in_array('stationary', $loc->motion) ? 1 : 0),
*/
		)
	);
	$redis->publish('xoxo-tracker', json_encode($data));
	$redis->set('xoxo-tracker-location', json_encode($data));
	$redis->lpush('xoxo-history', json_encode($data));

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, Config::$baseURL.'/streaming/pub?id=shuttle');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	echo $response."\n";
  
} while($pkt !== false);
