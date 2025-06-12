<?php

namespace Rpc\Com;

use Dragonfly\Rpc\Rpc;
/**
 * Auth - 登录验证类 (调用yar)
 *
 * @author zhangwh
 * @since 2015-08-22 15:31
 */
class Auth {

    protected static $YEAR = 86400*365;

    /**
     * 获取用户的浏览器信息，进行md5加密后返回
     *
     * @param  bool $md5 是否返回md5后的值，默认为是
     * @return  string
     */
    public function getClientInfo($md5 = true) {
        $info = array(
            'agent' => empty($_SERVER['HTTP_USER_AGENT']) ? 'user_agent' : $_SERVER['HTTP_USER_AGENT'],
            'lang'  => empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? 'cn' : strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']),
            // IE 浏览器普通请求的 Accept-Language 值是 zh-CN，ajax请求的是 zh-cn，
            // 故需要统一转换成小写
        );

        $info = implode('', $info);

        return $md5 ? md5($info) : $info;
    }

    /**
     * 验证保持登录的cookie是否正确
     *
     * @return  int    验证登录时，如通过验证则返回该cookie对应的UID，失败时，返回失败编号
     */
    protected function chkAuthCookie() {
        if (empty($_COOKIE['uch_auth'])) {
            return -1;
        }
        $auth_code = $_COOKIE['uch_auth'];
        $uid = 0;

        $info = array(
            'uid'         => $uid,
            'auth_code'   => $auth_code,
            'client_ip'   => $_SERVER['REMOTE_ADDR'],
            'user_agent'  => $this->getClientInfo(false),
            'from'        => 'com',
        );

        static $checked;
        if (isset($checked[$auth_code])) {
            return $checked[$auth_code];
        }
        $checked[$auth_code] = $this->checkUcSession($info);

        return $checked[$auth_code];
    }

    /**
     * 对用户的登录进程进行验证
     *
     * @param  array $info 需要进行验证的信息，包括：
     *      auth_code    必选，加密后的登录权限字符串
     *      client_ip    必选，请求验证的用户的IP
     *      user_agent   必选，请求验证的用户的浏览器的相关信息
     * @return  int    验证登录时，如通过验证则返回该cookie对应的UID，失败时，返回失败编号
     */
    public function checkUcSession($info) {
        $rpc = new Rpc();
        $res = $rpc->call('auth', 'checkUcSession', [$info]);
        return $res;
    }

    /**
     * 每30分钟检测登录期间是否登录进程已被删除(比如其他进程修改了密码)
     * @param   int $uid 当前登录用户，保存在session里的uid
     *
     * @return  mixed     验证登录时，返回该cookie对应的UID，失败时，返回FALSE
     */
    protected function chkAuthSession($uid) {
        if (isset($_SESSION['last_uc_check_time']) && time() - $_SESSION['last_uc_check_time'] > 1800) {
            $uid = $this->chkAuthCookie();
            if ($uid < 0) {
                // 登录凭证验证失败后，将当前的session删除
                if (session_id() != '') {   // check login被调用多次。。所以这里需要加个判断
                    session_destroy();
                }
            } else {
                $_SESSION['last_uc_check_time'] = time();
            }
        }
        if (empty($uid) || $uid < 0) {
            $uid = 0;
        }

        return $uid;
    }

    /**
     * 判断是否已登录
     *  符合英文语法的准确方法名应该为 logged_in，实际中大家比较能理解的是 isLogin
     * 故取方法名为 isLogin
     *
     * @return int 未登录返回0，已登录返回 uid
     */
    public function isLogin() {
        if (!isset($_COOKIE['uch_auth'])) {
            // 无 uch_auth cookie 则直接认为未登录，
            // 以避免 start_session 生成一堆 session 文件，及 rpc 调用
            return 0;
        }

        // 校验登录的参数
        $info['cookie'] = [
            'uch_auth' => $_COOKIE['uch_auth']
        ];
        $info['server'] = [
            'remote_addr'           => $_SERVER['REMOTE_ADDR'],
            'http_user_agent'       => $_SERVER['HTTP_USER_AGENT'],
            'http_accept_language'  => $_SERVER['HTTP_ACCEPT_LANGUAGE']
        ];
        $rpc = new Rpc();
        $res = $rpc->call('auth', 'isLogin', [$info]);
        if(isset($res['session']['uid'])) {
            $_SESSION['uid'] = $res['session']['uid'];
            return $_SESSION['uid'];
        }
        return 0;
    }

    /**
     * 检查指定用户的状态
     * 
     * @param  int $uid 用户ID
     * @return bool 启用返回 true，禁用返回 false
     */
    public function checkUserStatus($uid) {
        $rpc = new Rpc();
        $res = $rpc->call('auth', 'checkUserStatus', [$uid]);
        return $res;
    }

    /**
     * 生成保持登录的cookie的信息
     *
     * @param   int $uid 用户编号
     * @param   int $username 用户名
     * @param   int $expire cookie保存时间，单位为秒，默认为NULL,即浏览器关闭后就失效
     * @return  string
     */
    public function setAuthCookie($uid, $username, $expire) {
        global $cookiedomain;
        
        $info['cookie'] = $_COOKIE;
        $info['server'] = [
            'remote_addr'           => $_SERVER['REMOTE_ADDR'],
            'http_user_agent'       => $_SERVER['HTTP_USER_AGENT'],
            'http_accept_language'  => $_SERVER['HTTP_ACCEPT_LANGUAGE']
        ];
        $rpc      = new Rpc();
        $uch_auth = $rpc->call('auth', 'setAuthCookie', [$uid, $username, $expire, $info]);
        
        $set_cookie = ($expire >= 0);  // API方式登录时，$expire 为 -1，无需设置cookie
        if ($set_cookie) {
            if (!empty($expire)) {
                $expire = time() + $expire;
            } else {
                $expire = 0;
            }
        } else {
            $expire = 0;
        }
        // 请求时需要提供auth_code、用户的client_info、用户的IP，所有判断都放到服务端
        if ($uch_auth) {  // API 登录方式无需设置 cookie
            header('P3P: CP=CAO PSA OUR');
            setcookie('uch_auth', $uch_auth, $expire, '/', $cookiedomain, false, true);
            setcookie('uch_loginuser', $username, time() + self::$YEAR, '/', $cookiedomain);
        }

        return $uch_auth;
    }

    /**
     * 登出
     */
    public function logout() {
        global $cookiedomain;

        // 清 session 代码出自: http://cn2.php.net/manual/en/function.session-destroy.php
        $this->startSession();
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();

        // 清除保持登录的 cookie
        setcookie('uch_auth', '', strtotime('-1 year'), '/', $cookiedomain);
    }

    /**
     * 开启session会话，开启前判断session是否已经启动
     *     不使用 cncn_session_start() 是为了避免对 include/function.php 文件的依赖
     *     因为有的程序没有包含 include/function.php 文件
     * 
     * @return void
     */
    private function startSession() {
        if (session_id() === '') {
            if (getenv('APP_ENV') === 'prod') {
                global $cookiedomain;
                ini_set('session.cookie_domain', $cookiedomain);
            }

            session_start();
        }
    }
}
