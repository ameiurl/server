<?php
/**
 * 姓名转换
 * User: zhangbiaohua
 * Date: 2019/11/12
 * Time: 10:36
 */

namespace erp\util;


class ChineseNameConvert
{
    protected $settings = [
        'charset'    => 'UTF-8',
        'outpinyin'  => false //是否输出拼音
    ];

    public function __construct($param = [])
    {
        if ($param) {
            $this->settings = array_merge($this->settings, $param);
        }
    }

    /**
     * 拆分姓名（姓氏和名字）
     * @param string $fullname 全名（如：百里屠苏）
     * @return array 一维数组[0=>'姓氏',1=>'名称']
     * @author: 爱是西瓜<blog.mbku.net>
     * @return array
     */
    public function splitName($fullname) {
        $hyphenated = [
            '百里','北堂','北野','北宫','辟闾',
            '淳于','成公','陈生','褚师','城池',
            '端木','东方','东郭','东野','东门','第五','大狐','段干','段阳','第二','东宫',
            '公孙','公冶','公羊','公良','公西','公孟','公伯','公析','公肩','公坚','公乘','公皙','公户','公广','公仪','公祖','公玉','公仲','公上','公门','公山','高堂','高阳','郭公','谷梁','毌将','毌丘','单于','叱干','叱利','车非',
            '独孤','大野','独吉','达奚','东里',
            '哥舒','贯丘',
            '皇甫','黄龙','胡母','何阳','赫连','呼延','贺兰','贺若','黑齿','斛律','斛粟',
            '夹谷','九方','即墨','吉胡',
            '可频',
            '梁丘','闾丘','洛阳','陵尹','冷富','龙丘','令狐',
            '慕容','万俟','抹捻',
            '纳兰','南荣',
            '南宫','南郭','女娲','南伯','南容','南门','南野',
            '欧阳','欧侯',
            '濮阳','普周','仆固','仆散','蒲察',
            '青阳','漆雕','亓官','渠丘','屈突','屈卢','钳耳',
            '壤驷','汝嫣',
            '上官','少室','少叔','司徒','司马','司空','司寇','士孙','申屠','申徒','申鲜','申叔','夙沙','叔先','叔仲','叔孙','侍其','是云','索卢','厍狄',
            '澹台','太史','太叔','太公','屠岸','唐古','拓跋','同蹄','秃发',
            '闻人','巫马','微生','王孙','无庸','完颜',
            '夏侯','西门','信平','鲜于','轩辕','相里','新垣','徐离姓',
            '羊舌','羊角','延陵','於陵','伊祁','吾丘','乐正','宇文','尉迟','耶律',
            '诸葛','颛孙','仲孙','仲长','钟离','宗政','主父','中叔','左人','左丘','宰父','长儿','仉督','长孙','子车','子书','子桑'
        ];

        $vLength = mb_strlen($fullname, $this->settings['charset']);
        $lastname = '';
        $firstname = '';//前为姓,后为名
        if ($vLength > 2) {
            $preTwoWords = mb_substr($fullname, 0, 2, $this->settings['charset']);//取命名的前两个字,看是否在复姓库中
            if (in_array($preTwoWords, $hyphenated)) {
                $lastname = $preTwoWords;
                $firstname = mb_substr($fullname, 2, 10, $this->settings['charset']);
            } else {
                $lastname = mb_substr($fullname, 0, 1, $this->settings['charset']);
                $firstname = mb_substr($fullname, 1, 10, $this->settings['charset']);
            }
        } else if($vLength == 2) {//全名只有两个字时,以前一个为姓,后一下为名
            $lastname = mb_substr($fullname ,0, 1, $this->settings['charset']);
            $firstname = mb_substr($fullname, 1, 10, $this->settings['charset']);
        } else {
            $lastname = $fullname;
        }

        return $this->settings['outpinyin'] ? $this->convertPinYin([$lastname, $firstname]) : [$lastname, $firstname];
    }


    public function convertPinYin($data) {
        $handler = new Pinyin();
        if (is_array($data)) {
            foreach ($data as &$val) {
                $val = $handler->parse($val);
            }
        }
        else {
            $data = $handler->parse($data);
        }

        return $data;
    }
}