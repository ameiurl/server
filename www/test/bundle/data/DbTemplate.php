<?php
namespace My\data;

use Butterfly\Database\Model;

class DbTemplate extends Model{
	
    public $data;

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = 'update_time';

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->data = !empty($data) ? $data : (array)$this->getDataAttribute('data');
    }

    /**
     * 获取数据模型中的属性
     * 比如读取保护属性[data:protected]  $tableData->getModelAttribute('data');
     * @param $attrName 属性名称
     * @return array|object|null
     */
    public function getDataAttribute($attrName){
        if (property_exists($this, $attrName)){
            return $this->$attrName;
        }else{
            return null;
        }
    }

	public function setTable($table)
	{
		self::$table = $table;
	}

}
