<?php
namespace My\component\lxs;

use My\component\BaseModel;
use My\data\lxs\LxsData;

class LxsModel extends BaseModel
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * @param \erp\component\初始化数据 $data
     * @return array|false|\PDOStatement|string|\think\Model|void
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _init($data)
    {
        $id = intval($data);
        return LxsData::getInstance()->getDataById($id);
    }

    /**
     * 缓存单条数据, 用于批量缓存获取失败重新设置缓存
     *
     * @param $id
     * @return mixed
     */
    public function cacheById($id)
    {
        $key = 'lxs_id_' . $id;
        return \Cache::remember($key, function () use($id) {
            return LxsData::getInstance()->getOne(['id' => $id, 'flag >'=>9], 'id,title');
        }, 864000);
    }

    /**
     * 批量获取缓存,不存在取数据库并缓存
     * @param $arr
     * @param $prefix
     * @return mixed
     */
    public function getMultiCache($arr,$prefix = 'lxs_id_')
    {
        $cacheKeys = array_map(function ($id) use($prefix) {
            return $prefix . $id;
        }, $arr);

        $result = \Cache::mget($cacheKeys);
        foreach ($result as $key=>$val) {
            if($val == false) {
                $result[$key] = $this->cacheById($arr[$key]);
            }
        }

        return array_combine($cacheKeys,$result);
    }
}
