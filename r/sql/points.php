<?php

namespace sql\points;

function fetchAll() {
    $sql = "SELECT lat, lng
            FROM points";

    return \db\fetchAll($sql);
}

function fetchRectCount($topLeftX, $topLeftY, $bottomRightX, $bottomRightY) {
    $sql = "SELECT COUNT(*)
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
