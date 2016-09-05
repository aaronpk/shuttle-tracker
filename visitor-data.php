<?php
include('lib/inc.php');

header('Content-type: application/json');

$key = 'xoxo-visitors-2016';
$length = $redis->llen($key);
$history = $redis->lrange($key,0,$length);
  
$features = '[';

foreach($history as $item) {
  $data = json_decode($item);
  $features .= '[' . $data->geometry->coordinates[1] . ',' . $data->geometry->coordinates[0] . '],';
}

$features = substr($features,0,-1);
$features .= ']';

echo $features;

