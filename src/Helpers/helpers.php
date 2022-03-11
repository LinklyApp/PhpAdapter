<?php

namespace Memento\OAuth2\Client\Helpers;

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
