<?php
/**
 * 该文件主要提供对于测速历史记录的查询
 * 支持分页查询
 * 支持按照某字段进行模糊搜索查询
 *  
 * param: all
 * description: 模式决定，返回多条数据
 *
 * param: start
 * description: 分页开始的下标
 *
 * param:length
 * description: 查询的数量
 *
 * param: search
 * description: 是否开启搜索
 *
 * param: search_field
 * description: 搜索的目标字段，只支持time, unumber, ip, name
 *
 * param: search_data
 * description: 搜索的字段内容，模糊搜索
 *
 * param: single
 * description: 模式决定，返回单个数据
 *
 * param: id
 * description: 返回单个数据时的数据ID
 */
include_once("./init.php");
include_once(__DIR__ . '/../utils/cidr.php');
needAdmin();
if(isset($_GET['all'])) {
    $start = get($_GET, 'start');
    if($start == null) $start = 0;
    $length= get($_GET, 'length');
    if($length== null) $length= 50;
    if(isset($_GET['search'])) {
        $field = get($_GET, 'search_field');
        if($field == null) $field = 'unumber';
        $search_data = get($_GET, 'search_data');
        $search_data = '%%' . $search_data . '%%';
        switch($field) {
            case 'time':
                $q = $conn->prepare("SELECT count(*) FROM speedtest_infos WHERE `timestamp` LIKE ?");
                $q->bind_param('s', $search_data);
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`, testpointid FROM speedtest_infos, speedtest_users
                                    WHERE speedtest_users.id = speedtest_infos.userid
                                    AND `timestamp` LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("sii", $search_data, $start, $length);
                break;
            case 'unumber' :
                $q = $conn->prepare("SELECT count(*) FROM speedtest_infos, speedtest_users WHERE speedtest_users.id = speedtest_infos.userid AND `number` LIKE ?");
                $q->bind_param('s', $search_data);
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`, testpointid FROM speedtest_infos, speedtest_users
                                    WHERE speedtest_users.id = speedtest_infos.userid 
                                    AND `number` LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("sii", $search_data, $start, $length);
                break;
            case 'ip' :
                $q = $conn->prepare("SELECT count(*) FROM speedtest_infos WHERE ip LIKE ?");
                $q->bind_param('s', $search_data);
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`, testpointid
                                    FROM speedtest_infos, speedtest_users 
                                    WHERE speedtest_users.id = speedtest_infos.userid
                                    AND ip LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("sii", $search_data, $start, $length);
                break;
            case 'name':
                $q = $conn->prepare("SELECT count(*) FROM speedtest_infos, speedtest_users WHERE speedtest_users.id = speedtest_infos.userid AND `name` LIKE ?");
                $q->bind_param('s', $search_data);
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`, testpointid
                                    FROM speedtest_infos, speedtest_users
                                    WHERE speedtest_users.id = speedtest_infos.userid
                                    AND `name` LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("sii", $search_data, $start, $length);
                break;
            default:
                $q = $conn->prepare("SELECT count(*) FROM speedtest_infos");
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`, testpointid
                                    FROM speedtest_infos, speedtest_users
                                    WHERE speedtest_users.id = speedtest_infos.userid 
                                    AND `timestamp` LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("ii", $start, $length);
        };
    }
    else {
        $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`, testpointid
                            FROM speedtest_infos, speedtest_users
                            WHERE speedtest_users.id = speedtest_infos.userid
                            ORDER BY `timestamp` DESC LIMIT ?,? ");
        $p->bind_param("ii", $start, $length);
        $q = $conn->prepare("SELECT count(*) FROM speedtest_infos");
    }
    $p->execute();
    $p->bind_result($name, $id, $time, $dl, $ul, $ping, $jitter, $ip, $unumber, $testPointId);
    $allData = [];
    $cidrFilterList = getCIDRListFromMysql();
    $filter = new IpCIDRFilter($cidrFilterList);
    while($p->fetch()) {
        $currentData = [
            'ip' => $ip,
            'id' => $id,
            'dl' => $dl,
            'ul' => $ul,
            'ping' => $ping,
            'jitter' => $jitter,
            'unumber' => $unumber,
            'name' => $name,
            'time' => $time,
            'testPointId' => $testPointId,
            'position' => 'Unknown',
            'accessMethod' => 'Unknown'
        ];
        $matchedIndex = $filter->test($ip);
        if (isset($matchedIndex[0])) {
            $ispInfo = $filter->getFilterInfoByIndex($matchedIndex[0]);
            $currentData['position'] = $ispInfo['position'] ? $ispInfo['position'] : 'Unknown';
            $currentData['accessMethod'] = $ispInfo['accessMethod'] ? $ispInfo['accessMethod'] : 'Unknown';
        }
        $allData[] = $currentData;
    }
    $p->close();
    $q->execute();
    $q->bind_result($allNum);
    $q->fetch();
    $q->close();
    // 获得所有的测速节点信息，留待后面测速信息添加上对应的测速节点信息
    $stmt = $conn->prepare('SELECT id, `name`, `server` FROM speedtest_testpoints');
    $stmt->execute();
    $stmt->bind_result($serverId, $serverName, $serverAddress);
    $allTestPoints = [];
    while ($stmt->fetch()) {
        $allTestPoints[$serverId] = [
            'name' => $serverName,
            'server' => $serverAddress
        ];
    }
    foreach ($allData as $key => $value) {
        $testPointId = $value['testPointId'];
        if ($testPointId == null || !isset($allTestPoints[$testPointId])) {
            // 对应的测速节点为null
            // 可能是由于未在后台配置测速节点时使用的默认节点进行测试
            // 或者是旧版本未有多节点支持时的测试记录
            // 因此将测试节点ID设置为-1，测试节点名称为站点名称，测试节点地址为当前域名
            $allData[$key]['testPointId'] = -1;
            $allData[$key]['serverName'] = getenv('TITLE') ?: 'LibreSpeed Example';
            $allData[$key]['serverAddress'] = $_SERVER['SERVER_NAME'];
        }
        else {
            $allData[$key]['serverName'] = $allTestPoints[$testPointId]['name'];
            $allData[$key]['serverAddress'] = $allTestPoints[$testPointId]['server'];
        }
    }
    $stmt->close();
    $conn->close();
    die(json_encode([
        'total' => $allNum,
        'rows' => $allData
    ]));
}

if(isset($_GET['single'])) {
    $id = get($_GET, 'id');
    if($id == null) {
        die(json_encode([
            'code' => 500,
            'status' => false,
            'info' => 'need info'
        ]));
    }
    $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`
                        FROM speedtest_infos, speedtest_users
                        WHERE speedtest_users.id = speedtest_infos.userid
                        AND speedtest_infos.id = ?");
    $p->bind_param('i', $id);
    $p->execute();
    $p->bind_result($name, $id, $time, $dl, $ul, $ping, $jitter, $ip, $unumber);
    $p->fetch();
    die(json_encode(['ip' => $ip,
        'id' => $id,
        'dl' => $dl,
        'ul' => $ul,
        'ping' => $ping,
        'jitter' => $jitter,
        'unumber' => $unumber,
        'time' => $time,
        'name' => $name
    ]));
}

if (isset($_GET['operation'])) {
    // 对记录进行增删改查
    $operation = get($_GET, 'operation');
    switch ($operation) {
        case 'delete':
            $id = get($_POST, 'id');
            if ($id == null) {
                echo json_encode([
                    'status' => false,
                    'code' => '500',
                    'info' => 'need id'
                ]);
                break;
            }
            $id = (int) $id;
            $p = $conn->prepare('DELETE FROM speedtest_infos WHERE id = ?');
            $p->bind_param('i', $id);
            $p->execute();
            $deletedNums = $p->affected_rows;
            $p->close();
            if ($deletedNums > 0) {
                echo json_encode([
                    'status' => true,
                    'code' => 200,
                    'info' => [
                        'affectedRows' => $deletedNums,
                        'message' => 'delete success'
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'code' => 200,
                    'info' => [
                        'affectedRows' => $deletedNums,
                        'message' => 'delete fail'
                    ]
                ]);
            }
            break;
        default:
            echo json_encode([
                'status' => false,
                'code' => '500',
                'info' => 'invalid operation'
            ]);
            break;
    }
}
