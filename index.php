<html>
<head>
  <meta charset=utf-8 />
  <title>XOXO Shuttle</title>
  <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

  <!--
  <link rel="stylesheet" href="/assets/leaflet.css" />
  <script src="/assets/leaflet.js"></script>
  -->

  <script src='https://api.mapbox.com/mapbox.js/v3.1.1/mapbox.js'></script>
  <link href='https://api.mapbox.com/mapbox.js/v3.1.1/mapbox.css' rel='stylesheet' />

  <script src="/assets/moment.min.js"></script>
  <script src="/assets/moment-timezone.min.js"></script>
  <script type="text/javascript" src="/assets/pushstream.js"></script>
  <script type="text/javascript" src="/assets/js-cookie.js"></script>

  <!--
  <script type="text/javascript" src="/assets/tracker-dist.js"></script>
  -->

  <link rel="apple-touch-icon" href="/images/xoxo-shuttle-icon.png">
  <link rel="stylesheet" href="https://use.typekit.net/tnr5bfv.css">

  <!-- <link href="/assets/style.css" rel="stylesheet" type="text/css"/> -->

  <style>
    body {
      margin:0;
      padding:0;
      font-weight: 400;
      font-family: sans-serif;
    }
    #container {
      height: 100vh;
      overflow-y: hidden;
    }
    #map {
      width: 100%;
      height: 50vh;
    }
    #stops {
      width: 100%;
      height: calc(50vh - 36px);
      background: #1E232A;
      color: white;
      overflow-y: scroll;
    }
    #stops .pad {
      padding: 10px;
    }
    #header {
      background: #1E232A;
      color: white;
      height: 36px;
      width: 100%;
      font-size: 20px;
      font-family: "cortado",sans-serif;
    }
    #header div {
      padding: 6px;
      padding-left: 12px;
    }
    #header.offline {
      background-color: #F52253;
    }
    #locate-me {
      background: transparent;
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
      background-color: transparent;
      background: url(/images/locate-me@2x.png) no-repeat center center;
      background-size: 40px;
      width: 40px;
      height: 40px;
      display: block;
    }

    #stops h3 {
      margin: 0;
      margin-bottom: 0.25em;
      padding: 0;
    }
    #stops ul {
      margin: 0;
      padding: 0;
      font-size: 16px;
    }
    #stops ul li {
      margin: 0;
      padding: .5em 0 .4em 0;
      border-top: 1px #424D5C solid;

      height: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    #stops ul li .in {
      padding-left: 40px;
    }
    #stops ul li:last-child {
      border-bottom: 1px #424D5C solid;
    }
    #stops .distance {
      font-size: 12px;
      color: #888;
    }

    #stops {
      position: relative;
    }
    .current-stop {
      position: absolute;
      left: 16px;
      top: 0;
      display: none;
    }

    .current-stop.stop1,
    .current-stop.stop2,
    .current-stop.stop3,
    .current-stop.stop4,
    .current-stop.stop5,
    .current-stop.stop6,
    .current-stop.stop7 {
      display: inline-block;
    }

    .current-stop.stop1 {
      top: 51px;
    }
    .current-stop.stop1.departed {
      top: 79px;
    }

    .current-stop.stop2 {
      top: 108px;
    }
    .current-stop.stop2.departed {
      top: 134px;
    }

    .current-stop.stop3 {
      top: 163px;
    }
    .current-stop.stop3.departed {
      top: 190px;
    }

    .current-stop.stop4 {
      top: 218px;
    }
    .current-stop.stop4.departed {
      top: 245px;
    }

    .current-stop.stop5 {
      top: 273px;
    }
    .current-stop.stop5.departed {
      top: 300px;
    }

    .current-stop.stop6 {
      top: 329px;
    }
    .current-stop.stop6.departed {
      top: 356px;
    }

    .current-stop.stop7 {
      top: 384px;
    }
    .current-stop.stop7.departed {
      top: 411px;
    }


    @keyframes thinkingAnimation {
      0%   { opacity:1; }
      50%  { opacity:0.5; }
      100% { opacity:1; }
    }
    .animate-thinking {
      opacity: 1;
      animation: thinkingAnimation 1.5s infinite;
    }
  </style>
