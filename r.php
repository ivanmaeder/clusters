<?php

/* Include at top of all controller scripts.
 */

error_reporting(0);

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    error_reporting(E_ALL);
}

$include_path = array($_SERVER['DOCUMENT_ROOT'],
    $_SERVER['DOCUMENT_ROOT'] . '/r/'
);

set_include_path(implode($include_path, ':'));

include('db.php');
include('redirect.php');
include('view.php');

session_start();

\redirect\_dequeue();

$_GET  = \db\_sanitize($_GET);
$_POST = \db\_sanitize($_POST);

?>
