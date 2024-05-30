

<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

    <div class="grid grid-cols-1 gap-2">
        <x-section-card>
            <x-slot:header>
                <div class="grid grid-cols-12 gap-y-6 justify-between items-center w-full">
                    <h2 class="col-start-1 col-end-7">{{ __('Services') }}</h2>
                    
                </div> 
            </x-slot:header>

            <x-alert></x-alert>

                <div class="card-body row bg-white p-4">
                    <div class="col-md-6" id="orders-container">
                        <h4>Buy</h4>
                        <livewire:components.buy-offers-tb :service="$service"/>
            
                        <!-- Buy form -->
                        <livewire:components.buy-frm :service="$service"/>
                    </div>
                    <div class="col-md-6">
                        <h4>Sell</h4>
                        <livewire:components.sell-offers-tb :service="$service"/>
            
                        <!-- Sell form -->
                        <livewire:components.sell-frm :service="$service"/>
                    </div>
                </div>
        </x-section-card>
    </div>
    
</div>