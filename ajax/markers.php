<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('sql/points.php');

$coordinates = array();

foreach (\sql\points\fetchAll() as $coordinate) {
    array_push($coordinates, array('id' => $coordinate['id'], 'lat' => $coordinate['lat'], 'lng' => $coordinate['lng']));
}

echo json_encode($coordinates);

?>
