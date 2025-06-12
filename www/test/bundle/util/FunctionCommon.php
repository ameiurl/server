<?php
/**
 * 公用函数库
 * Created by PhpStorm.
 * User: yuzq
 * Date: 2018/2/6
 * Time: 17:15
 */


/**
 * 计算两个时间戳的相隔天数
 * @param $minTime
 * @param $maxTime
 * @return string
 */
function differenceTimeCountDays($minTime, $maxTime){
    $minDate = date_create(date("Y-m-d", $minTime));
    $maxDate = date_create(date("Y-m-d", $maxTime));
    $diff = date_diff($minDate, $maxDate);
    return $diff->format('%a');
}

/**
 * 打印函数
 * @param null $var
 */
function pr($var = null)
{
    if(is_inner_ip())
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
function is_inner_ip()
{
    //$ip = request()->ip();

    $ipArr = array(
        '121.10.141.189',//erp.e10.cncn.net

        '120.42.46.10',
        '117.25.182.10',
        '110.80.36.234',
        '27.154.234.150',
        '120.41.7.98',
        '110.80.36.74',
        '207.246.109.24',

        '192.168.9.15',
        '27.154.237.90',
        '59.57.174.178',
        '120.41.180.63',
        '120.41.190.37',
        '172.16.0.1',
        '59.57.169.75',
        '110.87.68.207',
        '59.57.169.75',
        '120.41.190.9',
        '59.57.193.46',
        '59.57.193.58',
        '110.87.13.34',
        '117.30.224.56',
        '110.87.13.22',

        //在家办公-个人电脑ip
        '110.87.110.98',
        '180.97.106.139',
        '180.97.106.19',
        '110.87.118.110',
        '125.210.245.118',
        '117.30.232.83',
        '110.80.36.74',
        '59.61.24.218',
        '58.23.236.200',
        '117.30.118.192',
        '110.85.45.235',
        '58.23.236.113',
        '110.87.116.148',
        '125.210.251.208',
        '117.29.137.226',
        '223.104.6.51',
        '110.87.80.205',
        '183.251.90.175',
        '58.23.232.133',
    );

    if(stripos($ip, "192.168.") === 0 || stripos($ip, "172.16.") === 0)
    {
        return true;
    }

    if (FORMAL_ENV == 0){
        $ipArr = array_merge($ipArr, ['127.0.0.1', '::1', '0.0.0.0']);
    }

    return in_array($ip, $ipArr);
}

function is_inner_dev_server_ip()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $ipArr = array(
        '::1',
        '0.0.0.0',
        '192.168.17.1',
        '127.0.0.1',
        '192.168.9.15',
    );

    if(stripos($ip, "192.168.") === 0 || stripos($ip, "172.16.") === 0)
    {
        return true;
    }

    return in_array($ip, $ipArr);
}

/**
 * 获取数组value
 * @param array|object $arr 数组
 * @param string $name 键名
 * @param mixed $default 默认值
 * @return mixed
 */
function get_value($arr, $name, $default = null)
{
    if (strpos($name, '.') !== false) {
        foreach (explode('.', $name) as $item_name) {
            $arr = get_value($arr, $item_name, $default);
            if ($arr == $default) {
                return $default;
            }
        }
        return $arr;
    } else {
        return $arr[$name] !== null ? $arr[$name] : $default;
    }
}

/**
 * 根据指定的键值索引数组
 * @param array $array 指定数组
 * @param string $key 索引值
 * @return array
 */
function arr_index($array, $key)
{
    $result = [];
    foreach ($array as $element) {
        $result[$element[$key]] = $element;
    }
    unset($array);

    return $result;
}

/**
 * 返回数组中指定列的值。
 * @param array|object $array 数组
 * @param string|\Closure $name  指定列
 * @param bool $keepKeys 是否维护阵列键。 如果为false，则生成数组
 * 将被重新索引整数。
 * @return array 列值列表
 */
function get_column($array, $name, $keepKeys = true)
{
    $result = [];
    if ($keepKeys) {
        foreach ($array as $k => $element) {
            $result[$k] = get_value($element, $name);
        }
    } else {
        foreach ($array as $element) {
            $result[] = get_value($element, $name);
        }
    }

    return $result;
}

/**
 * 将对象或对象数组转换为数组。
 * @param object|array|string $object 要转换为数组的对象
 * @param boolean $recursive 是否将对象的属性递归转换为数组。
 * @return array
 */
function to_array($object, $recursive = true)
{
    if (is_array($object)) {
        if ($recursive) {
            foreach ($object as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $object[$key] = to_array($value, true);
                }
            }
        }

        return $object;
    } elseif (is_object($object)) {
        if($object instanceof \erp\component\BaseModel){
            return to_array($object->getDataAttribute('data'));
        }

        $result = [];
        foreach ($object->getDataAttribute('data') as $field => $value) {
            $result[$field] = get_value($object, $field);
        }

        return $recursive ? to_array($result) : $result;
    } else {
        return [$object];
    }
}

/**
 * 处理金额（将分转元）
 * @param float|int $amount 金额（单位分）
 * @param int $format 是否格式化数字
 * @param int $round 保留位数
 * @return float
 */
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

/**
 * 将换行符号替换成br
 * @param $str 字符串
 * @param string $char 要替换的字符
 * @return string
 */
function nl_to_br($str, $char = "<br/>")
{
    $str = str_replace("\r\n", $char, $str);
    $str = str_replace("\n", $char, $str);
    return $str;
}

function br_to_nl($str) {
    return preg_replace("/<br\s*\/?>/i",PHP_EOL, $str);
}

/**
 * 生成UUID-唯一值
 * @return string
 */
function generate_uuid()
{
    return md5(uniqid(md5(microtime(true)), true));
}



/**
 * 将数字转为大写
 * @param int $number 数字
 * @param int $type 1.中文简体  2.中文繁体
 * @return mixed|string
 */
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

/**

 *数字金额转换成中文大写金额的函数

 *String Int $num 要转换的小写数字或小写字符串

 *return 大写字母

 *小数位为两位

 **/

