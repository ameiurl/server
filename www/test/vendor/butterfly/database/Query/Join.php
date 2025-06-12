<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Database\Query;

/**
 * Join 表连接查询对象
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Join
{
    /**
     * 连接类型
     *
     * @var string
     */
    public $type = null;

    /**
     * 要连接的表
     *
     * @var string
     */
    public $table = null;

    /**
     * on 连接条件语句
     *
     * @var array
     */
    public $clauses = [];

    /**
     * 构造函数
     *
     * @param string $type  连接类型
     * @param string $table 要连接的表
     */
    public function __construct(string $type, string $table)
    {
        $this->type  = $type;
        $this->table = $table;
    }

    /**
     * 添加 ON 连接条件
     *
     * @param  string $column1
     * @param  string $column2
     * @return Join
     */
    public function on(string $column1, string $column2)
    {
        $operator  = '=';
        $connector = 'AND';

        $this->clauses[] = compact('column1', 'operator', 'column2',
            'connector');

        return $this;
    }
}
