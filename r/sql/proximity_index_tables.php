<?php

namespace sql\proximity_index_tables;

function fetchAll($tableId) {
    $tableName = _tableName($tableId);

    $sql = "SELECT *
            FROM $tableName
            ORDER BY distance";

    return \db\fetchAll($sql);
}

function insert($tableId, $point_id_1, $point_id_2, $distance) {
    $tableName = _tableName($tableId);

    $sql = "INSERT INTO $tableName (
              id_1,
              id_2,
              distance
            )
            VALUES (
              $point_id_1,
              $point_id_2,
              $distance            )";

    \db\execute($sql);
}

function truncate($tableId) {
    $tableName = _tableName($tableId);

    $sql = "TRUNCATE $tableName";

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
                  `id_1` bigint(20) unsigned NOT NULL,
                  `id_2` bigint(20) NOT NULL,
                  `distance` decimal(16,6) NOT NULL,
                  UNIQUE KEY `id_from` (`id_1`,`id_2`),
                  KEY `distance` (`distance`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        \db\execute($sql);
    }
}

function _tableName($tableId) {
    $tableName = '';

    if (defined('INDEX_TABLES_PREFIX')) {
        $tableName .= INDEX_TABLES_PREFIX . '_';
    }
    
    $tableName .= "proximity_index_$tableId";

    return $tableName;
}

?>
