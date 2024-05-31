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

    /**
     * Client can only decrement remaining amount in update offer. 
     * Other queries will exclude and update method will return false
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = []):bool
    {
        if(in_array('remaining_amount', array_keys($attributes)))
            if($attributes['remaining_amount'] <= $this->remaining_amount){
                $this->remaining_amount = $attributes['remaining_amount'];

                return $this->save();
            }

        return false;
    }

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
