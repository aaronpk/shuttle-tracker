<?php
chdir('..');
include('lib/inc.php');

echo "Starting...\n";

$stops = json_decode(file_get_contents('stops.geojson'));
$bridges = [
  ['name'=>'burnside','coordinates'=>[-122.66786813735963,45.522871441089144]],
  ['name'=>'morrison','coordinates'=>[-122.66934871673583,45.51801532361445]],
  ['name'=>'hawthorne','coordinates'=>[-122.670648, 45.513140]],
];


$shuttle = $argv[1];

foreach($stops as $stop) {
  if(get_stop_state($redis, $shuttle, $stop->properties->Name) === false) {
    echo "Setting default state\n";
    set_stop_state($redis, $shuttle, $stop->properties->Name, 'outside');
  }
}
foreach($bridges as $bridge) {
  if(get_stop_state($redis, $shuttle, $bridge['name']) === false) {
    echo "Setting default state for ".$bridge['name']."\n";
    set_stop_state($redis, $shuttle, $bridge['name'], 'outside');
  }
}

$redis->subscribe(['xoxo-tracker-'.$shuttle], function($r, $channel, $data) use($stops, $shuttle, $bridges) {
  $redis2 = new Redis();
  $redis2->connect('127.0.0.1', 6379);

  $data = json_decode($data);
  echo $data->geometry->coordinates[1] . ',' . $data->geometry->coordinates[0] . "\n";

  foreach($stops as $stop) {
    if(geo\gcDistance($stop->geometry->coordinates[1], $stop->geometry->coordinates[0],
      $data->geometry->coordinates[1], $data->geometry->coordinates[0]) <= 40) {
      // Shuttle is inside this stop right now
      // If the shuttle was previously outside this stop, trigger a notification
      if(($state=get_stop_state($redis2, $shuttle, $stop->properties->Name)) == 'outside') {
        // The shuttle arrived
        post_to_slack('Shuttle arrived at ' . $stop->properties->Name);
        set_current_stop_status($redis2, $shuttle, $stop->properties->Name, 'arrived');
      }
      if($state != 'inside')
        set_stop_state($redis2, $shuttle, $stop->properties->Name, 'inside');
    } else {
      // echo "outside " . $stop->properties->Name . " " . $state[$stop->properties->Name] . "\n";
      // Shuttle is not inside this stop anymore
      if(($state=get_stop_state($redis2, $shuttle, $stop->properties->Name)) == 'inside') {
        // The shuttle departed
        post_to_slack('Shuttle departed ' . $stop->properties->Name);
        set_current_stop_status($redis2, $shuttle, $stop->properties->Name, 'departed');
      }
      if($state != 'outside')
        set_stop_state($redis2, $shuttle, $stop->properties->Name, 'outside');
    }
  }
  
  foreach($bridges as $bridge) {
    if(geo\gcDistance($bridge['coordinates'][1], $bridge['coordinates'][0],
      $data->geometry->coordinates[1], $data->geometry->coordinates[0]) <= 200) {
      // Shuttle is on this bridge
      if(($state=get_stop_state($redis2, $shuttle, $bridge['name'])) == 'outside') {
        post_to_slack('Shuttle is crossing the ' . ucfirst($bridge['name']) . ' bridge');
      }
      if($state != 'inside')
        set_stop_state($redis2, $shuttle, $bridge['name'], 'inside');
    } else {
      $state = get_stop_state($redis2, $shuttle, $bridge['name']);
      if($state != 'outside')
        set_stop_state($redis2, $shuttle, $bridge['name'], 'outside');
    }
  }

  $redis2->close();
});

function get_stop_state(&$r, $shuttle, $stop) {
  return $r->get('xoxo-shuttle-state::'.$shuttle.'::'.$stop);
}

function set_stop_state(&$r, $shuttle, $stop, $state) {
  return $r->set('xoxo-shuttle-state::'.$shuttle.'::'.$stop, $state);
}

function set_current_stop_status(&$r, $shuttle, $stop, $status) {
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

  return $r->set('xoxo-shuttle-current::'.$shuttle, json_encode($data));
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
  $response = curl_exec($ch);  
}
