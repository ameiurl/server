<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Database\Query;

/**
 * SQL 函数
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Fn
{
    /**
     * 函数名
     *
     * @var string
     */
    public $fn = '';

    /**
     * 函数参数
     *
     * @var array
     */
    public $params = [];

    /**
     * 该函数表达式的别名
     *
     * @var string
     */
    public $alias = null;

    /**
     * 构造函数
     *
     * @param string       $fn     函数名
     * @param string|array $params 函数参数
     * @param string       $alias  别名
     */
    public function __construct(string $fn, $params, $alias = null)
    {
        $this->fn = $fn;
        $this->params = (array) $params;
        $this->alias = $alias;
    }
}
