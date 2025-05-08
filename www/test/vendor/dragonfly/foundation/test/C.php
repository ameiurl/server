<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2019/3/14 0014
 * Time: 9:29
 */

require_once 'test.php';

class C
{
    use Base;

    public function test()
    {
        \cncn\foundation\util\Logger::getInstance()->info("1111".__DIR__.":".__CLASS__);
    }
}

trait Base
{
    protected $data = [];

    public function getData()
    {
        $data = get_object_vars($this);
        unset($data['data']);
        return array_merge($this->data,$data);
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
}

$c = new C();
$c->a="12321";
print_r($c->getData());

$c->test();