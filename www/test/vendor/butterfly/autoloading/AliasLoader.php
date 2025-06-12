<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Autoloading;

/**
 * 类的别名自动加载
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class AliasLoader
{
    /**
     * 类别名
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * 是否该类别名的自动加载器已经注册过
     *
     * @var bool
     */
    protected $registered = false;

    /**
     * AliasLoader constructor.
     *
     * @param array $aliases
     */
    public function __construct(array $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * 自动加载类别名
     *
     * @param string $alias 类别名
     * @return bool 返回 false
     */
    public function load(string $alias) : bool
    {
        if (isset($this->aliases[$alias])) {
            return class_alias($this->aliases[$alias], $alias);
        }

        return false;
    }

    /**
     * 注册别名加载器
     */
    public function register()
    {
        if (!$this->registered) {
            spl_autoload_register([$this, 'load'], true, true);
            $this->registered = true;
        }
    }
}
