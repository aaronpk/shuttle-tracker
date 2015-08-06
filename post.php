<?php
include('lib/inc.php');

header('Content-type: application/json');

$data = file_get_contents("php://input");

$input = json_decode($data);

if($input == null) {
	respondWithError(array(
		'error' => 'No input'
	));
}

if(!is_array($input->locations)) {
	respondWithError(array(
		'error' => 'locations is not an array'
	));
}


if(count($input->locations) > 0) {
  $loc = $input->locations[count($input->locations)-1];

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
			'walking' => (in_array('walking', $loc->motion) ? 1 : 0),
			'running' => (in_array('running', $loc->motion) ? 1 : 0),
			'driving' => (in_array('driving', $loc->motion) ? 1 : 0),
			'stationary' => (in_array('stationary', $loc->motion) ? 1 : 0),
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
	curl_exec($ch);
}


echo json_encode(array(
	'result' => 'ok',
));
