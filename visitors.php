<html>
<head>
  <meta charset=utf-8 />
  <title>XOXO Shuttle</title>
  <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

  <link rel="stylesheet" href="/assets/leaflet.css" />
  <script type="text/javascript" src="/assets/tracker-dist.js"></script>
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <!-- <link href="/assets/style.css" rel="stylesheet" type="text/css"/> -->
  <style>
    body {
      margin:0;
      padding:0;
      font-weight: 400;
      font-family: sans-serif;
    }
    #header {
      width: 100%;
      color: black;
      font-size: 18px;
    }
    #header div {
      padding: 6px;
      padding-left: 12px;
    }
    #header.offline {
      background-color: #e7726c;
    }
    #map {
      position: absolute;
      top:36px;
      bottom:0;
      right:0;
      left:0;
    }
    .leaflet-control-container a {
      background-image: none;
    }
    #locate-me {
      background: white;
      width: 40px;
      height: 40px;
      border-radius: 20px;
      position: absolute;
      bottom: 30px;
      left: 30px;
      z-index: 100;
    }
    #locate-me.hidden {
      display: none;
    }
    #locate-me a {
      background: url(/images/locate-me@2x.png) no-repeat center center;
      background-size: 40px;
      width: 40px;
      height: 40px;
      display: block;
    }
  </style>
</head>
<body>

<div id="header">
  <div style="font-weight: bold; float: left;">XOXO</div>
  <div style="float: right;" id="header-text">Shuttle Tracker</div>
  <div style="clear: both;"></div>
</div>
<div id="map"></div>

<script type='text/javascript'>
  moment.tz.add('America/Los_Angeles|PST PDT|80 70|0101|1Lzm0 1zb0 Op0');

  function get_request(u, c) {
    (function(url,callback){
      request = new XMLHttpRequest();
      request.open('GET', url, true);
      request.onload = function() {
        if (request.status >= 200 && request.status < 400){
          // Success!
          callback(JSON.parse(request.responseText));
        } else {
          // We reached our target server, but it returned an error
          console.log("Error: " + request.status);
        }
      };
      
      request.onerror = function() {
        // There was a connection error of some sort
          console.log("There was an error connecting");
      };
      
      request.send();
    })(u, c);
  }

  var map = L.map('map');
  
  var bus = null;
  var me = null;
  var routeHistoryLine = null;
  var autoPanBus = true;
  var autoPanMe = true;

  L.esri.basemapLayer('Gray').addTo(map);

  map.attributionControl.setPrefix('shuttle tracker by <a href="http://aaronparecki.com/">aaronpk</a> | <a href="http://leafletjs.com/">Leaflet</a>');

  var stops = <?= file_get_contents('stops.geojson') ?>;

  var icons = [];
  
  var busIcon = L.icon({
    iconUrl: '/images/bus.png',
    iconRetinaUrl: '/images/bus@2x.png',
    iconSize: [24, 29],
    iconAnchor: [12, 29],
    popupAnchor: [0, -29]
  });

  var meIcon = L.icon({
    iconUrl: '/images/me.gif',
    iconRetinaUrl: '/images/me@2x.gif',
    iconSize: [16, 16],
    iconAnchor: [8, 8],
    popupAnchor: [0, 8]
  });

  var visitorIcon = L.icon({
    iconUrl: '/images/red-dot@2x.png',
    iconRetinaUrl: '/images/red-dot@2x.png',
    iconSize: [6, 6],
    iconAnchor: [3, 3],
    popupAnchor: [0, 6]
  });

  var bounds = new L.LatLngBounds();

  for(var i in stops) {
    var stopLocation = new L.LatLng(stops[i].geometry.coordinates[1], stops[i].geometry.coordinates[0]);

    var marker = L.marker(stopLocation, {
      icon: L.icon({
        iconUrl: "/images/"+stops[i].properties.icon+".png",
        iconRetinaUrl: "/images/"+stops[i].properties.icon+"@2x.png",
        iconSize: [24, 29],
        iconAnchor: [12, 29],
        popupAnchor: [0, -29]
      })
    })
    marker.addTo(map);

    bounds.extend(stopLocation);
  }

  map.fitBounds(bounds);

  // setTimeout(start_watching, 500);

  var visitorGroup = null;
  get_request('visitor-data.php', function(data) {
    if(visitorGroup) {
      map.removeLayer(visitorGroup);
    }
    visitorGroup = new L.layerGroup();
    for(var i in data) {
      visitorGroup.addLayer(L.marker([data[i][0], data[i][1]], {
        icon: visitorIcon
      }));
    }
    visitorGroup.addTo(map);
  });

</script>

</body>
</html>