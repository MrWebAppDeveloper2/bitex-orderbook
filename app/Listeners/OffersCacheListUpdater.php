<?php

namespace App\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Enums\Offer\OfferType;
use App\Events\OfferCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OffersCacheListUpdater
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OfferCreated $event): void
    {
        $lockTime = config()->get('custom.offer.lock_time');

        $event->offer->type == OfferType::BUY->value ?
            cache()->lock(OfferAtomLockName::BUY_LOCK->value, $lockTime):
            cache()->lock(OfferAtomLockName::SELL_LOCK->value, $lockTime);
    }
}
