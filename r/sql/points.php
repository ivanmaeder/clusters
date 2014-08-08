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

function fetchNearbyPoints($id, $x, $y, $distance = NULL) {
    $center = fetch($id);

    $sql = "SELECT t.*,
              ST_DISTANCE(point, GEOMFROMTEXT('POINT($x $y)')) AS distance
            FROM (
              SELECT *
              FROM points
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

function _cross($x, $y, $r) {
    $plus_x = cos(deg2rad(45)) * $r;
    $plus_y = sin(deg2rad(45)) * $r;

    $p1  = ($x - $plus_x) . " " . ($y - $r);
    $p2  = ($x + $plus_x) . " " . ($y - $r);
    $p3  = ($x + $plus_x) . " " . ($y - $plus_y);
    $p4  = ($x + $r) . " " . ($y - $plus_y);
    $p5  = ($x + $r) . " " . ($y + $plus_y);
    $p6  = ($x + $plus_x) . " " . ($y + $plus_y);
    $p7  = ($x + $plus_x) . " " . ($y + $r);
    $p8  = ($x - $plus_x) . " " . ($y + $r);
    $p9  = ($x - $plus_x) . " " . ($y + $plus_y);
    $p10 = ($x - $r) . " " . ($y + $plus_y);
    $p11 = ($x - $r) . " " . ($y - $plus_y);
    $p12 = ($x - $plus_x) . " " . ($y - $plus_y);

    return "POLYGON(($p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8,$p9,$p10,$p11,$p12,$p1))";
}

function _pointOnCircle($x, $y, $r, $angle) {
    $x = $x + cos(deg2rad($angle)) * $r;
    $y = $y - sin(deg2rad($angle)) * $r;

    return array('x' => $x, 'y' => $y);
}

function _square($x, $y, $r) {
    $p1 = ($x - $r) . " " . ($y - $r);
    $p2 = ($x + $r) . " " . ($y - $r);
    $p3 = ($x + $r) . " " . ($y + $r);
    $p4 = ($x - $r) . " " . ($y + $r);

    return "POLYGON(($p1,$p2,$p3,$p4,$p1))";
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
