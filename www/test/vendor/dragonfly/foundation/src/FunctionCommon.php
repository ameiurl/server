<?php
/**
 * 公用函数库
 */

if (!function_exists('finish_request'))
{
    function finish_request($close_session = true) {
        if (function_exists('fastcgi_finish_request')) {
            if($close_session && isset($_SESSION) && PHP_SESSION_ACTIVE == session_status())
            {
                session_commit();
                session_write_close();
            }
            fastcgi_finish_request();
        }
    }
}


if (!function_exists('is_cli'))
{
    function is_cli(){
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
    }
}

//兼容cli模式$_SERVER
if(is_cli())
{
    $_SERVER['HTTP_X_FORWARDED_FOR'] = getenv('HTTP_X_FORWARDED_FOR');
    $_SERVER['HTTP_CLIENT_IP'] = getenv('HTTP_CLIENT_IP');
    $_SERVER['REMOTE_ADDR'] = getenv('REMOTE_ADDR');
    $_SERVER['SERVER_ADDR'] = getenv('SERVER_ADDR');
    $_SERVER['LOCAL_ADDR'] = getenv('LOCAL_ADDR');
}

/**
 * 获取客户端ip
 * @return array|false|string
 */
if (!function_exists('get_client_ip'))
{
    function get_client_ip($type = 0, $adv = true)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }
}

/**
 * 获取服务端ip
 * @return array|false|string
 */
