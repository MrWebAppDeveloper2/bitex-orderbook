<?php

namespace App\Models;

use App\Events\OfferHistoryCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OfferHistory extends Model
{
    use HasFactory;

    protected $dispatchesEvents = [
        'created' => OfferHistoryCreated::class
    ];

    public $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function offer(): HasOne
    {
        return $this->hasOne(Offer::class, 'history_id');
    }

        /**
     * Get the offer's total value that equal to amount * price.
     */
    protected function totalValue(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => ($this->price * $this->amount),
        );
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }
}
