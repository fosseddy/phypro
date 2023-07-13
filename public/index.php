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

$app->add_route("GET", "/", function() { web\render_view("index"); });

$app->add_route(
    "POST",
    "/api/month",
    function(array $ctx) {
        $body = json_decode(file_get_contents("php://input"), true);

        $name = trim($body["name"] ?? "");

        if (!$name) throw new http\Bad_Request("name is required");

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

// TODO(art): add pagination
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
        $id = $ctx["id"];

        $month = $ctx["db"]->query_one(
            "select name from month where id = ?",
            [$id]
        );

        if (!$month) throw new http\Not_Found("month not found");

        $ctx["db"]->exec("delete from month where id = ?", [$id]);

        $month["id"] = $id;

        http_response_code(200);
        echo json_encode(["data" => $month]);
    },
    ["require_id"]
);

// TODO(art): validate fields
$app->add_route(
    "PATCH",
    "/api/day",
    function(array $ctx) {
        $id = $ctx["id"];

        $body = json_decode(file_get_contents("php://input"), true);

        $fields = [];
        $supported = ["weight", "workout", "supplement"];

        foreach ($array_keys($body) as $key)
        {
            if (in_array($key, $supported)) $fields[$key] = $body[$key];
        }

        if (!$fields) throw new http\Bad_Request("provide fields to update");

        $day = $ctx["db"]->query_one(
            "select id, weight, workout, supplement, value
             from day where id = ?",
            [$id]
        );

        if (!$day) throw new http\Not_Found("day not found");

        $sql = [];
        $vals = [];
        foreach ($fields as $k => $v)
        {
            $sql[] = "$k = ?";
            $vals[] = $v;
            $day[$k] = $v;
        }

        $sql = join(", ", $sql);
        $ctx["db"]->exec("update day set $sql where id = ?", [...$vals, $id]);

        http_response_code(200);
        echo json_encode(["data" => $day]);
    },
    ["require_id"]
);

function require_id(array &$ctx)
{
    $id = trim($_GET["id"] ?? "");

    if (!$id) throw new http\Bad_Request("id is required");

    $ctx["id"] = $id;
}

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
        echo json_encode(["error" => $e->getData()]);
        exit;
    }

    echo $e->getMessage();
}
catch (\Exception $e)
{
    error_log($e->getMessage());

    http_response_code(500);

    $type = $_SERVER["HTTP_CONTENT_TYPE"] ?? "";

    if ($type === "application/json")
    {
        echo json_encode(["error" => ["message" => "internal server error"]]);
        exit;
    }

    echo "Something went wrong and it is not your fault";
}
