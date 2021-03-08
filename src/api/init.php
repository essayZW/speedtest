<?php
/**
 * 初始化数据库连接以及登陆判断
 */
error_reporting(-1);
ini_set('display_errors', 1);
include_once("../results/telemetry_settings.php");
date_default_timezone_set('PRC');
header("Content-Type: application/json");

/**
 * 从某个数组中按照key得到值，若key不存在则返回预设的default值
 * @param array $array 数组
 * @param string $key 值的key
 * @param mixed $default 若key不存在返回的默认值
 * @return mixed
 */
function get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * 权限验证
 */
function permissionsValidate() {
    session_start();
    return isset($_SESSION['logged']) && $_SESSION['logged'] === true;
}

/**
 * 进行一次权限验证，若不通过则直接返回错误信息并终止脚本运行
 */
function needAdmin() {
    if (!permissionsValidate()) {
        http_response_code(500);
        die(json_encode([
            "code" => 500,
            "info" => "need login",
            "status" => false
        ]));
    }
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