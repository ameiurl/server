<?php
namespace My\component\line;

use My\component\BaseModel;
//use My\data\line\LineContentData;
use My\data\line\LineData;
//use My\data\line\LineLabelData;
//use My\data\line\LinePictureData;
//use My\data\line\LineSchedulingData;
//use My\data\line\LineShopSiteData;
//use My\data\line\LineZoneData;

/**
 * Class LineModel
 * @package erp\component\product
 *
 *  @property integer $id ID
 * @property integer $erp_id ERP_ID
 * @property integer $supplier_company_id 供应商公司ID
 * @property integer $employee_id 发布线路员工ID
 * @property string $title 线路名称
 * @property string $sub_tile 线路副标题
 * @property string $line_no 线路编号
 * @property integer $scheduling_type 线路行程类型  1.按天编辑   2.自定义编辑
 * @property integer $day_num 行程天数
 * @property integer $group_regionalid_1 集团-一级分类
 * @property integer $group_regionalid_2 集团-二级分类
 * @property integer $group_regionalid_3 集团-三级分类
 * @property integer $group_regionalid_4 集团-四级分类
 * @property integer $regionalid_1 产品-一级分类
 * @property integer $regionalid_2 产品-二级分类
 * @property integer $regionalid_3 产品-三级分类
 * @property integer $regionalid_4 产品-四级分类
 * @property integer $from_type 产品发布来源类型（1.自营产品  2.代售产品  3.云端产品）
 * @property integer $flag 业务状态（-1.下架  1.上架 2.草稿）
 * @property integer $go_traffic 去程交通（0.不需要 1.飞机  2.汽车 3.动车  4.火车  5.轮船）
 * @property integer $back_traffic 返程交通（0.不需要 1.飞机  2.汽车 3.动车  4.火车  5.轮船）
 * @property integer $add_time 添加时间
 * @property integer $update_time 更新时间
 *
 * @property LineContentData $lineContent 线路内容
 * @property LinePictureData[] $linePictures 获取线路图片
 * @property LineShopSiteData[] $lineShopSites 获取线路购物点、自费项
 * @property LineZoneData[] $lineFromZones 获取出发地
 * @property LineZoneData[] $lineToZones 获取目的地
 * @property LineLabelData[] $lineLabels 获取线路标签
 * @property LineSchedulingData[] $lineSchedulings 获取线路行程
 * @property sting $lineFromZoneName 获取线路出发地名称
 */
