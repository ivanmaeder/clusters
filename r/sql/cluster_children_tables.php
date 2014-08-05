<?php

namespace sql\cluster_children_tables;

function insert($table_id, $id, $parent_id) {
    $sql = "INSERT INTO cluster_children_$table_id (
              id,
              parent
            )
            VALUES (
              $id,
              $parent_id
            )";

    \db\execute($sql);
}

function truncate($table_id) {
    $sql = "TRUNCATE cluster_children_$table_id";

    \db\execute($sql);
}

?>
