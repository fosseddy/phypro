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
require_once LIB_DIR . "/http.php";
require_once LIB_DIR . "/database.php";

$app = new web\App();

$app->ctx = [
    "db" => new database\Connection("localhost", "phypro", "user", "1234")
];

$app->router->get("/", function() {
    require_once "index.html";
});

$app->router->post("/api/month", function(array $ctx) {
    [
        "db" => $db,
        "body" => $body
    ] = $ctx;

    $name = trim($body["name"] ?? "");

    if (!$name) throw new http\Error(400, "name is required");

    $res["name"] = $name;

    $db->transaction();
    try
    {
        $db->exec("insert into month (name) values (?)", [$name]);
        $month_id = (int) $db->last_id();

        $res["id"] = $month_id;

        $sql = "insert into day (month_id, value) values ";
        $binds = [];

        for ($day = 1; $day <= 31; $day++)
        {
            if ($day > 1) $sql .= ", ";
            $sql .= "(?, ?)";
            $binds[] = $month_id;
            $binds[] = $day;
        }

        $db->exec($sql, $binds);
        $db->commit();
    }
    catch (\Exception $e)
    {
        $db->rollback();
        throw $e;
    }

    $res["days"] = $db->query_many(
        "select id, weight, workout, supplement, value
         from day where month_id = ?",
        [$month_id]
    );

    http_response_code(201);
    echo json_encode(["data" => $res]);
}, ["with_json_body"]);

$app->router->get("/api/month", function(array $ctx) {
    $db = $ctx["db"];

    $per_page = 5;
    $page_index = 1;
    ["count" => $items_total] = $db->query_one(
        "select count(*) as count from month"
    );

    if (isset($_GET["page"]))
    {
        $v = filter_var($_GET["page"], FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 1]
        ]);
        if ($v !== false) $page_index = $v;
    }

    $stmt = $db->pdo->prepare(
        "select id, name from month
         order by created_at desc
         limit :limit offset :offset"
    );

    $stmt->bindValue("offset", ($page_index - 1) * $per_page, \PDO::PARAM_INT);
    $stmt->bindValue("limit", $per_page, \PDO::PARAM_INT);
    $stmt->execute();

    $months = $stmt->fetchAll();

    foreach ($months as $i => $it)
    {
        $months[$i]["days"] = $db->query_many(
            "select id, weight, workout, supplement, value from day
             where month_id = ? order by value",
            [$it["id"]]
        );
    }

    http_response_code(200);
    echo json_encode(["data" => [
        "items" => $months,
        "items_count" => count($months),
        "items_per_page" => $per_page,
        "items_total" => $items_total,
        "page_index" => $page_index,
        "page_total" => ceil($items_total / $per_page)
    ]]);
});

$app->router->delete("/api/month", function(array $ctx) {
    [
        "db" => $db,
        "id" => $id
    ] = $ctx;

    $month = $db->query_one("select name from month where id = ?", [$id]);

    if (!$month) throw new http\Error(404, "month not found");

    $db->exec("delete from month where id = ?", [$id]);

    $month["id"] = $id;

    http_response_code(200);
    echo json_encode(["data" => $month]);
}, ["require_id"]);

$app->router->patch("/api/day", function(array $ctx) {
    [
        "db" => $db,
        "id" => $id,
        "body" => $body
    ] = $ctx;

    $fields = [];
    $errors = [];
    $filter_opts = ["options" => ["min_range" => 0, "max_range" => 1]];

    if (isset($body["workout"]))
    {
        $fields["workout"] = filter_var(
            $body["workout"],
            FILTER_VALIDATE_INT,
            $filter_opts
        );

        if ($fields["workout"] === false)
        {
            unset($fields["workout"]);
            $errors[] = ["message" => "invalid workout value"];
        }
    }

    if (isset($body["supplement"]))
    {
        $fields["supplement"] = filter_var(
            $body["supplement"],
            FILTER_VALIDATE_INT,
            $filter_opts
        );

        if ($fields["supplement"] === false)
        {
            unset($fields["supplement"]);
            $errors[] = ["message" => "invalid supplement value"];
        }
    }

    if (isset($body["weight"]))
    {
        $filter_opts["options"]["max_range"] = 99900;

        $fields["weight"] = filter_var(
            $body["weight"],
            FILTER_VALIDATE_INT,
            $filter_opts
        );

        if ($fields["weight"] === false)
        {
            unset($fields["weight"]);
            $errors[] = ["message" => "invalid weight value"];
        }
    }

    if ($errors)
    {
        throw new http\Error(400, "invalid data", ["errors" => $errors]);
    }
    else if (!$fields)
    {
        throw new http\Error(400, "provide fields to update");
    }

    $day = $db->query_one(
        "select id, weight, workout, supplement, value
         from day where id = ?",
        [$id]
    );

    if (!$day) throw new http\Error(404, "day not found");

    $sql = [];
    $vals = [];
    foreach ($fields as $k => $v)
    {
        $sql[] = "$k = ?";
        $vals[] = $v;
        $day[$k] = $v;
    }

    $sql = join(", ", $sql);
    $db->exec("update day set $sql where id = ?", [...$vals, $id]);

    http_response_code(200);
    echo json_encode(["data" => $day]);
}, ["require_id", "with_json_body"]);

function require_id(array &$ctx)
{
    $id = trim($_GET["id"] ?? "");
    if (!$id) throw new http\Error(400, "id is required");
    $ctx["id"] = $id;
}

function with_json_body(array &$ctx)
{
    $ctx["body"] = json_decode(file_get_contents("php://input"), true) ?? [];
}

try
{
    $app->handle_request();
}
catch (http\Error $e)
{
    http_response_code($e->status_code);
    echo json_encode(["error" => $e->getData()]);
}
catch (\Throwable $e)
{
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode(["error" => ["message" => "internal server error"]]);
}
