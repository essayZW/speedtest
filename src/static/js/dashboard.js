// 对Date的扩展，将 Date 转化为指定格式的String
// 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
// 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
// 例子：
// (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
// (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
Date.prototype.Format = function (fmt) { //author: meizz
  var o = {
    "M+": this.getMonth() + 1, //月份
    "d+": this.getDate(), //日
    "h+": this.getHours(), //小时
    "m+": this.getMinutes(), //分
    "s+": this.getSeconds(), //秒
    "q+": Math.floor((this.getMonth() + 3) / 3), //季度
    "S": this.getMilliseconds() //毫秒
  };
  if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
  for (var k in o)
    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
  return fmt;
}
window.router = new Router();
window.charts = [];
window.editableTableData = {};
Chart.register({
  id: 'beforeDraw',
  beforeDraw: function (chart) {
    var ctx = chart.ctx;
    ctx.save();
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, chart.width, chart.height);
    ctx.restore();
  }
});
let config = {
  routes: [
    {
      path: '/index',
      name: 'index',
      pageHeader: {
        title: "概览",
        info: "系统数据概览",
        pageTitle: "数据概览"
      },
      callback: function () {
        let endTime = new Date().Format("yyyy-MM-dd");
        let startTime = new Date().getTime();
        startTime -= (1000 * 3600 * 24);
        startTime = new Date(startTime).Format("yyyy-MM-dd");
        axios.get("/api/speedAVGLog.php", {
          params: {
            'start_time': startTime,
            'end_time': endTime
          }
        }).then((rep) => {
          let repData = rep.data;
          $('#yesterdayUserNum').html(repData.userNum);
          $('#yesterdayTestNum').html(repData.testNum);
          $('#yesterdayAVGDl').html(repData.dl.toFixed(2) + ' Mbps');
          $('#yesterdayAVGUl').html(repData.ul.toFixed(2) + ' Mbps');
          $('#yesterdayAVGPing').html(repData.ping.toFixed(2) + ' ms');
          $('#yesterdayAVGJitter').html(repData.jitter.toFixed(2) + ' ms');
        }).catch((error) => {
          console.error(error);
          alertModal("数据加载失败", "首页数据加载失败");
        });
        endTime = new Date().getTime();
        endTime += (1000 * 3600 * 24);
        endTime = new Date(endTime).Format('yyyy-MM-dd');
        startTime = new Date().getTime();
        startTime -= (1000 * 3600 * 24 * 30);
        startTime = new Date(startTime).Format("yyyy-MM-dd");
        axios.get("/api/speedRangeLog.php", {
          params: {
            'start_time': startTime,
            'end_time': endTime
          }
        }).then((rep) => {
          let repData = rep.data;
          let chartWeek = {
            labels: [],
            datasets: [
              {
                label: '测速人数',
                data: [],
                borderColor: 'red',
              },
              {
                label: '测速次数',
                data: [],
                borderColor: '#0ae',
              }
            ]
          };
          let chartMonth = JSON.parse(JSON.stringify(chartWeek));
          for (let i = 0; i < 7; i++) {
            let index = repData.data.length - 7 + i;
            chartWeek.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
            chartWeek.datasets[0].data.push(repData.data[index].userNum);
            chartWeek.datasets[1].data.push(repData.data[index].testNum);
          }
          let canvas = document.querySelector("#userChartWeek").getContext('2d');
          window.charts.push(new Chart(canvas, {
            type: 'line',
            data: chartWeek
          }));
          for (let i = 0; i < repData.data.length; i++) {
            let index = i;
            chartMonth.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
            chartMonth.datasets[0].data.push(repData.data[index].userNum);
            chartMonth.datasets[1].data.push(repData.data[index].testNum);
          }
          canvas = document.querySelector("#userChartMonth").getContext('2d');
          window.charts.push(new Chart(canvas, {
            type: 'line',
            data: chartMonth
          }));

          let duchartWeek = {
            labels: [],
            datasets: [
              {
                label: '上传速度 Mbps',
                borderColor: 'red',
                data: []
              },
              {
                label: '下载速度 Mbps',
                borderColor: '#0ae',
                data: []
              }
            ]
          };
          let duchartMonth = JSON.parse(JSON.stringify(duchartWeek));
          for (let i = 0; i < 7; i++) {
            let index = repData.data.length - 7 + i;
            duchartWeek.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
            duchartWeek.datasets[0].data.push(repData.data[index].avg.ul);
            duchartWeek.datasets[1].data.push(repData.data[index].avg.dl);
          }
          canvas = document.querySelector("#duChartWeek").getContext('2d');
          window.charts.push(new Chart(canvas, {
            type: 'line',
            data: duchartWeek
          }));
          for (let i = 0; i < repData.data.length; i++) {
            let index = i;
            duchartMonth.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
            duchartMonth.datasets[0].data.push(repData.data[index].avg.ul);
            duchartMonth.datasets[1].data.push(repData.data[index].avg.dl);
          }
          canvas = document.querySelector("#duChartMonth").getContext('2d');
          window.charts.push(new Chart(canvas, {
            type: 'line',
            data: duchartMonth
          }));

          let pjchartWeek = {
            labels: [],
            datasets: [
              {
                label: 'ping ms',
                borderColor: 'red',
                data: []
              },
              {
                label: 'jitter ms',
                borderColor: '#0ae',
                data: []
              }
            ]
          }
          let pjchartMonth = JSON.parse(JSON.stringify(pjchartWeek));
          for (let i = 0; i < 7; i++) {
            let index = repData.data.length - 7 + i;
            pjchartWeek.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
            pjchartWeek.datasets[0].data.push(repData.data[index].avg.ping);
            pjchartWeek.datasets[1].data.push(repData.data[index].avg.jitter);
          }
          canvas = document.querySelector("#pjChartWeek").getContext('2d');
          window.charts.push(new Chart(canvas, {
            type: 'line',
            data: pjchartWeek
          }));
          for (let i = 0; i < repData.data.length; i++) {
            let index = i;
            pjchartMonth.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
            pjchartMonth.datasets[0].data.push(repData.data[index].avg.ping);
            pjchartMonth.datasets[1].data.push(repData.data[index].avg.jitter);
          }
          canvas = document.querySelector("#pjChartMonth").getContext('2d');
          window.charts.push(new Chart(canvas, {
            type: 'line',
            data: pjchartMonth
          }));

        }).catch((error) => {
          console.error(error);
          alertModal("数据加载失败", "使用情况概况数据加载失败");
        });
      }
    },
    {
      path: '/history',
      name: 'history',
      pageHeader: {
        title: '测速历史数据',
        info: '系统中的所有测速记录查看页面',
        pageTitle: '历史数据'
      },
      callback: (routes) => {
        let page = 1;
        if (routes.query.page != undefined) {
          page = routes.query.page;
        }
        $('#historyDataTable').bootstrapTable({
          url: '/api/speedLog.php',
          sidePagination: "true",
          uniqueId: "id",
          pageSize: 25,
          pageNumber: page,
          pageList: [10, 25, 50, 100],
          showRefresh: true,
          pagination: true,
          buttonsToolbar: '#tableToolArea',
          sidePagination: "server",
          columns: [
            {
              field: 'id',
              title: 'id'
            },
            {
              field: 'unumber',
              title: '学号'
            },
            {
              field: 'name',
              title: '姓名'
            },
            {
              field: 'ip',
              title: 'IP'
            },
            {
              field: 'dl',
              title: '下载速度Mbps'
            },
            {
              field: 'ul',
              title: '上传速度Mbps'
            },
            {
              field: 'ping',
              title: '延迟 ms'
            },
            {
              field: 'jitter',
              title: '抖动 ms'
            },
            {
              field: 'time',
              title: '测速时间'
            }
          ],
          queryParams: (params) => {
            let $allRadios = $('input[type=radio][name=searchMethod]');
            let value = 'unumber';
            for (let i = 0; i < $allRadios.length; i++) {
              if ($allRadios[i].checked) {
                value = $allRadios[i].value;
              }
            }
            let searchData = $("#searchInput").val();
            let newParams = {
              all: '',
              start: params.offset,
              length: params.limit,
              search_data: searchData,
              search_field: value
            };
            if (searchData.length) {
              newParams['search'] = '';
            }
            return newParams;
          }
        });
      }
    },
    {
      path: '/chart/line',
      name: 'lineChart',
      pageHeader: {
        info: '一段时间内测速人数、每人测速次数、测速上传下载速度、测速ping和测速jitter变化折线图',
        title: '测速变化趋势',
        pageTitle: '测速变化趋势'
      },
      callback: () => {
        let butt = document.querySelector("#useinfoChartButt");
        butt.click();
      }
    },
    {
      path: '/chart/pie',
      name: 'pieChart',
      pageHeader: {
        info: '下载速度、上传速度、ping和jitter各部分区间占比详情',
        title: '占比详情',
        pageTitle: '占比详情'
      },
      callback: () => {
        let butt = document.querySelector('#pieinfoChartButt');
        butt.click();
      }
    },
    {
      path: '/settings/cidr',
      name: 'cidrSettings',
      pageHeader: {
        info: '通过CIDR绑定IP网段与接入方式、接入地点、ISP等信息的对应关系,不再此列表中的IP将会送往公网API查询相关信息',
        title: 'CIDR列表设置',
        pageTitle: 'CIDR列表设置'
      },
      callback: () => {
        const TableId = 'cidrTable';
        $(`#${TableId}`).bootstrapTable({
          url: '/api/cidr.php?operation=select',
          sidePagination: true,
          pageSize: 10,
          pageList: [10, 25, 50, 100],
          showRefresh: true,
          pagination: true,
          buttonsToolbar: '#cidrTableToolArea',
          uniqueId: 'id',
          columns: [
            {
              field: 'cidr',
              title: 'CIDR',
              editable: {
                type: 'text',
                title: '新的"CIDR"值',
                validate: (v) => {
                  if (!v.length) return 'CIDR不能为空';
                }
              }
            },
            {
              field: 'position',
              title: '接入地点',
              editable: {
                type: 'text',
                title: '新的"接入地点"值',
              }
            },
            {
              field: 'accessMethod',
              title: '接入方式',
              editable: {
                type: 'text',
                title: '新的"接入方法"值'
              }
            },
            {
              field: 'isp',
              title: '互联网服务供应商',
              editable: {
                type: 'text',
                title: '新的"互联网服务供应商"值'
              }
            },
            {
              field: 'ispinfo',
              title: '互联网服务供应商信息',
              editable: {
                type: 'text',
                title: '新的"互联网服务供应商信息"值'
              }
            },
            {
              title: '操作',
              formatter: (value, row, index) => {
                return `
                <button class="btn btn-success table-save" data-api="/api/cidr.php" data-tableid="${TableId}" data-index="${index}">保存</button>
                <button class="btn btn-danger table-delete" data-api="/api/cidr.php" data-tableid="${TableId}" data-index="${index}">删除</button>
                <button class="btn btn-primary table-restore" data-api="/api/cidr.php" data-tableid="${TableId}" data-index="${index}">恢复</button>
                `;
              }
            }
          ],
          responseHandler: (res) => {
            // 将null转化为字符串 Unknown
            for (let i = 0; i < res.length; i ++) {
              for (let key in res[i]) {
                if (res[i][key] == null || res[i][key] == '') {
                  res[i][key] = 'Unknown';
                }
              }
            }
            window.editableTableData[TableId] = JSON.parse(JSON.stringify(res));
            return res;
          }
        });
      }
    },
    {
      path: '/settings/testpoints',
      name: 'testPointsSettings',
      pageHeader: {
        info: '设置系统支持的测速节点及具体节点信息设置',
        title: '测速节点设置',
        pageTitle: '测速节点设置'
      },
      callback: () => {

      }
    },
    {
      path: '/chart/pareto',
      name: 'paretoChart',
      pageHeader: {
        info : '测速人数、每人测速次数、测速下载速度、上传速度、ping和jitter按区间分隔帕累托图',
        title: '测速数据帕累托图',
        pageTitle: '测速数据帕累托图'
      },
      callback: () => {
        let butt = document.querySelector('#paretoinfoChartButt');
        butt.click();
      }
    }
  ]
}
router.init(config);
router.afterEach(function (transition) {
  let index = config.routes.findIndex((item) => {
    return item.path == transition.path;
  });
  document.querySelector("#pageTitle").innerHTML = config.routes[index].pageHeader.title;
  document.querySelector("#pageInfo").innerHTML = config.routes[index].pageHeader.info;
  document.title = config.routes[index].pageHeader.pageTitle;
  let pages = document.querySelectorAll('.sidebar-menu a');
  for (let i = 0; i < pages.length; i++) {
    let href = pages[i].getAttribute('href').substr(1);
    if (href == transition.path) {
      pages[i].parentElement.classList.add('active');
      continue;
    }
    pages[i].parentElement.classList.remove('active');
  }
});
router.beforeEach(function (transition) {
  clearCharts();
  transition.next();
});
function clearCharts() {
  for (let i = 0; i < window.charts.length; i++) {
    window.charts[i].destroy();
  }
  window.charts = [];
}

