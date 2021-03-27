<?php
include_once('./init.php');

$operation = get($_GET, 'operation');

switch ($operation) {
    case 'select':
        $repData = [];
        $p = $conn->prepare('SELECT id, `name`, `server`, port, dlURL, ulURL, pingURL, getIpURL
                            FROM speedtest_testpoints');
        $p->execute();
        $p->bind_result($id, $name, $server, $port, $dlURL, $ulURL, $pingURL, $getIpURL);
        while ($p->fetch()) {
            $repData[] = [
                'id' => $id,
                'name' => $name,
                'server' => $server,
                'port' => $port,
                'dlURL' => $dlURL,
                'ulURL' => $ulURL,
                'pingURL' => $pingURL,
                'getIpURL' => $getIpURL
            ];
        }
        $p->close();
        echo json_encode([
            'status' => true,
            'code' => 200,
            'data' => $repData,
            'message' => 'success'
        ]);
        break;
    case 'insert':
        needAdmin();
        $rep = validateParams();
        if (!$rep['status']) {
            unset($rep['data']);
            echo json_encode($rep);
            break;
        }
        $name = $rep['data']['name'];
        $server = $rep['data']['server'];
        $port = $rep['data']['port'];
        $dlURL = $rep['data']['dlURL'];
        $ulURL = $rep['data']['ulURL'];
        $pingURL = $rep['data']['pingURL'];
        $getIpURL = $rep['data']['getIpURL'];
        unset($rep['data']);
        try {
            $p = $conn->prepare('INSERT INTO speedtest_testpoints 
                            (`name`, `server`, `port`, `dlURL`, `ulURL`, `pingURL`, `getIpURL`)
                            VALUES (?, ?, ?, ?, ?, ?, ?)');
            if (!$p) {
                throw new Exception('sql prepare error');
            }
            $p->bind_param('ssissss', $name, $server, $port, $dlURL, $ulURL, $pingURL, $getIpURL);
            $p->execute();
        }
        catch (Exception $e) {
            $rep['info']['message'] = 'insert fail (SQL error)';
            $rep['status'] = false;
            echo json_encode($rep);
            break;
        }
        if ($p->affected_rows > 0) {
            $rep['status'] = true;
            $rep['info']['message'] = 'success';
        }
        else {
            $rep['status'] = false;
            $rep['info']['message'] = 'insert fail';
        }
        echo json_encode($rep);
        break;
    case 'update':
        needAdmin();
        $rep = validateParams();
        if (!$rep['status']) {
            unset($rep['data']);
            echo json_encode($rep);
            break;
        }
        $name = $rep['data']['name'];
        $server = $rep['data']['server'];
        $port = $rep['data']['port'];
        $dlURL = $rep['data']['dlURL'];
        $ulURL = $rep['data']['ulURL'];
        $pingURL = $rep['data']['pingURL'];
        $getIpURL = $rep['data']['getIpURL'];
        unset($rep['data']);
        $id = get($_POST, 'id');
        if ($id == null) {
            $rep['status'] = false;
            $rep['info']['message'] = 'need id';
            echo json_encode($rep);
            break;
        }
        $id = (int) $id;

        try {
            $p = $conn->prepare('UPDATE speedtest_testpoints SET
                                `name` = ?, `server` = ?, `port` = ?,
                                `dlURL` = ?, `ulURL` = ?, `pingURL` = ?, `getIpURL` = ? 
                                WHERE id = ?');
            if (!$p) {
                throw new Exception('sql prepare error');
            }
            $p->bind_param('ssissssi', $name, $server, $port, $dlURL, $ulURL, $pingURL, $getIpURL, $id);
            $p->execute();
        }
        catch (Exception $e) {
            $rep['info']['message'] = 'update fail (SQL error): '. $e->getMessage();
            $rep['status'] = false;
            echo json_encode($rep);
            break;
        }
        if ($p->affected_rows > 0) {
            $rep['status'] = true;
            $rep['info']['message'] = 'success';
        }
        else {
            $rep['status'] = false;
            $rep['info']['message'] = 'update fail';
        }
        echo json_encode($rep);
        break;
    case 'delete':
        needAdmin();
        $id = get($_POST, 'id');
        if ($id == null) {
            echo json_encode([
                'status' => false,
                'code' => 200,
                'info' => 'need id'
            ]);
            break;
        }
        $id = (int) $id;
        try {
            $p = $conn->prepare('DELETE FROM speedtest_testpoints WHERE id = ?');
            if (!$p) {
                throw new Exception($conn->error);
            }
            $p->bind_param('i', $id);
            $p->execute();
        }
        catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'code' => 200,
                'info' => [
                    'message' => 'delete fail (SQL error)'
                ]
            ]);
            break;
        }
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
$conn->close();


/**
 * 验证参数，并对参数进行处理
 *
 * @return array
 */
function validateParams() {
    $name = get($_POST, 'name');
    $server = get($_POST, 'server');
    // default server port is 80
    $port = (int) get($_POST, 'port', 80);
    $dlURL = get($_POST, 'dlURL', '/garbage.php');
    $ulURL = get($_POST, 'ulURL', '/empty.php');
    $pingURL = get($_POST, 'pingURL', '/empty.php');
    $getIpURL = get($_POST, 'getIpURL', '/getIP.php');
    $rep = [
        'status' => false,
        'info' => [
            'message' => ""
        ],
        'data' => []
    ];

    $checkFlag = false;
    if (!$name) {
        $rep['info']['message'] = 'need testpoints name';
    } else if (!$server) {
        $rep['info']['message'] = 'need testpoints server address';
    } else if ($port < 0 || $port > 65535) {
        $rep['info']['message'] = 'server port must between 0 and 65535';
    } else if (
        !is_string($name) || !is_string($dlURL) || !is_string($ulURL)
        || !is_string($pingURL) || !is_string($getIpURL)
    ) {
        $rep['info']['message'] = 'invalid params';
    } else {
        $checkFlag = true;
    }
    if (!$checkFlag) {
        return $rep;
    }
    $rep['status'] = true;
    if (!preg_match('/^(http:\/\/|https:\/\/).+$/', $server)) {
        // 默认http协议
        $server = 'http://' . $server;
    }
    if (!isset($_POST['port'])) {
        // 未传递默认port，根据Server address 的HTTP协议判断默认端口
        if (strpos($server, 'https://') === 0) {
            $port = 443;
        } else {
            $port = 80;
        }
    }
    $rep['data'] = [
        'name' => $name,
        'server' => $server,
        'port' => $port,
        'dlURL' => $dlURL,
        'ulURL' => $ulURL,
        'pingURL' => $pingURL,
        'getIpURL' => $getIpURL
    ];
    return $rep;
}
