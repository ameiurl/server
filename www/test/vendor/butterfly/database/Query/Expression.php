<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Database\Query;

/**
 * SQL 表达式
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Expression
{
    /**
     * 数据库表达式的值
     *
     * @var string
     */
    protected $value = '';

    /**
     * 创建 Expression 对象实例
     *
     * @param string $value 数据库表达式的值
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * 获取该 Expression 数据库表达式的值
     *
     * @return string
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * 获取该 Expression 数据库表达式的值
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}
