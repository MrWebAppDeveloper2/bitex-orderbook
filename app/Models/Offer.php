<?php

namespace App\Models;

use App\Events\OfferCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offer extends Model
{
    use HasFactory;

    protected $dispatchesEvents = [
        'created' => OfferCreated::class
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
