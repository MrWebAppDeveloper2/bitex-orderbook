<?php

namespace App\Models;

use App\Enums\Offer\OfferType;
use App\Events\OfferAmountDecremented;
use App\Events\OfferCreated;
use App\Events\OfferDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offer extends Model
{
    use HasFactory;

    protected $dispatchesEvents = [
        'created' => OfferCreated::class,
        'updated' => OfferAmountDecremented::class,
        'deleted' => OfferDeleted::class,
    ];

    public $guarded = ['id'];

    public function scopeBuy($query):void
    {
        $query->where('type', OfferType::BUY->value);
    }

    public function scopeSell($query):void
    {
        $query->where('type', OfferType::SELL->value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function history(): BelongsTo
    {
        return $this->belongsTo(OfferHistory::class);
    }

    /**
     * Get the offer's total value that equal to amount * price.
     */
    protected function totalValue(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => ($this->price * $this->remaining_amount),
        );
    }
}
