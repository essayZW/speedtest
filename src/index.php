<?php
require_once('./utils/validation.php');
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no" />
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="/static/css/index.css">
    <link rel="shortcut icon" href="favicon.ico">
    <script type="text/javascript" src="/static/js/speedtest.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Define default test point server name
        window.defaultServerInfo = {};
        window.defaultServerInfo.name = "<?= getenv('TITLE') ?: 'LibreSpeed Example' ?>";
        //INITIALIZE SPEEDTEST
        var s = new Speedtest(); //create speedtest object
        // for dev
        <?php if (getenv("TELEMETRY") == "true") { ?>
            s.setParameter("telemetry_level", "basic");
        <?php } ?>
        <?php if (getenv("DISABLE_IPINFO") == "true") { ?>
            s.setParameter("getIp_ispInfo", "false");
        <?php } ?>
        <?php if (getenv("DISTANCE")) { ?>
            s.setParameter("getIp_ispInfo_distance", "<?= getenv("DISTANCE") ?>");
        <?php } ?>
    </script>
    <script src="/static/js/index.js"></script>
    <style type="text/css">
    </style>
    <title><?= getenv('TITLE') ?: 'LibreSpeed Example' ?></title>
</head>

<body>
    <div class="header">
        <div class="name">欢迎你: <?php echo $__USER_NAME__; ?></div>
    </div>
    <h1><?= getenv('TITLE') ?: 'LibreSpeed Example' ?></h1>
    <div id="testWrapper">
        <div id="startStopBtn" class="forbidden" title="测速节点信息加载中" onclick="startStop()"></div><br />
        <div id="serverSelectArea">
            <div>
                <label for="serverList">Server:</label>
                <select id="serverList">
                </select>
            </div>
        </div>
        <?php if (getenv("TELEMETRY") == "true") { ?>
            <a class="privacy" href="#" onclick="I('privacyPolicy').style.display=''">Privacy</a>
        <?php } ?>
        <div id="test">
            <div class="testGroup">
                <div class="testArea2">
                    <div class="testName">Ping</div>
                    <div id="pingText" class="meterText" style="color:#AA6060"></div>
                    <div class="unit">ms</div>
                </div>
                <div class="testArea2">
                    <div class="testName">Jitter</div>
                    <div id="jitText" class="meterText" style="color:#AA6060"></div>
                    <div class="unit">ms</div>
                </div>
            </div>
            <div class="testGroup">
                <div class="testArea">
                    <div class="testName">Download</div>
                    <canvas id="dlMeter" class="meter"></canvas>
                    <div id="dlText" class="meterText"></div>
                    <div class="unit">Mbps</div>
                </div>
                <div class="testArea">
                    <div class="testName">Upload</div>
                    <canvas id="ulMeter" class="meter"></canvas>
                    <div id="ulText" class="meterText"></div>
                    <div class="unit">Mbps</div>
                </div>
            </div>
            <div id="ipArea">
                <div id="ipAndIsp"></div>
                <div id="ipPositionAndAccessMethod"></div>
            </div>
            <div id="shareArea" style="display:none">
                <h3>Share results</h3>
                <p>Test ID: <span id="testId"></span></p>
                <input type="text" value="" id="resultsURL" readonly="readonly" onclick="this.select();this.focus();this.select();document.execCommand('copy');alert('Link copied')" />
                <img src="" id="resultsImg" />
            </div>
        </div>
        <a href="#" onclick="I('netCommonsense').style.display='';">网速知识</a>
        <a href="/results/history.php" target="_blank">测速历史</a>
        <a href="https://github.com/essayZW/speedtest">Source code</a>
    </div>
    <div id="privacyPolicy" style="display:none">
        <h2>Privacy Policy</h2>
        <p>This HTML5 Speedtest server is configured with telemetry enabled.</p>
        <h4>What data we collect</h4>
        <p>
            At the end of the test, the following data is collected and stored:
        <ul>
            <li>Test ID</li>
            <li>Time of testing</li>
            <li>Test results (download and upload speed, ping and jitter)</li>
            <li>IP address</li>
            <li>ISP information</li>
            <li>Approximate location (inferred from IP address, not GPS)</li>
            <li>User agent and browser locale</li>
            <li>Test log (contains no personal information)</li>
            <li>Login ID</li>
            <li>Test point server ID</li>
        </ul>
        </p>
        <h4>How we use the data</h4>
        <p>
            Data collected through this service is used to:
        <ul>
            <li>Allow sharing of test results (sharable image for forums, etc.)</li>
            <li>To improve the service offered to you (for instance, to detect problems on our side)</li>
        </ul>
        No personal information is disclosed to third parties.
        </p>
        <h4>Your consent</h4>
        <p>
            By starting the test, you consent to the terms of this privacy policy.
        </p>
        <h4>Data removal</h4>
        <p>
            If you want to have your information deleted, you need to provide either the ID of the test or your IP address. This is the only way to identify your data, without this information we won't be able to comply with your request.<br /><br />
            Contact this email address for all deletion requests: <a href="mailto:<?= getenv("EMAIL") ?>"><?= getenv("EMAIL") ?></a>.
        </p>
        <br /><br />
        <a class="privacy" href="#" onclick="I('privacyPolicy').style.display='none'">Close</a><br />
    </div>
    <div id="netCommonsense" style="display:none;">
        <h2>下载、上传</h2>
        <p>网络数据传输分为发送数据和接收数据两部分。上传就是向外部发送数据，下载为从外部接收数据。他们都受网络带宽和设备性能制约。 在日常网络传输中大致1Mbps=1024/8Kb/s=128Kb/s(1/8)。例如上行的网络带宽为100Mbps,那么最大上传速度就是12800Kb/s，也就是12.5Mb/s。</p>
        <p>用户申请的宽带业务速率指技术上所能达到的最大理论速率值，用户上网时还受到用户电脑软硬件的配置、所浏览网站的位置、对端网站带宽等情况的影响，故用户上网时的速率通常低于理论速率值。 理论上：2M（即2Mb/s）宽带理论速率是：256KB/s（即2048Kb/s），实际速率大约为103--200KB/s；4M（即4Mb/s）的宽带理论速率是：512KB/s，实际速率大约为200---440KB/s。以此类推。</p>
        <h2>宽带速率对照表</h2>
        <table>
            <caption>以下标准是宽带速率的基础参考值，可对照自己的签约宽带查看宽带速率。</caption>
            <thead align="center">
                <tr>
                    <th>常见宽带（M）</th>
                    <th>理论最高速率（Mbps）</th>
                    <th>理论最高速率（KB/s）</th>
                    <th>常见下载速率（KB/s）</th>
                </tr>
            </thead>
            <tbody align="center">
                <tr>
                    <td>1</td>
                    <td> 1</td>
                    <td> 128</td>
                    <td> 77~128</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td> 2</td>
                    <td> 256</td>
                    <td> 154~256</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td> 3</td>
                    <td> 384</td>
                    <td> 231~384</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td> 4</td>
                    <td> 512</td>
                    <td> 307~512</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td> 6</td>
                    <td> 620</td>
                    <td> 462~620</td>
                </tr>
                <tr>
                    <td>8</td>
                    <td> 8</td>
                    <td> 1024</td>
                    <td> 614~1024</td>
                </tr>
                <tr>
                    <td>10</td>
                    <td> 10</td>
                    <td> 1280</td>
                    <td> 768~1280</td>
                </tr>
                <tr>
                    <td>12</td>
                    <td> 12</td>
                    <td> 1536</td>
                    <td> 922~1536</td>
                </tr>
                <tr>
                    <td>20</td>
                    <td> 20</td>
                    <td> 2560</td>
                    <td> 1536~2560</td>
                </tr>
                <tr>
                    <td>30</td>
                    <td> 30</td>
                    <td> 3840</td>
                    <td> 2560~3840</td>
                </tr>
                <tr>
                    <td>50</td>
                    <td> 50</td>
                    <td> 6400</td>
                    <td> 3840~6400</td>
                </tr>
                <tr>
                    <td>100</td>
                    <td> 100</td>
                    <td> 12800</td>
                    <td> 7680~12800</td>
                </tr>
            </tbody>
        </table>
        <h2>计算方法</h2>
        <p>在计算机科学中，bit是表示信息的最小单位，叫做二进制位；一般用0和1表示。Byte叫做字节，由8个位（8bit）组成一个字节(1Byte)，用于表示计算机中的一个字符。bit与Byte之间可以进行换算，其换算关系为：1Byte=8bit（或简写为：1B=8b）；在实际应用中一般用简称，即1bit简写为1b(注意是小写英文字母b)，1Byte简写为1B（注意是大写英文字母B）。</p>
        <p>在计算机网络或者是网络运营商中，一般，宽带速率的单位用bps(或b/s)表示；bps表示比特每秒即表示每秒钟传输多少位信息，是bit per second的缩写。在实际所说的1M带宽的意思是1Mbps（是兆比特每秒Mbps不是兆字节每秒MBps）。</p>
        <p>换算公式：<strong>1B=8b 1B/s=8b/s(或1Bps=8bps)。</strong></p>
        <p>规范提示：实际书写规范中B应表示Byte(字节)，b应表示bit(比特)，但在平时的实际书写中有的把bit和Byte都混写为b ，如把Mb/s和MB/s都混写为Mb/s，导致人们在实际计算中因单位的混淆而出错。</p>
        <p>实例： 在我们实际上网应用中，下载软件时常常看到诸如下载速度显示为128KBps（KB/s），103KB/s等等宽带速率大小字样，因为ISP提供的线路带宽使用的单位是比特，而一般下载软件显示的是字节（1字节=8比特），所以要通过换算，才能得实际值。然而我们可以按照换算公式换算一下：</p>
        <p><strong>128KB/s=128×8(Kb/s)=1024Kb/s=1Mb/s即128KB/s=1Mb/s</strong></p>
        <h2>PING</h2>
        <p>PING指一个数据包从用户的设备发送到测速点，然后再立即从测速点返回用户设备的来回时间。</p>
        <p>一般以毫秒（ms）计算</p>
        <p>一般PING在0~100ms都是正常的速度，不会有较为明显的卡顿。</p>
        <h2>抖动</h2>
        <p>网络中的延迟是指信息从发送到接收经过的延迟时间；而抖动是指最大延迟与最小延迟的时间差，如最大延迟是20毫秒，最小延迟为5毫秒，那么网络抖动就是15毫秒，它主要标识一个网络的稳定性。</p>
        <h2>丢包</h2>
        <p>丢包是指一个或多个数据包的数据无法通过网络到达目的地。可能原因是多方面的，或是网络中多路径衰落造成信号衰减；或是通道阻塞造成丢包；或是损坏的数据包被拒绝通过；或是有缺陷的网上硬件有缺陷，网上驱动程序有故障。</p>
        <h2>IP地址</h2>
        <p>IP地址被用来给联网的电脑一个编号。大家日常见到的情况是每台联网的电脑上都需要有IP地址，才能正常通信。我们可以把“个人电脑”比作“一台电话”，那么“IP地址”就相当于“电话号码”。</p>
        <h2>网速测试建议指南</h2>
        <p>
        <ol>
            <li>关闭其它正在运行中的网络应用程序，不要同时加载其它网页和软件。</li>
            <li>尝试在不同时段执行这个下载速度测试，最好是在非繁忙时间来作多次测试得出的结果,取平均值,会比较准确。</li>
            <li>另外由于宽带测速受包括你电脑的配置、CPU频率及系统内存的容量、是否安装了网络防火墙、计算机是否有病毒、上网终端设备的性能、测速服务器是否处于忙时等因素的影响，在一定程度上会影响测速的结果。</li>
        </ol>
        </p>
        <a class="privacy" href="#" onclick="I('netCommonsense').style.display='none'">关闭</a><br />
    </div>
    <script type="text/javascript">
        setTimeout(function() {
            initUI()
        }, 100);
    </script>
</body>

</html>
