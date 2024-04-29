<div class="card my-3 px-3">
    <div class="card-body row">
        <div class="col-md-6" id="orders-container">
            <h4>Buy</h4>
            <table class="table table-success">
                <thead>
                <tr>
                    <th>amount</th>
                    <th>price</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>2</td>
                    <td>50000</td>
                </tr>
                </tbody>
            </table>

            <!-- Buy form -->
            <livewire:components.buy-frm/>
        </div>
        <div class="col-md-6">
            <h4>Sell</h4>
            <table class="table table-danger">
                <thead>
                <tr>
                    <th>amount</th>
                    <th>price</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>2</td>
                    <td>50000</td>
                </tr>
                </tbody>
            </table>

            <!-- Sell form -->
            <livewire:components.sell-frm/>
        </div>
    </div>
</div>
