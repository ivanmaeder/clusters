<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('sql/points.php');

//Grid-based clustering algorithm

define('GRID_EDGE_SIZE', MAPKIT_MAP_WIDTH / 12);

$coordinates = array();

$x = 0;
$y = 0;

do {
    $result = \sql\points\fetchRectCount($x, $y, $x + GRID_EDGE_SIZE, $y + GRID_EDGE_SIZE);

    if ($result['COUNT(*)'] > 0) {
        $centroidX = $x + GRID_EDGE_SIZE / 2;
        $centroidY = $y + GRID_EDGE_SIZE / 2;

        $coordinate = \maps\toCoordinate($centroidX, $centroidY);
        array_push($coordinates, $coordinate);
    }

    $x += GRID_EDGE_SIZE;

    if ($x > MAPKIT_MAP_WIDTH) {
        $x = 0;
        $y += GRID_EDGE_SIZE;
    }
}
while (($y + GRID_EDGE_SIZE) <= MAPKIT_MAP_HEIGHT);

echo json_encode($coordinates);

?>