$('.date-range-picker').daterangepicker({
  timePicker: true,
  timePickerIncrement: 60,
  locale: {
    format: 'YYYY-MM-DD HH:00'
  },
  timePicker24Hour: true,
  ranges: {
    '今天': [
      moment().startOf('day'),
      moment().endOf('day')
    ],
    '最近7天': [
      moment().startOf('day').subtract(6, 'days'),
      moment().endOf('day')
    ],
    '最近31天': [
      moment().startOf('day').subtract(31, 'days'),
      moment().endOf('day')
    ],
    '本周': [
      moment().startOf('week'),
      moment().endOf('week')
    ],
    '本月': [
      moment().startOf('month'),
      moment().endOf('month')
    ]
  },
  showCustomRangeLabel: false,
  alwaysShowCalendars: true
});
// 因为不止一个时间范围选择器，所以遍历挨个进行初始化
$allDateRangePicker = $('.date-range-picker');
for (let i = 0; i < $allDateRangePicker.length; i++) {
  $now = $($allDateRangePicker[i]);
  $now.data('daterangepicker').setStartDate(moment().startOf('day').subtract(6, 'days'));
  $now.data('daterangepicker').setEndDate(moment().endOf('day'))
}


// 测速数据折线图页面，搜索时重新拉取数据
let butt = document.querySelector('#searchButt');
butt.addEventListener('click', (e) => {
  $('#tableToolArea button[name=refresh]').click();
  e.preventDefault();
});

