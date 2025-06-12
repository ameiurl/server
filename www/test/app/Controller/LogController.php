<?php
namespace App\Controller;

/**
 * 查看日志
 */
class LogController extends BaseController{
	public function __construct()
	{
		parent::__construct();
        $this->logPath = FIREFLY_STORAGE_PATH. '/logs';
	}
    protected function checkPassword() {
        $accounts = array(
            'xmtb' => '3s23oUsX',
            'cncn' => 'Lkbp5A89',
            'amei' => 'meishan3',
        );

        \Session::start();
        if (!isset($_SERVER['PHP_AUTH_USER']) && empty($_SESSION["auth_user"])) {
            $_SESSION["auth_user"] = true;
            header('WWW-Authenticate: Basic realm="cncn.com"');
            header('HTTP/1.0 401 Unauthorized');
            exit('Error');
        } else {
            $au = $_SERVER['PHP_AUTH_USER'];
            if (isset($accounts[$au]) && $accounts[$au] === $_SERVER['PHP_AUTH_PW']) {
                $_SESSION["auth_user"] = $au;
            } else {
                $_SESSION["auth_user"] = '';
                header('WWW-Authenticate: Basic realm="cncn.com"');
                header('HTTP/1.0 401 Unauthorized');
                exit('Error');
            }
            unset($au);
        }
    }

    public function index() {
        $this->showLog();
    }

	public function logView2()
	{
		$this->checkPassword();
        $log_path = $this->logPath;
		//$re = \My\Util\Dir::listDir($log_path);
		$re = \File::scanfDir($log_path, true);
        print_r($re);exit;
		if ($re['files']) {
			foreach ($re['files'] as $key=>$val) {
				$logfiles[$key] = str_replace($log_path, '', $val);
			}
		}

		$this->view->assign(
			array(
				'logfiles' => $logfiles,
				'log_path' => $log_path,
			));
		$this->view->display('log');
	}

	public function logView()
	{
		$this->checkPassword();
        $page       = $_GET['page']??1;
        $perpage    = $_GET['perpage']??2;
        $file		= $_GET['file']??'';
        $page       = (int)$page;
        $perpage    = (int)$perpage;
		$file       = addslashes(trim($file));
        $logs       = [];
        
        $filename = $this->logPath.'/'.$file;
		if(!is_file($filename)){
			$this->throwError('该文件不存在');
		} 

        $file_content = file_get_contents($filename);
        $pattern="/(\[\d{2} \d{2}:\d{2}:\d{2}\])/";
        $out = preg_split($pattern, $file_content, -1, PREG_SPLIT_DELIM_CAPTURE);
        array_shift($out);
        if (!empty($out)) {
            for($i=0;$i<count($out);$i++){
                $out2['dateline'] = $out[$i];       
                $out2['extra']  = $this->deal_br($out[$i+1]);       
                $logs[] = $out2;
                $i++;
            }
        }
        $page_logs = $this->pageArray($perpage, $page, $logs, 0);
		$url = "?action=log&todo=log_view&file=$file&page=pageno";
		$cutpage = $this->multitb(COUNT($logs), $perpage, $page, $url);
        //print_r($cutpage);exit;

		$this->view->assign(
			array(
				'ac'		=> 'view',
				'page_logs' => $page_logs,
				'log_path' => $log_path,
				'cutpage'  => $cutpage,
			));
		$this->view->display('log');
	}
    
    public function showLog() {
		 $this->checkPassword();

        if (!isset($_GET['path'])) {
			$log_date = $_GET['date'] ?? date('Y-m');
            $log_path = FIREFLY_STORAGE_PATH. '/logs/' . $log_date . '/*';
            $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/?action=log&todo=log_view&file';
            foreach (glob($log_path) as $filePath) {
                $filePath = str_replace(FIREFLY_STORAGE_PATH . '/logs/', '', $filePath);
                $basename = basename($filePath);
                echo "<a target='_blank' href='$base_url=$filePath'>$basename<a>" . "<br>\n";
            }
            exit;
        }

        $path = $_GET['path'];
        $filePath = FIREFLY_STORAGE_PATH . '/logs/' . $path;
        if (!is_file($filePath)) {
            exit('log file not found');
        }

        $cncn_ips = \Dragonfly\Company\Cncn::ips();
        if (getenv('APP_ENV') === 'prod') {
            if (!in_array($_SERVER['REMOTE_ADDR'], $cncn_ips)/* || !$debug*/) {
                exit('access denied');
            }
        }

        header("Content-Type: text/plain; charset=UTF-8"); 
        $fp = fopen($filePath, 'r');
        $size = filesize($filePath);
        $threhold = 2 << 20;  // 2M
        if ($size > $threhold) {  // 超过阈值
            fseek($fp, $size - $threhold);  // 只输出阈值大小的内容
        }
        fpassthru($fp);
    }

    //获取文件名后缀
    private function fileext($filename) {
    	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
    }  
  
    private function deal_br($str){
        $str = str_replace(array(chr(13),chr(10),'&lt;br/&gt;','&lt;b&gt;','&lt;/b&gt;'),array('<br/>','<br/>','<br/>','<b>','</b>'),$str);
        return $str;
    }

