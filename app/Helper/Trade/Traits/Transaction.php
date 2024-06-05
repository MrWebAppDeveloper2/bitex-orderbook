<?php

namespace App\Helper\Trade\Traits;

use Exception;
use App\Models\Offer;
use App\Models\Trade;
use App\Exceptions\TradeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait Transaction
{
    /**
     * Preper offers for transaction then call executeTransaction method
     *
     * @param Offer $buy
     * @param Offer $sell
     * @return Trade|false
     */
    private function tradeTransaction(Offer $buy, Offer $sell):Trade|false
    {
        if($buyLock = $this->lockOffer($buy) and $sellLock = $this->lockOffer($sell)){
            $res = $this->executeTransaction($buy, $sell);

            $buyLock->release();

            $sellLock->release();

            return $res;
        }
        else
            return false;
    }

    /**
     * Trade transaction
     *
     * @param Offer $buy
     * @param Offer $sell
     * @return Trade|false
     */
    private function executeTransaction(Offer $buy, Offer $sell):Trade|false
    {

        try{
            
            DB::beginTransaction();

                if($buy->remaining_amount > $sell->remaining_amount){

                    $buy->remaining_amount -= $sell->remaining_amount;

                    $buy->save();
        
                    $sell->delete();

                }elseif($buy->remaining_amount < $sell->remaining_amount){

                    $sell->remaining_amount -= $buy->remaining_amount;
    
                    $sell->save();
        
                    $buy->delete();

                }elseif($buy->remaining_amount == $sell->remaining_amount){

                    $buy->delete();
     
                    $sell->delete();

                }

                $trade = Trade::create(['offer_buy_id' => $buy->history->id, 'offer_sell_id' => $sell->history->id]);

            DB::commit();

            return $trade;
            
        } catch (Exception $e){

            DB::rollBack();

            throw new TradeException("Trade transaction failed ! Buy Offer User ID: {$buy->user->id}, Sell Offer User ID: {$sell->user->id}. \n Exception description: {$e->getMessage()}");
        }
    }

    /**
     * Try to set atomic lock for offer
     *
     * @param Offer $offer
     * @return Lock|false
     */
    private function lockOffer(Offer $offer):Lock|false
    {
        $lock = Cache::lock("trade-transaction-offer.{$offer->id}", config('custom.trade.offer_trade_lock_time'));

        return $lock->get() ? $lock : false;
    }
}
