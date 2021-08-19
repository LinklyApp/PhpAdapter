<?php

if (!function_exists('dd')) {
    function dd($variable)
    {
        echo "<pre>";
        var_export($variable);
        echo "</pre>";
        die;
    }
}

if (!function_exists('returnAsJson')) {
    function returnAsJson($array)
    {
        header('Content-type: application/json');
        echo json_encode($array);
    }
}
