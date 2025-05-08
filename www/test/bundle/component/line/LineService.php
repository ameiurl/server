<?php
/**
 * 产品线路Service
 * Created by PhpStorm.
 * User: yuzq
 * Date: 2018/2/6
 * Time: 15:31
 */

namespace My\component\line;

use My\component\BaseService;
use My\component\line\event\CreatedLineEvent;
use My\data\line\LineData;

class LineService extends BaseService {

    /**
     * 添加线路
     * @param array $param 线路提交数据
     * @throws \Exception
     * @return int
     */
    public function addLine($param = []){
        $postData = $param;
        $draftFlag = intval($param['draft_flag']);  //是否为草稿

        if (!$draftFlag){
            //验证数据
            $checkHandler = new CheckLineDataHandler();
            $param = $checkHandler->checkLineData($param);
        }

        //组合数据
        $dealHandler = new DealLineDataHandler();
        $data  = $dealHandler->dealLineData($param);

        //线路信息
        $lineData   = $data['lineData'];
        $actionName = !$data['lineData']['id'] ? '新增' : '编辑';

        $beforeData = $afterData = [];
        if ($data['lineData']['id']){
            $beforeData = $this->getProductRelatedDataById($data['lineData']['id']);
        }

        $lineId   = LineData::getInstance()->saveInfo($lineData);
        if (!$lineId){
            $this->throwError('线路信息保存失败');
        }

        if ($data['lineData']['id']){
            $afterData = $this->getProductRelatedDataById($data['lineData']['id']);
        }

        $lineModel = new LineModel($lineId);
        //线路内容
        $lineContentData = $data['lineContentData'];
        $lineModel->updateLineContent($lineContentData);

        //线路标签
        $lineLabelData = $data['lineLabelData'];
        $lineModel->updateLineLabels($lineLabelData);

        //线路图片
        $linePictureData = $data['linePictureData'];
        $lineModel->updateLinePictures($linePictureData);

        //线路购物点、自费项
        $lineShopSiteData = $data['lineShopSiteData'];
        $lineModel->updateLineShopSites($lineShopSiteData);

        //线路出发地、目的地
        $lineZoneData  = $data['lineZoneData'];
        $lineModel->updateLineDestination($lineZoneData);

        //线路行程
        if ($lineData['scheduling_type'] == LineType::SCHEDULING_TYPE_DAY){
            $lineSchedulingData = $data['lineSchedulingData'];
            $lineModel->updateLineSchedulings($lineSchedulingData);
        }

        //线路服务项目
        $serviceData = $data['serviceData'];
        $lineModel->updateLineService($serviceData);

        //集合站点
        if ($param['shuttle_site_flag']){
            $shuttleSiteData = $data['shuttleSiteData'];
            $lineModel->updateLineStation($shuttleSiteData);
        }

        //线路文件
        $fileData = $data['fileData'];
        $lineModel->updateLineFiles($fileData);

        if ($param['groupLine']){
            $command = new UpdatedByAddGroupShareCommand();
            $command->updatedSaveInfo($lineId);
        }

        //同步渠道
        $channelData = $param;
        $channelData['line_id'] = $lineModel->id;
        $channelData['channel_code'] = $param['channelCodes'];
        $this->saveChannel($channelData);

        //核销设置
        $lineModel->updateVerification($data['verificationData']);

        //添加日志
        $lineModel->addLog("{$actionName}产品", $beforeData, $afterData);

        //处理消息
        $handler = new MsgForProductHandler($lineModel);
        $handler->doSupplierAddProduct($param);

        //发送创建线路事件
        (new CreatedLineEvent($lineModel,$param))->publish();

        return $lineId;
    }

	public function testEvent($lineModel)
	{
		$param = [];
        //发送创建线路事件
        (new CreatedLineEvent($lineModel,$param))->publish();
	}

    /**
     * 获取列表类型名称
     * @param int $type
     * @return string
     */
    public function getListTypeName($type = LineType::FROM_TYPE_SELF){
        $listName = '';
        switch ($type){
            case LineType::FROM_TYPE_SELF:  //自营产品
                $listName = 'core_line_saleIndex';
                break;

            case LineType::FROM_TYPE_TRUST:  //代售产品
                $listName = 'core_line_trustIndex';
                break;

            case LineType::FROM_TYPE_YUN:  //云端产品
                $listName = 'core_line_yunIndex';
                break;
        }

        return $listName;
    }

