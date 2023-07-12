<?php

const BASE_DIR = __DIR__ . "/..";
const LIB_DIR = BASE_DIR . "/lib";

function debug($val)
{
    echo "<pre>";
    var_dump($val);
    echo "</pre>";
}

require_once LIB_DIR . "/web.php";
require_once LIB_DIR . "/database.php";

$app = new web\App();

$app->ctx = [
    "db" => new database\Connection("localhost", "phypro", "user", "1234")
];

$app->add_route(
    "GET",
    "/",
    function() {
        web\render_view("index");
    }
);

try
{
    $app->handle_request();
}
catch (http\Not_Found $e)
{
    http_response_code(404);
    echo "Page Not Found";
}
catch (Exception $e)
{
    error_log($e->getMessage());

    http_response_code(500);
    echo "Something went wrong and it is not your fault";
}
