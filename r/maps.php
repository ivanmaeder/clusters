<?php

namespace maps;

define('MAPKIT_MAP_WIDTH',  268435456);
define('MAPKIT_MAP_HEIGHT', 268435456);

define('MIN_MAPKIT_LAT', -85);
define('MAX_MAPKIT_LAT', 85);
define('MIN_MAPKIT_Y', 439674.402484);
define('MAX_MAPKIT_Y', 267995781.597516);

/* PHP implementation of MapKit function MKMapPointForCoordinate.

   Formulas from:

   http://stackoverflow.com/questions/14329691/covert-latitude-longitude-point-to-a-pixels-x-y-on-mercator-projection
 */
function toPoint($lat, $lng) {
    $x = ($lng + 180) * (MAPKIT_MAP_WIDTH / 360);

    if ($lat >= MAX_MAPKIT_LAT) {
        $y = MIN_MAPKIT_Y;
    }
    elseif ($lat <= MIN_MAPKIT_LAT) {
        $y = MAX_MAPKIT_Y;
    }
    else {
        $y = (MAPKIT_MAP_HEIGHT / 2) - (MAPKIT_MAP_HEIGHT * log(tan((M_PI / 4) + (($lat * M_PI / 180) / 2))) / (2 * M_PI));
    }

    return array('x' => $x, 'y' => $y);
}

?>