    /**
     * 获取线路列表默认表头
     * @param int $type  列表类型  1.自营产品  2.代售产品  3.云端产品
     * @return array
     */
    protected function getDefaultTitleList($type = LineType::FROM_TYPE_SELF){
        $fieldList = [];
        switch ($type){
            case LineType::FROM_TYPE_SELF:  //自营产品
                $fieldList = [
                    ['type' => 'checkbox'],
                    ['field' => 'combineType', 'title' => '组合产品'],
                    ['field' => 'lineName', 'title' => '产品名称','minWidth'=>200],
                    ['field' => 'regionalName', 'title' => '产品分类'],
                    ['field' => 'planNum', 'title' => '收客计划'],
                    ['field' => 'dayNum', 'title' => '行程天数'],
                    ['field' => 'fromZone', 'title' => '出发地'],
                    ['field' => 'addEmployeeName', 'title' => '创建人'],
                    ['field' => 'addDepartmentName', 'title' => '创建部门'],
                    ['field' => 'supplierNmae', 'title' => '供应商'],
                    ['field' => 'lineFlag', 'title' => '业务状态'],
                    ['field' => 'btnEdit', 'title' => '操作', 'fixed' => 'right', 'templet' => '#j-btn-operate', 'width'=>80],
                ];
                $coinAppPermission = CoinPermission::getInstance()->checkAppPermission();
                if($coinAppPermission){
                    $fieldList[] = ['field' => 'coin_rule_info', 'title' => '金币使用规则','width'=>350];
                    $fieldList[] = ['field' => 'coin_rule_op_username', 'title' => '金币规则创建人','width'=>100];
                }
                break;

            case LineType::FROM_TYPE_TRUST:  //代售产品
                $fieldList = [
                    ['type' => 'checkbox'],
                    ['field' => 'lineName', 'title' => '产品名称'],
                    ['field' => 'regionalName', 'title' => '产品分类','minWidth'=>200],
                    ['field' => 'salePlanNum', 'title' => '收客计划'],
                    ['field' => 'dayNum', 'title' => '行程天数'],
                    ['field' => 'fromZone', 'title' => '出发地'],
                    ['field' => 'addEmployeeName', 'title' => '创建人'],
                    ['field' => 'addDepartmentName', 'title' => '创建部门'],
                    ['field' => 'supplierNmae', 'title' => '供应商'],
                    ['field' => 'lineFlag', 'title' => '业务状态'],
                    ['field' => 'btnEdit', 'title' => '操作', 'fixed' => 'right', 'templet' => '#j-btn-operate', 'width'=>80],
                ];
                break;

            case LineType::FROM_TYPE_YUN:  //云端产品
                $fieldList = [
                    ['type' => 'checkbox'],
                    ['field' => 'lineName', 'title' => '产品名称','minWidth'=>200],
                    ['field' => 'regionalName', 'title' => '产品分类'],
                    ['field' => 'salePlanNum', 'title' => '收客计划'],
                    ['field' => 'dayNum', 'title' => '行程天数'],
                    ['field' => 'fromZone', 'title' => '出发地'],
                    ['field' => 'toZone', 'title' => '目的地'],
                    ['field' => 'addEmployeeName', 'title' => '创建人'],
                    ['field' => 'addDepartmentName', 'title' => '创建部门'],
                    ['field' => 'supplierNmae', 'title' => '供应商'],
                    ['field' => 'lineFlag', 'title' => '业务状态'],
                    ['field' => 'btnEdit', 'title' => '操作', 'fixed' => 'right', 'templet' => '#j-btn-operate', 'width'=>80],
                ];
                break;
        }

        return $fieldList;
    }

    /**
     * 获取线路列表表头
     * @param int $type  列表类型  1.自营产品  2.代售产品  3.云端产品
     * @return array
     */
    public function getTitleList($type = LineType::FROM_TYPE_SELF){
        $type      = intval($type);
        $fieldList = [];
        if (!array_key_exists($type, LineType::$fromTypeList)){
            return $fieldList;
        }

        $listName = $this->getListTypeName($type);

        $defaultList   = $this->getDefaultTitleList($type);
        $listTitle = ListCustomSortData::getInstance()->getTableList($listName, $defaultList);

        return $listTitle;
    }

    /**
     * 获取线路字段设置
     * @param integer $lineTypeId 线路一级分类
     * @param int $company_id  公司ID
     * @return array
     */
    public function getLineFieldSetting($lineTypeId, $company_id = 0){
        return RegionalService::getInstance()->getFieldSetting($lineTypeId,$company_id);
    }

