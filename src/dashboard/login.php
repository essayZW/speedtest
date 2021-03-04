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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
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

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
</body>

</html>