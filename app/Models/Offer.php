<?php

namespace App\Models;

use App\Events\OfferCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $dispatchesEvents = [
        'created' => OfferCreated::class
    ];

    public $guarded = ['id'];

    public function order():BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
