<?php

namespace App\Helper\Trade\Traits;

use App\Models\Offer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Log;

trait Broker
{
    private function findSellOfferWithEqualOrCheaperPrice(User $user, Service $service, int $price):Offer|null
    {
        return $service->offers()->sell()->where('price', '<=', $price)->where("user_id", '!=', $user->id)->orderBy('created_at', 'ASC')->first();
    } 

    private function findBuyOfferWithEqualOrHigherPrice(User $user, Service $service, int $price):Offer|null
    {
        return $service->offers()->buy()->where('price', '>=', $price)->where("user_id", '!=', $user->id)->orderBy('created_at', 'ASC')->first();
    } 
}
