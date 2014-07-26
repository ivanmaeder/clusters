<?php

namespace sql\points;

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
