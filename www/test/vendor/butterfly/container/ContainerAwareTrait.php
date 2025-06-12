<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Container;

use RuntimeException;

/**
 * 支持设置依赖注入容器，及从容器中获取存储项的 trait
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
trait ContainerAwareTrait
{
    /**
     * 依赖注入容器
     *
     * @var Container;
     */
    protected $container;

    /**
     * 已经从容器中获取到的项目的缓存
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * 设置依赖注入容器
     *
     * @param $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 获取存储在容器中的项目
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (!isset($this->resolved[$key])) {
            if (!isset($this->container[$key])) {
                $tpl = '%s::%s() Unable to resolve [ %s ].';
                throw new RuntimeException(vsprintf($tpl,
                    [__TRAIT__, __FUNCTION__, $key]));
            }

            $this->resolved[$key] = $this->container[$key];
        }

        return $this->resolved[$key];
    }
}
