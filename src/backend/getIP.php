<?php
/*
	This script detects the client's IP address and fetches ISP info from ipinfo.io/
	Output from this script is a JSON string composed of 2 objects: a string called processedString which contains the combined IP, ISP, Contry and distance as it can be presented to the user; and an object called rawIspInfo which contains the raw data from ipinfo.io (will be empty if isp detection is disabled).
	Client side, the output of this script can be treated as JSON or as regular text. If the output is regular text, it will be shown to the user as is.
*/
error_reporting(0);
$ip = "";
header('Content-Type: application/json; charset=utf-8');
if(isset($_GET["cors"])){
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['X-Real-IP'])) {
    $ip = $_SERVER['X-Real-IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    $ip = preg_replace("/,.*/", "", $ip); # hosts are comma-separated, client is first
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

// $ip = preg_replace("/^::ffff:/", "", $ip);
include_once(__DIR__ . '/../utils/cidr.php');
include_once(__DIR__ . '/../results/telemetry_settings.php');
// 得到CIDR 过滤列表
$cidrRuleList = [];
$conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename, $MySql_port);
$p = $conn->prepare('SELECT `cidr`, position, accessmethod, isp, ispinfo FROM  speedtest_cidrinfo');
$p->execute();
$p->bind_result($cidr, $position, $accessMethod, $isp, $ispInfo);
while ($p->fetch()) {
    $cidrRuleList[] = [
        'rule' => $cidr,
        'info' => [
            'position' => $position,
            'accessMethod' => $accessMethod,
            'isp' => $isp,
            'ispInfo' => $ispInfo
        ]
    ];
}
$p->close();
$conn->close();
$cidrFilterList = [];
for ($i = 0; $i < count($cidrRuleList); $i ++) {
    $currentRule = $cidrRuleList[$i];
    if (isset($currentRule['rule']) && is_string($currentRule['rule'])) {
        try {
            $cidrFilterList[] = [
                'rule' => new IpCIDR($currentRule['rule']),
                'info' => $currentRule['info']
            ];
        }
        catch (Exception $e) {
            // nothing to do
        }
    }
}
$filter = new IpCIDRFilter($cidrFilterList);
$matchedIndex = $filter->test($ip);
if (isset($matchedIndex[0])) {
    $ispInfo = $filter->getFilterInfoByIndex($matchedIndex[0]);
    $ispInfo['ip'] = $ip;
    echo json_encode($ispInfo);
    die();
}

// $school_cidr_info = json_encode(['processedString' => $ip . " - private IPv4 access", 'rawIspInfo' => ""]);
// if(preg_match('/^202\.4\.1(2[8-9]|[3-5]\d)\./', $ip) === 1) { // IPv4 202.4.128.0/19
//     echo $school_cidr_info;
//     die();
// }
// if(preg_match('/^219\.242\.(9[6-9]|10\d|111)\./', $ip) === 1) { // IPv4 219.242.96.0/20
//     echo $school_cidr_info;
//     die();
// }
// if(preg_match('/^219\.225\.(\d|1[0-5])\./', $ip) === 1) { // IPv4 219.225.0.0/20
//     echo $school_cidr_info;
//     die();
// }
// if(preg_match('/^222\.199\.2(2[4-9]|[3-4]\d|5[0-5])\./', $ip) === 1) { // IPv4 222.199.224.0/19
//     echo $school_cidr_info;
//     die();
// }
// if(preg_match('/^121\.195\.1(2[8-9]|[3-5]\d)\./', $ip) === 1) { // IPv4 121.195.128.0/19
//     echo $school_cidr_info;
//     die();
// }
// if(preg_match('/^58\.195\.88\.(6[4-9]|7\d)$/', $ip) === 1) { // IPv4 58.195.88.64/28
//     echo $school_cidr_info;
//     die();
// }
// $school_cidr_info = json_encode(['processedString' => $ip . " - private IPv6 access", 'rawIspInfo' => ""]);
// if(strpos($ip, '2001:0250:0207:') === 0) { // IPv6 2001:250:207::/48
//     echo $school_cidr_info;
//     die();
// }
// if(strpos($ip, '0010:0001:0001:0007:' === 0)) { // IPv6 10:1:1:7::/64
//     echo $school_cidr_info;
//     die();
// }
// if(strpos($ip, '2001:0da8:ff3a:c88e:') === 0) { // IPv6 2001:da8:ff3a:c88e::/64
//     echo $school_cidr_info;
//     die();
// }
// if(strpos($ip, '2001:0da8:0237:') === 0) { // IPv6 2001:da8:237::/48
//     echo $school_cidr_info;
//     die();
// }
/**
 * Optimized algorithm from http://www.codexworld.com
 *
 * @param float $latitudeFrom
 * @param float $longitudeFrom
 * @param float $latitudeTo
 * @param float $longitudeTo
 *
 * @return float [km]
 */
function distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) {
    $rad = M_PI / 180;
    $theta = $longitudeFrom - $longitudeTo;
    $dist = sin($latitudeFrom * $rad) * sin($latitudeTo * $rad) + cos($latitudeFrom * $rad) * cos($latitudeTo * $rad) * cos($theta * $rad);
    return acos($dist) / $rad * 60 * 1.853;
}
function getIpInfoTokenString(){
	$apikeyFile="getIP_ipInfo_apikey.php";
	if(!file_exists($apikeyFile)) return "";
	require $apikeyFile;
	if(empty($IPINFO_APIKEY)) return "";
	return "?token=".$IPINFO_APIKEY;
}
if (isset($_GET["isp"])) {
    $isp = "";
	$rawIspInfo=null;
    try {
        $json = file_get_contents("https://ipinfo.io/" . $ip . "/json".getIpInfoTokenString());
        $details = json_decode($json, true);
		$rawIspInfo=$details;
        if (array_key_exists("org", $details)){
            $isp .= $details["org"];
			$isp=preg_replace("/AS\d{1,}\s/","",$isp); //Remove AS##### from ISP name, if present
		}else{
            $isp .= "Unknown ISP";
		}
		if (array_key_exists("country", $details)){
			$isp .= ", " . $details["country"];
		}
        $clientLoc = NULL;
        $serverLoc = NULL;
        if (array_key_exists("loc", $details)){
            $clientLoc = $details["loc"];
		}
        if (isset($_GET["distance"])) {
            if ($clientLoc) {
				$locFile="getIP_serverLocation.php";
				$serverLoc=null;
				if(file_exists($locFile)){
					require $locFile;
				}else{
					$json = file_get_contents("https://ipinfo.io/json".getIpInfoTokenString());
					$details = json_decode($json, true);
					if (array_key_exists("loc", $details)){
						$serverLoc = $details["loc"];
					}
					if($serverLoc){
						$lf=fopen($locFile,"w");
						fwrite($lf,chr(60)."?php\n");
						fwrite($lf,'$serverLoc="'.addslashes($serverLoc).'";');
						fwrite($lf,"\n");
						fwrite($lf,"?".chr(62));
						fclose($lf);
					}
				}
                if ($serverLoc) {
                    try {
                        $clientLoc = explode(",", $clientLoc);
                        $serverLoc = explode(",", $serverLoc);
                        $dist = distance($clientLoc[0], $clientLoc[1], $serverLoc[0], $serverLoc[1]);
                        if ($_GET["distance"] == "mi") {
                            $dist /= 1.609344;
                            $dist = round($dist, -1);
                            if ($dist < 15)
                                $dist = "<15";
                            $isp .= " (" . $dist . " mi)";
                        }else if ($_GET["distance"] == "km") {
                            $dist = round($dist, -1);
                            if ($dist < 20)
                                $dist = "<20";
                            $isp .= " (" . $dist . " km)";
                        }
                    } catch (Exception $e) {

                    }
                }
            }
        }
    } catch (Exception $ex) {
        $isp = "Unknown ISP";
    }
    echo json_encode([
        'ip' => $ip,
        'isp' => $isp,
        'ispInfo' => $rawIspInfo,
        'position' => null,
        'accessMethod' => null
    ]);
} else {
    echo json_encode([
        'ip' => $ip,
        'isp' => $isp,
        'ispInfo' => null,
        'position' => null,
        'accessMethod' => null
    ]);
}
