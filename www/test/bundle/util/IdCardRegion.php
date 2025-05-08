<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2018/7/25 0025
 * Time: 14:16
 */

namespace erp\util;


class IdCardRegion
{
    private static $instance = null;

    private $region = [];

    private function __construct()
    {
        $this->region = include CONF_PATH."region.php";
    }

    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getNativePlace($district_code)
    {
        return $this->region[$district_code]? $this->region[$district_code] : '';
    }
}