<?php
/**
 * @copyright Copyright (c) 2015 Xiamen Xinxin Information Technologies, Inc.
 */
namespace Rpc\Com;

use Dragonfly\Rpc\Rpc;
/**
 * Account - 账号相关
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 */
class Account {
    /**
     * 注册来源
     *
     * @var array
     */
    protected static $regComes = array(
        0  => '未知',
        1  => '下订单',
        2  => 'WEB注册',
        3  => 'APP注册',
        4  => '手机版',
        5  => '合作账号',
        6  => '定制游',
        7  => '接口下单',
        8  => '宝中接口下单',
        51 => 'm合作',
        52 => 'app合作',
        61 => 'm定制游',
    );

    /**
     * 注册来源
     *
     * @return array
     *         0    未知
     *         1    下订单
     *         2    WEB注册
     *         3    APP注册
     *         4    手机版
     *         5    合作账号
     *         6    定制游
     *         7    接口下单
     *         8    宝中接口下单
     *         51   m合作
     *         52   app合作
     *         62   m定制游
     */
    public static function regComes() {
        return self::$regComes;
    }

    /**
     * 检查手机号是否存在
     *
     * 使用示例：
     * ```php
     * $acc = new Cncn\Account();
     * if ($acc->mobileExists($mobile)) {
     *     // 此手机号已被注册
     * }
     * ```
     *
     * @param  string $mobile 要检查的手机号
     * @return int            不存在返回0，存在返回 uid
     */
    public function mobileExists($mobile) {
        
        $rpc = new Rpc();
        $res = $rpc->call('account', 'mobileExists', [$mobile]);

        return $res;
    }

    /**
     * 检查已验证的手机号是否存在
     *
     * @param  string $mobile  要检查的手机号
     * @return int    不存在返回0,存在返回uid
     */
    public function verifiedMobileExists($mobile) {
        if (preg_match('/^1[34578]\d{9}$/', $mobile) === 0 ) {
            return 0;
        }
        
        $rpc = new Rpc();
        $res = $rpc->call('account', 'verifiedMobileExists', [$mobile]);

        return $res;
    }


    /**
     * 检查电子邮件是否存在
     *
     * 使用示例：
     * ```php
     * $acc = new Cncn\Account();
     * if ($acc->emailExists($email)) {
     *     // 此邮箱已被注册，请重新选择一个邮箱！
     * }
     * ```
     *
     * @param  string $email 要检查的电子邮件
     * @return int           不存在返回0，存在返回 uid
     */
    public function emailExists($email) {
        
        $rpc = new Rpc();
        $res = $rpc->call('account', 'emailExists', [$email]);
        return $res;
    }

    /**
     * 注册新用户
     *
     * 对应原来的reg_cncn_user函数，去掉非公用部分，
     * 例如插入 member_lxs 表，不是每个注册接口都需要用到的
     *
     * 使用示例：
     * ```php
     * $acc = new Cncn\Account();
     *
     * $acc->reg(array(
     *     'mobiletel'      => $mobiletel,
     *     'password'       => $password,
     *     'come_id'        => 4,
     * ));  // 手机号注册方式
     *
     * $acc->reg(array(
     *     'user_email'     => $email,
     *     'password'       => $password,
     *     'come_id'        => 2,
     * ));  // 电子邮件注册方式
     * ```
     *
     * @param array $info    包含注册信息的数组
     *                       user_email          电子邮件，手机号、电子邮件两者需要填写一个，邮箱注册方式填写邮箱
     *                       mobiletel           手机号，手机号、电子邮件两者需要填写一个，手机注册方式填写手机号
     *                       password            必选，密码
     *                       come_id             可选，来源ID，详见 Cncn\Account::regComes() 方法，默认值为 2
     *                       contact_name        可选，姓名
     * @param array $options 可选，选项
     *                       verified 是否该账号已经认证，默认值为 true
     * @return int 负数为错误代码，正数为该注册账号的 uid
     */
    public function reg(array $info, array $options = array()) {
        // --- 参数检查 begin ---------------------------------------------------
        // 确保不包含不支持的参数
        $params = array('user_email', 'mobiletel', 'password', 'come_id',
                        'contact_name', 'zone_id', 'sex');  // 支持的参数
        $info   = array_intersect_key($info, array_flip($params));

        // 必选参数检查
        if (empty($info['user_email']) && empty($info['mobiletel'])) {
            trigger_error(__METHOD__ . '() expects array key user_email or mobiletel', E_USER_WARNING);
            return 0;
        }
        if (empty($info['password'])) {
            trigger_error(__METHOD__ . '() expects array key password', E_USER_WARNING);
            return 0;
        }

        // 可选参数检查
        if (!isset($info['come_id'])) {
            $info['come_id'] = 2;
        }
        // --- 参数检查 end -----------------------------------------------------

        $info['dateline'] = time();  // 注册时间
        $regType         = !empty($info['mobiletel']) ? 1 : 2;
        if ($regType === 1) {  // 手机注册方式
            $info['username'] = $info['user_email'] = $info['mobiletel'];
        } else {  // 邮箱注册方式
            $info['username']  = $info['user_email'];
            $info['mobiletel'] = '';
        }
        $info['username'] = substr($info['username'], 0, 15);

        if (!isset($options['verified']) || $options['verified']) {  // 已认证
            $flag        = $regType === 1 ? 'mobile_flag' : 'email_flag';
            $info[$flag] = 1;
        }

        $rpc = new Rpc();
        $res = $rpc->call('account', 'reg', [$info, $options]);

        return $res;
    }

