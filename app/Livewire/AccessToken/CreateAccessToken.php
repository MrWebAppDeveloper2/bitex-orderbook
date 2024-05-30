<?php

namespace App\Livewire\AccessToken;

use Livewire\Attributes\Validate;
use Livewire\Component;

class CreateAccessToken extends Component
{
    #[Validate(['required', 'max:255'])]
    public string $name;

    public function store()
    {
        $this->validate();
        
        $token = auth()->user()->createToken($this->name);

        session()->flash('alert-success', __('New token:') . ' ' . $token->plainTextToken);

        $this->redirect(route('access.token.index'), true);
    }

    public function render()
    {
        return view('livewire.access-token.create-access-token');
    }
}