    /**
     * 批量上下架产品
     * @param array $param
     */
    public function batchChangeStatus($param = []){
        $flag = intval($param['flag']);  //1.上架  -1下架
        $ids = array_filter(explode(',', $param['ids']));
        if (empty($ids)){
            $this->throwError('请选择线路产品');
        }

        if (!in_array($flag, [-1, 1])){
            $this->throwError('操作类型错误');
        }

        $where = array(
            'erp_id' => $this->token->erp_id,
            'id'     => ['IN', $ids]
        );

        $flagName = $flag == 1 ? '上架' : '下架';
        /** @var LineData[] $lines */
        $lines = LineData::getInstance()->getDataList($where);
        $productIds = $flagArr = $update = [];

        $bzErpApi = BzerpGateWayFactory::getApiForBzerp();
        $bzerpSyncService = new BzerpSyncService();
        $bzErpSetting = $bzerpSyncService->getBzerpSetting();

        foreach($lines as $line){
            $lineModel = new LineModel($line);
            $productIds[$line->id] = $line->id;
            if ($line->flag == LineType::LINE_FLAG_DRAFT){
                $this->throwError("产品“{$line->title}”是草稿状态，不能{$flagName}");
            }

            if ($line->audit_flag != LineType::AUDIT_FLAG_PASS){
                $this->throwError("产品“{$line->title}”未审核通过，不能{$flagName}");
            }

            if ($line->from_type == LineType::FROM_TYPE_TRUST &&
                !$this->token->is_master &&
                $line->company_id != $this->token->company_id &&
                $line->erp_company_id != $this->token->company_id){
                $this->throwError("产品“{$line->title}”所属供应商的产品您无法{$flagName}");
            }

            $flagArr[$line->flag] = $line->flag;
            $update[] = array(
                'id'   => $line->id,
                'flag' => $flag,
            );

            //添加日志
            $lineModel->addLog("{$flagName}产品：{$lineModel->title}");

            //------------------------------同步到宝中start------------------------
            $bzSyncLineInfo = $bzerpSyncService->getSyncSuccessLineInfo($line->id);
            $bzSyncLineInfo = $bzSyncLineInfo ? to_array($bzSyncLineInfo) : [];
            if(!empty($bzSyncLineInfo)){//如果有同步过线路信息，才可以同步状态
                if (!empty($bzErpSetting)) {
                    $bzErpApi->initProperty($bzErpSetting);
                    $bzerpHandler = new BzerpSyncHandler($lineModel);
                    $params = ['line_id' => $bzSyncLineInfo['bz_line_id'],'flag' => (($flag == 1)?1:0)];
                    $bzerpHandler->doLineFlagSync($params);

                    $bzSyncLineFlag = $bzerpSyncService->getSyncLineInfo($line->id,LineSyncType::SYNC_LINE_FLAG);
                    $bzSyncLineFlag = to_array($bzSyncLineFlag);
                    if(!empty($bzSyncLineFlag)){
                        $bzerpSyncService->bzerpSyncInfo = $bzSyncLineFlag;
                        $params = ['line_id' => $bzSyncLineInfo['bz_line_id'],'flag' => $flag];
                        $result = $bzErpApi->syncLineFlag($params);
                        if ($result['status'] == 1) {
                            $bzerpSyncService->handleSuccessLineSyncResponseInfo($result['msg']);
                        } else {
                            $bzerpSyncService->handleErrorLineSyncResponseInfo($result['msg']);
                            $this->throwError($result['msg']);
                        }
                    }
                }
            }
            //------------------------------同步到宝中end--------------------------
        }

        if (count($flagArr) > 1){
            $this->throwError('所选线路状态不一致');
        }

        $res = LineData::getInstance()->saveInfoAll($update);
        if (!$res){
            $this->throwError('操作失败');
        }

        /*if ($flag == -1){
            //下架所有计划
            $planIds = ProductPlanData::getInstance()->getDataList(['product_id' => ['IN', $productIds], 'product_type' => ProductType::REGIONAL_TYPE_FOLLOW, 'flag' => PlanType::PLAN_FLAG_SELL], 'id');
            $planIds = get_column($planIds, 'id');

            if (!empty($planIds)){
                $planData = array(
                    'flag' => -1,
                    'ids'  => implode(',', $planIds),
                );
                $handler = new ChangePlanStatusHandler();
                $handler->batchChangeStatus($planData);
            }
        }*/

    }

    /**
     * 删除线路
     * @param array $param
     */
    public function deleteLine($param = []){
        $lineId = intval($param['line_id']);
        if (empty($lineId)){
            $this->throwError('请选择操作线路');
        }

        $where = [
            'erp_id' => $this->token->erp_id,
            'id'     => $lineId,
        ];

        /** @var LineData $lineInfo */
        $lineInfo = LineData::getInstance()->getData($where);
        if (!$lineInfo){
            $this->throwError('线路不存在');
        }

        $lineModel = new LineModel($lineInfo);
        if ($lineInfo->flag != LineType::LINE_FLAG_DRAFT && $lineModel->plans){
            $this->throwError('线路含有计划，无法删除');
        }

        $flag = LineData::getInstance()->deleteById($lineInfo->id);
        if (!$flag){
            $this->throwError('线路删除失败');
        }

        //添加日志
        $lineModel->addLog("删除线路：{$lineModel->title}");
    }

