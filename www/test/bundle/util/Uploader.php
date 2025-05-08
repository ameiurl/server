<?php
/**
 * file_uploads，upload_max_filesize，upload_tmp_dirpost_max_size 以及 max_input_time
 *
 * User: nathena
 * Date: 2017/6/8 0008
 * Time: 14:08
 */

namespace erp\util;

use erp\context\Context;

class Uploader
{
    /**
     * 文件头部信息，十六进制信息，取前4位
     * JPEG (jpg)，文件头：FFD8FFe1
     * PNG (png)，文件头：89504E47
     * GIF (gif)，文件头：47494638
     * TIFF (tif)，文件头：49492A00
     * Windows Bitmap (bmp)，文件头：424D
     * CAD (dwg)，文件头：41433130
     * Adobe Photoshop (psd)，文件头：38425053
     * Rich Text Format (rtf)，文件头：7B5C727466
     * XML (xml)，文件头：3C3F786D6C HTML
     * (html)，文件头：68746D6C3E
     * Email [thorough only]  (eml)，文件头：44656C69766572792D646174653A
     * Outlook Express (dbx)，文件头：CFAD12FEC5FD746F
     * Outlook (pst)，文件头：2142444E
     * MS Word/Excel (xls.or.doc)，文件头：D0CF11E0
     * MS Access (mdb)，文件头：5374616E64617264204A
     * WordPerfect (wpd)，文件头：FF575043
     * Postscript (eps.or.ps)，文件头：252150532D41646F6265
     * Adobe Acrobat (pdf)，文件头：255044462D312E
     * Quicken (qdf)，文件头：AC9EBD8F
     * Windows Password (pwl)，文件头：E3828596
     * ZIP Archive (zip)，文件头：504B0304
     * RAR Archive (rar)，文件头：52617221
     * Wave (wav)，文件头：57415645
     * AVI (avi)，文件头：41564920
     * Real Audio (ram)，文件头：2E7261FD
     * Real Media (rm)，文件头：2E524D46
     * MPEG (mpg)，文件头：000001BA
     * MPEG (mpg)，文件头：000001B3
     * Quicktime (mov)，文件头：6D6F6F76
     * Windows Media (asf)，文件头：3026B2758E66CF11
     * MIDI (mid)，文件头：4D546864
     * MP4 (mp4)，文件头：文件头：00000020667479706d70
     */

    //取前4位,十六进制
    private static $file_hex_headers = [
        'jpg' => ['FFD8FFe1', 'FFD8FFE0'],
        'jpeg' => 'FFD8FFe1',
        'gif' => '47494638',
        'png' => '89504E47',
        'tif' => '49492A00',
        'bmp' => '424D',
        'dwg' => '41433130',
        'psd' => '38425053',
        //'rtf' => '7B5C727466',
        //'xml' => '3C3F786D6C',
        //'htm' => '68746D6C3E',
        //'html' => '68746D6C3E',
        //'html5' => '68746D6C3E',
        'eml' => '44656C69766572792D646174653A',
        'dbx' => 'CFAD12FEC5FD746F',
        'pst' => '2142444E',
        'xls' => 'D0CF11E0',
        'doc' => 'D0CF11E0',
        'docx'=> '504B0304',
        'xlsx'=> '504B0304',
        'mdb' => '5374616E64617264204A',
        'wpd' => 'FF575043',
        'eps' => '252150532D41646F6265',
        'ps' => '252150532D41646F6265',
        'pdf' => '255044462D312E',
        'qdf' => 'AC9EBD8F',
        'pwl' => 'E3828596',
        'zip' => '504B0304',
        'rar' => '52617221',
        'wav' => '57415645',
        'avi' => '41564920',
        'ram' => '2E7261FD',
        'rm' => '2E524D46',
        'mpg' => ['000001BA', '000001B3'],
        'mov' => '6D6F6F76',
        'asf' => '3026B2758E66CF11',
        'mid' => '4D546864',
        'mp4' => '00000020667479706d70',
    ];

    public static function addFileHeader($ext, $bin)
    {
        $ext = strtolower($ext);
        if (!isset(self::$file_hex_headers[$ext])) {
            self::$file_hex_headers[$ext] = $bin;
        } else {
            $new_bin = [];
            $old_bin = self::$file_hex_headers[$ext];
            $new_bin = is_array($old_bin) ? array_merge($new_bin, $old_bin) : [$old_bin];
            $new_bin[] = $bin;
            self::$file_hex_headers[$ext] = $new_bin;
        }
    }

    public static function getFileHeaderInfo($file)
    {
        $file_info = explode(".", $file);
        $ext = end($file_info);
        $bin = self::_getFileHeader0($file);

        return [$ext, $bin];
    }

    //上传根目录
    protected $upload_root_path;

    //上传错误
    private $err_arr;
    private $max_size;
    private $mime_arr;
    private $img_mime_arr;
    private $file_mime_arr;
    private $disallowedTypes;
    private $filedata;
    private $msg;
    private $processed = false;

