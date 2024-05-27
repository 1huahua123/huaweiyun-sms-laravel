<?php
/**
 * @Author: Ray
 * @Date: 2024/5/27 10:32
 * @Project: huaweiyun-sms-laravel
 * @Description: 华为云短信服务配置信息
 */
return [
    'endpoint' => env('HUAWEI_SMS_ENDPOINT', 'https://smsapi.cn-north-4.myhuaweicloud.com:443'),
    'app_key' => env('HUAWEI_SMS_APP_KEY'),
    'app_secret' => env('HUAWEI_SMS_APP_SECRET'),
    'sender' => env('HUAWEI_SMS_SENDER'),
    'signature' => env('HUAWEI_SMS_SIGNATURE'),
];
