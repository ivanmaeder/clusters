<?php

namespace sql\cluster_children_tables;

function insert($tableId, $id, $parent_id) {
    $tableName = _tableName($tableId);

    $sql = "INSERT INTO $tableName (
              id,
              parent
            )
            VALUES (
              $id,
              $parent_id
            )";

    \db\execute($sql);
}

function truncate($tableId) {
    $tableName = _tableName($tableId);

    $sql = "TRUNCATE $tableName";

    \db\execute($sql);
}

function moveChildren($tableId, $newParent, $oldParent) {
    $tableName = _tableName($tableId);

    $sql = "SELECT *
            FROM $tableName
            WHERE parent = $oldParent";

    $rows = \db\fetchAll($sql);

    $childrenMoved = array();

    foreach ($rows as $row) {
        array_push($childrenMoved, $row['id']);
    }

    $sql = "UPDATE $tableName
            SET parent = $newParent
            WHERE parent = $oldParent";

    \db\execute($sql);

    return $childrenMoved;
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
                  `id` bigint(20) unsigned NOT NULL,
                  `parent` bigint(20) unsigned NOT NULL
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        \db\execute($sql);
    }
}

function _tableName($tableId) {
    $tableName = '';

    if (defined('INDEX_TABLES_PREFIX')) {
        $tableName .= INDEX_TABLES_PREFIX . '_';
    }

    $tableName .= "cluster_children_$tableId";

    return $tableName;
}

?>