    /**
     * 保存销售渠道
     * @param array $param
     */
    public function saveChannel($param = []){
        $lineId       = intval($param['line_id']);
        $isGroup      = intval($param['isGroup']);  //集团产品
        $channelCodes = (array)$param['channel_code'];
        if (empty($lineId)){
            $this->throwError('请选择操作线路');
        }

        $groupShareType = SellChannelCodeType::CODE_GROUP_SHARE;
        if ($isGroup){
            $channelCodes[] = $groupShareType;
        }

        $where = [
            'erp_id' => $this->token->erp_id,
            'id'     => $lineId,
        ];

        /** @var LineData $lineInfo */
        $lineInfo = LineData::getInstance()->getData($where);
        if (!$lineInfo){
            $this->throwError('线路不存在');
        }

        $erpModel = new ErpModel($this->token->erp_id);
        $channels = $erpModel->getUserChannelList();

        $data = $channelNames = $innerCompanys = $ownCompany = [];
        $companyId = $this->token->company_id;
        if ($this->token->company_type != CompanyType::COMPANY_TYPE_INSIDE){
            $companyId = $this->token->parent_company_id;
        }


        $productType = ProductType::REGIONAL_TYPE_FOLLOW;

        foreach ($channelCodes as $code){
            $data[] = [
                'erp_id'       => $this->token->erp_id,
                'company_id'   => $companyId,
                'product_type' => $productType,
                'product_id'   => $lineInfo->id,
                'channel_code' => trim($code),
                'share_flag'   => 0,
            ];

            $channelNames[$code] = $channels[$code]['name'];
        }

        $lineModel = new LineModel($lineInfo);
        $deleteFlag = $lineModel->deleteLineChannels($companyId);
        if ($data){
            ProductChannelData::getInstance()->saveInfoAll($data);
        }

        $lineUpdate = [
            'id' => $lineModel->id,
            'group_share_flag' => in_array($groupShareType, $channelCodes) ? 1 : 0
        ];

        LineData::getInstance()->saveInfo($lineUpdate);

        //添加日志
        if (empty($channelNames)){
            if ($deleteFlag){
                $lineModel->addLog("编辑线路“{$lineModel->title}”的渠道：置空渠道");
            }
        }else{
            $channelNames = implode(',', $channelNames);
            $lineModel->addLog("编辑线路“{$lineModel->title}”的渠道：{$channelNames}");
        }

        //共享渠道
        $shareHandler = new ShareChannelHandler(ProductType::REGIONAL_TYPE_FOLLOW, $lineModel->id, $companyId);
        $shareHandler->share();

        //------宝中线路同步start---------------
        $bzerpHandler = new BzerpSyncHandler($lineModel);
        $bzerpHandler->doLineInfoSync($param);
        //------宝中线路同步end--------------

    }

    /**
     * 获取线路组合标品信息
     * @param $lineId
     * @return array
     */
    public function getCombineProductsInfo($lineId){
        $lineModel = new LineModel($lineId);

        //交通标品
        $combineProducts = $lineModel->getlineCombineProducts();

        $routeWayTypeList = PlaneType::$routeWayTypeList;

        $routeFlagList = PlaneType::$routeFlagList;

        $combineLineInfo = $goTrafficInfo = $backTrafficInfo = [];
        foreach ($combineProducts as $product){
            $productType = $product->product_type;
            if ($productType == ProductType::REGIONAL_TYPE_PLANE){
                $routeModel = new RouteModel($product->product_id);
                $routeData = to_array($routeModel->getData());
                $flights = $product->return_type == 1 ? $routeModel->goFlights : $routeModel->backFlights;
                $routeData['product_type'] = $productType;
                $routeData['typeName'] = $routeWayTypeList[$routeData['way_type']];
                $routeData['flagName'] = $routeFlagList[$routeData['flag']];
                $routeData['flights']  = [];
                foreach ($flights as $key => $flight){
                    $flightModel = new FlightModel($flight->id);
                    $routeData['flights'][] = number_to_big($key + 1) . "段：" . $flightModel->getFlightInfoStr();
                }

                if ($product->return_type == 1){
                    $goTrafficInfo[$productType][] = $routeData;
                }elseif ($product->return_type == 2){
                    $backTrafficInfo[$productType][] = $routeData;
                }

                $trafficInfo[$product->return_type][] = $routeData;
            }elseif ($productType == ProductType::REGIONAL_TYPE_FOLLOW){
                $combineLineInfo = LineData::getInstance()->getDataById($product->product_id);
            }
        }
        return [
            'combineLineInfo' => to_array($combineLineInfo),
            'goTrafficInfo'   => $goTrafficInfo,
            'backTrafficInfo' => $backTrafficInfo,
        ];
    }

