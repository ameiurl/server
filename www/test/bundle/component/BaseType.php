<?php
/**
 * Type基类
 * Created by PhpStorm.
 * User: yuzq
 * Date: 2018/2/6
 * Time: 17:29
 */

namespace My\component;

abstract class BaseType
{
    /**
     * 类型列表
     * @var array
     */
    protected static $typeList = [];

    public static function getTypeList()
    {
        return static::$typeList;
    }

    /**
     * 检查类型是否存在
     *
     * @param $typeKey
     * @return bool
     */
    public static function checkType($typeKey)
    {
        $types = static::getTypeList();
        return array_key_exists($typeKey,$types);
    }

    /**
     * 获取类型名称
     *
     * @param $typeKey
     * @return mixed|string
     */
    public static function getType($typeKey)
    {
        $types = static::getTypeList();
        return array_key_exists($typeKey,$types) ? $types[$typeKey] : '';
    }
}
