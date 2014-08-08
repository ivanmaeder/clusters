<?php

namespace db;

$_r_connection;

function _connection() {
    global $_r_connection;

    if (!isset($_r_connection)) {
        $_r_connection = mysqli_connect('localhost', 'root', '', 'clusters');

        if ($error = mysqli_connect_error()) {
            _internalServerError($error);
        }

        mysqli_set_charset($_r_connection, 'utf8');
    }

    return $_r_connection;
}

function _internalServerError($error) {
    if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] == 'localhost') {
        trigger_error($error);
    }
    else {
        //TODO
    }
}

function fetchAll($sql) {
    $connection = _connection();

    $result = mysqli_query($connection, $sql);

    if ($error = mysqli_error($connection)) {
        _internalServerError($error);
    }

    while ($row = mysqli_fetch_assoc($result)) {
        yield $row;
    }
}

function fetch($sql) {
    $row = NULL; //loop may not run

    foreach (fetchAll($sql) as $row) {
        break;
    }

    return $row;
}

function execute($sql) {
    $connection = _connection();

    $result = mysqli_query($connection, $sql);

    if ($error = mysqli_error($connection)) {
        _internalServerError($error);
    }

    return $result;
}

function insertId() {
    return mysqli_insert_id(_connection());
}

function _sanitize($var) {
    if (is_array($var)) {
        return array_map('\db\_sanitize', $var);
    }

    return mysqli_real_escape_string(_connection(), $var);
}

?>
