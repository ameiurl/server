<?php
namespace App\Controller;

use Butterfly\Web\Controller;

/**
 * Class BaseController
 *
 * @property \Butterfly\View\ViewInterface $view
 * @property \Butterfly\Log\FileLogger $logger
 * @property \Butterfly\Web\Request $request
 */
class BaseController extends Controller
{
    public function __construct()
    {
        //parent::__construct();
		error_reporting(E_ALL);
        //$this->initToken();

        //$this->token = Context::getInstance()->getToken();
        //$this->assign('token', $this->token->getData());
		$this->_initialize();
    }

    /**
     * @return \think\View
     */
    public function getView()
    {
        return $this->view;
    }

    protected function _initialize()
    {
        //重置框架设置的错误日志
        restore_error_handler();
        //session初始化
        //Session::boot();
		$this->cncn_session_start();
        //异常处理
        set_error_handler([$this,'__error_handler']);//暂时不启动语法错误日志
        set_exception_handler([$this,'__exception_handler']);
        register_shutdown_function([$this, '__appShutdown']);

        //表单令牌验证
        //Validate::checkFormToken();

        //$this->assign('showVersion', 0);  //显示底部版权所有
    }

    /**
     * 抛出异常
     * @param string $msg
     */
    protected function throwError($msg = ''){
        throw new \RuntimeException($msg);
    }

    /**
     * ajax返回数据
     * @param $data
     * @param int $code
     * @param array $header
     * @return \think\response\Json
     */
    protected function ajaxReturn($data, $code = 200, $header = []){
        return json($data,$code,$header,['json_encode_param'=>JSON_PARTIAL_OUTPUT_ON_ERROR]);
    }

    /**
     * ajax返回成功
     * @param string $msg 返回数据
     * @param array $data 返回数据-附加信息
     * @return \think\response\Json
     */
    protected function ajaxSuccess($msg = '', $data = []){
        $returnData = [
            'code' => 1,
            'msg'  => $msg
        ];
        if (!empty($data) && is_array($data)){
            $returnData = array_merge($returnData, $data);
        }
        return $this->ajaxReturn($returnData);
    }

    /**
     * ajax返回错误
     * @param string $msg 返回数据
     * @param array $data 返回数据-附加信息
     * @return \think\response\Json
     */
    protected function ajaxError($msg = '', $data = []){
        $returnData = [
            'code' => 0,
            'msg'  => $msg
        ];
        if (!empty($data) && is_array($data)){
            $returnData = array_merge($returnData, $data);
        }
        return $this->ajaxReturn($returnData);
    }

    /**
     * 渲染列表数据
     * @param array $data
     * @return \think\response\Json
     */
    protected function renderLayuiDataTable($data = []){
        $returnData = array(
            'code'  => 0,
            'msg'   => '',
            'count' => intval($data['count']),
            'data'  => $data['list'],
        );
        return $this->ajaxReturn($returnData);
    }

    /**
     * 加载弹窗模板输出
     * @param string $template 模板文件名
     * @param array $vars      模板输出变量
     * @param array $replace   模板替换
     * @param array $config    模板参数
     * @return string
     */
    protected function fetchDialog($template = '', $vars = [], $replace = [], $config = [])
    {
		//$this->view->engine->layout(false);
		return $this->view->fetch($template, $vars, $replace, $config);
    }

    public function __appShutdown()
    {
        //Session::pause();
		$this->cncn_session_destroy();
    }

    public function __error_handler($errno, $errstr, $errfile='', $errline='', $errcontext=null)
    {
        if ($errno == E_STRICT || $errno == E_NOTICE || $errno == E_RECOVERABLE_ERROR || $errno == E_WARNING)
        {
            return;
        }

        $message  = 'type   = PHP ERROR'."\n".
            'code    = '.$errno."\n".
            'message = '.$errstr."\n".
            'file    = '.$errfile."\n".
            'line    = '.$errline."\n";

        $this->__errorMsgHandler($errno,$errstr, $message);
    }

    /**
     * @param \Exception $exception
     */
    public function __exception_handler($exception){

        $type     = get_class($exception);
        $code     = $exception->getCode();
        $message  = $exception->getMessage()."\n".$exception->getTraceAsString();
        $file     = $exception->getFile();
        $line     = $exception->getLine();

        $message  = 'type   = '.$type."\n".
            'code    = '.$code."\n".
            'message = '.$message."\n".
            'file    = '.$file."\n".
            'line    = '.$line."\n";

        $this->__errorMsgHandler($code,$exception->getMessage(), $message);
    }

    //系统错误处理
    protected function __errorMsgHandler($code,$msg,$message){

        \Log::error("{$code}: $message");

        if(is_inner_ip() || true){
            header('Content-Type: text/html; charset=utf-8',true,404);
            $str = '<style>body {font-size:12px;}</style>';
            $str .= '<h1>操作失败！</h1><br />';
            $str .= '<strong>错误信息：<strong><font color="red">' . $msg . '</font><br />';
            $str .= '<strong>Trace：' . nl2br($message) . '<br />';

            echo $str;exit;
        }else if($this->request->isAjax()){

            header('Content-type:application/x-javascript');
            header('Content-type: application/json');
            exit(json_encode(['code' => $code, 'msg' => $msg]));

        }else{

            header('Content-Type: text/html; charset=utf-8',true,404);
			$this->view->assign('errorMsg', $msg);
			echo $this->fetchDialog("error_msg");
            exit;
        }
    }

	/**
	 * @brief   cncn_session_start   自定义开启session
	 *
	 * @Param   $limiter            浏览器缓存，默认session_start()是nocache
	 *
	 * @Returns    
	 */
	function cncn_session_start($limiter = '') {
		if (session_id() == '') {
			ini_set('session.name', 'XMBZERP');      //自定义session_name
			ini_set('session.cookie_httponly', 1);      //开启http-only,防止客户端js通过xss盗取cookie

			if (in_array($limiter, array('public', 'private', 'nocache', 'private_no_expire'))) {
				session_cache_limiter($limiter);        //参考:http://www.9enjoy.com/pragma-no-cache-session/
			}

			ini_set('session.gc_maxlifetime', 4 * 3600);    //session过期时间，启动垃圾回收机制
			session_start();
		}
	}


	/**
	 * @brief   diy_session_destroy     彻底注销session
	 *
	 * @Returns NUL   
	 */
	function cncn_session_destroy() {
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 86400, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
			);
		}
		session_destroy();

		//$this->cncn_exit('登录已过期，请刷新重新登录');
	}
}
