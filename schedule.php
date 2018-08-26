<html>
<head>
  <title>XOXO Shuttle Schedule</title>
  <link href="assets/style.css" rel="stylesheet" type="text/css"/>
  <style type="text/css">
    ul li {
      margin-bottom: 0;
    }
  </style>
</head>
<body>

<div style="padding:30px;">

<?php
$stops = json_decode(file_get_contents('stops.geojson'));

foreach($stops as $stop) {
  echo '<h3>' . $stop->properties->name . '</h3>';
  echo '<ul>';
    foreach($stop->properties->schedule as $s) {
      echo '<li>' . $s . '</li>';
    }
  echo '</ul>';
}
?>

</div>

</body>
</html>
