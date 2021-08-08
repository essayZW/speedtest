<?php
/**
 * 该页面支持对cidr信息进行增删改查
 * 
 * param: operation
 * description: 需要进行的操作，只支持四个值，分别是select, delete, insert, update
 * 
 * param: id
 * description: 需要操作的cidr信息在数据库中的id
 * 
 * param: cidr
 * description: cidr信息，包括cide前缀和前缀长度，形如 10.10.10.10/10
 * 
 * param: position
 * description: 某个 cidr 所表示的网段在网络部署中的部署位置信息
 * 
 * param: accessMethod
 * description: 某个 cidr 所表示的网段在网络部署中的接入方式，一般指的是有线还是无线接入
 * 
 * param: isp
 * description: 某个 cidr 所表示的网段中地址的isp信息摘要
 * 
 * param: ispinfo
 * description: 某个 cidr 所表示的网段中ip地址的详细isp信息，一般为json数据
 */
include_once('./init.php');
include_once(__DIR__ . '/../utils/cidr.php');
needAdmin();

$operation = get($_GET, 'operation');

switch ($operation) {
    case 'select':
        $start = get($_GET, 'start');
        if ($start == null) $start = 0;
        $length = get($_GET, 'length');
        if ($length == null) $length = 50;
        $p = $conn->prepare('SELECT id, `cidr`, position, accessMethod, isp, ispinfo FROM speedtest_cidrinfo LIMIT ?, ?');
        $p->bind_param('ii', $start, $length);
        $p->execute();
        $p->bind_result($id, $cidr, $position, $accessmethod, $isp, $ispinfo);
        $cidrList = [];
        while ($p->fetch()) {
            $cidrList[] = [
                'cidr' => $cidr,
                'id' => $id,
                'position' => $position,
                'accessMethod' => $accessmethod,
                'isp' => $isp,
                'ispinfo' => $ispinfo
            ];
        }
        $p->close();
        $p = $conn->prepare("SELECT COUNT(*) FROM speedtest_cidrinfo");
        $p->execute();
        $p->bind_result($allNum);
        $p->fetch();
        echo json_encode([
            'total' => $allNum,
            'rows' => $cidrList
        ]);
        break;

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
        $p = $conn->prepare('DELETE FROM speedtest_cidrinfo WHERE id = ?');
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

    case 'insert':
        $cidr = get($_POST, 'cidr');
        $position = get($_POST, 'position', '');
        $accessMethod = get($_POST, 'accessMethod', '');
        $isp = get($_POST, 'isp', '');
        $ispinfo = get($_POST, 'ispinfo', '');

        if (!IpCIDR::validate($cidr, $message)) {
            echo json_encode([
                'status' => false,
                'code' => 200,
                'info' => $message
            ]);
            break;
        }

        if ($cidr == null) {
            echo json_encode([
                'status' => false,
                'code' => 200,
                'info' => 'need cidr'
            ]);
            break;
        }
        $index = (new IpCIDR($cidr))->getPrefixLength();
        $p = $conn->prepare('INSERT INTO speedtest_cidrinfo (`cidr`, position, accessmethod, isp, ispinfo, `index`)
                                VALUES (?, ?, ?, ?, ?, ?)');
        $p->bind_param('sssssi', $cidr, $position, $accessMethod, $isp, $ispinfo, $index);
        $p->execute();
        $affectNums = $p->affected_rows;
        $p->close();
        if ($affectNums) {
            echo json_encode([
                'status' => true,
                'code' => 200,
                'info' => [
                    'message' => 'add cidr info success',
                    'affectedRows' => $affectNums
                ]
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'code' => 200,
                'info' => [
                    'message' => 'add cidr info fail',
                    'affectedRows' => $affectNums
                ]
            ]);
        }
        break;

    case 'update':
        $id = get($_POST, 'id');
        $cidr = get($_POST, 'cidr');
        $position = get($_POST, 'position', '');
        $accessMethod = get($_POST, 'accessMethod', '');
        $isp = get($_POST, 'isp', '');
        $ispinfo = get($_POST, 'ispinfo', '');

        if ($id == null) {
            echo json_encode([
                'status' => false,
                'code' => 200,
                'info' => 'need id'
            ]);
            break;
        }

        if (!IpCIDR::validate($cidr, $message)) {
            echo json_encode([
                'status' => false,
                'code' => 200,
                'info' => $message 
            ]);
            break;
        }
        // 在此使用CIDR所能表示的IP数量来作为其优先级
        // 数量越多，优先级越低
        $id = (int) $id;
        $index = (new IpCIDR($cidr))->getPrefixLength();
        $p = $conn->prepare('UPDATE speedtest_cidrinfo SET `cidr` = ?, position = ?, accessmethod = ?,
                            isp = ?, ispinfo = ?, `index` = ? WHERE id = ?');
        $p->bind_param('sssssii', $cidr, $position, $accessMethod, $isp, $ispinfo, $index, $id);
        $p->execute();
        $affectedNums = $p->affected_rows;
        $p->close();
        if ($affectedNums) {
            echo json_encode([
                'status' => true,
                'code' => 200,
                'info' => [
                    'message' => 'update cidr info success',
                    'affectedRows' => $affectedNums
                ]
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'code' => 200,
                'info' => [
                    'message' => 'update cidr info fail',
                    'affectedRows' => $affectedNums
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

$conn->close();
