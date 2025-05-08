<?php
/**
 * User: nathena
 * Date: 2018/2/6 0006
 * Time: 10:53
 */
//Context 功能上下文会话
namespace erp\component;

use erp\component\token\AbstractToken;
use erp\component\token\SimpleToken;

class Context
{
    private static $_instance;

    /**
     * @return Context
     */
    public static function getInstance()
    {
        if(empty(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return AbstractToken
     */
    public function getToken()
    {
        return $this->token;
    }

    private $token;

    private function __construct()
    {
        $this->token = new SimpleToken();
    }
}