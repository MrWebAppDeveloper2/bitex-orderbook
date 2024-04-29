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
use Illuminate\Support\Facades\Config;

class AddNewSellOfferToCacheList
{
// cache list will bind here
    private array $list;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Tries to get atomic lock then return
     *
     * If lock is not release wait for release and force
     * release if lock is not released after wait time
     *
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function atomicLock():mixed
    {
        $lockTime = config()->get('custom.offer.lock_time');

        $lock = cache()->lock(OfferAtomLockName::SELL_LOCK->value, $lockTime);

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
     * Returns the buy type offers cache list
     *
     * @return array
     */
    private function list():array
    {
        if(!isset($this->list))
            $this->list = Cache::get(OfferCacheListName::SELL_CACHE_LIST->value, []);

        return $this->list;
    }

    /**
     * Extract the highest price offer from the cache list then return
     *
     * returns null if the list is empty
     *
     * @return int|null
     */
    private function highestPrice():int|null
    {
        $item = collect($this->list())
            ->sortByDesc('price')
            ->first();

        return $item ? $item['price'] : null;
    }

    /**
     * Tries to find an offer item that its price is equivalent with $price
     *
     * @param int $price
     * @return int|null Returns the item key if found otherwise returns null
     */
    private function findByPrice(int $price):int|null
    {
        foreach ($this->list() as $key => $item)
            if($item['price'] == $price)
                return $key;

        return null;
    }

    /**
     * Push $item to cache list then dispatch broadcast event
     *
     * Also reorder the list according price then take items
     * according cache list length limitation that specified
     * in the config.
     *
     * @param Offer $item
     * @return void
     */
    private function push(Offer $item):void
    {
        $list = $this->list();

        $list[] = [
            'remaining_amount' => $item->remaining_amount,
            'price' => $item->price,
        ];

        $reorder = collect($list)
            ->sortBy('price')
            ->take(Config::get('custom.offer.cache_list_length'))
            ->toArray();

        $this->update($reorder);
    }

    /**
     * Update buy type offers cache list and dispatch broadcast event
     *
     * @param array $list
     * @return void
     */
    private function update(array $list):void
    {
        Cache::set(OfferCacheListName::SELL_CACHE_LIST->value, $list);

        SellOffersCacheListUpdated::dispatch();
    }

    /**
     * Handle the event.
     * @param OfferCreated $event
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle(OfferCreated $event): void
    {
        $offer = $event->offer;

        if($offer->type != OfferType::SELL->value)
            return;

        $lock = $this->atomicLock();

        $list = $this->list();

        if(empty($list))
            $this->push($offer);

        elseif($highestPrice = $this->highestPrice() and  $offer->price <= $highestPrice){
            // check is there any offer in cache that have same price with new offer and merge if there is
            if($key = $this->findByPrice($offer->price)){
                $list[$key]['remaining_amount'] += $offer->remaining_amount;

                $this->update($list);

                return;
            } else
                $this->push($offer);
        }

        $lock->release();
    }
}
