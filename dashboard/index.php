<?php
error_reporting(-1);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    header("Location: /dashboard/login.php");
}

include_once("../results/telemetry_settings.php");
$conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename, $MySql_port);
if (!$conn) {
    die("error");
}
$conn->query("set time_zone = '+8:00'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <link rel="stylesheet" href="/static/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="stylesheet" href="/static/css/dashboard.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link rel="stylesheet" href="/static/css/skin-blue.min.css">
    <script src="/static/js/chart.min.js"></script>
</head>

<body class="skin-blue hold-transition sidebar-mini">
    <div class="wrapper">
        <header class="main-header">
            <a href="#/index" class="logo">
                <span class="logo-lg">Speedtest Dashboard</span>
                <span class="logo-mini">Speed</span>
            </a>
            <nav class="navbar navbar-static-top" role="navigation">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>

            </nav>
        </header>
        <aside class="main-sidebar">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="/static/images/user.jpg" class="img-circle" alt="User Image">
                    </div>
                    <div class="pull-left info">
                        <p>admin</p>
                        <a href="javascript:;"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                <ul class="sidebar-menu tree" data-widget="tree">
                    <li class="header">菜单</li>
                    <li class="active">
                        <a href="#/index"><i class="fa fa-table"></i><span>数据概览</span></a>
                    </li>
                    <li>
                        <a href="#/history"><i class="fa fa-sticky-note-o"></i><span>测速记录</span></a>
                    </li>
                </ul>
            </section>
        </aside>
        <div class="content-wrapper">
            <div class="content-header">
                <h1>
                    <span id="pageTitle"></span>
                    <small id="pageInfo"></small>
                </h1>
            </div>
            <div id="routerView" class="content">
                <div class="page" data-hash="/index">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3 id="yesterdayAVGDl">0 Mbps</h3>
                                    <p>昨日平均下载速度</p>
                                </div>
                                <div class="icon">
                                    <ion-icon class="f-white" name="cloud-download-outline"></ion-icon>
                                </div>
                                <a href="javascript:;" class="small-box-footer"></a>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3 id="yesterdayAVGUl">0 Mbps</h3>
                                    <p>昨日平均上传速度</p>
                                </div>
                                <div class="icon">
                                    <ion-icon class="f-white" name="cloud-upload-outline"></ion-icon>
                                </div>
                                <a href="javascript:;" class="small-box-footer"></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <section class="col-lg-6 no-padding">
                            <div class="col-lg-6">
                                <div class="small-box bg-green">
                                    <div class="inner">
                                        <h3 id="yesterdayAVGPing">0 ms</h3>
                                        <p>昨日平均ping</p>
                                    </div>
                                    <div class="icon">
                                        <ion-icon class="f-white" name="time-outline"></ion-icon>
                                    </div>
                                    <a href="javascript:;" class="small-box-footer"></a>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="small-box bg-green">
                                    <div class="inner">
                                        <h3 id="yesterdayAVGJitter">0 ms</h3>
                                        <p>昨日平均jitter</p>
                                    </div>
                                    <div class="icon">
                                        <ion-icon class="f-white" name="time-outline"></ion-icon>
                                    </div>
                                    <a href="javascript:;" class="small-box-footer"></a>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="small-box bg-yellow">
                                    <div class="inner">
                                        <h3 id="yesterdayUserNum">0</h3>
                                        <p>昨日测速人数</p>
                                    </div>
                                    <div class="icon">
                                        <ion-icon class="f-white" name="person-outline"></ion-icon>
                                    </div>
                                    <a href="javascript:;" class="small-box-footer"></a>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="small-box bg-yellow">
                                    <div class="inner">
                                        <h3 id="yesterdayTestNum">0</h3>
                                        <p>昨日测速次数</p>
                                    </div>
                                    <div class="icon">
                                        <ion-icon class="f-white" name="speedometer-outline"></ion-icon>
                                    </div>
                                    <a href="javascript:;" class="small-box-footer"></a>
                                </div>
                            </div>
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs pull-right">
                                    <li class="active">
                                        <a href="#pjchartWeekData" data-toggle="tab" aria-expanded="true">周</a>
                                    </li>
                                    <li>
                                        <a href="#pjchartMonthData" data-toggle="tab" aria-expanded="true">月</a>
                                    </li>
                                    <li class="pull-left header">
                                        <i class="fa fa-area-chart" aria-hidden="true"></i>
                                        平均ping和jitter
                                    </li>
                                </ul>
                                <div class="tab-content no-padding">
                                    <div id="pjchartWeekData" class="tab-pane chart active">
                                        <canvas id="pjChartWeek"></canvas>
                                    </div>
                                    <div id="pjchartMonthData" class="tab-pane chart">
                                        <canvas id="pjChartMonth"></canvas>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section class="col-lg-6">
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs pull-right">
                                    <li class="active">
                                        <a href="#chartWeekData" data-toggle="tab" aria-expanded="true">周</a>
                                    </li>
                                    <li>
                                        <a href="#chartMonthData" data-toggle="tab" aria-expanded="true">月</a>
                                    </li>
                                    <li class="pull-left header">
                                        <i class="fa fa-area-chart" aria-hidden="true"></i>
                                        使用情况
                                    </li>
                                </ul>
                                <div class="tab-content no-padding">
                                    <div id="chartWeekData" class="tab-pane chart active">
                                        <canvas id="userChartWeek"></canvas>
                                    </div>
                                    <div id="chartMonthData" class="tab-pane chart">
                                        <canvas id="userChartMonth"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs pull-right">
                                    <li class="active">
                                        <a href="#duchartWeekData" data-toggle="tab" aria-expanded="true">周</a>
                                    </li>
                                    <li>
                                        <a href="#duchartMonthData" data-toggle="tab" aria-expanded="true">月</a>
                                    </li>
                                    <li class="pull-left header">
                                        <i class="fa fa-area-chart" aria-hidden="true"></i>
                                        平均上传下载速度
                                    </li>
                                </ul>
                                <div class="tab-content no-padding">
                                    <div id="duchartWeekData" class="tab-pane chart active">
                                        <canvas id="duChartWeek"></canvas>
                                    </div>
                                    <div id="duchartMonthData" class="tab-pane chart">
                                        <canvas id="duChartMonth"></canvas>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="page" data-hash="/history">
                    <div class="row">
                    
                    </div>
                </div>
            </div>
        </div>
        <div class="main-footer">
            <strong>Copyright &copy; 2021-2021 essay.</strong>
            All rights reserved.
        </div>
    </div>

    <!-- message modal start -->
    <div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="alertModalLabel"></h4>
                </div>
                <div class="modal-body" id="alertModalMessage"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>
    <!-- message modal end -->

    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.5.1/jquery.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/ionicons@5.4.0/dist/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/static/js/adminlte.min.js"></script>
    <script src="/static/js/router.js"></script>
    <script src="/static/js/dashboard.js"></script>
</body>

</html>
<?php
$conn->close();
?>