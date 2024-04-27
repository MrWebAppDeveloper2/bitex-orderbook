<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Package extends Model
{
    use HasFactory;

    public function type():HasOne
    {
        return $this->hasOne(PackageType::class);
    }

    public function service():BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
