<?php
date_default_timezone_set('UTC');

include(dirname(__FILE__).'/geo.php');
include(dirname(__FILE__).'/config.php');

$redis = new Redis();
$redis->pconnect('127.0.0.1', 6379);

$tile38 = new Redis();
$tile38->pconnect('127.0.0.1', 9851);
// $tile38 = new Predis\Client('tcp://127.0.0.1:9851');




function name_to_id($name) {
  return strtolower(str_replace(' ', '_', $name));
}

function stop_from_id($id, $stops) {
  foreach($stops as $stop) {
    if($id == name_to_id($stop->properties->name))
      return $stop;
  }
}

function bridge_from_id($id, $bridges) {
  foreach($bridges as $bridge) {
    if($id == name_to_id($bridge->properties->name))
      return $bridge;
  }
}



class ErrorHandling {
  public static function handle_error($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
      // this error code is not included in error_reporting
      return;
    }

		dieWithError(array(
			'error' => $errstr,
			'errno' => $errno,
			'file' => $errfile,
			'line' => $errline
		));
  }

	public static function handle_exception($exception, $call_previous = true) {
		dieWithError(array(
			'error' => $exception->getMessage()
		));
  }
}

if(!array_key_exists('SHELL',$_SERVER)) {
  set_error_handler(array("ErrorHandling", "handle_error"));
  set_exception_handler(array("ErrorHandling", "handle_exception"));
}

function dieWithError($err) {
  header('HTTP/1.1 400 Bad Request');
	die(json_encode($err));
}

function respondWithError($err) {
  header('HTTP/1.1 400 Bad Request');
	die(json_encode($err));
}

function getDistanceToStops($lat, $lng) {
  $stops = json_decode(file_get_contents(dirname(__FILE__).'/../stops.geojson'), true);

  $locations = [
    $lat.','.$lng
  ];

  foreach($stops as $stop) {
    $locations[] = $stop['geometry']['coordinates'][1].','.$stop['geometry']['coordinates'][0];
  }

  $ch = curl_init('https://www.mapquestapi.com/directions/v2/routematrix?key='.Config::$mapquestKey);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'locations' => $locations,
    'options' => [
      "unit" => "m",
      "routeType" => "pedestrian",
      "doReverseGeocode" => false,
      "allToAll" => false
    ]
  ]));
  $data = json_decode(curl_exec($ch), true);

  $response = [];
  foreach($stops as $i=>$stop) {
    $response[] = [
      'name' => $stop['properties']['name'],
      'stop' => md5($stop['properties']['name']),
      'distance' => $data['distance'][$i+1],
      'seconds' => $data['time'][$i+1],
    ];
  }
  return $response;
}


function post_to_slack($msg) {
  global $redis;

  $payload = array(
    'text' => $msg,
    'username' => 'ShuttleBot',
    'icon_url' => Config::$baseURL . '/assets/slack-icon.png'
  );

  $ch = curl_init(Config::$slackURL);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('payload'=>json_encode($payload))));
  $response = curl_exec($ch);


  if(Config::$micropubEndpoint) {
    $ch = curl_init(Config::$micropubEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
      'h' => 'entry',
      'content' => $msg,
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Authorization: Bearer ' . Config::$micropubToken
    ]);
    $response = curl_exec($ch);
  }

}

