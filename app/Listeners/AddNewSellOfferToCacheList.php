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

    private function getCacheList():array
    {
        return Cache::get(OfferCacheListName::SELL_CACHE_LIST->value, []);
    }

    private function updateCache(array $list):void
    {
        Cache::set(OfferCacheListName::SELL_CACHE_LIST->value, $list);

        SellOffersCacheListUpdated::dispatch();
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

        $list = $this->getCacheList();

        if (empty($list) || count($list) < config()->get('custom.offer.cache_list_length')) {

            $list[] = [
                'remaining_amount' => $this->offer->remaining_amount,
                'price' => $this->offer->price,
            ];

            $this->updateCache($list);

            return;
        }

        $highestPrice = collect($list)
            ->sortByDesc('price')
            ->first()['price'];

        if($this->offer->price > $highestPrice)
            return;

        // check is there any offer in cache that have same price with new offer and merge if there is
        foreach ($list as $key => $element){
            if($element['price'] == $this->offer->price){
                $list[$key]['remaining_amount'] += $this->offer->remaining_amount;

                $this->updateCache($list);

                return;
            }
        }

        $list[] = [
            'remaining_amount' => $this->offer->remaining_amount,
            'price' => $this->offer->price,
        ];

        $this->updateCache(collect($list)
            ->sortBy('price')
            ->take(config()->get('custom.offer.cache_list_length'))
            ->toArray());

        return;
    }
}
