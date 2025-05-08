<?php
/**
 * @copyright Copyright (c) 2016 Xiamen Xinxin Information Technologies, Inc.
 */
namespace Rpc\Com;

use Dragonfly\Rpc\Rpc;

/**
 * Uc - ucenter.uc_members 表操作相关
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 */
class Uc
{
    /**
     * Rpc 调用客户端类
     *
     * @var Rpc
     */
    protected $rpc;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->rpc = new Rpc();
    }

    /**
     *  更新用户信息
     *
     * @param int   $uid
     * @param array $data 要更新的字段键值对数组
     * @return int
     */
    public function updateByUid($uid, $data)
    {
        return $this->rpc->call('uc', 'updateByUid', [$uid, $data]);
    }

    /**
     * 根据 uid 获取用户信息
     *
     * @param int    $uid
     * @param string $fields
     * @return array
     */
    public function findByUid($uid, $fields = '*')
    {
        return $this->rpc->call('uc', 'findByUid', [$uid, $fields]);
    }

    /**
     * 根据 uid 数组获取用户信息
     *
     * @param array  $uids   uid 数组
     * @param string $fields 要获取的字段列表
     * @return array
     */
    public function findAllByUids(array $uids, $fields = '*')
    {
        if (!$uids) {
            return [];
        }

        return $this->rpc->call('uc', 'findAllByUids', [$uids, $fields]);
    }

    /**
     * 按天统计注册人数
     *
     * @param int $beginTimestamp
     * @param int $endTimestamp
     * @return array
     */
    public function numByDay($beginTimestamp, $endTimestamp)
    {
        return $this->rpc->call('uc', 'numByDay', [$beginTimestamp, $endTimestamp]);
    }

    /**
    * 获取用户数据
    *
    * @param string $username 用户名
    * @param int $isuid 0 默认用户名，1 uid
    * @return array|int
    * 成功时返回数组，array(用户ID，用户名，Email)
    */
    public function ucGetUser($username, $isuid = 0)
    {
        return $this->rpc->call('uc', 'ucGetUser', [$username, $isuid]);
    }
    
    /**
    * 获取用户数据，根据username or uid
    *
    * @param string $username 用户名
    * @param int $uid  
    * @return array|int
    * 成功时返回数组，array(用户ID，用户名，Email)
    */
    public function findByUidOrUsername($username, $uid)
    {
        return $this->rpc->call('uc', 'findByUidOrUsername', [$username, $uid]);
    }
}
