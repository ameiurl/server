<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2019/3/14 0014
 * Time: 9:32
 */
namespace Dragonfly\foundation\traits;

trait ObjectAccessWrapTrait
{
    protected $data = [];

    public function getData()
    {
        return $this->data;
    }

    public function getObjectProperties()
    {
        $data = get_object_vars($this);
        return array_merge($this->getData(),$data);
    }

    public function __get($key)
    {
        if(!empty($key)){
            if(isset($this->data[$key])){
                return $this->data[$key];
            }

            if(property_exists($this,$key))
            {
                return $this->{$key};
            }

            $property_method = "get".ucfirst($key);
            if(method_exists($this,$property_method)){
                $this->data[$key] = $this->{$property_method}();
                return $this->data[$key];
            }
        }
        return '';
    }

    public function __set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }

    public function current()
    {
        return current($this->data);
    }

    public function next()
    {
        return next($this->data);
    }

    public function key()
    {
        return key($this->data);
    }

    public function valid()
    {
        return key($this->data) !== null;
    }

    public function rewind()
    {
        return reset($this->data);
    }
}