function num_to_rmb($num, $currency_code = 'CNY') {
    $_titleArr = [
        'CNY' => '人民币',
        'USD' => '美元',
        'EUR' => '欧元',
    ];
    $_title = isset($_titleArr[$currency_code]) ? $_titleArr[$currency_code] : '';

    $c1 = "零壹贰叁肆伍陆柒捌玖";

    $c2 = "分角元拾佰仟万拾佰仟亿";

    //精确到分后面就不要了，所以只留两个小数位

    $num = round($num, 2);

    //将数字转化为整数

    $num = $num * 100;

    if (strlen($num) > 10) {
        return "金额太大，请检查";
    }

    $i = 0;

    $c = "";

    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num)-1, 1);
        } else {
            $n = $num % 10;
        }

        //每次将最后一位数字转化为中文

        $p1 = substr($c1, 3 * $n, 3);

        $p2 = substr($c2, 3 * $i, 3);

        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }

        $i = $i + 1;

        //去掉数字最后一位了
        $num = $num / 10;

        $num = (int)$num;

        //结束循环

        if ($num == 0) {
            break;
        }
    }

    $j = 0;

    $slen = strlen($c);

    while ($j < $slen) {

        //utf8一个汉字相当3个字符

        $m = substr($c, $j, 6);

        //处理数字中很多0的情况,每次循环去掉一个汉字“零”

        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {

            $left = substr($c, 0, $j);

            $right = substr($c, $j + 3);

            $c = $left . $right;

            $j = $j-3;

            $slen = $slen-3;

        }

        $j = $j + 3;

    }

    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c)-3, 3) == '零') {
        $c = substr($c, 0, strlen($c)-3);
    }

    //将处理的汉字加上“整”
    if (empty($c)) {
        return "零元整";
    } else {
        //分没有"整"
        return $_title . $c . (strpos($c, '分') ? '' : '整');
    }

}


/**
 * 将分制数字转为大写
 * @param int $number
 * @param string $currency_code
 * @return string
 */
function cents_amount_to_big($number = 0, $currency_code = '')
{
    $number = bcdiv($number,100,2);
    return amount_to_big($number,$currency_code);
}

/**
 * 金额小写转换为大写
 * @param float $amount 金额
 * @param string $currency_code
 * @return string
 */
function amount_to_big($amount, $currency_code = '')
{
    return num_to_rmb($amount,$currency_code);
}

/**
 * 将数字位置与相应金额单位对应
 * @param $list
 * @param $units
 * @return array
 */
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

/**
 * 获取会话请求中的查询语句
 */
function getQuerySql(){
    $info = trace();
    return $info['sql'];
}

/**
 * 检查身份证号码
 * @param string $id_card
 * @return bool|string
 */
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
        $Bit18 = $this->getVerifyBit($idcard);//算出第18位校验码
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

/**
 * 计算身份证校验码，根据国家标准GB 11643-1999
 * @param $idcard_base
 * @return bool|mixed
 */
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

/**
 * 获取身份证号码信息
 *
 * @param integer|string $card_no 身份证号码
 * @return array
 * 返回示例
 * ```
 * [
 *      'province_short_code' => 52, // 省份短代码
 *      'province_full_code' => 520000, // 省份完整代码
 *      'city_short_code' => 5221, // 城市短代码
 *      'city_full_code' => 522100, // 城市完整代码
 *      'age' => 18, // 年龄
 *      'gender' => 1, // 性别标识 1.男,2.女
 *      'gender_name' => '男', // 性别名称
 *  ];
 * ```
 */
function get_card_no_info($card_no)
{
    $province_short_code = substr($card_no, 0, 2); // 省份短代码
    $province_full_code = substr($card_no, 0, 2) . '0000'; // 省份完整代码
    $city_short_code = substr($card_no, 0, 4); // 城市短代码
    $city_full_code = substr($card_no, 0, 4) . '00'; // 城市完整代码
    $district_code = substr($card_no, 0, 6); // 县、区代码
    if (strlen($card_no) == 18) {
        $gender_code = substr($card_no, 16, 1);
        $birthday_timestamp = strtotime(substr($card_no, 6, 8));
    } elseif (strlen($card_no) == 15) {
        $gender_code = substr($card_no, 14, 1);
        $birthday_timestamp = strtotime('19' . substr($card_no, 6, 6));
    } else {
        return [];
    }
    if (($gender_code % 2) == 1) {
        $gender = 1;
        $gender_name = '男';
    } else {
        $gender = 2;
        $gender_name = '女';
    }
    list($birthday_y, $birthday_m, $birthday_d) = explode('-', date('Y-m-d', $birthday_timestamp));
    list($now_y, $now_m, $now_d) = explode('-', date('Y-m-d'));
    $age = $now_y - $birthday_y;
    if (($now_m . $now_d) < ($birthday_m . $birthday_d)) {
        $age -= 1;
    }

    //籍贯
//    $zoneList = \erp\data\zone\ZoneData::getInstance()->getDataMapByIds([$province_full_code, $city_full_code, $district_code]);
//    $province = !$zoneList[$province_full_code]['zone_name'] ? "" : $zoneList[$province_full_code]['zone_name'];
//    $city = !$zoneList[$city_full_code]['zone_name'] ? "" : '-'.$zoneList[$city_full_code]['zone_name'];
//    $district = !$zoneList[$district_code]['zone_name'] ? "" : '-'.$zoneList[$district_code]['zone_name'];

    $district = \erp\util\IdCardRegion::getInstance()->getNativePlace($district_code);

    return [
        'province_short_code' => $province_short_code,
        'province_full_code' => $province_full_code,
        'city_short_code' => $city_short_code,
        'city_full_code' => $city_full_code,
        'district_code' => $district_code,
        'age' => $age,
        'gender' => $gender,
        'gender_name' => $gender_name,
        'birthday' => $birthday_y. '-' .$birthday_m. '-' .$birthday_d,
//        'native_place' => $province.$city.$district
        'native_place' => $district
    ];
}

/**
 *  检查护照号码
 *      因私普通护照号码格式有：14/15+7位数,G+8位数；
 *      因公普通的是：P+7位数；公务的是：S+7位数 或者 S+8位数,
 *      外交护照：D开头
 * @param string $id_passport
 * @return bool|string
 */
function check_id_passport($id_passport)
{
    return preg_match("/^1[45][0-9]{7}$|^[G][0-9]{8}$|^[P][0-9]{7}$|^[S][0-9]{7,8}$|^[D][0-9]{7,8}$|^[\w][0-9a-zA-Z][0-9]{6,8}$/", $id_passport);
}

