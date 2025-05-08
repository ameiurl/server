<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Pagination;

/**
 * 分页类工厂
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class PaginationFactory
{
    /**
     * 配置选项
     *
     * @var array
     */
    protected $options = [];

    /**
     * 构造函数
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * 创建分页类
     *
     * @param int       $rowCount 总记录数
     * @param int       $perPage  每页显示的记录数
     * @param array|int $options  为整数时，指第几页；为数组时为配置数组
     * @return Pagination
     */
    public function create($rowCount, $perPage, $options = [])
    {
        $perPage = $perPage ?? $this->options['per_page'];
        if (is_array($options)) {
            $options += $this->options;
            $page = $_REQUEST[$options['page_key']] ?? 1;
        } else {
            $page = (int)$options;
        }

        $currentPage = Pagination::currentPage($page, $rowCount, $perPage);

        $pageCount = (int)ceil($rowCount / $perPage);

        return new Pagination($currentPage, $rowCount, $perPage, $pageCount);
    }
}
