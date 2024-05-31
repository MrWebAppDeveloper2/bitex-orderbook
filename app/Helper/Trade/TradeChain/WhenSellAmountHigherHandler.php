<?php

namespace App\Helper\Trade\TradeChain;

use Exception;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use App\Contracts\TradeChainHandlerInterface;

class WhenSellAmountHigherHandler extends BaseHandler implements TradeChainHandlerInterface 
{
    public function handle(Offer $buy, Offer $sell):bool
    {
        if($buy->remaining_amount < $sell->remaining_amount){
            try{
                DB::beginTransaction();
    
                $sell->remaining_amount -= $buy->remaining_amount;
    
                $sell->save();
    
                $buy->delete();
    
                DB::commit();
    
                return true;
                
            } catch (Exception $e){
                return false;
            }
        }

        return $this->next($buy, $sell);
    }

}