/**
 *  检查大陆居民往来台湾通行证号码
 *  T+8位数字
 * @param string $code
 * @return bool|string
 */
function check_futai_code($code)
{
    return preg_match("/^[T][0-9]{8}$/", $code);
}

/**
 *  检查大陆居民往来港澳通行证号码
 *  [C|W]+8位数字
 * @param string $code
 * @return bool|string
 */
function check_gangao_code($code)
{
    return preg_match("/^[C|W][0-9]{8}$/", $code);
}

/**
 * 获取日期对应的星期
 * @param mixed $time  时间戳
 * @return string
 */
function get_week_name($time){
    $weekArr = [ 1 => '周一', 2 => '周二', 3 => '周三', 4 => '周四', 5 => '周五', 6 => '周六', 7 => '周日'];
    $w = date('N', $time);
    return $weekArr[$w];
}

/**
 * 渲染输出Widget
 * @param string    $name Widget名称
 * @param array     $data 传入的参数
 * @return mixed
 */
function erp_widget($name, $data = [])
{
    return \think\Loader::action($name, ['param' => $data], 'widget');
}

/**
 * JS获取Widget内容
 * @param string    $name Widget名称
 * @param array     $data 传入的参数
 * @return mixed
 */
function erp_js_widget($name, $data = [])
{
    $html = erp_widget($name, $data);
    echo json_encode($html);
}

/**
 * Url生成
 * @param string        $url 路由地址
 * @param string|array  $vars 变量
 * @param bool|string   $suffix 生成的URL后缀
 * @param bool|string   $domain 域名
 * @return string
 */
function erp_url($url = '', $vars = '', $suffix = true, $domain = false){
    //添加当前模块名称
    $moduleName = request()->module();
    $url = ltrim(ltrim($url, '/'), '/');
    if (strpos($url, $moduleName) === false){
        $url = "{$moduleName}/{$url}";
    }
    $url = '/' . ltrim($url, '/');

    return url($url, $vars, $suffix, $domain);
}

function m_erp_url($url = '', $vars = '', $suffix = true, $domain = false){
    //添加当前模块名称
    $moduleName = request()->module();

    $vars = $vars ? $vars : [];
    $params = request()->param();
    if($params['sell_uid']){
        $vars['sell_uid'] = $params['sell_uid'];
    }
    if($params['buyer_uid']){
        $vars['buyer_uid'] = $params['buyer_uid'];
    }

    $url = ltrim(ltrim($url, '/'), '/');
    if (strpos($url, $moduleName) === false){
        $url = "{$moduleName}/{$url}";
    }
    $url = '/' . ltrim($url, '/');

    return url($url, $vars, $suffix, $domain);
}

/**
 * 缩略图路径
 * @param string $src
 * @param int $width
 * @param int $height
 * @return string
 */
function erp_thumb_url($src = '', $width = 0, $height = 0){
    $moduleName = request()->module();
    return "/{$moduleName}/image/getThumbImg?imgSrc={$src}&width={$width}&height={$height}";
}

/**
 * PC官网缩略图路径
 * @param string $src
 * @param int $width
 * @param int $height
 * @return string
 */
function erp_website_thumb_url($src = '', $width = 0, $height = 0){
    return "/image/getThumbImg?imgSrc={$src}&width={$width}&height={$height}";
}

/**
 * M官网缩略图路径
 * @param string $src
 * @param int $width
 * @param int $height
 * @return string
 */
function erp_m_thumb_url($src = '', $width = 0, $height = 0){
    return "/image/getThumbImg?imgSrc={$src}&width={$width}&height={$height}";
}

function erp_count($array,$number=0)
{
    return count($array)+$number;
}

/**
 * Undocumented function
 *
 * @return void
 */
function get_http_referer()
{
    $refer = request()->param('refer', '');
    $refer = empty($refer) ? $_SERVER["HTTP_REFERER"] : erp_url(urldecode($refer));
    $refer = empty($refer) ? erp_url('/') : $refer;

    //webshell refer check
    \erp\util\XssCleaner::doClean($refer);
    $vars = parse_url($refer);
    $token = \erp\context\Context::getInstance()->getToken();
    $domain = strtolower($vars['host']) . (!empty($vars['port']) ? ':' . $vars['port'] : '');

    if(
        (isset($vars['scheme']) && !in_array(strtolower($vars['scheme']),["http","https"]))
        || (isset($vars['host']) && !in_array($domain,[$token->mDomain,$token->erpDomain,$token->mDomain]))
        || \erp\util\Validate::is_xss($refer)
    )
    {
        $refer = erp_url('/');
    }

    return $refer;
}
/**
 * 构建url字符串
 * @param array $param
 * @return string
 */
function build_query($param = []){
    if (is_array($param)) {
        $param = array_map(function($in){
            return is_array($in) ? array_map('trim', $in) : trim($in);
        }, $param);
    }
    else {
        $param = [urlencode($param)];
    }

    return http_build_query($param);
}

/**
 * 处理url字符串
 * @param array $param
 * @return string
 */
function erp_build_query($param = []){
    if (!is_array($param)){
        $param = [$param];
    }

    foreach ($param as $key => $item){
        //$param[$key] = str_replace('+', "%20", $item);
    }

    return http_build_query($param);
}

/**
 * 处理URL trim
 * @param $param
 * @return string
 */
function erp_url_trim($param)
{
    $param = trim($param,'+');
    return trim($param);
}

function logs($varDump)
{
    //已追加的方式打开日志文件
    $fp_log = fopen(RUNTIME_PATH . "/temp/var_dump_log.txt", "a+");
    //首先加入时间
    $log_time          = date("Y-m-d H:i:s");
    $dump_log_time     = '[' . $log_time . ']' . "\t";
    $write_log_content = $dump_log_time;
    fwrite($fp_log, $write_log_content);
    //加入变量内容
    $write_log_content = var_export($varDump, true);
    fwrite($fp_log, $write_log_content);
    //加入换行
    fwrite($fp_log, "\n");
    fclose($fp_log);
}

/**
 * 根据某个相同key分组数组
 * @param $arr
 * @param string $key  字段
 * @return array
 */
