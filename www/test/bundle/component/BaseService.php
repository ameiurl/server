<?php
namespace My\component;

/**
 * 所有的service 都必须是单例，表示事务脚本
 * Class BaseService
 * @package erp\component
 */
abstract class BaseService extends BaseComponent
{
    /**
     * 上下文数据
     */
    protected $token;

    private static $_instances = [];

    /**
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if(!isset(self::$_instances[$class])){
            self::$_instances[$class] = new static();
        }
        return self::$_instances[$class];
    }

    protected function __construct()
    {
        //$this->token = Context::getInstance()->getToken();
    }
}
