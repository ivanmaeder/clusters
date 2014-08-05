<?php
$time = time();
require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('sql/cluster_children_tables.php');
require_once('sql/cluster_tables.php');
require_once('sql/points.php');
require_once('sql/proximity_index_tables.php');

/* Using the database to get a list of node pairs ordered by distance doesn't
 * work out, performance degrades very quickly:
 *
 *   - With   2,000 nodes, 0.5s +++++
 *   - With   4,000 nodes, 2.3s +++++++++++++++++++++++
 *
 * But finding nodes within a rectangle is fast and constant:
 *
 *   - With   8,000 nodes, 0.02s
 *   - With  16,000 nodes, 0.03s
 *   - With  32,000 nodes, 0.03s
 *   - With  64,000 nodes, 0.04s
 *   - With 128,000 nodes, 0.09s +
 *   - With 256,000 nodes, 0.17s ++
 *
 * Taking advantage of this makes sense, but the result from a standard
 * grid-based algorithm is awful.
 *
 * Alternative 1
 * -------------
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
 * Alternative 2
 * -------------
 * For every node, save a list of nodes close to that node (inside
 * a rectangle around the node). This way we can perform the single-link
 * algorithm to cluster the nodes at zoom level CURRENT - 1.
 *
 * Then repeat:
 *
 *   1. Find the next set of closest neighbours
 *   2. Use the single-link algorithm to cluster this set
 * 
 * The hierarchy of clusters is defined in each iteration.
 *
 * This file contains the implementation for Alternative 2.
 *
 * Performance is very slow, but it's not too far from linear, at least
 * for a while:
 *
 *   - With   1,000 nodes,   3s #
 *   - With   2,000 nodes,   8s ##
 *   - With   4,000 nodes,  20s #####
 *   - With   8,000 nodes,  43s ###########
 *   - With  16,000 nodes, 118s ##############################
 *
 * The script failed to finish before timing out with 32,000 nodes.
 *
 * About 90-95% of the time is spent in the first part of the algorithm:
 * finding the closest neighbours for each point. Even if this time is
 * distributed to the time of each point's INSERT, the same step is required
 * in the next step of the hierarchy (there are fewer nodes every time,
 * but not that much fewer).
 *
 * The proximity searched does make a difference in performance (even in
 * the first part of the algorithm).
 */

define('PROXIMITY', 5000000);

//1. FIND NODES WITH DISTANCE < PROXIMITY /////////////////////////////////////

\sql\proximity_index_tables\truncate(1);

$points = \sql\points\fetchAll();

foreach ($points as $point) {
    $id_1 = $point['id'];

    $nearbyPoints = \sql\points\fetchNearbyPoints($id_1, PROXIMITY);

    foreach ($nearbyPoints as $nearbyPoint) {
        $id_2 = $nearbyPoint['id'];
        $distance = $nearbyPoint['distance'];

        if ($distance < PROXIMITY) { //trim the corners off the rectangle
            \sql\proximity_index_tables\insert(1, $id_1, $id_2, $distance);
        }
    }
}

//echo time() - $time; echo "\n"; flush(); $time = time();
//2. CLUSTER CLOSEST NODES ////////////////////////////////////////////////////

\sql\cluster_tables\truncate(1);
\sql\cluster_tables\copyPoints(1);

\sql\cluster_children_tables\truncate(1);

$pairs = \sql\proximity_index_tables\fetchAll(1);

$clusters = array();

foreach ($pairs as $pair) {
    $id_1 = $pair['id_1'];
    $id_2 = $pair['id_2'];

    $x = $pair['mid_x'];
    $y = $pair['mid_y'];

    $clusterId = NULL;

    if (isset($clusters[$id_1]) && isset($clusters[$id_2])) {
        continue;
    }
    else if (isset($clusters[$id_1])) {
        $clusterId = $clusters[$id_1];

        \sql\cluster_children_tables\insert(1, $id_2, $clusterId);
    }
    else if (isset($clusters[$id_2])) {
        $clusterId = $clusters[$id_2];

        \sql\cluster_children_tables\insert(1, $id_1, $clusterId);
    } else {
        $clusterId = \sql\cluster_tables\insert(1);

        \sql\cluster_children_tables\insert(1, $id_1, $clusterId);
        \sql\cluster_children_tables\insert(1, $id_2, $clusterId);
    }

    $clusters[$id_1] = $clusterId;
    $clusters[$id_2] = $clusterId;

    \sql\cluster_tables\delete(1, array($id_1, $id_2));
}

//echo time() - $time; echo "\n"; flush(); $time = time();
//3. FIND CENTER OF NEW NODES /////////////////////////////////////////////////

$unlocated = \sql\cluster_tables\fetchUnlocated(1);

foreach ($unlocated as $cluster) {
    $x = $cluster['x'];
    $y = $cluster['y'];

    $coordinate = \maps\toCoordinate($x, $y);

    \sql\cluster_tables\update(1, $cluster['id'], $coordinate['lat'], $coordinate['lng'], $x, $y);
}

//echo time() - $time; echo "\n"; flush(); $time = time();
//exit();
//4. RETURN RESULT ////////////////////////////////////////////////////////////

$coordinates = array();

foreach (\sql\cluster_tables\fetchAll(1) as $coordinate) {
    array_push($coordinates, array('lat' => $coordinate['lat'], 'lng' => $coordinate['lng']));
}

echo json_encode($coordinates);

?>