function array_group_by($arr, $key)
{
    $grouped = [];
    foreach ($arr as $value) {
        $grouped[$value[$key]][] = $value;
    }

    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $key => $value) {
            $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
            $grouped[$key] = call_user_func_array('array_group_by', $parms);
        }
    }
    return to_array($grouped);
}


/**
 * 比较两个小数大小
 * @param float $float_num1
 * @param float $float_num2
 * @param int $round  精确位数
 * @return int 1.$float_num1>$float_num2  -1.$float_num1<$float_num2  0.$float_num1=$float_num2
 */
function compare_float($float_num1 = 0.00, $float_num2 = 0.00, $round = 2){
    $round = intval($round);
    $float_num1 = floatval($float_num1);
    $float_num1 = round($float_num1, $round);

    $float_num2 = floatval($float_num2);
    $float_num2 = round($float_num2, $round);

    if ($float_num1 > $float_num2){
        return 1;
    }elseif ($float_num1 < $float_num2){
        return -1;
    }else{
        return 0;
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
                $tmp = $row;
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

/**
 * 无限级分类：递归合并线路分类
 *
 * @param   array   $reg_list   线路分类
 * @param   int     $pid    父类ID
 * @return  array
 */
function recurrence($reg_list = array(), $pid = 0)
{
    $result = array();

    foreach ($reg_list as $v) {
        if ($v['parent_id'] == $pid) {
            $v['child'] = recurrence($reg_list, $v['id']);
            $result[] = $v;
        }
    }

    return $result;
}

if (!function_exists('show_qrcode')) {
    /**
     * 显示二维码图片
     * @param string $outfile 是否保存二维码
     * @param string $str     二维码内容
     * @param string $level   图片
     * @param int $size
     */
    function show_qrcode($str = '', $outfile = false, $level = 'Q', $size = 8) {
        require_once ROOT . 'bundle/util/phpqrcode.php';
        \QRcode::png($str, $outfile, $level, $size, 0);
    }
}

if (!function_exists('download_qrcode')) {
    /**
     * 下载二维码图片
     * @param string $str
     * @param $outfile
     * @param string $level
     * @param int $size
     */
    function download_qrcode($str = '', $outfile, $level = 'Q', $size = 8) {
        $fileurl = RUNTIME_PATH . $outfile;

        //保存二维码
        show_qrcode($str, $fileurl, $level, $size);

        //下载图片
        header("Cache-control: private");
        header("Content-type: image/png"); //设置要下载的文件类型
        header("Content-Length:" . filesize($fileurl)); //设置要下载文件的文件大小
        header("Content-Disposition: attachment; filename=" . urldecode($outfile)); //设置要下载文件的文件名

        readfile($fileurl);
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

        $token = \erp\context\Context::getInstance()->getToken();
        $erpModel = $token->currentErpModel;
        $upload_root_path = "/upload/{$token->erp_id}_{$erpModel['add_time']}/{$token->company_id}";
        $new_file = $upload_root_path . $path . "/" .date('Ymd',time()) . "/";

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


/**
 *  检查手机号码
 * @param   string  $mobile    手机号码
 * @return boolean
 */
function check_mobile($mobile = ''){
    return preg_match('/^1\d{10}$/', $mobile) === 1;
}

/**
 *  检查电话号码
 */
function check_telephone($telephone = ''){
	//return preg_match( "/^[\d]+[\d-]*[\d]$/", $telephone);
	return preg_match('/^(0?(([1-9]\d)|([3-9]\d{2}))-?)?\d{7,8}$/', $telephone);
}

/**
 * 将游客名字第二个字替换成 *
 *
 * @param   string  $member_name    游客姓名
 * @return string
 */
function replace_member_name($member_name = '') {
    $member_name = trim($member_name);

    if (mb_strlen($member_name) < 2) {
        return $member_name;
    }

    // 如果是手机号码，改成 xxx****xxxx 格式
    if (check_mobile($member_name)) {
        return substr_replace($member_name, '****', 3, 4);
    }

    return mb_substr($member_name, 0, 1) . '*' . mb_substr($member_name, -1);
}

if (!function_exists('createPoster')) {
    /**
     * 生成宣传海报
     * @param array  参数,包括图片和文字
     * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
     * @return [type] [description]
     */
    function createPoster($config = array(), $filename = "") {
        //如果要看报什么错，可以先注释调这个header
        if(empty($filename)) header("content-type: image/png");

        $imageDefault = array(
            'left'=>0,
            'top'=>0,
            'right'=>0,
            'bottom'=>0,
            'width'=>100,
            'height'=>100,
            'opacity'=>100
        );
        $textDefault = array(
            'text'=>'',
            'left'=>0,
            'top'=>0,
            'fontSize'=>32,       //字号
            'fontColor'=>'255,255,255', //字体颜色
            'angle'=>0,
        );

        $background = $config['background'];//海报最底层得背景
        //背景方法
        $backgroundInfo = getimagesize($background);
        $backgroundFun = 'imagecreatefrom' . image_type_to_extension($backgroundInfo[2], false);
        $background = $backgroundFun($background);
        $backgroundWidth = imagesx($background);  //背景宽度
        $backgroundHeight = imagesy($background);  //背景高度
        $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
        $color = imagecolorallocate($imageRes, 0, 0, 0);
        imagefill($imageRes, 0, 0, $color);
        //imageColorTransparent($imageRes, $color);  //颜色透明
        imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));

        //处理图片
        if (!empty($config['image'])) {
            foreach ($config['image'] as $key => $val) {
                $val = array_merge($imageDefault, $val);
                $info = getimagesize($val['url']);
                $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
                if ($val['stream']) {   //如果传的是字符串图像流
                    $info = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                }
                $res = $function($val['url']);
                $resWidth = $info[0];
                $resHeight = $info[1];
                //建立画板 ，缩放图片至指定尺寸
                $canvas = imagecreatetruecolor($val['width'], $val['height']);
                imagefill($canvas, 0, 0, $color);
                //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
                //放置图像
                imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
            }
        }

        //处理文字
        if (!empty($config['text'])) {
            foreach ($config['text'] as $key => $val) {
                $val = array_merge($textDefault,$val);
                list($R,$G,$B) = explode(',', $val['fontColor']);
                $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
                imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'] ? $val['fontPath'] : "simhei.ttf",$val['text']);
            }
        }

        //生成图片
        if (!empty($filename)) {
            $res = imagejpeg ($imageRes, $filename,90); //保存到本地
            imagedestroy($imageRes);
            if(!$res) return false;
            return $filename;
        }
        //在浏览器上显示
        else {
            imagejpeg ($imageRes);
            imagedestroy($imageRes);
        }
    }

    /**
     * Excel转数组
     * @param string $file Excel文件
     * @param array $replace_keys 键替换
     * @param int $key_row_no 键名行号
     * @param array $filter_row_nos 过滤行号列表
     * @return array
     * @throws PHPExcel_Reader_Exception
     */
    function excel_to_array($file, $replace_keys, $key_row_no = 1, $filter_row_nos = [0]) {
        $list = [];
        if (file_exists($file)) {
            require_once ROOT . 'vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
            require_once ROOT . 'vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
            require_once ROOT . 'vendor/phpoffice/phpexcel/Classes/PHPExcel/Reader/Excel5.php';

            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            if(!$objReader->canRead($file)){
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
                if(!$objReader->canRead($file)){
                    $objReader = PHPExcel_IOFactory::createReader('CSV');
                    $objReader->setInputEncoding('GBK');
                    $objReader->setDelimiter(',');
                    if(!$objReader->canRead($file)){
                        exit('无法读取');
                    }
                }
            }
            $objPHPExcel = $objReader->load($file);

            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                $rows = $worksheet->toArray();
                $keys = [];
                foreach ($rows as $i => $row) {
                    if (!empty($replace_keys)) {
                        if ($i == $key_row_no) {
                            foreach ($row as $r_k => $name) {
                                if (isset($replace_keys[$name])) {
                                    $row[$r_k] = $replace_keys[$name];
                                }
                            }
                            $keys = $row;
                        } elseif (!in_array($i, $filter_row_nos) && array_filter($row)) {
                            $item = array_filter(array_combine($keys, $row));
                            // 添加无键的值
                            foreach (array_diff($replace_keys, array_keys($item)) as $add_name) {
                                $item[$add_name] = '';
                            }
                            $list[] = $item;
                        }
                    }
                    //无需键替换
                    else {
                        if ($i <= $key_row_no || empty(array_filter($row))) {
                            continue;
                        }

                        $list[] = $row;
                    }
                }
            }
        }

        return $list;
    }


    /**
     * 导出word文件
     * @param string $html  文件HTML内容
     * @param string $absolutePath 项目的绝对路径。如果HTML内容里的图片路径为相对路径，那么就需要填写这个参数，来让该函数自动填补成绝对路径。这个参数最后需要以'/'结束
     * @param string $file_name  文件名称
     */
    function export_word_file($html = '', $absolutePath = '', $file_name = '', $use_html=false)
    {
        if (empty($html)) {
            return false;
        }

//        $absolutePath = rtrim($absolutePath, '/') . '/';

        if (empty($file_name)) {
            $file_name = date('YmdHis');
        }

        require_once ROOT . 'bundle/util/MhtFileMaker.php';

        //通用头
        header('Content-type: application/octet-stream');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Transfer-Encoding: binary");

        $file_name = $file_name.".doc";
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua) || preg_match("/Trident/", $ua)) {
            $encoded_filename = urlencode($file_name);
            $encoded_filename = str_replace("+", "%20", $encoded_filename);
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } elseif (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $file_name . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
        }

        //if ($use_html) {
            echo $html;
            exit;
        //}

        //$maker = new MhtFileMaker();
        //echo $maker->getWordDocument($html, $absolutePath);

        //exit();
    }

    /**
     *
     * 二维数组排序
     *
     * @param $multi_array 数组
     * @param $sort_key 排序字段
     * @param int $sort 升序 降序
     * @return bool
     */
    function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){
        if(is_array($multi_array)){
            foreach ($multi_array as $row_array){
                if(is_array($row_array)){
                    $key_array[] = $row_array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
//        var_dump($key_array);
//       array_multisort($key_array,$sort,$multi_array);


        return $multi_array;
    }

    /**
     * 验证数字
     * @param string $num
     * @return false|int
     */
    function check_num($num='')
    {
        return preg_match('/^\d{5,}$/', $num);
    }

    /**
     * 验证正整数
     * @param string $num
     * @return false|int
     */
    function check_int_num($num='')
    {
        return preg_match('/^([0-9]*)$/', $num);
    }

    /**
     * 验证链接
     * @param $url
     * @param $withPro
     * @return false|int
     */
    function check_url($url,$withPro = false)
    {
        if($withPro)
        {
            return preg_match('/(\b(https|http|ftp):\/\/)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $url);
        }else{
            return preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $url);
        }
    }

    /**验证字母+数字
     * @param string $str
     * @return false|int
     */
    function check_c_start_num($str='')
    {
        return preg_match('/^[a-zA-Z0-9]{4,40}$/', $str);
    }

    /**验证税号
     * @param string $str
     * @return false|int
     */
    function check_tax_id($str=''){
//        return preg_match('/^(?=.*\d)(?=.*[a-zA-Z])(?=.*[a-zA-Z]).{10,40}$/', $str);
        return preg_match('/^[a-zA-Z0-9]{10,30}$/', $str);
    }

    /**
     *  检查日期 xxxx-xx-xx
     */
    function check_date($date = ''){
        return preg_match('/^[\d]{4}\-[\d]{1,2}-[\d]{1,2}$/', $date);
    }

}

function render_date($date = '')
{
    if(empty($date) || stripos($date,"0000-00-00") !==false || stripos($date,"0100-01-01") !==false || stripos($date,"1970-01-01")!==false ){
        return '';
    }
    return $date;
}

/**
 * 创建父节点树形数组
 * 参数
 * $items 数组，邻接列表方式组织的数据
 * $id 数组中作为主键的下标或关联键名
 * $pid 数组中作为父键的下标或关联键名
 * 返回 多维数组
 **/
function getTree($items,$id='id',$pid='topid',$son = 'children'){
    $tree = array(); //格式化的树
    $tmpMap = array();  //临时扁平数据

    foreach ($items as $item) {
        $tmpMap[$item[$id]] = $item;
    }

    foreach ($items as $item) {
        if (isset($tmpMap[$item[$pid]])) {
            $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
        } else {
            $tree[] = &$tmpMap[$item[$id]];
        }
    }

    return $tree;

}

/**
 * 查询2个日期之间的每一天
 *
 * @param $sdate
 * @param $edate
 * @return array
 */
function getDates($sdate, $edate){
    $dates = [];
    $dt_start = strtotime($sdate);
    $dt_end = strtotime($edate);
    while ($dt_start<=$dt_end){
        $dates[] = date('Y-m-d', $dt_start);
        $dt_start = strtotime('+1 day',$dt_start);

    }
    return $dates;
}

/**
 * 获取车型座位图
 * @param int $cols 列
 * @param int $rows 行
 * @param string $default 默认位置
 * @param string $selected 已选位置
 * @param string $disabled 不可选位置
 * @param int $seat_id
 * @return string
 */
function get_car_seat_table($cols, $rows, $default, $selected = '', $disabled = '', $seat_id=0){
    $default = json_decode($default, true);
    $selected = is_array($selected) ? $selected  : (!empty($selected) ? json_decode($selected, true) : '');
    $disabled = !empty($disabled) ? json_decode($disabled, true) : '';
    $table = '<table class="layui-table seat-table" width="100%" data-toggle="'.count(array_filter($default)).'"><tbody>';
    $pos = 1;

    for ($i=1; $i<= $rows; $i++) {
        $table .= "<tr>";
        for($j=1; $j<= $cols; $j++){
            $pos = ($i-1) * $cols + $j;
            $table .= (!empty($disabled) && in_array($default[$pos],$disabled)) ? '<td class="disabled"><input type="checkbox" lay-filter="seat-int" title="'.($default[$pos] < 10 ? '0'.$default[$pos] : $default[$pos]).'" value="'.$default[$pos].'" disabled></td>' :
                    (!empty($default[$pos]) ? '<td><input type="checkbox" lay-filter="seat-int" '.(!empty($selected) && in_array($default[$pos],$selected) ? 'checked' : '').
                    ' name="seat_table_id['.$seat_id.'][]" title="'.($default[$pos] < 10 ? '0'.$default[$pos] : $default[$pos]).'" value="'. $default[$pos] .'"></td>' : '<td></td>');
        }
        $table .= "</tr>";
    }

    $table .= "</tbody></table>";
    return $table;

}

/**
 * 获取通用车型座位图
 * @param int $total_num 数量
 * @param string $selected 已选位置
 * @param string $disabled 不可选位置
 * @param int $seat_id
 * @return string
 */
function get_general_car_seat_table($total_num, $selected = '', $disabled = '', $seat_id=0){
    $cols = 5;
    $rows = ceil($total_num/($cols-1));
    $selected = is_array($selected) ? $selected  : (!empty($selected) ? json_decode($selected, true) : '');
    $disabled = !empty($disabled) ? json_decode($disabled, true) : '';
    $table = '<table class="layui-table seat-table" width="100%" data-toggle="'.$total_num.'"><tbody>';
    $pos = 1;

    for ($i=1; $i<= $rows; $i++) {
        $table .= "<tr>";
        for($j=1; $j<= $cols; $j++){
            $pos = ($i-1) * ($cols-1) + ($j>=3 ? $j-1 : $j);
            if ($pos <= $total_num) {
                $table .= (!empty($disabled) && in_array($pos,$disabled) && $j != 3) ? '<td class="disabled"><input type="checkbox" lay-filter="seat-int" title="' . ($pos < 10 ? '0' . $pos : $pos) . '" value="' . $pos . '" disabled></td>' :
                    (($j != 3) ? '<td><input type="checkbox" lay-filter="seat-int" ' . (!empty($selected) && in_array($pos,$selected) ? 'checked' : '') .
                        ' name="seat_table_id[' . $seat_id . '][]" title="' . ($pos < 10 ? '0' . $pos : $pos) . '" value="' . $pos . '"></td>' : '<td class="ui-w50"></td>');
            }
        }
        $table .= "</tr>";
    }

    $table .= "</tbody></table>";
    return $table;

}

/**
 * 过滤为空参数
 * @param array $params
 * @return array
 */
function check_params(array $params){
    if(empty($params)){
        return [];
    }
    foreach($params as &$v){
        if($v === ''){
            unset($v);
        }
    }
    return $params;
}

/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function diffBetweenTwoDays ($day1, $day2)
{
    $second1 = $day1;
    $second2 = $day2;
    if ($second1 < $second2) {
        $tmp = $second2;
        $second2 = $second1;
        $second1 = $tmp;
    }
    return ($second1 - $second2) / 86400;
}

/**
 * SQL指令安全过滤
 * @param string $str
 * @return string
 */
function quote($str = ''){
    $str = trim($str);
    return \erp\data\ErpDb::quote($str);
}

/**
 * 防止xss注入
 * @param string $str
 * @return mixed
 */
function do_xss_clean($str = ''){
    if(empty($str)){
        return $str;
    }
    $params =  \erp\util\XssCleaner::doClean($str);

    if(defined("BIND_MODULE") && in_array(BIND_MODULE,['www','website']))
    {
        $request = request();
        $controller = $request->controller();
        $action = $request->action();

        $url = '/'.strtolower(str_replace(".","/",$controller)).'/'.$action;
        $request_uri = $url. ($params ? '?'.http_build_query($params) : '' );
        $request->server(["REQUEST_URI"=>$request_uri]);
        $request->get($params);
    }
    return $params;
}

/**
 * 过滤emoji图标
 * @param $source
 * @return string
 */
function str_replaceEmoji($source)
{
    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $source);
    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);
    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);
    // Match Miscellaneous Symbols
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '', $clean_text);
    // Match Dingbats
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    return $clean_text;
}

