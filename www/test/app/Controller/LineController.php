<?php
namespace App\Controller;

use My\data\line\LineData;
use My\component\line\LineModel;
use My\component\line\LineService;
use My\component\line\LineType;

class LineController extends BaseController {

    public function __construct()
    {
		parent::__construct();
		error_reporting(E_ALL);
    }

	public function index()
	{

		$lineId = 1654892;
        $lineInfo = LineData::getInstance()->getDataById($lineId);
        if (!$lineInfo){
            $this->throwError("产品不存在，操作失败");
        }
		//print_r($lineInfo);exit;
			//$this->throwError("产品不存在，操作失败");

		$lineModel = new LineModel($lineInfo);
		$title = $lineModel->title;
		//print_r($title);exit;

		$typeList = LineType::$fromTypeList;

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
