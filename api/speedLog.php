<?php
include_once("./init.php");
try {
    $startTime = get($_GET, 'start_time');
    if ($startTime == null) $startTime = date("Y-m-d", strtotime("-6 day")) . ' 0:0:0';
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
$reponseData = [];

if(isset($_GET['average'])) {
    $p = $conn->prepare("SELECT avg(ul), avg(dl), avg(ping), avg(jitter) FROM speedtest_infos WHERE `timestamp` BETWEEN ? AND ?");
    $p->bind_param("ss", $startTime, $endTime);
    $p->execute();
    $p->bind_result($adl, $aul, $aping, $ajitter);
    $p->fetch();
    $p->close();

    if ($adl == null) $adl = 0;
    if ($aul == null) $aul = 0;
    if ($aping == null) $aping = 0;
    if ($ajitter == null) $ajitter = 0;
    $reponseData['avg'] = [
        'dl' => $adl,
        'ul' => $aul,
        'ping' => $aping,
        'jitter' => $ajitter
    ];
}

$step = 3600 * 24;
if(isset($_GET['hour_step'])) {
    $step = 3600;
}
$p = $conn->prepare("SELECT ul, dl, ping, jitter, unumber, `timestamp` FROM speedtest_infos WHERE `timestamp` BETWEEN ? AND ?");
$p->bind_param('ss', $startTime, $endTime);
$p->execute();
$p->store_result();
$p->bind_result($ul, $dl, $ping, $jitter, $unumber, $timestamp);
$reponseData['testNum'] = $p->num_rows();
$currentTime = strtotime($startTime);
$end = strtotime($endTime);
$repData = [];
$index = 0;
$allUserList = [];
while($p->fetch()) {
    $timeNowRow = strtotime($timestamp);
    $allUserList[$unumber] = true;
    while($timeNowRow >= $currentTime + $step) {
        if(!isset($repData[$index])) {
            $repData[$index] = [
                "testNum" => 0,
                "userNum" => 0,
                "avg" => [
                    "dl" => 0,
                    'ul' => 0,
                    "ping" => 0,
                    "jitter" => 0
                ],
                "data" => [],
                "startTime" => $currentTime,
                "endTime" => $currentTime + $step
            ];
        }
        $currentTime += $step;
        $index ++;
    }
    if(!isset($repData[$index])) {
        $repData[$index] = [
            "testNum" => 0,
            "userNum" => 0,
            "avg" => [
                "dl" => 0,
                'ul' => 0,
                "ping" => 0,
                "jitter" => 0
            ],
            "data" => [],
            "startTime" => $currentTime,
            "endTime" => $currentTime + $step
        ];
    }
    $repData[$index]['testNum'] ++;
    $repData[$index]['data'][] = [
        'dl' => $dl,
        'ul' => $ul,
        'ping' => $ping,
        'jitter' => $jitter,
        'unumber' => $unumber,
        'timestamp' => $timestamp
    ];
}
while ($currentTime < $end) {
    if (!isset($repData[$index])) {
        $repData[$index] = [
            "testNum" => 0,
            "userNum" => 0,
            "avg" => [
                "dl" => 0,
                'ul' => 0,
                "ping" => 0,
                "jitter" => 0
            ],
            "data" => [],
            "startTime" => $currentTime,
            "endTime" => $currentTime + $step
        ];
    }
    $currentTime += $step;
    $index++;
}
foreach($repData as $key => $value) {
    $userList = [];
    $sumdl = 0;
    $sumul = 0;
    $sumping = 0;
    $sumjitter = 0;
    $num = 0;
    foreach($value['data'] as $dataValue) {
        $userList[$dataValue['unumber']] = true;
        $sumdl += $dataValue['dl'];
        $sumul += $dataValue['ul'];
        $sumping += $dataValue['ping'];
        $sumjitter += $dataValue['jitter'];
        $num ++;
    }
    $repData[$key]['userNum'] = count($userList);
    $repData[$key]['testNum'] = $num;
    if($num == 0) $num = 1;
    $repData[$key]['avg']['dl'] = $sumdl / $num;
    $repData[$key]['avg']['ul'] = $sumul / $num;
    $repData[$key]['avg']['ping'] = $sumping / $num;
    $repData[$key]['avg']['jitter'] = $sumjitter / $num;
    if(!isset($_GET['withdata'])) {
        unset($repData[$key]['data']);
    }
}
$reponseData['data'] = $repData;
$reponseData['userNum'] = count($allUserList);
$p->close();
echo json_encode($reponseData);
$conn->close();