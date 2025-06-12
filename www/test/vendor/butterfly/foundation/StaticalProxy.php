<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Foundation;

/**
 * 把类转换成静态调用方式
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
abstract class StaticalProxy
{
    /**
     * 应用实例
     *
     * @var Application
     */
    protected static $app;

    /**
     * Proxy 对应的对象实例
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * 设置 Proxy 关联的应用实例
     *
     * @param Application $app
     */
    public static function setApplication(Application $app)
    {
        static::$app = $app;
    }

    /**
     * 获取 Proxy 对应的指定名称的实例
     *
     * @param string $name
     * @return mixed
     */
    protected static function getInstance(string $name)
    {
        if (!isset(static::$instances[$name])) {
            static::$instances[$name] = static::$app->getContainer()[$name];
        }

        return static::$instances[$name];
    }

    /**
     * 获取 Proxy 对应的 Service 名称
     *
     * @return mixed
     */
    protected static function getAccesor()
    {
        $tpl = '%s(): Proxy does not implemented getAccessor method.';
        throw new \RuntimeException(vsprintf($tpl, [__METHOD__]));
    }

    /**
     * 调用 Proxy 对应的实例的方法
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $instance = static::getInstance(static::getAccesor());

        return $instance->$name(...$arguments);
    }
}
