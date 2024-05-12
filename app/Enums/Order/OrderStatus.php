<?php

namespace App\Enums\Order;

enum OrderStatus: int
{
    case OPEN = 0;

    case DONE = 1;
}
