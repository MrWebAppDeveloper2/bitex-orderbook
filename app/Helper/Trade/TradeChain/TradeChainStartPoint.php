<?php

namespace App\Helper\Trade\TradeChain;

use App\Contracts\TradeChainHandlerInterface;
use App\Exceptions\TradeException;
use App\Models\Offer;

class TradeChainStartPoint
{
    public $rings = [
        WhenBuyAmountHigherHandler::class,
        WhenSellAmountHigherHandler::class,
        WhenBuyAndSellAmountSameHandler::class,
    ];

    public TradeChainHandlerInterface $start;

    public function __construct()
    {
        $this->weaver();
    }

    public function instantiateRings():array
    {
        $instances = [];

        foreach($this->rings as $ring)
            $instances[] = app()->make($ring);

        return $instances;
    }

    public function weaver()
    {
        $instances = $this->instantiateRings();

        $start = null;

        $previous = null;

        foreach($instances as $instance){
            if(is_null($previous))
                $start = $previous = $instance;
            else{
                $previous->setNext($instance);

                $previous = $instance;
            }
        }

        $this->start = $start;
    }

    public function handle(Offer $buy, Offer $sell):bool
    {
        if($buy->id == $sell->id)
            throw new TradeException('Buy and sell offers which passed to trade chain has same id');

        return $this->start->handle($buy, $sell);
    }
}
