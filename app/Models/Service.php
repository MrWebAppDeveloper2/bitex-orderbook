<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    public $guarded = ['id'];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}
