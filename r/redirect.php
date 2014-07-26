<?php

namespace redirect;

function set($key, $value) {
    $_SESSION['_r_redirect_queue'][1][$key] = $value;
}

function get($key) {
    $value = $_SESSION['_r_redirect_queue'][0][$key];

    //if (!$value) {
        //$value = @$_SESSION['_r_redirect_queue'][1][$key];
    //}

    return $value;
}

function _dequeue() {
    unset($_SESSION['_r_redirect_queue'][0]);

    $_SESSION['_r_redirect_queue'][0] = @$_SESSION['_r_redirect_queue'][1];

    unset($_SESSION['_r_redirect_queue'][1]);
}

function http($url) {
    header("Location: $url");
}


?>
