<?php
/**
 * Excel导出类（基于XLSXWriter）
 * Created by PhpStorm.
 * User: yuzq
 * Date: 2018/10/10
 * Time: 14:00
 */

namespace erp\util;

class ExcelWriter{

    private $_writeObj;                           //XLSXWriter对象
    private $_head_arr          = [];             //表格表头
    private $_body_arr          = [];             //表体内容
    private $_default_color     = '#f3f3f3';      //默认背景颜色
    private $_default_width     = 20;             //默认宽度
    Private $_default_align     = 'center';       //默认对齐方式
    Private $_default_font      = '微软雅黑';        //默认字体
    Private $_default_font_size = 11;             //默认字体
    public  $active_sheet       = 0;              //当前sheet
    public  $sheet_title_arr    = [];             //所有sheet名称
    public  $styles             = [];             //表格样式
    public  $merged_cell_arr    = [];             //跨行/跨列
    public  $width_arr          = [];             //宽度
    public  $type_relation       = [];             //数据类型转换

    public function __construct()
    {
        $this->_writeObj = new XLSXWriter();
        $this->type_relation = [
            'int'      => 'integer',  //整数
            'float'    => '#,##0.00',  //小数
            'string'   => 'string',  //字符串
            'date'     => 'YYYY-MM-DD',  //日期
            'datetime' => 'YYYY-MM-DD HH:MM:SS',  //日期时间
            'rmb'      => '[$￥-411]#,##0.00;[RED]-[$￥-411]#,##0.00',  //人民币
            'dollar'   => '[$$-1009]#,##0.00;[RED]-[$$-1009]#,##0.00',  //美元
            'euro'     => '#,##0.00 [$€-407];[RED]-#,##0.00 [$€-407]',  //欧元
        ];
    }

    /**
     * 设置当前sheet
     */
    public function set_active_sheet($title = ''){
        $title = trim($title);
        $this->active_sheet = !empty($title) ? $title : 'sheet' . (count($this->sheet_title_arr) + 1);
        $this->sheet_title_arr[$this->active_sheet] = $this->active_sheet;
    }

    /**
     * 添加一个空白行
     */
    public function add_blank_row(){
        $this->add_body([[]]);
    }

    /**
     * 添加表头
     * @param array $data = array(
     * // 子元素可以为数组或字符串
     * @example array(
     *              array(
     *                  'value' => '序号',  //表头名称
     *                  'width' => 20,  //宽度
     *                  'colspan' => 2,  //跨列
     *                  'rowspan' => 2,  //跨行
     *                  'type' => 'int|float|string'  //数字、小数、字符串（参考 $this->type_relation）
     *                 ),
     *              '订单号',
     *              '同行客户'
     *     )
     * )
     *
     */
    public function add_head($data = []){
        if (!$this->sheet_title_arr){
            $this->set_active_sheet();
        }

        $data = array_values($data);
        foreach ($data as $key => $item){
            if (!is_array($item)){
                $item = [
                    'value' => $item
                ];
            }

            if (!$item['type'] || !array_key_exists($item['type'], $this->type_relation)){
                $item['type'] = 'string';
            }else{
                $item['type'] = $this->type_relation[$item['type']];
            }

            if ($item['rowspan'] > 1){
                $this->merged_cell_arr[$this->active_sheet][] = [
                    'start_row' => 1,
                    'start_col' => $key,
                    'end_row'   => $item['rowspan'],
                    'end_col'   => $key,
                ];
            }

            if ($item['colspan'] > 1){
                $this->merged_cell_arr[$this->active_sheet][] = [
                    'start_row' => 1,
                    'start_col' => $key,
                    'end_row'   => 1,
                    'end_col'   => $key + $item['colspan'] - 1,
                ];
            }

            $this->width_arr[$this->active_sheet][] = $item['width'] ? $item['width'] : $this->_default_width;
            $this->_head_arr[$this->active_sheet][$item['value']] = $item['type'];
        }
    }

