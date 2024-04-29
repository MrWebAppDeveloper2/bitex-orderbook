<?php

namespace App\Livewire\Components;

use App\Concretes\Caching\BuyOffersCacheList;
use Livewire\Attributes\On;
use Livewire\Component;

class BuyOffersTb extends Component
{
    public array $offers;

    #[On('echo:buy-offers,BuyOffersCacheListUpdated')]
    public function listUpdated($event)
    {
        $this->offers = $event['list'];
    }

    public function mount(BuyOffersCacheList $list)
    {
        $this->offers = $list->all();
    }

    public function render()
    {
        return view('livewire.components.buy-offers-tb');
    }
}
