<?php
/**
 * 线路
 * Created by PhpStorm.
 * User: yuzq
 * Date: 2018/2/6
 * Time: 16:20
 */

namespace My\data\line;


//use erp\component\product\LineType;
use My\data\BaseData;

/**
 * 线路表
 * 这是表"t_line"的数据模型类
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
 */
class LineData extends BaseData {

    protected $table = 'lxs_line';

    /*
     * 查询分类
     */
    public function getOneByRegionalId($erp_id, $regional_id, $field = '*'){
        return $this->db->where('erp_id', $erp_id)->where('group_regionalid_1|group_regionalid_2|group_regionalid_3|group_regionalid_4|regionalid_1|regionalid_2|regionalid_3|regionalid_4', $regional_id)->field($field)->find();
    }

}
