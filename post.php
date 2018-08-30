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
  if(property_exists($input, 'current')) {
    $data = $input->current;
    $data->properties->history_type = 'current';
  } else {
    $data = $input->locations[count($input->locations)-1];
    $data->properties->history_type = 'historic';
  }

  $shuttle = $data->properties->device_id;

	$redis->publish('xoxo-tracker-'.$shuttle, json_encode($data));
	$redis->set('xoxo-tracker-location-'.$shuttle, json_encode($data));
	$redis->lpush('xoxo-history-'.$shuttle, json_encode($data));
  $tile38->rawCommand('SET', 'xoxo', $shuttle,
    'POINT', $data->geometry->coordinates[1], $data->geometry->coordinates[0]);

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