class LineModel extends BaseModel
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
        return LineData::getInstance()->getDataById($id);
    }

    /**
     * 更新线路内容
     * @param $lineContentData
     */
    public function updateLineContent($lineContentData)
    {
        if(!empty($lineContentData)){
            $lineContentData['line_id'] = $this->data['id'];

            $lineContent = $this->getLineContent();
            if ($lineContent){
                $lineContentData['id'] = $lineContent->id;
            }

            $lineContentId = LineContentData::getInstance()->saveInfo($lineContentData);
            if (!$lineContentId){
                $this->throwError('线路内容保存失败');
            }
        }
    }

    /**
     * 更新线路标签
     * @param $lineLabelData
     * @throws \Exception
     */
    public function updateLineLabels($lineLabelData)
    {
        $this->deleteLineLabels();  //删除旧数据
        if (!empty($lineLabelData)){
            foreach ($lineLabelData as $key => $item){
                $lineLabelData[$key]['line_id'] = $this->data['id'];
            }
            LineLabelData::getInstance()->saveInfoAll($lineLabelData);
        }
    }

    /**
     * 更新线路图片
     * @param $linePictureData
     * @throws \Exception
     */
    public function updateLinePictures($linePictureData)
    {
        $this->deleteLinePictures();//删除旧数据
        if (!empty($linePictureData)){
            foreach ($linePictureData as $key => $item){
                $linePictureData[$key]['line_id'] = $this->data['id'];
            }
            LinePictureData::getInstance()->saveInfoAll($linePictureData);
        }
    }

    /**
     * //线路购物点、自费项
     * @param $lineShopSiteData
     * @throws \Exception
     */
    public function updateLineShopSites($lineShopSiteData)
    {
        $this->deleteLineShopSites();////删除旧数据
        if (!empty($lineShopSiteData)){
            foreach ($lineShopSiteData as $key => $item){
                $lineShopSiteData[$key]['line_id'] = $this->data['id'];
            }
            LineShopSiteData::getInstance()->saveInfoAll($lineShopSiteData);
        }
    }

    /**
     * @param $lineZoneData
     * @throws \Exception
     */
    public function updateLineDestination($lineZoneData)
    {
        $this->deleteLineFromZones();
        $this->deleteLineToZones();

        if (!empty($lineZoneData)){
            foreach ($lineZoneData as $key => $item){
                $lineZoneData[$key]['line_id'] = $this->id;
            }
            LineZoneData::getInstance()->saveInfoAll($lineZoneData);
        }
    }

    public function updateLineSchedulings($lineSchedulingData)
    {
        $this->deleteLineSchedulings();

        if (!empty($lineSchedulingData)){
            foreach ($lineSchedulingData as $key => $item){
                $schedulingData = $item['scheduling_data'];
                $schedulingData['line_id'] = $this->id;
                $scheduling_id = LineSchedulingData::getInstance()->saveInfo($schedulingData);
                if (!$scheduling_id){
                    $this->throwError('第' . ($key + 1) . '天行程数据保存失败');
                }

                //行程图片
                $schedulingPictureData = $item['picture_data'];
                if (!empty($schedulingPictureData)){
                    foreach ($schedulingPictureData as $_key => $_item){
                        $schedulingPictureData[$_key]['line_id']            = $this->id;
                        $schedulingPictureData[$_key]['line_scheduling_id'] = $scheduling_id;
                    }
                    LineSchedulingPictureData::getInstance()->saveInfoAll($schedulingPictureData);
                }

                //行程到达城市
                $schedulingCityData = $item['city_data'];
                if (!empty($schedulingCityData)){
                    foreach ($schedulingCityData as $_key => $_item){
                        $schedulingCityData[$_key]['line_id']            = $this->id;
                        $schedulingCityData[$_key]['line_scheduling_id'] = $scheduling_id;
                    }
                    LineSchedulingCityData::getInstance()->saveInfoAll($schedulingCityData);
                }
            }
        }
    }

    /**
     * 获取线路内容
     * @return LineContentData
     */
    public function getLineContent(){
        return LineContentData::getInstance()->getDataByLineId($this->data->id);
    }

    /**
     * 删除线路内容
     * @return bool
     */
    public function deleteLineContent(){
        return LineContentData::getInstance()->deleteByLineId($this->data->id);
    }

    /**
     * 获取线路行程
     * @return LineSchedulingData[]
     */
    public function getLineSchedulings(){
        return LineSchedulingData::getInstance()->getDataByLineId($this->data->id);
    }

    /**
     * 删除线路行程
     * @return bool
     */
    public function deleteLineSchedulings(){
        LineSchedulingData::getInstance()->deleteByLineId($this->data->id);
        return true;
    }

    /**
     * 获取线路标签
     * @return LineLabelData[]
     */
    public function getLineLabels(){
        return LineLabelData::getInstance()->getListByLineId($this->data->id);
    }

    /**
     * 删除线路标签
     * @return bool
     */
    public function deleteLineLabels(){
        return LineLabelData::getInstance()->deleteListByLineId($this->data->id);
    }

    /**
     * 获取线路图片
     * @return LinePictureData[]
     */
    public function getLinePictures(){
        return LinePictureData::getInstance()->getListByLineId($this->data->id);
    }

    /**
     * 删除线路图片
     * @return bool
     */
    public function deleteLinePictures(){
        return LinePictureData::getInstance()->deleteListByLineId($this->data->id);
    }

    /**
     * 获取购物点、自费项
     * @return LineShopSiteData[]
     */
    public function getLineShopSites(){
        return LineShopSiteData::getInstance()->getListByLineId($this->data->id);
    }

    /**
     * 删除购物点、自费项
     * @return bool
     */
    public function deleteLineShopSites(){
        return LineShopSiteData::getInstance()->deleteListByLineId($this->data->id);
    }

    /**
     * 获取出发地区
     * @return LineZoneData[]
     */
    public function getLineFromZones(){
        return LineZoneData::getInstance()->getFromZoneListByLineId($this->data->id);
    }

    /**
     * 删除出发地区
     * @return bool
     */
    public function deleteLineFromZones(){
        return LineZoneData::getInstance()->deleteFromZoneListByLineId($this->data->id);
    }

    /**
     * 获取出发地区
     * @return LineZoneData[]
     */
    public function getLineToZones(){
        return LineZoneData::getInstance()->getToZoneListByLineId($this->data->id);
    }

    /**
     * 删除出发地区
     * @return bool
     */
    public function deleteLineToZones(){
        return LineZoneData::getInstance()->deleteToZoneListByLineId($this->data->id);
    }

    /**
     * 获取线路出发地名称
     * @return string
     */
    public function getLineFromZoneName(){
        $zoneNames = get_column($this->getLineFromZones(), 'zone_name');
        $zoneNameStr = implode('|', array_filter(array_unique($zoneNames)));
        $zoneNameStr = !empty($zoneNameStr) ? $zoneNameStr : LineType::DEFAULT_FROM_ZONE_NAME;
        return $zoneNameStr;
    }
}
