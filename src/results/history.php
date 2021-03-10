<?php
require_once(__DIR__ .  '/../utils/validation.php');
require_once('./telemetry_settings.php');
require_once('./idObfuscation.php');
error_reporting(0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>测速历史-<?php echo $__USER_NUMBER__; ?></title>
    <style>
        * {
            margin: 0px;
            padding: 0px;
        }

        h1,
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .header {
            width: 80%;
            height: 60px;
            padding: 0px 10%;
        }

        .header>div {
            float: right;
            height: 100%;
            line-height: 60px;
            padding: 0px 10px;
        }

        table {
            margin: 0px auto;
        }

        table,
        tr,
        th,
        td {
            border: 1px solid #AAAAAA;
        }

        td,
        th {
            padding: 3px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="name">欢迎你: <?php echo $__USER_NAME__; ?></div>
    </div>
    <h1>测速历史-<?php echo $__USER_NUMBER__; ?></h1>
    <?php
    $conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename, $MySql_port);
    if (!$conn) {
        die('<h2>数据查询失败</h2>');
    }
    $conn->query("set time_zone = '+8:00'");
    $stmp = $conn->prepare('SELECT speedtest_infos.id,timestamp,ip,dl,ul,ping,jitter FROM speedtest_infos, speedtest_users 
                                WHERE speedtest_users.id = speedtest_infos.userid
                                AND `number`=?');
    $stmp->bind_param('s', $__USER_NUMBER__);
    $stmp->execute();
    $stmp->bind_result($id, $time, $ip, $dl, $ul, $ping, $jitter);
    $tableHTML5 = '';
    while ($stmp->fetch()) {
        if ($enable_id_obfuscation) {
            $id = obfuscateId($id);
        }
        $tableHTML5 .= "<tr><td>${time}</td><td>${ip}</td><td>${dl}</td><td>${ul}</td><td>${ping}</td><td>${jitter}</td><td><a href=\"/results/?id=${id}\">分享</a></td></tr>";
    }
    if (!strlen($tableHTML5)) { ?>
        <h2>没有记录</h2>
    <?php } else { ?>
        <table>
            <tr>
                <th>时间</th>
                <th>IP</th>
                <th>下载速度/Mbps</th>
                <th>上传速度/Mbps</th>
                <th>ping</th>
                <th>jitter</th>
                <th>其他</th>
            </tr>
            <?php echo $tableHTML5; ?>
        </table>
    <?php } ?>

</body>

</html>