    /**
     * 获取注册错误代码对应的错误信息
     *
     * @param  int $errno 错误代码
     * @return string     错误信息
     *                    -1   用户名不合法
     *                    -2   用户名包含不允许注册的词语
     *                    -3   用户名已经存在
     *                    -4   Email 格式有误
     *                    -5   Email 不允许注册
     *                    -6   该 Email 已经被注册
     *                    其他非正数 未知错误
     */
    public function regError($errno) {
        if ($errno > 0) {
            return '';
        }

        switch ($errno) {
            case -1:
                $errmsg = '用户名不合法';
                break;
            case -2:
                $errmsg = '用户名包含不允许注册的词语';
                break;
            case -3:
                $errmsg = '用户名已经存在';
                break;
            case -4:
                $errmsg = 'Email 格式有误';
                break;
            case -5:
                $errmsg = 'Email 不允许注册';
                break;
            case -6:
                $errmsg = '该 Email 已经被注册';
                break;
            default:
                $errmsg = '未知错误';
                break;
        }

        return $errmsg;
    }

    /**
     * 设置该账号为已认证状态
     *
     * 使用示例：
     * ```php
     * $acc = new Cncn\Account();
     * $acc->setVerify($uid, 'mobile_flag');
     * // UPDATE member_temp SET mobile_flag=1 WHERE uid='$uid'
     * ```
     *
     * @param int  $uid  UID
     * @param bool $flag 可选，要设置的 flag: email_flag 或 mobile_flag
     * @return bool 设置成功返回 true，失败返回 false
     */
    public function setVerify($uid, $flag = null) {
        
        $rpc = new Rpc();
        $res = $rpc->call('account', 'setVerify', [$uid, $flag]);
        return $res;
    }

    /**
     * 给个人会员用户增减积分
     *
     * @param int    $uid        用户uid
     * @param int    $ad_mark    增减的分数
     * @param int    $typeid     1订单 2点评 3注册 4赠送 5退款 6抽奖 7兑换
     * @param int    $typeid2    1网店线路 2网店服务 3定制 4商城线路 5商城服务
     * @param int    $product_id 产品id
     * @param string $source     来源/用途
     * @return bool   成功返回 true，失败返回 false
     */
    public function memberAddmark($uid, $ad_mark, $typeid = 1, $typeid2 = 0,
                                   $product_id = 0, $source = '') {
        if (!is_numeric($uid) || !is_numeric($ad_mark)) {
            return false;
        }

        $rpc = new Rpc();
        $res = $rpc->call('account', 'memberAddmark', [$uid, $ad_mark, $typeid, $typeid2, $product_id, $source]);
        return $res;
    }