</head>
<body>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-4617305-28', 'auto');
  ga('send', 'pageview');

</script>

<div id="container">
  <div id="header">
    <div style="font-weight: bold; float: left;">XOXO</div>
    <div style="float: right;" id="header-text">Shuttle Tracker</div>
    <div style="clear: both;"></div>
  </div>
  <div id="map">
    <div id="locate-me"><a href="javascript:locateMe();"></a></div>
  </div>
  <div id="stops">
    <div class="pad">
      <? $stops = json_decode(file_get_contents('stop-order.json'), true); ?>
      <?
        $now = new DateTime();
        //$now = new DateTime('2018-09-07T16:00:00-0700');

        $now->setTimeZone(new DateTimeZone('US/Pacific'));

        if($now->format('H') >= 18 || $now->format('H') <= 4)
          $index = 1;
        else
          $index = 0;

        if($now->format('H') <= 3)
          $today = $now->sub(new DateInterval('PT6H'));
        $today = $now->format('j');

        if(array_key_exists($today, $stops) && array_key_exists($index, $stops[$today]) && count($stops[$today][$index])):
          ?>
          <h3><?= $now->format('M j') ?> Schedule</h3>
          <ul>
            <? foreach($stops[$today][$index] as $stop): ?>
              <li id="stop-<?= md5($stop) ?>">
                <div class="in">
                  <div class="name"><?= $stop ?></div>
                  <div class="distance"></div>
                </div>
              </li>
            <? endforeach; ?>
          </ul>
          <?
        else:
          ?>
          <p>The shuttle is not in service</p>
          <?
        endif;
        ?>
    </div>
    <img src="/images/bus-right@2x.png" width="29" class="current-stop" id="shuttle-rose">
    <img src="/images/bus-right@2x.png" width="29" class="current-stop" id="shuttle-grey">
    <input type="hidden" id="schedule-day" value="<?= $today ?>">
    <input type="hidden" id="schedule-index" value="<?= $index ?>">
  </div>
</div>

