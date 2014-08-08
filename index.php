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
            var id = data[i]['id'];

            var lat = data[i]['lat'];
            var lng = data[i]['lng'];

            var marker = new google.maps.Marker({ position: new google.maps.LatLng(lat, lng), title: id});

            markers[script].push(marker);
        }

        showMarkers(markers[script]);
    }, 'json');
}

function hideAllMarkers() {
    for (v in markers) {
        hideMarkers(markers[v]);
    }
}

$(function() {
    $('input[type="button"]').click(function() {
        hideAllMarkers();

        loadMarkers($(this).attr('file'));
    });
});

</script>
</head>
<body>
    <div style="position: absolute; z-index: 999999; bottom: 10px; left: 10px">
        <input type="button" value="All" file="/ajax/markers.php">
        <input type="button" value="Grid" file="/ajax/markers_cluster1.php">
        <input type="button" value="Nearest neighbour (incomplete)" file="/ajax/markers_cluster2.php">
        <input type="button" value="Nearest neighbour (distance limited)" file="/ajax/markers_cluster3.php">
        <input type="button" value="C1 (1)" file="/ajax/markers_cluster4.php?level=1">
        <input type="button" value="C1 (2)" file="/ajax/markers_cluster4.php?level=2">
        <input type="button" value="C1 (3)" file="/ajax/markers_cluster4.php?level=3">
        <input type="button" value="C1 (4)" file="/ajax/markers_cluster4.php?level=4">
        <input type="button" value="C1 (5)" file="/ajax/markers_cluster4.php?level=5">
        <input type="button" value="C1 (6)" file="/ajax/markers_cluster4.php?level=6">
        <input type="button" value="C1 (7)" file="/ajax/markers_cluster4.php?level=7">
        <input type="button" value="C1 (8)" file="/ajax/markers_cluster4.php?level=8">
        <input type="button" value="C1 (9)" file="/ajax/markers_cluster4.php?level=9">
        <input type="button" value="C1 (10)" file="/ajax/markers_cluster4.php?level=10">
    </div>
    <div id="map-canvas" />
</body>
</html>
