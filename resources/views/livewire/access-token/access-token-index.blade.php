<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

    <div class="grid grid-cols-1 gap-2">
        <x-section-card>
            <x-slot:header>
                <div class="grid grid-cols-12 gap-y-6 justify-between items-center w-full">
                    <h2 class="col-start-1 col-end-7">{{ __('Api Access Tokens') }}</h2>
                    
                    <div class="text-end col-start-7 col-end-13">
                        <x-atag-button href="{{ route('access.token.create') }}" wire:navigate color="green">
                            {{ __('Register New Api Access Token') }}
                        </x-atag-button>
                    </div>
                </div> 
            </x-slot:header>

            <x-alert></x-alert>

            <div class="relative overflow-x-auto overflow-y-visible shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                #
                            </th>
                            <th scope="col" class="px-6 py-3">
                                {{ __('Name') }}
                            </th>
                            <th scope="col" class="px-6 py-3">
                                {{ __('Action') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tokens as $token)
                            <tr class="bg-white border-b" :key="$token->id">
                                <td class="px-6 py-4">
                                    {{ $loop->iteration}}
                                </td>
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $token->name }}
                                </th>
                                <td class="px-6 py-4">
                                    <x-danger-button wire:click="delete">{{ __('Delete') }}</x-danger-button>
                                </td>
                        @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>            
            <div class="mt-10">
                {{ $tokens->links() }}
            </div>
        </x-section-card>
    </div>
    
</div>