    private $tmp_type;
    private $tmp_name;
    private $name;
    private $tmp_path;
    private $path;
    private $tmp_size;
    private $tmp_fileExt;
    private $file_type;  //1.文件  2.图片

    //组织架构数据
    private $erp_id;
    private $company_id;
    private $department_id;
    private $erp_add_time;

    public function __construct($filedata, $msg = "")
    {
        $token = Context::getInstance()->getToken();
        $this->erp_id        = $token->erp_id;
        $this->company_id    = $token->company_id;
        $this->department_id = $token->department_id;
        $this->erp_add_time  = $token->currentErpModel->add_time;

        //$this->upload_root_path = "./upload/{$this->erp_id}/{$this->company_id}/{$this->department_id}";
        $this->upload_root_path = "./upload/{$this->erp_id}_{$this->erp_add_time}/{$this->company_id}/{$this->department_id}";

        $this->err_arr = [
            1 => '上传的文件超过php.ini中的upload_max_filesize选项限制的值:' . ini_get('upload_max_filesize'),
            2 => '上传的文件超过隐藏表的的MAX_FILE_SIZE指定的值',
            3 => '文件只有部分被上传',
            4 => '没有文件被上传',
            6 => '找不到临时路径',
            7 => '文件写入失败',
            8 => 'A PHP extension stopped the file upload.',
            9 => 'The uploaded file exceeds the user-defined max file size.',
            10 => 'The uploaded file is not allowed.',
            11 => 'The specified upload directory does not exist.',
            12 => 'The specified upload directory is not writable.',
            13 => 'Unexpected error.'
        ];
        //判断文件大小
        $this->max_size = 1024 * 1024 * 10;

        //图片文件扩展
        $this->img_mime_arr = [
            'image/gif',
            'image/png',
            'image/jpg',
            'image/jpeg',
            'image/pjpeg',
            'image/x-png',
        ];

        //文件扩展
        $this->file_mime_arr = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/x-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/pdf',
            'application/zip',
            'application/rar',
            'application/x-zip-compressed',
            'application/x-tar',
            'application/x-rar-compressed',
            'application/octet-stream',
            'application/x-pkcs12',
            'application/x-x509-ca-cert',
        ];

        //所有文件扩展
        $this->mime_arr = array_merge($this->img_mime_arr, $this->file_mime_arr);

        $this->disallowedTypes = [
            'css', 'htm', 'html', 'js', 'json', 'pgsql', 'php', 'php3', 'php4', 'php5', 'sql', 'sqlite', 'yaml', 'yml', 'pfx', 'cer'
        ];

