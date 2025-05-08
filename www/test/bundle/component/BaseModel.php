<?php
/**
 * User: nathena
 * Date: 2017/8/21 0021
 * Time: 10:52
 */

namespace My\component;

abstract class BaseModel extends BaseComponent
{
    /**
     * 上下文数据
     */
    protected $token;

    public function __construct($data)
    {
        if(is_array($data) || is_object($data)){
            $this->data = $data;
        }else{
            $this->data = $this->_init($data);
        }
        //$this->token = Context::getInstance()->getToken();
    }

    /**
     * 初始化
     * @param $data 初始化数据
     */
    protected function _init($data)
    {
        $this->throwError(get_class($this).".".__METHOD__.": Not Implemented");
    }

    public function __get($key)
    {
        if(!empty($key)){
            if(isset($this->data[$key])){
                return get_value($this->data, $key);
            }
            $property_method = "get".ucfirst($key);
            if(method_exists($this,$property_method)){
                return $this->{$property_method}();
            }
        }
        return '';
    }

    public function __set($key, $val)
    {
        if (is_object($this->data)){
            $this->data->$key = $val;
        }else{
            $this->data[$key] = $val;
        }
    }

    public function __isset($key)
    {
        if (is_object($this->data)){
            return property_exists($this->data, $key);
        }else{
            return isset($this->data[$key]);
        }
    }

    public function __unset($key)
    {
        if (is_object($this->data)){
            unset($this->data->$key);
        }else{
            unset($this->data[$key]);
        }
    }
}
