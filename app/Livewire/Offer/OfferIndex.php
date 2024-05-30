<?php

namespace App\Livewire\Offer;

use App\Models\Service;
use Livewire\Component;

class OfferIndex extends Component
{
    public Service $service;

    public function render()
    {
        return view('livewire.offer.offer-index');
    }
}
