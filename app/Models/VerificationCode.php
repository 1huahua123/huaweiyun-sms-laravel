<?php
/**
 * @Author: Ray
 * @Date: 2024/5/28 09:16
 * @Project: huaweiyun-sms-laravel
 * @Description: 验证码存储
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'phone',
        'code',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = str_replace('-', '',(string) Str::uuid());
            }
        });
    }

    public $timestamps = false;

    protected array $dates = ['created_at'];
}
