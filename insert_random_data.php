<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');
require_once('maps.php');
require_once('sql/points.php');

/*
$x = 152031575.248560;
$y =  79827630.933874;

for ($i = 1; $i < 100; $i++) {
    $x += $i * 64;

    $coordinate = \maps\toCoordinate($x, $y);

    \sql\points\insert($coordinate['lat'], $coordinate['lng'], $x, $y);
}
*/

define('PRECISION', 100000000);

for ($i = 0; $i < 8000; $i++) {
    $random_lat = rand(MIN_MAPKIT_LAT * PRECISION, MAX_MAPKIT_LAT * PRECISION) / PRECISION;
    $random_lng = rand(-180 * PRECISION, 180 * PRECISION) / PRECISION;

    $point = \maps\toPoint($random_lat, $random_lng);
    $x = $point['x'];
    $y = $point['y'];

    \sql\points\insert($random_lat, $random_lng, $x, $y);
}

?>
