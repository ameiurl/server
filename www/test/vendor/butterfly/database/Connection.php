<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Database;

use ErrorException;
use PDO;
use PDOException;
use Butterfly\Database\Query\Expression;

/**
 * 数据库连接
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Connection
{
    /**
     * 数据库连接配置
     *
     * @var array
     */
    protected $config = [];

    /**
     * 语法生成器
     *
     * @var Query\Grammar
     */
    protected $grammar;

    /**
     * 字段前缀
     *
     * @var string
     */
    protected $fieldPrefix = '';

    /**
     * 处理语句对象
     *
     * @var \PDOStatement
     */
    protected $stmt;

    /**
     * 是否已成功连接到数据库
     *
     * @var bool
     */
    protected $connected = false;

    /**
     * 上次查询执行的SQL语句
     *
     * @var string
     */
    protected $lastQuery = '';

    /**
     * 查询日志
     *
     * @var array
     */
    protected $logs = [];

    /**
     * 是否启用日志
     *
     * @var bool
     */
    protected $enableLog = false;

    /**
     * 是否使用指定主库查询
     *
     * @var bool
     */
    protected $useMaster = false;

    /**
     * 当前使用的 PDO 连接
     *
     * @var PDO
     */
    protected $currentPdo;

    /**
     * 所有数据库连接配置
     *
     * @var array
     */
    protected $allConfigs = [];

    /**
     * 所有的 PDO 连接
     *
     * @var PDO
     */
    protected $pdos = [];

    /**
     * 连接失败时，已重连的次数
     *
     * @var int
     */
    protected $reconnectRetries = 0;

    /**
     * 连接失败时，允许重连的次数
     *
     * @var int
     */
    protected $reconnectThrehold = 1;

    /**
     * 默认的 PDO 连接选项
     *
     * @var array
     */
    protected $defaultOptions = [
        // http://cn2.php.net/manual/en/pdo.setattribute.php
        PDO::ATTR_CASE               => PDO::CASE_NATURAL,       // 返回字段大小写处理方式
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // 错误报告模式
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // 数据获取方式
        PDO::ATTR_STRINGIFY_FETCHES  => true,  // 是否把数据库中获取到的数值型数据转换成字符串
        PDO::ATTR_EMULATE_PREPARES   => false,  // 是否使用客户端模拟 PREPARE
        PDO::ATTR_TIMEOUT            => 10,  // 超时时间，单位秒
    ];

    /**
     * Connection constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config    = $config;
        $this->enableLog = $this->config['log_queries'] ?? false;

        $this->initConfigs();
    }

    /**
     * 初始化数据库连接配置
     */
    protected function initConfigs()
    {
        $slaves = $this->config['slaves'] ?? [];

        $masterConfig = $this->config;
        unset($masterConfig['slaves'], $masterConfig['log_queries']);
        $masterConfig['options'] += $this->defaultOptions;

        $this->allConfigs[] = $masterConfig;

        foreach ($slaves as $slave) {
            $this->allConfigs[] = $slave + $masterConfig;
        }
    }

    /**
     * 获取当前 PDO 连接
     *
     * @return PDO
     */
    protected function currentPdo()
    {
        if ($this->currentPdo === null) {
            if ($this->useMaster) {
                $this->currentPdo = $this->masterPdo();
            } else {
                $this->currentPdo = $this->randomPdo();
            }
        }

        return $this->currentPdo;
    }

    /**
     * 获取 PDO 连接
     *
     * @param string $sql          当前要查询的 SQL
     * @param bool   $reuseConnect 是否重用已有的数据库连接
     * @return PDO
     */
    protected function pdo($sql, $reuseConnect = true)
    {
        if ($this->useMaster) {
            return $this->masterPdo($reuseConnect);
        }

        // 根据 SQL 模式获取 PDO
        // 'S' 或 's' 字符开头的认为是查询模式
        // 此字符开头的有： select, show
        if (strtoupper($sql[0]) === 'S') {
            return $this->randomPdo($reuseConnect);
        } else {
            return $this->masterPdo($reuseConnect);
        }
    }

    /**
     * 按连接索引获取 PDO 连接
     *
     * @param int  $index        数据库连接配置的键值
     * @param bool $reuseConnect 是否重用已有的数据库连接
     * @return PDO
     */
    private function pdoByIndex($index = 0, $reuseConnect = true)
    {
        if (!isset($this->pdos[$index]) || !$reuseConnect) {
            $this->pdos[$index] = $this->currentPdo =
                $this->connect($this->allConfigs[$index]);
        }

        return $this->pdos[$index];
    }

    /**
     * 随机获取一个 PDO 连接
     *
     * @param bool $reuseConnect 是否重用已有的数据库连接
     * @return PDO
     */
    protected function randomPdo($reuseConnect = true)
    {
        $index = array_rand($this->allConfigs);

        return $this->pdoByIndex($index, $reuseConnect);
    }

    /**
     * 获取主库的 PDO 连接
     *
     * @param bool $reuseConnect 是否重用已有的数据库连接
     * @return PDO
     */
    protected function masterPdo($reuseConnect = true)
    {
        return $this->pdoByIndex(0, $reuseConnect);
    }

    /**
     * 创建数据库连接
     *
     * @param array PDO 连接配置
     * @return PDO
     */
    protected function connect($config) : PDO
    {
        return new PDO($config['dsn'], $config['username'], $config['password'],
            $config['options']);
    }

    /**
     * 获取数据库驱动名称
     *
     * @return string
     */
    public function getDriver() : string
    {
        return $this->currentPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * 获取查询构造器
     *
     * @return Query\Builder
     */
    public function builder() : Query\Builder
    {
        return new Query\Builder($this, $this->grammar());
    }

    /**
     * 获取语法生成器
     *
     * @return Query\Grammar
     */
    public function grammar() : Query\Grammar
    {
        if ($this->grammar === null) {
            $this->grammar = new Query\Grammar();
        }

        return $this->grammar;
    }

    /**
     * 获取带表名的查询构造器
     *
     * @param string $table
     * @return Query\Builder
     */
    public function table(string $table) : Query\Builder
    {
        return $this->builder()->table($table);
    }

    /**
     * 返回带 select 条件的查询构造器
     *
     * @param string|array $fields
     * @return Query\Builder
     */
    public function select(string $fields) : Query\Builder
    {
        return $this->builder()->select($fields);
    }

    /**
     * 选择要查询的数据库
     *
     * @param  string $dbName 数据库名
     * @return mixed
     */
    public function selectDb(string $dbName)
    {
        return $this->currentPdo()->query("USE $dbName");
    }

    /**
     * 删除字段前缀
     *
     * @param  string $fieldName
     * @return string
     */
    protected function removeFieldPrefix(string $fieldName) : string
    {
        return $this->fieldPrefix != '' ?
            str_replace($this->fieldPrefix, '', $fieldName) : $fieldName;
    }

    /**
     * 删除所有字段前缀
     *
     * @param  array $fields
     * @return array
     */
    protected function removeFieldPrefixes(array $fields) : array
    {
        if (!$fields) {
            return [];
        }

        if ($this->fieldPrefix === '') {
            return $fields;
        }

        $arr = [];
        foreach ($fields as $key => $field) {
            if (is_array($field)) {
                $arr[$key] = $this->removeFieldPrefixes($field);
            } else {
                $key       = $this->removeFieldPrefix($key);
                $arr[$key] = $field;
            }
        }

        return $arr;
    }

    /**
     * 获取当前的SQL错误信息
     *
     * @return string
     */
    public function errorInfo() : string
    {
        return $this->currentPdo()->errorInfo();
    }

    /**
     * 获取当前的SQL错误代码
     *
     * @return int
     */
    public function errorCode()
    {
        return $this->currentPdo()->errorCode();
    }

    /**
     * 设置字符集
     *
     * @param  string $charset 字符集
     * @return bool
     */
    public function setCharset(string $charset)
    {
        return $this->currentPdo()->query("SET NAMES $charset");
    }

    /**
     * 转义字符串
     *
     * @param  string $str
     * @return string
     */
    public function escape(string $str) : string
    {
        return $this->currentPdo()->quote($str);
    }

    /**
     * 获取 指定表名的所有字段数组
     *
     * @param  string $sql
     * @param  string $fieldPrefix
     * @return array
     */
    public function listFields(string $sql, string $fieldPrefix = '') : array
    {
        $this->fieldPrefix = $fieldPrefix;
        $this->query($sql, [], $fieldPrefix);
        $rows    = $this->fetchAll();
        $columns = [];
        foreach ($rows as $row) {
            $columns[] = $this->removeFieldPrefix(reset($row));
        }

        return $columns;
    }

    /**
     * 获取结果集中的一行
     *
     * @param  int $fetchStyle
     * @return array|false
     */
    public function fetch($fetchStyle = PDO::FETCH_ASSOC)
    {
        return $this->stmt->fetch($fetchStyle);
    }

    /**
     * 获取结果集中的所有的记录
     *
     * @param  int $fetchStyle
     * @return array
     */
    public function fetchAll($fetchStyle = PDO::FETCH_ASSOC) : array
    {
        $arr = $this->stmt->fetchAll($fetchStyle);
        if ($fetchStyle === PDO::FETCH_ASSOC || $fetchStyle === PDO::FETCH_BOTH) {
            $arr = $this->removeFieldPrefixes($arr);
        }

        return $arr;
    }

    /**
     * 获取结果集中的所有的记录，以指定的 $key 作为返回结果的键值
     *
     * @param  string $key
     * @param  int    $fetchStyle
     * @return array
     */
    public function fetchKeyAll($key, $fetchStyle = PDO::FETCH_ASSOC) : array
    {
        $rows = [];
        while ($row = $this->fetch($fetchStyle)) {
            $row              = $this->removeFieldPrefixes($row);
            $rows[$row[$key]] = $row;
        }

        return $rows;
    }

    /**
     * 判断当前数据库连接是否有效
     *
     * @param PDOException|null $ex
     * @return bool
     */
    public function isAlive($ex = null)
    {
        if ($ex instanceof PDOException && $this->getDriver() === 'mysql') {
            return !in_array($ex->errorInfo[1], [2006, 2013]);
            // http://dev.mysql.com/doc/refman/5.7/en/gone-away.html
            // CR_SERVER_GONE_ERROR(2006) The client couldn't send a question to the server.
            // CR_SERVER_LOST(2013) The client didn't get an error when writing
            // to the server, but it didn't get a full answer (or any answer) to the question.
        } else {
            try {
                $this->currentPdo()->query('SELECT 1');
            } catch (PDOException $ex) {
                return false;
            } catch (ErrorException $ex) {
                return false;
            }

            return true;
        }
    }

    /**
     * 是否要重连
     *
     * @param PDOException $ex
     * @return bool
     */
    protected function shouldReconnect(PDOException $ex)
    {
        if ($this->reconnectRetries >= $this->reconnectThrehold) {
            // 达到重连次数上限
            return false;
        }

        if ($this->isAlive($ex)) {
            // 判断当前连接是否可用
            return false;
        }

        return true;
    }

    /**
     * SQL 预处理
     *
     * @param string $sql
     * @return null|\PDOStatement
     */
    protected function prepare($sql)
    {
        $stmt         = null;
        $reuseConnect = true;
        do {
            try {
                $stmt = $this->pdo($sql, $reuseConnect)->prepare($sql);

                break;  // 无抛出异常，则跳出循环
            } catch (PDOException $ex) {  // PDO::prepare(): MySQL server has gone away
                // 如果不应当重连，则抛出异常
                if (!$this->shouldReconnect($ex)) {
                    throw new PDOException(
                        $ex->getMessage() . ' [ ' . $this->lastQuery() . ' ] ',
                        (int)$ex->getCode(),
                        $ex->getPrevious()
                    );
                    // http://cn2.php.net/manual/en/class.pdoexception.php#95812
                    // getCode() 方法的值必须转换成整数
                }

                $this->reconnectRetries++;
                $reuseConnect = false;  // 数据库连接有问题需要重连，故不能重用数据库连接
            } catch (ErrorException $ex) {  // PDO::prepare(): MySQL server has gone away
                // 如果不应当重连，则抛出异常
                if (!$this->shouldReconnect($ex)) {
                    throw new ErrorException(
                        $ex->getMessage() . ' [ ' . $this->lastQuery() . ' ] ',
                        (int)$ex->getCode(),
                        $ex->getPrevious()
                    );
                }

                $this->reconnectRetries++;
                $reuseConnect = false;  // 数据库连接有问题需要重连，故不能重用数据库连接
            }
        } while (true);

        $this->reconnectRetries = 0;  // 重置 连接重试 次数

        return $stmt;
    }

    /**
     * 执行 SQL
     *
     * @param  string $sql         prepare sql 语句
     * @param  array  $bindings    绑定参数
     * @param  string $fieldPrefix 字段前缀
     * @return bool
     */
    public function query(string $sql, $bindings = [], $fieldPrefix = '') : bool
    {
        $bindings          = (array)$bindings;
        $this->fieldPrefix = $fieldPrefix;

        $this->setLastQuery($sql, $bindings);

        $this->stmt = $this->prepare($sql);
        if (!$this->stmt) {
            return false;
        }

        $start  = microtime(true);
        $result = $this->execute($bindings);
        $end    = microtime(true);

        if ($this->enableLog) {
            $this->logQuery($start, $end);
        }

        return $result;
    }

    /**
     * 设置执行的 SQL 语句
     *
     * @param  string $query
     * @param  array  $bindings
     * @return string
     */
    protected function setLastQuery(string $query, $bindings) : string
    {
        foreach ($bindings as $binding) {
            if ($binding instanceof Expression) {
                continue;
            }

            $binding = "'" . addslashes(addslashes($binding)) . "'";
            $query   = preg_replace('/\?/', $binding, $query, 1);
        }
        $query           = preg_replace('/\s+/', ' ', $query);  // 去除SQL语句中的多余空格
        $this->lastQuery = $query;

        return $this->lastQuery;
    }

    /**
     * 获取上次执行的查询语句
     */
    public function lastQuery() : string
    {
        return $this->lastQuery;
    }

    /**
     * 执行 SQL prepare语句
     *
     * @param  array $bindings 绑定参数
     * @return bool
     */
    protected function execute(array $bindings = []) : bool
    {
        $bindings = array_filter($bindings, function ($binding) {
            return !$binding instanceof Expression;
        });
        $bindings = array_values($bindings);

        foreach ($bindings as &$binding) {
            if ($binding === null || $binding === false) {
                $binding = (string)$binding;
            }
        }

        return $this->stmt->execute($bindings);
    }

    /**
     * 获取插入的记录的主键
     *
     * @return int
     */
    public function insertId()
    {
        return $this->currentPdo()->lastInsertId();
    }

    /**
     * 有改动到的记录的个数
     *
     * @return int
     */
    public function affectedRows()
    {
        return $this->stmt->rowCount();
    }

    /**
     * 判断上次执行的 SQL 是否发生重复数据异常
     *
     * @return bool
     */
    public function isDuplicate() : bool
    {
        return $this->currentPdo()->errorCode() === 1062;
    }

    /**
     * 生成可用于手动执行的 SQL 语句
     *
     * @param  string $sql
     * @param  array  $bindings
     * @return mixed
     */
    public function compileSql(string $sql, array $bindings) : bool
    {
        foreach ($bindings as $binding) {
            if ($binding instanceof Expression) {
                continue;
            }

            $binding = "'" . $this->escape($binding) . "'";
            $sql     = preg_replace('/\?/', $binding, $sql, 1);
        }

        return preg_replace('/\s+/', ' ', $sql);  // 去除SQL语句中的多余空格
    }

    /**
     * 记录 SQL 查询
     *
     * @param float $start 开始时间
     * @param float $end   结束时间
     */
    public function logQuery(float $start, float $end)
    {
        $time  = number_format(($end - $start) * 1000, 2);
        $query = $this->lastQuery();

        $this->logs[] = compact('query', 'time');
    }

    /**
     * 获取查询日志
     *
     * @return array
     */
    public function getLogs() : array
    {
        return $this->logs;
    }

    /**
     * 是否指定使用主库查询
     */
    public function useMaster()
    {
        $this->useMaster = true;
    }
    
    /**
     * 开启事务
     */
    public function transStart() {
        $this->currentPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->currentPdo()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $this->currentPdo()->beginTransaction();
    }
    
    /**
     * 提交事务
     */
    public function transComplete() {
        $this->currentPdo()->commit();
    }
    
    /**
     * 事务回滚
     */
    public function transRollback() {
        $this->currentPdo()->rollBack();
    }
}
