<?php

/**
 * ip地址CIDR信息类,支持查询表示范围以及判断某一个IP地址是否在该表示范围内
 */
class IpCIDR {
    static $IPv4_FLAG = 4;
    static $IPv6_FLAG = 6;

    public $prefix;
    public $prefixLength;
    private $type;
    private $range;

    public function __construct($prefix, $prefixLength = 0) {
        // prefix 类似于 x.x.x.x/length 
        if (strpos($prefix, '/') !== false) {
            $temp = explode('/', $prefix);
            $prefix = $temp[0];
            $prefixLength = $temp[1];
        }
        $this->prefix = $prefix;
        if (filter_var($this->prefix, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $type = self::$IPv4_FLAG;
        }
        else if (filter_var($this->prefix, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $type = self::$IPv6_FLAG;
        }
        else {
            $type = 0;
        }
        if ($type == 0) {
            throw new Exception('error cidr prefix given!');
        }
        $prefixLength = (int) $prefixLength;
        if ($prefixLength < 0) {
            throw new Exception('CIDR prefix length must be a positive integer');
        }
        if ($type == self::$IPv4_FLAG && $prefixLength > 32) {
            throw new Exception('IPv4 CIDR prefix length should not greater than 32');
        }
        else if ($type == self::$IPv6_FLAG && $prefixLength > 128) {
            throw new Exception('IPv6 CIDR prefix length should not greater than 128');
        }
        $this->type = $type;
        $this->prefixLength = $prefixLength;

        if ($this->type == self::$IPv4_FLAG) {
            $this->range = $this->getIPv4CIDRRange();
        }
        else {
            $this->range = $this->getIPv6CIDRRange();
        }
    }
    /**
     * 验证某个 cidr字符串是否合法
     */
    static public function validate($cidr, &$message) {
        try {
            $temp = new IpCIDR($cidr);
            $message = 'ok';
            return true;
        }
        catch (Exception $e) {
            $message = $e->getMessage();
            return false;
        } 
    }

    /**
     * 得到当前的CIDR所表示的是IPv4还是IPv6
     * @return integer IPCIDR::IPv4_FLAG | IPCODR::IPv_6_FLAG, 若返回0则表示非法地址
     */
    public function getType() {
        return $this->type;
    }

    /**
     * 返回当前CIDR所表示的范围
     */
    public function getRange() {
        return $this->range;
    }

    /**
     * 将给定的字符串按照指定宽度为一组，将每组从指定的进制转化为另一种进制
     *
     * @param string $source 源字符串
     * @param integer $width 每组的宽度，不足的部分补足前导0
     * @param integer $fromBase 源字符串进制
     * @param integer $toBase 目标字符串进制
     * @return array 
     */
    private function baseConvertWithWidth($source, $width, $fromBase, $toBase) {
        $res = [];
        $i = strlen($source) - 1;
        while($i >= 0) {
            $temp = '';
            for($j = 0; $j < $width; $j ++) {
                $temp .= $source[$i];
                $i --;
            }
            $temp = strrev($temp);
            $res = array_merge([base_convert($temp, $fromBase, $toBase)], $res);
        } 
        return $res;
    }
    /**
    * 计算给定的IPv6下的CIDR表示的地址范围
    *
    * @param string $prefix 前缀
    * @param integer $length 共享前缀长度
    * @return array 
    */
    private function getIPv6CIDRRange() {
        $prefix = $this->prefix;
        $length = $this->prefixLength;
        $prefix = $this->expandIpv6($prefix);
        $nums = explode(':', $prefix);
        $bin = '';
        $length = 128 - $length;
        for($i = 0; $i < 8; $i ++) {
            $bin .= str_pad(base_convert($nums[$i], 16, 2), 16, '0', STR_PAD_LEFT);
        }
        // 计算起始IP地址
        for($i = 0; $i < $length; $i ++) {
            $bin[128 - $i - 1] = '0';
        }
        $startIp = $this->baseConvertWithWidth($bin, 16, 2, 16);
        $startIp = implode(':', $startIp);
        // 计算结束IP
        for($i = 0; $i < $length; $i ++) {
            $bin[128 - $i - 1] = '1';
        }
        $endIp = $this->baseConvertWithWidth($bin, 16, 2, 16);
        $endIp = implode(':', $endIp);
        return [$startIp, $endIp];
    }


    /**
    * 得到给定的IPv4地址下的CIDR范围
    *
    * @return array 包含开始地址与结束地址的数组 
    */
    private function getIPv4CIDRRange() {
        $prefix = $this->prefix;
        $length = $this->prefixLength;
        $nums = explode('.', $prefix);
        // 给定的长度是前缀长度，在此转化成后面可变部分的长度
        $length = 32 - $length;
        $binIp = '';
        for($i = 0; $i < 4; $i ++) {
            $binIp .=  str_pad(base_convert($nums[$i], 10, 2), 8, '0', STR_PAD_LEFT);
        }
        // 将后面的$length 位置为0，得到起始IP
        for($i = 0; $i < $length; $i ++) {
            $binIp[32 - $i - 1] = '0';
        }
        $nums = $this->baseConvertWithWidth($binIp, 8, 2, 10);
        $startIp = implode('.', $nums);
        // 将后面的$length 位置为1，得到结束IP
        for($i = 0; $i < $length; $i ++) {
            $binIp[32 - $i - 1] = '1';
        }
        $nums = $this->baseConvertWithWidth($binIp, 8, 2, 10);
        $endIp= implode('.', $nums);
        return [$startIp, $endIp];
    }

    /**
    * 展开IPv6地址中的双冒号
    *
    * @return void
    */
    private function expandIpv6($ip) {
        if(strpos($ip, '::') === false) {
            // 没有::
            return $ip;
        }
        // 省略的为0的项数，为冒号个数减去两个冒号个数后得到已经有的项数，在被7减去
        $nums = 9 - substr_count($ip, ':');
        $replace = ':';
        for($i = 0; $i < $nums; $i ++) {
            $replace .= '0:';
        }
        $ip = str_replace('::', $replace, $ip);
        if($ip[0] == ':') {
            $ip = substr($ip, 1);
        }
        if($ip[strlen($ip) - 1] == ':') {
            $ip = substr($ip, 0, -1);
        }
        return $ip;
    }
    /**
    * 按照字符串分割给定的字符串并将分割的每一个部分进行pad后再进行拼接
    *
    * @param string $str
    * @param string $splitFlagStr
    * @param integer $padLength
    * @param string $padStr
    * @param integer $padFlag
    * @return string 
    */
    private function splitAndPad($str, $splitFlagStr, $padLength, $padStr = ' ', $padFlag) {
        $arr = explode($splitFlagStr, $str);
        for ($i = 0; $i < count($arr); $i ++) {
            $arr[$i] = str_pad($arr[$i], $padLength, $padStr, $padFlag);
        }
        return implode($splitFlagStr, $arr);
    }

    /**
    * 判断给定的IP地址是否属于给定的CIDR所表示的IP范围内
    *
    * @param string $ip 给定的IP
    * @param string $cidrPrefix 给定的CIDR前缀
    * @param integer $cidrLength CIDR前缀长度
    * @return boolean
    */
    public function isIpInRange($ip) {
        $cidrLength = $this->prefixLength;
        $cidrPrefix = $this->prefix;
        if ($this->type == self::$IPv4_FLAG) {
            $range = $this->getIPv4CIDRRange($cidrPrefix, $cidrLength);
            $ip = $this->splitAndPad($ip, '.', 3, '0', STR_PAD_LEFT);
            $range[0] = $this->splitAndPad($range[0], '.', 3, '0', STR_PAD_LEFT);
            $range[1] = $this->splitAndPad($range[1], '.', 3, '0', STR_PAD_LEFT);
            return strcmp($ip, $range[0]) >= 0 && strcmp($ip, $range[1]) <= 0;
        }
        else if ($this->type == self::$IPv6_FLAG) {
            $range = $this->getIPv6CIDRRange($cidrPrefix, $cidrLength);
            $ip = $this->splitAndPad($ip, ':', 4, '0', STR_PAD_LEFT);
            $range[0] = $this->splitAndPad($range[0], ':', 4, '0', STR_PAD_LEFT);
            $range[1] = $this->splitAndPad($range[1], ':', 4, '0', STR_PAD_LEFT);
            return strcmp($ip, $range[0]) >= 0 && strcmp($ip, $range[1]) <= 0;
        }
        else {
            throw new Exception('invalid ip ');
        }
    }

    /**
     * 获得该CIDR前缀长度
     *
     * @return integer 
     */
    public function getPrefixLength() {
        return $this->prefixLength;
    }

    /**
     * 获得该CIDR所表示的IP范围内的IP数量是2的几次幂
     *
     * @return integer
     */
    public function getIpNumsWithBitLength() {
        if ($this->type == self::$IPv4_FLAG) {
            return 32 - $this->getPrefixLength();
        }
        return 128 - $this->getPrefixLength();
    }
}

// IpCIDR class test
// $ipv4 = new IpCIDR('10.10.1.32/1');
// var_dump($ipv4->getRange());
// var_dump($ipv4->isIpInRange('10.10.127.32'));
// 
// $ipv6 = new IpCIDR('::2001:da8:ff3a:c88e', 119);
// var_dump($ipv6->getRange());
// var_dump($ipv6->isIpInRange('0000:0000:0000:0000:2001:0da8:ff3a:c8ff'));
// var_dump(IpCIDR::validate('10.10.1.32/9', $message));
// var_dump($message);
// var_dump(IpCIDR::validate('10.10.1./9', $message));
// var_dump($message);
// var_dump(IpCIDR::validate('test', $message));
// var_dump($message);
// var_dump(IpCIDR::validate('::1', $message));
// var_dump(IpCIDR::validate('::1/130', $message));
// var_dump($message);




/**
 * 支持导入指定的IP CIDR过滤列表，以检测给定的IP是否属于某一个CIDR列表中
 * 并根据匹配的规则查找对应的预设相关信息
 * 以做到将IP与ISP信息通过CIDR信息关联
 */
class IpCIDRFilter {
    public $filterList;
    public function __construct($filterList = []) {
        for($i = 0; $i < count($filterList); $i ++) {
            $singleFilter = $filterList[$i];
            if (! $singleFilter['rule'] instanceof IpCIDR) {
                throw new Exception('single filter rule must be a instance of class IpCIDR');
            }
        }
        $this->filterList = $filterList;
    }

    /**
     * 验证指定的IP符合哪些过滤器
     * 
     * @param string 需要验证的IP
     * @return array 返回符合过滤规则的过滤器的Index列表 
     */
    public function test($ip) {
        $matchedIndex = [];
        for($i = 0; $i < count($this->filterList); $i ++) {
            $singleFilter = $this->filterList[$i]['rule'];
            if ($singleFilter->isIpInRange($ip)) {
                $matchedIndex[] = $i;
            }
        }
        return $matchedIndex;
    }

    /**
     * 通过指定的Index得到对应的过滤器的信息
     * @param integer $index
     * @return mixed 指定的过滤器info字段，若无则返回null
     */
    public function getFilterInfoByIndex($index) {
        if (isset($this->filterList[$index])) {
            return $this->filterList[$index]['info'];
        }
        return null;
    }
}


/**
 * 从数据库中加载CIDR过滤器列表
 * @return array
 */
function getCIDRListFromMysql() {
    include(__DIR__ . '/../results/telemetry_settings.php');
    $cidrRuleList = [];
    $conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename, $MySql_port);
    // 由于一般CIDR网段不会有交集，但是会有完全包含的情况
    // 但是可能会有完全包含的关系
    // 因此需要调整优先级使得先过滤范围较小的CIDR
    $p = $conn->prepare('SELECT `cidr`, position, accessmethod, isp, ispinfo FROM  speedtest_cidrinfo ORDER BY `index` DESC, id DESC');
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
    for ($i = 0; $i < count($cidrRuleList); $i++) {
        $currentRule = $cidrRuleList[$i];
        if (isset($currentRule['rule']) && is_string($currentRule['rule'])) {
            try {
                $cidrFilterList[] = [
                    'rule' => new IpCIDR($currentRule['rule']),
                    'info' => $currentRule['info']
                ];
            } catch (Exception $e) {
                // nothing to do
            }
        }
    }
    return $cidrFilterList;
}
