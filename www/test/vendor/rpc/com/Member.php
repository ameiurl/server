<?php
/**
 * @copyright Copyright (c) 2016 Xiamen Xinxin Information Technologies, Inc.
 */
namespace Rpc\Com;

use Dragonfly\Rpc\Rpc;
/**
 * Member - member_temp 表操作相关
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 */
class Member
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
        return $this->rpc->call('member', 'updateByUid', [$uid, $data]);
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
        return $this->rpc->call('member', 'findByUid', [$uid, $fields]);
    }

    /**
     * 根据 mobile 获取用户信息
     *
     * @param string $mobile
     * @param string $fields
     * @return array
     */
    public function findByMobile($mobile, $fields = '*')
    {
        return $this->rpc->call('member', 'findByMobile', [$mobile, $fields]);
    }

    /**
     * 根据 mobiles 数组获取用户信息
     *
     * @param array $mobiles
     * @param string $fields
     * @return array
     */
    public function findAllByMobiles($mobiles, $fields = '*')
    {
        if (!$mobiles) {
            return [];
        }

        return $this->rpc->call('member', 'findAllByMobiles', [$mobiles, $fields]);
    }

    /**
     * 根据 省份 获取用户信息
     *
     * @param string $prov
     * @param string $maxUid
     * @param string $fields
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function findByZone($prov, $maxUid, $fields='*', $page = 1, $perPage=10)
    {
        return $this->rpc->call('member', 'findByZone', [$prov, $maxUid, $fields, $page, $perPage]);
    }

    /**
     * 根据邮箱获取用户信息
     *
     * @param int    $email  邮箱
     * @param string $fields 要获取的字段列表
     * @return array
     */
    public function findByEmail($email, $fields = '*')
    {
        return $this->rpc->call('member', 'findByEmail', [$email, $fields]);
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

        return $this->rpc->call('member', 'findAllByUids', [$uids, $fields]);
    }

    /**
     * 获取第三方登录的信息
     *
     * @param int    $uid
     * @param string $fields
     * @return mixed
     */
    public function socialLoginInfo($uid, $fields = '*')
    {
        return $this->rpc->call('member', 'socialLoginInfo', [$uid, $fields]);
    }

    /**
     * 根据 uid 获取积分历史
     *
     * @param int $uid     uid
     * @param int $page    第几页
     * @param int $perPage 每页显示的记录数
     * @return array
     */
    public function markHistoryByUid($uid, $page = 1, $perPage = 10)
    {
        return $this->rpc->call('member', 'markHistoryByUid', [$uid, $page, $perPage]);
    }

    /**
     * 按 uid 获取 member_field 表的数据
     *
     * @param int    $uid    uid
     * @param string $fields 要获取的字段列表
     * @return array
     */
    public function findMemberFieldByUid($uid, $fields = '*')
    {
        return $this->rpc->call('member', 'findMemberFieldByUid', [$uid, $fields]);
    }

    /**
     * 按 uid 数组获取 uc_members 表的数据
     *
     * @param array    $uids    uids
     * @param string $fields 要获取的字段列表
     * @return array
     */
    public function findAllUcMembersByUids($uids, $fields = '*')
    {
        return $this->rpc->call('member', 'findAllUcMembersByUids', [$uids, $fields]);
    }

    /**
     * 按 uid 更新 uc_members 表的数据
     *
     * @param int    $uid    uid
     * @param array $data   要更新的字段列表
     * @return array
     */
    public function updateUcMembersByUid($uid, $data = [])
    {
        return $this->rpc->call('uc', 'updateByUid', [$uid, $data]);
    }

    /**
     * 按 id 主键更新 member_field 表数据
     *
     * @param int   $id   member_field 表主键
     * @param array $data 要更新的数据
     * @return false|int
     */
    public function updateMemberFieldById($id, $data)
    {
        return $this->rpc->call('member', 'updateMemberFieldById', [$id, $data]);
    }

    /**
     * 插入 member_field 表数据
     *
     * @param array $data
     * @return false|int
     */
    public function insertMemberField($data)
    {
        return $this->rpc->call('member', 'insertMemberField', [$data]);
    }

    /**
     * 获取 member_connect 表数据
     *
     * @param  string  $usertype          第三方帐号类型
     * @param  string  $socialUid         第三方帐号id
     * @param  string  $unionid           微信唯一id
     * @return false|int
     */
    public function getMemberConnect($usertype, $socialUid, $unionid = '')
    {
        return $this->rpc->call('member', 'findMemberConnect', [$usertype, $socialUid, $unionid]);
    }

    /**
     * 按 Uid 获取 member_connect 表数据
     *
     * @param  int  $uid        用户id
     * @param string $fields    要获取的字段列表
     * @return array
     */
    public function findMemberConnectByUid($uid, $fields = '*', $usertype = '')
    {
        return $this->rpc->call('member', 'findMemberConnectByUid', [$uid, $fields, $usertype]);
    }

    /**
     * 插入 member_connect 表数据
     *
     * @param array $data 要插入的数据
     * @return false|int  成功返回表的ID主键，失败返回 false
     */
    public function insertMemberConnect($data)
    {
        if (empty($data)) {
            return false;
        }
        return $this->rpc->call('member', 'insertMemberConnect', [$data]);
    }

    /**
     +----------------------------------------------------------
     * 用户注册接口
     +----------------------------------------------------------
     * @param string $username 用户名
     * @param string $password 用户密码
     * @param string $email 邮箱
     +----------------------------------------------------------
     * @return $int
        大于 0:返回用户 ID，表示用户注册成功
        -1:用户名不合法
        -2:包含不允许注册的词语
        -3:用户名已经存在
        -4:Email 格式有误
        -5:Email 不允许注册
        -6:该 Email 已经被注册
     */
    public function uc_user_register($username, $password, $email) {
         $res = $this->rpc->call('uc', 'ucUserRegister', [$username, $password, $email]);
         return $res;
    }

    
    /**
     * 获取加入黑名单的原因
     *
     * @param string $usertype 对应原来的$this->source
     * @param string $account 对应原来的$this->member_identifier
     * @param array $data 更新数据的关联数组
     * @return bool|false|int
     */
    public function updateMemberConnectByAccount($usertype = '', $account = '', $data = array())
    {
        return $this->rpc->call('member', 'updateMemberConnectByAccount', [$usertype, $account, $data]);
    }

    
    public function insertMemberTemp($data)
    {
        if (!$data) {
            return 0;
        }
        return $this->rpc->call('member', 'insertMemberTemp', [$data]);
    }
}