function redirectThumbImg($imgSrc,$width, $height)
{
    $imgSrc = parseThumbImgPath($imgSrc,$width,$height);

    header("Location:{$imgSrc}");
    exit;
}

function parseThumbImgPath($imgSrc,$width, $height)
{
    $ext = strtolower(pathinfo($imgSrc, PATHINFO_EXTENSION));
    if(!in_array($ext,['gif','png','jpg','jpeg'])){
        return '/favicon.ico';
    }

    if (mb_substr($imgSrc, 0, 4) == 'http'){
        return $imgSrc;
    }

    $token = \erp\context\Context::getInstance()->getToken();
    if (!$token->webShowThumbImg){
        return $imgSrc;
    }

    $width  = intval($width);  //缩略图宽度
    $height = intval($height);  //缩略图高度
    $thumbSrc = get_thumb_src($imgSrc, $width, $height);
    return $thumbSrc;
}

/**
 * 获取图片缩略图路径
 * @param $imgSrc  原图片路径
 * @param $width  缩略图宽度
 * @param $height  缩略图高度
 * @return string
 */
function get_thumb_src($imgSrc, $width, $height){
    $imgSrc = trim($imgSrc);
    $width  = intval($width);
    $height = intval($height);
    if (empty($imgSrc) || empty($width) || empty($height)){
        return $imgSrc;
    }

    $rootPath = ROOT_PATH . 'webapp';
    $originalImage = $rootPath . $imgSrc;
    if (!file_exists($originalImage)){
        return $imgSrc;
    }

    $ext = substr($imgSrc, strrpos($imgSrc, '.'));
    $srcLeft = rtrim($imgSrc, $ext);
    $ext = ltrim($ext, '.');
    if(!in_array($ext, ['jpg', 'gif', 'png', 'jpeg'])){
        return $imgSrc;
    }

    $thumbDir = '/thumb/' . ltrim(substr($imgSrc, 0, strrpos($srcLeft, '/')), '/upload');
    if (!is_dir($rootPath . $thumbDir)){
        @mkdir($rootPath . $thumbDir, 755, true);
    }

    $thumbSrc = '/thumb/' . ltrim($srcLeft, '/upload') . "_{$width}_{$height}.{$ext}";
    if (file_exists($rootPath . $thumbSrc)){  //已存在缩略图
        return $thumbSrc;
    }

    //生成缩略图
    $image = \think\Image::open($originalImage);
    $image->thumb($width, $height, 2)->save($rootPath . $thumbSrc);

    return $thumbSrc;
}

