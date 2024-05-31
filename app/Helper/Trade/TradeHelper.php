<?php

namespace App\Helper\Trade;

use App\Models\Offer;
use App\Models\Service;
use App\Enums\Offer\OfferType;
use App\Exceptions\TradeException;
use App\Helper\Trade\TradeChain\TradeChain;
use App\Helper\Trade\TradeChain\TradeChainStartPoint;
use App\Models\OfferHistory;
use App\Models\Trade;
use Exception;

class TradeHelper
{
    private function findSellOfferWithEqualOrCheaperPrice(Service $service, int $price):Offer|null
    {
        return $service->offers()->sell()->where('price', '<=', $price)->orderBy('created_at', 'ASC')->first();
    } 

    private function findBuyOfferWithEqualOrHigherPrice(Service $service, int $price):Offer|null
    {
        return $service->offers()->buy()->where('price', '>=', $price)->orderBy('created_at', 'ASC')->first();
    } 

    private function sendToTradeChain(Offer $buy, Offer $sell):bool
    {
        $chain = app()->make(TradeChainStartPoint::class);

        return $chain->handle($buy, $sell);
    }

    public function createTradeRecord(OfferHistory $buy, OfferHistory $sell)
    {
        return Trade::create(['offer_buy_id' => $buy->id, 'offer_sell_id' => $sell->id]);
    }
    
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

        if(isset($found) and $found){
            if(!$buyHistory = $buy->history){
                $buy->delete();

                throw new TradeException("Offer with {$buy->id} id has not offer history record and for this reason deleted !");

            } elseif(!$sellHistory = $sell->history){
                $sell->delete();

                throw new TradeException("Offer with {$sell->id} id has not offer history record and for this reason deleted !");
            }

            if($this->sendToTradeChain($buy, $sell)){
                return $this->createTradeRecord($buyHistory, $sellHistory);
            }
        }

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

    }
}
