<?php
require_once(__DIR__ . '/utils.php');
require_once(__DIR__ . '/../results/telemetry_settings.php');
if (!PermissionValidator::check()) {
    if (!isset($_GET['ticket'])) {
        // 未登录，前往统一认证界面
        header("Location: " . __PORTAL_LOGIN_URL__ . '?service=' . urlencode(getRequestUrl()));
    } else {
        // 验证ticket
        $ticket = $_GET['ticket'];
        if (PermissionValidator::validate($ticket)) {
            $__USER_NAME__ = PermissionValidator::getLoginName();
            $__USER_NUMBER__ = PermissionValidator::getLoginNumber();
            PermissionValidator::set('user', $__USER_NUMBER__);

            $conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename, $MySql_port);
            $conn->query('SET CHARSET utf8mb4');
            $q = $conn->prepare('SELECT count(*) AS num FROM speedtest_users WHERE number=?');
            $q->bind_param("s", $__USER_NUMBER__);
            $q->execute();
            $q->bind_result($num);
            $q->fetch();
            $q->close();
            if (!$num) {
                $q = $conn->prepare('INSERT INTO speedtest_users (`name`, `number`) VALUES (?, ?)');
                $q->bind_param("ss", $__USER_NAME__, $__USER_NUMBER__);
                $q->execute();
                $q->close();
            }
            $conn->close();
        }
        header("Location: " . getRequestUrl());
    }
} else {
    // 已经登陆，从数据库中获取相关信息
    $conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename, $MySql_port);
    $conn->query('SET CHARSET utf8mb4');
    $q = $conn->prepare('SELECT name FROM speedtest_users WHERE `number`=?');
    $q->bind_param('s', PermissionValidator::get('user'));
    $q->execute();
    $q->bind_result($name);
    $q->fetch();
    $q->close();
    $conn->close();
    $__USER_NAME__ = $name;
    $__USER_NUMBER__ = PermissionValidator::get('user');
}
