<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');
require_once('maps.php');
require_once('sql/points.php');

define('PRECISION', 100000000);

for ($i = 0; $i < 1; $i++) {
    $random_lat = rand(MIN_MAPKIT_LAT * PRECISION, MAX_MAPKIT_LAT * PRECISION) / PRECISION;
    $random_lng = rand(-180 * PRECISION, 180 * PRECISION) / PRECISION;

    $point = \maps\toPoint($random_lat, $random_lng);
    $x = $point['x'];
    $y = $point['y'];

    \sql\points\insert($random_lat, $random_lng, $x, $y);
}

?>
