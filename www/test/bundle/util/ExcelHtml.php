<?php
/**
 * Excel导出类（Html）
 * Created by PhpStorm.
 * User: yuzq
 * Date: 2018/3/23
 * Time: 10:49
 */

namespace erp\util;

class ExcelHtml{

    private $_common_style    = '';             //通用样式
    private $_head            = '';             //表头内容
    private $_body            = '';             //表格内容
    private $_head_bgcolor    = '#f3f3f3';      //表头默认背景颜色
    private $_body_bgcolor    = '';             //表格默认背景颜色
    private $_default_width   = 100;            //默认宽度
    Private $_default_align   = 'center';       //默认对齐方式
    private $_default_charset = 'utf-8';        //默认导出编码
    private $_tables          = [];             //一个文件里有多个table时使用
    private $_table_brs       = [];             //table间换行数量

    /**
     * 设置表头背景颜色
     */
    public function set_head_bgcolor($bgcolor = '#f3f3f3'){
        $this->_head_bgcolor = $bgcolor;
    }

    /**
     * 设置表格背景颜色
     */
    public function set_body_bgcolor($bgcolor = ''){
        $this->_body_bgcolor = $bgcolor;
    }

    /**
     * 设置输出字符编码
     */
    public function set_charset($charset = 'utf-8'){
        $this->_default_charset = $charset;
    }

    /**
     * 设置默认对齐方式
     */
    public function set_align($align = 'center'){
        $this->_default_align = $align;
    }

    /**
     * 设置默认宽度
     * @param int $width
     */
    public function set_default_width($width = 100){
        $this->_default_width = $width;
    }

    /**
     * 设置通用样式
     * @param string $style
     */
    public function set_common_style($style = ''){
        $this->_common_style = $style;
    }

    /**
     * 添加一个table
     * @param int $br_num  添加换行数量
     */
    public function add_table($br_num = 0){
        $this->_tables[] = array(
            'head' => $this->_head,
            'body' => $this->_body,
        );

        $this->_table_brs[] = intval($br_num);
        $this->_head = '';
        $this->_body = '';
    }


    /**
     * 添加表头
     * @param array $head_arr = array(
     * // 子元素可以为数组或字符串
     * @example array(
     *               //value：表头名称  width：宽度   colspan:跨列数   rowspan：跨行数  align:对齐方式
     *              array('value' => '序号', 'width' => 100, 'colspan' => 2, 'rowspan' => 2, 'align' => 'center'),
     *              '订单号',
     *              '同行客户'
     *     )
     * )
     *
     */
    public function add_head($head_arr = []){
        $head_html = "<tr>\n";
        if (is_array($head_arr) && !empty($head_arr)){
            foreach ($head_arr as $head){
                if (!is_array($head)){
                    $head_html .= "<th width='" . $this->_default_width . "' bgcolor='" . $this->_head_bgcolor . "'>" . $this->get_value($head)  . "</th>\n";
                }else {
                    $head_html .= "<th bgcolor='" . $this->_head_bgcolor . "'";

                    $width = isset($head['width']) ? $head['width'] : $this->_default_width;
                    $head_html .= " width='{$width}' ";

                    if (isset($head['colspan'])){
                        $head_html .= " colspan='{$head['colspan']}' ";
                    }

                    if (isset($head['rowspan'])){
                        $head_html .= " rowspan='{$head['rowspan']}' ";
                    }

                    if (isset($head['align'])){
                        $head_html .= " align='{$head['align']}' ";
                    }

                    $head_html .= " >" . $this->get_value($head['value']) . "</th>\n";
                }
            }
        }

        $head_html   .= "</tr>\n";
        $this->_head .= $head_html;
    }

    /**
     * 添加表格内容
     * @param array $body_arr = array(
     * // 子元素可以为数组或字符串
     * @example array(
     *         //value：单元格的值  align：对齐方式   colspan:跨列数   rowspan：跨行数  style：自定义样式  deal_long_num：是否处理长度较长的数字字符串（防止转换为科学计数法）
     *         array(
     *              'value' => 1,
     *              'align' => 'left',
     *              'colspan' => 2,
     *              'rowspan' => 2,
     *              'style' => 'width:100px;height:50px',
     *              'deal_long_num' => 1,
     *          ),
     *         '往来单位'
     *     )
     * )
     *
     */
    public function add_body($body_arr = []){
        $body_html = "<tr>\n";
        if (is_array($body_arr) && !empty($body_arr)){
            foreach ($body_arr as $body){
                if (!is_array($body)){
                    $body_html .= "<td bgcolor='" . $this->_body_bgcolor . "' align='" . $this->_default_align . "' >" . $this->get_value($body) . "</td>\n";
                }else {
                    $align = isset($body['align']) ? $body['align'] : $this->_default_align;
                    $style = isset($body['style']) ? $body['style'] : '';
                    $deal_long_num = isset($body['deal_long_num']) ? $body['deal_long_num'] : 0;

                    if (!empty($style)){
                        $style = $deal_long_num ? (rtrim($style, ';"') . ";mso-number-format:'\@';" . '"') : $style;
                    }else{
                        $style = $deal_long_num ? 'style="mso-number-format:\'\@\';"' : '';
                    }

                    $body_html .= "<td bgcolor='" . $this->_body_bgcolor . "' align='" . $align ."' " . $style . "";

                    if (isset($body['colspan'])){
                        $body_html .= " colspan='{$body['colspan']}' ";
                    }

                    if (isset($body['rowspan'])){
                        $body_html .= " rowspan='{$body['rowspan']}' ";
                    }

                    $body_html .= ">" . $this->get_value($body['value'])  . "</td>\n";
                }
            }
        }

        $body_html   .= "</tr>\n";
        $this->_body .= $body_html;
    }

    /**
     * 下载excel文件
     */
    public function downLoad($filename = ''){
        $this->add_table();
        $chare_set     = $this->_default_charset;
        $down_content  = '<meta http-equiv="Content-Type" content="text/html; charset=' . $chare_set . '" />' . "\n";
        $down_content .= $this->_common_style;

        foreach ($this->_tables as $t_key => $table){
            if (empty($table['head']) && empty($table['body'])){
                continue;
            }

            $down_content .= '<table border="1">' . "\n";
            $down_content .= $table['head'] . "\n";
            $down_content .= $table['body'] . "\n";
            $down_content .= '</table>';

            if ($this->_table_brs[$t_key]){
                for($i = 0; $i < $this->_table_brs[$t_key]; $i++){
                    $down_content .= "<br/>";
                }
            }

            $down_content .= "\n";
        }

        if(!$filename) {
            $filename = date('YmdHis',time()).'.xls';
        }

        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE){  //IE浏览器
            header('Content-Type: application/excel');  //限制为Excel导出，想导出其他文件，可以扩展
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header("Content-Transfer-Encoding: binary");
            header('Pragma: public');
            header("Content-Length: " . strlen($down_content));
        }else {
            header('Content-Type: application/excel');  //限制为Excel导出，想导出其他文件，可以扩展
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Pragma: no-cache');
            header("Content-Length: " . strlen($down_content));
        }

        exit($down_content);
    }

    /**
     * 字符编码转换
     * @param $value
     * @return string
     */
    private function get_value($value){
        if (strtolower($this->_default_charset) != 'utf-8'){
            return iconv('utf-8', $this->_default_charset, $value);
        }else{
            return $value;
        }
    }
}