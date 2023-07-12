<?php

declare(strict_types=1);

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

$app->add_route(
    "POST",
    "/api/month",
    function(array $ctx) {
        $body = json_decode(file_get_contents("php://input"), true);

        $name = trim($body["name"] ?? "");

        if (!$name)
        {
            http_response_code(400);
            echo json_encode([
                "error" => [
                    "message" => "name is required",
                    "value" => $name
                ]
            ]);
            exit;
        }

        $res["data"]["name"] = $name;

        $ctx["db"]->transaction();
        try
        {
            $ctx["db"]->exec("insert into month (name) values (?)", [$name]);
            $month_id = (int) $ctx["db"]->last_id();

            $res["data"]["id"] = $month_id;

            $sql = "insert into day (month_id, value) values ";
            $binds = [];

            for ($day = 1; $day <= 31; $day++)
            {
                if ($day > 1) $sql .= ", ";
                $sql .= "(?, ?)";
                $binds[] = $month_id;
                $binds[] = $day;
            }

            $ctx["db"]->exec($sql, $binds);
            $ctx["db"]->commit();
        }
        catch (\Exception $e)
        {
            $ctx["db"]->rollback();
            throw $e;
        }

        $res["data"]["days"] = $ctx["db"]->query_many(
            "select id, weight, workout, supplement, value
             from day where month_id = ?",
            [$month_id]
        );


        http_response_code(201);
        echo json_encode($res);
    }
);

$app->add_route(
    "GET",
    "/api/month",
    function(array $ctx) {
        $months = $ctx["db"]->query_many(
            "select id, name from month order by created_at desc"
        );

        foreach ($months as $i => $it)
        {
            $months[$i]["days"] = $ctx["db"]->query_many(
                "select id, weight, workout, supplement, value from day
                 where month_id = ? order by value",
                [$it["id"]]
            );
        }

        http_response_code(200);
        echo json_encode(["data" => ["items" => $months]]);
    }
);

$app->add_route(
    "DELETE",
    "/api/month",
    function(array $ctx) {
        $id = trim($_GET["id"] ?? "");

        if (!$id)
        {
            http_response_code(400);
            echo json_encode([
                "error" => [
                    "message" => "id is required",
                    "value" => $id
                ]
            ]);
            exit;
        }

        $month = $ctx["db"]->query_one(
            "select name from month where id = ?",
            [$id]
        );

        if (!$month)
        {
            http_response_code(404);
            echo json_encode([
                "error" => [
                    "message" => "month not found",
                    "value" => $id
                ]
            ]);
            exit;
        }

        $ctx["db"]->exec("delete from month where id = ?", [$id]);

        $month["id"] = $id;

        http_response_code(200);
        echo json_encode(["data" => $month]);
    }
);

try
{
    $app->handle_request();
}
catch (http\Error $e)
{
    http_response_code($e->status_code);

    $type = $_SERVER["HTTP_CONTENT_TYPE"] ?? "";

    if ($type === "application/json")
    {
        echo $e->json();
        exit;
    }

    echo $e->message();
}
catch (\Exception $e)
{
    error_log($e->getMessage());

    http_response_code(500);
    echo "Something went wrong and it is not your fault";
}
