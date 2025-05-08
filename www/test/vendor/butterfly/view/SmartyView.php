<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\View;

use Butterfly\Utility\Str;
use Smarty;

/**
 * Smarty 视图引擎
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class SmartyView implements ViewInterface
{
    /**
     * 模板文件扩展名
     *
     * @var string
     */
    protected $tplExt = 'tpl';

    /**
     * @var Smarty
     */
    protected $smarty = null;

    /**
     * 构造函数
     *
     * @param array $config 视图引擎配置
     */
    public function __construct($config)
    {
        $this->smarty = new Smarty();
        foreach ($config['config'] as $key => $val) {
            $method = 'set' . Str::pascal($key);
            $this->smarty->$method($val);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function assign($name, $value = null)
    {
        $this->smarty->assign($name, $value);
    }

    /**
     * executes & displays the template results
     *
     * @param string $resourceName
     * @param string $cacheId
     * @param string $compileId
     * @param object $parent
     */
    public function display(
        $resourceName,
        $cacheId = null,
        $compileId = null,
        $parent = null
    ) {
        $this->smarty->display($resourceName . '.' . $this->tplExt,
            $cacheId, $compileId, $parent);
    }

    /**
     * executes & returns or displays the template results
     *
     * @param string $resourceName
     * @param string $cacheId
     * @param string $compileId
     * @param object $parent
     * @return mixed
     */
    public function fetch(
        $resourceName,
        $cacheId = null,
        $compileId = null,
        $parent = null
    ) {
        return $this->smarty->fetch($resourceName . '.' . $this->tplExt,
            $cacheId, $compileId, $parent);
    }
}
