<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2019/3/26 0026
 * Time: 16:32
 */

namespace Dragonfly\foundation\traits;


trait TypeObjectTrait
{
    /**
     * 获取CodeList
     * @return array
     */
    public static function getTypeList()
    {
        if(!static::$typeList)
        {
            try
            {
                $class = new \ReflectionClass(static::class);
                /**
                 * @var \ReflectionProperty[]|\ReflectionClassConstant[] $properties
                 */
                $properties = [];
                if(version_compare(PHP_VERSION,"7.1") == 1)
                {
                    $properties = array_merge($properties,$class->getReflectionConstants());
                }
                $properties = array_merge($properties,$class->getProperties( \ReflectionProperty::IS_STATIC ));
                $pattern = "#@PHPDoc\s*(.+)#";
                foreach($properties as $property)
                {
                    $doc = $property->getDocComment();
                    preg_match_all($pattern, $doc, $matches, PREG_PATTERN_ORDER);
                    $msg = '';
                    if(count($matches)==2)
                    {
                        $msg = implode(",",$matches[1]);
                    }
                    $search   = array("\r\n", "\n", "\r");
                    static::$typeList[$property->getValue()] = str_replace($search, '', $msg);
                }
            }
            catch (\Exception $e)
            {

            }
        }

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
     * @param string $default
     * @return mixed|string
     */
    public static function getType($typeKey, $default = '')
    {
        $types = static::getTypeList();
        return array_key_exists($typeKey,$types) ? $types[$typeKey] : $default;
    }

    protected static $typeList = [];
}
