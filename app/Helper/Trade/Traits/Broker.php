<?php

namespace App\Helper\Trade\Traits;

use App\Models\Offer;
use App\Models\Service;

trait Broker
{
    private function findSellOfferWithEqualOrCheaperPrice(Service $service, int $price):Offer|null
    {
        return $service->offers()->sell()->where('price', '<=', $price)->orderBy('created_at', 'ASC')->first();
    } 

    private function findBuyOfferWithEqualOrHigherPrice(Service $service, int $price):Offer|null
    {
        return $service->offers()->buy()->where('price', '>=', $price)->orderBy('created_at', 'ASC')->first();
    } 
}
