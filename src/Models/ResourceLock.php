<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ResourceLock extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'locking_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'locking_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            if (is_null($model->locking_at)) {
                $model->locking_at = now();
            }
            if (is_null($model->expired_at)) {
                $model->expired_at = now()->addMinutes(config('resource-lock.lock_timeout', 10));
            }
        });
    }
    public function user(): BelongsTo
    {
            $userModel = config('moonshine.auth.providers.moonshine.model');

            return $this->belongsTo($userModel, 'user_id');
    }

    public function lockable(): MorphTo
    {
            return $this->morphTo();
    }

    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expired_at);
    }
}