    /**
     * 添加表体内容
     * @param array $data = array(
     * // 子元素可以为数组或字符串
     * @example array(
     *              array(
     *                  'value'   => '1',  //值
     *                  'colspan' => 2,  //跨列
     *                  'rowspan' => 2,  //跨行
     *                  'align' => 'center|left|right',  //对齐方式
     *                  'style' => [
     *                        'font'        => 'Arial',     //字体（Arial, Times New Roman, Courier New, Comic Sans MS）
     *                        'font-size'  => 11,         //字号
     *                        'font-style' => 'bold'      //字体类型（bold-加粗, italic-斜体, underline-加下划线, strikethrough-加删除线）
     *                        'color'      => '#f00'      //字体颜色【十六进制值】
     *                        'fill'       => '#f3f3f3'  //背景色
     *                   ]
     *                 ),
     *              '订单号',
     *              '同行客户'
     *     )
     * )
     *
     */
    public function add_body($data = []){
        if (!$this->sheet_title_arr){
            $this->set_active_sheet();
        }

        $data = array_values($data);
        $body_count = count($this->_body_arr[$this->active_sheet]);
        foreach ($data as $key => $item){
            if (!is_array($item)){
                $item = [
                    'value' => $item
                ];
            }

            $style = [
                'valign'       => 'center',
                'wrap_text'    => true,
                'border'       => 'left,right,top,bottom',
                'border-style' => 'thin',
            ];
            if ($item['style'] && is_array($item['style'])){
                $style = array_merge($style, $item['style']);
            }

            if ($item['font']){
                $style['font'] = $item['font'];
            }else{
                $style['font'] = $this->_default_font;
            }

            if ($item['font-size']){
                $style['font-size'] = $item['font-size'];
            }else{
                $style['font-size'] = $this->_default_font_size;
            }

            if ($item['fill']){
                $style['fill'] = $item['fill'];
            }

            if ($item['font-style']){
                $style['font-style'] = $item['font-style'];
            }

            //对齐方式
            if ($item['align']){
                $item['align'] = strtolower($item['align']);
                if ($item['align'] == 'center'){
                    $style['halign'] = 'center';
                }elseif ($item['align'] == 'left'){
                    $style['halign'] = 'left';
                }elseif ($item['align'] == 'right'){
                    $style['halign'] = 'right';
                }else{
                    $style['halign'] = $this->_default_align;
                }
            }
            $this->styles[$this->active_sheet][$body_count][] = $style;

            $this->_body_arr[$this->active_sheet][$body_count][] = $item['value'];

            if ($item['rowspan'] > 1){
                $this->merged_cell_arr[$this->active_sheet][] = [
                    'start_row' => 1 + $body_count,
                    'start_col' => $key,
                    'end_row'   => $body_count + $item['rowspan'],
                    'end_col'   => $key,
                ];
            }

            if ($item['colspan'] > 1){
                $this->merged_cell_arr[$this->active_sheet][] = [
                    'start_row' => 1 + $body_count,
                    'start_col' => $key,
                    'end_row'   => 1 + $body_count,
                    'end_col'   => $key + $item['colspan'] - 1,
                ];

                for($i = 1; $i < $item['colspan']; $i++){
                    $this->styles[$this->active_sheet][$body_count][] = [];
                    $this->_body_arr[$this->active_sheet][$body_count][] = '';
                }
            }
        }
    }

    /**
     * 下载excel文件（多个sheet）
     */
    public function downLoad($filename = ''){
        if(!$filename) {
            $filename = date('YmdHis',time()).'.xlsx';
        }

        $this->set_header($filename);

        foreach ($this->sheet_title_arr as $sheet){
            //添加表头
            if ($this->_head_arr[$sheet]){
                //表头数据、配置
                $this->_writeObj->writeSheetHeader(
                    $sheet,
                    $this->_head_arr[$sheet],
                    [
                        //'suppress_row' => true,
                        'widths'       => $this->width_arr[$sheet],
                        'font'         => $this->_default_font,
                        'font-size'    => $this->_default_font_size,
                        'font-style'   => 'bold',
                        'fill'         => $this->_default_color,
                        'halign'       => 'center',
                        'valign'       => 'center',
                        'border'       => 'left,right,top,bottom',
                        'border-style' => 'thin',
                    ]);
            }

            //添加表体
            if ($this->_body_arr[$sheet]){
                foreach ($this->_body_arr[$sheet] as $key => $body){
                    $style = isset($this->styles[$sheet][$key]) ? $this->styles[$sheet][$key] : [];
                    $this->_writeObj->writeSheetRow($sheet, $body, $style);
                }
            }

            //跨行、跨列
            if ($this->merged_cell_arr[$sheet]){
                foreach ($this->merged_cell_arr[$sheet] as $cell){
                    $this->_writeObj->markMergedCell($sheet, $cell['start_row'], $cell['start_col'], $cell['end_row'], $cell['end_col']);
                }
            }

        }

        $this->_writeObj->writeToStdOut();
        exit;
    }

    /**
     * 设置文件header
     */
    private function set_header($filename){
        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE){  //IE浏览器
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

        }else {
            header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
        }
    }
}