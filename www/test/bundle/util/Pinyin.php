<?php
/**
 * 汉字转拼音扩展类
 */
namespace erp\util;
class Pinyin
{
    protected $dictionary = array();
    protected $settings = array(
        'delimiter' => '',
        'accent' => false,
        'only_chinese' => false,
        'uppercase' => false,
        'charset' => 'UTF-8' // GB2312,UTF-8
    );

    public  function __construct()
    {
        $this->dictionary = json_decode(file_get_contents(dirname(__FILE__).'/../../config/pinyin/dict.json'), true);

    }

    /**
     * 设置转换参数
     */
    public function set($key, $value)
    {
        $this->settings[$key] = $value;
    }

    /**
     * 设置转换参数
     */
    public function settings(array $settings = array())
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * 转换为拼音
     */
    public function trans($string, array $settings = array())
    {
        $parsed = $this->parse($string, $settings);
        return $parsed['pinyin'];
    }

    /**
     * 首字母转换为拼音
     */
    public function letter($string, array $settings = array())
    {
        $settings = array_merge($settings, array('accent' => false, 'only_chinese' => true));
        $parsed = $this->parse($string, $settings);
        return $parsed['letter'];
    }

    /**
     * 解析中文字符
     */
    public function parse($string, array $settings = array())
    {
        $raw = $string;
        $settings = array_merge($this->settings, $settings);
        // add charset set
        if (!empty($settings['charset']) && $settings['charset'] != 'UTF-8') {
            $string = iconv($settings['charset'], 'UTF-8', $string);
        }
        // remove non-Chinese char.
        if ($settings['only_chinese']) {
            $string = $this->justChinese($string);
        }
        $source = $this->string2pinyin($string);
        // add accents
        if ($settings['accent']) {
            $pinyin = $this->addAccents($source);
        } else {
            $pinyin = $this->removeTone($source);
        }
        //add delimiter
        $delimitedPinyin = $this->delimit($pinyin, $settings['delimiter']);
        $return = array(
            'src' => $raw,
            'pinyin' => stripslashes($delimitedPinyin),
            'letter' => stripslashes($this->getFirstLetters($source, $settings)),
        );
        return $return;
    }

    /**
     * Get first letters from pinyin.
     *
     * @param string $pinyin
     * @param array $settings
     *
     * @return string
     */
    protected function getFirstLetters($pinyin, $settings)
    {
        $letterCase = $settings['uppercase'] ? 'strtoupper' : 'strtolower';
        $letters = array();
        foreach (explode(' ', $pinyin) as $word) {
            if (empty($word)) {
                continue;
            }
            $ord = ord(strtolower($word{0}));
            if ($ord >= 97 && $ord <= 122) {
                $letters[] = $letterCase($word{0});
            }
        }
        return implode($settings['delimiter'], $letters);
    }

    /**
     * 转换为拼音
     */
    protected function string2pinyin($string)
    {
        $pinyin = strtr($this->prepare($string), $this->dictionary);
        return trim(str_replace('  ', ' ', $pinyin));
    }

    /**
     * 判断是否包含中文字符
     */
    protected function containChinese($string)
    {
        return preg_match('/\p{Han}+/u', $string);
    }

    /**
     * 删除非中文字符
     */
    public function justChinese($string)
    {
        return preg_replace('/[^\p{Han}]/u', '', $string);
    }

    protected function prepare($string)
    {
        $pattern = array(
            '/([A-z])(\d)/' => '$1\\\\\2', // test4 => test\\4
        );
        return preg_replace(array_keys($pattern), $pattern, $string);
    }

    /**
     * 增加分隔符
     */
    protected function delimit($string, $delimiter = '')
    {
        return preg_replace('/\s+/', strval($delimiter), trim($string));
    }

    /**
     * 删除后缀
     */
    protected function removeTone($string)
    {
        $replacement = array(
            '/u:/' => 'u',
            '/([a-z])[1-5]/i' => '\\1',
        );
        return preg_replace(array_keys($replacement), $replacement, $string);
    }

    /**
     * 增加音调
     * at http://stackoverflow.com/questions/1598856/convert-numbered-to-accentuated-pinyin.
     */
    protected function addAccents($string)
    {
        // find words with a number behind them, and replace with callback fn.
        return str_replace('u:', 'ü', preg_replace_callback(
            '~([a-zA-ZüÜ]+\:?)([1-5])~',
            array($this, 'addAccentsCallback'),
            $string));
    }

    protected function addAccentsCallback($match)
    {
        static $accentmap = null;
        if ($accentmap === null) {
            // where to place the accent marks
            $stars = 'a* e* i* o* u* ü* ü* ' .
                'A* E* I* O* U* Ü* ' .
                'a*i a*o e*i ia* ia*o ie* io* iu* ' .
                'A*I A*O E*I IA* IA*O IE* IO* IU* ' .
                'o*u ua* ua*i ue* ui* uo* üe* ' .
                'O*U UA* UA*I UE* UI* UO* ÜE*';
            $nostars = 'a e i o u u: ü ' .
                'A E I O U Ü ' .
                'ai ao ei ia iao ie io iu ' .
                'AI AO EI IA IAO IE IO IU ' .
                'ou ua uai ue ui uo üe ' .
                'OU UA UAI UE UI UO ÜE';
            // build an array like array('a' => 'a*') and store statically
            $accentmap = array_combine(explode(' ', $nostars), explode(' ', $stars));
        }
        $vowels = array('a*', 'e*', 'i*', 'o*', 'u*', 'ü*', 'A*', 'E*', 'I*', 'O*', 'U*', 'Ü*');
        $pinyin = array(
            1 => array('ā', 'ē', 'ī', 'ō', 'ū', 'ǖ', 'Ā', 'Ē', 'Ī', 'Ō', 'Ū', 'Ǖ'),
            2 => array('á', 'é', 'í', 'ó', 'ú', 'ǘ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ǘ'),
            3 => array('ǎ', 'ě', 'ǐ', 'ǒ', 'ǔ', 'ǚ', 'Ǎ', 'Ě', 'Ǐ', 'Ǒ', 'Ǔ', 'Ǚ'),
            4 => array('à', 'è', 'ì', 'ò', 'ù', 'ǜ', 'À', 'È', 'Ì', 'Ò', 'Ù', 'Ǜ'),
            5 => array('a', 'e', 'i', 'o', 'u', 'ü', 'A', 'E', 'I', 'O', 'U', 'Ü'),
        );
        list(, $word, $tone) = $match;
        // add star to vowelcluster
        $word = strtr($word, $accentmap);
        // replace starred letter with accented
        $word = str_replace($vowels, $pinyin[$tone], $word);
        return $word;
    }
}
