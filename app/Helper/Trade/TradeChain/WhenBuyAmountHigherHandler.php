<?php

namespace App\Helper\Trade\TradeChain;

use App\Models\Offer;
use App\Contracts\TradeChainHandlerInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class WhenBuyAmountHigherHandler extends BaseHandler implements TradeChainHandlerInterface 
{
    public function handle(Offer $buy, Offer $sell):bool
    {
        if($buy->remaining_amount > $sell->remaining_amount){
            try{
                DB::beginTransaction();

                $buy->remaining_amount -= $sell->remaining_amount;

                $buy->save();
    
                $sell->delete();
    
                DB::commit();
    
                return true;
                
            } catch (Exception $e){
                return false;
            }
        }

        return $this->next($buy, $sell);
    }


}
