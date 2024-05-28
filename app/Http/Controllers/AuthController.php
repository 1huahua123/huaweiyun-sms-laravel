<?php
/**
 * @Author: Ray
 * @Date: 2024/5/28 09:19
 * @Project: huaweiyun-sms-laravel
 * @Description: 登录具体操作
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\HuaweiSmsService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Random\RandomException;

class AuthController extends Controller
{
    protected HuaweiSmsService $smsService;

    public function __construct(HuaweiSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * 发送验证码
     *
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     * @throws RandomException
     */
    public function sendCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/^1[3456789]\d{9}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $phone = $request->phone;

        $lastCode = VerificationCode::where('phone', $phone)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastCode && $lastCode->created_at->diffInSeconds(Carbon::now()) < 60) {
            return response()->json(['error' => '请等待60秒后再发送验证码'], 429);
        }

        $code = rand(100000, 999999);

        // TODO 发送验证码服务
        if (!$this->smsService->sendSms($phone, $code)) {
            return response()->json(['error' => '验证码发送失败，请稍后再试'], 500);
        }

        VerificationCode::create([
            'phone' => $phone,
            'code' => $code,
            'created_at' => Carbon::now()
        ]);

        return response()->json(['message' => '验证码已发送']);
    }

    /**
     * 登录
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/^1[3456789]\d{9}$/',
            'code' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $phone = $request->phone;
        $code = $request->code;

        $verificationCode = VerificationCode::where('phone', $phone)
            ->where('code', $code)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->first();

        if (!$verificationCode) {
            return response()->json(['error' => '验证码无效或已过期'], 400);
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $user = User::create([
                'phone' => $phone,
                'username' => 'user_' . Str::random(8),
                'token' => Str::random(60)
            ]);
        } else {
            $user->update([
                'token' => Str::random(60)
            ]);
        }

        return response()->json(['token' => $user->token, 'user' => $user]);
    }
}
