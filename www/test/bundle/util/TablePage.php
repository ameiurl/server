<?php
/**
 * 列表分页类
 * Created by PhpStorm.
 * User: yuzq
 * Date: 2018/3/31
 * Time: 10:09
 */

namespace erp\util;

use think\Config;
use think\Db;
use think\View;

class TablePage{
    public $pageOpt;
    public $sql = '';           #执行的sql语句，方便调试 echo $this->page->sql;
    public $view;            //视图变量
    public $pages = array(
        'var'        => 'page',#当前页参数
        'link'       => '<a {url}>{num}</a> ',#普通分页链接
        'current'    => 'href="javascript:;" class="on"',#当前页链接样式
        'url'        => '',
        'per'        => 20,#每页显示数
        'total'      => 0,#总记录数
        'nonce'      => 0,#当前页(起始页为1)
        'count'      => 0,#总页数
        'prev'       => 0,#下一页页码
        'next'       => 0,#上一页页码
        'page_param' => 'page',#页面参数
        'links'      => array(),
        'goto'       => '',
    );

    //每页条数设置
    protected $pageItemNums = [10, 20, 30, 40, 50, 60, 70, 80, 90];


    public function __construct(){
        $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));

        if (!$this->pages['url']) {
            $url_info = parse_url($_SERVER['REQUEST_URI']);
            $path = $url_info['path'];
            $params = [];
            if (!empty($url_info['query'])){
                parse_str($url_info['query'], $arr);
            }
            $params = !empty($url_info['query']) ? parse_url($url_info['query']) : [];
            $params[$this->pages['page_param']] = '';
            unset($params['_pjax']);
            unset($params['_block']);
            $url = $path . '?' . http_build_query($params) . '{num}';
            $this->pages['url'] = 'href="' . $url . '" ';
        }
    }
    #设置分页参数
    function set_vars($a){
        $this->pageOpt=$a;
    }

    #带分页查询
    function query($sql, $bind = []){
        isset($this->pageOpt)||$this->pageOpt=array();
        $this->pages['nonce']= isset($_REQUEST[$this->pages['var']])?max((int)$_REQUEST[$this->pages['var']],1):1;#设置当前页
        foreach($this->pageOpt as $k=>$v){
            $this->pages[$k]=$v;  //设置分页参数
        }

        $limit = ($this->pages['nonce']-1)*$this->pages['per'].','.$this->pages['per'];
        //$r = $this->page_db->query(substr_replace(trim($sql),'SELECT SQL_CALC_FOUND_ROWS ',0,6).' LIMIT '.$limit);
        $r = Db::query(substr_replace(trim($sql),'SELECT SQL_CALC_FOUND_ROWS ',0,6).' LIMIT '.$limit, $bind);
        $t = Db::query("SELECT FOUND_ROWS() AS t");
        $t = $t[0];
        $this->pages['total'] = $t['t'];#设置总记录数
        $this->assign('total',$t['t']);
        //$this->pages['html'] = $t['t']<$this->pages['per']?'':$this->page();
        $this->pages['html'] = $this->page();
        $this->assign('tablePage',$this->pages['html']);
        $this->assign('page_total',ceil($this->pages['total']/$this->pages['per']));
        return $r;
    }

    //数组分页
    function query_ary($ary, $preserve_keys = false){
        isset($this->pageOpt)||$this->pageOpt=array();
        $this->pages['nonce']= isset($_REQUEST[$this->pages['var']])?max((int)$_REQUEST[$this->pages['var']],1):1;#设置当前页
        foreach($this->pageOpt as $k=>$v)$this->pages[$k]=$v;#设置分页参数
        $limit = ($this->pages['nonce']-1)*$this->pages['per'];
        $r = array_slice($ary,$limit,$this->pages['per'], $preserve_keys);
        $t = count($ary);
        $this->pages['total'] = $t;#设置总记录数
        $this->assign('total',$t);
        $this->pages['html'] = $t<$this->pages['per']?'':$this->page();
        $this->assign('tablePage',$this->pages['html']);
        $this->assign('page_total',$this->pages['total']);
        return $r;
    }

    //根据总条数，创建分页样式
    function create_page_by_total($total = 0){
        isset($this->pageOpt) || $this->pageOpt=array();
        $this->pages['nonce'] = isset($_REQUEST[$this->pages['var']]) ? max((int)$_REQUEST[$this->pages['var']], 1) : 1; //设置当前页
        foreach($this->pageOpt as $k => $v){
            $this->pages[$k] = $v;  //设置分页参数
        }

        $limit                = ($this->pages['nonce']-1) * $this->pages['per'];
        $this->pages['total'] = $total; //设置总记录数
        $this->pages['html']  = $total < $this->pages['per'] ? '' : $this->page();

        $this->assign('total',$total);
        $this->assign('page',$this->pages['html']);
        $this->assign('tablePage',$this->pages['html']);
        $this->assign('page_total',$this->pages['total']);
    }

    /**
     * 获取分页样式
     * @return mixed|string
     */
    function page(){
        foreach($this->pages as $k => &$v){
            ${'_'.$k}=$v;
        }

        if ($_per) {
            $_count= (int)ceil($_total / $_per);
        } else {
            $_count=0;
        }

        /*if($_count<2){
            return '';
        }*/

        $_nonce = min($_count, max($_nonce, 1));  //当前页区间[1,count]

        /*分页区间*/
        $off = 3;
        $off2= $off * 2;
        if($_count>$off2+1){
            $first=$_nonce<$off+1?1:$_nonce-$off;
            $last=$_count-$_nonce<$off+1?$_count:$_nonce+$off;
            if($last-$first<$off2)($last-$off2>0)?($first=$last-$off2):($last=$first+$off2);
        }else{
            $first = 1;
            $last = $_count;
        }

        /*--------*/
        $_prev = $_nonce == 1 ? '' : $_nonce-1;  //上一页
        $_next = $_nonce == $_count?'':$_nonce+1;  //下一页

        $prev = $next = '';
        if ($_nonce > 1){
            $prev = '<a href="' . $this->current(['page' => $_prev]) . '" class="layui-laypage-prev" data-page="' . $_prev . '">上一页</a>';
        }else{
            $prev = '<a href="javascript:;" class="layui-laypage-prev layui-disabled" data-page="0">上一页</a>';
        }

        if ($_nonce != $_count){
            $next = '<a href="' . $this->current(['page' => $_next]) . '" class="layui-laypage-next" data-page="' . $_next . '">下一页</a>';
        }else{
            $next = '<a href="javascript:;" class="layui-laypage-next layui-disabled" data-page="0">下一页</a>';
        }

        $page1 = $page2 = $page3 = '';  //中间3页
        if ($_nonce > 1){
            $_page = $_nonce - 1;
            $page1 = '<a href="' . $this->current(['page' => $_page]) . '" data-page="' . $_page . '">' . $_page . '</a>';
        }

        $page2 = '<span class="layui-laypage-curr"><em class="layui-laypage-em"></em><em>' . $_nonce . '</em></span>';

        if ($_nonce + 1 <= $_count){
            $_page = $_nonce + 1;
            $page3 = '<a href="' . $this->current(['page' => $_page]) . '" data-page="' . $_page . '">' . $_page . '</a>';
        }

        $first = $last = '';
        if ($_nonce >= 3){
            $first = '<a href="' . $this->current(['page' => 1]) . '" data-page="' . 1 . '">' . 1 . '</a>';
        }

        if ($_count >= 3 && $_count > ($_nonce + 1)){
            $last = '<a href="' . $this->current(['page' => $_count]) . '" data-page="' . $_count . '">' . $_count . '</a>';
        }


        $prevStr = '';
        if ($_nonce >= 4){
            $prevStr = '<span class="layui-laypage-spr">…</span>';
        }

        $nextStr = '';
        if ($_count >= 4 && $last){
            $nextStr = '<span class="layui-laypage-spr">…</span>';
        }

        //每页条数设置
        $pageItemNumStr = '';
        /*$pageItemNumStr .= '<span class="layui-laypage-limits">';
        $pageItemNumStr .= '<select>';
        foreach ($this->pageItemNums as $item){
            $pageItemNumStr .= '<option value="' . $item . '">' . $item . ' 条/页</option>';
        }
        $pageItemNumStr .= '</select>';
        $pageItemNumStr .= '</span>';*/

        //页面跳转
        $goStr = '';
        //$goStr = '<span class="layui-laypage-skip">到第 <input type="text" min="1" value="' . $_nonce . '" class="layui-input">页 <button type="button" class="layui-laypage-btn">确定</button></span>';

        $html = "<div class=\"panel\">
                 <div class=\"layui-table-page\">
                 <div id=\"layui-table-page1\">
                 <div class=\"layui-box layui-laypage layui-laypage-default\">
          <span class=\"layui-laypage-count\">共{$_total}条</span>
          {$prev}
          {$first}
          {$prevStr}
          {$page1}
          {$page2}
          {$page3}
          {$nextStr}
          {$last}
          {$next}
          {$pageItemNumStr}
          {$goStr}
        </div>
        </div>
        </div>
        </div>";

        return $html;
    }

    /**
     * 替换当前URL参数
     * @param array $params 参数
     * @param bool $scheme
     * @return string
     */
    public function current($params = [], $scheme = false)
    {
        $url_info = parse_url($_SERVER['REQUEST_URI']);
        $path = isset($url_info['path']) ? $url_info['path'] : '';
        $query = isset($url_info['query']) ? $url_info['query'] : '';
        parse_str($query, $query_params);
        foreach ($params as $key => $value) {
            if (isset($query_params[$key])) {
                $query_params[$key] = $value;
            } else {
                $query_params[$key] = $value;
            }
        }

        $query_params = array_map('clean_url_javascript', $query_params);

        $path = urldecode($path);
        $path = clean_url_javascript($path);
        $path = '/' . ltrim($path, '/') . '?' . http_build_query($query_params);
        if ($scheme) {
            $path = "http://{$_SERVER['HTTP_HOST']}" . $path;
        }
        return $path;
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    public function assign($name, $value = ''){
        $this->view->assign($name, $value);
    }

    /**
     * 获取分页样式代码
     */
    function get_page_html(){
        return $this->pages['html'];
    }
}