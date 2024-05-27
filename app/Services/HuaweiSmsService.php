<?php
/**
 * @Author: Ray
 * @Date: 2024/5/27 10:36
 * @Project: huaweiyun-sms-laravel
 * @Description: 华为云短信服务
 */

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class HuaweiSmsService
{
    protected Client $client;
    protected mixed $config;

    public function __construct()
    {
        $this->config = config('huaweisms');
        $this->client = new Client();
    }

    /**
     * 发送短信
     *
     * @param $phoneNumber
     * @param $message
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function sendSms($phoneNumber, $message): ResponseInterface
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $nonce = uniqid();
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'WSSE realm="SDP",profile="UsernameToken",type="Appkey"',
            'X-WSSE' => $this->buildWsseHeader($timestamp, $nonce),
        ];

        $body = [
            'form' => $this->config['sender'],
            'to' => $phoneNumber,
            'body' => $message,
            'signature' => $this->config['signature'],
        ];

        return $this->client->post($this->config['endpoint'], [
            'headers' => $headers,
            'form_params' => $body
        ]);
    }

    /**
     * 构建华为云短信服务所需的 WSSE 头部信息
     *
     * @param $timestamp
     * @param $nonce
     * @return string
     */
    private function buildWsseHeader($timestamp, $nonce): string
    {
        $appKey = $this->config['app_key'];
        $appSecret = $this->config['app_secret'];
        $digest = base64_encode(sha1($nonce . $timestamp . $appSecret, true));

        return sprintf(
            'UsernameToken Username="%s",PasswordDigest="%s",Nonce="%s",Created="%s"',
            $appKey,
            $digest,
            base64_encode($nonce),
            $timestamp
        );
    }
}
