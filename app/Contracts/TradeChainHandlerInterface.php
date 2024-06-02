<?php

namespace App\Contracts;

use App\Models\Offer;

interface TradeChainHandlerInterface
{
    public function setNext(TradeChainHandlerInterface $next);

    public function handle(Offer $buy, Offer $sell):bool;
}
