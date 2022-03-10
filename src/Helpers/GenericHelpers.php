<?php

namespace Memento\OAuth2\Client\Helpers;

class GenericHelpers
{
    public static function dd($variable)
    {
        echo "<pre>";
        var_export($variable);
        echo "</pre>";
        die;
    }

    public static function returnAsJson($array)
    {
        header('Content-type: application/json');
        echo json_encode($array);
    }
}