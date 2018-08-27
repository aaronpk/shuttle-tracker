<?php
chdir(dirname(__FILE__).'/..');
require_once('vendor/autoload.php');
include('lib/inc.php');

$stops = json_decode(file_get_contents('stops.geojson'));

foreach($stops as $stop) {

  $name = strtolower(str_replace(' ', '_', $stop->properties->name));
  echo "Registering geofence $name\n";
  $result = $tile38->rawCommand('SETHOOK', 'stop-'.$name, 'https://xoxo.io.dev/hook.php',
    'NEARBY', 'xoxo',
    'FENCE', 'DETECT', 'enter,exit,cross',
    'POINT', $stop->geometry->coordinates[1], $stop->geometry->coordinates[0], 100
  );

}

$bridges = json_decode(file_get_contents('bridges.geojson'));

foreach($bridges as $bridge) {
  $name = strtolower(str_replace(' ', '_', $bridge->properties->name));

  echo "Registering geofence $name\n";
  echo json_encode($bridge->geometry, JSON_UNESCAPED_SLASHES)."\n";
  $result = $tile38->rawCommand('SETHOOK', 'bridge-'.$name, 'https://xoxo.io.dev/hook.php',
    'INTERSECTS', 'xoxo',
    'FENCE', 'DETECT', 'enter,exit,cross',
    'OBJECT', "'".json_encode($bridge->geometry, JSON_UNESCAPED_SLASHES)."'");
  var_dump($result);
  echo "\n";
}

echo "\n";
