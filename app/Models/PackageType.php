<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageType extends Model
{
    use HasFactory;

    public $guarded = ['id'];

    public function package():BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
