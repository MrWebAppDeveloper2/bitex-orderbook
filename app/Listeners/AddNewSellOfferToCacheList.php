<?php

namespace App\Listeners;

use App\Enums\Offer\OfferAtomLockName;
use App\Enums\Offer\OfferCacheListName;
use App\Enums\Offer\OfferType;
use App\Events\OfferCreated;
use App\Events\SellOffersCacheListUpdated;
use App\Exceptions\InvalidOfferTypeException;
use App\Models\Offer;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class AddNewSellOfferToCacheList
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

        $waitingTime = config()->get('custom.offer.waiting_time');

        $lock = cache()->lock(OfferAtomLockName::SELL_LOCK->value, $lockTime);

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

        if($this->offer->type != OfferType::SELL->value)
            return;

        $lock = $this->getAtomLock();

        $listName = OfferCacheListName::SELL_CACHE_LIST->value;

        $list = Cache::get(OfferCacheListName::SELL_CACHE_LIST->value, []);

        if (empty($list) || count($list) < config()->get('custom.offer.cache_list_length')) {
            $list[] = [
                'remaining_amount' => $this->offer->remaining_amount,
                'price' => $this->offer->price,
            ];

            Cache::set($listName, $list);

            SellOffersCacheListUpdated::dispatch();
        }

        $highestPrice = collect($list)
            ->sortByDesc('price')
            ->first()['price'];

        if($this->offer->price > $highestPrice)
            return;
    }
}
