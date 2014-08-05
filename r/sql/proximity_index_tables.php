<?php

namespace sql\proximity_index_tables;

function fetchAll($table_id) {
    $sql = "SELECT *
            FROM proximity_index_$table_id
            ORDER BY distance";

    return \db\fetchAll($sql);
}

function insert($table_id, $point_id_1, $point_id_2, $distance, $mid_x = 0, $mid_y = 0) {
    $sql = "INSERT INTO proximity_index_$table_id (
              id_1,
              id_2,
              distance,
              mid_point,
              mid_x,
              mid_y
            )
            VALUES (
              $point_id_1,
              $point_id_2,
              $distance,
              GEOMFROMTEXT('POINT($mid_x $mid_y)'),
              $mid_x,
              $mid_y
            )";

    \db\execute($sql);
}

function truncate($table_id) {
    $sql = "TRUNCATE proximity_index_$table_id";

    \db\execute($sql);
}

?>
