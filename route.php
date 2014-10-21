<html>
<head>
  <meta charset=utf-8 />
  <title>XOXO Shuttle</title>
  <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

  <!-- Load Leaflet from CDN-->
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
  <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>

  <!-- Load Esri Leaflet from CDN -->
  <script src="http://cdn-geoweb.s3.amazonaws.com/esri-leaflet/0.0.1-beta.5/esri-leaflet.js"></script>

  <script src="http://cdn-geoweb.s3.amazonaws.com/terraformer/1.0.4/terraformer.min.js"></script>
  <script src="http://cdn-geoweb.s3.amazonaws.com/terraformer-arcgis-parser/1.0.4/terraformer-arcgis-parser.min.js"></script>

  <script src="http://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script src="assets/jquery.cookie.js"></script>

  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

  <style type="text/css">
    body {
      margin: 0;
      padding: 0;
      background: #eee;
    }
    #map {
      position: absolute;
      top: 0;
      bottom: 0;
      right: 0;
      left: 0;
      margin-right: 500px;
    }
    #controls {
      position: absolute;
      top: 0;
      right: 0;
      width: 200px;
      overflow-y: scroll;
    }
    #results {
      position: absolute;
      top: 0;
      right: 200px;
      width: 300px;
      overflow-y: scroll;
    }
    #results ul {
      padding: 0;
      margin: 0;
      list-style-type: none;
    }
    #results ul li {
      padding: 3px 0;
      padding-left: 30px;
      margin: 0;
    }
    #results li img {
      margin-right: 4px;
      margin-left: -26px;
    }
    .pad {
      padding: 6px;
    }
  </style>
</head>
<body>

<div id="map"></div>

<div id="results"><div class="pad">
  <h3>Summary</h3>
  <div id="route_summary"></div>
  <h3>Directions</h3>
  <ul></ul>
</div></div>

<div id="controls"><div class="pad">
  <input type="button" value="Sign In" id="sign-in" class="btn btn-primary">
  <br><br>

  <div class="checkbox">
    <input type="checkbox" id="chk_reorder" checked="checked"> Find Best Route
  </div>

  <div class="checkbox">
    <input type="checkbox" id="chk_preventuturns"> Avoid U-Turns
  </div>

  <div class="checkbox">
    <input type="checkbox" id="chk_hawthorne"> Avoid Hawthorne Bridge
  </div>

  <input type="button" value="Route" id="route" class="btn btn-success">

  <br><br>
  
  <?php
  $stops = json_decode(file_get_contents('stops.geojson'));
  foreach($stops as $i=>$stop):
  ?>
    <div class="form-group">
      <label><?= $stop->properties->Name ?></label>
      <select class="form-control" id="stop_<?= $i ?>_approach">
        <option value="0" <?= $stop->properties->CurbApproach == 0 ? 'selected="selected"' : '' ?>>Either Side</option>
        <option value="3" <?= $stop->properties->CurbApproach == 3 ? 'selected="selected"' : '' ?>>Either Side No U-Turn</option>
        <option value="2" <?= $stop->properties->CurbApproach == 2 ? 'selected="selected"' : '' ?>>Left Side</option>
        <option value="1" <?= $stop->properties->CurbApproach == 1 ? 'selected="selected"' : '' ?>>Right Side</option>
      </select>
  
      <div style="margin-top: 3px;">
        <input type="text" value="<?= $stop->properties->Attr_TravelTime?>" class="form-control input-sm" style="height: 20px; width: 40px; float: left; margin-right: 4px;">
        <span>Minutes</span>
      </div>
    </div>
  <?php
  endforeach;
  ?>
  
</div></div>

