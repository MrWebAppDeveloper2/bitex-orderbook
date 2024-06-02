<?php

namespace App\Helper\Trade;

use Exception;
use App\Models\Offer;
use App\Models\Trade;
use App\Models\Service;
use App\Models\OfferHistory;
use App\Enums\Offer\OfferType;
use App\Exceptions\TradeException;
use Illuminate\Support\Facades\DB;
use App\Helper\Trade\Traits\Broker;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use App\Helper\Trade\Traits\Transaction;
use Illuminate\Support\Facades\Log;

class TradeHelper
{
    use Transaction, Broker;

    /**
     * Takes an offer and lookin for corresponding offer for that and do trade if found
     *
     * @param Offer $offer
     * @return boolean
     */
    public function trade(Offer $offer):Trade|false
    {
        if($offer->type == OfferType::BUY->value){
            if($found = $this->findSellOfferWithEqualOrCheaperPrice($offer->service, $offer->price)){
                $buy = $offer;

                $sell = $found;
            }
        } else {
            if($found = $this->findBuyOfferWithEqualOrHigherPrice($offer->service, $offer->price)){

                $buy = $found;

                $sell = $offer;
            }
        }


        if($found){
            return $this->tradeTransaction($buy, $sell);
        }
        else
            return false;
    }

    /**
     * Takes an buy and an sell offers and trade thoese togther
     *
     * @param Offer $buy
     * @param Offer $sell
     * @return boolean
     */
    public function tradeTogther(Offer $buy, Offer $sell):Trade|false
    {
        return $this->tradeTransaction($buy, $sell);
    }
}
