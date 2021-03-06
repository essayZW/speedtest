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
        return '2018040532';
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


/**
 * 以下是判断IP地址是否属于某一个CIDR所表示的范围内的函数
 * 支持IPv6地址和IPv4地址
 */

/**
 * 将给定二进制位按照$width 的步长转化为十进制
 *
 * @param string $bin
 * @param integer $width
 * @return array 
 */
function bin2decWithWidth($bin, $width) {
    $res = [];
    $i = strlen($bin) - 1;
    while($i >= 0) {
        $temp = '';
        for($j = 0; $j < $width; $j ++) {
            $temp .= $bin[$i];
            $i --;
        }
        $temp = strrev($temp);
        $res = array_merge([base_convert($temp, 2, 10)], $res);
    } 
    return $res;
}

/**
 * 将给定二进制位按照$width 的步长转化为十六进制
 *
 * @param string $bin
 * @param integer $width
 * @return array 
 */
function bin2hexWithWidth($bin, $width) {
    $res = [];
    $i = strlen($bin) - 1;
    while($i >= 0) {
        $temp = '';
        for($j = 0; $j < $width; $j ++) {
            $temp .= $bin[$i];
            $i --;
        }
        $temp = strrev($temp);
        $res = array_merge([base_convert($temp, 2, 16)], $res);
    } 
    return $res;
}

/**
 * 得到给定的IPv4地址下的CIDR范围
 *
 * @param string $prefix CIDR 前缀
 * @param integer  $length  共享前缀长度
 * @return array 包含开始地址与结束地址的数组 
 */
function getIPv4CIDRRange($prefix, $length) {
    $nums = explode('.', $prefix);
    // 给定的长度是前缀长度，在此转化成后面可变部分的长度
    $length = 32 - $length;
    for($i = 0; $i < 4; $i ++) {
        $binIp .=  str_pad(base_convert($nums[$i], 10, 2), 8, '0', STR_PAD_LEFT);
    }
    // 将后面的$length 位置为0，得到起始IP
    for($i = 0; $i < $length; $i ++) {
        $binIp[32 - $i - 1] = '0';
    }
    $nums = bin2decWithWidth($binIp, 8);
    $startIp = implode('.', $nums);
    // 将后面的$length 位置为1，得到结束IP
    for($i = 0; $i < $length; $i ++) {
        $binIp[32 - $i - 1] = '1';
    }
    $nums = bin2decWithWidth($binIp, 8);
    $endIp= implode('.', $nums);
    return [$startIp, $endIp];
}

/**
 * 展开IPv6地址中的双冒号
 *
 * @return void
 */
function expandIpv6($ip) {
    if(strpos($ip, '::') === false) {
        // 没有::
        return $ip;
    }
    // 省略的为0的项数，为冒号个数减去两个冒号个数后得到已经有的项数，在被7减去
    $nums = 9 - substr_count($ip, ':');
    $replace = ':';
    for($i = 0; $i < $nums; $i ++) {
        $replace .= '0:';
    }
    $ip = str_replace('::', $replace, $ip);
    if($ip[0] == ':') {
        $ip = substr($ip, 1);
    }
    if($ip[strlen($ip) - 1] == ':') {
        $ip = substr($ip, 0, -1);
    }
    return $ip;
}
/**
 * 按照字符串分割给定的字符串并将分割的每一个部分进行pad后再进行拼接
 *
 * @param string $str
 * @param string $splitFlagStr
 * @param integer $padLength
 * @param string $padStr
 * @param integer $padFlag
 * @return string 
 */
function splitAndPad($str, $splitFlagStr, $padLength, $padStr = ' ', $padFlag) {
    $arr = explode($splitFlagStr, $str);
    for ($i = 0; $i < count($arr); $i ++) {
        $arr[$i] = str_pad($arr[$i], $padLength, $padStr, $padFlag);
    }
    return implode($splitFlagStr, $arr);
}

/**
 * 计算给定的IPv6下的CIDR表示的地址范围
 *
 * @param string $prefix 前缀
 * @param integer $length 共享前缀长度
 * @return array 
 */
function getIPv6CIDRRange($prefix, $length) {
    $prefix = expandIpv6($prefix);
    $nums = explode(':', $prefix);
    $bin = '';
    $length = 128 - $length;
    for($i = 0; $i < 8; $i ++) {
        $bin .= str_pad(base_convert($nums[$i], 16, 2), 16, '0', STR_PAD_LEFT);
    }
    // 计算起始IP地址
    for($i = 0; $i < $length; $i ++) {
        $bin[128 - $i - 1] = '0';
    }
    $startIp = bin2hexWithWidth($bin, 16);
    $startIp = implode(':', $startIp);
    // 计算结束IP
    for($i = 0; $i < $length; $i ++) {
        $bin[128 - $i - 1] = '1';
    }
    $endIp = bin2hexWithWidth($bin, 16);
    $endIp = implode(':', $endIp);
    return [$startIp, $endIp];
}
/**
 * 判断给定的IP地址是否属于给定的CIDR所表示的IP范围内
 *
 * @param string $ip 给定的IP
 * @param string $cidrPrefix 给定的CIDR前缀
 * @param integer $cidrLength CIDR前缀长度
 * @return boolean
 */
function isIpInCIDRRange($ip, $cidrPrefix, $cidrLength) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $range = getIPv4CIDRRange($cidrPrefix, $cidrLength);
        $ip = splitAndPad($ip, '.', 3, '0', STR_PAD_LEFT);
        $range[0] = splitAndPad($range[0], '.', 3, '0', STR_PAD_LEFT);
        $range[1] = splitAndPad($range[1], '.', 3, '0', STR_PAD_LEFT);
        return strcmp($ip, $range[0]) >= 0 && strcmp($ip, $range[1]) <= 0;
    }
    else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $range = getIPv6CIDRRange($cidrPrefix, $cidrLength);
        $ip = splitAndPad($ip, ':', 4, '0', STR_PAD_LEFT);
        $range[0] = splitAndPad($range[0], ':', 4, '0', STR_PAD_LEFT);
        $range[1] = splitAndPad($range[1], ':', 4, '0', STR_PAD_LEFT);
        return strcmp($ip, $range[0]) >= 0 && strcmp($ip, $range[1]) <= 0;
    }
    else {
        return false;
    }
}

// test
// print_r(getIPv4CIDRRange('10.10.1.32', 9));
// var_dump(isIpInCIDRRange('10.10.127.32', '10.10.1.32', 9));
// var_dump(expandIpv6('2001:da8:ff3a:c88e::'));

// var_dump(getIPv6CIDRRange('::2001:da8:ff3a:c88e', 119));
// var_dump(isIpInCIDRRange('0000:0000:0000:0000:2001:0da8:ff3a:c8ff', '::2001:da8:ff3a:c88e', 119));
