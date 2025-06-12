<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Database\Query;

use RuntimeException;

/**
 * SQL 语句构建
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Grammar
{
    /**
     * 数据库表前缀
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * 字段前缀
     *
     * @var string
     */
    protected $fieldPrefix = '';

    /**
     * select语句的各个组成部分
     *
     * @var array
     */
    protected $selectParts = [
        'select',
        'table',
        'join',
        'where',
        'groupBy',
        'having',
        'orderBy',
        'limit',
    ];

    /**
     * 主表的引用
     *
     * @var string
     */
    protected $mainTableRef = '';

    /**
     * 创建逗号分割的列名列表
     *
     * ```php
     * // 返回 "`foo`, `bar`"
     * $columns = $this->columnize(['foo', 'bar']);
     * ```
     *
     * @param  array $columns
     * @return string
     */
    protected function columnize(array $columns)
    {
        return implode(', ', array_map([$this, 'quoteFieldExpr'], $columns));
    }

    /**
     * 获取合适的查询参数.
     *
     * ```php
     * // 返回 "?" sql prepare 语句占位符
     * $value = $this->parameter('foo');
     *
     * // 返回 "count+1" sql 查询表达式
     * $value = $this->parameter(new Expression('count+1'));
     * ```
     *
     * @param  mixed $value
     * @return string
     */
    public function parameter($value)
    {
        return ($value instanceof Expression) ? $value->value() : '?';
    }

    /**
     * 创建 prepare 语句的参数列表
     *
     * ```php
     * // 返回 "?, ?, ?"
     * $parameters = $this->parameterize([1, 2, 3]);
     *
     * // 返回 "?, 'count+1'"
     * $parameters = $this->parameterize([1, (new Expression('count+1'))]);
     * ```
     *
     * @param  array $values
     * @return string
     */
    protected function parameterize(array $values)
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    /**
     * 对字符串加标识符引用
     *
     * @param  string $identifier
     * @return string
     */
    protected function quote($identifier)
    {
        if ($identifier === '*' || is_numeric($identifier)) {
            // SELECT * 或 SELECT 1
            return $identifier;
        }

        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * 对字段名加字段前缀
     *
     * @param  string $field
     * @return string
     */
    protected function prefixField(string $field)
    {
        return ($field !== '*') ? $this->fieldPrefix . $field : $field;
    }

    /**
     * 对表名加表名前缀
     *
     * @param  string $table
     * @return string
     */
    protected function prefixTable($table)
    {
        return $this->tablePrefix . $table;
    }

    /**
     * 对 db_name.tbl_name as alias_name 的表名表达式加 backtick(`)
     *
     * @param  string|Expression $value
     * @return string
     */
    public function quoteTableExpr($value)
    {
        if ($value instanceof Expression) {
            return $value->value();
        }

        if (stripos($value, ' AS ') !== false) {
            $segments = preg_split('/\s+/', $value, 3);

            return sprintf(
                '%s AS %s',
                $this->quoteTable($segments[0]),
                $this->quote($segments[2])
            );
        }

        return $this->quoteTable($value);
    }
//
//    /**
//     * 构造 SQL 查询函数语句
//     *
//     * @param  Fn     $fn SQL 查询函数 对象
//     * @return string
//     */
//    protected function fn(Fn $fn)
//    {
//        $alias = isset($fn->alias) ? ' AS ' . $this->quote($fn->alias) : '';
//
//        if (strpos($fn->fn, '%field') !== false) {
//            // 指定参数位置的查询，例如： Butterfly\Database::fn('count(distinct(%field))', 'name', 'num');
//            $field = str_replace('%field', $this->quoteField($fn->params[0]), $fn->fn);
//            return $field . $alias;
//        } else {
//            // 不指定参数位置的查询，例如：Butterfly\Database::fn('count', 'id', 'num');
//            $params = implode(', ', array_map([$this, 'quoteField'], $fn->params));
//            return $fn->fn . '(' . $params . ')' . $alias;
//        }
//    }

    /**
     * 对 tbl_name.field_name as alias_name 的字段名表达式加 backtick(`)
     *
     * @param  string|Expression $value
     * @return string
     */
    public function quoteFieldExpr($value)
    {
        if (strpos($value, '#') === 0) {
            return substr($value, 1);
        }/* elseif ($value instanceof Expression) {
            return $value->value();
        } elseif ($value instanceof Fn) {
            return $this->fn($value);
        }*/

        if (stripos($value, ' AS ') !== false) {
            $segments = preg_split('/\s+/', $value, 3);

            return sprintf(
                '%s as %s',
                $this->quoteField($segments[0]),
                $this->quote($segments[2])
            );
        }

        return $this->quoteField($value);
    }

    /**
     * 对表名加 backtick(`)
     *
     * @param  string $value
     * @return string
     */
    public function quoteTable(string $value)
    {
        $segments = explode('.', $value, 2);
        if (count($segments) === 2) {
            return $this->quote($segments[0]) . '.' .
            $this->quote($this->prefixTable($segments[1]));
        } else {
            return $this->quote($this->prefixTable($value));
        }
    }

    /**
     * 对字段名加 backtick(`)
     *
     * @param  string|Expression $value
     * @return string
     */
    public function quoteField($value)
    {
        if ($value instanceof Expression) {
            return $value->value();
        }/* else if ($value instanceof Fn) {
            return $this->fn($value);
        }*/

        $segments = explode('.', $value, 2);
        if (count($segments) === 2) {
            // 当前字段的引用不等于主表的引用时，不需要添加字段前缀
            if ($segments[0] !== $this->mainTableRef) {
                return $this->quote($segments[0]) . '.' .
                $this->quote($segments[1]);
            } else {
                return $this->quote($segments[0]) . '.' .
                $this->quote($this->prefixField($segments[1]));
            }
        } else {
            return $this->quote($this->prefixField($value));
        }
    }

    /**
     * 获取表引用
     *
     * @param  string $table
     * @return string
     */
    public function tableRef(string $table)
    {
        // table 表达式的最后一部分为表引用
        $segments = explode(' ', trim($table));
        return array_pop($segments);
    }

    /**
     * 生成 SELECT 查询语句
     *
     * @param  array $parts 各个 SELECT 语句的组成部分
     * @param  array $prefixes
     * @return string
     */
    public function compileSelect(array $parts, array $prefixes)
    {
        $this->setPrefixes($prefixes);

        $this->mainTableRef = $this->tableRef($parts['table']);

        $sql = [];
        foreach ($this->selectParts as $selectPart) {
            if (isset($parts[$selectPart]) && $parts[$selectPart]) {
                $method           = 'build' . ucfirst($selectPart);
                $sql[$selectPart] = $this->$method($parts[$selectPart]);
            }
        }

        return implode(' ', array_filter($sql, function ($value) {
            $value = (string)$value;

            return $value !== '';
        }));
    }

    /**
     * 构造 SELECT ... 条件
     *
     * @param  array $partSelect
     * @return string
     */
    protected function buildSelect(array $partSelect)
    {
        return 'SELECT ' . $this->columnize($partSelect);
    }

    /**
     * 构造 FROM .. 条件
     *
     * @param string|Expression $partTable
     * @return string
     */
    protected function buildTable($partTable)
    {
        return 'FROM ' . $this->quoteTableExpr($partTable);
    }

    /**
     * 构造 [LEFT|INNER] JOIN 条件
     *
     * @param  array $partJoin
     * @return string
     */
    protected function buildJoin(array $partJoin)
    {
        $sql = [];
        foreach ($partJoin as $join) {
            $table = $this->quoteTableExpr($join->table);

            $clauses = [];
            foreach ($join->clauses as $clause) {
                $column1   = $this->quoteField($clause['column1']);
                $column2   = $this->quoteField($clause['column2']);
                $operator  = $clause['operator'];
                $connector = $clause['connector'];

                $clauses[] = "$connector $column1 $operator $column2";
            }

            // 去掉第一个条件的 and/or
            $search     = ['AND ', 'OR '];
            $clauses[0] = str_replace($search, '', $clauses[0]);

            $sql[] = sprintf('%s JOIN %s ON %s',
                $join->type, $table, implode(' ', $clauses));
        }

        return implode(' ', $sql);
    }

    /**
     * 构造 WHERE/HAVING 查询条件
     *
     * @param  array  $conditions
     * @param  string $partType
     * @return string
     */
    protected function buildConditions(
        array $conditions,
        string $partType = 'where'
    ) {
        if (!$conditions) {
            return '';
        }

        $predicates = [];
        foreach ($conditions as $condition) {
            if (isset($condition['parts'])) {
                $predicates[] = $condition['connector'] . ' ' .
                    $this->predicateNested($condition, $partType);
            } else {
                $predicates[] = $condition['connector'] . ' ' .
                    $this->predicate($condition);
            }
        }

        if (count($predicates) > 0) {
            $predicates = implode(' ', $predicates);
            // 去掉第一个查询条件前的 AND 或 OR
            return strtoupper($partType) . ' ' . preg_replace('/AND |OR /', '',
                $predicates, 1);
        }

        return '';
    }

    /**
     * 构造 WHERE ... 条件
     *
     * @param  array $partWhere
     * @return string
     */
    protected function buildWhere(array $partWhere)
    {
        return $this->buildConditions($partWhere, 'where');
    }

    /**
     * 生成一个查询条件
     *
     * @param  array $condition
     * @return string
     */
    protected function predicate(array $condition)
    {
        $column = $this->quoteField($condition['column']);
        if (is_array($condition['value'])) {  // IN, NOT IN, BETWEEN, NOT BETWEEN
            if (stripos($condition['operator'],
                    'IN') !== false
            ) {  // IN, NOT IN
                $parameters = $this->parameterize($condition['value']);

                return $column . ' ' . $condition['operator'] . ' (' . $parameters . ')';
            } else {
                $min = $this->parameter($condition['value'][0]);
                $max = $this->parameter($condition['value'][1]);

                return $column . ' ' . $condition['operator'] . ' ' . $min . ' AND ' . $max;
            }
        } else {
            if ($condition['operator'] === null && $condition['value'] === null) {
                return $condition['column'];
            } else {
                $parameter = $this->parameter($condition['value']);
                return $column . ' ' . $condition['operator'] . ' ' . $parameter;
            }
        }
    }

    /**
     * 生成嵌套的一个查询条件
     *
     * @param  array  $condition
     * @param  string $partType
     * @return string
     */
    protected function predicateNested(
        array $condition,
        string $partType = 'where'
    ) {
        $parts = $condition['parts'][$partType];
		//\Log::log('test', var_export($parts, true) .'|'. count($parts));

        $start = strlen($partType) + 1;
        if (count($parts) === 1) {
            return substr($this->buildConditions($parts), $start);
        } else {
            return '(' . substr($this->buildConditions($parts), $start) . ')';
        }
    }

    /**
     * 构造 GROUP BY ... 条件
     *
     * @param  array $partGroupBy
     * @return string
     */
    protected function buildGroupBy(array $partGroupBy)
    {
        return 'GROUP BY ' . $this->columnize($partGroupBy);
    }

    /**
     * 构造 HAVING ... 条件
     *
     * @param  array $partHaving
     * @return string
     */
    protected function buildHaving(array $partHaving)
    {
        return $this->buildConditions($partHaving, 'having');
    }

    /**
     * 构造 ORDER BY ... 条件
     *
     * @param  array $partOrderBy
     * @return string
     */
    protected function buildOrderBy(array $partOrderBy)
    {
        $sql = [];
        foreach ($partOrderBy as $orderBy) {
            $sql[] = $this->quoteField($orderBy['column']) . ' ' . $orderBy['direction'];
        }

        return 'ORDER BY ' . implode(', ', $sql);
    }

    /**
     * 构造 LIMIT ... 条件
     *
     * @param  array $partLimit
     * @return string
     */
    protected function buildLimit(array $partLimit)
    {
        return sprintf('LIMIT %d, %d', $partLimit['offset'],
            $partLimit['count']);
    }

    /**
     * 生成 INSERT/REPLACE INTO ... SQL 语句
     *
     * @param  string $type
     * @param  array  $parts
     * @param  array  $values 按引用传递，可能需要修改值
     * @param  array  $prefixes
     * @return string
     */
    public function compilePersist(
        string $type,
        array $parts,
        array& $values,
        array $prefixes
    ) {
        $this->setPrefixes($prefixes);
        $table = $this->quoteTableExpr($parts['table']);
        if (!is_array(reset($values))) { // batch insert 模式
            $values = [$values];
        }

        $columns    = $this->columnize(array_keys(reset($values)));
        $parameters = $this->parameterize(reset($values));
        $parameters = implode(', ',
            array_fill(0, count($values), "($parameters)"));

        $type = strtoupper($type);

        return "$type INTO {$table} ({$columns}) VALUES {$parameters}";
    }

    /**
     * 生成 UPDATE ... SQL 语句
     *
     * @param  array $parts
     * @param  array $values 按引用传递，可能需要修改值
     * @param  array $prefixes
     * @return string
     */
    public function compileUpdate(array $parts, array& $values, array $prefixes)
    {
        $this->setPrefixes($prefixes);
        $table   = $this->quoteTableExpr($parts['table']);
        $columns = [];
        foreach ($values as $column => $value) {
            $columns[] = $this->quoteField($column) . ' = ' . $this->parameter($value);
        }
        $columns = implode(', ', $columns);

        $whereClause = $this->buildWhere($parts['where']);
        if (!$whereClause) {
            throw new RuntimeException('No where clause provided for update operation');
        }

        $sql = "UPDATE {$table} SET {$columns} " . $whereClause;

        if ($parts['orderBy']) {
            $sql .= ' ' . $this->buildOrderBy($parts['orderBy']);
        }
        if ($parts['limit']) {
            $sql .= ' LIMIT ' . array_pop($parts['limit']) . ' ';
        }

        return $sql;
    }

    /**
     * 生成 UPDATE ... SQL 语句
     *
     * @param  array      $parts
     * @param  array      $values       按引用传递，可能需要修改值
     * @param  array|null $valuesUpdate 记录存在时要更新的值
     * @param  array      $prefixes
     * @return string
     */
    public function compileUpsert(
        array $parts,
        array& $values,
        $valuesUpdate,
        array $prefixes
    ) {
        $this->setPrefixes($prefixes);
        $table   = $this->quoteTableExpr($parts['table']);
        $columns = [];
        foreach ($values as $column => $value) {
            $columns[] = $this->quoteField($column) . ' = ' . $this->parameter($value);
        }
        $columns = implode(', ', $columns);

        if ($valuesUpdate === null) {
            $columnsUpdate = $columns;
        } else {
            $columnsUpdate = [];
            foreach ($valuesUpdate as $col => $val) {
                $columnsUpdate[] = $this->quoteField($col) . ' = ' . $this->parameter($val);
            }
            $columnsUpdate = implode(', ', $columnsUpdate);
        }

        $sql = "INSERT INTO {$table} SET {$columns} ON DUPLICATE KEY UPDATE {$columnsUpdate}";

        return $sql;
    }

    /**
     * 生成 ... LIMIT ...
     *
     * @param  array $partLimit
     * @return string
     */
    protected function buildDeleteLimit(array $partLimit)
    {
        if (count($partLimit) > 0) {
            return 'LIMIT ' . array_pop($partLimit);
        }

        return '';
    }

    /**
     * 生成 DELETE FROM ... SQL 语句
     *
     * @param  array $parts
     * @param  array $prefixes
     * @return string
     */
    public function compileDelete(array $parts, array $prefixes)
    {
        $this->setPrefixes($prefixes);
        $table = $this->quoteTableExpr($parts['table']);

        $whereClause = $this->buildWhere($parts['where']);
        if (!$whereClause) {
            throw new RuntimeException('No where clause provided for delete operation');
        }

        return "DELETE FROM {$table} " . $whereClause . ' ' .
        $this->buildDeleteLimit($parts['limit']);
    }

    /**
     * 生成 DESCRIBE table_name
     *
     * @param  string|Expression $table
     * @param  array  $prefixes
     * @return string
     */
    public function compileListFields($table, array $prefixes)
    {
        $this->setPrefixes($prefixes);
        $table = $this->quoteTableExpr($table);

        return 'DESCRIBE ' . $table;
    }

    /**
     * 设置表前缀、字段前缀
     *
     * @param array $prefixes
     */
    protected function setPrefixes(array $prefixes)
    {
        $this->tablePrefix = $prefixes['table'];
        $this->fieldPrefix = $prefixes['field'];
    }
}
