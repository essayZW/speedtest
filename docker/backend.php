<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no" />
    <meta charset="UTF-8" />
    <link rel="shortcut icon" href="favicon.ico">
    <title><?= getenv('TITLE') ?: 'LibreSpeed Example' ?></title>
    <style>
        * {
            margin: 0px;
            padding: 0px;
        }

        h1, h2 {
            text-align: center;
            padding-bottom: 20px;
            color: #404040;
        }
        h1 {
            padding-top: 100px;
        }

        div {
            text-align: center;
        }
    </style>
</head>

<body>
    <h1><?= getenv('TITLE') ?: 'LibreSpeed Example' ?></h1>
    <h2>This is a speed test node for speedtest</h2>
    <div>To run a speed test please click <a href="<?= getenv('FRONT_ADDRESS') ?: 'javascript:;' ?>">here</a></div>
    <br>
    <div><a href="https://github.com/essayZW/speedtest">Source code</a></div>
</body>

</html>