        $this->filedata = trim($filedata);
        $this->msg = trim($msg);
    }

    /**
     * 设置文件限制大小
     * @param $max_size
     */
    public function setMaxSize($max_size)
    {
        $this->max_size = $max_size;
    }

    /**
     * 设置文件上传根目录
     */
    protected function setFileUploadRootPath(){
        $this->upload_root_path = rtrim($this->upload_root_path, '/') . '/file/';
    }

    /**
     * 设置图片文件上传根目录
     */
    protected function setImgUploadRootPath(){
        $this->upload_root_path = rtrim($this->upload_root_path, '/') . '/image/';
    }

    /**
     * 添加允许上传文件扩展类型
     * @param $mime_types
     */
    public function addAllowdMimeType($mime_types)
    {
        if (is_array($mime_types)) {
            $this->mime_arr = array_merge($this->mime_arr, $mime_types);
        } else {
            $this->mime_arr[] = trim($mime_types);
        }
    }

    /**
     * 添加禁止上传文件扩展类型
     * @param $disallowed_types
     */
    public function addDisallowedTypes($disallowed_types)
    {
        if (is_array($disallowed_types)) {
            $this->disallowedTypes = array_merge($this->disallowedTypes, $disallowed_types);
        } else {
            $this->disallowedTypes[] = $disallowed_types;
        }
    }

    /**
     * 设置允许上传文件扩展类型
     * @param $mime_types
     */
    public function setAllowdMimeType($mime_types)
    {
        if (is_array($mime_types)) {
            $this->mime_arr = $mime_types;
        } else {
            $this->mime_arr = [trim($mime_types)];
        }
    }

    /**
     * 设置禁止上传文件扩展类型
     * @param $disallowed_types
     */
    public function setDisallowedTypes($disallowed_types)
    {
        if (is_array($disallowed_types)) {
            $this->disallowedTypes = $disallowed_types;
        } else {
            $this->disallowedTypes = [$disallowed_types];
        }
    }

    /**
     * 设置图片文件扩展
     */
    public function setImgMimeType(){
        $this->setAllowdMimeType($this->img_mime_arr);
    }

    /**
     * 设置文件扩展
     */
    public function setFileMimeType(){
        $this->setAllowdMimeType($this->file_mime_arr);
    }

    /**
     * 获取缓存文件名称
     * @return mixed
     */
    public function getTmpName()
    {
        $this->process();
        return $this->tmp_name;
    }

    /**
     * 获取文件名称
     * @return mixed
     */
    public function getName()
    {
        $this->process();
        return $this->name;
    }

    /**
     * 获取缓存文件路径
     * @return mixed
     */
    public function getTmpPath()
    {
        $this->process();
        return $this->tmp_path;
    }

    /**
     * 获取文件保存路径
     * @return mixed
     */
    public function getPath()
    {
        $this->process();
        return $this->path;
    }

    /**
     * 获取文件大小
     * @return mixed
     */
    public function getSize()
    {
        $this->process();
        return $this->tmp_size;
    }

    /**
     * 获取文件扩展名
     * @return mixed
     */
    public function getFileExt()
    {
        $this->process();
        return $this->tmp_fileExt;
    }

    /**
     * 获取文件类型
     * @return mixed
     */
    public function getFileType(){
        $this->process();
        return $this->file_type;
    }

    /**
     * 保存文件
     * @param $path
     * @return string
     */
    public function saveTo($path)
    {
        $this->process();

        //文件名称
        $filename = UUIDGenerator::snumberNo() . '.' . $this->tmp_fileExt;

        //保存路径
        if(in_array($this->tmp_type, $this->img_mime_arr)){  //图片类型
            $this->file_type = 2;
            $this->setImgUploadRootPath();
        }else{  //文件类型
            $this->file_type = 1;
            $this->setFileUploadRootPath();
        }

        $path = $this->upload_root_path . ltrim($path, '/') . '/' . date('Y-m-d');

        if(!file_exists($path)){
            mkdir($path, 0755, true);
        }

        if(!is_dir($path)){
            throw new \RuntimeException('创建目录'. $path. '失败,请检查目录名是否被占用 或者 目录是否有写权限');
        }

        $destination = $path  . '/' . $filename;
        if(!move_uploaded_file($this->tmp_path, $destination)){
            throw new \RuntimeException('保存上传文件'. $this->tmp_name. '失败,');
        }

        $destination = ltrim($destination, '.');
        $this->name = $filename;
        $this->path = $destination;

        return $destination;
    }

    protected function process()
    {
        if ($this->processed) {
            return;
        }
        $this->processed = true;

        $filedata = $this->filedata;
        if (!empty($_FILES[$filedata])) {
            $_file = $_FILES[$filedata];
            if (4 == $_file['error']) {
                return;
            }
            if (!empty($_file['error'])) {
                throw new \RuntimeException($this->msg . '上传失败：' . $this->err_arr[$_file['error']]);
            }

            if ($_file['size'] > $this->max_size) {
                throw new \RuntimeException($this->msg . '上传文件过大, 不能超过：' . intval($this->max_size / 1024 / 1024) . 'M');
            }
            //判断MIME类型
            if (!in_array($_file['type'], $this->mime_arr)) {
                throw new \RuntimeException($this->msg . '上传文件格式错误:'.$_file['type']);
            }

            //判断是否是所上传的文件
            if (!is_uploaded_file($_file['tmp_name'])) {
                throw new \RuntimeException($this->msg . '上传出错，请稍后再试');
            }

            $tmp_name = $_file['name'];
            $tmp_path = $_file['tmp_name'];

            $tmp_name_ext = explode(".", $tmp_name);
            $tmp_name_ext = end($tmp_name_ext);
            //$this->check_file($tmp_name_ext, self::_getFileHeader0($tmp_path));

            $this->tmp_type = $_file['type'];
            $this->tmp_name = $tmp_name;
            $this->tmp_path = $tmp_path;
            $this->tmp_fileExt = $tmp_name_ext;
            $this->tmp_size = $_file['size'];
        }else{
            throw new \RuntimeException($this->msg . $this->filedata.'上传出错，请稍后再试');
        }
    }

    /**
     * 严格验证文件
     * @param $ext
     * @param $bin
     */
    protected function check_file($ext, $bin)
    {
        $ext = strtolower($ext);
        $bin = strtoupper($bin);

        if (in_array($ext, $this->disallowedTypes)) {
            throw new \RuntimeException($this->msg . '不允许上传' . $ext . '文件');
        }

        if (!isset(self::$file_hex_headers[$ext])) {
            throw new \RuntimeException($this->msg . '文件格式不允许:'.$bin);
        }

        $headers = self::$file_hex_headers[$ext];
        if (is_string($headers)) {
            $headers = [$headers];
        }
        foreach ($headers as $header) {
            if ($header == $bin) {
                return;
            }
        }
        throw new \RuntimeException($this->msg . '文件格式异常');
    }

    private static function _getFileHeader0($file)
    {
        if (!is_file($file)) {
            throw new \RuntimeException("获取文件头失败");
        }
        $fh = fopen($file, "rb");
        $head = fread($fh, 4);
        fclose($fh);

        return strtoupper(bin2hex($head));
    }
}