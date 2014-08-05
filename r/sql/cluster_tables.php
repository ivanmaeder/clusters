<?php

namespace sql\cluster_tables;

function fetchAll($table_id) {
    $sql = "SELECT *
            FROM clusters_$table_id";

    return \db\fetchAll($sql);
}

function fetchUnlocated($table_id) {
    $sql = "SELECT clusters_$table_id.id AS id, AVG(points.x) AS x, AVG(points.y) AS y
            FROM clusters_$table_id
              JOIN cluster_children_$table_id ON clusters_$table_id.id = cluster_children_$table_id.parent
              JOIN points ON cluster_children_$table_id.id = points.id
            WHERE clusters_$table_id.coordinate IS NULL
            GROUP BY clusters_$table_id.id";

    return \db\fetchAll($sql);
}

function update($table_id, $id, $lat, $lng, $x, $y) {
    $sql = "UPDATE clusters_$table_id
            SET coordinate = GEOMFROMTEXT('POINT($lng $lat)'),
              lat = $lat,
              lng = $lng,
              point = GEOMFROMTEXT('POINT($x $y)'),
              x = $x,
              y = $y
            WHERE id = $id";
    
    \db\execute($sql);
}

function insert($table_id) {
    $sql = "INSERT INTO clusters_$table_id (
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

function delete($table_id, $points) {
    foreach ($points as $point_id) {
        $sql = "DELETE FROM clusters_$table_id
                WHERE id = $point_id";

        \db\execute($sql);
    }
}

function truncate($table_id) {
    $sql = "TRUNCATE clusters_$table_id";

    \db\execute($sql);
}

function copyPoints($table_id) {
    $origin = "clusters_$table_id";

    if ($table_id == 1) {
        $origin = 'points';
    }

    $sql = "INSERT INTO clusters_$table_id
            SELECT * FROM $origin";

    \db\execute($sql);
}

?>
