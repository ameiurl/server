<?php
namespace My\data;

//所有的Data都是单例，表示数据存储操作。非ddd中的repository
abstract class BaseData{
    /**
     * Db对象
     * @var \think\db\Query
     */
    protected $db;

    /**
     * @var 表名
     */
	protected $table;

    /**
     * 子类对象池
     * @var array
     */
    private static $_instances = [];

    /**
     * 获取数据单例对象
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if(!isset(self::$_instances[$class])){
            self::$_instances[$class] = new static();
        }

		self::$_instances[$class]->setTableName();
        return self::$_instances[$class];
    }

    /**
     * 设置表名
     */
    private function setTableName() {
        $this->db->setTable($this->table);
    }


    //是否收保护的构造来屏蔽外部实例话子类
    protected function __construct()
    {
		if(empty($this->table)){
			$this->throwError('Unknow The Table');
		}

        $this->db = new DbTemplate();
    }

    public function getOne($where = null, $field = '*', $order = []){
		return $this->db->find($where, $field, $order);
    }

    public function getList($where = null, $page = 1, $pagesize = 10, $fields = '*', $order = []){
		return $this->db->findAll($where, $fields, $order, [$page, $pagesize]);
    }

    /*
     * 聚合统计
     */
    public function getCount($where = null){
		return $this->db->findValue($where, "#COUNT(*)");
    }

    /**
     * 获取表的字段
     * @return array
     */
    public function tableFields(){
        $info = $this->db->getTableInfo($this->table, 'fields');
        $fields = [];
        if(!empty($info)){
            foreach ($info as $val) {
                $fields[$val] = '';
            }
        }
        return $fields;
    }

    /**
     * 通过ID获取数据
     * @param int $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDataById($id = 0){
        return $this->db->find(intval($id));
    }

    /**
     * 获取SQL语句
     * @return string
     */
    public function getLastSql(){
        return $this->db->lastQuery();
    }

    /**
     * 通过IDs获取数据
     * @param array $where 查询条件
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDataList($where = null)
    {
        return $this->db->findAll($where);
    }

    /**
     * 保存当前数据对象
     * @access public
     * @param array  $data     数据
     * @param array  $where    更新条件
     * @param string $sequence 自增序列名
     * @return integer
     */
    public function saveInfo($data = [], $where = null, $sequence = null)
    {
        $id = 0;
        if (isset($data['id'])){
            $id = $data['id'];
            $this->db->update(['id'=>$id], $data);
        }else{
            $id = $this->db->insert($data);
        }

        return $id;
    }

    /**
     * 保存多个数据到当前数据对象
     * @access public
     * @param array   $dataSet 数据
     * @param boolean $replace 是否自动识别更新和写入
     * @return array|false
     * @throws \Exception
     */
    //public function saveInfoAll($dataSet, $replace = true){
        //return $this->db->saveAll($dataSet, $replace);
    //}

    /**
     * 通过Id删除数据
     * @param int $id
     * @return bool
     */
    public function deleteById($id = 0){
        $id = intval($id);
        if (empty($id)){
            return false;
        }
        return $this->db->delete('id', $id);
    }

	/**
	 * groupby
	 */
	public function getAll($where = [], $field = '*', $order = [], $group = [])
	{
		if (empty($where)) {
			return [];
		}
		return $this->db->query()
			->select($field)
			->where($where)
            ->orderBy($order)
            ->groupBy($group)
			->getRows();
	}

    /**
     * 抛出错误信息
     * @param string $msg 错误信息
     */
    protected function throwError($msg = ''){
        throw new \RuntimeException($msg);
    }
}
