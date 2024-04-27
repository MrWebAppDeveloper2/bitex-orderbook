<?php

namespace App\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Enums\Offer\OfferCacheListName;
use App\Enums\Offer\OfferType;
use App\Events\OfferCreated;
use App\Models\Offer;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

        $listName = $this->offer->type == OfferType::BUY->value?
            OfferCacheListName::BUY_CACHE_LIST->value:
            OfferCacheListName::SELL_CACHE_LIST->value;

        $list = Cache::get($listName, new Collection());

        if($list->isEmpty()){
            $list->add($this->offer);

            Cache::set($listName, $list);
        }
    }
}