    /**
     * 批量审核产品
     * @param array $param
     */
    public function batchAudit($param = []){
        $flag = intval($param['flag']);  //2.审核通过  -1审核不通过
        $ids = array_filter(explode(',', $param['ids']));
        $auditRemark = trim($param['audit_remark']);
        if (empty($ids)){
            $this->throwError('请选择线路产品');
        }

        if (!in_array($flag, [-1, 2])){
            $this->throwError('操作类型错误');
        }

//        if ($flag == -1 && !$auditRemark){
//            $this->throwError('请填写备注');
//        }

        $where = array(
            'erp_id' => $this->token->erp_id,
            'id'     => ['IN', $ids]
        );

        $flagName = $flag == -1 ? '审核不通过' : '审核通过';
        /** @var LineData[] $lines */
        $lines = LineData::getInstance()->getDataList($where);
        $productIds = $flagArr = $update = [];
        foreach($lines as $line){
            $lineModel = new LineModel($line);
            if ($line->from_type == LineType::FROM_TYPE_TRUST &&
                $line->company_id != $this->token->company_id &&
                $line->erp_company_id != $this->token->company_id &&
                !($this->token->is_master || $this->token->is_master_admin)){
                $this->throwError("产品“{$line->title}”所属供应商的产品您无法审核");
            }

            $productIds[$line->id] = $line->id;

            $flagArr[$line->audit_flag] = $line->audit_flag;
            $update[] = array(
                'id'         => $line->id,
                'audit_flag' => $flag,
                'flag'       => $flag == -1 ? LineType::LINE_FLAG_UNDER : LineType::LINE_FLAG_UP,
            );

            //添加日志
            $lineModel->addLog("{$flagName}线路：{$lineModel->title}");

            //审核日志
            if ($auditRemark){
                $auditFlag = $flag == -1 ? -1 : 1;
                $lineModel->addAuditLogs($auditFlag, $auditRemark);
            }

            $handler = new MsgForProductHandler($lineModel);
            $handler->doAuditProduct($param);
        }

        if (count($flagArr) > 1){
            $this->throwError('所选线路审核状态不一致');
        }

        $res = LineData::getInstance()->saveInfoAll($update);
        if (!$res){
            $this->throwError('操作失败');
        }

        //同步更新计划审核状态
        $command = new UpdatedByAuditPlanCommand();
        $command->updatedSaveInfo($ids);
    }


    public function getLineInfoById($params = [])
    {
        $id = isset($params['id']) ? intval($params['id']) : 0;
        $plan_id = isset($params['plan_id']) ? intval($params['plan_id']) : 0;
        $package_id = isset($params['package_id']) ? intval($params['package_id']) : 0;

        if (empty($id) || empty($plan_id) || empty($package_id)) {
            $this->throwError('请求参数异常');
        }

        $line_info = LineData::getInstance()->getDataById($id);

        if (empty($line_info)) {
            $this->throwError('没有产品信息');
        }

        if ($line_info->flag != LineType::LINE_FLAG_UP){
            $this->throwError('产品已下架');
        }

        $plan_info = ProductPlanData::getInstance()->getData([
            'id' => $plan_id,
            'product_id' => $id
        ]);

        if (!$plan_info) {
            $this->throwError("没有产品计划");
        }

        if ($plan_info['line_date'] < strtotime(date('Y-m-d'))) {
            $this->throwError("产品计划已过期");
        }

        $package_info = ProductPlanPackageData::getInstance()->getData([
            'id' => $package_id,
            'plan_id' => $plan_id
        ]);

        if (empty($package_info)) {
            $this->throwError('没有计划套餐');
        }

//        $sql = "SELECT a.id, a.price_amount, b.price_combo_code, b.remark FROM t_product_plan_price a
//                LEFT JOIN t_product_plan_price_combo b ON a.price_combo_code = b.price_combo_code
//                WHERE a.package_id = {$package_id} AND b.package_id = {$package_id} AND a.plan_id = {$plan_id} AND b.plan_id = {$plan_id} AND price_combo_level_id = " .
//            PriceLevelType::MARKET_PRICE;

        $priceLevelType = $this->token->userType == 3 ? PriceLevelType::PEER_PRICE : PriceLevelType::MARKET_PRICE;
        $sql = "SELECT a.id, a.price_combo_code, a.price_amount, b.price_combo_code, b.remark content FROM t_product_plan_price a left join t_product_plan_price_combo b ON a.price_combo_code = b.price_combo_code WHERE a.package_id={$package_id} AND b.package_id = {$package_id} AND a.price_combo_level_id=".
        $priceLevelType;
        $price_list = Db::query($sql);

        //关联库存
        $mapCodeStock = ErpPriceComboData::getInstance()->getListMapByCode($this->token->erp_id);

        if (!empty($price_list)) {
            foreach ($price_list as $k => $v) {
                $price_list[$k]['price_amount'] = deal_amount($v['price_amount']);
                $price_list[$k]['name'] = PriceComboCodeType::getType($v['price_combo_code']);
                $price_list[$k]['stock'] = isset($mapCodeStock[$v['price_combo_code']]) ? $mapCodeStock[$v['price_combo_code']]['stock_num'] : 1;
            }
        }
        $line_info['plan_info'] = $plan_info;
        $line_info['package_info'] = $package_info;
        $line_info['price_list'] = $price_list;
        return $line_info;
    }

