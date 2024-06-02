<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    use HasFactory;

    public $guarded = ['id'];

    public function buyOffer(): BelongsTo
    {
        return $this->belongsTo(OfferHistory::class);
    }

    public function sellOffer(): BelongsTo
    {
        return $this->belongsTo(OfferHistory::class);
    }
}
