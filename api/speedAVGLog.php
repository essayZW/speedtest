<?php
include_once("./init.php");
try {
    $startTime = get($_GET, 'start_time');
    if ($startTime == null) $startTime = date("Y-m-d", strtotime("-0 day")) . ' 0:0:0';
    $startTime = date("Y-m-d H:i:s", strtotime($startTime));
    $endTime = get($_GET, 'end_time');
    if ($endTime == null) $endTime = date("Y-m-d", strtotime("-0 day")) . " 24:0:0";
    $endTime = date("Y-m-d H:i:s", strtotime($endTime));
}
catch(Exception $e) {
    http_response_code(500);
    die(json_encode([
        "status" => false,
        "code" => 500,
        "info" => "param error"
    ]));
}

$p = $conn->prepare("SELECT count(*), avg(dl), avg(ul), avg(ping), avg(jitter) FROM speedtest_infos WHERE `timestamp` BETWEEN ? AND ?");
$p->bind_param("ss", $startTime, $endTime);
$p->execute();
$p->store_result();
$p->bind_result($testNums, $adl, $aul, $aping, $ajitter);
$p->fetch();
$p->close();

$p = $conn->prepare("SELECT count(*) FROM (SELECT count(*) FROM speedtest_infos WHERE `timestamp` BETWEEN ? AND ?) subquery");
$p->bind_param("ss", $startTime, $endTime);
$p->execute();
$p->store_result();
$p->bind_result($userNums);
$p->fetch();
$p->close();
$conn->close();
if($adl == null) $adl = 0;
if($aul == null) $aul = 0;
if($aping == null) $aping = 0;
if($ajitter == null) $ajitter = 0;
if($testNums == null) $testNums = 0;
if($userNums == null) $userNums = 0;
$res = [
    "dl" => $adl,
    "ul" => $aul,
    "ping" => $aping,
    "jitter" => $ajitter,
    "testNum" => $testNums,
    "userNum" => $userNums
];
echo json_encode($res);