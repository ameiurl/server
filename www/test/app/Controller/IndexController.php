<?php
namespace App\Controller;

class IndexController extends BaseController {

    public function __construct()
    {
		parent::__construct();
		error_reporting(E_ALL);
    }

	public function index()
	{
		echo PHP_EOL;
		$arr = [
			2,3
		];
		foreach ($arr as $key=>&$val) {
			if ($val == 2) {
				$val = 4;
			}
			if ($val == 3) {
				unset($val);
				//unset($arr[$key]);
			}
		}
		// print_r($arr);exit;

		$this->view->assign(
			array(
				'test' => 222,
			));
		$this->view->display('index');
	}

}
