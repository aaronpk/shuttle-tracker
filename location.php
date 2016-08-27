<?php
include('lib/inc.php');

header('Content-type: application/json');

$shuttles = [];

for($i=1; $i<=2; $i++) {

  $current = $redis->get('xoxo-tracker-location-'.$i);

  $history = array();

  $loc = $redis->lrange('xoxo-history-'.$i, 0, 1000);
  $coordinates = [];
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

  $history = array_map(function($item){
    return array($item[1],$item[0]);
  }, $newCoordinates);
  $history = array_reverse($history);

  $shuttles[] = [
    'current' => json_decode($current),
    'history' => $history
  ];
}

echo json_encode([
  'shuttles' => $shuttles
]);
