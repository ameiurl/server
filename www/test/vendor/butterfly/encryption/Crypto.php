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
 * Crypto - 加密/解密
 *     ref.
 *     https://paragonie.com/blog/2015/05/if-you-re-typing-word-mcrypt-into-your-code-you-re-doing-it-wrong
 *
 * 使用示例：
 *
 * ```php
 * // 密钥，由32个字节组成(256bit)，请**不**要使用此示例密钥作为实际使用的密钥
 * $key    = '5m3jkwLJyVn79Bv9hueXgzmQHH8fhb3n';
 * $crypto = new \Butterfly\Encryption\Crypto($key);
 *
 * // 加密
 * $encryptedText = $crypto->encrypt('text_to_encrypt');
 * if ($encryptedText === false) {
 *     // 加密失败
 * } else {
 *     // 加密成功
 * }
 *
 * // 解密
 * $plainText = $crypto->decrypt($encryptedText);
 * if ($plainText === false) {
 *     // 解密失败
 * } else {
 *     // 解密成功
 * }
 * ```
 *
 * 系统要求：
 * - 需启用的扩展：
 *     + mb_string 扩展
 *     + openssl 扩展，mcrypt 扩展存在较多问题，故不使用
 * - 不使用 mcrypt
 * 的原因详见：<http://cn2.php.net/manual/en/function.mcrypt-encrypt.php#117667>
 */
class Crypto
{
    /**
     * 密钥
     *
     * @var string
     */
    protected $key;

    /**
     * 初始向量的字节数
     *
     * @var int
     */
    protected $ivSize;

    /**
     * 加密算法
     *
     * @see http://cn2.php.net/manual/en/function.openssl-get-cipher-methods.php
     * @var string
     */
    protected static $cipher = 'aes-256-cbc';

    /**
     * 构造函数
     *
     * @param string $key    必须参数，密钥，由字节组成
     * @param string $cipher 可选参数，加密算法
     * @throws InvalidArgumentException 密钥长度错误时，抛出该异常
     */
    public function __construct($key, $cipher = 'aes-256-cbc')
    {
        // http://cn2.php.net/manual/en/function.mb-strlen.php#77040
        // If you need length of string in bytes (strlen cannot be trusted
        // anymore because of mbstring.func_overload) you should use
        // `<?php mb_strlen($string, '8bit'); ?\>.
        $bytes       = self::keyLength();
        $actualBytes = mb_strlen($key, '8bit');
        if ($actualBytes !== $bytes) {
            $desc = "class Crypto needs a $bytes bytes key, $actualBytes bytes key given!";
            throw new InvalidArgumentException($desc);
        }

        $this->key    = $key;
        $this->ivSize = openssl_cipher_iv_length(self::$cipher);
        self::$cipher = $cipher;
    }

    /**
     * 加密文本 $plainText
     *
     * @param  string $plainText 明文
     * @return string|bool 成功返回密文，失败返回 false
     */
    public function encrypt($plainText)
    {
        $iv         = random_bytes($this->ivSize);
        $cipherText = openssl_encrypt(
            $plainText,
            self::$cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($cipherText === false) {
            return false;
        } else {
            return base64_encode($iv . $cipherText);
        }
    }

    /**
     * 解密文本
     *
     * @param  string $cipherTextBase64 包含初始向量的 Base64 编码的密文
     * @return bool|string  成功返回明文，失败返回 false
     */
    public function decrypt($cipherTextBase64)
    {
        $cipherText = base64_decode($cipherTextBase64);
        $iv         = mb_substr($cipherText, 0, $this->ivSize, '8bit');
        $cipherText = mb_substr($cipherText, $this->ivSize, null, '8bit');

        return openssl_decrypt(
            $cipherText,
            self::$cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    /**
     * 密钥长度（字节）
     *
     * @return int 字节数
     */
    protected static function keyLength() : int
    {
        return 32;
        // list(, $bits, ) = sscanf(self::$cipher, '%s-%d-%s');  // 获取密钥长度
        // return $bits / 8;
    }

    /**
     * 创建加密、解密用的密钥
     *
     * @return string
     * @throws \Exception
     */
    public static function createKey() : string
    {
        $bytes = self::keyLength();
        return random_bytes($bytes);
    }
}
