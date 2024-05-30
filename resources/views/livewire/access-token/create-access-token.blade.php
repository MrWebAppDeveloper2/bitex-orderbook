<div class="max-w-7xl mx-auto py-5 sm:px-6 lg:px-8">

    <div class="grid grid-cols-1 gap-2">
        <x-section-card>
            <x-slot:header>
                <div class="grid grid-cols-12 gap-y-6 justify-between items-center w-full">
                    <h3 class="col-start-1 col-end-7">{{ __('Create New Api Access Tokens') }}</h3>
                </div> 
            </x-slot:header>

            <x-alert></x-alert>

            <form action="" wire:submit="store" class="grid grid-cols-1 gap-1 items-end">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-label for="name" value="{{ __('Name') }}" />
                        <x-input id="name" type="text" class="mt-1 block w-full text-sm" wire:model="name" autocomplete="off"/>
                        <x-jetstream-input-error for="name" class="mt-2" />
                    </div>
                </div>
                <div class="text-left pt-5">
                    
                    <x-button type="submit">
                        {{ __('Store') }}
                    </x-button>
                  
                    <x-atag-button href="{{ route('access.token.index') }}" wire:navigate class="bg-yellow-300 text-gray-950">
                        {{ __('Cancel') }}
                    </x-atag-button>
                </div>
            </form>
        </x-section-card>
    </div>
    
</div>