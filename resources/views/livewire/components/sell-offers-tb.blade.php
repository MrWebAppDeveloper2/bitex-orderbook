<div>
    <table class="table table-danger">
        <thead>
        <tr>
            <th>amount</th>
            <th>price</th>
        </tr>
        </thead>
        <tbody>
        @foreach($offers as $item)
            <tr>
                <td>{{ $item['remaining_amount'] }}</td>
                <td>{{ $item['price'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
