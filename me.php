<?php
include('lib/inc.php');

header('Content-type: application/json');

$data = array(
	'type' => 'Feature',
	'geometry' => array(
		'type' => 'Point',
		'coordinates' => array((double)$_GET['lng'], (double)$_GET['lat'])
	),
	'properties' => array(
    'id' => $_GET['uniq'],
    'date' => date('Y-m-d H:i:s')
  )
);

$redis->lpush('xoxo-visitors', json_encode($data));

echo "{}";
