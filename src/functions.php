<?php

/**
 * @param string $path
 * @return string
 */
function coaster_commerce_base_path($path = '') {

    return realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . $path;

}

/**
 * @param string $path
 * @return string
 */
function coaster_commerce_src_path($path = '') {

    return coaster_commerce_base_path('src' . DIRECTORY_SEPARATOR . $path);

}