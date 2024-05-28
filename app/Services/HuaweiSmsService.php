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
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Random\RandomException;

class HuaweiSmsService
{
    protected Client $client;
    protected mixed $endpoint;
    protected mixed $appKey;
    protected mixed $appSecret;
    protected mixed $sender;
    protected mixed $signature;

    public function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('HUAWEI_SMS_ENDPOINT');
        $this->appKey = env('HUAWEI_SMS_APP_KEY');
        $this->appSecret = env('HUAWEI_SMS_APP_SECRET');
        $this->sender = env('HUAWEI_SMS_SENDER');
        $this->signature = env('HUAWEI_SMS_SIGNATURE');
    }

    /**
     * 发送短信
     *
     * @param $phone
     * @param $code
     * @return bool|ResponseInterface
     * @throws GuzzleException
     * @throws RandomException
     */
    public function sendSms($phone, $code): bool|ResponseInterface
    {
        $url = $this->endpoint . '/v1/sms/message';
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'WSSE realm="SDP",profile="UsernameToken",type="Appkey"',
            'X-WSSE' => $this->buildWsseHeader(),
        ];

        $body = [
            'form' => $this->sender,
            'to' => $phone,
            'templatedId' => $this->signature,
            'templatedParas' => "[$code]",
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => $headers,
                'json' => $body,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode == 200 && isset($body['status']) && $body['status'] == '000000') {
                return true;
            } else {
                Log::error('SMS send failed', ['response' => $body]);
                return false;
            }
        } catch (\Exception $exception) {
            Log::error('SMS send error', ['error' => $exception->getMessage()]);
            return false;
        }
    }

    /**
     * 构建华为云短信服务所需的 WSSE 头部信息
     *
     * @return string
     * @throws RandomException
     */
    private function buildWsseHeader(): string
    {
        $nonce = bin2hex(random_bytes(16));
        $created = gmdate('Y-m-d\TH:i:s\Z');
        $digest = base64_encode(sha1($nonce . $created . $this->appSecret, true));

        return sprintf(
            'UsernameToken Username="%s",PasswordDigest="%s",Nonce="%s",Created="%s"',
            $this->appKey,
            $digest,
            base64_encode($nonce),
            $created
        );
    }
}
