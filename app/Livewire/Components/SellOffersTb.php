<?php

namespace App\Livewire\Components;

use App\Concretes\Caching\SellOffersCacheList;
use Livewire\Attributes\On;
use Livewire\Component;

class SellOffersTb extends Component
{
    public array $offers;

    #[On('echo:buy-offers,SellOffersCacheListUpdated')]
    public function listUpdated($event)
    {
        dd($event->list);
    }

    public function mount(SellOffersCacheList $list)
    {
        $this->offers = $list->all();
    }

    public function render()
    {
        return view('livewire.components.sell-offers-tb');
    }
}