<script type='text/javascript'>
  moment.tz.add('America/Los_Angeles|PST PDT|80 70|0101|1Lzm0 1zb0 Op0');

  L.mapbox.accessToken = 'pk.eyJ1IjoiYWFyb25wayIsImEiOiJjamxhM2g4OWsxdjBvM3BuNXBud29qYXd5In0.Gl2tcr7jV3okCU5WMJNHcA';

  function get_request(url, callback) {
    var request = new XMLHttpRequest();
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
  }

  var map = L.map('map');

  L.control.attribution({
    position: "bottomleft",
    prefix: "Shuttle tracker by <a href=\"https://aaronparecki.com\">aaronpk</a>"
  }).addTo(map);

  var bus = [];
  var routeHistoryLine = [];
  var me = null;
  var autoPanBus = true;
  var autoPanMe = true;

  var mapboxTiles = L.mapbox.styleLayer('mapbox://styles/aaronpk/cjla32atp29ho2qp9nauwjdjo');

  map.addLayer(mapboxTiles);

  var stops = <?= file_get_contents('stops.geojson') ?>;
  var order = <?= file_get_contents('stop-order.json') ?>;

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

  var today = new Date();
  //today = new Date(2018,8,6,11,0,0);

  var uniqid = today.toISOString()+today.getMilliseconds();

  var date = "";
  if(today.getDate() <= 6) {
    // show the first day of xoxo if it's before the first day
    date = "8";
  } else if(today.getHours() >= 0 && today.getHours() <= 4) {
    // show yesterday if after midnight!
    date = ""+(today.getDate()-1);
  } else {
    date = ""+today.getDate();
  }

  var bounds = new L.LatLngBounds();

  for(var i in stops) {
    // If "show_on" is set, then this stop will appear on the map *only* on these dates
    if("show_on" in stops[i].properties && stops[i].properties.show_on.indexOf(date) == -1) {
      continue;
    }

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
    var stop_schedule = '';

    if(date in stops[i].properties.schedule) {
      stop_schedule = stops[i].properties.schedule[date];
    }
    marker.addTo(map);
    marker.bindPopup('<b>'+stops[i].properties.name+'</b><br>'+stops[i].properties.street+'<br>'+stop_schedule);

    bounds.extend(stopLocation);
  }

  map.fitBounds(bounds);

  map.on('dragstart', function(){
    autoPanBus = false;
    autoPanMe = false;
  });

  /*
  Thu: 11 AM - 1 AM
  Fri: 8 AM - 1 AM, 4 PM - 1 AM
  Sat: 8 AM - 1 AM
  Sun: 8 AM - 1 AM
  */

  // Show a warning when viewing the map outside the schedule times
  var schedule = [
    // Thursday
    {
      from: (new Date(2018,8,6,11,0,0)),
      to:   (new Date(2018,8,7,1,0,0))
    },
    // Friday
    {
      from: (new Date(2018,8,7,8,0,0)),
      to:   (new Date(2018,8,8,1,0,0))
    },
    // Saturday
    {
      from: (new Date(2018,8,8,8,0,0)),
      to:   (new Date(2018,8,9,1,0,0))
    },
    // Sunday
    {
      from: (new Date(2018,8,9,8,0,0)),
      to:   (new Date(2018,8,10,1,0,0))
    }
  ];
  var active = false;
  for(var i in schedule) {
    if(today >= schedule[i].from && today <= schedule[i].to) {
      active = true;
    }
  }

  if(active) {
    start_watching();
  } else {
    document.getElementById("header").classList.add("offline");
    document.getElementById("header-text").innerText = "Shuttle is not in service";
  }


  function start_watching() {
    // Load the inital data
    get_request('location.php', function(location) {
      for(var i=0; i<location.shuttles.length; i++) {
        data = location.shuttles[i];
        console.log(data);

        if(data.current) {
          bus[data.current.properties.device_id] = L.marker([data.current.geometry.coordinates[1], data.current.geometry.coordinates[0]], {
            icon: busIcon
          }).addTo(map);
          bus[data.current.properties.device_id].bindPopup(bus_popup(data.current.properties.timestamp, data.current.properties.device_id));
          routeHistoryLine[data.current.properties.device_id] = L.polyline(data.history, {
            "color": "#6A0A1C",
            "weight": 5,
            "opacity": 0.65
          }).addTo(map);
          map.panTo(new L.LatLng(data.current.geometry.coordinates[1], data.current.geometry.coordinates[0]));
        }
      }
      if(location.stops) {
        for(var i=0; i<location.stops.length; i++) {
          setShuttleCurrentStop(location.stops[i]);
        }
      }
    });

    // Wait for streaming data
    var pushstream = new PushStream({
      host: window.location.hostname,
      port: 443,
      useSSL: true,
      modes: "eventsource",
      urlPrefixEventsource: "/streaming/sub",
      channelsByArgument: true,
      channelsArgument: "id"
    });
    pushstream.onmessage = function(data,id,channel) {
      if(channel == 'shuttle') {
        if(routeHistoryLine[data.properties.device_id]) {
          routeHistoryLine[data.properties.device_id].addLatLng([data.geometry.coordinates[1],data.geometry.coordinates[0]]);
        }
        if(bus[data.properties.device_id]) {
          bus[data.properties.device_id].setLatLng([data.geometry.coordinates[1], data.geometry.coordinates[0]]);
          bus[data.properties.device_id].bindPopup(bus_popup(data.properties.timestamp, data.properties.device_id));

          if(autoPanBus && !map.getBounds().contains(bus[data.properties.device_id].getLatLng())) {
            map.panTo(bus[data.properties.device_id].getLatLng());
          }
        }
      } else if(channel == 'stop') {
        setShuttleCurrentStop(data);
      }
    }
    pushstream.addChannel('shuttle');
    pushstream.addChannel('stop');
    pushstream.connect();
  }

  /*
  function updateLocation() {
    get_request('location.php', function(data) {
      bus.setLatLng([data.current.geometry.coordinates[1], data.current.geometry.coordinates[0]]);
      bus.bindPopup(bus_popup(data.current.properties.date));
      map.removeLayer(routeHistory);
      routeHistory = L.geoJson(data.history, {
        style: {
          "color": "#257eca",
          "weight": 5,
          "opacity": 0.65
        }
      }).addTo(map);
      setTimeout(updateLocation, 2000);
    });
  }
  */

  function setShuttleCurrentStop(data) {
    // data contains the name of the current stop and the status (arrived, departed)
    // Figure out what index the current stop is given the current time of day
    var day = document.getElementById("schedule-day").value;
    var index = document.getElementById("schedule-index").value;

    // console.log(data);
    if(order[day] && order[day][index]) {
      var currentStops = order[day][index];
      // console.log(currentStops);
      for(var i=0; i<currentStops.length; i++) {
        if(currentStops[i] == data.stop) {
          document.getElementById("shuttle-"+data.shuttle).classList.add("stop"+(i+1));
        } else {
          document.getElementById("shuttle-"+data.shuttle).classList.remove("stop"+(i+1));
        }
        if(data.status == "departed") {
          document.getElementById("shuttle-"+data.shuttle).classList.add("departed");
        } else {
          document.getElementById("shuttle-"+data.shuttle).classList.remove("departed");
        }
      }
    }
  }

  function bus_popup(date_str, shuttle) {
    var contents = '';

    var date = moment(date_str);
    contents += '<b>' + shuttle + " shuttle: " + date.tz('America/Los_Angeles').format('h:mma') + '</b><br>' + date.fromNow();
    return contents;
  }

  if(!navigator.geolocation) {
    document.getElementById('locate-me').classList.add('hidden');
  }

  var lastLocationUpdate = false;

  function locateMe() {
    Cookies.set('locate-me', 1);
    document.getElementById('locate-me').classList.add("animate-thinking");

    if(navigator.geolocation) {
      navigator.geolocation.watchPosition(function(position){
        document.getElementById('locate-me').classList.remove("animate-thinking");

        autoPanBus = false;
        if(me == null) {
          me = L.marker([position.coords.latitude, position.coords.longitude], {
            icon: meIcon
          }).addTo(map);
        } else {
          me.setLatLng([position.coords.latitude, position.coords.longitude]);
        }
        if(autoPanMe) {
          map.panTo(new L.LatLng(position.coords.latitude, position.coords.longitude));
        }
        var now = Math.floor((new Date()).getTime()/1000);
        if(lastLocationUpdate == false || now - lastLocationUpdate > 5) {
          get_request('me.php?uniq='+uniqid+'&lat='+position.coords.latitude+'&lng='+position.coords.longitude, function(data){
            lastLocationUpdate = Math.floor((new Date()).getTime()/1000);
            for(var i=0; i<data.length; i++) {
              if(document.getElementById('stop-'+data[i].stop)) {
                var distance;
                if(data[i].distance < 0.1) {
                  distance = "You are here";
                } else {
                  distance = (Math.round(data[i].distance*10)/10)+" miles";
                  distance += " &bull; ";
                  distance += (Math.round(data[i].seconds/60))+" minutes walking";
                }
                document.querySelector("#stop-"+data[i].stop+" .distance").innerHTML = distance;
              }
            }
          });
        }
      });
    }
    return false;
  }

  document.addEventListener("DOMContentLoaded", function() {
    if(Cookies.get('locate-me') == 1) {
      locateMe();
    }
  });

</script>

</body>
</html>
