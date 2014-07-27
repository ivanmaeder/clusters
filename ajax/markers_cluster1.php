<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('sql/points.php');

//Grid-based clustering algorithm

$coordinates = array();

\sql\points\fetchGrid();
//foreach (\sql\points\fetchGrid() as $coordinate) {
    //array_push($coordinates, $coordinate);
//}

//echo json_encode($coordinates);

?>
