<?php

namespace App\Livewire\Components;

use App\Concretes\Caching\SellOffersCacheList;
use App\Models\Service;
use Livewire\Component;
use Livewire\Attributes\On;

class SellOffersTb extends Component
{
    public Service $service;

    public array $offers;

    public function getListeners()
    {
        return [
            "echo:sell-offers.{$this->service->id},SellOffersCacheListUpdated" => 'listUpdated',
        ];
    }

    public function listUpdated($event)
    {
        $this->offers = $event['list'];
    }

    public function mount()
    {
        $list = app()->makeWith(SellOffersCacheList::class, ['service' => $this->service]);

        $this->offers = $list->all();
    }

    public function render()
    {
        return view('livewire.components.sell-offers-tb');
    }
}
