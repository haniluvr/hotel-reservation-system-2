<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #8b9c7a;
        }
        .company-info {
            flex: 1;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h1 {
            margin: 0;
            color: #8b9c7a;
            font-size: 24px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            color: #8b9c7a;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .two-columns {
            display: flex;
            justify-content: space-between;
        }
        .column {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            font-size: 14px;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h2 style="margin: 0; color: #8b9c7a;">{{ $company['name'] }}</h2>
            <p style="margin: 5px 0;">{{ $company['address'] }}</p>
            <p style="margin: 5px 0;">Phone: {{ $company['phone'] }}</p>
            <p style="margin: 5px 0;">Email: {{ $company['email'] }}</p>
        </div>
        <div class="invoice-info">
            <h1>INVOICE</h1>
            <p><strong>Invoice #:</strong> {{ $invoice_number }}</p>
            <p><strong>Date:</strong> {{ $invoice_date }}</p>
            <p><strong>Reservation #:</strong> {{ $reservation->reservation_number }}</p>
        </div>
    </div>

    <div class="two-columns">
        <div class="column">
            <div class="section">
                <div class="section-title">Bill To:</div>
                <p><strong>{{ $reservation->user->name }}</strong></p>
                <p>{{ $reservation->user->email }}</p>
            </div>
        </div>
        <div class="column">
            <div class="section">
                <div class="section-title">Reservation Details:</div>
                <p><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($reservation->check_in_date)->format('F d, Y') }}</p>
                <p><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($reservation->check_out_date)->format('F d, Y') }}</p>
                <p><strong>Nights:</strong> {{ $reservation->getTotalNights() }}</p>
                <p><strong>Guests:</strong> {{ $reservation->adults }} Adult(s), {{ $reservation->children }} Child(ren)</p>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Accommodation Details:</div>
        <p><strong>Hotel:</strong> {{ $reservation->room->hotel->name }}</p>
        <p><strong>Room Type:</strong> {{ $reservation->room->room_type }}</p>
        <p><strong>Address:</strong> {{ $reservation->room->hotel->address }}, {{ $reservation->room->hotel->city }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $reservation->room->room_type }} - {{ $reservation->room->hotel->name }}<br>
                    <small>{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('M d') }} - {{ \Carbon\Carbon::parse($reservation->check_out_date)->format('M d, Y') }}</small>
                </td>
                <td class="text-right">{{ $reservation->getTotalNights() }} night(s)</td>
                <td class="text-right">₱{{ number_format($reservation->room->price_per_night, 2) }}</td>
                <td class="text-right">₱{{ number_format($reservation->room->price_per_night * $reservation->getTotalNights(), 2) }}</td>
            </tr>
            @if($reservation->discount_amount > 0)
            <tr>
                <td colspan="3" class="text-right">Discount @if($reservation->promo_code)({{ $reservation->promo_code }})@endif:</td>
                <td class="text-right">-₱{{ number_format($reservation->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>Total Amount:</strong></td>
                <td class="text-right"><strong>₱{{ number_format($reservation->total_amount, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if($reservation->payment)
    <div class="section">
        <div class="section-title">Payment Information:</div>
        <p><strong>Payment Status:</strong> {{ ucfirst($reservation->payment->status) }}</p>
        @if($reservation->payment->paid_at)
        <p><strong>Paid On:</strong> {{ $reservation->payment->paid_at->format('F d, Y h:i A') }}</p>
        @endif
        <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $reservation->payment->payment_method)) }}</p>
        <p><strong>Payment ID:</strong> {{ $reservation->payment->xendit_invoice_id }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for choosing {{ $company['name'] }}!</p>
        <p>This is a computer-generated invoice. No signature required.</p>
        <p>&copy; {{ date('Y') }} {{ $company['name'] }}. All rights reserved.</p>
    </div>
</body>
</html>