    /**
     * 线路海报生成
     */
    public function createPoster($params){
        ini_set('memory_limit', '512M');
        $params['erp_id'] = $params['erp_id'] ? $params['erp_id'] : $this->token->erp_id;
        $params['company_id'] = $params['company_id'] ? $params['company_id'] : $this->token->company_id;

        //查询是否有海报,有海报则删除
        $lineFileData = LineFileData::getInstance()->getPosterShareByLineId($params['line_id']);

        if(!empty($lineFileData->path)){
            LineFileData::getInstance()->deleteById($lineFileData->id);
            @unlink(ROOT_PATH.'/webapp'.$lineFileData->path);
        }

        $lineModel = new LineModel($params['line_id']);
        $linePoster = $lineModel->getLinePoster();

        if(empty($linePoster->path) || !file_exists(ROOT_PATH.'/webapp'.$linePoster->path)){
            return '';
        }

        if (!$this->token->mDomain){
            return $linePoster->path;
        }

        //创建目录
        $root_path = ROOT_PATH . '/webapp/upload/image/poster/' . date('Y-m-d') . '/' ;
        if(!file_exists($root_path)){
            mkdir($root_path, 0755, true);
        }
        $file_name =  generate_uuid(). '.' .$linePoster -> file_ext ;

        //绝对路径
        $src_path = $root_path . $file_name;

        //二维码url
        $url = $this->token->getMDomain().'/product/line/detail';
        $url.= '/id/'.$params['line_id']                                         //产品线路id
            . '/distributor_company_id/' . $params['distributor_company_id']  //分销商公司id
            . '/buyer_uid/' . $params['buyer_uid']                            //分销商员工id
            . '/company_id/'. $params['company_id'];                          //分销商上级id

        show_qrcode($url, $src_path, $level = 'Q', $size = 3); //生成二维码

        //给原图左下角添加二维码水印并保存
        $dst_path = ROOT_PATH.'/webapp'.$linePoster->path; //海报图
        Image::open($dst_path)->water($src_path, \think\Image::WATER_SOUTHEAST)->save($src_path);

        $save_path = '/upload/image/poster/' . date('Y-m-d') . '/' . $file_name;     //存储路径

        //存储分享海报
        $data = [
            'erp_id'          => $params['erp_id'],
            'line_id'         => $params['line_id'],
            'type'            => LineType::LINE_FILE_TYPE_POSTER_SHARE,
            'path'            => $save_path,
            'file_first_name' => $linePoster->file_first_name,
            'file_name'       => $file_name,
            'file_ext'        => $linePoster->file_ext,
            'file_type'       => 2,
        ];
        LineFileData::getInstance()->saveInfo($data);

        return $save_path;
    }

    /**
     * 线路海报生成 BySellId
     */
    public function createPosterBySellId($params){
        ini_set('memory_limit', '512M');
        $params['erp_id'] = $params['erp_id'] ? $params['erp_id'] : $this->token->erp_id;

        //查询是否有海报,有海报则删除
        $lineFileData = LineFileData::getInstance()->getPosterShareByLineId($params['line_id']);

        if(!empty($lineFileData->path)){
            LineFileData::getInstance()->deleteById($lineFileData->id);
            @unlink(ROOT_PATH.'/webapp'.$lineFileData->path);
        }

        $lineModel = new LineModel($params['line_id']);
        $linePoster = $lineModel->getLinePoster();

        if(empty($linePoster->path) || !file_exists(ROOT_PATH.'/webapp'.$linePoster->path)){
            return '';
        }

        //创建目录
        $root_path = ROOT_PATH . '/webapp/upload/image/poster/' . date('Y-m-d') . '/' ;
        if(!file_exists($root_path)){
            mkdir($root_path, 0755, true);
        }
        $file_name =  generate_uuid(). '.' .$linePoster -> file_ext ;

        //绝对路径
        $src_path = $root_path . $file_name;

        //二维码url
        $url = $this->token->getMDomain().'/product/line/detail';
        $url.= '/id/'.$params['line_id'];

        $url .= empty($params['sell_uid']) == false ? ('/sell_uid/' . $params['sell_uid']) : '';
        $url .= empty($params['company_id']) == false ? ('/company_id/' . $params['company_id']) : '';


        show_qrcode($url, $src_path, $level = 'Q', $size = 3); //生成二维码

        //给原图左下角添加二维码水印并保存
        $dst_path = ROOT_PATH.'/webapp'.$linePoster->path; //海报图
        Image::open($dst_path)->water($src_path, \think\Image::WATER_SOUTHEAST)->save($src_path);

        $save_path = '/upload/image/poster/' . date('Y-m-d') . '/' . $file_name;     //存储路径

        //存储分享海报
        $data = [
            'erp_id'          => $params['erp_id'],
            'line_id'         => $params['line_id'],
            'type'            => LineType::LINE_FILE_TYPE_POSTER_SHARE,
            'path'            => $save_path,
            'file_first_name' => $linePoster->file_first_name,
            'file_name'       => $file_name,
            'file_ext'        => $linePoster->file_ext,
            'file_type'       => 2,
        ];
        LineFileData::getInstance()->saveInfo($data);

        return $save_path;
    }

