<?php
include('lib/inc.php');

header('Content-type: application/json');

$data = array(
  'id' => $_GET['uniq'],
  'date' => date('Y-m-d H:i:s'),
  'lat' => $_GET['lat'],
  'lng' => $_GET['lng'],
);

$redis->lpush('xoxo-visitors-2016', json_encode($data));

echo "{}";
