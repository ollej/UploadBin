<?php
/**
 * Return human readable sizes
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.1.0
 * @link        http://aidanlister.com/repos/v/function.size_readable.php
 * @param       int    $size        Size
 * @param       int    $unit        The maximum unit
 * @param       int    $retstring   The return string format
 * @param       int    $si          Whether to use SI prefixes
 */
function size_readable($size, $unit = null, $retstring = null, $si = true)
{
    // Units
    if ($si === true) {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod   = 1000;
    } else {
        $sizes = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod   = 1024;
    }
    $ii = count($sizes) - 1;

    // Max unit
    $unit = array_search((string) $unit, $sizes);
    if ($unit === null || $unit === false) {
        $unit = $ii;
    }

    // Return string
    if ($retstring === null) {
        $retstring = '%01.2f %s';
    }

    // Loop
    $i = 0;
    while ($unit != $i && $size >= 1024 && $i < $ii) {
        $size /= $mod;
        $i++;
    }

    return sprintf($retstring, $size, $sizes[$i]);
}

?>