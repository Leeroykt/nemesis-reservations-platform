<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Confirmed</title>
    <style>
        body {
            font-family: 'Raleway', system-ui, sans-serif;
            background: #f8f9fa;
            padding: 40px 20px;
            margin: 0;
            line-height: 1.6;
            color: #1a1a1a;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #C9A227;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            color: #C9A227;
            margin: 0;
        }
        .content {
            font-size: 16px;
            white-space: pre-wrap;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
            margin-top: 30px;
        }
        .reference {
            background: #f8f9fa;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-block;
            font-weight: 600;
            color: #C9A227;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Confirmed</h1>
        </div>
        <div class="content">
            {!! nl2br(e($body)) !!}
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>