butt = document.querySelector("#useinfoChartButt");
butt.addEventListener('click', (e) => {
  let start = $('#lineChartDatePicker').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:00');
  let end = $('#lineChartDatePicker').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:00');
  let step = document.querySelector('#lineChartStepSelector').selectedOptions[0].value;
  let dayHourFlag = false;
  if (step == 'dayHour') {
    step = 'single';
    dayHourFlag = true;
  }
  let reqParam = {
    start_time: start,
    end_time: end,
    step: step
  }
  if (dayHourFlag) {
    reqParam.withdata = true;
  }
  axios.get('/api/speedRangeLog.php', {
    params: reqParam
  }).then((rep) => {
    let repDataJSON = JSON.stringify(rep.data);
    clearCharts();
    let repData = JSON.parse(repDataJSON);
    initLineChart(repData, ['userNum', 'testNum'], [
      {
        label: '测速人数',
        data: [],
        borderColor: 'red',
      },
      {
        label: '测速次数',
        data: [],
        borderColor: '#0ae',
      }
    ], 'use', dayHourFlag, step);
    repData = JSON.parse(repDataJSON);
    initLineChart(repData, ['dl', 'ul'], [
      {
        label: '平均下载速度 Mbps',
        data: [],
        borderColor: 'red',
      },
      {
        label: '平均上传速度 Mbps',
        data: [],
        borderColor: '#0ae',
      }
    ], 'uldl', dayHourFlag, step);
    repData = JSON.parse(repDataJSON);
    initLineChart(repData, ['ping', 'jitter'], [
      {
        label: 'ping ms',
        data: [],
        borderColor: 'red',
      },
      {
        label: 'jitter ms',
        data: [],
        borderColor: '#0ae',
      }
    ], 'pj', dayHourFlag, step);
  }).catch((error) => {
    console.error(error);
    alertModal('加载失败', '统计图数据加载失败');
  });
  e.preventDefault();
});

