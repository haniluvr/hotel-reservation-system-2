<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #8b9c7a;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin: 20px 0;
        }
        .receipt-details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
            color: #8b9c7a;
            margin-top: 10px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .success-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment Receipt</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $reservation->user->name }},</p>
            
            <div class="success-badge">
                ✓ Payment Successful
            </div>
            
            <p>Thank you for your payment. Your reservation has been confirmed.</p>
            
            <div class="receipt-details">
                <h2 style="margin-top: 0;">Payment Details</h2>
                
                <div class="detail-row">
                    <span class="label">Payment ID:</span>
                    <span class="value">{{ $payment->xendit_invoice_id }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Reservation Number:</span>
                    <span class="value">{{ $reservation->reservation_number }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Payment Method:</span>
                    <span class="value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Payment Date:</span>
                    <span class="value">{{ $payment->paid_at->format('F d, Y h:i A') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Hotel:</span>
                    <span class="value">{{ $reservation->room->hotel->name }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Room Type:</span>
                    <span class="value">{{ $reservation->room->room_type }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Check-in:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('F d, Y') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Check-out:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('F d, Y') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Amount Paid:</span>
                    <span class="value total">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                </div>
            </div>
            
            <p>Your reservation is now confirmed. You will receive a check-in reminder 24 hours before your arrival date.</p>
            
            <p>If you have any questions, please contact us at support@belmonthotel.com or call +63 2 1234 5678.</p>
            
            <p>We look forward to welcoming you!</p>
            
            <p>Best regards,<br>
            Belmont Hotel Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} Belmont Hotel. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

