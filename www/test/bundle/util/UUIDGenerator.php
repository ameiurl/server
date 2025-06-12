<?php
namespace erp\util;

class UUIDGenerator
{
	public static function numberNo($type='',$length=2)
	{
		$no = $type.date("YmdHis");
		list($millisecond, $sec) = explode(" ", microtime());
		$millisecond = sprintf("%03d",$millisecond*1000);
	
		return $no.$millisecond.self::randNumber($length);
	}
	
	public static function charNo($type='',$length=2)
	{
		$no = $type.date("YmdHis");
		list($millisecond, $sec) = explode(" ", microtime());
		$millisecond = sprintf("%03d",$millisecond*1000);
	
		return $no.$millisecond.self::randChar($length);
	}
	
	public static function snumberNo($type='',$length=2)
	{
		$no = $type.date("ymdHis");
		list($millisecond, $sec) = explode(" ", microtime());
		$millisecond = sprintf("%03d",$millisecond*1000);
//        $millisecond = '';
	
		return $no.$millisecond.self::randNumber($length);
	}

    /**
     * %06d 6位  总共20位
     * @return string
     */
    public static function snumberNo20()
    {
        return date('YmdHi') . conver_base(substr(sprintf(
                '%2d%06d%05d',
                (int)date('s') + 16, // 当前秒数，为保证输出固定8个字符，这里给秒数加了16秒（最小值为 1600000000001）
                doubleval(microtime()) * 1000000, // 精确到微秒
                getmypid() // php 当前进程 id
            ), 0, 12), BASE_51, BASE_DEC); // 将十进制数值转换为对用户友好的 51 进制
    }
	
	public static function scharNo($type='',$length=2)
	{
		$no = $type.date("ymdHis");
		list($millisecond, $sec) = explode(" ", microtime());
		$millisecond = sprintf("%03d",$millisecond*1000);
	
		return $no.$millisecond.self::randChar($length);
	}
	
	/**
	 * 随机安全字符
	 * @param number $length
	 * @return string
	 */
	public static function randChar($length=2)
	{
		$str = "";
		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($strPol)-1;
	
		for($i=0;$i<$length;$i++)
		{
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}
		return $str;
	}
	
	//随机数字
	public static function randNumber($length=2)
	{
		$str = "";
		$strPol = "0123456789";
		$max = strlen($strPol)-1;
	
		for($i=0;$i<$length;$i++)
		{
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}
		return $str;
	}

    /**
     * YmdHisu（17） + 8  总共25位
     * @return string
     */
    public static function snumberNo25()
    {
        $uTimestamp = microtime(true);
        $timestamp = floor($uTimestamp);
        $milliseconds = round(($uTimestamp - $timestamp) * 1000);
        $str17 = date(preg_replace('`(?<!\\\\)u`', $milliseconds, 'YmdHisu'), $timestamp);

        $str8 = conver_base(substr(sprintf(
            '%2d%06d%05d',
            (int)date('s') + 16, // 当前秒数，为保证输出固定8个字符，这里给秒数加了16秒（最小值为 1600000000001）
            doubleval(microtime()) * 1000000, // 精确到微秒
            getmypid() // php 当前进程 id
        ), 0, 13), BASE_51, BASE_DEC); // 将十进制数值转换为对用户友好的 51 进制

        $str = $str17 . $str8;

        return $str;
    }
}