<script type='text/javascript'>
  var client_id = 'tb05i4SX2CrKuobn';
  var access_token = '';
  
  if($.cookie("ago")) {
    access_token = $.cookie("ago");
    $("#sign-in").removeClass('btn-primary');
  }
  
  $("#controls").css("height", $(window).height());
  
  var callback_url = 'http://pin13.net/xoxo/oauth.php';

  var route_service = "https://route.arcgis.com/arcgis/rest/services/World/Route/NAServer/Route_World/solve";

  var map = L.map('map').setView([45.51798525, -122.669760], 15);
  L.esri.basemapLayer('Gray').addTo(map);

  var stops = <?= file_get_contents('stops.geojson') ?>;
  
  var route;

  for(var i in stops) {
    L.marker(new L.LatLng(stops[i].geometry.coordinates[1], stops[i].geometry.coordinates[0]), {
      icon: L.icon({
        iconUrl: 'images/'+stops[i].properties.icon+'.png',
        iconRetinaUrl: 'images/'+stops[i].properties.icon+'@2x.png',
        iconSize: [24, 29],
        iconAnchor: [12, 29],
        popupAnchor: [0, -10],
      })
    }).addTo(map);
  }

  var arcgis_stops = Terraformer.ArcGIS.convert({
    type: "FeatureCollection",
    features: stops
  });
  
  arcgis_stops.push(arcgis_stops[0]);
  arcgis_stops[arcgis_stops.length-1].attributes.Attr_TravelTime = 0;

  $(function(){
    $("#sign-in").click(function(){
      window.open('https://www.arcgis.com/sharing/oauth2/authorize?client_id='+client_id+'&response_type=token&expiration=20160&redirect_uri=' + window.encodeURIComponent(callback_url), 'oauth-window', 'height=400,width=600,menubar=no,location=yes,resizable=yes,scrollbars=yes,status=yes');
    });
    
    $("#route").click(function(){
    
      // Set the curb approach property for each stop
      for(var i in arcgis_stops) {
        if($("#stop_"+(i%5)+"_approach").val() == 0) {
          delete arcgis_stops[i].attributes.CurbApproach;
        } else {
          arcgis_stops[i].attributes.CurbApproach = $("#stop_"+(i%5)+"_approach").val();
        }
      }
    
      var params = {
        stops: JSON.stringify({
          features: arcgis_stops
        }),
        returnDirections: true,
        returnStops: true,
        findBestSequence: $("#chk_reorder").prop("checked"),
        preserveFirstStop: true,
        preserveLastStop: true,
        ignoreInvalidLocations: false,
        useHierarchy: true,
        restrictUTurns: ($("#chk_preventuturns").prop("checked") ? 'esriNFSBNoBacktrack' : 'esriNFSBAllowBacktrack'),
        token: access_token,
        f: 'json'
      };
      
      if($("#chk_hawthorne").prop("checked")) {
        params.barriers = "-122.670421600341,45.51308360513236";
      }
      
      $.post(route_service, params, function(response){
        if(route) {
          map.removeLayer(route);
        }

        var directions = JSON.parse(response);
        console.log(directions);
        $("#route_summary").html(''+Math.round(directions.routes.features[0].attributes.Total_Miles)+' miles<br>'
          +Math.round(directions.routes.features[0].attributes.Total_TravelTime)+' minutes');
        
        $("#results ul").empty();
        for(var i in directions.directions[0].features) {
          var step = directions.directions[0].features[i];
          $("#results ul").append('<li><img src="http://servicesbeta.esri.com/demos/3.11/api/js/esri/dijit/images/Directions/maneuvers/'+step.attributes.maneuverType+'.png">'+step.attributes.text+'</li>');
        }
        
        var feature = Terraformer.ArcGIS.parse(directions.routes.features[0]);

        var total_miles = directions.routes.features[0].attributes.Total_Miles;
        var total_minutes = directions.routes.features[0].attributes.Total_TravelTime;
        
        route = L.geoJson(feature, {
          style: {
            "color": "#7800ff",
            "weight": 5,
            "opacity": 0.65
          }
        }).addTo(map);
        
        
      });
      
    });
  });
  
  window.oauthCallback = function(token, expires) {
    access_token = token;
    $.cookie("ago", token, {expires: expires/86400});
    $("#sign-in").removeClass('btn-primary');
  };
  
</script>

</body>
</html>