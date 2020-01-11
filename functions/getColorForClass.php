<?php
if ( ! function_exists('getColorForClass'))
{
    /**
     * @param array $data
     * @param string $class
     * @return string
     */
    function getColorForClass($data,$class)
    {
        if (!key_exists($class,$data)) return 'red';
        return 'green';
    }
}