/**
 * 获取当前请求（不带请求参数）
 * @return string
 */
function getUrlWithoutParam(){
    $dispatchInfo = request()->dispatch();
    $dispatchModule = $dispatchInfo['module'];
    $module      = $dispatchModule[0];
    $action      = $dispatchModule[2];
    $controllers = explode('.', $dispatchModule[1]);
    $urls        = [];
    $urls[]      = strtolower($module);
    foreach ($controllers as $controller){
        $urls[] = strtolower($controller);
    }

    $urls[] = strtolower($action);
    $url    = implode('/', $urls);
    return $url;
}

/**
 * 验证操作权限
 * @param $url
 * @return bool
 */
function checkPermission($url){
    if(empty($url)){
        return true;
    }

    $token = \erp\context\Context::getInstance()->getToken();
    $url = strtolower(ltrim($url, '/'));
    $moduleName = strtolower(request()->module()) . '/';
    if (strrpos($url, $moduleName) === 0){
        $url = substr($url, strlen($moduleName));
    }

    $url   = $moduleName . $url;
    $allFunctionPermissions  = $token->all_function_permission;
    $userFunctionPermissions = $token->user_function_permission;

    if (!in_array($url, $userFunctionPermissions) && in_array($url, $allFunctionPermissions)){
        return false;
    }

    return true;
}

