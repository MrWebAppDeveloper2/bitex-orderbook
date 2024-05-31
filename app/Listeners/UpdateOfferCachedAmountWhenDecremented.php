<?php

namespace App\Listeners;

use App\Enums\Offer\OfferType;
use Illuminate\Support\Facades\Cache;
use App\Events\OfferAmountDecremented;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Concretes\Caching\BuyOffersCacheList;
use App\Concretes\Caching\SellOffersCacheList;

class UpdateOfferCachedAmountWhenDecremented
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private BuyOffersCacheList $buyList,
        private SellOffersCacheList $sellList,
    )
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OfferAmountDecremented $event): void
    {
        $offer = $event->offer;

        $cachedAmount = Cache::put('offer.' . $offer->id, ['remaining_amount' => $offer->remaining_amount]);

        if($cachedAmount){
            $decrementAmount = $cachedAmount - $offer->remaining_amount;

            if($offer->type == OfferType::BUY->value)
                $this->buyList->decrementAmount($offer->price, $decrementAmount);
            elseif($offer->type == OfferType::SELL->value)
                $this->sellList->decrementAmount($offer->price, $decrementAmount);

            $cachedAmount = Cache::put('offer.' . $offer->id, ['remaining_amount' => $offer->remaining_amount]);
        } else 
            $offer->delete();
    }
}
