<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('sql/points.php');

/* Single-link clustering algorithm.
 */

$result = \sql\points\fetchAllDistances();

$dendrogram = new Dendrogram();
$index = array();

foreach ($result as $i => $row) {
    $distance = $row['distance'];

    if ($distance != 0) {
        if ($i % 2 == 0) { //every other row is a duplicate
            $dataPoint1 = $row['p1_id'];
            $dataPoint2 = $row['p2_id'];

            if (isset($index[$dataPoint1]) && isset($index[$dataPoint2]) && $index[$dataPoint1] == $index[$dataPoint2]) {
                continue;
            }

            //in each iteration, add a new layer
            $cluster = array($dataPoint1, $dataPoint2);

            //when a connection is made to another cluster, integrate the nodes from that cluster
            if (isset($index[$dataPoint1])) {
                $clusterToCopyFrom = $dendrogram->level($index[$dataPoint1]);

                $cluster = array_merge($cluster, $clusterToCopyFrom);
            }

            if (isset($index[$dataPoint2])) {
                $clusterToCopyFrom = $dendrogram->level($index[$dataPoint2]);

                $cluster = array_merge($cluster, $clusterToCopyFrom);
            }

            foreach ($cluster as $data) {
                $index[$data] = $distance;

            }

            $dendrogram->add($distance, $cluster);
        }
    }
}

showClusters($dendrogram);

function showClusters($dendrogram) {
    $levels = $dendrogram->levels();

    sort($levels);

    $clustered = array();
    $coordinates = array();

    foreach ($levels as $i => $level) {
        $points = $dendrogram->level($level);

        $x_array = array();
        $y_array = array();

        $skip = FALSE;

        foreach ($points as $point) {
            if (in_array($point, $clustered)) {
                $skip = TRUE;

                continue;
            }

            array_push($clustered, $point);

            $result = \sql\points\fetch($point);

            array_push($x_array, $result['x']);
            array_push($y_array, $result['y']);
        }

        if ($skip) {
            continue;
        }

        if ($x_array) {
            $centroid_x = array_sum($x_array) / count($x_array);
            $centroid_y = array_sum($y_array) / count($y_array);

            array_push($coordinates, \maps\toCoordinate($centroid_x, $centroid_y));
        }

        if ($i > 40) {
            break;
        }
    }

    echo json_encode($coordinates);
}

class Dendrogram {
    function __construct() {
        $this->tree = array();
    }

    function add($level, $data) {
        if (!is_array($data)) {
            $data = array($data);
        }

        if (!isset($this->tree[$level])) {
            $this->tree[$level] = new Set();
        }

        $set = $this->tree[$level];

        foreach ($data as $d) {
            $set->add($d);
        }
    }

    function level($level) {
        $set = $this->tree[$level];

        return $set->set();
    }

    function levels() {
        return array_keys($this->tree);
    }
}

class Set {
    function __construct($data = NULL) {
        $this->set = array();

        if ($data) {
            $this->add($data);
        }
    }

    function add($data) {
        if (!is_array($data)) {
            $data = array($data);
        }

        foreach ($data as $d) {
            if (in_array($d, $this->set)) {
                continue;
            }

            array_push($this->set, $d);
        }
    }

    function set() {
        return $this->set;
    }
}

function distanceMatrix($distances) {
    $distanceMatrix = array();

    foreach ($distances as $distance) {
        $distanceMatrix[$distance['p1_id']][$distance['p2_id']] = $distance['distance'];
    }

    return $distanceMatrix;
}

function printMatrix($distanceMatrix) {
    echo '<table border="1">
            <tr>
              <td></td>';

    $orderedPoints = array_keys($distanceMatrix);

    foreach ($orderedPoints as $y) {
        echo "<td>$y</td>";
    }

    foreach ($orderedPoints as $x) {
        echo "</tr>
              <tr>
                <td>$x</td>";

        foreach ($orderedPoints as $y) {
            echo '<td>' . $distanceMatrix[$x][$y] . '</td>';
        }

        echo '</tr>';
    }

    echo '</table>';
}

?>
