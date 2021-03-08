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
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number` FROM speedtest_infos, speedtest_users
                                    WHERE speedtest_users.id = speedtest_infos.userid
                                    AND `timestamp` LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("sii", $search_data, $start, $length);
                break;
            case 'unumber' :
                $q = $conn->prepare("SELECT count(*) FROM speedtest_infos, speedtest_users WHERE speedtest_users.id = speedtest_infos.userid AND `number` LIKE ?");
                $q->bind_param('s', $search_data);
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number` FROM speedtest_infos, speedtest_users
                                    WHERE speedtest_users.id = speedtest_infos.userid 
                                    AND `number` LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("sii", $search_data, $start, $length);
                break;
            case 'ip' :
                $q = $conn->prepare("SELECT count(*) FROM speedtest_infos WHERE ip LIKE ?");
                $q->bind_param('s', $search_data);
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`
                                    FROM speedtest_infos, speedtest_users 
                                    WHERE speedtest_users.id = speedtest_infos.userid
                                    AND ip LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("sii", $search_data, $start, $length);
                break;
            case 'name':
                $q = $conn->prepare("SELECT count(*) FROM speedtest_infos, speedtest_users WHERE speedtest_users.id = speedtest_infos.userid AND `name` LIKE ?");
                $q->bind_param('s', $search_data);
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`
                                    FROM speedtest_infos, speedtest_users
                                    WHERE speedtest_users.id = speedtest_infos.userid
                                    AND `name` LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("sii", $search_data, $start, $length);
                break;
            default:
                $q = $conn->prepare("SELECT count(*) FROM speedtest_infos");
                $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`
                                    FROM speedtest_infos, speedtest_users
                                    WHERE speedtest_users.id = speedtest_infos.userid 
                                    AND `timestamp` LIKE ? ORDER BY `timestamp` DESC LIMIT ?,? ");
                $p->bind_param("ii", $start, $length);
        };
    }
    else {
        $p = $conn->prepare("SELECT `name`, speedtest_infos.id, `timestamp`, dl, ul, ping, jitter, ip, `number`
                            FROM speedtest_infos, speedtest_users
                            WHERE speedtest_users.id = speedtest_infos.userid
                            ORDER BY `timestamp` DESC LIMIT ?,? ");
        $p->bind_param("ii", $start, $length);
        $q = $conn->prepare("SELECT count(*) FROM speedtest_infos");
    }
    $p->execute();
    $p->bind_result($name, $id, $time, $dl, $ul, $ping, $jitter, $ip, $unumber);
    $allData = [];
    while($p->fetch()) {
        $allData[] = [
            'ip' => $ip,
            'id' => $id,
            'dl' => $dl,
            'ul' => $ul,
            'ping' => $ping,
            'jitter' => $jitter,
            'unumber' => $unumber,
            'name' => $name,
            'time' => $time
        ];
    }
    $p->close();
    $q->execute();
    $q->bind_result($num);
    $q->fetch();
    $conn->close();
    die(json_encode([
        'total' => $num,
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
