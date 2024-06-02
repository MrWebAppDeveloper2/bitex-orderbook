<?php

namespace App\Listeners;

use App\Enums\Offer\OfferType;
use Illuminate\Support\Facades\Cache;
use App\Events\OfferAmountDecremented;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Concretes\Caching\BuyOffersCacheList;
use App\Concretes\Caching\SellOffersCacheList;
use App\Models\Service;
use Illuminate\Support\Facades\Log;

class UpdateOfferCachedAmountWhenDecremented
{
    private BuyOffersCacheList $buyList;

    private SellOffersCacheList $sellList;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * BuyOffersCacheList instance method factory
     *
     * @param Service $service
     * @return BuyOffersCacheList
     */
    private function buyOffersCacheList(Service $service):BuyOffersCacheList
    {
        if(!isset($this->buyList))
            $this->buyList = app()->makeWith(BuyOffersCacheList::class, ['service' => $service]);

        return $this->buyList;
    }

    /**
     * SellOffersCacheList instance method factory
     *
     * @param Service $service
     * @return SellOffersCacheList
     */
    private function sellOffersCacheList(Service $service):SellOffersCacheList
    {
        if(!isset($this->sellList))
            $this->sellList = app()->makeWith(SellOffersCacheList::class, ['service' => $service]);

        return $this->sellList;
    }

    /**
     * Handle the event.
     */
    public function handle(OfferAmountDecremented $event): void
    {
        $offer = $event->offer;

        $cache = Cache::get('offer.' . $offer->id);

        if($cache){
            $decrementAmount = $cache['remaining_amount'] - $offer->remaining_amount;

            if($offer->type == OfferType::BUY->value)
                $this->buyOffersCacheList($offer->service)->decrementAmount($offer->price, $decrementAmount);
            elseif($offer->type == OfferType::SELL->value)
                $this->sellOffersCacheList($offer->service)->decrementAmount($offer->price, $decrementAmount);

            Cache::put('offer.' . $offer->id, ['remaining_amount' => $offer->remaining_amount]);
        } else 
            $offer->delete();
    }
}
