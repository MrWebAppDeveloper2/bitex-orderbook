<?php

namespace App\Helper\Trade\TradeChain;

use Exception;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use App\Contracts\TradeChainHandlerInterface;

class WhenBuyAndSellAmountSameHandler extends BaseHandler implements TradeChainHandlerInterface 
{
    public function handle(Offer $buy, Offer $sell):bool
    {
        if($buy->remaining_amount == $sell->remaining_amount){
            try{
                DB::beginTransaction();

                $buy->delete();
    
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
