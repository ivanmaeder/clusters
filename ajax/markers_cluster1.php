<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('sql/points.php');

/* Grid-based clustering algorithm.  
 *
 * Not bad for sparsely populated maps, otherwise the grid is obvious.
 */

define('GRID_EDGE_SIZE', MAPKIT_MAP_WIDTH / 16);

$coordinates = array();

$grid_x = 0;
$grid_y = 0;

while (moreGridPositionsAvailable($grid_x, $grid_y)) {
    $result = \sql\points\fetchAverageForRect($grid_x, $grid_y, $grid_x + GRID_EDGE_SIZE, $grid_y + GRID_EDGE_SIZE);

    if ($result['avg_lat']) {
        $coordinate = array('lat' => $result['avg_lat'], 'lng' => $result['avg_lng']);

        array_push($coordinates, $coordinate);
    }

    list($grid_x, $grid_y) = nextGridPosition($grid_x, $grid_y);
}

echo json_encode($coordinates);

function moreGridPositionsAvailable($x, $y) {
    return ($y + GRID_EDGE_SIZE) <= MAPKIT_MAP_HEIGHT;
}

function nextGridPosition($x, $y) {
    $x += GRID_EDGE_SIZE;

    if ($x > MAPKIT_MAP_WIDTH) {
        $x = 0;
        $y += GRID_EDGE_SIZE;
    }

    return array($x, $y);
}

?>
