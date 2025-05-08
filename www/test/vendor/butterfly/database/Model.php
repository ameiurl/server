<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Database;

use RuntimeException;

/**
 * 模型层基类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Model
{
    /**
     * 采用的数据库连接
     *
     * @var string
     */
    protected static $dbIndex = null;

    /**
     * 表名(不包含表前缀)
     *
     * @var string
     */
    protected static $table = '';

    /**
     * 数据库表前缀
     *
     * @var string
     */
    protected static $tablePrefix = '';

    /**
     * 数据库表的字段的前缀
     *
     * @var string
     */
    protected static $fieldPrefix = '';

    /**
     * 数据库表主键(不包含字段前缀)
     *
     * @var string
     */
    protected static $pkey = 'id';

    /**
     * 分页类一页显示的记录数
     *
     * @var int
     */
    public $perPage = 20;

    /**
     * 数据库查询对象
     *
     * @var Query\Builder
     */
    protected $q;

    /**
     * 连接管理器
     *
     * @var ConnectionManager
     */
    protected static $connectionManager;

    /**
     * 缓存存储的键值
     *
     * @var string
     */
    protected static $cacheKey = '';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->q = $this->getConnection()
                        ->table(static::$table)
                        ->prefixTable(static::$tablePrefix)
                        ->prefixField(static::$fieldPrefix);
    }

    /**
     * 设置数据库连接管理器
     *
     * @param ConnectionManager $connectionManager
     */
    public static function setConnectionManager(
        ConnectionManager $connectionManager
    ) {
        static::$connectionManager = $connectionManager;
    }

    /**
     * 获取数据库连接
     *
     * @return Connection
     * @throws RuntimeException
     */
    public function getConnection()
    {
        return static::$connectionManager->connect(static::$dbIndex);
    }

    /**
     * 获取查询构造器
     *
     * @return \Butterfly\Database\Query\Builder
     */
    public function query()
    {
        return $this->q;
    }

    /**
     * 获取数据库表主键
     *
     * @return string
     */
    public function pkey()
    {
        return $this->{static::$pkey};
    }

    /**
     * 处理只提供主键的情况
     *
     * @param array|int $condition 条件数组或 ID 主键
     * @return array
     */
    protected function pkeyCondition($condition)
    {
        if (is_numeric($condition)) {  // 主键
            $condition = [static::$pkey => $condition];
        }

        return $condition;
    }

    /**
     * 获取数据
     *
     * @param string         $method    获取数据使用的方法
     * @param array|int|null $condition 条件数组或 ID 主键
     * @param string         $fields
     * @param array          $orderBy
     * @param int|null       $limit
     * @return mixed
     */
    protected function fetch(
        $method,
        $condition = null,
        $fields = '*',
        $orderBy = [],
        $limit = null
    )
    {
        $q = $this->q->table(static::$table)->select($fields);
        if ($condition !== null) {
            $q->where($this->pkeyCondition($condition));
        }
        if ($orderBy) {
            $q->orderBy($orderBy);
        }

        if ($limit !== null) {
            if (is_numeric($limit)) {
                $q->limit($limit);
            } elseif (is_array($limit) && count($limit) === 2) {
                $q->limit($limit[0], $limit[1]);
            }
        }

        return $q->$method();
    }

    /**
     * 获取一行数据
     *
     * @param array|int|null $condition 条件数组或 ID 主键
     * @param string         $fields
     * @param array          $orderBy
     * @param int|null       $limit
     * @return array
     */
    public function find(
        $condition = null,
        $fields = '*',
        $orderBy = [],
        $limit = null
    ) {
        return $this->fetch('getRow', $condition, $fields, $orderBy, $limit);
    }

    /**
     * 获取多行数据
     *
     * @param array|int|null $condition 条件数组或 ID 主键
     * @param string         $fields
     * @param array          $orderBy
     * @param int|null       $limit
     * @return array
     */
    public function findAll(
        $condition = null,
        $fields = '*',
        $orderBy = [],
        $limit = null
    )
    {
        return $this->fetch('getRows', $condition, $fields, $orderBy, $limit);
    }

    /**
     * 获取第一行的第一个值
     *
     * @param array|int|null $condition 条件数组或 ID 主键
     * @param string         $fields
     * @param array          $orderBy
     * @param int|null       $limit
     * @return string|int|null
     */
    public function findValue(
        $condition = null,
        $fields = '*',
        $orderBy = [],
        $limit = null
    ) {
        return $this->fetch('getValue', $condition, $fields, $orderBy, $limit);
    }

    /**
     * 获取第一行的第一个值
     *
     * @param array|int|null $condition 条件数组或 ID 主键
     * @param string         $fields
     * @param array          $orderBy
     * @param int|null       $limit
     * @return array
     */
    public function findColumn(
        $condition = null,
        $fields = '*',
        $orderBy = [],
        $limit = null
    )
    {
        return $this->fetch('getColumn', $condition, $fields, $orderBy, $limit);
    }

