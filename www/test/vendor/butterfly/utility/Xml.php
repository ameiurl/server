<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Utility;

use DOMDocument,
    DOMElement,
    Exception,
    SimpleXMLElement;

/**
 * Xml - XML操作类
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Xml
{
    /**
     * 是否格式化 XML
     *
     * @var bool
     */
    public static $formatXml = false;

    /**
     * 转换数组为 XML
     *
     * 使用示例：
     * 1. _不_带相同key的传参方式
     * ```php
     * $data = [
     *      'DepartCity'    => 'WUH',
     *      'ArrivalCity'   => 'PEK',
     *      'DepartDate'    => '2015-12-24',
     * ];
     * Butterfly\Xml::fromArray($data)
     * ```
     *
     * 生成 xml
     * ```xml
     * <Request>
     *   <DepartCity>WUH</DepartCity>
     *   <ArrivalCity>PEK</ArrivalCity>
     *   <DepartDate>2015-12-24</DepartDate>
     * </Request>
     * ```
     *
     * 2. 带相同key的传参方式
     * ```php
     * $data = [
     *     'passenger' => [
     *         0 => ['name' => 'John Doe', 'gender' => 'male'],
     *         1 => ['name' => 'Jane Doe', 'gender' => 'female'],
     *     ],
     * ];
     * Butterfly\Xml::fromArray($data, 'passengers');
     * ```
     *
     * 生成 xml
     * ```xml
     * <passengers>
     *   <passenger>
     *     <name>John Doe</name>
     *     <gender>male</gender>
     *   </passenger>
     *   <passenger>
     *     <name>Jane Doe</name>
     *     <gender>female</gender>
     *   </passenger>
     * </passengers>
     * ```
     *
     * @param  array  $arr      要转换的数组
     * @param  string $rootName 根节点的名称
     * @return string            转换后的 XML
     */
    public static function fromArray(array $arr, $rootName = 'Request')
    {
        $dom = new DOMDocument();

        $dom->preserveWhiteSpace = false;
        // from http://stackoverflow.com/questions/8615422/php-xml-how-to-output-nice-format
        if (self::$formatXml) {
            $dom->formatOutput = true;
        }

        try {
            $root = $dom->createElement($rootName);
            foreach ($arr as $key => $value) {
                self::convertNode($value, $key, $root);
            }
            $dom->appendChild($root);

            /* $dom->documentElement 去掉 <?xml version="1.0"?> 声明 */
            return $dom->saveXML($dom->documentElement);
        } catch (Exception $ex) {
            return 'Malform XML: ' . $ex->getMessage();
        }
    }

    /**
     * 转换节点元素
     *
     * @param  string|string[] $nodeValue 节点元素的值
     * @param  string          $nodeName  节点元素的名称
     * @param  \DOMNode        $parent    父节点
     */
    protected static function convertNode($nodeValue, $nodeName, $parent)
    {
        if (is_array($nodeValue)) {
            foreach ($nodeValue as $key => $value) {
                if (is_array($value)) {
                    $child = $parent->appendChild(new DOMElement($nodeName));
                    if (!is_numeric($key)) {
                        self::convertNode($value, $key, $child);
                    } else {
                        foreach ($value as $k => $v) {
                            self::convertNode($v, $k, $child);
                        }
                    }
                } else {
                    if (!$parent->hasChildNodes()) {
                        $child = $parent->appendChild(new DOMElement($nodeName));
                    }
                    if (isset($child)) {
                        $child->appendChild(new DOMElement($key, $value));
                    }
                }
            }
        } else {
            $parent->appendChild(new DOMElement($nodeName, $nodeValue));
        }
    }

    /**
     * 转换 XML 字符串为数组
     *
     * @param  string $xmlStr XML 字符串
     * @return array
     */
    public static function toArray($xmlStr)
    {
        // ref. http://php.net/manual/en/book.simplexml.php#105330
        // http://stackoverflow.com/questions/2970602/php-how-to-handle-cdata-with-simplexmlelement
        // 此转换方法的缺陷：会丢失 XML的属性，例如 <a href="foo">bar</a> 的 href 属性会丢失
        try {
            $sxe = new SimpleXMLElement($xmlStr, LIBXML_NOCDATA);
            return json_decode(json_encode($sxe), true);
        } catch (Exception $ex) {
            return [];
        }
    }
}
