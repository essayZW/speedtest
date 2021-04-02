function I(i) {
  return document.getElementById(i);
}
var meterBk = /Trident.*rv:(\d+\.\d+)/i.test(navigator.userAgent) ? "#EAEAEA" : "#80808040";
var dlColor = "#6060AA",
  ulColor = "#616161";
var progColor = meterBk;

//CODE FOR GAUGES
function drawMeter(c, amount, bk, fg, progress, prog) {
  var ctx = c.getContext("2d");
  var dp = window.devicePixelRatio || 1;
  var cw = c.clientWidth * dp,
    ch = c.clientHeight * dp;
  var sizScale = ch * 0.0055;
  if (c.width == cw && c.height == ch) {
    ctx.clearRect(0, 0, cw, ch);
  } else {
    c.width = cw;
    c.height = ch;
  }
  ctx.beginPath();
  ctx.strokeStyle = bk;
  ctx.lineWidth = 12 * sizScale;
  ctx.arc(c.width / 2, c.height - 58 * sizScale, c.height / 1.8 - ctx.lineWidth, -Math.PI * 1.1, Math.PI * 0.1);
  ctx.stroke();
  ctx.beginPath();
  ctx.strokeStyle = fg;
  ctx.lineWidth = 12 * sizScale;
  ctx.arc(c.width / 2, c.height - 58 * sizScale, c.height / 1.8 - ctx.lineWidth, -Math.PI * 1.1, amount * Math.PI * 1.2 - Math.PI * 1.1);
  ctx.stroke();
  if (typeof progress !== "undefined") {
    ctx.fillStyle = prog;
    ctx.fillRect(c.width * 0.3, c.height - 16 * sizScale, c.width * 0.4 * progress, 4 * sizScale);
  }
}

function mbpsToAmount(s) {
  return 1 - (1 / (Math.pow(1.3, Math.sqrt(s))));
}

function format(d) {
  d = Number(d);
  if (d < 10) return d.toFixed(2);
  if (d < 100) return d.toFixed(1);
  return d.toFixed(0);
}

//UI CODE
var uiData = null;
// 当其为true时意味着测速节点信息已经加载好，可以开启测速
var pointReady = false;
function startStop() {
  if (!pointReady) return;
  if (s.getState() == 3) {
    //speedtest is running, abort
    s.abort();
    data = null;
    I("startStopBtn").className = "";
    initUI();
  } else {
    //test is not running, begin
    I("startStopBtn").className = "running";
    I("shareArea").style.display = "none";
    s.onupdate = function (data) {
      uiData = data;
    };
    s.onend = function (aborted) {
      I("startStopBtn").className = "";
      updateUI(true);
      if (!aborted) {
        //if testId is present, show sharing panel, otherwise do nothing
        try {
          var testId = uiData.testId;
          if (testId != null) {
            var shareURL = window.location.href.substring(0, window.location.href.lastIndexOf("/")) + "/results/?id=" + testId;
            I("resultsImg").src = shareURL;
            I("resultsURL").value = shareURL;
            I("testId").innerHTML = testId;
            I("shareArea").style.display = "";
          }
        } catch (e) {}
      }
    };
    s.start();
  }
}
//this function reads the data sent back by the test and updates the UI
function updateUI(forced) {
  if (!forced && s.getState() != 3) return;
  if (uiData == null) return;
  var status = uiData.testState;
  // 因为再调用abort之后不知道为啥该函数会再执行一次
  // 此时所有uiData已经为空，因此不需要显示链接符号
  let linkChar = '';
  if (uiData.clientIp.length && uiData.isp.length)
    linkChar = '-';
  I("ipAndIsp").textContent = uiData.clientIp + linkChar + uiData.isp;
  if (uiData.ipPosition.length && uiData.ipAccessMethod.length) {
    linkChar = '-';
  } else {
    linkChar = '';
  }
  I("ipPositionAndAccessMethod").textContent = uiData.ipPosition + linkChar + uiData.ipAccessMethod;
  I("dlText").textContent = (status == 1 && uiData.dlStatus == 0) ? "..." : format(uiData.dlStatus);
  drawMeter(I("dlMeter"), mbpsToAmount(Number(uiData.dlStatus * (status == 1 ? oscillate() : 1))), meterBk, dlColor, Number(uiData.dlProgress), progColor);
  I("ulText").textContent = (status == 3 && uiData.ulStatus == 0) ? "..." : format(uiData.ulStatus);
  drawMeter(I("ulMeter"), mbpsToAmount(Number(uiData.ulStatus * (status == 3 ? oscillate() : 1))), meterBk, ulColor, Number(uiData.ulProgress), progColor);
  I("pingText").textContent = format(uiData.pingStatus);
  I("jitText").textContent = format(uiData.jitterStatus);
}

function oscillate() {
  return 1 + 0.02 * Math.sin(Date.now() / 100);
}
//update the UI every frame
window.requestAnimationFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.msRequestAnimationFrame || (function (callback, element) {
  setTimeout(callback, 1000 / 60);
});

function frame() {
  requestAnimationFrame(frame);
  updateUI();
}
frame(); //start frame loop
//function to (re)initialize UI
function initUI() {
  drawMeter(I("dlMeter"), 0, meterBk, dlColor, 0);
  drawMeter(I("ulMeter"), 0, meterBk, ulColor, 0);
  I("dlText").textContent = "";
  I("ulText").textContent = "";
  I("pingText").textContent = "";
  I("jitText").textContent = "";
  I("ipAndIsp").textContent = "";
  I("ipPositionAndAccessMethod").textContent = "";
}
// 加载测速节点列表
axios.get('/api/testpoints.php', {
  params: {
    operation: 'select'
  }
}).then((rep) => {
  let serverLists = rep.data.data;
  if (serverLists == undefined || serverLists.length == 0) {
    // default test point
    serverLists = [{
      'id': null,
      'name': window.defaultServerInfo.name,
      'server': window.location.protocol + '//' + window.location.hostname,
      'port': window.location.port,
      'dlURL': '/backend/garbage.php',
      'ulURL': '/backend/empty.php',
      'pingURL': '/backend/empty.php',
      'getIpURL': '/backend/getIP.php'
    }];
  }
  s.addTestPoints(serverLists);
  // default no auto server select
  s.setSelectedServer(serverLists[0]);
  // update ui
  let serverListSelector = document.querySelector('#serverList');
  for (let i = 0; i < serverLists.length; i++) {
    let option = document.createElement('option');
    option.innerHTML = serverLists[i].name;
    option.value = i;
    serverListSelector.appendChild(option);
  }
  pointReady = true;
  I("startStopBtn").className = "";
  I("startStopBtn").title= "";
  serverListSelector.addEventListener('change', function() {
    let index = this.selectedOptions[0].value;
    index = parseInt(index);
    s.setSelectedServer(s._serverList[index]);
  });
}).catch((error) => {
  console.error(error);
  alert('测速节点列表加载失败!');
});
