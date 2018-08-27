<?php
include('lib/inc.php');

$data = json_decode(file_get_contents("php://input"), true);

$stops = json_decode(file_get_contents('stops.geojson'));
$bridges = json_decode(file_get_contents('bridges.geojson'));

$redis->publish('xoxo-test', json_encode($data));


// Turn the hook into a sentence

$shuttle = $data['id'];
$event = $data['detect'];
$hook = $data['hook'];


if(preg_match('/stop-/', $hook)) {

  $stop = stop_from_id(str_replace('stop-', '', $hook), $stops);

  post_to_slack(ucfirst($shuttle).' shuttle '
    .($event == 'enter' ? 'arrived at' : 'departed').' '
    .$stop->properties->name);

  set_current_stop($shuttle, $stop->properties->name, $event == 'enter' ? 'arrived' : 'departed');

} elseif(preg_match('/bridge-/', $hook)) {

  $bridge = bridge_from_id(str_replace('bridge-', '', $hook), $bridges);

  if($event == 'enter') {

    post_to_slack(ucfirst($shuttle).' shuttle '
      .'is crossing the '
      .$bridge->properties->name);

  }

}



function set_current_stop($shuttle, $stop, $status) {
  global $redis;

  $data = [
    'shuttle' => $shuttle,
    'stop' => $stop,
    'status' => $status
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, Config::$baseURL.'/streaming/pub?id=stop');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_exec($ch);

  return $redis->set('xoxo-shuttle-current::'.$shuttle, json_encode($data));
}

