<?php

namespace maps;

define('MAPKIT_MAP_WIDTH',  268435456);
define('MAPKIT_MAP_HEIGHT', 268435456);

define('MIN_LAT',  -90);
define('MAX_LAT',   90);
define('MIN_LNG', -180);
define('MAX_LNG',  180);

define('MIN_MAPKIT_LAT', -85);
define('MAX_MAPKIT_LAT',  85);

/* PHP implementation of the MapKit MKMapPointForCoordinate function.
 *
 * Formulas from: http://stackoverflow.com/questions/14329691/
 *
 * Regarding the +/-85 latitude cutoff: https://code.google.com/p/gmaps-api-issues/issues/detail?id=6391
 */
function toPoint($lat, $lng) {
    if (!isValidCoordinate($lat, $lng)) {
        return array('x' => -1.000000, 'y' => -1.000000);
    }

    if ($lat < MIN_MAPKIT_LAT) {
        $lat = MIN_MAPKIT_LAT;
    }

    if ($lat > MAX_MAPKIT_LAT) {
        $lat = MAX_MAPKIT_LAT;
    }

    $x = ($lng + 180) * (MAPKIT_MAP_WIDTH / 360);
    $y = (MAPKIT_MAP_HEIGHT / 2) - (MAPKIT_MAP_HEIGHT * log(tan((M_PI / 4) + (($lat * M_PI / 180) / 2))) / (2 * M_PI));

    return array('x' => $x, 'y' => $y);
}

function isValidCoordinate($lat, $lng) {
    return $lat >= MIN_LAT && $lat <= MAX_LAT && $lng >= MIN_LNG && $lng <= MAX_LNG;
}

function toCoordinate($x, $y) {
    if ($x > MAPKIT_MAP_WIDTH) {
        $lng = -180;
    }
    else {
        $lng = ($x - (MAPKIT_MAP_WIDTH / 2)) / (MAPKIT_MAP_WIDTH / 360);
    }

    $lat = (atan(exp(($y - MAPKIT_MAP_WIDTH / 2) / -MAPKIT_MAP_WIDTH * (2 * M_PI))) - (M_PI / 4)) * 2 * 180 / M_PI;

    return array('lat' => $lat, 'lng' => $lng);
}

?>
