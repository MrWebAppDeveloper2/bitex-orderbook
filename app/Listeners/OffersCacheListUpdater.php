<?php

namespace App\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Enums\Offer\OfferType;
use App\Events\OfferCreated;
use App\Models\Offer;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OffersCacheListUpdater
{
    public Offer $offer;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    private function getAtomLock()
    {
        $lockTime = config()->get('custom.offer.lock_time');

        $lockName = $this->offer->type == OfferType::BUY->value ?
            OfferAtomLockName::BUY_LOCK->value:
            OfferAtomLockName::SELL_LOCK->value;

        $lock = cache()->lock($lockName, $lockTime);

        $waitingTime = config()->get('custom.offer.waiting_time');

        try {

            $lock->block($waitingTime);

        } catch (LockTimeoutException $exception){

            $lock->forceRelease();

            $lock->get();

        } finally{
            return $lock;
        }

    }

    /**
     * Handle the event.
     */
    public function handle(OfferCreated $event): void
    {
        $this->offer = $event->offer;

        $lock = $this->getAtomLock();
    }
}
