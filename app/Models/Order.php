<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public $guarded = ['id'];

    public function items():HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function offer():HasOne
    {
        return $this->hasOne(Offer::class);
    }
}
