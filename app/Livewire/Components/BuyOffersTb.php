<?php

namespace App\Livewire\Components;

use App\Concretes\Caching\BuyOffersCacheList;
use App\Models\Service;
use Livewire\Attributes\On;
use Livewire\Component;

class BuyOffersTb extends Component
{
    public Service $service;

    public array $offers;

    public function getListeners()
    {
        return [
            "echo:buy-offers.{$this->service->id},BuyOffersCacheListUpdated" => 'listUpdated',
        ];
    }

    public function listUpdated($event)
    {
        $this->offers = $event['list'];
    }

    public function mount()
    {
        $list = app()->makeWith(BuyOffersCacheList::class, ['service' => $this->service]);

        $this->offers = $list->all();
    }

    public function render()
    {
        return view('livewire.components.buy-offers-tb');
    }
}
