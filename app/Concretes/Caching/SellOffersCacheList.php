<?php

namespace App\Concretes\Caching;

use App\Enums\Offer\OfferCacheListName;
use App\Events\SellOffersCacheListUpdated;
use App\Models\Offer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class SellOffersCacheList
{
    // cache list will bind here
    private array $list;

    /**
     * Returns the buy type offers cache list
     *
     * @return array
     */
    public function all():array
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
    public function highestPrice():int|null
    {
        $item = collect($this->all())
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
    public function findByPrice(int $price):int|null
    {
        foreach ($this->all() as $key => $item)
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
    public function push(Offer $item):void
    {
        $list = $this->all();

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
    public function update(array $list):void
    {
        Cache::set(OfferCacheListName::SELL_CACHE_LIST->value, $list);

        SellOffersCacheListUpdated::dispatch($list);
    }
}
