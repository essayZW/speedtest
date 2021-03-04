<?php
/**
 * param: start_time
 * description: 查询的记录开始时间
 * 
 * param : end_time
 * description: 查询的记录结束时间
 * 
 * param: average
 * description: 返回数据是否包含从start_time 到 end_time这一段时间的ul, dl, ping, jitter平均值
 * 
 * param: step
 * description: 数据分段的步长，支持小时，天，周，月
 * 
 * param: withdata
 * description: 是否在返回数据中携带具体的测速记录数据
 */
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

$stepFlag = get($_GET, 'step');
switch ($stepFlag) {
    case 'hour':
        $step = 3600;
        break;
    case 'day':
        $step = 3600 * 24;
        break;
    case 'week':
        $step = 3600 * 24 * 7;
        break;
    case 'month':
        $step = 3600 * 24 * 31;
        break;
    case 'single':
        $step = -1;
        break;
    default:
        $step = 3600 * 24;
        break;
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
if($step != -1) {
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
}
else {
    while($p->fetch()) {
        $allUserList[$unumber] = true;
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
                "startTime" => strtotime($timestamp),
                "endTime" => strtotime($timestamp)
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
        $index ++;
    }
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