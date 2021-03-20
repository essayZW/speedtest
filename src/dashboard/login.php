<?php
session_start();
include_once("../results/telemetry_settings.php");
if (isset($_SESSION['logged']) && $_SESSION['logged'] === true) {
    header("Location: /dashboard/index.php");
    die();
}
if (isset($_GET['login'])) {
    $password = $_POST['password'];
    if ($password === $stats_password) {
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
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <title>登陆</title>
    <style>
        form {
            max-width: 330px;
            margin: 0px auto;
        }
        form > button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form class="form-signin" action="<?= $_SERVER['PHP_SELF'] ?>?login" method="POST">
            <h2 class="form-signin-heading">Please sign in</h2>
            <label for="inputPassword" class="sr-only">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
        </form>
    </div>

    <script src="/static/js/bootstrap/bootstrap-table.min.js"></script>
</body>

</html>