    /**
     * 登录账号
     *
     * @param  string $username       帐号名
     * @param  string $password       密码
     * @param  int    $keeploginTime 保持登录的时间，整数代表设置的保持登录时间的秒数，
     *                                -1 代表不设置保持登录的cookie(api等需要用到)
     * @param  bool   $saveSession   是否保存登录 session 信息
     * @return int|array 负数为错误代码，数组为该登录账号的信息
     *                                uid 用户id
     *                                username 用户名
     *                                email 邮箱
     */
    public function login($username, $password, $keeploginTime = 0, $saveSession = true) {
        if ($username == '' || $password == '') {
            return -11;
        }

        $rpc = new Rpc();
        $res = $rpc->call('account', 'login', [$username, $password, $keeploginTime, $saveSession, $_SERVER['REMOTE_ADDR']]);
        return $res;
    }

    /**
     * 获取登录错误代码对应的错误信息
     *
     * @param  int $errno 错误代码
     * @return string     错误信息
     *                    -11  缺少参数
     *                    -12  错误登录次数超过限制
     *                    -1   用户名不存在
     *                    -2   密码不对
     *                    -3   账号被禁用
     *                    其他非正数 未知错误
     */
    public function loginError($errno) {
        if ($errno > 0) {
            return '';
        }

        switch ($errno) {
            case -11:
                $errmsg = '缺少参数';
                break;
            case -12:
                $errmsg = '错误登录次数超过限制';
                break;
            case -1:
                $errmsg = '用户名不存在';
                break;
            case -2:
                $errmsg = '密码不对';
                break;
            case -3:
                $errmsg = '账号被禁用';
                break;
            default:
                $errmsg = '未知错误！返回值为' . $errno;
                break;
        }

        return $errmsg;
    }

    /**
     * 修改密码
     *
     * @param  string $username  用户名
     * @param  string $oldPass  旧密码
     * @param  string $newPass  新密码
     * @param  string $newPass2 确认密码
     * @return int 负数为错误信息，正数为成功
     */
    public function editPassword($username, $oldPass, $newPass, $newPass2) {
        if (!$username || !$oldPass || !$newPass || !$newPass2) {
            return -11;
        }

        if ($newPass != $newPass2) {
            return -12;
        }

        if ($oldPass == $newPass) {
            return -13;
        }

        $rpc = new Rpc();
        $res = $rpc->call('account', 'editPassword', [$username, $oldPass, $newPass, $newPass2]);
        return $res;
    }

    /**
     * 获取修改密码错误代码对应的错误信息
     *
     * @param  int $errno 错误代码
     * @return string     错误信息
     *                    -11  缺少参数
     *                    -12  输入的两次新密码不一样
     *                    -13  新密码不能与旧密码相同
     *                    -14  新密码必须由6~16位非空字符串组成，且不包含英文单引号、双引号或反斜线
     *                    -1   旧密码不正确
     *                    -4   Email 格式有误
     *                    其他非正数 未知错误
     */
    public function editPasswordError($errno) {
        if ($errno >= 0) {
            return '';
        }

        switch ($errno) {
            case -11:
                $errmsg = '缺少参数';
                break;
            case -12:
                $errmsg = '输入的两次新密码不一样';
                break;
            case -13:
                $errmsg = '新密码不能与旧密码相同';
                break;
            case -14:
                $errmsg = '新密码必须由6~16位非空字符串组成，且不包含英文单引号、双引号或反斜线';
                break;
            case -1:
                $errmsg = '旧密码不正确';
                break;
            case -4:
                $errmsg = 'Email 格式有误';
                break;
            default:
                $errmsg = '未知错误！返回值为' . $errno;
                break;
        }

        return $errmsg;
    }

    /**
     * 修改用户信息
     *
     * @param  array $info 要修改的用户信息
     *                     uid          用户ID
     *                     contact_name 姓名
     *                     zone_id      所在城市ID
     *                     sex          性别ID: 0-保密；1-男；2-女
     *                     birthday     生日
     *                     qq           QQ号
     *                     user_email   可选，电子邮箱
     * @return int         成功返回整数，失败返回负数
     */
    public function editInfo($info) {
        $rpc = new Rpc();
        $res = $rpc->call('account', 'editInfo', [$info]);
        return $res;
    }

