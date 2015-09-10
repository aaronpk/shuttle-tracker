<?php
include('lib/inc.php');

$stops = json_decode(file_get_contents('stops.geojson'));

foreach($stops as $stop) {
  if(get_stop_state($redis, $stop->properties->Name) === false) {
    echo "Setting default state\n";
    set_stop_state($redis, $stop->properties->Name, 'outside');
  }
}

$redis->subscribe(['xoxo-tracker'], function($r, $channel, $data) use($stops) {
  $redis2 = new Redis();
  $redis2->connect('127.0.0.1', 6379);

  $data = json_decode($data);
  echo $data->geometry->coordinates[1] . ',' . $data->geometry->coordinates[0] . "\n";

  foreach($stops as $stop) {
    if(geo\gcDistance($stop->geometry->coordinates[1], $stop->geometry->coordinates[0],
      $data->geometry->coordinates[1], $data->geometry->coordinates[0]) <= 20) {
      // Shuttle is inside this stop right now
      // If the shuttle was previously outside this stop, trigger a notification
      if(($state=get_stop_state($redis2, $stop->properties->Name)) == 'outside') {
        // The shuttle arrived
        post_to_slack('The shuttle arrived at ' . $stop->properties->Name);
      }
      if($state != 'inside')
        set_stop_state($redis2, $stop->properties->Name, 'inside');
    } else {
      // echo "outside " . $stop->properties->Name . " " . $state[$stop->properties->Name] . "\n";
      // Shuttle is not inside this stop anymore
      if(($state=get_stop_state($redis2, $stop->properties->Name)) == 'inside') {
        // The shuttle departed
        post_to_slack('The shuttle departed ' . $stop->properties->Name);
      }
      if($state != 'outside')
        set_stop_state($redis2, $stop->properties->Name, 'outside');
    }
  }

});

function get_stop_state(&$r, $stop) {
  return $r->get('xoxo-shuttle-state::'.$stop);
}

function set_stop_state(&$r, $stop, $state) {
  return $r->set('xoxo-shuttle-state::'.$stop, $state);
}

function post_to_slack($msg) {
  echo $msg . "\n";

  $payload = array(
    'text' => $msg,
    'username' => 'ShuttleBot',
    'icon_url' => Config::$baseURL . '/assets/slack-icon.png'
  );
  
  $ch = curl_init(Config::$slackURL);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('payload'=>json_encode($payload))));
  #$response = curl_exec($ch);  
}
