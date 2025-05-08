<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Pagination;

/**
 * 分页类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Pagination
{
    /**
     * 当前页的查询结果
     *
     * @var array
     */
    public $results;

    /**
     * 当前第几页
     *
     * @var int
     */
    protected $currentPage = 1;

    /**
     * 总页数
     *
     * @var int
     */
    public $pageCount;

    /**
     * 记录总条数
     *
     * @var int
     */
    public $rowCount;

    /**
     * 每页显示的记录条数
     *
     * @var int
     */
    protected $perPage;

    /**
     * 偏移量
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * 分页样式， 详见 config/default.php pagination 数组
     *
     * @var array
     */
    protected $format;

    /**
     * HTTP GET 请求参数
     *
     * @var array
     */
    protected $request;

    /**
     * 请求URL参数(不带查询字符串的“/”分隔的前三个部分)
     *
     * @var string
     */
    protected $uri = null;

    /**
     * 创建分页类实例
     *
     * @param int   $currentPage
     * @param int   $rowCount
     * @param int   $perPage
     * @param int   $pageCount
     */
    public function __construct(
        $currentPage,
        $rowCount,
        $perPage,
        $pageCount
    ) {
        $this->currentPage = $currentPage;
        $this->pageCount   = $pageCount;
        $this->rowCount    = $rowCount;
        $this->perPage     = $perPage;
//        $this->format = Config::get('default.pagination');

        $this->offset = ($this->currentPage - 1) * $this->perPage;
    }

    /**
     * 设置当前页的查询结果
     *
     * @param array $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * 获取当前页码
     *
     * @param  int $page
     * @param  int $rowCount
     * @param  int $perPage
     * @return int
     */
    public static function currentPage($page, $rowCount, $perPage)
    {
        $currentPage = (int) $page;

        $pageCount = ceil($rowCount / $perPage);
        if ($currentPage > $pageCount) {
            return ($pageCount > 0) ? $pageCount : 1;
        } else {
            return $currentPage ?: 1;
        }
    }

    /**
     * 获取当前的偏移量
     *
     * @return int
     */
    public function offset(): int
    {
        return $this->offset;
    }

    /**
     * 获取当前要取的记录数
     *
     * @return int
     */
    public function limit() : int
    {
        return $this->perPage;
    }

    /**
     * 获取当前页数
     *
     * @return int
     */
    public function page() : int
    {
        return $this->currentPage;
    }

    /**
     * 把分页数据以数组的形式返回
     *
     * @return array
     */
    public function toArray() : array
    {
        return [
            'current_page'  => $this->currentPage,
            'page_count'    => $this->pageCount,
            'row_count'     => $this->rowCount,
            'per_page'      => $this->perPage,
            'offset'        => $this->offset,
            'results'       => $this->results,
        ];
    }
}
