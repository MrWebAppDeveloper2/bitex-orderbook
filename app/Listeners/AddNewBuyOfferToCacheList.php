<?php

namespace App\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Enums\Offer\OfferCacheListName;
use App\Enums\Offer\OfferType;
use App\Events\BuyOffersCacheListUpdated;
use App\Events\OfferCreated;
use App\Exceptions\InvalidOfferTypeException;
use App\Models\Offer;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class AddNewBuyOfferToCacheList
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

        $lock = cache()->lock(OfferAtomLockName::BUY_LOCK->value, $lockTime);

        $waitingTime = config()->get('custom.offer.waiting_time');

        try {

            $lock->block($waitingTime);

        } catch (LockTimeoutException $exception) {

            $lock->forceRelease();

            $lock->get();

        } finally {
            return $lock;
        }

    }

    /**
     * Handle the event.
     * @throws InvalidOfferTypeException
     */
    public function handle(OfferCreated $event): void
    {
        $this->offer = $event->offer;

        if($this->offer->type != OfferType::BUY->value)
            return;

        $lock = $this->getAtomLock();

        $listName = OfferCacheListName::BUY_CACHE_LIST->value;

        $list = Cache::get(OfferCacheListName::BUY_CACHE_LIST->value, []);

        if (empty($list)) {
            $list[] = [
                'remaining_amount' => $this->offer->remaining_amount,
                'price' => $this->offer->price,
            ];

            Cache::set($listName, $list);

            BuyOffersCacheListUpdated::dispatch();

            return;
        }

        $lowestPrice = collect($list)
            ->sortBy('price')
            ->first()['price'];

        if($this->offer->price < $lowestPrice)
            return;
    }
}
