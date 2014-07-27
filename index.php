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

    loadMarkers('/ajax/markers.php');
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

$(function() {
    $('#map-canvas').click(function() {
        if (markers['/ajax/markers.php'][0].getMap() != null) {
            hideMarkers(markers['/ajax/markers.php']);

            loadMarkers('/ajax/markers_cluster1.php');
        } else if (markers['/ajax/markers_cluster1.php'][0].getMap() != null) {
            hideMarkers(markers['/ajax/markers_cluster1.php']);

            loadMarkers('/ajax/markers.php');
        }
    });
});

</script>
</head>
<body>
    <div id="map-canvas" />
</body>
</html>
