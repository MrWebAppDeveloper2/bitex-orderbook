<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

class Orderbook extends Component
{
    #[On('echo:buy-offers,BuyOffersCacheListUpdated')]
    public function updateBuyList()
    {
        dd('here');
    }

    #[Title('Orderbook')]
    public function render()
    {
        return view('livewire.orderbook');
    }
}
