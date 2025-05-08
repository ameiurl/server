<?php
/**
 * User: nathena
 */

namespace erp\util;

class Downloader
{
    public static function getInstance()
    {
        return new self();
    }

    public function sendFile($file,$ngx_send=false)
    {
        if(empty($file)){
            return;
        }
        if (stripos($file, ".") === false || stripos($file, "..") !==false ) {
            return;
        }

        if(!file_exists($file)){
            return;
        }

        $this->sendHeader($file);
        if($ngx_send){
            //让Xsendfile发送文件
            header('X-Accel-Redirect: ' . $file);
        }else{
            readfile($file);
        }
    }

    public function sendData($data,$filename)
    {
        if(empty($data)){
            return;
        }
        $this->sendHeader($filename);
        echo $data;
    }

    private function sendHeader($filename)
    {
        $filename = basename($filename);
        //通用头
        header('Content-type: application/octet-stream');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Transfer-Encoding: binary");

        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            $encoded_filename = urlencode($filename);
            $encoded_filename = str_replace("+", "%20", $encoded_filename);
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
    }

    private function __construct()
    {
        ob_clean();
    }
}