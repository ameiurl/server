<?php
/**
 * Excel导出类（Xml）
 * Created by PhpStorm.
 * User: yuzq
 * Date: 2018/3/23
 * Time: 11:39
 */

namespace erp\util;

class ExcelXml{

    private $_row_arr          = [];             //表格内容
    private $_default_color    = '#f3f3f3';      //默认背景颜色
    private $_default_width    = 150;            //默认宽度
    Private $_default_align    = 'Center';       //默认对齐方式
    private $_default_charset  = 'utf-8';        //默认导出编码
    public  $active_sheet      = 0;              //当前sheet
    public  $sheet_title_arr   = [];             //所有sheet名称
    public  $styles            = '';             //表格样式
    public  $column_arr        = [];             //列设置
    public  $id_index          = 1;              //列ID
    public  $rowspan_arr       = [];             //跨行影响点，比如第一列跨三行，那么第二、三行的第一个元素要往后挪一位，才能正常显示

    /**
     * 设置背景颜色
     */
    public function set_bgcolor($bgcolor = '#f3f3f3'){
        $this->_default_color = $bgcolor;
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
    public function set_align($align = 'Center'){
        $this->_default_align = ucfirst($align);
    }

    /**
     * 设置当前sheet
     */
    public function set_active_sheet($title = ''){
        $title = $this->get_value($title);
        $this->active_sheet = !empty($title) ? $title : time();
        $this->sheet_title_arr[$title] = $title;
        $this->rowspan_arr = [];  //重置
    }

    /**
     * 添加表头
     */
    public function add_head($data = []){
        $this->add_row($data, 1);
    }

    /**
     * 添加表格内容
     */
    public function add_body($data = []){
        $this->add_row($data, 0);
    }

    /**
     * 添加表格内容
     * @param array $data = array(
     *     //array(array(字段值, 对齐方式, 样式, 其他设置))  //对齐方式、样式、其他设置（如跨列，跨行等）可选
     *     array(
     *         '列表值1',
     *         array('value' => '列表值2', 'align' => 'left', 'width' => 200,  'colspan' => 2, 'rowspan' => 2, 'color' => '#000000'), //对齐方式、跨列，跨行 颜色 等可选
     *         '列表值3',
     *     )
     * )
     *
     */
    private function add_row($data = [], $is_head = 0){
        $is_head = intval($is_head);
        $xml = '<Row>';
        if (is_array($data) && !empty($data)){
            if (empty($this->active_sheet)){
                $this->set_active_sheet();
            }

            if (!isset($this->column_arr[$this->active_sheet])){
                $this->column_arr[$this->active_sheet] = [];
            }

            $index       = 1;
            $id_index    = $this->id_index;
            $rowspan_arr = $this->rowspan_arr;
            $index_flags = [];  //已填写数据的单元格
            foreach($data as $row){
                $s_id  = 's' . $id_index++;
                $style = '';

                $ss_index = 0;
                if(!isset($rowspan_arr[$index])){
                    $rowspan_arr[$index] = 0;
                }
                if(!empty($rowspan_arr[$index]) || !empty($index_flags[$index])){
                    for($i = $index + 1; $i <= count($rowspan_arr); $i++){
                        if(empty($rowspan_arr[$i]) && empty($index_flags[$i])){
                            $ss_index = $i;
                            break;
                        }
                    }
                    $rowspan_arr[$index]--;
                }

                if(!is_array($row)){
                    if(!empty($ss_index)){
                        $xml .= "<Cell ss:Index='{$ss_index}'>";
                    }else{
                        $xml .= "<Cell>";
                    }
                    $xml .= "<Data ss:Type='String'>" . $this->get_value($row) . "</Data></Cell>\n";
                }else{
                    if ($is_head){
                        //宽度
                        if (isset($row['width'])){
                            $width = intval($row['width']);
                            $index = $ss_index ? $ss_index : $index;
                            $this->column_arr[$this->active_sheet][$index] = "<Column ss:Index='" . $index . "' ss:AutoFitWidth='0' ss:Width='{$width}' />\n";
                        }
                    }

                    //背景色
                    if (isset($row['color'])){
                        $style .= "<Interior ss:Color='{$row['color']}' ss:Pattern='Solid'/>";
                    }

                    //对齐方式
                    if (isset($row['align'])){
                        $align  = ucfirst($row['align']);
                        $style .= "<Alignment ss:Horizontal='{$align}'/>";
                    }

                    if(!empty($ss_index)){
                        $xml .= "<Cell ss:Index='{$ss_index}'";
                    }else{
                        $xml .= "<Cell";
                    }

                    if(!empty($style)){
                        $xml .= " ss:StyleID='{$s_id}'";
                    }

                    //跨列
                    if(isset($row['colspan'])){
                        $colspan = intval($row['colspan']) - 1;
                        $colspan = $colspan >= 0 ? $colspan : 0;
                        $xml .= " ss:MergeAcross='{$colspan}'";
                    }

                    //跨行
                    if(isset($row['rowspan'])){
                        $rowspan = intval($row['rowspan']) - 1;
                        $rowspan = $rowspan >= 0 ? $rowspan : 0;
                        $xml .= " ss:MergeDown='{$rowspan}'";

                        $rowspan_arr[$index] += $rowspan;
                    }

                    $xml .= " ><Data ss:Type='String'>" . $this->get_value($row['value']) . "</Data></Cell>\n";
                }

                if(!empty($ss_index)){
                    $index_flags[$ss_index] = 1;
                }else{
                    $index_flags[$index] = 1;
                }

                if(!empty($style)){
                    $this->styles  .= ("<Style ss:ID='{$s_id}'>" . $style . "</Style>\n");
                }

                $index++;
            }
        }
        $xml .= "</Row>\n";

        if (!isset($this->_row_arr[$this->active_sheet])){
            $this->_row_arr[$this->active_sheet] = '';
        }

        $this->_row_arr[$this->active_sheet] .= $xml . "\n";
        $this->id_index    = $id_index;
        $this->rowspan_arr = $rowspan_arr;
    }

    /**
     * 下载excel文件（多个sheet）
     */
    public function downLoad($filename = ''){
        if(!$filename) {
            $filename = date('YmdHis',time()).'.xls';
        }

        $chare_set = $this->_default_charset;
        $this->set_header($filename);
        $headers = "<?xml version=\"1.0\" encoding='{$chare_set}'?\>
                    <Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
                    xmlns:o=\"urn:schemas-microsoft-com:office:office\"
                    xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
                    xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
                    xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
        echo stripslashes($headers);

        //默认样式
        $default_style = '<Style ss:ID="Default" ss:Name="Normal">
                          <Alignment ss:Horizontal="' . $this->_default_align . '" ss:Vertical="' . $this->_default_align . '"/>
                          <Font ss:FontName="宋体" ss:Size="11" ss:Color="#000000"/>
                          </Style>';
        echo "\n<Styles>\n" . $default_style . "\n" . $this->styles . "</Styles>";

        foreach($this->sheet_title_arr as $title){
            $title = $this->get_value($title);
            echo "\n<Worksheet ss:Name='{$title}'>\n<Table ss:StyleID='Default' ss:DefaultColumnWidth='{$this->_default_width}' ss:DefaultRowHeight='25'>\n";
            echo implode('', $this->column_arr[$title]);
            echo $this->_row_arr[$title];
            echo "</Table>\n</Worksheet>\n";
        }
        echo "</Workbook>";
        exit();
    }

    private function get_value($value){
        if ($this->_default_charset != 'utf-8'){
            return iconv('utf-8', $this->_default_charset, $value);
        }else{
            return $value;
        }
    }

    /**
     * 设置表头
     */
    private function set_header($filename){
        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE){  //IE浏览器
            header('Content-Type: application/excel');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header("Content-Transfer-Encoding: binary");
            header('Pragma: public');
            //header("Content-Length: ".strlen($down_content));
        }else {
            header('Content-Type: application/excel');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Pragma: no-cache');
            //header("Content-Length: ".strlen($down_content));
        }
    }
}