/**
 * @param string $str 字符串
 * @return string
 */
function replace_char($str)
{
    $str = str_replace("", '', $str);
    $str = str_replace('&nbsp;',' ',$str);
    $str = str_replace('&','&amp;',$str);
    $str = str_replace("<", "&lt;", $str);
    $str = str_replace(">", "&gt;", $str);
    $str = str_replace("\r\n", '<w:br/>', $str);
    $str = str_replace("\n", '<w:br/>', $str);
    $str = trim($str,'<w:br/>');
    return $str;
}

function gbk_to_utf8($str){
    $charset = mb_detect_encoding($str,array('UTF-8','GBK','GB2312'));
    $charset = strtolower($charset);
    if('cp936' == $charset){
        $charset='gbk';
    }
    if("utf-8" != $charset){
        $str = iconv($charset,"UTF-8//IGNORE",$str);
    }
    return $str;
}

/**
 * 导出word 自定义内容
 * @param $html
 * @return string
 */
function clean_html_label($html) {
    $html = preg_replace("/<p.*?>/is","", $html);
    $labels = array(
        '/<\/p>/is',
        '/<\/li>/is',
        '/<\/h1>/is',
        '/<\/h2>/is',
        '/<\/h3>/is',
        '/<\/h4>/is',
        '/<\/h5>/is',
        '/<\/h6>/is',
        '/<\/div>/is',
    );
    $html = preg_replace($labels,'&93806256CEA94585B35091E9B4F5B4C1',$html);
    $html = strip_tags($html,"<br/>");
    $html = str_replace('&93806256CEA94585B35091E9B4F5B4C1','<br/>',$html);
    $html = str_replace('<br/>','<w:br/>',$html);
    $html = str_replace('&nbsp;',' ',$html);
    $html = str_replace('&','&amp;',$html);
    $html = preg_replace('/[\x00-\x08\x0b-\x0c\x0e-\x1f\x7f]/', '', $html);
    $html = trim($html,'<w:br/>');

  return $html;
}

function clean_url_javascript($url){
    if(!is_array($url))
    {
        $preg = "/<script[\s\S]*?<\/script>/i";
        $url = preg_replace($preg, '', $url);

        $chars = array(
            '/\'/is',
            '/\"/is',
            '/</is',
            '/>/is',
        );

        $url = preg_replace($chars, '', $url);

    }else{
        $url = array_map('clean_url_javascript', $url);
    }
    return $url;
}

/**
 * 统计游客数量
 * @param $touristList
 * @return string
 */
function convert_tourist($touristList) {
    if (empty($touristList)) {
        return '';
    }

    $totalDesc = [];
    $totalGroupType = \erp\type\dict\TouristType::getGroupByTouristType($touristList);
    if (!empty($totalGroupType)) {
        foreach ($totalGroupType as $val) {
            $totalDesc[] = $val['num'] . (isset($val['type_name']) ? $val['type_name'] : '');
        }
    }

    return !empty($totalDesc) ? implode('、', $totalDesc) : '';
}

/**
 * 判断是否微信访问
 * @return bool
 */
