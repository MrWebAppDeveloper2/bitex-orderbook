<?php

namespace App\Concretes\Caching;

use App\Models\Offer;
use App\Models\Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Enums\Offer\OfferCacheListName;
use App\Events\BuyOffersCacheListUpdated;
use Dedoc\Scramble\Support\Generator\Types\NullType;

class BuyOffersCacheList
{
    // cache list will bind here
    private array $list;

    public function __construct(
        private Service $service)
    {}

    private function cacheListKeyName():string
    {
        return OfferCacheListName::BUY_CACHE_LIST->value . '.' . $this->service->id;
    }

    /**
     * Returns the buy type offers cache list
     *
     * @return array
     */
    public function all():array
    {
        if(!isset($this->list))
            $this->list = Cache::get($this->cacheListKeyName(), []);

        return $this->list;
    }

    /**
     * Extract the lowest price offer from the cache list then return
     *
     * returns null if the list is empty
     *
     * @return int|null
     */
    public function lowestPrice():int|null
    {
        $item = collect($this->all())
            ->sortBy('price')
            ->first();

        return $item ? $item['price'] : null;
    }

    /**
     * Tries to find an offer item that its price is equivalent with $price
     *
     * @param int $price
     * @return int|null Returns the item key if found otherwise returns null
     */
    public function findByPrice(int $price):int|null
    {
        foreach ($this->all() as $key => $item)
            if($item['price'] == $price)
                return $key;

        return null;
    }

    /**
     * Order entry list by price and descending
     *
     * @param array $list
     * @return array
     */
    private function reorderList(array $list):array
    {
        return array_values(
            collect($list)
            ->sortByDesc('price')
            ->take(Config::get('custom.offer.cache_list_length'))
            ->toArray()
        );
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
    public function push(Offer $item):void
    {
        $list = $this->all();

        $list[] = [
            'remaining_amount' => $item->remaining_amount,
            'price' => $item->price,
        ];

        $reorder = $this->reorderList($list);

        $this->update($reorder);
    }

    /**
     * Makes new item through sum amounts of all buy offers that has lower price than $loastPrice
     *
     * @param integer $loastPrice
     * @return array|null
     */
    public function inquireItemFromDb(int $loastPrice):array|null
    {
        $maxPriceAfterLoastPrice = Offer::buy()->where('price', '<', $loastPrice)->orderBy('price', 'DESC')->first();

        $sumAmount = Offer::buy()->where('price', $maxPriceAfterLoastPrice)->sum('remaining_amount');

        return ($sumAmount and $maxPriceAfterLoastPrice)?
            [
                'remaining_amount' => $sumAmount,
                'price' => $maxPriceAfterLoastPrice,
            ]:
            null;
    }

    /**
     * Find offer item in cache list and decrement its amount and add new item from database 
     * if item amount finished and was empty after minus amount
     *
     * @param integer $price
     * @param integer $decrement
     * @return void
     */
    public function decrementAmount(int $price, int $decrement):void
    {
        $list = $this->all();

        foreach ($list as $key => $item)
            if($item['price'] == $price){
                $item['remaining_amount'] -= $decrement;

                if($item['remaining_amount'] <= 0)
                    unset($list[$key]); 
                else
                    $list[$key] = $item;

                break;
            }

        $list = array_values($list);

        if(count($list) < config('custom.offer.cache_list_length') and count($list) > 0){
            if($item = $this->inquireItemFromDb($list[count($list) - 1]['price'])){
                $list[] = $item;

                $list = $this->reorderList($list);
            }
        }

        $this->update($list);
    }

    /**
     * Update buy type offers cache list and dispatch broadcast event
     *
     * @param array $list
     * @return void
     */
    public function update(array $list):void
    {
        Cache::set($this->cacheListKeyName(), $list);

        BuyOffersCacheListUpdated::dispatch($this->service, $list);
    }

}
