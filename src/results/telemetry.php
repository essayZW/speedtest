<?php
require_once(__DIR__ . '/../utils/validation.php');
include_once('telemetry_settings.php');
require 'idObfuscation.php';

// 检查必须的参数
if (
    !isset($_POST['dl']) || !isset($_POST['ul'])
    || !isset($_POST['ping']) || !isset($_POST['jitter'])
) {
    die('0');
}


$ip = ($_SERVER['REMOTE_ADDR']);
$ispinfo = ($_POST["ispinfo"]);
$extra = ($_POST["extra"]);
$ua = ($_SERVER['HTTP_USER_AGENT']);
$lang = "";
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $lang = ($_SERVER['HTTP_ACCEPT_LANGUAGE']);
$dl = ($_POST["dl"]);
$ul = ($_POST["ul"]);
$ping = ($_POST["ping"]);
$jitter = ($_POST["jitter"]);
$log = ($_POST["log"]);
if (isset($_POST['server'])) {
    $testpointid = (int) $_POST['server'];
}
else {
    $testpointid = null;
}
if ($redact_ip_addresses) {
    $ip = "0.0.0.0";
    $ipv4_regex = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
    $ipv6_regex = '/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/';
    $hostname_regex = '/"hostname":"([^\\\\"]|\\\\")*"/';
    $ispinfo = preg_replace($ipv4_regex, "0.0.0.0", $ispinfo);
    $ispinfo = preg_replace($ipv6_regex, "0.0.0.0", $ispinfo);
    $ispinfo = preg_replace($hostname_regex, "\"hostname\":\"REDACTED\"", $ispinfo);
    $log = preg_replace($ipv4_regex, "0.0.0.0", $log);
    $log = preg_replace($ipv6_regex, "0.0.0.0", $log);
    $log = preg_replace($hostname_regex, "\"hostname\":\"REDACTED\"", $log);
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

$conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename, $MySql_port) or die("1");
$p = $conn->prepare('SELECT id FROM speedtest_users WHERE `number` = ?');
$p->bind_param('s', $__USER_NUMBER__);
$p->execute();
$p->bind_result($userid);
$p->fetch();
$p->close();
$stmt = $conn->prepare("INSERT INTO speedtest_infos (ip,ispinfo,extra,ua,lang,dl,ul,ping,jitter,log,userid,testpointid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)") or die("2");
$stmt->bind_param("sssssssssssi", $ip, $ispinfo, $extra, $ua, $lang, $dl, $ul, $ping, $jitter, $log, $userid,$testpointid) or die("3");
$stmt->execute() or die("4");
$stmt->close() or die("5");
$id = $conn->insert_id;
echo "id " . ($enable_id_obfuscation ? obfuscateId($id) : $id);
$conn->close() or die("6");
