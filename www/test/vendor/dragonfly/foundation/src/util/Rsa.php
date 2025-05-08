<?php
namespace Dragonfly\foundation\util;

/**
 * Class Rsa
 * @package erp\util
 */
class Rsa
{
    /**
     * 公钥
     * @var string
     */
    private $publicKey;

    /**
     * 私钥
     * @var string
     */
    private $privateKey;

    /**
     * Rsa constructor.
     * @param array $keyData
     */
    public function __construct(array $keyData)
    {
        $this->publicKey = array_key_exists('public_key', $keyData) ? $keyData['public_key'] : '';
        $this->privateKey = array_key_exists('private_key', $keyData) ? $keyData['private_key'] : '';
    }

    /**
     * 签名（私钥）
     * @param mixed $signData
     * @param string $signType
     * @return mixed
     */
    public function sign($signData, $signType = 'RSA2')
    {
        $privateKey = $this->getPrivateKey();

        if ($signType == 'RSA2') {
            openssl_sign($signData, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($signData, $sign, $privateKey);
        }

        return $sign;
    }

    /**
     * 原数据
     * @param mixed $signData
     *
     * 私钥加密后的数据
     * @param mixed $binarySignature
     *
     * 加密方式
     * @param string $signType
     *
     * @return boolean
     */
    public function verify($signData, $binarySignature, $signType = 'RSA2')
    {
        $publicKey = $this->getPublicKey();

        if ($signType == 'RSA2') {
            $result = openssl_verify($signData, $binarySignature, $publicKey, OPENSSL_ALGO_SHA256);
        } else {
            $result = openssl_verify($signData, $binarySignature, $publicKey);
        }

        return $result == 1;
    }

    /**
     * @return string
     */
    private function getPrivateKey()
    {
        $privateKey = $this->trimKey($this->privateKey);

        $res = "-----BEGIN RSA PRIVATE KEY-----\n";
        $res .= wordwrap($privateKey, 64, "\n", true) . "\n";
        $res .= "-----END RSA PRIVATE KEY-----";

        return $res;
    }

    /**
     * @return string
     */
    private function getPublicKey()
    {
        $publicKey = $this->trimKey($this->publicKey);

        $res = "-----BEGIN PUBLIC KEY-----\n";
        $res .= wordwrap($publicKey, 64, "\n", true) . "\n";
        $res .= "-----END PUBLIC KEY-----";

        return $res;
    }

    /**
     * @param $key
     * @return mixed
     */
    private function trimKey($key)
    {
        $key = str_replace("\r", '', $key);
        $key = str_replace("\n", '', $key);
        $key = str_replace(" ", '', $key);

        return $key;
    }
}
