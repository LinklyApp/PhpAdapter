<?php

namespace Linkly\OAuth2\Client\Helpers;

function dd($variable)
{
    echo "<pre>";
    var_export($variable);
    echo "</pre>";
    die;
}

function returnAsJson($array)
{
    header('Content-type: application/json');
    echo json_encode($array);
}

function isJson($string) : bool {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

function isXml($string) : bool {
    libxml_use_internal_errors(true);
    simplexml_load_string($string);
    return !libxml_get_errors();
}