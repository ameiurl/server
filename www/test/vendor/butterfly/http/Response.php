<?php
/**
 * Butterfly : Non-business component library
 *
 * @copyright Copyright (c) 2016 CNCN Information Co., Ltd.
 * All rights reserved.
 */

namespace Butterfly\Http;

use InvalidArgumentException;

class Response extends \Zend\Diactoros\Response
{
    /**
     * Add `ETag` header to PSR7 response object (copy from slimphp, and modified)
     *
     * @param  string $value The ETag value
     * @param  string $type  ETag type: "strong" or "weak"
     *
     * @return \Psr\Http\Message\ResponseInterface           A new PSR7
     *                                                       response object
     *                                                       with `ETag` header
     * @throws \InvalidArgumentException if the etag type is invalid
     */
    public function withEtag($value, $type = 'strong')
    {
        if (!in_array($type, ['strong', 'weak'])) {
            throw new InvalidArgumentException('Invalid etag type. Must be "strong" or "weak".');
        }
        $value = '"' . $value . '"';
        if ($type === 'weak') {
            $value = 'W/' . $value;
        }

        return $this->withHeader('ETag', $value);
    }

    /**
     * 获取用于 json_encode 的设置项
     *
     * @return int
     */
    protected function jsonOptions()
    {
        return getenv('APP_ENV') === 'prod' ?
            0 : JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
    }

    /**
     * 获取成功信息的HTTP响应
     *
     * @param  mixed $data       成功响应
     * @param  int   $httpStatus HTTP status code
     * @return Response
     */
    public function success($data = null, int $httpStatus = 200) : Response
    {
        $result = [
            'code' => 0,
        ];

        if ($data !== null && $data != []) {
            $result['data'] = $data;
            $res            = $this->withEtag(md5(serialize($result['data'])));
        } else {
            $res = $this;
        }

        return $res->withJson($result, $httpStatus, $this->jsonOptions());
    }

    /**
     * 获取失败信息的HTTP响应
     *
     * @param  int    $status     返回状态
     * @param  string $message    错误信息
     * @param  int    $httpStatus HTTP status code
     * @return Response
     */
    public function error(
        int $status,
        string $message,
        int $httpStatus = 491
    ) : Response
    {
        $result = [
            'code' => $status,
            'msg'  => $message,
        ];

        return $this->withJson($result, $httpStatus, $this->jsonOptions());
    }

    /**
     * 获取要输出的成功或失败信息的HTTP响应
     *
     * @param  array $result 成功响应
     * @return Response
     */
    public function output(array $result) : Response
    {
        if ($result[0] === 0) {
            return $this->success($result[1] ?? null);
        } else {
            return $this->error($result[0], $result[1] ?? '');
        }
    }

    /**
     * 获取 OAuth 的　HTTP　响应
     *
     * @param  array $result 成功响应
     * @return Response
     */
    public function authorizeOutput(array $result) : Response
    {
        if (isset($result['error'])) {
            $httpStatus = 490;
            $res        = $this;
        } else {
            $httpStatus = 200;
            $res        = $this->withHeader('Cache-Control', 'no-store')
                               ->withHeader('Pragma', 'no-cache');
        }

        return $res->withJson($result, $httpStatus, $this->jsonOptions());
    }

    /**
     * Json. (copy from slimphp)
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Json
     * response to the client.
     *
     * @param  mixed  $data   The data
     * @param  int    $status The HTTP status code.
     * @param  int    $encodingOptions Json encoding options
     * @throws \RuntimeException
     * @return self
     */
    public function withJson($data, $status = null, $encodingOptions = 0)
    {
        $body = $this->getBody();
        $body->rewind();
        $body->write($json = json_encode($data, $encodingOptions));

        // Ensure that the json encoding passed successfully
        if ($json === false) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        $responseWithJson = $this->withHeader('Content-Type', 'application/json;charset=utf-8');
        if (isset($status)) {
            return $responseWithJson->withStatus($status);
        }
        return $responseWithJson;
    }

    /**
     * 往 HTTP Body 写数据的简便方法
     *
     * @param string $data
     * @return Response
     */
    public function write($data)
    {
        $this->getBody()->write($data);

        return $this;
    }

    /**
     * 转换 HTTP 响应为字符串
     *
     * @return string
     */
    public function __toString()
    {
        // HTTP 状态
        $reasonPhrase = $this->getReasonPhrase();
        $output = sprintf(
            'HTTP/%s %d%s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $reasonPhrase ? ' ' . $reasonPhrase : ''
        );
        $output .= PHP_EOL;

        // HTTP 响应头
        foreach ($this->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $output .= sprintf('%s: %s', $name, $value) . PHP_EOL;
            }
        }
        $output .= PHP_EOL;

        // HTTP Body
        $output .= (string) $this->getBody();

        return $output;
    }
}
