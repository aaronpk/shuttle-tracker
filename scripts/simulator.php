<?php
chdir('..');
include('lib/inc.php');

if(!array_key_exists(3, $argv)) {
  echo "Usage: simulator.php [route1.json] [offset] [shuttle]\n";
  die();
}

$file = $argv[1];
$offset = $argv[2];
$shuttle = $argv[3];

$route = json_decode(file_get_contents('scripts/'.$file));
echo "Starting from offset: $offset\n";

$step_size = 10; // step size in meters
$delay = 100;    // delay in milliseconds

$last = false;

$first_loop = true;

while(true) {
  foreach($route->geometry->coordinates as $o=>$point) {
    if($offset != 'all') {
      if($first_loop && $offset) {
        if($o < $offset) {
          $last = $point;
          continue;
        }
      }
    }

    if($last) {
      print_r($point);
      // Calculate the distance between the two points
      $distance = geo\gcDistance($point[1], $point[0], $last[1], $last[0]);
      echo "Distance from last point to this point: ".$distance."\n";

      if($offset == 'all') {
        post_location([$point[0], $point[1]], $shuttle, $o);
        usleep($delay*1000);
      } else {
        // Figure out how many steps to divide these points into
        $steps = floor($distance / $step_size);
        echo "Breaking route into $steps steps\n";
        for($i=0; $i<$steps; $i++) {
          $x_diff = ($point[0] - $last[0]) / $steps;
          $y_diff = ($point[1] - $last[1]) / $steps;
          $x = $last[0] + ($x_diff * $i);
          $y = $last[1] + ($y_diff * $i);
          $response = post_location([$x, $y], $shuttle, $o);
          usleep($delay*1000);
        }
      }
    }

    $last = $point;
  }

  $first_loop = false;
}


function post_location($loc, $shuttle, $offset) {
  echo "$offset Location: ".implode(",", $loc)."\n";
  $ch = curl_init(Config::$baseURL.'/post.php');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'locations' => [
      [
        'type' => 'Feature',
        'geometry' => [
          'type' => 'Point',
          'coordinates' => $loc,
        ],
        'properties' => [
          'shuttle' => $shuttle,
          'accuracy' => 10,
          'timestamp' => time(),
          'horizontal_accuracy' => 5,
          'speed' => -1,
          'altitude' => -1,
          'motion' => [],
        ]
      ]
    ]
  ]));
  return curl_exec($ch);
}