    /**
     * 通过ID获取产品的相关信息
     * @param $productId
     * @return array
     */
    public function getProductRelatedDataById($productId){
        $productId = intval($productId);
        $productInfo = LineData::getInstance()->getDataById($productId);
        if (!$productInfo){
            $this->throwError('产品不存在');
        }
        $productModel = new LineModel($productInfo);

        //产品信息
        $productInfoData = to_array($productInfo);

        //产品标签
        $productLabelData = to_array($productModel->getLineLabels());

        //产品图片
        $productPicturesData = to_array($productModel->getLinePictures());

        //产品服务项目
        $productServiceData = to_array($productModel->getLineService());

        //产品购物点自费项信息
        $productShopSites = to_array($productModel->getLineShopSites());

        //产品接送站点信息
        $productStations = to_array($productModel->getLineStationSite());

        //产品地区信息
        $productZones = [];
        $productZones = array_merge($productZones, to_array($productModel->getLineFromZones()));
        $productZones = array_merge($productZones, to_array($productModel->getLineToZones()));

        //产品渠道
        $productChannel = to_array($productModel->getLineChannels());

        return [
            't_line'            => $productInfoData,
            't_line_service'    => $productServiceData,
            't_line_picture'    => $productPicturesData,
            't_line_label'      => $productLabelData,
            't_line_shop_site'  => $productShopSites,
            't_line_station'    => $productStations,
            't_line_zone'       => $productZones,
            't_product_channel' => $productChannel,
        ];

    }

    /**
     * 获取操作日志信息
     * @return array
     */
    public function getOperatorLogs($productId){
        $productId = intval($productId);
        if (empty($productId)){
            $this->throwError('缺少线路ID');
        }

        /** @var ProductLogsData[] $logs */
        $logs = ProductLogsData::getInstance()->getDataList(['product_id' => $productId, 'product_type' => ProductType::REGIONAL_TYPE_FOLLOW], '*', 0, 'id DESC');

        $uids = get_column($logs, 'uid');

        /** @var ErpUserData[] $users */
        $users = ErpUserData::getInstance()->getDataList(['erp_id' => $this->token->erp_id, 'id' => ['IN', $uids]]);
        $users = arr_index($users, 'id');

        $list = [];
        $count = count($logs);
        foreach ($logs as $key => $log){
            $logModel = new ProductLogsModel($log);
            $editInfo = $logModel->getEditInfo();
            $_index = $count--;
            if ($editInfo){
                foreach ($editInfo as $_k => $item){
                    $list[] = [
                        'index'         => $_index,
                        'product_id'    => $log->product_id,
                        'content'       => $log->content,
                        'editModule'    => $item['title'],
                        'editInfo'      => $item['detail'],
                        'operator_user' => $users[$log->uid]['name'],
                        'operator_time' => date('Y-m-d H:i:s', $log->add_time),
                        'rowspan'       => !$_k ? count($editInfo) : 0,
                    ];
                }
            }else{
                $list[] = [
                    'index'         => $_index,
                    'product_id'    => $log->product_id,
                    'content'       => $log->content,
                    'editModule'    => '',
                    'editInfo'      => '',
                    'operator_user' => $users[$log->uid]['name'],
                    'operator_time' => date('Y-m-d H:i:s', $log->add_time),
                    'rowspan'       => 1,
                ];
            }
        }

        return $list;
    }

    /**
     * 获取审核日志
     * @return array
     */
    public function getAuditLogs($productId){
        $productId = intval($productId);
        if (empty($productId)){
            $this->throwError('缺少线路ID');
        }

        /** @var ProductAuditLogsData[] $logs */
        $logs = ProductAuditLogsData::getInstance()->getDataListByLineLogs($productId);

        $list = [];
        $i = 1;
        foreach ($logs as $key => $log){
            $log = to_array($log);
            $index = $i++;
            $log['index'] = $index;
            $log['audit_name'] = $log['audit_flag'] == 1 ? '审核通过' : '审核不通过';
            $log['audit_time'] = date('Y-m-d H:i:s', $log['audit_time']);
            $list[$index] = $log;
        }

        krsort($list);
        return $list;
    }
    
