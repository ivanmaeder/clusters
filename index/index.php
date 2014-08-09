<?php

/* This fries the CPU while it's working, the fans can't keep up.

     ------  --------------  --------------  --------------  -----  -----
     Points  Time taken      Time taken      Time taken      Tests  Cache
             min (s)         max (s)         average (s)           size
     ------  --------------  --------------  --------------  -----  -----
        500   15.5763900280   17.7834279537   16.6462629636      3      ?
      1,000   28.4845070838   31.9279808998   30.2062439918      2    250
      2,000   56.3349969387   83.1703760624   69.7526865006      2    600
      4,000  116.8473930359  122.7696211338  119.8085070849      2   1300
      8,000  321.8266918659  321.8266918659  321.8266918659      1      ?
     ------  --------------  --------------  --------------  -----  -----
   
 */

require_once('../r/db.php');
require_once('../r/maps.php');

require_once('../r/sql/cluster_children_tables.php');
require_once('../r/sql/cluster_tables.php');
require_once('../r/sql/points.php');
require_once('../r/sql/proximity_index_tables.php');

define('INDEX_TABLES_PREFIX', 'c1');
define('INDEX_TABLES_SOURCE', 'points');

define('CLUSTER_LEVELS', 10);

buildCluster();

function buildCluster() {
    $microtime = microtime(TRUE);

    for ($clusterLevel = 1; $clusterLevel <= CLUSTER_LEVELS; $clusterLevel++) {
        initializeTables($clusterLevel);
    }

    $distance = 1000;

    for ($clusterLevel = 1; $clusterLevel <= CLUSTER_LEVELS; $clusterLevel++) {
        $inputPoints = \sql\cluster_tables\fetchAll($clusterLevel - 1);

        indexProximityBetweenInputPoints($clusterLevel, $inputPoints, $distance);

        clusterIndexedPoints($clusterLevel);

        $distance += $clusterLevel * 200000;
    }

    echo 'Time taken: ' . (microtime(TRUE) - $microtime) . "s\n";
}

/* These tables are used to store the data for each level of the clustering
   hierarchy.
 */
function initializeTables($tableId) {
    \sql\proximity_index_tables\initialize($tableId);//drop, truncate or create
    \sql\cluster_tables\initialize($tableId);
    \sql\cluster_children_tables\initialize($tableId);
}

function indexProximityBetweenInputPoints($level, $points, $proximity) {
    foreach ($points as $point) {
        $id_1 = $point['id'];

        $nearbyPoints = \sql\cluster_tables\fetchNearbyPoints($level - 1, $id_1, $point['x'], $point['y'], $proximity);

        foreach ($nearbyPoints as $nearbyPoint) {
            $id_2 = $nearbyPoint['id'];
            $distance = $nearbyPoint['distance'];

            \sql\proximity_index_tables\insert($level, $id_1, $id_2, $distance);
        }
    }
}

function clusterIndexedPoints($level) {
    \sql\cluster_tables\copyPointsTo($level);
    
    $pairs = \sql\proximity_index_tables\fetchAll($level);

    $pointsClusters = array();

    foreach ($pairs as $pair) {
        $id_1 = $pair['id_1'];
        $id_2 = $pair['id_2'];

        $clusterId = NULL;

        if (isset($pointsClusters[$id_1]) && isset($pointsClusters[$id_2])) {
            if ($pointsClusters[$id_1] != $pointsClusters[$id_2]) {
                $moveTo   = $pointsClusters[$id_1];
                $moveFrom = $pointsClusters[$id_2];

                $childrenMoved = moveChildren($level, $moveTo, $moveFrom);

                foreach ($childrenMoved as $childMoved) {
                    $pointsClusters[$childMoved] = $moveTo;
                }
            }

            continue;
        }
        else if (isset($pointsClusters[$id_1])) {
            $clusterId = $pointsClusters[$id_1];

            \sql\cluster_children_tables\insert($level, $id_2, $clusterId);
        }
        else if (isset($pointsClusters[$id_2])) {
            $clusterId = $pointsClusters[$id_2];

            \sql\cluster_children_tables\insert($level, $id_1, $clusterId);
        }
        else {
            $clusterId = \sql\cluster_tables\insert($level);

            \sql\cluster_children_tables\insert($level, $id_1, $clusterId);
            \sql\cluster_children_tables\insert($level, $id_2, $clusterId);
        }

        $pointsClusters[$id_1] = $clusterId;
        $pointsClusters[$id_2] = $clusterId;

        \sql\cluster_tables\delete($level, array($id_1, $id_2));
    }

    updateClusterPositions($level);
}

function moveChildren($level, $newParent, $oldParent) {
    $childrenMoved = \sql\cluster_children_tables\moveChildren($level, $newParent, $oldParent);

    \sql\cluster_tables\delete($level, array($oldParent));

    return $childrenMoved;
}

function updateClusterPositions($level) {
    $unlocated = \sql\cluster_tables\fetchUnlocated($level);

    foreach ($unlocated as $cluster) {
        $x = $cluster['x'];
        $y = $cluster['y'];

        $coordinate = \maps\toCoordinate($x, $y);

        \sql\cluster_tables\update($level, $cluster['id'], $coordinate['lat'], $coordinate['lng'], $x, $y);
    }
}

?>
