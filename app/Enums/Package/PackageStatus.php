<?php

namespace App\Enums\Package;

enum PackageStatus: int
{
    case ENABLED = 1;

    case DISABLED = 0;
}
