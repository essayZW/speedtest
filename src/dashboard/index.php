<?php
error_reporting(-1);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    header("Location: /dashboard/login.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link rel="stylesheet" href="/static/css/skin-blue.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-table.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-editable.css">
    <link rel="stylesheet" href="/static/css/daterangepicker.css">
    <link rel="stylesheet" href="/static/css/dashboard.css">
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
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li>
                            <a href="/"><i class="fa fa-home"></i>&nbsp;返回主页</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <aside class="main-sidebar">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="/static/img/user.jpg" class="img-circle" alt="User Image">
                    </div>
                    <div class="pull-left info">
                        <p>admin</p>
                        <a href="javascript:;"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                <ul class="sidebar-menu tree" data-widget="tree">
                    <li class="header">功能菜单</li>
                    <li class="active">
                        <a href="#/index"><i class="fa fa-table"></i><span>数据概览</span></a>
                    </li>
                    <li>
                        <a href="#/history"><i class="fa fa-sticky-note-o"></i><span>测速记录</span></a>
                    </li>
                    <li class="treeview">
                        <a href="javascript:;">
                            <i class="fa fa-cog"></i>
                            <span>系统设置</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="#/settings/cidr"><i class="fa"><strong>IP</strong></i>CIDR列表</a></li>
                            <li><a href="#/settings/testpoints"><i class="fa fa-server"></i>测速节点</a></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="javascript:;">
                            <i class="fa fa-area-chart"></i>
                            <span>数据图表</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="#/chart/line"><i class="fa fa-circle-o"></i>测速情况变化曲线图</a></li>
                            <li><a href="#/chart/pie"><i class="fa fa-circle-o"></i>各项数据占比饼状图</a></li>
                        </ul>
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
                <!-- 首页 -->
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
                <!-- 首页结束 -->
                <!-- 测速历史查看页面 -->
                <div class="page" data-hash="/history">
                    <div class="row">
                        <form role="form" class="col-lg-6 form-inline">
                            <div class="form-group">
                                <label for="searchInput">搜索</label>
                                <input type="text" class="form-control" id="searchInput" placeholder="模糊搜索">
                            </div>
                            <div class="form-group">
                                <label for="">搜索类型</label>
                                <label class="radio-inline">
                                    <input type="radio" name="searchMethod" id="optionsRadios1" value="ip" checked> IP
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="searchMethod" id="optionsRadios2" value="unumber"> 学号
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="searchMethod" id="optionsRadios3" value="time"> 时间
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="searchMethod" id="optionsRadios" value="name"> 姓名
                                </label>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" id="searchButt">搜索</button>
                            </div>
                        </form>
                    </div>
                    <div class="row history-area">
                        <div class="col-lg-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">测速历史记录</h3>
                                    <div class="box-tools" id="tableToolArea"></div>
                                </div>
                                <div class="box-body">
                                    <table id="historyDataTable"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 测速历史查看页面结束 -->
                <!-- 折线图页面 -->
                <div class="page" data-hash="/chart/line">
                    <div class="row">
                        <form role="form" class="col-lg-12 form-inline">
                            <div class="form-group">
                                <label>数据时间范围:</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input type="text" class="form-control pull-right date-range-picker" id="lineChartDatePicker">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>时间步长:</label>
                                <select class="form-control" id="lineChartStepSelector">
                                    <option value="single">一条</option>
                                    <option value="hour">一小时</option>
                                    <option value="day" selected>一天</option>
                                    <option value="week">一周</option>
                                    <option value="dayHour" title="以一小时为单位，但是将每天的同一小时合并起来统计">一小时(合并)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary" id="useinfoChartButt">确认</button>
                            </div>
                        </form>
                    </div>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3 id="lineChartUserNum">0</h3>
                                    <p>测速人数</p>
                                </div>
                                <div class="icon">
                                    <ion-icon class="f-white" name="person-outline"></ion-icon>
                                </div>
                                <a href="javascript:;" class="small-box-footer"></a>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3 id="lineChartTestNum">0</h3>
                                    <p>测速次数</p>
                                </div>
                                <div class="icon">
                                    <ion-icon class="f-white" name="speedometer-outline"></ion-icon>
                                </div>
                                <a href="javascript:;" class="small-box-footer"></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-area-chart"></i>测速人数以及每人测速次数统计图</h3>
                                    <div class="pull-right box-tools">
                                        <button type="button" class="btn btn-default pull-right" data-widget="collapse" data-toggle="tooltip" title="折叠" data-original-title="Collapse">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                        <button class="btn btn-default pull-right col-size-minus" data-selector=".useinfoChartArea">
                                            <i class="fa fa-search-minus"></i>
                                        </button>
                                        <button class="btn btn-default pull-right col-size-plus" data-selector=".useinfoChartArea">
                                            <i class="fa fa-search-plus"></i>
                                        </button>
                                        <button class="btn btn-primary pull-right export-canvas" data-target="#useinfoChart">
                                            导出为PNG图片
                                        </button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="col-lg-10 col-lg-offset-1 useinfoChartArea" data-size="10">
                                        <canvas id="useinfoChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-area-chart"></i><span>测速下载和上传速度统计图</span></h3>
                                    <div class="pull-right box-tools">
                                        <button type="button" class="btn btn-default pull-right" data-widget="collapse" data-toggle="tooltip" title="折叠" data-original-title="Collapse">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                        <button class="btn btn-default pull-right col-size-minus" data-selector=".uldlinfoChartArea">
                                            <i class="fa fa-search-minus"></i>
                                        </button>
                                        <button class="btn btn-default pull-right col-size-plus" data-selector=".uldlinfoChartArea">
                                            <i class="fa fa-search-plus"></i>
                                        </button>
                                        <button class="btn btn-primary pull-right export-canvas" data-target="#uldlinfoChart">
                                            导出为PNG图片
                                        </button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="col-lg-10 col-lg-offset-1 uldlinfoChartArea" data-size="10">
                                        <canvas id="uldlinfoChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-area-chart"></i>测速ping和jitter统计图</h3>
                                    <div class="pull-right box-tools">
                                        <button type="button" class="btn btn-default pull-right" data-widget="collapse" data-toggle="tooltip" title="折叠" data-original-title="Collapse">
                                            <i class="fa fa-minus"></i>
                                        </button>
                                        <button class="btn btn-default pull-right col-size-minus" data-selector=".pjinfoChartArea">
                                            <i class="fa fa-search-minus"></i>
                                        </button>
                                        <button class="btn btn-default pull-right col-size-plus" data-selector=".pjinfoChartArea">
                                            <i class="fa fa-search-plus"></i>
                                        </button>
                                        <button class="btn btn-primary pull-right export-canvas" data-target="#pjinfoChart">
                                            导出为PNG图片
                                        </button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="col-lg-10 col-lg-offset-1 pjinfoChartArea" data-size="10">
                                        <canvas id="pjinfoChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 折线图页面结束 -->
                <!-- 饼状图页面 -->
                <div class="page" data-hash="/chart/pie">
                    <div class="row">
                        <form role="form" class="col-lg-12 form-inline">
                            <div class="form-group">
                                <label>数据时间范围:</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input type="text" class="form-control pull-right date-range-picker" id="pieinfoDatePicker">
                                </div>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary" id="pieinfoChartButt">确认</button>
                            </div>
                        </form>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title pie-box-title">用户测速次数占比</h3>
                                    <div class="box-tools pie-dividedata-input-area">
                                        <div class="input-group input-group-sm pull-right">
                                            <input type="text" class="form-control pull-right" value="5, 10, 20" id="userTestDivisionInput" placeholder="请输入划分的区间,以逗号分隔">
                                            <div class="input-group-btn">
                                                <button type="submit" class="btn btn-default pie-division-butt"><i class="fa fa-check"></i></button>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary pull-right export-canvas" data-target="#userTestPieChart">
                                            导出为PNG图片
                                        </button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="col-lg-6 col-lg-offset-3">
                                        <div class="col-lg-6 col-lg-offset-3">
                                            <div class="text-center">每位用户测速次数饼图</div>
                                            <canvas id="userTestPieChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title pie-box-title">下载、上传速度占比</h3>
                                    <div class="box-tools pull-right pie-dividedata-input-area">
                                        <div class="input-group input-group-sm pull-right">
                                            <input type="text" class="form-control pull-right" id="dlulDivisionInput" value="100, 200, 500, 800, 1000" placeholder="请输入划分的区间,以逗号分隔">
                                            <div class="input-group-btn">
                                                <button type="submit" class="btn btn-default pie-division-butt"><i class="fa fa-check"></i></button>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary pull-right export-multiple-canvas" data-target="#dlPieChart|#ulPieChart">
                                            导出为PNG图片
                                        </button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="col-lg-3 col-lg-offset-3">
                                        <div class="text-center">下载速度饼图</div>
                                        <canvas id="dlPieChart"></canvas>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="text-center">上传速度饼图</div>
                                        <canvas id="ulPieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title pie-box-title">ping、jitter占比</h3>
                                    <div class="box-tools pie-dividedata-input-area">
                                        <div class="input-group pull-right input-group-sm">
                                            <input type="text" class="form-control pull-right" id="pjDivisionInput" value="3, 5, 10, 100" placeholder="请输入划分的区间,以逗号分隔">
                                            <div class="input-group-btn">
                                                <button type="submit" class="btn btn-default pie-division-butt"><i class="fa fa-check"></i></button>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary pull-right export-multiple-canvas" data-target="#pingPieChart|#jitterPieChart">
                                            导出为PNG图片
                                        </button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="col-lg-3 col-lg-offset-3">
                                        <div class="text-center">ping饼图</div>
                                        <canvas id="pingPieChart"></canvas>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="text-center">jitter饼图</div>
                                        <canvas id="jitterPieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 饼状图页面结束 -->
                <!-- CIDR列表设置页面 -->
                <div class="page" data-hash="/settings/cidr">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><span>CIDR列表</span><small>点击字段可修改内容</small></h3>
                                    <div class="box-tools pull-right">
                                        <div class="pull-right" id="cidrTableToolArea"></div>
                                        <div class="pull-right"><button class="btn btn-primary" id="showCidrInfoModal">添加CIDR信息</button></div>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <table id="cidrTable" class="table-event-handler"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- CIDR列表设置页面结束 -->
                <!-- 测速节点设置页面 -->
                <div class="page" data-hash="/settings/testpoints">
                    <div class="row">测速节点设置</div>
                </div>
                <!-- 测速节点设置页面结束 -->
            </div>
        </div>
        <div class="main-footer">
            <strong>Copyright &copy; 2021-2021 BUCT.</strong>
            All rights reserved.
            <strong>Powered by <a href="https://github.com/essayZW/speedtest" target="_blank">essay</a></strong>
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
    <!-- CIDR info modal start -->
    <div class="modal fade" id="cidrModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">CIDR信息填写</h4>
                </div>
                <div class="modal-body">
                    <form role="form">
                        <div class="form-group">
                            <label for="cidrInput" class="required">CIDR</label>
                            <input type="text" class="form-control" id="cidrInput" placeholder="形如10.0.0.0/8" name="cidr" required>
                        </div>
                        <div class="form-group">
                            <label for="cidrPositionInput">接入位置</label></label>
                            <input type="text" class="form-control" id="cidrPositionInput" placeholder="CIDR对应的IP地址的接入地址，可以为空" name="position">
                        </div>
                        <div class="form-group">
                            <label for="cidrAccessMethodInput">接入方式</label>
                            <input type="text" class="form-control" id="cidrAccessMethodInput" placeholder="CIDR对应的IP地址的接入方式，可以为空" name="accessMethod">
                        </div>
                        <div class="form-group">
                            <label for="cidrIspInput">ISP</label>
                            <input type="text" class="form-control" id="cidrIspInput" placeholder="CIDR对应的ISP, 可以为空" name="isp">
                        </div>
                        <div class="form-group">
                            <label for="cidrIspInfoInput">ISP信息</label>
                            <input type="text" class="form-control" id="cidrIspInfoInput" placeholder="详细的ISP信息，可以为空" name="ispinfo">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" id="addCidrInfo">添加</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>
    <!-- CIDR info modal end-->

    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://unpkg.com/ionicons@5.4.0/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/static/js/bootstrap/bootstrap.min.js"></script>
    <script src="/static/js/bootstrap/bootstrap-table.min.js"></script>
    <script src="/static/js/bootstrap/bootstrap-table-editable.min.js"></script>
    <script src="/static/js/bootstrap/bootstrap-table-zh-CN.min.js"></script>
    <script src="/static/js/bootstrap/bootstrap-editable.min.js"></script>
    <script src="/static/js/chart.min.js"></script>
    <script src="/static/js/palette.js"></script>
    <script src="/static/js/adminlte.min.js"></script>
    <script src="/static/js/moment.min.js"></script>
    <script src="/static/js/daterangepicker.js"></script>
    <script src="/static/js/router.js"></script>
    <script src="/static/js/dashboard.js"></script>
</body>

</html>