<?php

namespace App\Listeners;

use App\Concretes\Caching\BuyOffersCacheList;
use App\Enums\Offer\OfferAtomLockName;
use App\Enums\Offer\OfferCacheListName;
use App\Enums\Offer\OfferType;
use App\Events\BuyOffersCacheListUpdated;
use App\Events\OfferCreated;
use App\Exceptions\InvalidOfferTypeException;
use App\Models\Offer;
use App\Models\Service;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AddNewBuyOfferToCacheList implements ShouldQueue
{
    /**
     * Tries to get atomic lock then return
     *
     * If lock is not release wait for release and force
     * release if lock is not released after wait time
     *@param Service $service which service lock 
     * 
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function atomicLock(Service $service):mixed
    {
        $lockTime = config()->get('custom.offer.lock_time');

        $lock = cache()->lock(OfferAtomLockName::BUY_LOCK->value . '.' . $service->id, $lockTime);

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
     * @param OfferCreated $event
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle(OfferCreated $event): void
    {
        $offer = $event->offer;

        if($offer->type != OfferType::BUY->value)
            return;

        $lock = $this->atomicLock($offer->service);

        $cacheList = app()->makeWith(BuyOffersCacheList::class, ['service' => $offer->service]);

        $list = $cacheList->all();

        if(empty($list))
            $cacheList->push($offer);

        elseif(($lowestPrice = $cacheList->lowestPrice() and  $offer->price >= $lowestPrice) or count($list) < config('custom.offer.cache_list_length')){
            // check is there any offer in cache that have same price with new offer and merge if there is
            if(($key = $cacheList->findByPrice($offer->price)) !== null){
                $list[$key]['remaining_amount'] += $offer->remaining_amount;

                $cacheList->update($list);

                return;
            } else
                $cacheList->push($offer);
        }

        $lock->release();

        Cache::lock('new-offer.' . $event->offer->id)->forceRelease();
    }
}