let allSizePlusButtons = document.querySelectorAll('.col-size-plus');
for (let i = 0; i < allSizePlusButtons.length; i ++) {
  allSizePlusButtons[i].addEventListener('click', function() {
    let target = this.dataset.selector;
    target = document.querySelector(target);
    if (!target) return;
    colResize(target, target.dataset.size, 2, 12, 6)
  });
}

let allSizeMinusButton = document.querySelectorAll('.col-size-minus');
for (let i = 0; i < allSizeMinusButton.length; i ++) {
  allSizeMinusButton[i].addEventListener('click', function() {
    let target = this.dataset.selector;
    target = document.querySelector(target);
    if (!target) return;
    colResize(target, target.dataset.size, -2, 12, 6)
  });
}

let allExportCanvasButtons = document.querySelectorAll('.export-canvas');
for (let i = 0; i < allExportCanvasButtons.length; i ++) {
  allExportCanvasButtons[i].addEventListener('click', function(e) {
    let targetSelector = this.dataset.target;
    let target = document.querySelector(targetSelector);
    if (!target) return;
    let fileName = target.id;
    exportCanvasAsPng(target, fileName + '_' + moment().format('YYYYMMDDHHmmss') + '.png');
    e.stopPropagation();
  });
}
// 单个按钮导出多个图片
// 多个图片的canvas选择器以|隔开
allExportCanvasButtons = document.querySelectorAll('.export-multiple-canvas');
for (let i = 0; i < allExportCanvasButtons.length; i ++) {
  allExportCanvasButtons[i].addEventListener('click', function(e) {
    let targetSelectors = this.dataset.target;
    targetSelectors = targetSelectors.split('|');
    for (let j = 0; j < targetSelectors.length; j ++) {
      let target = document.querySelector(targetSelectors[j]);
      if (!target) return;
      let fileName = target.id;
      exportCanvasAsPng(target, fileName + '_' + moment().format('YYYYMMDDHHmmss') + '.png');
    }
    e.stopPropagation();
  });
}

// 饼图页面的按钮事件注册
// 重新划分区间时重新拉取数据绘制饼图
let divisionButts = document.querySelectorAll('.pie-division-butt');
for (let i = 0; i < divisionButts.length; i++) {
  divisionButts[i].addEventListener('click', () => {
    document.querySelector("#pieinfoChartButt").click();
  });
}
butt = document.querySelector("#pieinfoChartButt");
butt.addEventListener('click', (e) => {
  clearCharts();
  let elementIDPrefix = 'pie';
  let start = $(`#${elementIDPrefix}infoDatePicker`).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:00');
  let end = $(`#${elementIDPrefix}infoDatePicker`).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:00');
  axios.get('/api/speedRangeLog.php', {
    params: {
      start_time: start,
      end_time: end,
      step: 'single',
      withdata: true
    }
  }).then((rep) => {
    let repData = rep.data;
    // 用来统计每个用户测速次数
    let userTestNumsTable = {};
    // 用来存储各项测试数据
    let pingDatas = [],
      jitterDatas = [],
      dlDatas = [],
      ulDatas = [],
      userDatas = [];
    for (let index = 0; index < repData.data.length; index++) {
      let singleData = repData.data[index].data[0];
      if (userTestNumsTable[singleData.unumber] == undefined) {
        userTestNumsTable[singleData.unumber] = 0;
      }
      userTestNumsTable[singleData.unumber]++;
      pingDatas.push(singleData.ping);
      jitterDatas.push(singleData.jitter);
      dlDatas.push(singleData.dl);
      ulDatas.push(singleData.ul);
    }
    for (let key in userTestNumsTable) {
      userDatas.push(userTestNumsTable[key]);
    }

    let userTestDivision = document.querySelector('#userTestDivisionInput').value;
    userTestDivision = splitIntArray(userTestDivision, ',');
    initPieChart(userTestDivision, userDatas, 'userTest');

    let dlulDivision = document.querySelector('#dlulDivisionInput').value;
    dlulDivision = splitIntArray(dlulDivision, ',');
    initPieChart(dlulDivision, dlDatas, 'dl');
    initPieChart(dlulDivision, ulDatas, 'ul');

    let pjDivision = document.querySelector('#pjDivisionInput').value;
    pjDivision = splitIntArray(pjDivision, ',');
    initPieChart(pjDivision, pingDatas, 'ping');
    initPieChart(pjDivision, jitterDatas, 'jitter');
  }).catch((error) => {
    console.error(error);
    alertModal('加载失败', '统计图数据加载失败');
  });
  e.preventDefault();
});


