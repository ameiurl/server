<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\View;

/**
 * PHP 视图引擎
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class PhpView implements ViewInterface
{
    /**
     * 视图文件存储路径
     *
     * @var string
     */
    protected $viewPath = '';

    /**
     * 模板文件变量数组
     *
     * @var array
     */
    protected $vars;

    /**
     * 构造函数
     *
     * @param array $config 视图引擎配置
     */
    public function __construct($config)
    {
        $this->viewPath = $config['path'];
    }

    /**
     * {@inheritdoc}
     */
    public function assign($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->vars[$k] = $v;
            }
        } else {
            $this->vars[$name] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function display($viewFile)
    {
        $oldPath = getcwd();
        chdir($this->viewPath);

        extract($this->vars);
        include "$viewFile.php";

        chdir($oldPath);
    }
}