function is_wx_visit()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    } else {
        return false;
    }
}

/**
 * 调用新浪接口将长链接转为短链接
 * @param  string        $source    申请应用的AppKey
 * @param  array|string  $url_long  长链接，支持多个转换（需要先执行urlencode)
 * @return array
 */
if (!function_exists("short_url")) {
    function short_url($url_long){
        $original_url = trim($url_long);
        return $original_url;
        // 参数检查
        if (empty($url_long)) {
            return false;
        }

        // 参数处理，字符串转为数组
        if (!is_array($url_long)) {
            $url_long = array($url_long);
        }

        // 拼接url_long参数请求格式
        $url_param = array_map(function ($value) {
            return '&url_long=' . urlencode($value);
        }, $url_long);

        $url_param = implode('', $url_param);

        // 新浪生成短链接接口
        $api = 'http://api.t.sina.com.cn/short_url/shorten.json';

        // 请求url
        $request_url = sprintf($api . '?source=3574597439%s', $url_param);

        // 执行请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $request_url);
        $data = curl_exec($ch);
        if ($error = curl_errno($ch)) {
            return false;
        }
        curl_close($ch);

        $result = json_decode($data, true);

        return $result[0]['url_short'] ? $result[0]['url_short'] : $original_url;
    }
}

/**
 * 用于旅投票严格验证手机--旅投任务添加
 * 截止至201807工信部已公布有效号段
 * 移动号段：134 135 136 137 138 139 147(上网卡) 148 150 151 152 157 158 159 172 178 182 183 184 187 188 198
 * 联通号段：130 131 132 145(上网卡) 146(4G) 155 156 166 171 175 176 185 186
 * 电信号段：133 149 153 173 174 177(4G) 180 181 189 199
 * 卫星通信：1349
 * 虚拟运营商：170
 * 验证是否是手机号
 * @param $mobile
 * @return bool
 */
function isMobileNo($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,6,7,8,9]{1}\d{8}$|^15[^4]{1}\d{8}$|^16[6]{1}\d{8}$|^17[^9]{1}\d{8}$|^18[\d]{9}$|^19[8,9]{1}\d{8}$#', $mobile) ? true : false;

}

function js_notice($mess = '', $forward = '')
{
    $mess = empty($mess) ? '' : 'alert("' . htmlspecialchars($mess, ENT_COMPAT, 'utf-8') . '");';
    header('Content-type: text/html; charset=utf-8');
    if($forward == 'reload'){
        $forward = "window.top.location.reload();";
    }else{
        $forward = empty($forward) ? 'history.back();' : ('location.href="' . $forward . '"');
    }
    echo '<script>' . $mess . $forward . '</script>';
    exit;
}

function replace_location_query($key,$val)
{
    $url_params = parse_url($_SERVER['REQUEST_URI']);
    $query = $url_params['query'];
    if($query){
        parse_str($query,$result);
        $result[$key] = $val;
        $query = http_build_query($result);
    }

    return $url_params['path'].($query?"?{$query}":"");
}

function generalSimpleCaptcha()
{
    $config = [
        'codeSet'  => '123456789',
        // 验证码字体大小
        'fontSize'    => 25,
        // 验证码位数
        'length'      => 4,
        // 验证码杂点
        'useNoise'    => false,
        // 验证码字体大小(px)
        'useCurve'    => false,
        //过期时间15分钟
        'expire'      => 900,
        // 验证码位数
        'fontttf'  => '5.ttf',
    ];
    $captcha = new \think\captcha\Captcha($config);

    return $captcha;
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

    /**
     * 获取产品分类名称
     * $regionIds 产品分类id数组
     * $idMapName 分类id为下标的分类数组
     */
    if(!function_exists('getRegionalName')){
        function getRegionalName($regionIds, $idMapName){
            $regionalName = [];
            array_walk($regionIds, function ($item, $index)use(&$regionalName, $idMapName){
                if(isset($idMapName[$item])){
                    $regionalName[] = $idMapName[$item]['regional_name'];
                }
            });
            return implode(' - ', $regionalName);
        }
    }

    /**
     * 判断字符串是否为 Json 格式
     *
     * @param  string     $data  Json 字符串
     * @param  bool       $assoc 是否返回关联数组。默认返回对象
     *
     * @return bool|array 成功返回转换后的对象或数组，失败返回 false
     */
    if(!function_exists('isJson')) {
        function isJson($data = '', $assoc = false)
        {
            $data = json_decode($data, $assoc);
            if ($data && (is_object($data)) || (is_array($data) && !empty(current($data)))) {
                return true;
            }
            return false;
        }
    }

    /**
     * 文件转换成base64格式
     */
    if(!function_exists('fileToBase64')) {
        function fileToBase64($file)
        {
            $base64file = '';
            if (file_exists($file)) {
                $fInfo    = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($fInfo, $file);
                finfo_close($fInfo);
                $base64data = base64_encode(file_get_contents($file));
                $base64file = 'data:' . $mimeType . ';base64,' . $base64data;
            }
            return $base64file;
        }
    }

    /**
     * 多维数组 差集
     * e.g
     * $array1 = array(1,2,3,array(1,2,array(1)))
     * $array2 = array(1,2,4,array(1,2,3))
     * array_diff_assoc_recursive($array1,$array2)
     *
     * array( 2 => 3, 3 => array(2=>array(0 => 1)))
     */
    if (!function_exists('array_diff_assoc_recursive')) {
        function array_diff_assoc_recursive($array1, $array2)
        {
            $diffArray = array();
            foreach ($array1 as $key => $value) {
                //推断数组每一个元素是否是数组
                if (is_array($value)) {
                    //推断第二个数组是否存在key
                    if (!isset($array2[$key])) {
                        $diffArray[$key] = $value;
                        //推断第二个数组key是否是一个数组
                    } elseif (!is_array($array2[$key])) {
                        $diffArray[$key] = $value;
                    } else {
                        $diff = array_diff_assoc_recursive($value, $array2[$key]);
                        if ($diff != false) {
                            $diffArray[$key] = $diff;
                        }
                    }
                } elseif (!array_key_exists($key, $array2) || $value !== $array2[$key]) {
                    $diffArray[$key] = $value;
                }
            }
            return $diffArray;
        }
    }


}
