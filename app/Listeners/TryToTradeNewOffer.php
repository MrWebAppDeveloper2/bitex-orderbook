<?php

namespace App\Listeners;

use App\Enums\Offer\OfferType;
use App\Events\OfferCreated;
use App\Helper\Trade\TradeHelper;
use App\Models\Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        $offer = $event->offer;

        $this->tradeHelper->trade($offer);
    }
}
