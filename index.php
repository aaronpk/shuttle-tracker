<html>
<head>
  <meta charset=utf-8 />
  <title>XOXO Shuttle</title>
  <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

  <link rel="stylesheet" href="/assets/leaflet.css" />

  <!--
  <script src="/assets/leaflet.js"></script>
  <script src="/assets/esri-leaflet.js"></script>
  <script src="/assets/moment.min.js"></script>
  <script src="/assets/moment-timezone.min.js"></script>
  <script type="text/javascript" src="/assets/pushstream.js"></script>
  <script type="text/javascript" src="/assets/js-cookie.js"></script>
  -->
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
    #container {
      height: 100vh;
      overflow-y: hidden;
    }
    #header {
      height: 36px;
      width: 100%;
    }
    #map {
      width: 100%;
      height: 50vh;
    }
    #stops {
      width: 100%;
      height: calc(50vh - 36px);
      background: #fff;
      overflow-y: scroll;
    }
    #stops .pad {
      padding: 10px;
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
/*    #map {
      position: absolute;
      top:36px;
      bottom:0;
      right:0;
      left:0;
    }
*/
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
      border-top: 1px #ccc solid;

      height: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    #stops ul li .in {
      padding-left: 40px;
    }
    #stops ul li:last-child {
      border-bottom: 1px #ccc solid;
    }
    #stops .distance {
      font-size: 12px;
      color: #888;
    }

    #stops {
      position: relative;
    }
    #current-stop {
      position: absolute;
      left: 16px;
      top: 0;
      display: none;
    }

    #current-stop.stop1,
    #current-stop.stop2,
    #current-stop.stop3,
    #current-stop.stop4,
    #current-stop.stop5,
    #current-stop.stop6,
    #current-stop.stop7 {
      display: inline-block;
    }

    #current-stop.stop1 {
      top: 51px;
    }
    #current-stop.stop1.departed {
      top: 79px;
    }

    #current-stop.stop2 {
      top: 108px;
    }
    #current-stop.stop2.departed {
      top: 134px;
    }

    #current-stop.stop3 {
      top: 163px;
    }
    #current-stop.stop3.departed {
      top: 190px;
    }

    #current-stop.stop4 {
      top: 218px;
    }
    #current-stop.stop4.departed {
      top: 245px;
    }

    #current-stop.stop5 {
      top: 273px;
    }
    #current-stop.stop5.departed {
      top: 300px;
    }

    #current-stop.stop6 {
      top: 329px;
    }
    #current-stop.stop6.departed {
      top: 356px;
    }

    #current-stop.stop7 {
      top: 384px;
    }
    #current-stop.stop7.departed {
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
        $now->setTimeZone(new DateTimeZone('US/Pacific'));
        if($now->format('H') <= 3)
          $today = $now->sub(new DateInterval('PT6H'));
        $today = $now->format('j');
        if($now->format('H') >= 18)
          $index = 1;
        else 
          $index = 0;
        if(array_key_exists($today, $stops)):
          ?>
          <h3><?= $now->format('M j') ?> <?= $index == 0 ? 'Morning' : 'Night' ?> Schedule</h3>
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
    <img src="/images/bus-right@2x.png" width="29" id="current-stop">
    <input type="hidden" id="schedule-day" value="<?= $today ?>">
    <input type="hidden" id="schedule-index" value="<?= $index ?>">
  </div>
</div>

<script type='text/javascript'>
  moment.tz.add('America/Los_Angeles|PST PDT|80 70|0101|1Lzm0 1zb0 Op0');

  function get_request(url, callback) {
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
  }

  var map = L.map('map');
  
  var bus = [];
  var routeHistoryLine = [];
  var me = null;
  var autoPanBus = true;
  var autoPanMe = true;

  L.esri.basemapLayer('Gray').addTo(map);

  map.attributionControl.setPrefix('shuttle tracker by <a href="http://aaronparecki.com/">aaronpk</a> | <a href="http://leafletjs.com/">Leaflet</a>');
    
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
  // today = new Date(2016,9,9,9,0,0);
  
  var uniqid = today.toISOString()+today.getMilliseconds();
  
  var date = "";
  if(today.getDate() <= 7) {
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
    marker.bindPopup('<b>'+stops[i].properties.Name+'</b><br>'+stops[i].properties.street+'<br>'+stop_schedule);

    bounds.extend(stopLocation);
  }

  map.fitBounds(bounds);

  map.on('dragstart', function(){
    autoPanBus = false;
    autoPanMe = false;
  });

  // Show a warning when viewing the map outside the schedule times
  var schedule = [
    {
      from: (new Date(2016,8,8,18,0,0)),
      to:   (new Date(2016,8,9,2,0,0))
    },
    {
      from: (new Date(2016,8,9,9,0,0)),
      to:   (new Date(2016,8,10,2,0,0))
    },
    {
      from: (new Date(2016,8,10,9,0,0)),
      to:   (new Date(2016,8,11,2,0,0))
    },
    {
      from: (new Date(2016,8,11,9,0,0)),
      to:   (new Date(2016,8,12,2,0,0))
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

        bus[data.current.properties.shuttle] = L.marker([data.current.geometry.coordinates[1], data.current.geometry.coordinates[0]], {
          icon: busIcon
        }).addTo(map);
        bus[data.current.properties.shuttle].bindPopup(bus_popup(data.current.properties.date));
        routeHistoryLine[data.current.properties.shuttle] = L.polyline(data.history, {
          "color": "#257eca",
          "weight": 5,
          "opacity": 0.65
        }).addTo(map);
        map.panTo(new L.LatLng(data.current.geometry.coordinates[1], data.current.geometry.coordinates[0]));
      }
      if(location.stop) {
        setShuttleCurrentStop(location.stop);
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
        routeHistoryLine[data.properties.shuttle].addLatLng([data.geometry.coordinates[1],data.geometry.coordinates[0]]);
        bus[data.properties.shuttle].setLatLng([data.geometry.coordinates[1], data.geometry.coordinates[0]]);
        bus[data.properties.shuttle].bindPopup(bus_popup(data.properties.date));

        if(autoPanBus && !map.getBounds().contains(bus[data.properties.shuttle].getLatLng())) {
          map.panTo(bus[data.properties.shuttle].getLatLng());
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
          document.getElementById("current-stop").classList.add("stop"+(i+1));
        } else {
          document.getElementById("current-stop").classList.remove("stop"+(i+1));
        }
        if(data.status == "departed") {
          document.getElementById("current-stop").classList.add("departed");
        } else {
          document.getElementById("current-stop").classList.remove("departed");
        }
      }
    }
  }

  function bus_popup(date_str) {
    var contents = '';
    
    var date = moment(date_str);
    contents += '<b>' + date.tz('America/Los_Angeles').format('h:mma') + '</b><br>' + date.fromNow();
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