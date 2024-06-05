<?php

namespace App\Listeners;

use App\Enums\Offer\OfferType;
use App\Events\OfferCreated;
use App\Exceptions\DecrementUserBalanceException;
use App\Models\User;
use App\Models\UserBalance;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DecrementUserBalance
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OfferCreated $event): void
    {
        $offer = $event->offer;

        try{
            if($offer->type == OfferType::SELL->value){
                if(!$balance = $offer->user->balances()->where('service_key', $offer->service->key)->withoutGlobalScopes()->first())
                    throw new DecrementUserBalanceException("UserBalance not found in user_balance table with {$offer->service->key} service key.");

                $balance->value = ($balance->value - $offer->totalValue);

                if(!$balance->save())
                    throw new DecrementUserBalanceException("Update user balance in user_balance table for {$offer->service->key} service key failed !");
            }

            elseif($offer->type == OfferType::BUY->value){
                $offer->user->balance = ($offer->user->balance - $offer->totalValue);

                if(!$offer->user->save())
                    throw new DecrementUserBalanceException('Update user decremented balance into database for buy offer, query failed !');
            }
            
        } catch(DecrementUserBalanceException $e){
            Log::error($e->getMessage());

            $offer->delete();
        }

    }
}
