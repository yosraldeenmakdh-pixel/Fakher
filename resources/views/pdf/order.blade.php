<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-6"/>
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body { font-family: Arial; font-size: 12px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Invoice #{{ $order->id }}</h2>
    <p><strong>Customer:</strong> {{ $order->name }}</p>
    <p><strong>Kitchen:</strong> {{ $order->kitchen->name ?? 'N/A' }}</p>

    <table>
        <tr><th>Meal</th><th>Qty</th><th>Price</th><th>Total</th></tr>
        @foreach($order->orderItems as $item)
        <tr>
            <td>{{ $item->meal->name ?? 'N/A' }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price, 2) }} AED</td>
            <td>{{ number_format($item->total_price, 2) }} AED</td>
        </tr>
        @endforeach
    </table>

    <h3>Total: {{ number_format($order->total, 2) }} AED</h3>
</body>
</html>