// 由于表格编辑按钮后创建
// 因此需要通过事件委托添加点击事件
// 委托事件到.table-event-handler元素上
eventDelegation('.table-event-handler', 'button.table-save', 'click', function(e, _this) {
  let targetId = _this.dataset.tableid;
  let tableIndex = _this.dataset.index;
  let currentTableData = $(`#${targetId}`).bootstrapTable('getData', false);
  let updatedData = currentTableData[tableIndex];
  // 在没有修改的时候应该拒绝发送修改的API请求
  let changeFlag = false;
  for (let key in updatedData) {
    if (updatedData[key] != window.editableTableData[targetId][tableIndex][key]) {
      changeFlag = true;
      break;
    }
  }
  if (!changeFlag) {
    alertModal('修改失败', '没有任何改动');
    return;
  }
  let urlParams = new URLSearchParams();
  for (let key in updatedData) {
    urlParams.append(key, updatedData[key]);
  }
  axios.post(_this.dataset.api, urlParams, {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    params: {
      operation: 'update'
    }
  }).then((rep) => {
    let repData = rep.data;
    if (repData.status) {
      alertModal('修改成功', '数据修改成功');
      // 只备份当前保存的这一行数据
      // 因为若先修改多行，再点保存，其他行的修改未提交，不算已经修改
      window.editableTableData[targetId][tableIndex] = JSON.parse(JSON.stringify(currentTableData[tableIndex]));
    }
    else {
      alertModal('修改失败', typeof repData.info == 'object' ? repData.info.message : repData.info);
    }
  }).catch((error) => {
    console.error(error);
    alertModal('修改失败', '发生未知错误');
  });
});

eventDelegation('.table-event-handler', 'button.table-delete', 'click', (e, _this) => {
  let targetId = _this.dataset.tableid;
  let tableIndex = _this.dataset.index;
  let currentTableData = $(`#${targetId}`).bootstrapTable('getData', false);
  let deleteId = currentTableData[tableIndex].id;
  let queryParams = new URLSearchParams();
  queryParams.append('id', deleteId);
  axios.post(_this.dataset.api, queryParams, {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    params: {
      operation: 'delete'
    }
  }).then((rep) => {
    let repData = rep.data;
    if (repData.status) {
      alertModal('删除成功', '删除成功');
      $(`#${targetId}`).bootstrapTable('refresh');
    }
    else {
      alertModal('修改失败', typeof repData.info == 'object' ? repData.info.message : repData.info);
    }
  }).catch((error) => {
    console.error(error);
    alertModal('修改失败', '发生未知错误');
  });
});

eventDelegation('.table-event-handler', 'button.table-restore', 'click', (e, _this) => {
  let targetId = _this.dataset.tableid;
  let tableIndex = _this.dataset.index;
  let currentTableData = $(`#${targetId}`).bootstrapTable('getData', false);
  let cirdId = currentTableData[tableIndex].id;
  $(`#${targetId}`).bootstrapTable('removeByUniqueId', cirdId);
  $(`#${targetId}`).bootstrapTable('insertRow', {
    index: tableIndex,
    row: JSON.parse(JSON.stringify(window.editableTableData[targetId][tableIndex]))
  });
});

let showCidrModalButton = document.querySelector('#showCidrInfoModal');
showCidrModalButton.addEventListener('click', () => {
  let allCidrInfoInputs = document.querySelectorAll('#cidrModal input');
  for (let i = 0; i < allCidrInfoInputs.length; i ++) {
    allCidrInfoInputs[i].value = '';
  }
  $('#cidrModal').modal('show');
});

let cidrAddbutton = document.querySelector('#addCidrInfo');
cidrAddbutton.addEventListener('click', () => {
  let allCidrInfoInputs = document.querySelectorAll('#cidrModal input');
  let urlParams = new URLSearchParams();
  for (let i = 0; i < allCidrInfoInputs.length; i ++) {
    urlParams.append(allCidrInfoInputs[i].getAttribute('name'), allCidrInfoInputs[i].value);
    if (allCidrInfoInputs[i].getAttribute('name') == 'cidr' && allCidrInfoInputs[i].value.length == 0) {
      return;
    }
  }
  axios.post('/api/cidr.php', urlParams, {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    params: {
      operation: 'insert'
    }
  }).then((rep) => {
    let repData = rep.data;
    if (repData.status) {
      $('#cidrTable').bootstrapTable('refresh');
    }
    else {
      alertModal('修改失败', typeof repData.info == 'object' ? repData.info.message : repData.info);
    }
  }).catch((error) => {
    console.log(error);
    alertModal('添加失败', '添加失败请稍候重试!');
  }).finally(() => {
    $('#cidrModal').modal('hide');
  });
});

