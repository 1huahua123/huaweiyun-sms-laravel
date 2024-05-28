<?php
/**
 * @Author: Ray
 * @Date: 2024/5/27 10:46
 * @Project: huaweiyun-sms-laravel
 * @Description: 华为云短信服务控制器
 */

namespace App\Http\Controllers;

use App\Services\HuaweiSmsService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Random\RandomException;

class SmsController extends Controller
{
    protected HuaweiSmsService $smsService;

    public function __construct(HuaweiSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * 发送短信验证码
     *
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException|RandomException
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $request->validate(['phone_number' => 'required|regex:/^[0-9]{11}$/']);

        $phoneNumber = $request->input('phone_number');

        $cacheKey = 'sms_sent_' . $phoneNumber;
        if (Cache::has($cacheKey)) {
            $secondsRemaining = Cache::get($cacheKey) - time();
            return response()->json(['message' => 'Please wait' . $secondsRemaining . ' seconds before requesting a new code'], 429);
        }

        $verificationCode = rand(100000, 999999);

        session(['verificationCode' => $verificationCode]);

        $message = "Your verification code is: $verificationCode";

        $this->smsService->sendSms($phoneNumber, $message);

        Cache::put($cacheKey, time() + 60, 60);

        return response()->json(['message' => 'Verification code sent successfully']);
    }

    /**
     * 验证短信验证码
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|regex:/^[0-9]{11}$/',
            'verification_code' => 'required|digits:6'
        ]);

        $verificationCode = $request->input('verification_code');

        $storedCode = session('verificationCode');
        if ($verificationCode == $storedCode) {
            return response()->json(['message' => 'Verification successful']);
        } else {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }
    }
}
