<?php

// http://stackoverflow.com/a/2510540

function formatBytes($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}