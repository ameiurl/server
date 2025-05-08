<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Console;

/**
 * Cli - 命令行终端交互类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Cli
{
    /**
     * 样式代码映射关系
     *     前景色/背景色
     *          r/R: Red
     *          g/G: Green
     *          b/B: Blue
     *          c/C: Cyan
     *          m/M: Magenta
     *          y/Y: Yellow
     *          k/K: Black
     *          w/W: White
     *          d/D: Default
     *     |          重置样式
     *     * / _      Bold / Italic / Underline
     *     ^          Blink
     *     ?          Reverse
     *
     * @var array
     */
    protected static $codeMap = [
        '%|' => "\x1b[0m",  // "\x1b" == "\x033"
        '%*' => "\x1b[1m",
        '%/' => "\x1b[3m",
        '%_' => "\x1b[4m",
        '%^' => "\x1b[5m",
        '%?' => "\x1b[7m",

        // 前景色
        '%k' => "\x1b[30m",
        '%r' => "\x1b[31m",
        '%g' => "\x1b[32m",
        '%y' => "\x1b[33m",
        '%b' => "\x1b[34m",
        '%m' => "\x1b[35m",
        '%c' => "\x1b[36m",
        '%w' => "\x1b[37m",

        '%d' => "\x1b[39m",

        '%!k' => "\x1b[90m",
        '%!r' => "\x1b[91m",
        '%!g' => "\x1b[92m",
        '%!y' => "\x1b[93m",
        '%!b' => "\x1b[94m",
        '%!m' => "\x1b[95m",
        '%!c' => "\x1b[96m",
        '%!w' => "\x1b[97m",

        // 背景色
        '%K'  => "\x1b[40m",
        '%R'  => "\x1b[41m",
        '%G'  => "\x1b[42m",
        '%Y'  => "\x1b[43m",
        '%B'  => "\x1b[44m",
        '%M'  => "\x1b[45m",
        '%C'  => "\x1b[46m",
        '%W'  => "\x1b[47m",

        '%D' => "\x1b[49m",

        '%!K' => "\x1b[100m",
        '%!R' => "\x1b[101m",
        '%!G' => "\x1b[102m",
        '%!Y' => "\x1b[103m",
        '%!B' => "\x1b[104m",
        '%!M' => "\x1b[105m",
        '%!C' => "\x1b[106m",
        '%!W' => "\x1b[107m",
    ];

    /**
     * 输出信息到标准输出(默认为终端的屏幕)
     *
     * @param  string $message 要输出的信息
     * @return Cli
     */
    public function stdout($message)
    {
        fwrite(STDOUT, $this->stylize($message) . PHP_EOL);

        return $this;
    }

    /**
     * 输出信息到标准错误(默认为终端的屏幕)
     *
     * @param  string $message 要输出的信息
     * @return Cli
     */
    public function stderr($message)
    {
        fwrite(STDERR, $this->stylize('%r' . $message) . PHP_EOL);

        return $this;
    }

    /**
     * 输出空行
     *
     * @param  int $lines 要输出的空行数
     * @return Cli
     */
    public function newline($lines = 1)
    {
        fwrite(STDOUT, str_repeat(PHP_EOL, $lines));

        return $this;
    }

    /**
     * 确认消息
     *
     * @param string $question 需要用户回答的问题
     * @return string
     */
    public function confirm($question)
    {
        fwrite(STDOUT, $question . ' [Y/N] ');

        $input = trim(fgets(STDIN));

        switch ($input) {
            case 'Y':
                return true;
                break;

            case 'N':
                return false;
                break;

            default:
                return $this->confirm($question);
                break;
        }
    }

    /**
     * 提示用户输入内容
     *
     * @param  string $question 需要用户回答的问题
     * @return string 用户输入的内容
     */
    public function input($question)
    {
        fwrite(STDOUT, $question . ' ');

        return trim(fgets(STDIN));
    }

    /**
     * 获取用户输入的具名参数值 (--<name>=<value>)
     *
     * @param  string $name    参数名
     * @param  string $default 默认的参数值
     * @return string
     */
    public function param($name, $default = null)
    {
        static $parameters;
        if ($parameters === null) {
            $parameters = [];

            foreach ($_SERVER['argv'] as $arg) {
                if (substr($arg, 0, 2) === '--') {
                    $arg                 = explode('=', substr($arg, 2), 2);
                    $parameters[$arg[0]] = isset($arg[1]) ? $arg[1] : true;
                }
            }
        }

        return isset($parameters[$name]) ? $parameters[$name] : $default;
    }

    /**
     * 返回错误代码（0为正常退出，非0为异常）
     *
     * @param int $code 错误代码 0~254（PHP允许的错误代码范围）
     */
    public function exitCode($code)
    {
        exit($code);
    }

    /**
     * 判断当前环境是否应该设置字体颜色、背景色
     *
     * @param bool $forceStyle 是否强制使用样式
     * @return bool
     */
    protected static function shouldStylize($forceStyle = false)
    {
        if ($forceStyle) {
            return true;
        }

        if (DIRECTORY_SEPARATOR == '\\') {  // Windows 平台
            return getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON'
            || getenv('BABUN_HOME') !== false;
        }

        // 判断是否输出被重定向
        // ref. http://stackoverflow.com/a/11327451
        $stat = fstat(STDOUT);
        $mode = $stat['mode'] & 0170000;
        // S_IFMT     0170000   bit mask for the file type bit field
        return $mode === 0020000;
        // S_IFCHR    0020000   character device
        // ref. http://man7.org/linux/man-pages/man2/stat.2.html
    }

    /**
     * 彩色输出文本
     *
     * @param string $text       要输出的文本
     * @param bool   $forceStyle 是否强制使用样式
     * @return string
     */
    public function stylize($text, $forceStyle = false)
    {
        if (!static::shouldStylize($forceStyle)) {
            $search     = array_keys(self::$codeMap);
            $destylized = str_replace($search, '', $text);
            return $destylized;
        }

        $text .= '%|';  // 重置成默认样式
        $text = str_replace('%%', '%¾', $text);  // %% 代表不需要处理的 %，暂时替换成临时的 %¾
        $text = strtr($text, self::$codeMap);
        $text = str_replace('%¾', '%', $text);  // 把临时的 %¾ 替换回 %

        return $text;  // 重置样式成默认的
    }
}