let allPretoDivisionButt = document.querySelectorAll('.pareto-division-butt');
for (let i = 0; i < allPretoDivisionButt.length; i ++) {
  allPretoDivisionButt[i].addEventListener('click', () => {
    document.querySelector('#paretoinfoChartButt').click();
  });
}


butt = document.querySelector('#paretoinfoChartButt');
butt.addEventListener('click', (e) => {
  clearCharts();
  let elementIDPrefix = 'pareto';
  let start = $(`#${elementIDPrefix}infoDatePicker`).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:00');
  let end = $(`#${elementIDPrefix}infoDatePicker`).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:00');
  axios.get('/api/speedRangeLog.php', {
    params: {
      start_time: start,
      end_time: end,
      step: 'single',
      withdata: true
    }
  }).then((rep) => {
    let repData = rep.data;
    // 用来统计每个用户测速次数
    let userTestNumsTable = {};
    // 用来存储各项测试数据
    let pingDatas = [],
      jitterDatas = [],
      dlDatas = [],
      ulDatas = [],
      userDatas = [];
    for (let index = 0; index < repData.data.length; index++) {
      let singleData = repData.data[index].data[0];
      if (userTestNumsTable[singleData.unumber] == undefined) {
        userTestNumsTable[singleData.unumber] = 0;
      }
      userTestNumsTable[singleData.unumber]++;
      pingDatas.push(singleData.ping);
      jitterDatas.push(singleData.jitter);
      dlDatas.push(singleData.dl);
      ulDatas.push(singleData.ul);
    }
    for (let key in userTestNumsTable) {
      userDatas.push(userTestNumsTable[key]);
    }

    let userTestDivision = document.querySelector('#paretoUserNumDivisionInput').value;
    userTestDivision = splitIntArray(userTestDivision, ',');
    initParetoChart(userTestDivision, userDatas, 'useNums', '每人测速次数');

    let dlulDivision = document.querySelector('#paretoDlulDivisionInput').value;
    dlulDivision = splitIntArray(dlulDivision, ',');
    initParetoChart(dlulDivision, dlDatas, 'dl', '下载速度');
    initParetoChart(dlulDivision, ulDatas, 'ul', '上传速度');

    let pjDivision = document.querySelector('#paretoPjDivisionInput').value;
    pjDivision = splitIntArray(pjDivision, ',');
    initParetoChart(pjDivision, pingDatas, 'ping', 'ping');
    initParetoChart(pjDivision, jitterDatas, 'jitter', 'jitter');
  }).catch((error) => {
    console.error(error);
    alertModal('加载失败', '统计图数据加载失败');
  });
  e.preventDefault();
});
/**
 * 注册事件委托
 * @param string eventHandlerSelector 事件的接收者
 * @param function targetElementSelector 事件的委托者
 * @param string eventType 需要委托的事件名
 * @param function eventHandlerMethod 事件的处理函数
 */
function eventDelegation(eventHandlerSelector, targetElementSelector, eventType, eventHandlerMethod) {
  let eventHandlers = document.querySelectorAll(eventHandlerSelector);
  for (let i = 0; i < eventHandlers.length; i ++) {
    let eventHandler = eventHandlers[i];
    eventHandler.addEventListener(eventType, (e) => {
      if (e.target.matches(targetElementSelector)) {
        eventHandlerMethod ? eventHandlerMethod(e, e.target) : null;
      }
    });
  }
}
/**
 * 根据数据初始化一个饼图
 * @param array division 区间划分数组
 * @param array  datas 数据数组
 * @param string elementIDPrefix canvas ID前缀
 */
function initPieChart(division, datas, elementIDPrefix) {
  let chartData = getDivisiedChartData(division, datas);
  chartData = translatePieChartData(chartData);
  let canvas = document.querySelector(`#${elementIDPrefix}PieChart`).getContext('2d');
  window.charts.push(new Chart(canvas, {
    type: 'pie',
    data: chartData
  }));
}

/**
 * 根据数据初始化一个帕累托图
 * @param array division 区间划分数组
 * @param array  datas 数据数组
 * @param string elementIDPrefix canvas ID前缀
 * @param string barName bar图的label
 */
