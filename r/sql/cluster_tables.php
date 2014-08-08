<?php

namespace sql\cluster_tables;

function fetchAll($tableId) {
    $tableName = _tableName($tableId);

    $sql = "SELECT *
            FROM $tableName";

    return \db\fetchAll($sql);
}

function fetchUnlocated($tableId) {
    $tableName = _tableName($tableId);
    $childrenTableName = _childrenTableName($tableId);
    $coordinatesOrigin = _tableName($tableId - 1);

    $sql = "SELECT $tableName.id AS id, AVG($coordinatesOrigin.x) AS x, AVG($coordinatesOrigin.y) AS y
            FROM $tableName
              JOIN $childrenTableName ON $tableName.id = $childrenTableName.parent
              JOIN $coordinatesOrigin ON $childrenTableName.id = $coordinatesOrigin.id
            WHERE $tableName.coordinate IS NULL
            GROUP BY $tableName.id";

    return \db\fetchAll($sql);
}

function fetch($tableId, $id) {
    $tableName = _tableName($tableId);

    $sql = "SELECT *
            FROM $tableName
            WHERE id = $id";

    return \db\fetch($sql);
}

function fetchNearbyPoints($tableId, $id, $x, $y, $distance = NULL) {
    $tableName = _tableName($tableId);

    $center = fetch($tableId, $id);

    $sql = "SELECT t.*,
              ST_DISTANCE(point, GEOMFROMTEXT('POINT($x $y)')) AS distance
            FROM (
              SELECT *
              FROM $tableName
              WHERE id > $id\n";

    if ($distance) {
        $polygon = _hexagon($x, $y, $distance);

        $sql .= "AND ST_CONTAINS(GEOMFROMTEXT('$polygon'), point)\n";
    }

    $sql .= ") AS t";

    return \db\fetchAll($sql);
}

function _hexagon($x, $y, $r) {
    /* This is not ideal, the hexagon is INSIDE the circle, but it will do
       for testing.

       This helped: http://www.br-gs.com/tutorial/hexagon-grid.html

       For the next step: http://stackoverflow.com/questions/23679130/

       Also, consider reusing the result of the cosine and sine calls, inside
       the function and between calls.
     */
    $p1 = $x . " " . ($y - $r);

    $p = _pointOnCircle($x, $y, $r, 30);
    $p2 = $p['x'] . " " . $p['y'];

    $p = _pointOnCircle($x, $y, $r, 330);
    $p3 = $p['x'] . " " . $p['y'];

    $p4 = $x . " " . ($y + $r);

    $p = _pointOnCircle($x, $y, $r, 210);
    $p5 = $p['x'] . " " . $p['y'];

    $p = _pointOnCircle($x, $y, $r, 150);
    $p6 = $p['x'] . " " . $p['y'];

    return "POLYGON(($p1,$p2,$p3,$p4,$p5,$p6,$p1))";
}

function _pointOnCircle($x, $y, $r, $angle) {
    $x = $x + cos(deg2rad($angle)) * $r;
    $y = $y - sin(deg2rad($angle)) * $r;

    return array('x' => $x, 'y' => $y);
}

function update($tableId, $id, $lat, $lng, $x, $y) {
    $tableName = _tableName($tableId);

    $sql = "UPDATE $tableName
            SET coordinate = GEOMFROMTEXT('POINT($lng $lat)'),
              lat = $lat,
              lng = $lng,
              point = GEOMFROMTEXT('POINT($x $y)'),
              x = $x,
              y = $y
            WHERE id = $id";
    
    \db\execute($sql);
}

function insert($tableId) {
    $tableName = _tableName($tableId);

    $sql = "INSERT INTO $tableName (
              coordinate,
              lat,
              lng,
              point,
              x,
              y
            )
            VALUES (
              NULL,
              NULL,
              NULL,
              NULL,
              NULL,
              NULL
            )";

    \db\execute($sql);

    return \db\insertId();
}

function delete($tableId, $points) {
    $tableName = _tableName($tableId);

    foreach ($points as $point_id) {
        $sql = "DELETE FROM $tableName
                WHERE id = $point_id";

        \db\execute($sql);
    }
}

function truncate($tableId) {
    $tableName = _tableName($tableId);

    $sql = "TRUNCATE $tableName";

    \db\execute($sql);
}

function copyPointsTo($tableId) {
    $destination = _tableName($tableId);
    $origin = _tableName($tableId - 1);

    $sql = "INSERT INTO $destination
            SELECT * FROM $origin";

    \db\execute($sql);
}

function initialize($tableId) {
    $tableName = _tableName($tableId);

    $sql = "SELECT * 
            FROM information_schema.tables
            WHERE table_schema = 'clusters' 
              AND table_name = '$tableName'
            LIMIT 1";

    $row = \db\fetch($sql);

    if ($row) {
        truncate($tableId);
    }
    else {
        $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
                  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `coordinate` point DEFAULT NULL,
                  `lat` decimal(10,8) DEFAULT NULL,
                  `lng` decimal(11,8) DEFAULT NULL,
                  `point` point DEFAULT NULL,
                  `x` decimal(16,6) DEFAULT NULL,
                  `y` decimal(16,6) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `x` (`x`,`y`),
                  KEY `y` (`y`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        \db\execute($sql);
    }
}

function _tableName($tableId) {
    if ($tableId == 0) {
        return 'points';
    }
    else {
        $tableName = '';

        if (defined('INDEX_TABLES_PREFIX')) {
            $tableName .= INDEX_TABLES_PREFIX . '_';
        }

        $tableName .= "clusters_$tableId";

        return $tableName;
    }
}

function _childrenTableName($tableId) {
    $tableName = '';

    if (defined('INDEX_TABLES_PREFIX')) {
        $tableName .= INDEX_TABLES_PREFIX . '_';
    }

    $tableName .= "cluster_children_$tableId";

    return $tableName;
}

?>
