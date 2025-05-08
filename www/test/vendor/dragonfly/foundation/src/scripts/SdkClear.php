<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2019/4/16 0016
 * Time: 13:51
 */

namespace Dragonfly\foundation\scripts;


class SdkClear
{
    public static function clear()
    {
        $vendor_cncn_path = dirname(dirname(dirname(dirname(__FILE__))));
        echo $vendor_cncn_path,"\n";
        $dh=opendir($vendor_cncn_path);
        while($file=readdir($dh))
        {
            if($file!="." && $file!="..")
            {
                $gitpath=$vendor_cncn_path."/".$file;
                if(is_dir($gitpath))
                {
                    scan_sdk_git($gitpath);
                }
            }
        }
        closedir($dh);
    }
}

function scan_sdk_git($sdk)
{
    $dh=opendir($sdk);
    while($file=readdir($dh))
    {
        if($file!="." && $file!=".." && ".git" == $file)
        {
            $gitpath=$sdk."/".$file;
            if(is_dir($gitpath))
            {
                echo $gitpath,"\n";
                del_sdk_git($gitpath);
            }
            echo "\n";
        }
    }
    closedir($dh);
}

function del_sdk_git($dir)
{
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh))
    {
        if($file!="." && $file!="..")
        {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                @unlink($fullpath);
            } else {
                del_sdk_git($fullpath);
            }
        }
    }
    closedir($dh);
    //删除当前文件夹：
    @rmdir($dir);
}
