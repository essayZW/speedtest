<?php
class Session {
    static public function start() {
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
    }
    static public function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    static public function get($key) {
        self::start();
        if(!isset($_SESSION[$key])) return '';
        return $_SESSION[$key];
    }
}
class PermissionValidator extends Session {
    static public $userdata;
    static public $isLogin = false;
    static public function check() {
        self::start();
        return isset($_SESSION['user']);
    }

    static public function validate($ticket) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, __PORTAL_VALIDATE_URL__ . '?service=' . getRequestUrl() . '&ticket=' . $ticket);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:text/xml; charset=utf-8"));
        $output = curl_exec($ch);
        curl_close($ch);
        self::parse($output);
        return self::$isLogin;
    }

    static public function parse($xmldata) {
        $xmldata = str_replace("cas:", "", $xmldata);
        $xml = simplexml_load_string($xmldata);
        $xml = json_encode($xml);
        $xml = json_decode($xml, true);
        self::$isLogin = isset($xml['authenticationSuccess']);
        if(!self::$isLogin) return;
        self::$userdata = $xml['authenticationSuccess'];
    }

    static public function getLoginName() {
        if(!self::$isLogin) return '';
        return self::$userdata['attributes']['name'];
    }

    static public function getLoginNumber() {
        if(!self::$isLogin) return '';
        return self::$userdata['attributes']['employeeNumber'];
    }
}

/**
 * 得到完整的请求URL
 */
function getRequestUrl() {
    $res = $_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"];
    if(is_https()) {
        return 'https://' . $res;
    }
    else {
        return 'http://' . $res;
    }
}

/**
 * PHP判断当前协议是否为HTTPS
 */
function is_https() {
    if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    } elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
        return true;
    } elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }
    return false;
}


define('__PORTAL_VALIDATE_URL__', 'https://portal.buct.edu.cn/cas/serviceValidate');
define('__PORTAL_LOGIN_URL__', 'https://portal.buct.edu.cn/cas/login');

require_once(__DIR__ . '/../results/telemetry_settings.php');
if(!PermissionValidator::check()) {
    if(!isset($_GET['ticket']) && !$needLogin) {
        // 未登录，前往统一认证界面
        $__USER_NUMBER__ = '1234567890';
    }
    else if ($needLogin) {
        header("Location: ". __PORTAL_LOGIN_URL__ . '?service=' .urlencode(getRequestUrl()));
    }
    else {
        // 验证ticket
        $ticket = $_GET['ticket'];
        if(PermissionValidator::validate($ticket)) {
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
            if(!$num) {
                $q = $conn->prepare('INSERT INTO speedtest_users (`name`, `number`) VALUES (?, ?)');
                $q->bind_param("ss", $__USER_NAME__, $__USER_NUMBER__);
                $q->execute();
                $q->close();
            }
            $conn->close();
        }
        header("Location: ". getRequestUrl());
    }
}
else {
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
