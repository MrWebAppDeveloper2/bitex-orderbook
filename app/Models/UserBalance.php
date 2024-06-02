<?php

namespace App\Models;

use App\Models\Scopes\UserBalanceScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy(UserBalanceScope::class)]
class UserBalance extends Model
{
    use HasFactory;

    public $guarded = ['id'];

    public $table = 'user_balance';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): Service|null
    {
        return Service::where('key', 'service_key')->first();
    }
}