    /**
     * 数组分页函数  核心函数  array_slice
     * 用此函数之前要先将数据库里面的所有数据按一定的顺序查询出来存入数组中
     * $count   每页多少条数据
     * $page   当前第几页
     * $array   查询出来的所有数组
     * order 0 - 不变     1- 反序
     */
    public function pageArray($count, $page, $array, $order) {
        $page  = (empty($page)) ? '1' : $page; #判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; #计算每次分页的开始位置
        if ($order == 1) {
            $array = array_reverse($array);
        }
        $totals    = count($array);
        $countpage = ceil($totals / $count); #计算总页面数
        $pagedata  = array();
        $pagedata  = array_slice($array, $start, $count);

        return $pagedata;  #返回查询数据
    }

	/*
	 * 淘宝格式分页
	 *-------------------------------------------------
	 * @param  int  $num       数据总条数
	 * @param  int  $perpage   每页显示的条数
	 * @param  int  $curpage   当前页数
	 * @param  int  $urlf      链接地址，如 pageno.html，其中pageno将会被替换为指定的页数
	 * @param  int  $maxpages  最大页数
	 * @param  int  $nofollow  默认正常href，1为搜索引擎不要抓取(rel="nofollow")
	 *-------------------------------------------------
	 */
	function multitb($num, $perpage, $curpage, $urlf, $maxpages = 0, $nofollow = 0) {
		// 数据总条数不足或只有一页时，不用处理，直接返回
		if ($num <= $perpage) {
			return '';
		}

		$multipage = '';
		$plus_str = '';
		if($nofollow) {
			$plus_str = ' rel="nofollow"';
		}

		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

		$multipage .= ($curpage > 1 ? '<a class="num prev" href="' . $this->pagehtmtb($curpage - 1, $urlf) . '"'.$plus_str.'><span>上一页</span><i class="triangle"></i></a>' : '');


		// 前面部分是固定显示的
		//----------------------------------------------------------------
		// 前面部分显示的分页数
		$head = 2;
		for ($i = 1; $i <= $head; $i++) {
			$multipage .= $i == $curpage ? '<span class="active" href="#"'.$plus_str.'>' . $i . '</span>' :
				'<a class="num" href="' . $this->pagehtmtb($i, $urlf) . '"'.$plus_str.'>' . $i . '</a>';
		}
		//----------------------------------------------------------------

		// 中间也将显示固定数量的分页数
		//----------------------------------------------------------------
		if ($pages > $head) {
			// 这里应该是个奇数，因为在当前页的两边需要显示一样的页数，
			// 不是奇数时，前后需要显示的数目会不同，下面的起始位置的算法也需要修改
			$middle = 5;

			// 中间将固定最多显示$middle个分页，计算起始值时和结束值时，将以当前页为标准，往前后推
			$middle_start = $curpage - floor($middle / 2);
			// 中间起始位置：
			// 1.如果小于等于前面的最大值的下一页，则用最大值+1
			// 2.不符合1时，判断是否可以显示下最后的分页，可以时，则直接从总页数倒推，不行时，则用前面算出的起始值
			$middle_start = $middle_start <= $head + 1 ? $head + 1 : ($middle_start + $middle > $pages ? $pages - $middle + 1 : $middle_start);
			// 当前页如果小于等于前面的最大值的下一页，则直接显示5个分页(主要目的是让用户选择方便，页面看起来也比较清爽)
			if ($curpage <= $head + 1 && $pages > ($head + $middle)) {
				$middle_end = 5;
			} else {
				$middle_end = $middle_start + $middle - 1;
				$middle_end = $middle_end > $pages ? $pages : $middle_end;
			}

			// 总页数大于前面和中间可显示的最大数量时，且中间的最左边大于前面部分的最大页数时
			// 需要在前面部分的后面加...
			if ($pages > ($head + $middle) && $middle_start > $head + 1) {
				$multipage .= '<span class="split">...</span>';
			}

			for ($i = $middle_start; $i <= $middle_end; $i++) {
				$multipage .= $i == $curpage ? '<span class="active" href="#"'.$plus_str.'>' . $i . '</span>' :
					'<a class="num" href="' . $this->pagehtmtb($i, $urlf) . '"'.$plus_str.'>' . $i . '</a>';
			}

			// 判断最后是否需要出现...
			if ($pages > $middle_end) {
				$multipage .= '<span class="split">...</span>';
			}
		}
		$pagehtm = $this->pagehtmtb($curpage + 1, $urlf);
		$multipage .= ($curpage < $pages ? '<a class="num next" href="' . $pagehtm . '"'.$plus_str.'><span>下一页</span><i class="triangle"></i></a>' : '');
		$multipage .= '<span class="text">共' . $pages . '页</span>';
		return $multipage;
	}

	/**
	 *页码替换
	 * 特殊参数：$fsthtm，如果有定义该全局变量，本函数也会使用该变量，作用跟$fst_param类似
	 *           强烈建议不使用该变量，而是直接传递$fst_param参数
	 *@param    int     $page       页码
	 *@param    string  $urlf       页码的跳转地址，里面包含pageno，匹配pageno替换成$page
	 *@param    string  $fst_param  第一页的页码地址：当$page等于1的时候页面地址为改参数
	 *@return   string              页面跳转地址(匹配替换后的), 当页码为第一页时，URL中不体现页码
	 */
	function pagehtmtb($page, $urlf, $fst_param='') {
		if($page == 1) {
			if ($fst_param) {
				return $fst_param;
			} else {
				global $fsthtm;
				if ($fsthtm) {
					return $fsthtm;
				} else if (substr($urlf, -7) == '/pageno') {
					return substr($urlf, 0, -7);
				}
			}
		}
		return str_replace("pageno",$page,$urlf);
	}
}
