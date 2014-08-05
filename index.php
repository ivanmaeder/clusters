<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
html {
    height: 100%;
}
body {
    height: 100%;
    margin: 0;
    padding: 0;
}
#map-canvas {
    height: 100%;
}
</style>
<script type="text/javascript" src="/js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2-VE-7sYKxKHnmfHDw7eNFQ-CneEs6Oo"></script>
<script type="text/javascript">

google.maps.event.addDomListener(window, 'load', initialize);

var map;
var markers = [];

function initialize() {
    var mapOptions = {
        center: new google.maps.LatLng(0, 0),
        zoom: 2
    };

    map = new google.maps.Map(document.getElementById("map-canvas"),
        mapOptions);
}

function showMarkers(markers) {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(map);
    }
}

function hideMarkers(markers) {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
}

function loadMarkers(script) {
    $.get(script, function(data) {
        markers[script] = [];

        for (var i = 0; i < data.length; i++) {
            var lat = data[i]['lat'];
            var lng = data[i]['lng'];

            var marker = new google.maps.Marker({ position: new google.maps.LatLng(lat, lng)});

            markers[script].push(marker);
        }

        showMarkers(markers[script]);
    }, 'json');
}

function hideAllMarkers() {
    if (markers['/ajax/markers.php'] != null) {
        hideMarkers(markers['/ajax/markers.php']);
    }

    if (markers['/ajax/markers_cluster1.php'] != null) {
        hideMarkers(markers['/ajax/markers_cluster1.php']);
    }

    if (markers['/ajax/markers_cluster2.php'] != null) {
        hideMarkers(markers['/ajax/markers_cluster2.php']);
    }

    if (markers['/ajax/markers_cluster3.php'] != null) {
        hideMarkers(markers['/ajax/markers_cluster3.php']);
    }
}

$(function() {
    $('#0').click(function() {
        hideAllMarkers();

        loadMarkers('/ajax/markers.php');
    });

    $('#1').click(function() {
        hideAllMarkers();

        loadMarkers('/ajax/markers_cluster1.php');
    });

    $('#2').click(function() {
        hideAllMarkers();

        loadMarkers('/ajax/markers_cluster2.php');
    });

    $('#3').click(function() {
        hideAllMarkers();

        loadMarkers('/ajax/markers_cluster3.php');
    });
});

</script>
</head>
<body>
    <div style="position: absolute; z-index: 999999; bottom: 10px; left: 10px">
        <input type="button" id="0" value="All">
        <input type="button" id="1" value="Grid">
        <input type="button" id="2" value="Nearest neighbour (incomplete)">
        <input type="button" id="3" value="Nearest neighbour (distance limited)">
    </div>
    <div id="map-canvas" />
</body>
</html>
