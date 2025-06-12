<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Console;

use Butterfly\Container\ContainerAwareTrait;

/**
 * 命令基类（对应web环境下的 Controller 类）
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
abstract class Command
{
    use ContainerAwareTrait;

    /**
     * Cli类
     *
     * @var Cli
     */
    protected $cli;

    /**
     * 使用说明信息
     *
     * 例如：
     *        protected static $helpInfo = [
     *            'start'   => [
     *                'params'      => '<param1> <param2> --option1=value1'
     *                'description' => 'Start the command.',
     *            ],
     *            'stop'    => [
     *                'params'      => '<param1> <param2> --option1=value1'
     *                'description' => 'Stop the command.',
     *            ],
     *        ];
     *
     * @var array
     */
    protected $helpInfo = [];

    /**
     * 构造函数
     *
     * @param Cli $cli Cli类
     */
    public function __construct($cli)
    {
        $this->cli = $cli;

        if ($this->cli->param('help', false)) {
            $this->showHelp();
        }
    }

    /**
     * 显示使用帮助信息
     */
    protected function showHelp()
    {
        $cmd = '%y./launch %|%g' . $_SERVER['argv'][1];
        foreach ($this->helpInfo as $key => $value) {
            $action = $key === 'index' ? '' : '/' . $key;

            $this->cli->stdout('' . $value['description'] . ':%|
  ' . $cmd . $action . '%w ' . $value['params'])
                      ->newline();
        }

        $this->cli->exitCode(0);
    }

    /**
     * 调用错误 action 时，显示所有支持的 action
     *
     * @param string $name      方法名
     * @param array  $arguments 参数数组
     */
    public function __call($name, $arguments)
    {
        if ($name === 'index') {
            $this->cli->stderr("Please specific command action.");
        } else {
            $this->cli->stderr(sprintf("Unknown command action '%s'.", $name));
        }
        $this->cli->newline();

        $this->showHelp();
    }
}
