<div>
    <form wire:submit="sell">
        <div class="row">
            <div class="form-group col-4">
                <input wire:model='amount' type="number" placeholder="amount" @class(['form-control', 'is-invalid' => $errors->has('amount')])>
            </div>
            <div class="form-group col-4">
                <input wire:model='price' type="number" placeholder="price" @class(['form-control', 'is-invalid' => $errors->has('price')])>
            </div>
            <div class="form-group col-4">
                <button class="btn w-100 btn-outline-danger" type="submit">Sell</button>
            </div>
        </div>
        <div class="p-3">
            @session('alert-success')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $value }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endsession
            @session('alert-danger')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $value }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endsession
        </div>
    </form>
</div>
