<?php

namespace App\Listeners;

use App\Concretes\Caching\BuyOffersCacheList;
use App\Concretes\Caching\SellOffersCacheList;
use App\Enums\Offer\OfferType;
use App\Events\OfferDeleted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateOfferCachedAmountWhenDeleted
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
    public function handle(OfferDeleted $event): void
    {
        $offer = $event->offer;

        $cachedAmount = Cache::put('offer.' . $offer->id, ['remaining_amount' => $offer->remaining_amount]);

        if($cachedAmount){
            if($offer->type == OfferType::BUY->value)
                $this->buyList->decrementAmount($offer->price, $cachedAmount);
            elseif($offer->type == OfferType::SELL->value)
                $this->sellList->decrementAmount($offer->price, $cachedAmount);
        }

        Cache::forget('offer.' . $offer->id);
    }
}
