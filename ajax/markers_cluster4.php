<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('maps.php');
require_once('sql/cluster_tables.php');

define('INDEX_TABLES_PREFIX', 'c1');

$level = $_GET['level'];

$coordinates = array();

foreach (\sql\cluster_tables\fetchAll($level) as $coordinate) {
    array_push($coordinates, array('id' => $coordinate['id'], 'lat' => $coordinate['lat'], 'lng' => $coordinate['lng']));
}

echo json_encode($coordinates);

?>
