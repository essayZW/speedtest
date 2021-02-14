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
function alertModal(title, message) {
    $('#alertModalLabel').html(title);
    $('#alertModalMessage').html(message);
    $('#alertModal').modal('show');
}
window.router = new Router();
window.charts = []
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
                    params : {
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
                    params : {
                        'start_time': startTime,
                        'end_time': endTime
                    }
                }).then((rep) => {
                    let repData = rep.data;
                    let chartWeek = {
                        labels : [],
                        datasets : [
                            {
                                label : '测速人数',
                                data : [],
                                borderColor : 'red',
                            },
                            {
                                label : '测速次数',
                                data : [],
                                borderColor : '#0ae',
                            }
                        ]    
                    };
                    let chartMonth = JSON.parse(JSON.stringify(chartWeek));
                    for(let i = 0; i < 7; i ++) {
                        let index = repData.data.length - 7 + i;
                        chartWeek.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
                        chartWeek.datasets[0].data.push(repData.data[index].userNum);
                        chartWeek.datasets[1].data.push(repData.data[index].testNum);
                    } 
                    let canvas = document.querySelector("#userChartWeek").getContext('2d');
                    window.charts.push(new Chart(canvas, {
                        type : 'line',
                        data : chartWeek
                    }));
                    for(let i = 0; i < repData.data.length; i ++) {
                        let index = i;
                        chartMonth.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
                        chartMonth.datasets[0].data.push(repData.data[index].userNum);
                        chartMonth.datasets[1].data.push(repData.data[index].testNum);
                    }
                    canvas = document.querySelector("#userChartMonth").getContext('2d');
                    window.charts.push(new Chart(canvas, {
                        type : 'line',
                        data : chartMonth
                    }));

                    let duchartWeek = {
                        labels : [],
                        datasets : [
                            {
                                label : '上传速度 Mbps',
                                borderColor : 'red',
                                data : []
                            },
                            {
                                label : '下载速度 Mbps',
                                borderColor : '#0ae',
                                data : []
                            }
                        ]
                    };
                    let duchartMonth = JSON.parse(JSON.stringify(duchartWeek));
                    for(let i = 0; i < 7; i ++) {
                        let index = repData.data.length - 7 + i;
                        duchartWeek.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
                        duchartWeek.datasets[0].data.push(repData.data[index].avg.ul);
                        duchartWeek.datasets[1].data.push(repData.data[index].avg.dl);
                    }
                    canvas = document.querySelector("#duChartWeek").getContext('2d');
                    window.charts.push(new Chart(canvas, {
                        type : 'line',
                        data : duchartWeek
                    }));
                    for(let i = 0; i < repData.data.length; i ++) {
                        let index = i; 
                        duchartMonth.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
                        duchartMonth.datasets[0].data.push(repData.data[index].avg.ul);
                        duchartMonth.datasets[1].data.push(repData.data[index].avg.dl);
                    }
                    canvas = document.querySelector("#duChartMonth").getContext('2d');
                    window.charts.push(new Chart(canvas, {
                        type : 'line',
                        data : duchartMonth
                    }));

                    let pjchartWeek = {
                        labels : [],
                        datasets : [
                            {
                                label : 'ping ms',
                                borderColor : 'red',
                                data : []
                            },
                            {
                                label : 'jitter ms',
                                borderColor : '#0ae',
                                data : []
                            }
                        ]
                    }
                    let pjchartMonth = JSON.parse(JSON.stringify(pjchartWeek));
                    for(let i = 0; i < 7; i ++) {
                        let index = repData.data.length - 7 + i;
                        pjchartWeek.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
                        pjchartWeek.datasets[0].data.push(repData.data[index].avg.ping);
                        pjchartWeek.datasets[1].data.push(repData.data[index].avg.jitter);
                    }
                    canvas = document.querySelector("#pjChartWeek").getContext('2d');
                    window.charts.push(new Chart(canvas, {
                        type : 'line',
                        data : pjchartWeek
                    }));
                    for(let i = 0; i < repData.data.length; i ++) {
                        let index = i; 
                        pjchartMonth.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
                        pjchartMonth.datasets[0].data.push(repData.data[index].avg.ping);
                        pjchartMonth.datasets[1].data.push(repData.data[index].avg.jitter);
                    }
                    canvas = document.querySelector("#pjChartMonth").getContext('2d');
                    window.charts.push(new Chart(canvas, {
                        type : 'line',
                        data : pjchartMonth
                    }));

                }).catch((error) => {
                    console.error(error);
                    alertModal("数据加载失败", "使用情况概况数据加载失败");
                });
            }
        },
        {
            path : '/history',
            name : 'history',
            pageHeader : {
                title : '测速历史数据',
                info : '系统中的所有测速记录查看页面',
                pageTitle : '历史数据'
            },
            callback : (routes) => {
                let page = 1;
                if(routes.query.page != undefined) {
                    page = routes.query.page;
                }
                $('#historyDataTable').bootstrapTable({
                    url : '/api/speedLog.php',
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
                            field : 'name',
                            title : '姓名'
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
                            field : 'time',
                            title : '测速时间'
                        }
                    ],
                    queryParams : (params) => {
                        let $allRadios = $('input[type=radio][name=searchMethod]');
                        let value = 'unumber';
                        for(let i = 0; i < $allRadios.length; i ++) {
                            if($allRadios[i].checked) {
                                value = $allRadios[i].value;
                            }
                        }
                        let searchData =  $("#searchInput").val();
                        let newParams = {
                            all : '',
                            start : params.offset,
                            offset : params.limit,
                            search_data : searchData,
                            search_field : value
                        };
                        if(searchData.length) {
                            newParams['search'] = '';
                        }
                        return newParams;
                    }
                });
            }
        },
        {
            path : '/chart/useinfo',
            name : 'userAndTestChart',
            pageHeader : {
                info : '使用人数以及测速次数详情统计图表',
                title : '使用情况统计图',
                pageTitle : '使用详情图表'
            },
            callback : () => {
                let butt = document.querySelector("#useinfoChartButt");
                butt.click();
            }
        },
        {
            path : '/chart/uldlinfo',
            name : 'uldlChart',
            pageHeader : {
                info : '下载速度和上传速度测速详情统计表',
                title : '测速速度统计表',
                pageTitle : '测速速度统计表'
            },
            callback : () => {
                let butt = document.querySelector("#uldlinfoChartButt");
                butt.click();
            }
        },
        {
            path : '/chart/pjinfo',
            name : 'pjChart',
            pageHeader : {
                info : 'ping和jitter测速详情统计表',
                title : '测速延迟统计表',
                pageTitle : '测速延迟统计表'
            },
            callback : () => {
                let butt = document.querySelector("#pjinfoChartButt");
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
    for(let i = 0; i < pages.length; i ++) {
        let href = pages[i].getAttribute('href').substr(1);
        if(href == transition.path) {
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
    for(let i = 0; i < window.charts.length; i ++) {
        window.charts[i].destroy();
    }
    window.charts = [];
}

$('.date-range-picker').daterangepicker({
    timePicker : true,
    timePickerIncrement : 60,
    locale : {
        format : 'YYYY-MM-DD HH:00'
    },
    timePicker24Hour : true,
    ranges : {
        '今天' : [
            moment().startOf('day'),
            moment().endOf('day')
        ],
        '最近一周' : [
            moment().subtract(6, 'days'),
            moment().endOf('day')
        ],
        '最近一月' : [
            moment().subtract(31, 'days'),
            moment().endOf('day')
        ],
        '本周' : [
            moment().startOf('week'),
            moment().endOf('week')
        ],
        '本月' : [
            moment().startOf('month'),
            moment().endOf('month')
        ]
    },
    showCustomRangeLabel: false,
    alwaysShowCalendars: true
});
$allDateRangePicker = $('.date-range-picker');
for(let i = 0; i < $allDateRangePicker.length; i ++) {
    $now = $($allDateRangePicker[i]);
    $now.data('daterangepicker').setStartDate(moment().subtract(6, 'days'));
    $now.data('daterangepicker').setEndDate(moment().endOf('day'))
}

let butt = document.querySelector('#searchButt');
butt.addEventListener('click', (e) => {
    $('#tableToolArea button[name=refresh]').click();
    e.preventDefault();
});

butt = document.querySelector("#useinfoChartButt");
butt.addEventListener('click', (e) => {
    let start = $('#useinfoDatePicker').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:00');
    let end = $('#useinfoDatePicker').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:00');
    let step = document.querySelector('#useinfoStepSelector').selectedOptions[0].value;
    axios.get('/api/speedRangeLog.php', {
        params : {
            start_time : start,
            end_time : end,
            step : step
        }
    }).then((rep) => {
        clearCharts();
        let repData = rep.data;
        document.querySelector('#useinfoUserNum').innerHTML = repData.userNum;
        document.querySelector('#useinfoTestNum').innerHTML = repData.testNum;
        let chartData = {
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
        for(let index = 0; index < repData.data.length; index ++) {
            if(step == 'week') {
                chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd +7'));
            }
            else if(step == 'hour') {
                chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd hh'));
            }
            else {
                chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
            }
            chartData.datasets[0].data.push(repData.data[index].userNum);
            chartData.datasets[1].data.push(repData.data[index].testNum);
        }
        let canvas = document.querySelector('#useinfoChart').getContext('2d');
        window.charts.push(new Chart(canvas, {
            type : 'line',
            data : chartData
        }));
    }).catch((error) => {
        console.error(error);
        alertModal('加载失败', '统计图数据加载失败');
    });
    e.preventDefault();
});

butt = document.querySelector("#uldlinfoChartButt");
butt.addEventListener('click', (e) => {
    let start = $('#uldlinfoDatePicker').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:00');
    let end = $('#uldlinfoDatePicker').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:00');
    let step = document.querySelector('#uldlinfoStepSelector').selectedOptions[0].value;
    axios.get('/api/speedRangeLog.php', {
        params : {
            start_time : start,
            end_time : end,
            step : step
        }
    }).then((rep) => {
        clearCharts();
        let repData = rep.data;
        document.querySelector('#uldlinfoUserNum').innerHTML = repData.userNum;
        document.querySelector('#uldlinfoTestNum').innerHTML = repData.testNum;
        let chartData = {
            labels: [],
            datasets: [
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
            ]    
        };
        for(let index = 0; index < repData.data.length; index ++) {
            if(step == 'week') {
                chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd +7'));
            }
            else if(step == 'hour') {
                chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd hh'));
            }
            else {
                chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
            }
            chartData.datasets[0].data.push(repData.data[index].avg.dl);
            chartData.datasets[1].data.push(repData.data[index].avg.ul);
        }
        let canvas = document.querySelector('#uldlinfoChart').getContext('2d');
        window.charts.push(new Chart(canvas, {
            type : 'line',
            data : chartData
        }));
    }).catch((error) => {
        console.error(error);
        alertModal('加载失败', '统计图数据加载失败');
    });
    e.preventDefault();
});

butt = document.querySelector("#pjinfoChartButt");
butt.addEventListener('click', (e) => {
    let start = $('#pjinfoDatePicker').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:00');
    let end = $('#pjinfoDatePicker').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:00');
    let step = document.querySelector('#pjinfoStepSelector').selectedOptions[0].value;
    axios.get('/api/speedRangeLog.php', {
        params : {
            start_time : start,
            end_time : end,
            step : step
        }
    }).then((rep) => {
        clearCharts();
        let repData = rep.data;
        document.querySelector('#pjinfoUserNum').innerHTML = repData.userNum;
        document.querySelector('#pjinfoTestNum').innerHTML = repData.testNum;
        let chartData = {
            labels: [],
            datasets: [
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
            ]    
        };
        for(let index = 0; index < repData.data.length; index ++) {
            if(step == 'week') {
                chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd +7'));
            }
            else if(step == 'hour') {
                chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd hh'));
            }
            else {
                chartData.labels.push(new Date(repData.data[index].startTime * 1000).Format('yyyy-MM-dd'));
            }
            chartData.datasets[0].data.push(repData.data[index].avg.ping);
            chartData.datasets[1].data.push(repData.data[index].avg.jitter);
        }
        let canvas = document.querySelector('#pjinfoChart').getContext('2d');
        window.charts.push(new Chart(canvas, {
            type : 'line',
            data : chartData
        }));
    }).catch((error) => {
        console.error(error);
        alertModal('加载失败', '统计图数据加载失败');
    });
    e.preventDefault();
});