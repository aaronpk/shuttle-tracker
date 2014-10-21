<html>
<head>
  <title>XOXO Shuttle Schedule</title>
  <style type="text/css">
    body {
      background-color: #eaeaea;
      background-image: url(/images/bkg.jpg);
      font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:14px;
    }
  </style>
</head>
<body>
<?php
$stops = json_decode(file_get_contents('stops.geojson'));

foreach($stops as $stop) {
  echo '<h3>' . $stop->properties->Name . '</h3>';
  echo '<ul>';
    foreach($stop->properties->schedule as $s) {
      echo '<li>' . $s . '</li>';
    }
  echo '</ul>';
}
?>
</body>
</html>