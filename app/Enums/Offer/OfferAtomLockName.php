<?php

namespace App\Enums\Offer;

enum OfferAtomLockName: string
{
    case BUY_LOCK = 'buy-type-offer-lock';

    case SELL_LOCK = 'sell-type-offer-lock';
}
