<?php

namespace sql\points;

/*

--2014-07-27

CREATE TABLE IF NOT EXISTS `points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coordinate` point NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `point` point NOT NULL,
  `x` decimal(16,6) NOT NULL,
  `y` decimal(16,6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

*/

function fetchAll() {
    $sql = "SELECT lat, lng
            FROM points";

    return \db\fetchAll($sql);
}

function fetchGrid() {
/*

--this is one way of doing it:
SELECT SUM(CASE WHEN lat < 0 AND lng < 0 THEN 1 ELSE 0 END),
  SUM(CASE WHEN lat > 0 AND lng < 0 THEN 1 ELSE 0 END),
  SUM(CASE WHEN lat < 0 AND lng > 0 THEN 1 ELSE 0 END),
  SUM(CASE WHEN lat > 0 AND lng > 0 THEN 1 ELSE 0 END)
FROM points

 */
    require_once('maps.php'); //TODO delete
    define('GRID_EDGE_SIZE', MAPKIT_MAP_WIDTH);

    $coordinates = array();

    $topLeft = array('x' => 0, 'y' => 0);

    do {
        $topLeftX = $topLeft['x'];
        $topLeftY = $topLeft['y'];
        $bottomRightX = $topLeft['y'] + GRID_EDGE_SIZE;
        $bottomRightY = $topLeft['y'] + GRID_EDGE_SIZE;

        $sql = "SELECT COUNT(*)
                FROM points
                WHERE x >= $topLeftX
                  AND x < $bottomRightX
                  AND y >= $topLeftY
                  AND y < $bottomRightY
                ";

        $result = \db\fetch($sql);
        if ($result['COUNT(*)'] > 0) {
            //NEED TO CONVERT POINTS TO LAT/LNG
            //array_push($coordinates, array(');
        }
    }
    while (($topLeft['x'] + GRID_EDGE_SIZE) < MAPKIT_MAP_WIDTH);

    return $coordinates;
}

function insert($lat, $lng, $x, $y) {
    $sql = "INSERT INTO points (
              coordinate,
              lat,
              lng,
              point,
              x,
              y
            )
            VALUES (
              GeomFromText('POINT($lng $lat)'),
              $lat,
              $lng,
              GeomFromText('POINT($x $y)'),
              $x,
              $y
            )";

    \db\execute($sql);
}

?>
