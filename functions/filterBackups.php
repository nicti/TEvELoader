<?php
if (!function_exists('filterBackups')) {
    function filterBackups($array)
    {
        return array_filter($array, function ($string) {
            return strpos($string, 'backups') === false;
        });
    }
}