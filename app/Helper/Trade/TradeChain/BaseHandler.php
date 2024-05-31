<?php

namespace App\Helper\Trade\TradeChain;

use App\Models\Offer;
use App\Contracts\TradeChainHandlerInterface;

abstract class BaseHandler
{
    public TradeChainHandlerInterface $next;

    public function setNext(TradeChainHandlerInterface $next)
    {
        $this->next = $next;
    }

    protected function next(Offer $buy, Offer $sell):bool
    {
        if(isset($this->next))
            return $this->next->handle($buy, $sell);

        return false;
    }
}
