<?php
namespace App\Controller;

use My\data\lxs\LxsData;
use My\component\lxs\LxsModel;

class LxsController extends BaseController {

    public function __construct()
    {
		parent::__construct();
		error_reporting(E_ALL);
    }

	public function index()
	{

		$lxsId = 38782;
        $lxsInfo = LxsData::getInstance()->getDataById($lxsId);
        if (!$lxsInfo){
            $this->throwError("产品不存在，操作失败");
        }
		//print_r($lxsInfo);exit;

		$lxsList = LxsData::getInstance()->getAll();
		print_r($lxsList);exit;

		//$lxsModel = new LxsModel($lxsInfo);
		//$lxsInfo = $lxsModel->getMultiCache([$lxsId]);
		//print_r($lxsInfo);exit;

		$lxsModel = new LxsModel($lxsInfo);
		$title = $lxsModel->title;
		//print_r($title);exit;


		$this->view->assign(
			array(
				'test' => 222,
			));
		$this->view->display('index');
	}

	public function testEvent($param = [])
	{
		$lineId = 1654892;
        $lineInfo = LineData::getInstance()->getDataById($lineId);
        if (!$lineInfo){
            $this->throwError("产品不存在，操作失败");
        }

		$lineModel = new LineModel($lineId);
		LineService::getInstance()->testEvent($lineModel);

	}

}
