<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\View;

/**
 * 视图引擎接口
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
interface ViewInterface
{
    /**
     * 模板变量赋值
     *
     * @param  string $name  变量名
     * @param  mixed  $value 变量值
     * @return ViewInterface
     */
    public function assign($name, $value = null);

    /**
     * 显示模板文件对应的页面
     *
     * @param  string $viewFile 模板文件名称或相对路径
     */
    public function display($viewFile);
}