    public function addViewNum($param = array())
    {
        $id         = intval($param['id']);
    	$view_num   = intval($param['view_num']) ? intval($param['view_num']) : 1;
    	$line       = to_array(LineData::getInstance()->getDataById($id));
        
    	if(!$line){
            return FALSE;
    	}
    	
        $data['id']       = $id;
        $data['view_num'] = $line['view_num'] ? ($line['view_num'] + $view_num) : 1;
        $data['update_time'] = $line['update_time'];  //更新时间不变，影响后台线路排序
        return LineData::getInstance()->saveInfo($data);
    }


    /**
     * 批量获取线路详细信息
     * @param $param
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBatchLineDetail($param){
        $line_ids = $param['line_ids'];

        //线路列表
        $lineList = LineData::getInstance()->getListByIds($line_ids);

        //线路内容
        $lineContent = Db::table("t_line_content")->whereIn('line_id', $line_ids)->select();
        $mapContent = [];
        if($lineContent){
            foreach ($lineContent as $val){
                $mapContent[$val['line_id']] = $val;
            }
        }

        //线路行程
        $lineScheduling = Db::table("t_line_scheduling")->whereIn('line_id', $line_ids)->select();
        $mapScheduling = [];
        if($lineScheduling){
            foreach ($lineScheduling as $val){
                $mapScheduling[$val['line_id']][] = $val;
            }
        }

        //购物点、自费点
        $lineShop = Db::table("t_line_shop_site")->whereIn('line_id', $line_ids)->select();
        $mapShop = [];
        $mapOwn = [];
        if($lineShop){
            foreach ($lineShop as $val){
                $val['amount'] = $val['amount'] / 100;
                if($val['type'] == 1){
                    $mapShop[$val['line_id']][] = $val;
                }elseif ($val['type'] == 2){
                    $mapOwn[$val['line_id']][] = $val;
                }
            }
        }

        //集合站点
        $stationList = Db::table("t_line_station")->whereIn('line_id', $line_ids)->select();
        $mapStation = [];
        if($stationList){
            foreach ($stationList as $val){
                $mapStation[$val['line_id']][] = [
                    'id' => $val['id'],
                    'shuttle_line_id' => $val['shuttle_line_id'],
                    'shuttle_site_id' => $val['shuttle_site_id'],
                    'go_flag' => $val['go_flag'],
                    'go_time' => $val['go_time'],
                    'go_price' => $val['go_price'] / 100,
                    'back_flag' => $val['back_flag'],
                    'back_price' => $val['back_price'] / 100
                ];
            }
        }

        //出发地 目的地
        $lineZoneList = Db::table("t_line_zone")->whereIn('line_id', $line_ids)->select();
        $mapFromZone = [];
        $mapToZone = [];
        if($lineZoneList){
            foreach ($lineZoneList as $val){
                if($val['type'] == 1){
                    $mapFromZone[$val['line_id']][] = [
                        'zone_id' => $val['zone_id'],
                        'zone_name' => $val['zone_name'],
                        'zone_level' => $val['zone_level']
                    ];
                }elseif ($val['type'] == 2){
                    $mapToZone[$val['line_id']][] = [
                        'zone_id' => $val['zone_id'],
                        'zone_name' => $val['zone_name'],
                        'zone_level' => $val['zone_level']
                    ];
                }
            }
        }

        $data = [];
        if($lineList){
            foreach ($lineList as $val){

                $content = $mapContent[$val['id']];

                if($val['scheduling_type'] == 1){
                    $scheduling = isset($mapScheduling[$val['id']]) ? $mapScheduling[$val['id']] : [];
                }else{
                    $scheduling = empty($content['scheduling_content']) ? '' : $content['scheduling_content'];
                }

                unset($content['id']);
                unset($content['erp_id']);
                unset($content['line_id']);
                unset($content['scheduling_content']);
                unset($content['add_time']);
                unset($content['update_time']);

                $temp = [
                    'id' => $val['id'],
                    'title' => $val['title'],
                    'scheduling_type' => $val['scheduling_type'],
                    'scheduling' => $scheduling,
                    'shop_site' => isset($mapShop[$val['id']]) ? $mapShop[$val['id']] : [],
                    'own_site' => isset($mapOwn[$val['id']]) ? $mapOwn[$val['id']] : [],
                    'shuttle_station' => isset($mapStation[$val['id']]) ? $mapStation[$val['id']] : [],
                    'from_zone' => isset($mapFromZone[$val['id']]) ? $mapFromZone[$val['id']] : [],
                    'to_zone' => isset($mapToZone[$val['id']]) ? $mapToZone[$val['id']] : []
                ];

                $new = array_merge($temp, $content);
                $data[] = $new;
            }
        }
        return $data;
    }



    
}
