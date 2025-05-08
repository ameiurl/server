<?php
namespace My\component\line;

use My\component\BaseType;

class LineType extends BaseType
{
    /**
     * 线路默认出发地名称
     */
    const DEFAULT_FROM_ZONE_NAME = '全国联运';


    /**
     * 行程类型-按天编辑
     */
    const SCHEDULING_TYPE_DAY = 1;

    /**
     * 行程类型-自定义行程
     */
    const SCHEDULING_TYPE_DIY = 2;

    /**
     * 线路行程内容
     * @var array
     */
    public static $schedulingTypeList = [
        self::SCHEDULING_TYPE_DAY => '按天编辑行程',
        self::SCHEDULING_TYPE_DIY => '自定义行程',
    ];


    /**
     * 交通类型-飞机
     */
    const TRAFFIC_TYPE_PLANE = 1;

    /**
     * 交通类型-汽车
     */
    const TRAFFIC_TYPE_CAR = 2;

    /**
     * 交通类型-动车
     */
    const TRAFFIC_TYPE_MOTOR_CAR = 3;

    /**
     * 交通类型-火车
     */
    const TRAFFIC_TYPE_TRAIN = 4;

    /**
     * 交通类型-轮船
     */
    const TRAFFIC_TYPE_SHIP = 5;

    /**
     * 交通类型
     * @var array
     */
    public static $trafficTypeList = [
        self::TRAFFIC_TYPE_PLANE     => '飞机',
        self::TRAFFIC_TYPE_CAR       => '汽车',
        self::TRAFFIC_TYPE_MOTOR_CAR => '动车',
        self::TRAFFIC_TYPE_TRAIN     => '火车',
        self::TRAFFIC_TYPE_SHIP      => '轮船',
    ];

    /**
     * 购物点
     */
    const SHOP_SITE_TYPE_BUY = 1;

    /**
     * 自费点
     */
    const SHOP_SITE_TYPE_OWN = 2;

    /**
     * 消费类型
     * @var array
     */
    public static $shopSiteTypeList = [
        self::SHOP_SITE_TYPE_BUY => '购物点',
        self::SHOP_SITE_TYPE_OWN => '自费点',
    ];


    /**
     * 出发地
     */
    const ZONE_TYPE_FROM = 1;

    /**
     * 目的地
     */
    const ZONE_TYPE_TO = 2;

    public static $zoneTypeList = [
        self::ZONE_TYPE_FROM => '出发地',
        self::ZONE_TYPE_TO   => '目的地',
    ];


    /**
     * 产品发布来源-自营产品
     */
    const FROM_TYPE_SELF = 1;

    /**
     * 产品发布来源-代售产品
     */
    const FROM_TYPE_TRUST = 2;

    /**
     * 产品发布来源-云端产品
     */
    const FROM_TYPE_YUN = 3;

    /**
     * 产品发布来源
     * @var array
     */
    public static $fromTypeList = [
        self::FROM_TYPE_SELF  => '自营产品',
        self::FROM_TYPE_TRUST => '代售产品',
        self::FROM_TYPE_YUN   => '云端产品',
    ];

    /**
     * 产品业务状态-下架
     */
    const LINE_FLAG_UNDER = -1;

    /**
     * 产品业务状态-上架
     */
    const LINE_FLAG_UP = 1;

    /**
     * 产品业务状态-草稿
     */
    const LINE_FLAG_DRAFT = 2;

    /**
     * 产品业务状态
     * @var array
     */
    public static $lineFlagList = [
        self::LINE_FLAG_UNDER => '下架',
        self::LINE_FLAG_UP    => '上架',
        self::LINE_FLAG_DRAFT => '草稿',
    ];
}
