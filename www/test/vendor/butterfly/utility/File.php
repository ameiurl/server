<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Utility;

/**
 * 文件处理类 
 */
class File
{

    /**
     * 根据评论ID和图片类型获取图片完整路径
     *
     * @param int    $id   照片ID
     * @param int    $type 图片类型
     * @param string $size 照片大小
     * @return string
     */
    protected static function getUrl($id, $type, $size = 's')
    {
        $srcStr = sprintf("%08d", $id);

        $part = [
            substr($srcStr, 0, -5),
            substr($srcStr, -5, 3),
            substr(md5($id . 'xltltx'), 0, 4) . '_' . $size . '.' . $type
        ];

        return '/' . implode('/', $part);
    }

	/**
	 * 遍历文件夹
	 * @param string $dir
	 * @param boolean $all  true表示递归遍历
	 * @return array
	 */
	public static function scanfDir($dir='', $all=false, &$ret=array()){
		if(false !== ($handle = opendir($dir))){
			while(false !== ($file = readdir($handle))){
				if(!in_array($file, array('.','..','.git','.gitignore','.svn','.htaccess','.buildpath','.project'))){
					$cur_path=$dir.'/'.$file;
					if(is_dir($cur_path)){
						$ret['dirs'][]=$cur_path;
						$all && self::scanfDir($cur_path,$all,$ret);
					}else{
						$ret['files'][]=$cur_path;
					}
				}
			}
			closedir($handle);
		}
		return $ret;
	}
}
