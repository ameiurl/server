<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Mail;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Mail
 *
 * 使用示例：
 * ```php
 * $mail = new Mail();
 * $result = $mail
 *     ->from('example@163.com', '示例用户名')
 *     ->to($user['email'])  // 可在第二个参数指定邮件账号名
 *     // ->to([$email1 => $name1, $email2 => $name2, ...])  // 方式2
 *     ->subject('邮件主题')
 *     ->body('邮件内容')
 *     ->send();
 * if ($result === true) {
 *     echo '发送成功';
 * } else {
 *     echo '发送失败';
 * }
 * ```
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  1.0
 */
class Mail
{
    /**
     * PHPMailer 实例
     *
     * @var PHPMailer
     */
    protected $mailer = null;

    public function __construct(array $config)
    {
        $this->mailer            = new PHPMailer();
        $this->mailer->CharSet   = $config['charset'] ?? 'UTF-8';
        $this->mailer->SMTPDebug = $config['debug'] ?? false;
        $this->mailer->isSMTP();                           // Set mailer to use SMTP
        $this->mailer->Host       = $config['host'];       // Specify main and backup server
        $this->mailer->SMTPAuth   = true;                  // Enable SMTP authentication
        $this->mailer->Username   = $config['username'];   // SMTP username
        $this->mailer->Password   = $config['password'];   // SMTP password
        $this->mailer->Port       = $config['port'];       // SMTP port
        $this->mailer->SMTPSecure = $config['encryption']; // Enable encryption, 'ssl' also accepted
    }

    /**
     * 设置邮件主题
     *
     * @param  string $subject
     * @return Mail
     */
    public function subject(string $subject)
    {
        $this->mailer->Subject = $subject;

        return $this;
    }

    /**
     * 设置邮件信息为 html 格式
     *
     * @param  bool $useHtml
     * @return Mail
     */
    public function html(bool $useHtml = true)
    {
        $this->mailer->isHTML($useHtml);

        return $this;
    }

    /**
     * 设置回复邮件地址
     *
     * @param  string $email
     * @param  string $name
     * @return Mail
     */
    public function reply(string $email, $name = null)
    {
        $this->mailer->addReplyTo($email, $name);

        return $this;
    }

    /**
     * 设置发件人邮件地址
     *
     * @param  string $email
     * @param  string $name
     * @return Mail
     */
    public function from(string $email, $name = null)
    {
        $this->mailer->From     = $email;
        $this->mailer->FromName = $name;

        return $this;
    }

    /**
     * 设置邮件发送地址
     *
     * @param  string|array $email
     * @param  string       $name
     * @return Mail
     */
    public function to($email, $name = null)
    {
        if (is_array($email)) {
            foreach ($email as $address => $name) {
                $this->mailer->addAddress($address, $name);
            }
        } else {
            $this->mailer->addAddress($email, $name);
        }

        return $this;
    }

    /**
     * 设置抄送邮件地址
     *
     * @param  string $email
     * @param  string $name
     * @return Mail
     */
    public function cc(string $email, $name = null)
    {
        $this->mailer->addCC($email, $name);

        return $this;
    }

    /**
     * 设置按抄送邮件地址
     *
     * @param  string $email
     * @param  string $name
     * @return Mail
     */
    public function bcc(string $email, $name = null)
    {
        $this->mailer->addBCC($email, $name);

        return $this;
    }

    /**
     * 设置邮件 body 部分
     *
     * @param  string $body
     * @return Mail
     */
    public function body(string $body)
    {
        $this->mailer->Body = $body;

        return $this;
    }

    /**
     * 添加附件
     *
     * @param  string $filePath
     * @param  string $fileName
     * @param  string $encoding
     * @param  string $mimeType
     * @return Mail
     */
    public function attach(
        string $filePath,
        $fileName = '',
        $encoding = 'base64',
        $mimeType = ''
    ) {
        $this->mailer->addAttachment($filePath, $fileName, $encoding,
            $mimeType);

        return $this;
    }

    /**
     * 自定义邮件的 HTTP header
     *
     * @param  string $header
     * @param  string $value
     * @return Mail
     */
    public function header(string $header, $value = null)
    {
        $this->mailer->addCustomHeader($header, $value);

        return $this;
    }

    /**
     * 发送邮件
     *
     * @return bool 成功或失败
     */
    public function send()
    {
        return $this->mailer->send();
    }

    /**
     * 错误信息
     *
     * @return string
     */
    public function errorInfo()
    {
        return $this->mailer->ErrorInfo;
    }

    /**
     * 方法不存在时调用底层 PHPMailer 的方法
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->mailer->$method(...$parameters);
    }

    /**
     * 属性不存在时获取底层 PHPMailer 的属性
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->mailer->$name;
    }

    /**
     * 属性不存在时设置底层 PHPMailer 的属性
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->mailer->$name = $value;
    }
}
