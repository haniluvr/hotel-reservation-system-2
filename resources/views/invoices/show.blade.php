<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @media print {
            body { margin: 0; padding: 10px; }
            .no-print { display: none; }
            .container { box-shadow: none; padding: 10px; }
        }
        @page {
            margin: 10mm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 15px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #8b9c7a;
        }
        .company-info {
            flex: 1;
        }
        .company-info h2 {
            margin: 0 0 3px 0;
            color: #8b9c7a;
            font-size: 18px;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 10px;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h1 {
            margin: 0 0 5px 0;
            color: #8b9c7a;
            font-size: 20px;
        }
        .invoice-info p {
            margin: 3px 0;
            font-size: 10px;
        }
        .section {
            margin-bottom: 12px;
        }
        .section-title {
            font-weight: bold;
            font-size: 12px;
            color: #8b9c7a;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }
        .two-columns {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .column {
            flex: 1;
        }
        .column p {
            margin: 3px 0;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            font-size: 11px;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .download-btn {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #8b9c7a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .download-btn:hover {
            background: #7a8a6a;
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="no-print" style="text-align: center; margin-bottom: 15px;">
        <a href="{{ route('invoices.download', $reservation->id) }}" class="download-btn">
            <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 5px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Download PDF
        </a>
    </div>
    <div class="header">
        <div class="company-info">
            <h2>{{ $company['name'] }}</h2>
            <p>{{ $company['address'] }}</p>
            <p>Phone: {{ $company['phone'] }}</p>
            <p>Email: {{ $company['email'] }}</p>
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
                <p><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($reservation->check_in_date)->format('M d, Y') }}</p>
                <p><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($reservation->check_out_date)->format('M d, Y') }}</p>
                <p><strong>Nights:</strong> {{ $reservation->getTotalNights() }} | <strong>Guests:</strong> {{ $reservation->adults }} Adult(s), {{ $reservation->children }} Child(ren)</p>
                <p><strong>Room:</strong> {{ $reservation->room->room_type }}</p>
            </div>
        </div>
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
                <td>{{ $reservation->room->room_type }} - Belmont Hotel<br>
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
    <div class="section" style="margin-top: 10px;">
        <div class="section-title">Payment Information:</div>
        <div class="two-columns">
            <div class="column">
                <p><strong>Status:</strong> {{ ucfirst($reservation->payment->status) }}</p>
                <p><strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $reservation->payment->payment_method)) }}</p>
            </div>
            <div class="column">
                @if($reservation->payment->paid_at)
                <p><strong>Paid On:</strong> {{ $reservation->payment->paid_at->format('M d, Y h:i A') }}</p>
                @endif
                <p><strong>Payment ID:</strong> {{ $reservation->payment->xendit_invoice_id }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for choosing {{ $company['name'] }}! | This is a computer-generated invoice. No signature required.</p>
        <p>&copy; {{ date('Y') }} {{ $company['name'] }}. All rights reserved.</p>
    </div>
    </div>
</body>
</html>