if (!function_exists('get_server_ip'))
{
    function get_server_ip()
    {
        if (isset($_SERVER)) {
            if ($_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip;
    }
}


    /**
 * 打印函数
 * @param null $var
 */
if (!function_exists('pr'))
{
    function pr($var = null)
    {
        @header('Content-Type: text/html; charset=utf-8');
        if (is_bool($var)) {
            var_dump($var);
        } else if (is_null($var)) {
            var_dump(NULL);
        } else {
            echo "<pre>" . print_r($var, true) . "</pre>";
        }
    }
}


/**
 *  检查是否是内网ip
 */
if (!function_exists('is_inner_ip'))
{
    function is_inner_ip()
    {
        $ip = get_client_ip();

        $ipArr = array(
            '120.42.46.10',
            '117.25.182.10',
            '110.80.36.234',
            '27.154.234.150',
            '120.41.7.98',
            '110.80.36.74',
            '207.246.109.24',
            '127.0.0.1',
            '::1',
            '0.0.0.0'
        );

        if(stripos($ip, "172.18.") === 0)
        {
            return true;
        }

        return in_array($ip, $ipArr);
    }
}

/**
 * 处理金额（将分转元）
 * @param float|int $amount 金额（单位分）
 * @param int $format 是否格式化数字
 * @param int $round 保留位数
 * @return float
 */
if (!function_exists('deal_amount'))
{
    function deal_amount($amount = 0, $format = 0, $round = 2){
        $amount = floatval($amount);
        $round  = intval($round);
        $format = intval($format);
        //$amount = bcdiv($amount , 100, $round);
        $amount = round(bcdiv($amount , 100, $round + 1), $round);
        if ($format){
            $amount = number_format($amount, $round);
        }
        return $amount;
    }
}


/**
 * 将换行符号替换成br
 * @param $str 字符串
 * @param string $char 要替换的字符
 * @return string
 */
if (!function_exists('nl2br'))
{
    function nl2br($str, $char = "<br/>")
    {
        $str = str_replace("\r\n", $char, $str);
        $str = str_replace("\n", $char, $str);
        return $str;
    }
}

/**
 * 将数字转为大写
 * @param int $number 数字
 * @param int $type 1.中文简体  2.中文繁体
 * @return mixed|string
 */
if (!function_exists('number_to_big'))
{
    function number_to_big($number = 0, $type = 1){
        $isMinus = false;
        if ($number < 0) {
            $isMinus = true;
            $number = abs($number);
        }
        $number = floatval($number);

        $cnyunits = array("点", "", "");
        if ($type == 2){
            $cnums = array("零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖");
            $grees = array("拾", "佰", "仟", "万", "拾", "佰", "仟", "亿");
        }else{
            $cnums = array("零", "一", "二", "三", "四", "五", "六", "七", "八", "九");
            $grees = array("十", "百", "千", "万", "十", "百", "千", "亿");
        }

        list($ns1, $ns2) = explode(".", $number, 2);
        $ns2 = array_filter(array($ns2[1], $ns2[0]));
        $ret = array_merge($ns2, array(implode("", cny_map_unit(str_split($ns1), $grees)), ""));
        $ret = implode("", array_reverse(cny_map_unit($ret, $cnyunits)));
        $value = str_replace(array_keys($cnums), $cnums, $ret);
        $value = rtrim($value, '点');

        if ($isMinus) {
            return '负' . $value;
        } else {
            return $value;
        }
    }
}


/**
 * 金额小写转换为大写
 * @param float $amount 金额
 * @return string
 */
if (!function_exists('amount_to_big'))
{
    function amount_to_big($amount)
    {
        $isMinus = false;
        if ($amount < 0) {
            $isMinus = true;
            $amount = abs($amount);
        }
        $amount = floatval($amount);

        $cnums = array("零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖");
        $cnyunits = array("圆", "角", "分");
        $grees = array("拾", "佰", "仟", "万", "拾", "佰", "仟", "亿");

        list($ns1, $ns2) = explode(".", $amount, 2);
        $ns2 = array_filter(array($ns2[1], $ns2[0]));
        $ret = array_merge($ns2, array(implode("", cny_map_unit(str_split($ns1), $grees)), ""));
        $ret = implode("", array_reverse(cny_map_unit($ret, $cnyunits)));
        $value = str_replace(array_keys($cnums), $cnums, $ret) . '整';
        if ($ns1 == 0 || $value == '圆整') {
            $value = '零' . $value;
        }

        if ($isMinus) {
            return '负数' . $value;
        } else {
            return $value;
        }
    }
}

/**
 * 将数字位置与相应金额单位对应
 * @param $list
 * @param $units
 * @return array
 */
if (!function_exists('cny_map_unit'))
{
    function cny_map_unit($list, $units)
    {
        $ul = count($units);
        $xs = array();
        foreach (array_reverse($list) as $x) {
            $l = count($xs);
            if ($x != "0" || !($l % 4)) $n = ($x == '0' ? '' : $x) . ($units[($l - 1) % $ul]);
            else $n = is_numeric($xs[0][0]) ? $x : '';
            array_unshift($xs, $n);
        }
        return $xs;
    }
}


/**
 * 检查身份证号码
 * @param string $id_card
 * @return bool|string
 */
if (!function_exists('check_id_card'))
{
    function check_id_card($id_card = '')
    {
        if (empty($id_card)) {
            return false;
        }
        $idcard = $id_card;
        $City = array(
            11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林",
            23 => "黑龙江", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西",
            37 => "山东", 41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南",
            50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏", 61 => "陕西", 62 => "甘肃",
            63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外");

        $iSum = 0;
        $idCardLength = strlen($idcard);
        //长度验证
        $check_id_card = preg_match("/^\d{17}(\d|x)$/i", $id_card) || preg_match("/^\d{15}$/i", $id_card);
        if (!$check_id_card) {
            return false;
        }
        //地区验证
        if (!array_key_exists(intval(substr($idcard, 0, 2)), $City)) {
            return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15) {
            $sBirthday = '19' . substr($idcard, 6, 2) . '-' . substr($idcard, 8, 2) . '-' . substr($idcard, 10, 2);
            $d = strtotime($sBirthday);
            $dd = date('Y-m-d', $d);
            if ($sBirthday != $dd) {
                return false;
            }
            $idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9);//
            $Bit18 = get_verify_bit($idcard);//算出第18位校验码
            $idcard = $idcard . $Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard, 6, 4);
        if ($year < 1900 || $year > 2078) {
            return false;
        }

        //18位身份证处理
        $sBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
        $d = strtotime($sBirthday);
        $dd = date('Y-m-d', $d);

        if ($sBirthday != $dd) {
            return false;
        }

        //身份证编码规范验证
        $idcard_base = substr($idcard, 0, 17);
        if (strtoupper(substr($idcard, 17, 1)) != get_verify_bit($idcard_base)) {
            return false;
        }
        return $id_card;
    }
}


/**
 * 计算身份证校验码，根据国家标准GB 11643-1999
 * @param $idcard_base
 * @return bool|mixed
 */
if (!function_exists('get_verify_bit'))
{
    function get_verify_bit($idcard_base)
    {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }
}

/**
 * 获取日期对应的星期
 * @param mixed $time  时间戳
 * @return string
 */
if (!function_exists('get_verify_bit'))
{
    function get_week_name($time){
        $weekArr = [ 1 => '周一', 2 => '周二', 3 => '周三', 4 => '周四', 5 => '周五', 6 => '周六', 7 => '周日'];
        $w = date('N', $time);
        return $weekArr[$w];
    }
}

/**
 * 获取二维数组中的元素,用于兼容5.5以下的写法
 *
 * @param type $input
 * @param type $columnKey
 * @param type $indexKey
 * @return type
 */
if(!function_exists("array_column"))
{
    function array_column($input, $columnKey, $indexKey = null)
    {
        $columnKeyIsNumber	 = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull		 = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber	 = (is_numeric($indexKey)) ? true : false;
        $result				 = array();
        foreach ((array) $input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            }elseif (is_null($columnKey)){
                $tmp = array_shift($row);
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }
}


if (!function_exists('base64_to_image')) {
    /**
     * base64格式转图片并保存
     * @param $base64_image_content base64格式内容
     * @param $path 保存目录
     * @return string
     */
    function base64_to_image($base64_image_content, $path) {
        if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            return '';
        }

        $root = '.';
        $type = $result[2];
        $new_file = $path . "/" .date('Ymd',time()) . "/";
        if (!file_exists($root.$new_file)) {
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($root.$new_file, 0700, true);
        }

        $new_file = $new_file . mt_rand(1, 1000000) . ".{$type}";
        if (file_put_contents($root.$new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            return $new_file;
        } else {
            return '';
        }
    }
}

if (!function_exists('conver_base')) {
    // 定义一些常用的进制
    defined('BASE_BIN') || define('BASE_BIN', '01');
    defined('BASE_OCT') || define('BASE_OCT', '01234567');
    defined('BASE_DEC') || define('BASE_DEC', '0123456789');
    defined('BASE_HEX') || define('BASE_HEX', '0123456789abcdef');
    defined('BASE_2') || define('BASE_2', BASE_BIN);
    defined('BASE_8') || define('BASE_8', BASE_OCT);
    defined('BASE_10') || define('BASE_10', BASE_DEC);
    defined('BASE_16') || define('BASE_16', BASE_HEX);
    defined('BASE_36') || define('BASE_36', '0123456789abcdefghijklmnopqrstuvwxyz');
    // url 友好且用户友好的进制，去除了输出可能引起歧义的 ijloqIOUuVv 字符
    defined('BASE_51') || define('BASE_51', '0123456789abcdefghkmnprstwxyzABCDEFGHJKLMNPQRSTWXYZ');
    defined('BASE_62') || define('BASE_62', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    /**
     * 数值进制转换
     *
     * @param string $numberInput 要转换的数值
     * @param string $toBaseInput 要输出的进制允许字符，可以是任意的 ascii 可视字符
     * @param string $fromBaseInput 输入数值的进制允许字符，从0开始，如二进制 01，八进制 01234567，默认使用十进制
     * @return string
     * @see http://php.net/manual/function.base-convert.php#106546
     */
    function conver_base($numberInput, $toBaseInput, $fromBaseInput = '0123456789')
    {
        $numberInput = (string)$numberInput;
        if ($fromBaseInput === $toBaseInput) {
            return $numberInput;
        }

        $retval = '';
        if ($toBaseInput === '0123456789') {
            $fromLen = strlen($fromBaseInput);
            $fromBase = str_split($fromBaseInput, 1);

            $numberLen = strlen($numberInput);
            $number = str_split($numberInput, 1);
            $retval = 0;
            for ($i = 1; $i <= $numberLen; $i++) {
                $retval = bcadd(
                    $retval,
                    bcmul(array_search($number[$i - 1], $fromBase), bcpow($fromLen, $numberLen - $i))
                );
            }
            return $retval;
        }

        if ($fromBaseInput !== '0123456789') {
            $base10 = conver_base($numberInput, '0123456789', $fromBaseInput);
        } else {
            $base10 = $numberInput;
        }

        $toLen = strlen($toBaseInput);
        $toBase = str_split($toBaseInput, 1);
        if ($base10 < strlen($toBaseInput)) {
            return $toBase[$base10];
        }

        while ($base10 !== '0') {
            $retval = $toBase[bcmod($base10, $toLen)] . $retval;
            $base10 = bcdiv($base10, $toLen, 0);
        }

        return $retval;
    }
}

//数组 转 对象
if (!function_exists('array_to_object')) {
    function array_to_object($arr) {
        if (gettype($arr) != 'array') {
            return null;
        }
        foreach ($arr as $k => $v) {
            if (gettype($v) == 'array' || getType($v) == 'object') {
                $arr[$k] = (object)array_to_object($v);
            }
        }
        return (object)$arr;
    }
}

//对象 转 数组
if (!function_exists('object_to_array')) {
    function object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return null;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)object_to_array($v);
            }
        }
        return $obj;
    }
}