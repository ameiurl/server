<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Console;

use Butterfly\Container\Container;
use Butterfly\Utility\Str;

/**
 * Launcher - 命令行终端下的分发器
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Launcher
{
    /**
     * Cli类
     *
     * @var Cli
     */
    protected $cli;

    /**
     * 依赖注入容器
     *
     * @var Container Container
     */
    protected $container;

    /**
     * 构造函数
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->cli       = new Cli();
        $this->container = $container;
    }

    /**
     * 创建分发器实例
     *
     * @param Container $container
     * @return Launcher
     */
    public static function make(Container $container)
    {
        return new static($container);
    }

    /**
     * 运行命令
     *
     * @param array $arguments 命令行参数
     */
    public function run($arguments)
    {
        // 去掉具名参数（具名参数指由 --= 引入的参数，具名参数可通过 $this->cli->param 获取）
        foreach ($arguments as $key => $value) {
            if (substr($value, 0, 2) === '--') {
                unset($arguments[$key]);
            }
        }

        $this->execute(array_values($arguments));
    }

    /**
     * 执行命令
     *
     * @param array $arguments 命令行参数数组
     */
    protected function execute($arguments)
    {
        if (!empty($arguments)) {
            if (strpos($arguments[0], '/') !== false) {
                list($command, $action) = explode('/', $arguments[0], 2);
            } else {
                $command = $arguments[0];
                $action  = 'index';
            }

            $commandName = '\App\Console\Command' . '\\' .
                Str::pascal($command) . 'Command';
            $commandObj  = new $commandName($this->cli);

            // 设置依赖注入容器
            if (method_exists($commandObj, 'setContainer')) {
                /** @var \Butterfly\Container\ContainerAwareTrait $commandObj */
                $commandObj->setContainer($this->container);
            }

            // 调用 $commandObj 的 $action，$arguments的第一个参数为命令名称需要剔除
            call_user_func_array([$commandObj, Str::camel($action)],
                array_slice($arguments, 1));
        } else {
            $this->showHelp();
        }
    }

    /**
     * 显示用法
     */
    public function showHelp()
    {
        $this->cli->stdout("%yUsage:%|
  php launch <command>[/action] [--option1=value1 --option2=value2 ... argument1 argument2 ...]")
                  ->exitCode(0);
    }
}
