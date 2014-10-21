<?php
include('inc.php');

header('Content-type: application/json');

$history = array(
  'type' => 'Feature',
  'properties' => array(),
  'geometry' => array(
    'type' => 'LineString',
    'coordinates' => array()
  )
);

$loc = $redis->lrange('xoxo-history', 0, 10);
foreach($loc as $l) {
  $p = json_decode($l, true);
  $history['geometry']['coordinates'][] = $p['geometry']['coordinates'];
}

echo json_encode($history);
