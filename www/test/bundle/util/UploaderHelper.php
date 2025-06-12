<?php
/**
 * Created by IntelliJ IDEA.
 * User: nathena
 * Date: 2018/9/11 0011
 * Time: 17:39
 */

namespace erp\util;


class UploaderHelper
{
    /**
     * 保存上传文件
     * @param  array $config 配置信息
     * @example $config = array(
     *     'file_type' => 1,  //限制上传文件类型  1.不限制，2.图片，3.文件  默认 1
     *     'file_key_name' => 'line_poster',  //上传文件键值名称 $_FILES['line_poster']
     *     'file_path' => '/line/poster',  //文件保存路径
     *     'max_size' => 2 * 1024 * 1024,  //文件大小限制 默认 2M
     * )
     * @return array 文件信息
     */
    public static function saveFile($config = [])
    {
        //上传文件类型
        $fileType = intval($config['file_type']);
        $fileType = !in_array($fileType, [1, 2, 3]) ? 1 : $fileType;

        //上传文件键值名称
        $fileKeyName = trim($config['file_key_name']);
        $fileKeyName = !empty($fileKeyName) ? $fileKeyName : 'file';

        //文件保存路径
        $filePath = trim($config['file_path']);
        $filePath = empty($filePath) ? '/other' : '/' . ltrim($filePath, '/');

        //文件大小限制
        $maxSize = $config['max_size'];
        $maxSize = !empty($maxSize) ? $maxSize : 2 * 1024 * 1024;

        //上传文件
        $upload = new Uploader($fileKeyName);
        $upload->setMaxSize($maxSize);

        if ($fileType == 2){
            $upload->setImgMimeType();
        }elseif ($fileType == 3){
            $upload->setFileMimeType();
        }

        $upload->saveTo($filePath);
        $filePath = $upload->getPath();

        $fileInfo = array(
            'path'            => $filePath,
            'file_first_name' => $upload->getTmpName(),
            'file_name'       => $upload->getName(),
            'file_ext'        => $upload->getFileExt(),
            'file_size'       => $upload->getSize(),
            'file_type'       => $upload->getFileType(),
        );

        return $fileInfo;
    }
}