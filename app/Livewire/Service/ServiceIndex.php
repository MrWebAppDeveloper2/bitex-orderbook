<?php

namespace App\Livewire\Service;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class ServiceIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public function render()
    {
        return view('livewire.service.service-index')
            ->with('services', Service::paginate(20));
    }
}
