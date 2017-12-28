<?php

/**
 * @return string
 */
function get_path_info()
{
    if (isset($_SERVER['PATH_INFO'])) {
        return $_SERVER['PATH_INFO'];
    } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
        return $_SERVER['ORIG_PATH_INFO'];
    } else {
        return $_SERVER['REQUEST_URI'];
    }
}