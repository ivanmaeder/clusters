<?php
$time = time();
//require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

//require_once('maps.php');
//require_once('sql/points.php');
require_once('../r/maps.php');
require_once('../r/sql/points.php');

/* I have no idea how this is going to look. It's supposed to be less
 * like a grid because the cluster is moved and the grids shift.
 *
 * Using a tiny grid size to begin with, create a cluster with the total
 * number of nodes in each grid. Position the clusters in the average
 * position of each node clustered and record the number of clustered
 * nodes.
 *
 * Then repeat:
 *
 *   1. Expand the cluster size
 *   2. Create a cluster with the total number of nodes, positioning the
 *      cluster at the center of all nodes, giving the original cluster a
 *      weight according to the number of nodes it clustered
 * 
 * The hierarchy of clusters is defined in each iteration.
 *
 * REMEMBER! When comparing performance with previous, the others are doing
 * just one level, here we're doing more.
 *
 * Starting with a tiny grid is impossible. A grid size 16 times too big
 * takes 256s without doing any actual work. The input size doesn't matter.
 */

define('GRID_EDGE_SIZE', 1024 * 128); //this is too big, but 1024 (the right size) takes a billion years
//1024 * 64 =  15s
//1024 * 32 =  59s
//1024 * 16 = 256s

$grid_x = 0;
$grid_y = 0;

while (moreGridPositionsAvailable($grid_x, $grid_y)) {
    list($grid_x, $grid_y) = nextGridPosition($grid_x, $grid_y);
}

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
echo time() - $time;
echo "\n";
?>
