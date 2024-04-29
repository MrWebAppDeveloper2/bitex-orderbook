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
    </form>
</div>
