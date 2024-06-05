<?php

namespace App\Listeners;

use App\Models\Service;
use App\Events\OfferCreated;
use App\Enums\Offer\OfferType;
use App\Helper\Trade\TradeHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TryToTradeNewOffer implements ShouldQueue
{
    public $queue = 'trade';

    /**
     * Create the event listener.
     */
    public function __construct(
        private TradeHelper $tradeHelper
    )
    {
        //
    }  

    /**
     * Handle the event.
     */
    public function handle(OfferCreated $event): void
    {
        Cache::lock('new-offer.' . $event->offer->id)->block(5, function() use ($event){
            $offer = $event->offer;

            $this->tradeHelper->trade($offer);
        });
    }
}
