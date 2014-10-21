<?php
include('inc.php');

header('Content-type: application/json');

$current = $redis->get('xoxo-tracker-location');

$history = array(
  'type' => 'LineString',
  'properties' => array(
    'count' => 0,
    'distance' => 0,
  ),
  'coordinates' => array(),
  'bbox' => array()
);

$loc = $redis->lrange('xoxo-history', 0, 1000);
foreach($loc as $l) {
  $p = json_decode($l, true);
  if($p['properties']['accuracy'] <= 100)
    $coordinates[] = $p['geometry']['coordinates'];
}


// Trim the line based on the line's distance
$maxDistance = 1200;

$newCoordinates = array();
$distance = 0;
$lineDistance = 0;
$last = false;
foreach($coordinates as $c) {
  if($last) {
    $d = geo\gcDistance($last[1],$last[0],$c[1],$c[0]);
    $distance += $d;
  }
  if($distance <= $maxDistance) {
    $newCoordinates[] = $c;
    if($last)
      $lineDistance += $d;
  }
  $last = $c;
}


// Simplify the line
$newCoordinates = geo\ramerDouglasPeucker($newCoordinates, 0.0001);


$history['coordinates'] = $newCoordinates;
$history['properties']['distance'] = $lineDistance;
$history['properties']['count'] = count($history['coordinates']);

echo json_encode(array(
  'current' => json_decode($current),
  'history' => $history
));
