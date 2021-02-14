<?php
/**
 * 初始化数据库连接以及登陆判断
 */
include_once("../results/telemetry_settings.php");
// error_reporting(-1);
// ini_set('display_errors', 1);
header("Content-Type: application/json");
session_start();
if(!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    http_response_code(500);
    die(json_encode([
        "code" => 500,
        "info" => "need login",
        "status" => false
    ]));
}
function get($array, $key) {
    return isset($array[$key]) ? $array[$key] : null;
}
try {
    $conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename, $MySql_port);
    if (!$conn) {
        http_response_code(500);
        die(json_encode([
            "status" => false,
            "code" => 500,
            "info" => "mysql connect error"
        ]));
    }
    $conn->query("set time_zone = '+8:00'");
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        "status" => false,
        "code" => 500,
        "info" => "mysql connect error"
    ]));
}