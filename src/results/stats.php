<?php
include_once(__DIR__ .  '/../utils/validation.php');
if (session_status() != PHP_SESSION_ACTIVE)
    session_start();
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html>

<head>
    <title>LibreSpeed - Stats</title>
    <style type="text/css">
        html,
        body {
            margin: 0;
            padding: 0;
            border: none;
            width: 100%;
            min-height: 100%;
        }

        html {
            background-color: hsl(198, 72%, 35%);
            font-family: "Segoe UI", "Roboto", sans-serif;
        }

        body {
            background-color: #FFFFFF;
            box-sizing: border-box;
            width: 100%;
            max-width: 70em;
            margin: 4em auto;
            box-shadow: 0 1em 6em #00000080;
            padding: 1em 1em 4em 1em;
            border-radius: 0.4em;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 300;
            margin-bottom: 0.1em;
        }

        h1 {
            text-align: center;
        }

        table {
            margin: 2em 0;
            width: 100%;
        }

        table,
        tr,
        th,
        td {
            border: 1px solid #AAAAAA;
        }

        th {
            width: 6em;
        }

        td {
            word-break: break-all;
        }
    </style>
</head>

<body>
    <h1>LibreSpeed - Stats</h1>
    <?php
    include_once("telemetry_settings.php");
    require "idObfuscation.php";
    if ($stats_password == "PASSWORD") {
    ?>
        Please set $stats_password in telemetry_settings.php to enable access.
        <?php
    } else if ($_SESSION["logged"] === true) {
        if ($_GET["op"] == "logout") {
            $_SESSION["logged"] = false;
        ?><script type="text/javascript">
                window.location = location.protocol + "//" + location.host + location.pathname;
            </script><?php
                    } else {
                        $conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename, $MySql_port);
                        ?>
            <form action="stats.php" method="GET"><input type="hidden" name="op" value="logout" /><input type="submit" value="Logout" /></form>
            <form action="stats.php" method="GET">
                <h3>Search test results</h6>
                    <input type="hidden" name="op" value="id" />
                    <input type="text" name="id" id="id" placeholder="Test ID" value="" />
                    <input type="submit" value="Find" />
                    <input type="submit" onclick="document.getElementById('id').value=''" value="Show last 100 tests" />
            </form>
            <?php
                        $q = null;
                        if ($_GET["op"] == "id" && !empty($_GET["id"])) {
                            $id = $_GET["id"];
                            if ($enable_id_obfuscation) $id = deobfuscateId($id);
                            $q = $conn->prepare("SELECT speedtest_infos.id, `timestamp`, ip, ispinfo, ua, lang, dl, ul, ping, jitter, `log`, extra, `number`, `name`
								   FROM speedtest_infos, speedtest_users
                                   WHERE speedtest_infos.id = ? AND speedtest_users.id = speedtest_infos.userid");
                            $q->bind_param("i", $id);
                            $q->execute();
                            $q->store_result();
                            $q->bind_result($id, $timestamp, $ip, $ispinfo, $ua, $lang, $dl, $ul, $ping, $jitter, $log, $extra, $number, $name);
                        } else {
                            $q = $conn->prepare("SELECT speedtest_infos.id, `timestamp`, ip, ispinfo, ua, lang, dl, ul, ping, jitter, `log`, extra, `number`, `name`
                                    FROM speedtest_infos, speedtest_users WHERE speedtest_users.id = speedtest_infos.userid ORDER BY `timestamp` DESC LIMIT 0,100");
                            $q->execute();
                            $q->store_result();
                            $q->bind_result($id, $timestamp, $ip, $ispinfo, $ua, $lang, $dl, $ul, $ping, $jitter, $log, $extra, $number, $name);
                        }
                        while (true) {
                            $id = null;
                            $timestamp = null;
                            $ip = null;
                            $ispinfo = null;
                            $ua = null;
                            $lang = null;
                            $dl = null;
                            $ul = null;
                            $ping = null;
                            $jitter = null;
                            $log = null;
                            $extra = null;
                            if (!$q->fetch()) break;
            ?>
                <table>
                    <tr>
                        <th>Test ID</th>
                        <td><?= htmlspecialchars(($enable_id_obfuscation ? (obfuscateId($id) . " (deobfuscated: " . $id . ")") : $id), ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td><?php echo htmlspecialchars($name, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Number</th>
                        <td><?php echo htmlspecialchars($number, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Date and time</th>
                        <td><?= htmlspecialchars($timestamp, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>IP and ISP Info</th>
                        <td><?= $ip ?><br /><?= htmlspecialchars($ispinfo, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>User agent and locale</th>
                        <td><?= $ua ?><br /><?= htmlspecialchars($lang, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Download speed</th>
                        <td><?= htmlspecialchars($dl, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Upload speed</th>
                        <td><?= htmlspecialchars($ul, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Ping</th>
                        <td><?= htmlspecialchars($ping, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Jitter</th>
                        <td><?= htmlspecialchars($jitter, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Log</th>
                        <td><?= htmlspecialchars($log, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Extra info</th>
                        <td><?= htmlspecialchars($extra, ENT_HTML5, 'UTF-8') ?></td>
                    </tr>
                </table>
            <?php
                        }
            ?>
        <?php
                    }
                } else {
                    if ($_GET["op"] == "login" && $_POST["password"] === $stats_password) {
                        $_SESSION["logged"] = true;
        ?><script type="text/javascript">
                window.location = location.protocol + "//" + location.host + location.pathname;
            </script><?php
                    } else {
                        ?>
            <form action="stats.php?op=login" method="POST">
                <h3>Login</h3>
                <input type="password" name="password" placeholder="Password" value="" />
                <input type="submit" value="Login" />
            </form>
    <?php
                    }
                }
    ?>
</body>

</html>