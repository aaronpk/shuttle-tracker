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
    #header {
      width: 100%;
      color: black;
      font-size: 18px;
    }
    #header div {
      padding: 6px;
      padding-left: 12px;
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

<div id="header"><div style="font-weight: bold; float: left;">XOXO</div><div style="float: right;">Shuttle Tracker</div></div>
<div id="locate-me"><a href="javascript:locateMe();"></a></div>
<div id="map"></div>

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
  
  var bus = null;
  var me = null;
  var routeHistoryLine = null;
  var autoPanBus = true;
  var autoPanMe = true;

  L.esri.basemapLayer('Gray').addTo(map);

  map.attributionControl.setPrefix('shuttle tracker by <a href="http://aaronparecki.com/">aaronpk</a> | <a href="http://leafletjs.com/">Leaflet</a>');

/*
  var route1 = {"type":"LineString","coordinates":[[-122.65769365902497,45.514720684902905],[-122.65769365902497,45.514380768528405],[-122.6586818058375,45.514380768528405],[-122.65867282268466,45.51293295356695],[-122.66077488044951,45.51293295356695],[-122.66602104170877,45.51293295356695],[-122.66646121619797,45.51281964473755],[-122.66690139068719,45.51263709114366],[-122.66737749778778,45.51251119176838],[-122.66787157119404,45.51242935702343],[-122.67398011512607,45.514009376068394],[-122.67425859286413,45.51414786167903],[-122.6744023233096,45.51426116783426],[-122.67453707060221,45.514412242353025],[-122.67476164942323,45.514758453262225],[-122.67497724509143,45.51484028462046],[-122.67520182391247,45.51480251631606],[-122.67610912234942,45.513058851998736],[-122.67515690814827,45.51281964473755],[-122.67539047012212,45.51237899712122],[-122.67543538588633,45.5121397869704],[-122.67521080706531,45.512341227165],[-122.67438435700392,45.513952722765815],[-122.67248891175441,45.51743994198533],[-122.67456402006073,45.51798755425165],[-122.67382740152775,45.51932194343546],[-122.68342140876216,45.52187733627626],[-122.6831249647184,45.52238084827151],[-122.68309801525989,45.52255707640525],[-122.68154392981833,45.52214168063564],[-122.6785435567694,45.52134864383142],[-122.67892084918871,45.520681477227434],[-122.68066358083989,45.51733923110329],[-122.67964848456886,45.51708115864536],[-122.67960356880464,45.517169281081145],[-122.67861542199212,45.51907017431345],[-122.67822914641994,45.51973736002385],[-122.67782490454209,45.52037936144591],[-122.67677387565966,45.522399729883674],[-122.67742066266425,45.52260113335244],[-122.67735778059433,45.522840299035146],[-122.67739371320572,45.52295988149523],[-122.67744761212278,45.52304170092673],[-122.67437537385108,45.52309205128698],[-122.67020719093276,45.523098345078836],[-122.66918311150886,45.52307946370114],[-122.66578747973487,45.52301023192871],[-122.66176302726204,45.52294729387983],[-122.65713670354883,45.522922118640565],[-122.65665161329538,45.522922118640565],[-122.65665161329538,45.520977297358264],[-122.6565887312255,45.52078218212744],[-122.65778349055337,45.5193471202857],[-122.65856502485057,45.51864845851023],[-122.65866383953183,45.518428157953224],[-122.65867282268466,45.51830856586107],[-122.65867282268466,45.51507948329302],[-122.65769365902497,45.51507948329302],[-122.65769365902497,45.514720684902905]],"bbox":[-122.68342140876216,45.5121397869704,-122.6565887312255,45.523098345078836]};
  var route2 = {"type":"LineString","coordinates":[[-122.65769365902497,45.514720684902905],[-122.65772060848349,45.5165209485049],[-122.65566346648285,45.5165209485049],[-122.65566346648285,45.51720704779709],[-122.65534007298058,45.51720704779709],[-122.66073894783814,45.51723222559364],[-122.66480831607518,45.51723852004102],[-122.66639833412809,45.517251108933664],[-122.66802428479234,45.5173707032733],[-122.67164449538734,45.51835892045717],[-122.67187805736123,45.51849110105753],[-122.67230026554475,45.51864845851023],[-122.6725517938243,45.518956877841006],[-122.67258772643567,45.51908905703667],[-122.67257874328283,45.519240118594375],[-122.67250687806009,45.5193471202857],[-122.67240806337884,45.51942894497119],[-122.67231823185044,45.519473004367924],[-122.67205772041805,45.519473004367924],[-122.6718511079027,45.51932194343546],[-122.6717433100686,45.51908905703667],[-122.67205772041805,45.51856033839093],[-122.67368367108229,45.515488636560164],[-122.6744023233096,45.51426116783426],[-122.67539047012212,45.51237899712122],[-122.67543538588633,45.5121397869704],[-122.67521080706531,45.512341227165],[-122.67438435700392,45.513952722765815],[-122.67248891175441,45.51743994198533],[-122.67456402006073,45.51798755425165],[-122.67382740152775,45.51932194343546],[-122.68342140876216,45.52187733627626],[-122.6831249647184,45.52238084827151],[-122.68309801525989,45.52255707640525],[-122.68154392981833,45.52214168063564],[-122.6785435567694,45.52134864383142],[-122.67892084918871,45.520681477227434],[-122.68066358083989,45.51733923110329],[-122.67964848456886,45.51708115864536],[-122.67960356880464,45.517169281081145],[-122.67861542199212,45.51907017431345],[-122.67822914641994,45.51973736002385],[-122.67782490454209,45.52037936144591],[-122.67677387565966,45.522399729883674],[-122.67742066266425,45.52260113335244],[-122.67735778059433,45.522840299035146],[-122.67739371320572,45.52295988149523],[-122.67744761212278,45.52304170092673],[-122.67437537385108,45.52309205128698],[-122.67020719093276,45.523098345078836],[-122.66918311150886,45.52307946370114],[-122.66578747973487,45.52301023192871],[-122.66176302726204,45.52294729387983],[-122.65713670354883,45.522922118640565],[-122.65665161329538,45.522922118640565],[-122.65665161329538,45.520977297358264],[-122.6565887312255,45.52078218212744],[-122.65778349055337,45.5193471202857],[-122.65856502485057,45.51864845851023],[-122.65866383953183,45.518428157953224],[-122.65867282268466,45.51830856586107],[-122.65867282268466,45.51507948329302],[-122.65769365902497,45.51507948329302],[-122.65769365902497,45.514720684902905]],"bbox":[-122.68342140876216,45.5121397869704,-122.65534007298058,45.523098345078836]};

  L.geoJson(route1, {
    style: {
      "color": "#7800ff",
      "weight": 5,
      "opacity": 0.65
    }
  })//.addTo(map);

  L.geoJson(route2, {
    style: {
      "color": "#ff7800",
      "weight": 5,
      "opacity": 0.65
    }
  })//.addTo(map);
*/
    
  var stops = <?= file_get_contents('stops.geojson') ?>;

  var icons = [];
  
  var busIcon = L.icon({
    iconUrl: '/images/bus.png',
    iconRetinaUrl: '/images/bus@2x.png',
    iconSize: [27, 31],
    iconAnchor: [13.5, 31],
    popupAnchor: [0, -11]
  });

  var meIcon = L.icon({
    iconUrl: '/images/me.gif',
    iconRetinaUrl: '/images/me@2x.gif',
    iconSize: [16, 16],
    iconAnchor: [8, 8],
    popupAnchor: [0, 8]
  });

  var today = new Date();
  //today = new Date(2015,9,13,9,0,0);
  
  var date = "";
  if(today.getDate() <= 9) {
    // show the first day of xoxo if it's before the first day
    date = "10";
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
    var schedule = '';
    
    if(date in stops[i].properties.schedule) {
      schedule = stops[i].properties.schedule[date];
    }
    marker.addTo(map);
    marker.bindPopup('<b>'+stops[i].properties.Name+'</b><br>'+stops[i].properties.street+'<br>'+schedule);

    bounds.extend(stopLocation);
  }

  map.fitBounds(bounds);

  map.on('movestart', function(){
    autoPanBus = false;
    autoPanMe = false;
  });

  // Load the inital data
  get_request('location.php', function(data) {
    bus = L.marker([data.current.geometry.coordinates[1], data.current.geometry.coordinates[0]], {
      icon: busIcon
    }).addTo(map);
    bus.bindPopup(bus_popup(data.current.properties.date));
    routeHistoryLine = L.polyline(data.history, {
      "color": "#257eca",
      "weight": 5,
      "opacity": 0.65
    }).addTo(map);
    map.panTo(new L.LatLng(data.current.geometry.coordinates[1], data.current.geometry.coordinates[0]));
  });

  // Wait for streaming data
  var pushstream = new PushStream({
    host: window.location.hostname,
    port: 80,
    useSSL: false,
    modes: "eventsource",
    urlPrefixEventsource: "/streaming/sub",
    channelsByArgument: true,
    channelsArgument: "id"
  });
  pushstream.onmessage = function(data,id,channel) {
    routeHistoryLine.addLatLng([data.geometry.coordinates[1],data.geometry.coordinates[0]]);
    bus.setLatLng([data.geometry.coordinates[1], data.geometry.coordinates[0]]);

    if(autoPanBus && !map.getBounds().contains(bus.getLatLng())) {
      map.panTo(bus.getLatLng());
    }
  }
  pushstream.addChannel('shuttle');
  pushstream.connect();

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

  function bus_popup(date_str) {
    var contents = '';
    
    var date = moment(date_str);
    contents += '<b>' + date.tz('America/Los_Angeles').format('h:mma') + '</b><br>' + date.fromNow();
    return contents;
  }
  
  if(!navigator.geolocation) {
    document.getElementById('locate-me').classList.add('hidden');
  }

  function locateMe() {
    if(navigator.geolocation) {
      navigator.geolocation.watchPosition(function(position){
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
      });
    }
    return false;
  }

</script>

</body>
</html>