function initParetoChart(division, datas, elementIDPrefix, barName) {
  let chartData = getDivisiedChartData(division, datas);
  chartData = translateParetoChartData(chartData);
  chartData.datasets[1].label = barName;
  let canvas = document.querySelector(`#${elementIDPrefix}ParetoChart`).getContext('2d');
  // 顶部padding防止超出图片的图例不显示
  let options = {
    layout: {
      padding: {
        top: 10
      }
    },
    scales: {
      value: {
          type: 'linear',
          position: 'left'
      },
      sum: {
        type: 'linear',
        position: 'right',
        gridLines: {
          color: '#aaa'
        },
        ticks: {
          callback: (value) => {
            return value * 100 + '%';
          }
        }
      }
    },
    animation: {
      onComplete: function(data) {
        let ctx = data.chart.ctx;
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";

        data.chart.config.data.datasets.forEach((dataset) => {
          switch (dataset.type) {
            case 'line':
              ctx.fillStyle = "red";
              dataset.data.forEach(function (value, index) {
                ctx.fillText((value * 100).toFixed(2) + '%', data.chart._metasets[0].data[index].x, data.chart._metasets[0].data[index].y - 20);
              });
              break;
            case 'bar':
              ctx.fillStyle = "#0ae";
              dataset.data.forEach(function (value, index) {
                // 避免与折线图的数字重合，向右偏移30px
                ctx.fillText(value, data.chart._metasets[1].data[index].x - 30, data.chart._metasets[1].data[index].y - 20);
              });
              break;
          }
        });
      },
    },
    plugins: {
      legend: {
        position: 'right'
      }
    },
    layout: {
      padding: {
        top: 30
      }
    }
  };
  window.charts.push(new Chart(canvas, {
    type: 'bar',
    data: chartData,
    options: options
  }));
}

/**
 * 从字符串中以某个字符分隔成数组，并转化为int
 * @param string str 需要分割的字符串
 * @param string splitFlag 分隔标志
 */
function splitIntArray(str, splitFlag) {
  str = str.split(splitFlag);
  let res = str.map((element) => {
    return parseInt(element);
  });
  return res;
}

/**
 * 将getDivisiedChartData生成的数据转化为Chart.js pie图需要的数据类型
 * @param array data 需要转化的数据
 */
function translatePieChartData(data) {
  let resDataTemplate = {
    labels: [],
    datasets: [
      {
        data: [],
        backgroundColor: palette('tol', data.length).map(function (hex) {
          return '#' + hex;
        })
      }
    ]
  }
  data.forEach((element) => {
    resDataTemplate.labels.push(element.label);
    resDataTemplate.datasets[0].data.push(element.value);
    resDataTemplate.datasets[0].backgroundColor.push(element.color);
  });
  return resDataTemplate;
}

/**
 * 将getDivisiedChartData生成的数据转化为chart.js pareto图需要的数据类型
 * @param array data 需要转化的数据
 */
function translateParetoChartData(data) {
  let resDataTemplate = {
    labels: [],
    datasets: [
      {
        data: [],
        borderColor: 'red',
        type: 'line',
        label: '累计频率',
        yAxisID: 'sum',
        clip: { 
          top: false,
          bottom: false,
          left: 5,
          right: 5
        }
      },
      {
        data: [],
        backgroundColor: '#0ae',
        type: 'bar',
        label: '',
        yAxisID: 'value',
      }
    ]
  };
  let nowPercentage = 0;
  data.forEach((element) => {
    resDataTemplate.labels.push(element.label);
    nowPercentage += element.percentage;
    resDataTemplate.datasets[0].data.push(nowPercentage);
    resDataTemplate.datasets[1].data.push(element.value);
  });
  return resDataTemplate;
}
/**
 * 生成用于chartjs 饼状图使用的数据
 * @param array division 划分的区间
 * @param array dataArray 所有的数据
 */
function getDivisiedChartData(division, dataArray) {
  if (division.length == 0) return [dataArray.length];
  division.sort((a, b) => a - b);
  dataArray.sort((a, b) => a - b);
  let res = new Array(division.length + 1);
  for (let i = 0; i < res.length; i++) {
    res[i] = {
      value: 0,
      label: '',
      percentage: 0
    };
    if (i == 0) {
      res[i].label = `(-INF, ${division[0]}]`;
    }
    else if (i == res.length - 1) {
      res[i].label = `(${division[division.length - 1]}, +INF)`;
    }
    else {
      res[i].label = `(${division[i - 1]}, ${division[i]}]`;
    }
  }
  // division 区间数组，左开右闭
  let resIndex = 0;
  let dataIndex = 0;
  while (dataIndex < dataArray.length && division[0] >= dataArray[dataIndex]) {
    res[resIndex].value++;
    dataIndex++;
  }
  resIndex++;
  for (let i = 1; i < division.length && dataIndex < dataArray.length; i++) {
    // 区间 (division[i - 1], division[i]]
    while (dataArray[dataIndex] > division[i - 1] && dataArray[dataIndex] <= division[i]) {
      res[resIndex].value++;
      dataIndex++;
    }
    resIndex++;
  }
  while (dataIndex < dataArray.length && division[division.length - 1] < dataArray[dataIndex]) {
    res[resIndex].value++;
    dataIndex++;
  }
  for (let i = 0; i < res.length; i++) {
    res[i].percentage = res[i].value / dataArray.length;
  }
  return res;
}

/**
 * 根据一段时间内的测速数据绘制指定字段的变化情况
 * @param object repData 需要展示的数据
 * @param array apiDataName 需要展示的数据在API返回数据中的字段名
 * @param object chartDatasetsTemplate 渲染给chartjs折线图的数据模板
 * @param string elementIDPrefix 展示的相关UI的ID的前缀
 * @param boolean dayHourFlag 是否将每天的同一小时进行合并
 * @param string step 数据划分的步长
 */
