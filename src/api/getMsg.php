<?php
require_once './init.php';
$sql="select * from speedtest_cidrinfo";
$res=$conn->query($sql);
$array=array();
$cidr=array();
$speed=array();
if ($res) {
	while ($result=mysqli_fetch_array($res)) {
			$array[]=$result;
		}
		// var_dump($array);
	if ($array==null) {
		echo '<script>alert("表为空");</script>';
	}else{
		foreach ($array as $v) {
			$str_len=strlen($v['cidr']);
			$start=strstr($v['cidr'],'/');
			$number=(int)substr(stristr($v['cidr'],"/"),1);
			$site=stristr($v['cidr'],'/',true);//返回IP地址
			$BinarySite=getCidr($site);
			$cidr[$v['id']]=array(
				'position'=>$v['position'],
				'site'=>$BinarySite,
				'number'=>$number
			);
		}
		$sql2="select * from speedtest_infos";
		$res2=$conn->query($sql2);
		while ($result=mysqli_fetch_array($res2)) {
			$array[]=$result;
		}
		if ($array!=null) {
			foreach ($array as $v) {
				$info=json_decode($v['ispinfo'],true);
				$position=$info['position'];
				$accessMethod=$info['accessMethod'];
				if ($speed[$position]&&$accessMethod=='有线连接') {
					$speed[$position]['dl']+=$v['dl'];
					$speed[$position]['ul']+=$v['ul'];
					$speed[$position]['num']++;
					$speed[$position]['avgdl']=$speed[$position]['dl']/$speed[$position]['num'];
					$speed[$position]['avgul']=$speed[$position]['ul']/$speed[$position]['num'];
				}else{
					$speed[$position]=array(
						'dl'=>$v['dl'],
						'num'=>1,
						'ul'=>$v['ul']
					);
				}
				
			}
			
		}else{
			echo '<script>alert("当前暂无测速数据");</script>';
		}
		// var_dump($speed);
		$keysvalue=$newspeed=array();
		foreach ($speed as $k => $v) {
			$keysvalue[$k]=$v['avgdl'];
		}
		asort($keysvalue);
		reset($keysvalue);
		foreach ($keysvalue as $k => $v) {
			$newspeed[$k]=$speed[$k];
		}
		$returnSpeed=array();
		$i=0;
		foreach ($newspeed as $k => $v) {
			if ($k!='Unknown'&&$k) {
				$returnSpeed[$i++]=array(
					'position'=>$k,
					'avgdl'=>round($v['avgdl'],2),
					'avgul'=>round($v['avgul'],2),
					'num'=>$v['num']
				);
			}
			
		}
		// var_dump($returnSpeed);
		echo json_encode($returnSpeed);
		// var_dump($newspeed);
	}
}

// function getPosition($ip){
// 	$BinarySite=getCidr($ip);
// 	// $size=count($cidr);
// 	var_dump($cidr);
// 	for ($i=1; $i < 103; $i++) { 
// 		if (strncmp($BinarySite, $cidr[$i]['site'], $cidr[$i]['number'])==0) {
// 			return $cidr[$i]['position'];
// 			// var_dump($cidr[$i]['position']);
// 		}else{
// 			return "unknow";
// 		}
// 	}
	
// }
function getCidr($site)//转为二进制IP地址
{
	$siteArray=explode(".", $site);
	$BinarySite='';
	foreach ($siteArray as $k) {
		$numK=strval(sprintf("%08d",decbin((int)$k)));
		$BinarySite=$BinarySite.$numK;
	}
	return $BinarySite;
}
?>

