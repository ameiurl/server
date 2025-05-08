<?php

namespace Dragonfly\Ubb;


class Ubb
{

    protected static $arr = [
        [
            'key' => "333333",
            'val' => "灰色-80%"
        ],
        [
            'key' => "666666",
            'val' => "灰色-60%"
        ],
        [
            'key' => "999999",
            'val' => "灰色-40%"
        ],
        [
            'key' => "cccccc",
            'val' => "灰色-20%"
        ],
        [
            'key' => "bb0000",
            'val' => "深红"
        ],
        [
            'key' => "dd0000",
            'val' => "红色"
        ],
        [
            'key' => "ee4488",
            'val' => "粉红"
        ],
        [
            'key' => "ff66dd",
            'val' => "淡紫"
        ],
        [
            'key' => "333399",
            'val' => "深蓝"
        ],
        [
            'key' => "0066cc",
            'val' => "蓝色"
        ],
        [
            'key' => "0099cc",
            'val' => "天蓝"
        ],
        [
            'key' => "66cccc",
            'val' => "淡蓝"
        ],
        [
            'key' => "336600",
            'val' => "深绿"
        ],
        [
            'key' => "999900",
            'val' => "深黄"
        ],
        [
            'key' => "cccc33",
            'val' => "淡黄"
        ],
        [
            'key' => "77cc33",
            'val' => "淡绿"
        ],
        [
            'key' => "663300",
            'val' => "咖啡"
        ],
        [
            'key' => "cc6633",
            'val' => "褐色"
        ],
        [
            'key' => "ff9900",
            'val' => "橙黄"
        ],
        [
            'key' => "ffcc33",
            'val' => "黄色"
        ]
    ];

    private static function getReplaceArr()
    {
        $length = count(self::$arr);
        for ($i = 0; $i < $length; $i++) {
            $colorkey                         = self::$arr[$i]['key'];
            $data['[font' . $colorkey . ']']  = '<font color="#' . $colorkey . '">';
            $data['[font1' . $colorkey . ']'] = '<font size="1" color="#' . $colorkey . '">';
            $data['[font2' . $colorkey . ']'] = '<font size="2" color="#' . $colorkey . '">';
            $data['[font3' . $colorkey . ']'] = '<font size="3" color="#' . $colorkey . '">';
            $data['[font4' . $colorkey . ']'] = '<font size="4" color="#' . $colorkey . '">';
        }
        $data['[font1]'] = '<font size="1">';
        $data['[font2]'] = '<font size="2">';
        $data['[font3]'] = '<font size="3">';
        $data['[font4]'] = '<font size="4">';
        $data['[/font]'] = '</font>';
        $data['[span]']  = '<span>';
        $data['[/span]'] = '</span>';

        $data['[b]']           = '<b>';
        $data['[/b]']          = '</b>';
        $data['[p]']           = '<p>';
        $data['[/p]']          = '</p>';
        $data['[br]']          = '<br/>';
        $data['[s]']           = '&nbsp;';
        $data['[a end]']       = '<a target="_blank"';
        $data['[end]']         = '>';
        $data['[/a]']          = '</a>';
        $data['&quot;']        = '"';
        $data['<span></span>'] = '';

        return $data;
    }

    /**
     * ubb替换成html
     * @param string $text
     * @param string $rep
     * @return mixed
     */
    public static function toHtml($text, $rep = 'all')
    {
        $replaceData = self::getReplaceArr();

        foreach ($replaceData as $key => $val) {
            if ($rep == 'noall') {
                if ($key == '[a end]' || $key == '[end]' || $key == '[/a]') {
                    $text = str_replace([' ' . $key, $key], [$val, $val],
                        $text);
                } else {
                    $text = str_replace([' ' . $key, $key], ['', ''], $text);
                }
            } else {
                $text = str_replace([' ' . $key, $key], [$val, $val], $text);
            }
        }

        return $text;
    }

    /**
     *  uub替换成空字符串
     *
     * @param string $text
     * @return mixed
     */
    public static function toNull($text)
    {
        $replaceData = self::getReplaceArr();

        foreach ($replaceData as $key => $val) {
            $text = str_replace([' ' . $key, $key], ['', ''], $text);
        }

        return $text;
    }
}
