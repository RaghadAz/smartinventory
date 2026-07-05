<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>فاتورة #{{ $sale->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');
        
        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            padding: 20px;
            background: #f5f5f5;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
        .info {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background: #2c3e50;
            color: #fff;
        }
        .totals {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>🧾 فاتورة مبيعات</h1>
            <p>رقم الفاتورة: #{{ $sale->id }}</p>
            <p>التاريخ: {{ $sale->created_at->format('Y-m-d H:i') }}</p>
        </div>

        <div class="info">
            <div class="info-row">
                <span><strong>👤 الزبون:</strong> {{ $sale->customer_name ?? 'كاش' }}</span>
                <span><strong>💳 طريقة الدفع:</strong> {{ $sale->payment_type === 'cash' ? 'كاش' : 'دين' }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product?->name ?? 'منتج محذوف' }}</td>
                    <td>{{ (int) $item->quantity }}</td>
                    <td>{{ number_format($item->price, 0) }} SYP</td>
                    <td>{{ number_format($item->total, 0) }} SYP</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span>💰 الإجمالي:</span>
                <span>{{ number_format($sale->total_price, 0) }} SYP</span>
            </div>
            <div class="total-row">
                <span>💵 المدفوع:</span>
                <span>{{ number_format($sale->paid_amount, 0) }} SYP</span>
            </div>
            @if($sale->remaining_price > 0)
            <div class="total-row" style="color: #e74c3c;">
                <span>📋 المتبقي:</span>
                <span>{{ number_format($sale->remaining_price, 0) }} SYP</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>📈 الربح:</span>
                <span>{{ number_format($sale->total_profit, 0) }} SYP</span>
            </div>
        </div>

        <div class="footer">
            <p>شكراً لتعاملكم معنا 🙏</p>
            <p>تم الطباعة بتاريخ: {{ now()->format('Y-m-d H:i') }}</p>
        </div>
    </div>
</body>
</html>