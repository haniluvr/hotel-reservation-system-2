<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
        }
        .success-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 20px;
            color: #C5A572;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <svg class="success-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h1>Payment Successful!</h1>
        <p>This window will close automatically...</p>
    </div>
    
    <script>
        // Close the window after a short delay
        setTimeout(function() {
            window.close();
            
            // Fallback: If window.close() doesn't work, try to redirect the opener
            if (!window.closed) {
                if (window.opener && !window.opener.closed) {
                    window.opener.focus();
                }
                // Try again
                window.close();
            }
        }, 1500);
    </script>
</body>
</html>