function initLineChart(repData, apiDataName, chartDatasetsTemplate,
                      elementIDPrefix, dayHourFlag, step)
{
    document.querySelector(`#lineChartUserNum`).innerHTML = repData.userNum;
    document.querySelector(`#lineChartTestNum`).innerHTML = repData.testNum;
    if (dayHourFlag) {
      let hourData = new Array(24);
      let fillDataJSON = JSON.stringify({
        userNum: 0,
        testNum: 0,
        sum: {
          ping: 0,
          jitter: 0,
          dl: 0,
          ul: 0
        },
        avg: {
          ping: 0,
          jitter: 0,
          dl: 0,
          ul: 0
        },
        startTime: '',
      });
      let hourDataUser = new Array(24);
      for (let hour = 0; hour < 24; hour++) {
        hourDataUser[hour] = {};
        hourData[hour] = JSON.parse(fillDataJSON);
        hourData[hour].startTime = new Date(2020, 1, 1, hour, 0, 0).getTime();
      }
      for (let i = 0; i < repData.data.length; i++) {
        let hour = new Date(repData.data[i].startTime * 1000).getHours();
        hourData[hour].testNum++;
        hourDataUser[hour][repData.data[i].data[0].unumber] = true;
        hourData[hour].sum.ping += repData.data[i].avg.ping;
        hourData[hour].sum.jitter += repData.data[i].avg.jitter;
        hourData[hour].sum.ul += repData.data[i].avg.ul;
        hourData[hour].sum.dl += repData.data[i].avg.dl;
      }
      for (let i = 0; i < hourData.length; i++) {
        if (hourData[i].testNum == 0) continue;
        hourData[i].userNum = Object.keys(hourDataUser[i]).length;
        hourData[i].avg.ping = hourData[i].sum.ping / hourData[i].testNum;
        hourData[i].avg.jitter = hourData[i].sum.jitter / hourData[i].testNum;
        hourData[i].avg.ul = hourData[i].sum.ul / hourData[i].testNum;
        hourData[i].avg.dl = hourData[i].sum.dl / hourData[i].testNum;
      }
      repData.data = hourData;
    }
    let chartData = {
      labels: [],
      datasets: chartDatasetsTemplate
    };
    for (let index = 0; index < repData.data.length; index++) {
      if (step == 'week') {
        chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd +7'));
      }
      else if (step == 'hour') {
        chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd hh'));
      }
      else if (step == 'single') {
        if (dayHourFlag) {
          chartData.labels.push(new Date(repData.data[index].startTime).Format('hh时'));
        }
        else {
          chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd hh:mm'));
        }
      }
      else {
        chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
      }
      repData.data[index].avg.testNum = repData.data[index].testNum;
      repData.data[index].avg.userNum = repData.data[index].userNum;
      for (let i = 0; i < apiDataName.length; i++) {
        chartData.datasets[i].data.push(repData.data[index].avg[apiDataName[i]]);
      }
    }
    let canvas = document.querySelector(`#${elementIDPrefix}infoChart`).getContext('2d');
    window.charts.push(new Chart(canvas, {
      type: 'line',
      data: chartData
    }));
}

/**
 * 通过修改div col-lg-* col-lg-offset-lg-* 两个class名修改元素大小并时刻保持居中
 * @param object target 目标元素
 * @param integer currentSize 当前的尺寸 0~12
 * @param integer step 尺寸变化步长
 * @param integer maxSize 最大的尺寸
 * @param integer minSize 最小的尺寸
 */
function colResize(target, currentSize, step = 2, maxSize = 12, minSize = 0) {
    currentSize = parseInt(currentSize);
    if (currentSize + step > maxSize) return;
    if (currentSize + step < minSize) return;
    // step 必须是整数
    if (step % 2) step -= 1;
    target.style.transition = 'width 0.7s,height 0.7s,margin 0.7s';
    target.classList.remove('col-lg-' + currentSize);
    target.classList.remove('col-lg-offset-' + ((12 - currentSize) / 2));
    currentSize += step;
    target.dataset.size = currentSize;
    target.classList.add('col-lg-' + currentSize);
    target.classList.add('col-lg-offset-' + ((12 - currentSize) / 2));
}
/**
 * 显示消息到modal中
 * @param string title
 * @param string message
 */
function alertModal(title, message) {
  $('#alertModalLabel').html(title);
  $('#alertModalMessage').html(message);
  $('#alertModal').modal('show');
}

/**
 * 导出canvas为PNG图片
 * @param object canvasElement canvas元素
 * @param string fileName 保存的文件名
 */
function exportCanvasAsPng(canvasElement, fileName) {
  if (!canvasElement) return;
  const fileType = 'image/png';
  let data = canvasElement.toDataURL(fileType);
  let a = document.createElement('a');
  a.href = data;
  a.download = fileName;
  a.style.display = 'none';
  document.body.append(a);
  a.click();
  document.body.removeChild(a);
}