    /**
     * 验证修改用户信息的参数是否正确
     *
     * @param  array $info 数组
     * @return int 整数为验证成功，负数为验证失败
     */
    protected function validEdit($info) {
        // 参数验证
        $params = array('uid', 'contact_name', 'zone_id', 'sex', 'birthday', 'qq');
        foreach ($params as $param) {
            if (!isset($info[$param])) {
                return -11;
            }
        }

        // 邮箱验证
        if (!empty($info['user_email'])) {
            if (!\Valid::email($info['user_email'])) {
                return -12;
            }
        }

        // 验证 zone_id
        if (!preg_match('/^[1-9]\d{3}00$/', $info['zone_id'])) {
            return -13;
        }

        // 验证性别
        if (!in_array($info['sex'], array(0, 1, 2))) {
            return -14;
        }

        // 验证生日
        if (!\Date::isValid($info['birthday'], 'Y-m-d')) {
            return -15;
        }

        // 验证QQ号
        if (!preg_match('/^[1-9]\d{4,11}$/', $info['qq'])) {
            return -16;
        }

        return 1;
    }

    /**
     * 获取修改用户信息错误代码对应的错误信息
     *
     * @param  int $errno 错误代码
     * @return string     错误信息
     *                    -11  缺少参数
     *                    -12  邮箱验证失败
     *                    -13  城市信息验证失败
     *                    -14  性别参数错误
     *                    -15  生日格式错误
     *                    -1   该用户不存在
     *                    其他非正数 未知错误
     */
    public function editInfoError($errno) {
        if ($errno >= 0) {
            return '';
        }

        switch ($errno) {
            case -11:
                $errmsg = '缺少参数';
                break;
            case -12:
                $errmsg = '邮箱验证失败';
                break;
            case -13:
                $errmsg = '城市信息验证失败';
                break;
            case -14:
                $errmsg = '性别参数错误';
                break;
            case -15:
                $errmsg = '生日格式错误';
            case -16:
                $errmsg = 'QQ格式错误';
                break;
            case -1:
                $errmsg = '该用户不存在';
                break;
            default:
                $errmsg = '未知错误！返回值为' . $errno;
                break;
        }

        return $errmsg;
    }

    /**
     * 获取用户信息
     *
     * @param  int $uid 用户ID
     * @return int|array 成功返回用户信息数组，失败返回负数
     */
    public function getInfo($uid) {
        $uid = intval($uid);
        
        $rpc = new Rpc();
        $res = $rpc->call('account', 'getInfo', [$uid]);
        return $res;
    }

    /**
     * 获取用户信息错误代码对应的错误信息
     *
     * @param  int $errno 错误代码
     * @return string     错误信息
     *                    -1   该用户不存在
     *                    其他非正数 未知错误
     */
    public function getInfoError($errno) {
        if ($errno >= 0) {
            return '';
        }

        switch ($errno) {
            case -11:
                $errmsg = '缺少参数';
                break;
            case -1:
                $errmsg = '该用户不存在';
                break;
            default:
                $errmsg = '未知错误！返回值为' . $errno;
                break;
        }

        return $errmsg;
    }

    /**
     * 重设密码
     *
     * @param  int    $uid      用户ID
     * @param  string $newPass 新密码
     * @return bool
     */
    public function resetPassword($uid, $newPass) {
        $uid      = intval($uid);
        $newPass = trim($newPass);
        $rpc      = new Rpc();
        $res      = $rpc->call('account', 'resetPassword', [$uid, $newPass]);
        return $res;
    }

    

    /**
     * 统计一天内使用该 IP 的注册次数
     *
     * 使用示例：
     * ```php
     * $acc = new Cncn\Account();
     * $ipRegNum = $acc->ipRegNum($onlineip);
     * if ($ipRegNum > 1) {
     *     // 对不起！同一IP一天最多只能注册两个账号！
     * }
     * ```
     *
     * @param  string $onlineip IP地址
     * @return int
     */
    public function ipRegNum($onlineip) {
        $rpc      = new Rpc();
        $res      = $rpc->call('account', 'ipRegNum', [$onlineip]);
        return $res;
    }

    /**
     * 统计一天内该 IP 是否注册过
     *
     * 使用示例：
     * ```php
     * $acc = new Cncn\Account();
     * if ($acc->ipRegistered($onlineip)) {
     *     // 对不起！推荐的同一IP注册，一天只能一次！
     * }
     * ```
     *
     * @param  string $onlineip IP地址
     * @return int 注册过则返回 uid，未注册过返回 0
     */
    public function ipRegistered($onlineip) {
        $rpc      = new Rpc();
        $res      = $rpc->call('account', 'ipRegistered', [$onlineip]);
        return $res;
    }
}
