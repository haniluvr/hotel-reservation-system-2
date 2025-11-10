<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancellation</title>
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
            background-color: #dc3545;
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
        .cancellation-details {
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
        .refund-amount {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Cancellation</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $reservation->user->name }},</p>
            
            <p>We're sorry to see you cancel your reservation. Your booking has been cancelled as requested.</p>
            
            <div class="cancellation-details">
                <h2 style="margin-top: 0;">Cancellation Details</h2>
                
                <div class="detail-row">
                    <span class="label">Reservation Number:</span>
                    <span class="value">{{ $reservation->reservation_number }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Hotel:</span>
                    <span class="value">Belmont Hotel</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Room Type:</span>
                    <span class="value">{{ $reservation->room->room_type }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Check-in Date:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('F d, Y') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Cancellation Date:</span>
                    <span class="value">{{ $reservation->cancelled_at->format('F d, Y h:i A') }}</span>
                </div>
                
                @if($reservation->cancellation_reason)
                <div class="detail-row">
                    <span class="label">Reason:</span>
                    <span class="value">{{ $reservation->cancellation_reason }}</span>
                </div>
                @endif
            </div>
            
            @if($refundAmount && $refundAmount > 0)
            <div class="refund-amount">
                Refund Amount: ₱{{ number_format($refundAmount, 2) }}
            </div>
            <p>Your refund of <strong>₱{{ number_format($refundAmount, 2) }}</strong> will be processed within 5-7 business days to your original payment method.</p>
            @elseif($reservation->payment && $reservation->payment->status === 'paid')
            <p>If you are eligible for a refund, it will be processed according to our cancellation policy. You will receive a separate email once the refund is processed.</p>
            @endif
            
            <p>We hope to welcome you in the future. If you have any questions, please contact us at support@belmonthotel.com or call +63 2 1234 5678.</p>
            
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

