<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('sql/points.php');

$coordinates = array();

foreach (\sql\points\fetchAll() as $coordinate) {
    array_push($coordinates, $coordinate);
}

echo json_encode($coordinates);

?>
