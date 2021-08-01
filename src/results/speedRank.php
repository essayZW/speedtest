<!DOCTYPE html>
<html>
<head>
	<title>各楼宇网速排行</title>
	<meta charset="utf-8">
	<script src="https://cdn.staticfile.org/echarts/4.3.0/echarts.min.js"></script>
	<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
	<style type="text/css">
		#main{
			width: 500px;
			height: 50px;
		}
		#main2{
			width: 500px;
			height: 600px;
            margin: 0px auto;
		}
	</style>
</head>
<body>
<div id="main"></div>
<div id="main2"></div>
</body>
<script type="text/javascript">
	function decodeUnicode(str){
		var res=str.replace(/\\/g,"%");
		return unescape(res);
	}
	var position=[];
	var avgdl=[];
	var avgul=[];
	var num=[];
	function drawRank() {
		axios.get("/api/getMsg.php",{

		}).then((rep)=>{
			let repdata=rep.data;
			// console.log(repdata[0]);
			for (var i = 0; i < repdata.length; i++) {
				position[i]=repdata[i].position;
				avgdl[i]=repdata[i].avgdl;
				avgul[i]=repdata[i].avgul;
				num[i]=repdata[i].num;
			}
			console.log(position);
			var chartDom = document.getElementById('main');
			var myChart = echarts.init(chartDom);
			var option;

			option = {
			    title: {
			        text: '楼宇网速排行',
			        subtext: window.location.host
			    },
			    tooltip: {
			        trigger: 'axis',
			        axisPointer: {
			            type: 'shadow'
			        }
			    },
			    legend: {
			        data: ['下载速度', '上传速度','测试次数']
			    },
			    grid: {
			        left: '10%',
			        right: '4%',
			        bottom: '3%',
			        containLabel: true
			    },
			    xAxis: {
			        type: 'value',
			        boundaryGap: [0, 0.01]
			    },
			    yAxis: {
			        type: 'category',
			        data: position
			    },
			    series: [
			    	{
			    		name:'测试次数',
			    		type: 'line',
			    		data: num
			    	},
			        {
			            name: '下载速度',
			            type: 'bar',
			            data: avgdl
			        },
			        {
			            name: '上传速度',
			            type: 'bar',
			            data: avgul
			        }
			    ]
			};

			option && myChart.setOption(option);

		}).catch(function (error){
			console.log(error);
		});
	}
	function drawRank2() {
		axios.get("/api/getMsgWireless.php",{

		}).then((rep)=>{
			let repdata=rep.data;
			// console.log(repdata[0]);
			for (var i = 0; i < repdata.length; i++) {
				position[i]=repdata[i].position;
				avgdl[i]=repdata[i].avgdl;
				avgul[i]=repdata[i].avgul;
				num[i]=repdata[i].num;
			}
			console.log(position);
			var chartDom = document.getElementById('main2');
			var myChart = echarts.init(chartDom);
			var option;

			option = {
			    title: {
			        text: '楼宇网速排行',
			        subtext: window.location.host

			    },
			    tooltip: {
			        trigger: 'axis',
			        axisPointer: {
			            type: 'shadow'
			        }
			    },
			    legend: {
			        data: ['下载速度', '上传速度','测试次数']
			    },
			    grid: {
			        left: '10%',
			        right: '4%',
			        bottom: '3%',
			        containLabel: true
			    },
			    xAxis: {
			        type: 'value',
			        boundaryGap: [0, 0.01]
			    },
			    yAxis: {
			        type: 'category',
			        data: position
			    },
			    series: [
			    	{
			    		name:'测试次数',
			    		type: 'line',
			    		data: num
			    	},
			        {
			            name: '下载速度',
			            type: 'bar',
			            data: avgdl
			        },
			        {
			            name: '上传速度',
			            type: 'bar',
			            data: avgul
			        }
			    ]
			};

			option && myChart.setOption(option);

		}).catch(function (error){
			console.log(error);
		});
	}
	// window.onload=drawRank;
	window.onload=drawRank2;
</script>
</html>
