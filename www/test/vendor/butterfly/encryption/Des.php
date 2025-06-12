<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Encryption;

use InvalidArgumentException;

/**
 * DES 加密
 *      使用 openssl 扩展实现
 *      对应原来程序中使用 mcrypt 扩展实现的 des 加密
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Des
{
    /**
     * 密钥
     *
     * @var string
     */
    protected $key;

    /**
     * 加密算法
     *
     * @see http://cn2.php.net/manual/en/function.openssl-get-cipher-methods.php
     * @var string
     */
    protected static $cipher = 'des-cbc';

    /**
     * zero iv
     */
    const IV = "\0\0\0\0\0\0\0\0";

    /**
     * 构造函数
     *
     * @param string $key 秘钥，长度必须是8字节
     */
    public function __construct(string $key)
    {
        $actualBytes = mb_strlen($key, '8bit');
        if ($actualBytes !== 8) {
            $desc = "class Des needs a 8 bytes key, $actualBytes bytes key given!";
            throw new InvalidArgumentException($desc);
        }

        $this->key = $key;
    }

    /**
     * 加密文本 $plainText
     *
     * @param  string $plainText 明文
     * @return string|bool 成功返回密文，失败返回 false
     */
    public function encrypt(string $plainText)
    {
        $cipherText = openssl_encrypt(
            $plainText,
            self::$cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            self::IV
        );
        if ($cipherText === false) {
            return false;
        }

        return strtoupper(bin2hex($cipherText));
    }

    /**
     * 解密文本
     *
     * @param  string $cipherText 十六进制编码的密文
     * @return bool|string  成功返回明文，失败返回 false
     */
    public function decrypt(string $cipherText)
    {
        $cipherText = hex2bin($cipherText);
        return openssl_decrypt(
            $cipherText,
            self::$cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            self::IV
        );
    }
}
