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
        $name = get($_POST, 'name');
        $server = get($_POST, 'server');
        // default server port is 80
        $port = get($_POST, 'port', 80);
        $dlURL = get($_POST, 'dlURL', '/backend/garbage.php');
        $ulURL = get($_POST, 'ulURL', '/backend/empty.php');
        $pingURL = get($_POST, 'pingURL', '/backend/empty.php');
        $getIpURL = get($_POST, 'getIpURL'. '/backend/getIP.php');
        $rep = [
            'status' => false,
            'code' => 200,
            'message' => ''
        ];
       
        $checkFlag = false;
        if (!$name) {
            $rep['message'] = 'need testpoints name';
        }
        else if (!$server) {
            $rep['message'] = 'need testpoints server address';
        }
        else if (!is_integer($port)) {
            $rep['message'] = 'server port must be a integer';
        }
        else if ($port < 0 || $port > 65535) {
            $rep['message'] = 'server port must between 0 and 65535';
        }
        else if (!is_string($name) || !is_string($dlURL) || !is_string($ulURL)
                    || !is_string($pingURL) || !is_string($getIpURL)) {
            $rep['message'] = 'invalid params';
        }
        else {
            $checkFlag = true;
        }
        if (!$checkFlag) {
            echo json_encode($rep);
            break;
        }

        try {
            $p = $conn->prepare('INSERT INTO speedtest_testpoints 
                            (`name`, `server`, `port`, `dlURL`, `ulURL`, `pingURL`, `getIpURL`)
                            VALUES (?, ?, ?, ?, ?, ?, ?)');
            $p->bind_param('ssissss', $name, $server, $port, $dlURL, $ulURL, $pingURL, $getIpURL);
            $p->execute();
        }
        catch (Exception $e) {
            $rep['message'] = 'insert fail (SQL error)';
            $checkFlag = false;
        }
        if (!$checkFlag) {
            echo json_encode($rep);
            break;
        }
        if ($p->affected_rows > 0) {
            $rep['status'] = true;
            $rep['message'] = 'success';
        }
        else {
            $rep['status'] = false;
            $rep['message'] = 'insert fail';
        }
        echo json_encode($rep);
        break;
    case 'update':
        needAdmin();
        break;
    case 'delete':
        needAdmin();
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
