<?php

define('DEBUG', FALSE);
$microtime = microtime(TRUE);

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('sql/cluster_children_tables.php');
require_once('sql/cluster_tables.php');
require_once('sql/points.php');
require_once('sql/proximity_index_tables.php');

/* Using the database to get a list of node pairs ordered by distance doesn't
   work out, performance degrades very quickly:
  
     - With   2,000 nodes, 0.5s +++++
     - With   4,000 nodes, 2.3s +++++++++++++++++++++++
  
   But finding nodes within a rectangle is fast and constant:
  
     - With   8,000 nodes, 0.02s
     - With  16,000 nodes, 0.03s
     - With  32,000 nodes, 0.03s
     - With  64,000 nodes, 0.04s
     - With 128,000 nodes, 0.09s +
     - With 256,000 nodes, 0.17s ++
  
   Taking advantage of this makes sense, but the result from a standard
   grid-based algorithm is awful.
  
   Alternative 1
   -------------
   Using a tiny grid size to begin with, create a cluster with the total
   number of nodes in each grid. Position the clusters in the average
   position of each node clustered and record the number of clustered
   nodes.
  
   Then repeat:
  
     1. Expand the cluster size
     2. Create a cluster with the total number of nodes, positioning the
        cluster at the center of all nodes, giving the original cluster a
        weight according to the number of nodes it clustered
   
   The hierarchy of clusters is defined in each iteration.
  
   But this doesn't work out: just iterating through the initial grid size
   makes the algorithm run much too slowly, no matter what the input is.
  
   Alternative 2
   -------------
   For every node, save a list of nodes close to that node (inside
   a rectangle around the node). This way we can perform the single-link
   algorithm to cluster the nodes at zoom level CURRENT - 1.
  
   Then repeat:
  
     1. Find the next set of closest neighbours
     2. Use the single-link algorithm to cluster this set
   
   The hierarchy of clusters is defined in each iteration.
  
   This file contains the implementation for Alternative 2.
  
   Performance is very slow, but it's not too far from linear, at least
   for a while:
  
     - With   1,000 nodes,   3s #
     - With   2,000 nodes,   8s ##
     - With   4,000 nodes,  20s #####
     - With   8,000 nodes,  43s ###########
     - With  16,000 nodes, 118s ##############################
  
   The script failed to finish before timing out with 32,000 nodes.
  
   About 90-95% of the time is spent in the first part of the algorithm:
   finding the closest neighbours for each point. Even if this time is
   distributed to the time of each point's INSERT, the same step is required
   in the next step of the hierarchy (there are fewer nodes every time,
   but not that much fewer).
  
   The proximity searched does make a difference in performance (even in
   the first part of the algorithm).
  
   Finding the distance of one node to all other nodes, takes:
  
     - 1 /  16,000 nodes, 0s
     - 1 /  32,000 nodes, 1s
     - 1 /  64,000 nodes, 1s
     - 1 / 128,000 nodes, 2s
     - 1 / 256,000 nodes, 3s
  
   Memory runs out in my configuration afterwards.
  
   Finding the distance of one node to all other nodes within the maximum
   1,000,000 points,
  
     - 1 /    16,000 nodes, 0.0167 ms
     - 1 /    32,000 nodes, 0.0232 ms
     - 1 /    64,000 nodes, 0.0465 ms
     - 1 /   128,000 nodes, 0.0905 ms
     - 1 /   256,000 nodes, 0.1690 ms
     - 1 /   512,000 nodes, 0.3398 ms
     - 1 / 1,024,000 nodes, 0.6859 ms
  
   This is O(n). Variations:
  
     - 1 / 1,024,000 nodes, 0.4108 ms (using ST_CONTAINS)
     - 1 / 1,024,000 nodes, 0.4321 ms (+ cross instead of rectangle)
     - 1 / 1,024,000 nodes, 0.3546 ms (+ hexagon instead)
     - 1 / 1,024,000 nodes, 0.3385 ms (+ removing unnecessary SELECT)

   To do this for all nodes, 96 hours.

   The same code with the shorter distance (100, instead of 1,000,000),

     - 1 / 1,024,000 nodes, 0.0024 ms

   To do this for all nodes, 40 minutes.

   NOTES
     - Making the heavy process work in bits at different times doesn't
       improve performance, it just shifts the work to a different time
     - Maybe there's no need to rebalance always. E.g., once things settle,
       most work will be done at the lower level, and things might shift a
       bit, but at the higher levels things might not have to change
     - Maybe this is faster as a stored procedure, or at least in Java

 */
define('PROXIMITY', 5000000);
//define('MAX_PROXIMITY', 1000000); //things > this far apart will never be clustered

//1. FIND NODE PAIRS WITH DISTANCE < PROXIMITY ////////////////////////////////

\sql\proximity_index_tables\truncate(1);

$points = \sql\points\fetchAll();

foreach ($points as $point) {
    $id_1 = $point['id'];

    //$microtime = microtime(TRUE);
    $nearbyPoints = \sql\points\fetchNearbyPoints($id_1, $point['x'], $point['y'], PROXIMITY);

    foreach ($nearbyPoints as $nearbyPoint) {
        $id_2 = $nearbyPoint['id'];
        $distance = $nearbyPoint['distance'];

        if ($distance < PROXIMITY) { //trim the corners off the rectangle
            \sql\proximity_index_tables\insert(1, $id_1, $id_2, $distance);
        }
    }
    //echo microtime(TRUE) - $microtime; echo "\n"; flush(); $microtime = microtime(TRUE);
    //exit();
}

if (DEBUG) {
    echo microtime(TRUE) - $microtime; echo "\n"; flush(); $microtime = microtime(TRUE);
}
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

if (DEBUG) {
    echo microtime(TRUE) - $microtime; echo "\n"; flush(); $microtime = microtime(TRUE);
}
//3. FIND CENTER OF NEW NODES /////////////////////////////////////////////////

$unlocated = \sql\cluster_tables\fetchUnlocated(1);

foreach ($unlocated as $cluster) {
    $x = $cluster['x'];
    $y = $cluster['y'];

    $coordinate = \maps\toCoordinate($x, $y);

    \sql\cluster_tables\update(1, $cluster['id'], $coordinate['lat'], $coordinate['lng'], $x, $y);
}

if (DEBUG) {
    echo microtime(TRUE) - $microtime; echo "\n"; flush(); $microtime = microtime(TRUE);
    exit();
}

//4. RETURN RESULT ////////////////////////////////////////////////////////////

$coordinates = array();

foreach (\sql\cluster_tables\fetchAll(1) as $coordinate) {
    array_push($coordinates, array('lat' => $coordinate['lat'], 'lng' => $coordinate['lng']));
}

echo json_encode($coordinates);

?>
