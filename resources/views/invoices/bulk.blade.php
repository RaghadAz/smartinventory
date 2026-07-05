<table class="table">
    <thead>
        <tr>
            <th>الصنف (عطر/مكياج)</th>
            <th>الكمية</th>
            <th>السعر</th>
            <th>الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $sale)
        <tr>
            <td>{{ $sale->product->name }}</td>
            <td>{{ $sale->quantity_sold }}</td>
            <td>{{ number_format($sale->selling_price) }}</td>
            <td>{{ number_format($sale->total_price) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">الإجمالي الكلي</td>
            <td>{{ number_format($grandTotal) }} ل.س</td>
        </tr>
    </tfoot>
</table>