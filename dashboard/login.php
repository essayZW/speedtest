<?php
session_start();
include_once("../results/telemetry_settings.php");
if(isset($_SESSION['logged']) && $_SESSION['logged'] === true) {
    header("Location: /dashboard/index.php");
    die();
}
if(isset($_GET['login'])) {
    $password = $_POST['password'];
    if($password === $stats_password) {
        $_SESSION['logged'] = true;
        header("Location: /dashboard/index.php");
        die();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
</head>
<body>
    <form action="<?= $_SERVER['PHP_SELF'] ?>?login" method="post">
        <input type="password" name="password">
        <input type="submit" value="login">
    </form>
</body>
</html>