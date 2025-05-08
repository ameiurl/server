<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Database\Query;

use Closure;
use Butterfly\Pagination\PaginationFactory;
use Butterfly\Pagination\Pagination;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Butterfly\Database\Connection;

/**
 * SQL 查询构造器
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Builder
{
    /**
     * 数据库连接
     *
     * @var Connection
     */
    protected $connection;

    /**
     * SQL 语法生成器
     *
     * @var Grammar
     */
    protected $grammar;

    /**
     * 当前执行的 SQL
     *
     * @var string
     */
    protected $sql;

    /**
     * SQL prepare 语句绑定的参数
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * SQL语句的各个组成元素（select, table, join, where, groupBy ...）
     *
     * @var array
     */
    protected $parts = [
        'select'  => [],
        'table'   => '',
        'join'    => [],
        'where'   => [],
        'groupBy' => [],
        'having'  => [],
        'orderBy' => [],
        'limit'   => [],
    ];

    /**
     * 存储一份未修改过的 $parts 数组
     *
     * @var array
     */
    protected $cleanParts = [
        'select'  => [],
        'table'   => '',
        'join'    => [],
        'where'   => [],
        'groupBy' => [],
        'having'  => [],
        'orderBy' => [],
        'limit'   => [],
    ];

    /**
     * 表、字段前缀
     *
     * @var array
     */
    protected $prefixes = [
        'table' => '',
        'field' => '',
    ];

    /**
     * 请求完是否重置除表名以外的参数
     *
     * @var bool
     */
    public $reset = true;

    /**
     * 分页类工厂
     *
     * @var PaginationFactory
     */
    protected static $paginationFactory;

    /**
     * 构造函数
     *
     * @param Connection $connection 数据库连接对象
     * @param Grammar    $grammar    语法生成器
     */
    public function __construct(Connection $connection, Grammar $grammar)
    {
        $this->connection = $connection;
        $this->grammar    = $grammar;
    }

    /**
     * 返回数据库连接对象
     *
     * @return Connection
     */
    public function connection() : Connection
    {
        return $this->connection;
    }

    /**
     * 设置表前缀
     *
     * @param  string $prefix 前缀
     * @return Builder
     */
    public function prefixTable(string $prefix) : Builder
    {
        $this->prefixes['table'] = $prefix;

        return $this;
    }

    /**
     * 设置字段前缀
     *
     * @param  string $prefix 前缀
     * @return Builder
     */
    public function prefixField(string $prefix) : Builder
    {
        $this->prefixes['field'] = $prefix;

        return $this;
    }

    /**
     * 返回当前的SQL查询错误信息
     *
     * @return string 错误信息
     */
    public function errorInfo() : string
    {
        return $this->connection->errorInfo();
    }

    /**
     * 设置字符集
     *
     * @param  string $charset 字符集
     * @return bool   成功返回true，失败返回false
     */
    public function setCharset(string $charset)
    {
        return $this->connection->setCharset($charset);
    }

    /**
     * 清除所有的 $this->parts 字段
     *
     * @return Builder
     */
    public function clear() : Builder
    {
        $this->parts    = $this->cleanParts;
        $this->bindings = [];

        return $this;
    }

    /**
     * 重置除 table 键值的 $this->parts 数组
     *
     * @return Builder
     */
    public function reset() : Builder
    {
        if (isset($this->parts['table'])) {
            $parts          = $this->cleanParts;
            $parts['table'] = $this->parts['table'];
            $this->parts    = $parts;
            $this->bindings = [];
        }

        return $this;
    }

    /**
     * 设置要获取的字段，默认所有
     *
     * 使用示例：
     * ```php
     * // 选择所有字段，未调用该语句则默认选择所有字段
     * $q->select('*');
     *
     * // 选择指定字段， 版本 1
     * $q->select('id, name, created_at');
     *
     * // 如果字段不应该自动加上字段标识符（MYSQL中是反顿号），则需要 第一个字符设置为"#"井号
     * $q->select('#count(*)')
     *
     * // 选择指定字段， 版本 2
     * $q->select('id', 'name', 'created_at');
     *
     * // 如果包含sql函数则必须使用 版本2，例如
     * $q->select("#CONCAT(first_name, ' ', last_name)", 'gender');
     *
     * // 带 SQL 表达式
     * $q->select('id', '#NOW()');
     *
     * // 所有字段以原始形式
     * $q->select('#id, NOW()');
     * ```
     *
     * @param  mixed $fields
     * @return Builder
     */
    public function select(...$fields) : Builder
    {
        $fields = $fields ?: ['*'];

        return $this->fieldList(__FUNCTION__, $fields);
    }

    /**
     * 生成字段列表
     *
     * @param string $partType
     * @param array  $args
     * @return Builder
     */
    protected function fieldList(string $partType, array $args) : Builder
    {
        $arg0 = $args[0];
        if (count($args) === 1) {
            if (strpos($arg0, '#') === 0) {
                $fields = [$arg0];
            } else {
                $fields = preg_split('/\s*,\s*/', trim($arg0), null,
                    PREG_SPLIT_NO_EMPTY);
            }
        } else {
            $fields = $args;
        }
        $this->parts[$partType] = $fields;

        return $this;
    }

    /**
     * 设置要操作的数据库表
     *
     * 使用示例：
     * ```php
     * $q->table('user');
     *
     * $q->table('user as u');
     * ```
     *
     * @param  string $table
     * @return Builder
     */
    public function table(string $table) : Builder
    {
        $this->parts[__FUNCTION__] = $table;

        return $this;
    }

    /**
     * table 方法的别名
     *
     * @see  Builder::table($table)
     * @param  string $table
     * @return Builder
     */
    public function from(string $table) : Builder
    {
        return $this->table($table);
    }

    /**
     * select查询的 join
     *
     * 使用示例：
     * ```php
     * $q->join('role', 'role.id', 'permission.id');
     * $q->join('role AS r', 'r.id', 'p.id');
     *
     * $q->from('account AS a')
     *   ->join('role AS r', ['r.id' => 'a.rid', 'r.type' => 'a.rtype']);
     * // SELECT * FROM account AS a
     * // JOIN role AS r ON r.id = a.rid AND r.type = a.rtype
     * ```
     *
     * @param  string       $table 要关联的表名
     * @param  string|array $column1
     * @param  string       $column2
     * @param  string       $type  join连接类型：'INNER', 'LEFT', 默认 INNER
     * @return Builder
     */
    public function join(
        string $table,
        $column1,
        $column2 = null,
        $type = 'INNER'
    ) : Builder
    {
        $join = new Join($type, $table);
        if (is_array($column1)) {
            foreach ($column1 as $col1 => $col2) {
                $join->on($col1, $col2);
            }
        } else {
            $join->on($column1, $column2);
        }

        $this->parts[__FUNCTION__][] = $join;

        return $this;
    }

    /**
     * SELECT 查询的 LEFT JOIN
     *
     * 使用示例：
     * ```php
     * $q->leftJoin('role', 'role.id', 'permission.id');
     * $q->leftJoin('role AS r', 'r.id', 'p.id');
     * ```
     *
     * @param  string $table 要关联的表名
     * @param  string $column1
     * @param  string $column2
     * @see join
     * @return Builder
     */
    public function leftJoin(string $table, $column1, $column2 = null) : Builder
    {
        return $this->join($table, $column1, $column2, 'LEFT');
    }

    /**
     * 获取一行数据
     *
     * @param int $fetchStyle 返回数据格式：
     *                        PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_BOTH
     * @return array|false  没有数据时，返回　false
     */
    public function getRow($fetchStyle = PDO::FETCH_ASSOC)
    {
        if (count($this->parts['select']) === 0) {
            $this->select();
        }

        $sql = $this->grammar->compileSelect($this->parts, $this->prefixes);
        $this->connection->query($sql, $this->bindings,
            $this->prefixes['field']);
        $this->reset && $this->reset();

        return $this->connection->fetch($fetchStyle);
    }

    /**
     * 获取所有数据
     *
     * @param int $fetchStyle 返回数据格式：
     *                        PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_BOTH
     * @return array
     */
    public function getRows($fetchStyle = PDO::FETCH_ASSOC) : array
    {
        if (count($this->parts['select']) === 0) {
            $this->select();
        }
        $sql = $this->grammar->compileSelect($this->parts, $this->prefixes);
        $this->connection->query($sql, $this->bindings,
            $this->prefixes['field']);
        $this->reset && $this->reset();

        return $this->connection->fetchAll($fetchStyle);
    }

    /**
     * 获取以指定键值作为索引的数组
     *
     * @param string $key        要作为索引的键值
     * @param int    $fetchStyle 返回数据格式：
     *                           PDO::FETCH_ASSOC, PDO::FETCH_NUM,
     *                           PDO::FETCH_BOTH
     * @return array
     */
    public function getKeyRows($key, $fetchStyle = PDO::FETCH_ASSOC) : array
    {
        if (count($this->parts['select']) === 0) {
            $this->select();
        }
        $sql = $this->grammar->compileSelect($this->parts, $this->prefixes);
        $this->connection->query($sql, $this->bindings,
            $this->prefixes['field']);
        $this->reset && $this->reset();

        return $this->connection->fetchKeyAll($key, $fetchStyle);
    }

    /**
     * 设置分页查询的 limit 跟 offset
     *
     * @param  int $currentPage
     * @param  int $perPage
     * @return Builder
     */
    protected function limitPage($currentPage, $perPage) : Builder
    {
        $offset = ($currentPage - 1) * $perPage;
        $this->limit($offset, $perPage);

        return $this;
    }

    /**
     * 执行 SELECT COUNT(*) AS COUNTER FROM ... 查询
     *
     * 使用示例：
     * ```php
     * $q->table('user')->count();
     * ```
     *
     * @return int
     */
    public function count() : int
    {
        // 记录 select,orderBy 条件，并去除 orderBy 条件
        $partSelect             = $this->parts['select'];
        $partOrderBy            = $this->parts['orderBy'];
        $this->parts['orderBy'] = [];

        // 带 having 条件时，需要保留 select 从句的带 Db::fn, Db::expression 的字段，
        // 以用于 having 条件
        if (!empty($this->parts['having'])) {
            $fields = array_filter($partSelect, function ($field) {
                return !is_scalar($field);
            });

            $this->select($fields);
            $counter = count($this->getRows());
        } else {
            if (empty($this->parts['groupBy'])) {  // 不带 group by 查询条件
                $this->select('#COUNT(*) AS counter');
                $counter = (int)$this->getValue();
            } else {  // 带 group by 条件的记录总数查询
                $sql      = $this->grammar->compileSelect($this->parts,
                    $this->prefixes);
                $sql      = $this->connection->compileSql($sql,
                    $this->bindings);
                $sqlCount = "SELECT COUNT(*) FROM ($sql) AS count_temp_tbl";
                $this->connection->query($sqlCount);
                $row = $this->connection->fetch();
                if ($row) {
                    $counter = current($row);
                } else {
                    $counter = 0;
                }
            }
        }

        // 还原查询条件
        $this->parts['select']  = $partSelect;
        $this->parts['orderBy'] = $partOrderBy;

        return $counter;
    }

    /**
     * 设置分页类
     *
     * @param PaginationFactory|\Closure $factory
     */
    public static function setPaginationFactory($factory)
    {
        static::$paginationFactory = $factory;
    }

    /**
     * 获取分页类
     *
     * @return PaginationFactory
     */
    public static function getPaginationFactory()
    {
        if (static::$paginationFactory instanceof Closure) {
            /** @var Closure $factory */
            $factory                   = static::$paginationFactory;
            static::$paginationFactory = $factory();
        }

        return static::$paginationFactory;
    }

    /**
     * 获取分页结果对象
     *
     * 使用示例：
     * ```php
     * $q->table('menu')->paginate(10);
     *
     * $p = $q->table('user')->paginate();
     * $p->results;  // 当前分页的数据
     * $p->links();  // 分页链接
     * ```
     *
     * @param int       $perPage 每页显示的记录数
     * @param array|int $options 为整数时，指第几页；为数组时为配置数组
     * @return Pagination
     */
    public function paginate($perPage = 20, $options = [])
    {
        $this->reset = false;

        $factory = static::getPaginationFactory();

        if (empty($this->parts['groupBy'])) {
            $count       = $this->count();
            $this->reset = true;

            $pagination = $factory->create($count, $perPage, $options);
            $offset     = $pagination->offset();
            $limit      = $pagination->limit();
            $rows       = $this->limit($offset, $limit)->getRows();
        } else {  // 带 group by 的查询方式
            $this->reset = true;
            $allRows     = $this->getRows();
            $count       = count($allRows);

            $pagination = $factory->create($count, $perPage, $options);
            $offset     = $pagination->offset();
            $rows       = array_slice($allRows, $offset, $perPage);
        }

        $pagination->setResults($rows);

        return $pagination;
    }

    /**
     *  获取第一行数据的第一列
     *
     * @return mixed
     */
    public function getValue()
    {
        $row = $this->getRow();
        if ($row) {
            return current($row);
        } else {
            return null;
        }
    }

    /**
     * 获取结果集中一列
     *
     * 使用示例：
     * ```php
     * $q->getColumn();
     * $q->getColumn(1);
     * $q->getColumn('id');
     * ```
     *
     * @param mixed $column 要返回的指定列
     *                      可用数字标识第几列（第1列的数字标识为0），也可用字段名指定
     * @return array
     */
    public function getColumn($column = 0) : array
    {
        $fetchStyle = is_int($column) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
        $result     = [];
        $rows       = $this->getRows($fetchStyle);
        foreach ($rows as $row) {
            $result[] = $row[$column];
        }

        return $result;
    }

    /**
     * 返回 key => value 形式的数组
     * 以SQL查询字段的第一列作为key，第二列作为value
     *
     * @return array
     */
    public function getPairs() : array
    {
        $rows  = $this->getRows(PDO::FETCH_NUM);
        $pairs = [];
        foreach ($rows as $row) {
            $pairs[$row[0]] = $row[1];
        }

        return $pairs;
    }

    /**
     * 插入数据
     *
     * 使用示例：
     * ```php
     * // 插入单条记录
     * $q->table('user')->insert(['name' => 'john', 'gender' => 1]);
     *
     * // 批量插入
     * $q->table('user')->insert([
     *     ['name' => 'John Doe', 'gender' => 1],
     *     ['name' => 'Jane Doe', 'gender' => 2]
     * ]);
     * ```
     *
     * @param  array $values
     * @param  bool  $getId 是否获取插入记录的 auto_increment 主键
     * @return int|false 执行失败返回false，否则返回插入的记录的ID主键或受影响的记录数
     */
    public function insert(array $values, $getId = true)
    {
        return $this->persist(__FUNCTION__, $values, $getId);
    }

    /**
     * 插入数据并忽略插入过程中的错误  (INSERT IGNORE INTO ...)
     *
     * 使用示例：
     * ```php
     * // 插入单条记录
     * $q->table('user')->insertIgnore(['name' => 'john', 'gender' =>
     * 1]);
     *
     * // 批量插入
     * $q->table('user')->insertIgnore([
     *     ['name' => 'John Doe', 'gender' => 1],
     *     ['name' => 'Jane Doe', 'gender' => 2]
     * ]);
     * ```
     *
     * @param  array $values
     * @param  bool  $getId 是否获取插入记录的 auto_increment 主键
     * @return int|false 执行失败返回false，否则返回插入的记录的ID主键或受影响的记录数
     */
    public function insertIgnore(array $values, $getId = true)
    {
        return $this->persist('insert ignore', $values, $getId);
    }

    /**
     * 替换数据：无则插入，有则更新
     *
     * 使用示例：
     * ```php
     * // 替换单条记录
     * $q->table('user')->replace(['name' => 'john', 'gender' => 1]);
     *
     * // 批量替换
     * $q->table('user')->replace([
     *     ['name' => 'John Doe', 'gender' => 1],
     *     ['name' => 'Jane Doe', 'gender' => 2]
     * ]);
     * ```
     *
     * @param  array $values
     * @param  bool  $getId 是否获取替换记录的 auto_increment 主键
     * @return int|false 执行失败返回false，否则返回替换的记录的ID主键或受影响的记录数
     */
    public function replace(array $values, $getId = true)
    {
        return $this->persist(__FUNCTION__, $values, $getId);
    }

    /**
     * 插入或替换数据
     *
     * @param  string $type
     * @param  array  $values
     * @param  bool   $getId
     * @return int
     * @throws InvalidArgumentException
     */
    protected function persist(
        string $type,
        array $values,
        bool $getId = true
    ) : int
    {
        if (!$values) {
            throw new InvalidArgumentException(sprintf('No data provided for %s operation.',
                $type));
        }

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $sql = $this->grammar->compilePersist($type, $this->parts, $values,
            $this->prefixes);

        $bindings = [];
        foreach ($values as $value) {
            $bindings = array_merge($bindings, array_values($value));
        }

        $result = $this->connection->query($sql, $bindings,
            $this->prefixes['field']);

        $this->reset && $this->reset();
        if ($result === false) {
            return false;
        }

        if ($getId) {
            return $this->connection->insertId();
        } else {
            return $this->connection->affectedRows();
        }
    }

    /**
     * 更新数据
     *
     * @param  array $values
     * @return int|false 执行失败返回false，否则受影响的记录数
     * @throws InvalidArgumentException
     */
    public function update(array $values)
    {
        if (!$values) {
            throw new InvalidArgumentException($values);
        }

        $values = $this->filterRawValues($values);

        $sql      = $this->grammar->compileUpdate($this->parts, $values,
            $this->prefixes);
        $bindings = array_values(array_merge($values, $this->bindings));
        $result   = $this->connection->query($sql, $bindings,
            $this->prefixes['field']);
        $this->reset && $this->reset();
        if ($result === false) {
            return false;
        }

        return $this->connection->affectedRows();
    }

    /**
     * 过滤"#"井号指向的原始值
     *
     * @param array $values
     * @return array
     */
    protected function filterRawValues($values)
    {
        $result = [];
        foreach ($values as $column => $value) {
            if (strpos($column, '#') === 0) {
                $column = substr($column, 1);
                $value  = is_scalar($value) ? new Expression($value) : $value;
            }
            $result[$column] = $value;
        }

        return $result;
    }

    /**
     * 插入数据，在有重复数据时，更新该条数据
     *
     * @param  array $values
     * @param  array $valuesUpdate
     * @return int|false 执行失败返回false，否则受影响的记录数
     * @throws InvalidArgumentException
     * @see https://wiki.postgresql.org/wiki/UPSERT
     */
    public function upsert(array $values, $valuesUpdate = null)
    {
        if (!$values) {
            throw new InvalidArgumentException($values);
        }

        $values = $this->filterRawValues($values);
        if ($valuesUpdate !== null) {
            $valuesUpdate = $this->filterRawValues($valuesUpdate);
        }

        $sql = $this->grammar->compileUpsert($this->parts, $values,
            $valuesUpdate, $this->prefixes);
        if ($valuesUpdate === null) {
            $valuesUpdate = $values;
        }
        $bindings = array_merge(array_values($values),
            array_values($valuesUpdate), $this->bindings);
        $result   = $this->connection->query($sql, $bindings,
            $this->prefixes['field']);
        $this->reset && $this->reset();
        if ($result === false) {
            return false;
        }

        return $this->connection->affectedRows();
        // http://dev.mysql.com/doc/refman/5.7/en/insert-on-duplicate.html
        // With ON DUPLICATE KEY UPDATE, the affected-rows value per row is 1
        // if the row is inserted as a new row, 2 if an existing row is updated,
        // and 0 if an existing row is set to its current values.
    }

    /**
     * 删除操作
     *
     * 使用示例：
     * ```php
     * $q->table('user')->where('id', 1)->delete();
     * $q->table('user')->delete(1);
     * ```
     *
     * @param  int $id 数据库表ID主键
     * @return int|false 执行失败返回false，否则返回受影响的记录数
     */
    public function delete($id = null)
    {
        if ($id !== null) {
            $this->where('id', $id);
        }

        $sql    = $this->grammar->compileDelete($this->parts, $this->prefixes);
        $result = $this->connection->query($sql, $this->bindings,
            $this->prefixes['field']);
        $this->reset && $this->reset();
        if ($result === false) {
            return false;
        }

        return $this->connection->affectedRows();
    }

    /**
     * 添加 ->where(1) 形式的查询条件
     *
     * @param string $partType
     * @param mixed  $value
     */
    protected function numericCondition(string $partType, $value)
    {
        $column                   = (string)$value;
        $operator                 = $value = null;
        $connector                = 'AND';
        $this->parts[$partType][] = compact('column', 'operator', 'value',
            'connector');
    }

    /**
     * 添加 ->where(['foo' => 'bar']) 形式的查询条件
     *
     * @param string       $partType   查询类型
     * @param array|string $conditions 需要处理的查询条件
     * @param string       $connector
     * @throws RuntimeException
     */
    protected function addConditions(string $partType, $conditions, $connector)
    {
        if (is_numeric($conditions)) {
            // 不支持 ->where(['foo = "bar"']) 形式的查询条件
            // 以防止开发人员直接把用户提供的值拼接到查询条件，以预防sql注入
            $this->numericCondition($partType, $conditions);
            return;
        }

        if (!is_array($conditions)) {
            throw new RuntimeException("$partType condition must be an array.");
        }

        $operators = ['AND', 'OR'];

        foreach ($conditions as $column => $value) {
            $numericKey = is_numeric($column);

            if ($numericKey && is_scalar($value) && !is_string($value)) {
//                ->where([1]) 形式
                $this->numericCondition($partType, $value);
                continue;
            }

            if (in_array($column, $operators) && !is_array($value)) {
                throw new RuntimeException('Value of nested condition must be array.');
            }

            if ($numericKey && is_array($value) || in_array($column,
                    $operators)
            ) {
                $conjunction = $numericKey ? 'AND' : $column;
                $this->nestedCondition($partType, $value, $connector,
                    $conjunction);
                continue;
            }

            if (!$numericKey) {
                $this->parseCondition($partType, $column, $value, $connector);
            }
        }
    }

    /**
     * 判断是否包含操作符
     *
     * @param  string $column
     * @return bool
     */
    protected function hasOperator($column)
    {
        return preg_match('/(?:<|>|!|=|\sIS NULL|\sIS NOT NULL|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\()/i',
            $column) === 1;
    }

    /**
     * 添加 ->where(['foo >=' => 1, 'bar LIKE‘ => 'example']) 类型的键值对查询条件
     *
     * @param string $partType
     * @param string $field
     * @param mixed  $value
     * @param string $connector
     */
    protected function parseCondition(
        string $partType,
        $field,
        $value,
        $connector = 'AND'
    ) {
        $operator = '=';
        $column   = trim($field);
        if (strpos($column, '(') !== false) {  // 不是字段名
            if ($this->hasOperator($column)) {
                $operator = '';
            }
        } else {
            $parts = explode(' ', trim($field), 2);
            if (count($parts) > 1) {
                list($column, $operator) = $parts;
            }
        }

        $operator = strtoupper(trim($operator));

        $this->keyValueCondition($partType, $column, $operator, $value,
            $connector);
    }

    /**
     * 添加 ->where(['foo' => 'bar']) 或 ->where('foo', 'bar') 形式的查询条件
     *
     * @param string $partType
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $connector
     */
    protected function keyValueCondition(
        string $partType,
        $column,
        $operator,
        $value,
        $connector
    ) {
        if ($value === null) {
            throw new RuntimeException(vsprintf('(%s): value cannot be omitted',
                [$partType]));
        }

        if (is_string($column)) {
            // 字段名的第一个字符是"#"井号，则自动不转义字段值
            if (strpos($column, '#') === 0) {
                // ->where('#curr_time', 'NOW()') 形式
                $column = substr($column, 1);
                $value  = new Expression($value);
            }

            // 如果包含非字母、数字、下划线，则自动不转义字段名
            if (!ctype_alnum(str_replace('_', '', $column))) {
                // http://cn2.php.net/manual/en/function.ctype-alnum.php
                $column = new Expression($column);
            }
        }

        $operator = is_array($value) && $operator === '=' ? 'IN' : $operator;

        $this->parts[$partType][] = compact('column', 'operator', 'value',
            'connector');
        if (is_array($value)) {
            $this->bindings = array_merge($this->bindings, $value);
        } elseif (is_scalar($value)) {
            $this->bindings[] = $value;
        }
    }

    /**
     * 添加嵌套查询条件
     *
     * @param string $partType
     * @param array  $conditions
     * @param string $connector
     * @param string $conjunction
     */
    protected function nestedCondition(
        string $partType,
        $conditions,
        $connector,
        $conjunction
    ) {
        $query = new self($this->connection, $this->grammar);
        $query->addConditions($partType, $conditions, $conjunction);
        $parts = $query->parts;

        $this->parts[$partType][] = compact('parts', 'connector');
        $this->bindings           = array_merge($this->bindings,
            $query->bindings);
    }

    /**
     * where查询条件
     *
     * 使用示例：
     * ```php
     * $q->where('id', 1);  // 条件为等于时
     * $q->where(0);  // where 0 可用于有分页的查询时，条件不满足的处理
     * $q->where('state !=', 1);
     * $q->where('id', [1, 2, 3]);  // 等价于 $q->where('id IN', [1, 2, 3]);
     * $q->where('id NOT IN', [1, 2, 3]);
     * $q->where('id BETWEEN', [1, 5]);
     * $q->where('name LIKE', 'a%');
     * $q->where('#updated_at IS NOT', 'NULL');
     *
     * $q->where([
     *    'activated' => 1,
     *    'AND' => [
     *          'gender'    => 1,
     *          'grade >'   => 2,
     *    ]
     * ]);  // WHERE activated=1 AND (gender=1 AND grade>2)
     * ```
     *
     * @param  string $column
     * @param  mixed  $value
     * @param  string $connector
     * @return Builder
     */
    public function where($column, $value = null, $connector = 'AND') : Builder
    {
        if (is_array($column)) {
            // ->where(['a >=1' => 1, 'b' => 2]) 形式
//            $this->addConditions(__FUNCTION__, $column, $connector);
            $this->nestedCondition(__FUNCTION__, $column, $connector, 'AND');
        } else {
            if (is_numeric($column)) {  // ->where(1)
                $this->numericCondition(__FUNCTION__, $column);
            } else {  // ->where('a', 1) 形式
                $this->parseCondition(__FUNCTION__, $column, $value,
                    $connector);
            }
        }

        return $this;
    }

    /**
     * or where 查询
     *
     * 使用示例：
     * ```php
     * $q->where('id >', 100)
     *   ->orWhere('id <', 10);
     * ```
     *
     * @see where
     * @param  string $column
     * @param  mixed  $value
     * @return Builder
     */
    public function orWhere($column, $value = null) : Builder
    {
        return $this->where($column, $value, 'OR');
    }

    /**
     * 指定 group by 条件
     *
     * 使用示例：
     * ```php
     * // 添加方式 1
     * $q->groupBy('role_id, permission_id');
     *
     * // 添加方式 2
     * $q->groupBy('role_id', 'permission_id');
     * ```
     *
     * @param  mixed $fields
     * @return Builder
     */
    public function groupBy(...$fields) : Builder
    {
        // 也可以实现 $q->groupBy('role_id')->groupBy('permission_id');
        // GROUP BY role_id, permission_id
        // 以及 $q->groupBy(['role_id', 'permission_id']); 方式
        // 这2种方式在也支持的情况下，实践中大家基本不会用到(要多敲很多字符)
        // 支持的参数方式跟 select() 方法保持统一，以降低开发人员的心智负担
        if (!$fields) {
            return $this;
        }

        return $this->fieldList(__FUNCTION__, $fields);
    }

    /**
     * 指定 having 条件
     *
     * 使用示例：
     * ```php
     * $q->groupBy('user')
     *   ->having('max(score) >', 10);
     *
     * $q->groupBy('user')
     *   ->having('max_score', 10);
     * ```
     *
     * @see Builder::where()
     * @param  string|Expression $column
     * @param  mixed             $value
     * @param  string            $connector 可选
     * @return Builder
     */
    public function having($column, $value = null, $connector = 'AND') : Builder
    {
        if (is_string($column) && strpos($column, '#') !== 0) {
            $column = new Expression($column);
        }

        if (is_array($column)) {
            // ->having(['a >=1' => 1, 'b' => 2]) 形式
            $this->addConditions(__FUNCTION__, $column, $connector);
        } else {  // ->having('a', 1) 形式
            $this->parseCondition(__FUNCTION__, $column, $value, $connector);
        }

        return $this;
    }

    /**
     * 指定 OR HAVING 条件
     *
     * @see having
     * @param  string|Expression $column
     * @param  mixed             $value
     * @return Builder
     */
    public function orHaving($column, $value) : Builder
    {
        return $this->having($column, $value, 'OR');
    }

    /**
     * 指定 order by 条件
     *
     * 使用示例：
     * ```php
     *
     * // 使用方式 1
     * $q->orderBy('sort')
     *   ->orderBy('created_at', 'DESC');
     *
     * // 使用方式 2
     * $q->orderBy(['sort' => 'ASC', 'created_at' => 'DESC');
     * ```
     *
     * @param  string|array $column
     * @param  string       $direction 'ASC' 或 'DESC'，默认： 'ASC'
     * @return Builder
     */
    public function orderBy($column, $direction = 'ASC') : Builder
    {
        if (!$column) {
            return $this;
        }

        // 单个 order by 条件处理
        $orderBy = function ($col, $dir, $func) {
            if (strpos($col, '#') === 0) {
                $col = new Expression(substr($col, 1));
                $dir = '';
            } else {
                $dir = strtoupper(trim($dir)) === 'ASC' ? 'ASC' : 'DESC';
            }

            $this->parts[$func][] = [
                'column'    => $col,
                'direction' => $dir,
            ];
        };

        if (is_array($column)) {
            foreach ($column as $col => $dir) {
                $orderBy($col, $dir, __FUNCTION__);
            }
        } else {
            $orderBy($column, $direction, __FUNCTION__);
        }

        return $this;
    }

    /**
     * 指定 limit 条件
     *
     * 使用示例：
     * ```php
     * $q->limit(5, 10);
     * $q->limit(3);
     * ```
     *
     * @param  int $offset
     * @param  int $count
     * @return Builder
     */
    public function limit($offset = 0, $count = null) : Builder
    {
        if ($count === null) {
            $count  = $offset;
            $offset = 0;
        }

        $offset = intval($offset);
        $count  = intval($count);

        $this->parts[__FUNCTION__] = compact('offset', 'count');

        return $this;
    }

    /**
     * 获取数据库表的字段名列表
     *
     * @param  string $table 表名
     * @return array  ['field1', 'field2', ...]
     */
    public function listFields(string $table) : array
    {
        $sql = $this->grammar->compileListFields($table, $this->prefixes);

        return $this->connection->listFields($sql, $this->prefixes['field']);
    }

    /**
     * 选择要查询的数据库
     *
     * @param  string $dbName 数据库名
     * @return Builder
     */
    public function selectDb(string $dbName) : Builder
    {
        $this->connection->selectDb($dbName);

        return $this;
    }

    /**
     * 是否指定使用主库查询
     *
     * @return Builder
     */
    public function useMaster()
    {
        $this->connection()->useMaster();

        return $this;
    }
}
