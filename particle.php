<?php
include('lib/inc.php');
header('Content-type: application/json');

$input = json_decode($_POST['data']);

$shuttle = 1;

$data = [
  'type' => 'Feature',
  'geometry' => [
    'type' => 'Point',
    'coordinates' => [(double)$input->lng, (double)$input->lat]
  ],
  'properties' => [
    'shuttle' => $shuttle,
    'date' => date('Y-m-d\TH:i:s\Z'),
    'accuracy' => $input->dop/2, // estimate
    'speed' => -1,
    'altitude' => -1,
    'walking' => 0,
    'running' => 0,
    'driving' => 0,
    'stationary' => 0,
  ]
];
$redis->publish('xoxo-tracker-'.$shuttle, json_encode($data));
$redis->set('xoxo-tracker-location-'.$shuttle, json_encode($data));
$redis->lpush('xoxo-history-'.$shuttle, json_encode($data));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, Config::$baseURL.'/streaming/pub?id=shuttle');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);

// $fp = fopen('particle.log', 'a');
// fwrite($fp, json_encode($data)."\n");
// fclose($fp);
