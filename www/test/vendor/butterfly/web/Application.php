<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Web;

use RuntimeException;
use Butterfly\Foundation\Application as BaseApplication;
use Butterfly\Utility\Str;

/**
 * 传统的 MVC 结构支持（不支持伪静态 URL）
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Application extends BaseApplication
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $c      = $this->config['app.trigger.controller'] ?? 'action';
        $action = isset($_REQUEST[$c]) ? $_REQUEST[$c] : 'index';
        $this->dispatchAction($action);
    }

    /**
     * 获取 $action 对应的控制器名
     *
     * @param string $action
     * @return string
     */
    protected function controllerName(string $action) : string
    {
        $parts = explode('-', $action);
        $parts = array_map(function ($part) {
            return Str::pascal($part);
        }, $parts);

        // ref. http://cn2.php.net/manual/en/language.namespaces.dynamic.php
        // 单引号时，只需要一个反斜杠，双引号时必须用2个反斜杠
        $ns = $this->config['app.namespace'] ?? 'App';
        return '\\' . $ns .'\Controller' . '\\' . implode('\\', $parts) . 'Controller';
    }

    /**
     * 分发 $action 请求
     *
     * @param string $action
     */
    protected function dispatchAction(string $action)
    {
        $controllerName = $this->controllerName($action);
        if (getenv('APP_ENV') === 'prod' && !class_exists($controllerName)) {
            $controllerName = '\App\Controller\IndexController';
            if (!class_exists($controllerName)) {
                header('HTTP/1.1 404 Not Found');
                exit;
            }
        }
        $controller = new $controllerName();

        // 设置依赖注入容器
        if (method_exists($controller, 'setContainer')) {
            /** @var \Butterfly\Container\ContainerAwareTrait $controller */
            $controller->setContainer($this->container);
        }

        // 执行前置逻辑
        if (method_exists($controller, 'before')) {
            $controller->before();
        }

        $m      = $this->config['app.trigger.method'] ?? 'todo';
        $method = $_REQUEST[$m] ?? 'index';
        $method = Str::camel($method);
        if (!method_exists($controller, $method)) {
            throw new RuntimeException("$action::$method does not exist.");
        }

        $controller->$method();

        // 执行后置逻辑
        if (method_exists($controller, 'after')) {
            $controller->after();
        }
    }
}
