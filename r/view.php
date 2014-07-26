<?php

namespace view;

$_r_display_vars = array();

function set($key, $value) {
    global $_r_display_vars;

    $_r_display_vars[$key] = $value;
}

function display() {
    $info = pathinfo($_SERVER['SCRIPT_NAME']);
    $path = dirname(__FILE__) . '/html' . $info['dirname'] . '/' . $info['filename'] . '.tpl';

    if (!file_exists($path)) {
        trigger_error("Unable to display $path; file not found");

        return;
    }

    global $_r_display_vars;

    extract($_r_display_vars, EXTR_OVERWRITE);

    include($path);
}

?>