//    public function __call($method, $parameters)
//    {
//        return call_user_func_array(
//            [$this->q, $method],
//            $parameters
//        );
//    }

    /**
     * 执行排序操作
     *
     * @param array $sort
     */
    public function sort(array $sort)
    {
        foreach ($sort as $index => $pkey) {
            $this->q
                ->table(static::$table)
                ->where(static::$pkey, $pkey)
                ->update(['sort' => $index]);
        }
    }

    /**
     * 插入数据，返回插入记录的主键
     *
     * @param  array $fields
     * @param  bool  $getId 是否获取插入记录的 auto_increment 主键
     * @return int|false
     */
    public function insert(array $fields, $getId = true)
    {
        if (!$fields) {
            return false;
        }

        // $fields = $this->fieldsData($fields, true);
        if ($getId && isset($fields[static::$pkey])) {
            unset($fields[static::$pkey]);
        }

        return $this->q->table(static::$table)->insert($fields, $getId);
    }

    /**
     * 更新数据
     *
     * @param array|int $condition 条件数组或 ID 主键
     * @param array     $fields
     * @return int|false
     */
    public function update($condition, array $fields)
    {
        if (!$fields) {
            return false;
        }

        // $fields = $this->fieldsData($fields);

        return $this->q
            ->table(static::$table)
            ->where($this->pkeyCondition($condition))
            ->update($fields);
    }

    /**
     * 删除数据
     *
     * @param array|int $condition 条件数组或 ID 主键
     * @return int|false
     */
    public function delete($condition)
    {
        return $this->q
            ->table(static::$table)
            ->where($this->pkeyCondition($condition))
            ->delete();
    }

    /**
     * 获取表的字段列表
     *
     * @param  string $table 表名
     * @return array  ['field1', 'field2', ...]
     */
    protected function getFields($table)
    {
        // if (defined('FIREFLY_ENV') && FIREFLY_ENV === 'prod') {  // 产品环境下缓存表结构
        //     $cacheKey = 'schema/' . static::$dbIndex . '_' . $table;
        //     $fileCache = Cache::driver('file');
        //     $fields = $fileCache->get($cacheKey);
        //     if (!$fields) {
        //         $fields = $this->q->listFields($table);
        //         $fileCache->set($cacheKey, $fields);
        //     }
        // } else {  // 开发环境下不缓存表结构
        $fields = $this->q->listFields($table);
        // }

        return $fields;
    }

    /**
     * 获取 POST 数组中存在的数据库字段的值
     *
     * @param  array $params    POST数组
     * @param  bool  $forInsert 用于插入操作
     * @return array 用于执行数据库操作的数据库字段键值数组
     */
    public function fieldsData($params, $forInsert = false)
    {
        $fields = $this->getFields(static::$table);
        $data   = [];
        foreach ($fields as $f) {
            if (isset($params[$f])) {
                $data[$f] = $params[$f];
            } elseif (isset($params['#' . $f])) {
                $f        = '#' . $f;
                $data[$f] = $params[$f];
            }
        }

        if ($forInsert === true && in_array('created_at', $fields)) {
            $data['created_at'] = time();
        }

        // 设置更新时间
        if ($data && in_array('updated_at', $fields)) {
            $data['updated_at'] = time();
        }

        return $data;
    }

    /**
     * 返回以数据库表字段为键值，空字符串为值的数组，用于控制器的 newAction 操作
     *
     * @param  array $extra
     * @return array
     */
    public static function dummyRow(array $extra = [])
    {
        $extra = (array)$extra;

        /** @var $obj Model */
        $obj    = new static();
        $fields = $obj->q->listFields(static::$table);
        $row    = array_merge(array_fill_keys($fields, ''), $extra);

        return $row;
    }

    /**
     * 获取上次查询执行的SQL语句
     */
    public function lastQuery()
    {
        return $this->q->connection()->lastQuery();
    }

    /**
     * 判断上次执行的SQL是否发生重复数据异常
     */
    public function isDuplicate()
    {
        return $this->q->connection()->isDuplicate();
    }
    
    public function transStart() {
        $this->q->connection()->transStart();
    }
    
    public function transComplete() {
        $this->q->connection()->transComplete();
    }
    
    public function transRollback() {
        $this->q->connection()->transRollback();
    }
}
