<?php

namespace sql\points;

function fetchAllDistances() {
    $sql = "SELECT p1.id AS p1_id,
              p2.id AS p2_id,
              ST_Distance(p1.point, p2.point) AS distance
            FROM points p1
              JOIN points p2
            ORDER BY distance";

    return \db\fetchAll($sql);
}

function fetchNearbyPoints($id, $distance) {
    $center = fetch($id);

    $x = $center['x'];
    $y = $center['y'];

    $min_x = $x - $distance;
    $max_x = $x + $distance;
    $min_y = $y - $distance;
    $max_y = $y + $distance;

    /* The id > $id condition is to avoid duplicates.
     */
    $sql = "SELECT t.*,
              ST_DISTANCE(point, GEOMFROMTEXT('POINT($x $y)')) AS distance
            FROM (
              SELECT *
              FROM points
              WHERE id > $id
                AND x >= $min_x
                AND y >= $min_y
                AND x < $max_x
                AND y < $max_y
              ) AS t";

    return \db\fetchAll($sql);
}

function fetchAll() {
    $sql = "SELECT *
            FROM points";

    return \db\fetchAll($sql);
}

function fetch($id) {
    $sql = "SELECT *
            FROM points
            WHERE id = $id";

    return \db\fetch($sql);
}

function fetchAverageForRect($topLeftX, $topLeftY, $bottomRightX, $bottomRightY) {
    $sql = "SELECT AVG(lat) AS avg_lat,
              AVG(lng) AS avg_lng
            FROM points
            WHERE x >= $topLeftX
              AND x < $bottomRightX
              AND y >= $topLeftY
              AND y < $bottomRightY";

    return \db\fetch($sql);
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
              GEOMFROMTEXT('POINT($lng $lat)'),
              $lat,
              $lng,
              GEOMFROMTEXT('POINT($x $y)'),
              $x,
              $y
            )";

    \db\execute($sql);
}

?>
