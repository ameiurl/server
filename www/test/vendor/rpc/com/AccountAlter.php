<?php
/**
 * @copyright Copyright (c) 2015 Xiamen Xinxin Information Technologies, Inc.
 */
namespace Rpc\Com;

use Dragonfly\Rpc\Rpc;

/**
 * AccountAlter - 账号合并、删除
 * 
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com> 
 * @since 2015-08-25 09:13:25
 */
class AccountAlter {
    /**
     * 合并 $from_uid 账号到 $to_uid
     *
     * @param  int $from_uid 要合并的 uid
     * @param  int $to_uid   合并到的 uid
     * @return bool          成功返回 true，失败返回 false
     */
    public function merge($from_uid, $to_uid) {
        $rpc = new Rpc();
        $res = $rpc->call('accountAlter', 'merge', [$from_uid, $to_uid]);
        return $res;
    }

    /**
     * 移除账号
     *
     * @param  int $uid 用户 uid
     * @return void
     */
    public function remove_account($uid) {
        $rpc = new Rpc();
        $rpc->call('accountAlter', 'removeAccount', [$uid]);
    }
    
    /**
     * 拉黑
     *
     * @param  int $uid 用户 uid
     * @param  int $log 说明
     * @return void
     */
    public function ban_account($uid, $log) {
        $rpc = new Rpc();
        $rpc->call('accountAlter', 'banAccount', [$uid, $log]);
    }
    
    /**
     * 移除黑名单
     *
     * @param  int $uid 用户 uid
     * @return void
     */
    public function unban_account($uid) {
        $rpc = new Rpc();
        $rpc->call('accountAlter', 'unBanAccount', [$uid]);
    }
}
