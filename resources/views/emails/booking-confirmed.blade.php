<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <style>
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background-color: #0E1712;
            color: #F3F1E9;
            line-height: 1.6;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #182620;
            border: 1px solid rgba(233, 228, 214, 0.09);
            border-radius: 16px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
        }
        /* Header with gold accent */
        .header {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 2px solid rgba(201, 162, 39, 0.3);
            margin-bottom: 32px;
        }
        .logo {
            font-size: 28px;
            font-weight: 700;
            font-style: italic;
            letter-spacing: 0.02em;
            color: #C9A227;
            font-family: 'Georgia', 'Times New Roman', serif;
        }
        .logo span {
            color: #F3F1E9;
        }
        .badge {
            display: inline-block;
            background: rgba(201, 162, 39, 0.15);
            border: 1px solid rgba(201, 162, 39, 0.3);
            color: #C9A227;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 12px;
        }
        /* Content styles */
        .content {
            padding: 0 4px;
        }
        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #F3F1E9;
            margin-bottom: 6px;
        }
        .greeting span {
            color: #C9A227;
        }
        .sub-greeting {
            color: #9AA79E;
            font-size: 15px;
            margin-bottom: 24px;
        }
        /* Booking details card */
        .details-card {
            background: #1F2E2A;
            border: 1px solid rgba(233, 228, 214, 0.08);
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(233, 228, 214, 0.06);
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            color: #9AA79E;
            font-size: 13px;
        }
        .details-value {
            color: #F3F1E9;
            font-weight: 600;
            font-size: 14px;
        }
        .details-value.reference {
            color: #C9A227;
            font-size: 16px;
            letter-spacing: 0.05em;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(63, 166, 114, 0.15);
            border: 1px solid rgba(63, 166, 114, 0.3);
            color: #3FA672;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #3FA672;
            display: inline-block;
        }
        /* Message */
        .message {
            color: #9AA79E;
            font-size: 15px;
            margin: 24px 0 16px;
            padding: 16px 20px;
            background: #1F2E2A;
            border-left: 3px solid #C9A227;
            border-radius: 0 8px 8px 0;
        }
        /* Footer */
        .footer {
            text-align: center;
            padding-top: 30px;
            margin-top: 32px;
            border-top: 1px solid rgba(233, 228, 214, 0.08);
        }
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-bottom: 16px;
        }
        .footer-links a {
            color: #9AA79E;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }
        .footer-links a:hover {
            color: #C9A227;
        }
        .footer-copy {
            color: #6D7C74;
            font-size: 12px;
            letter-spacing: 0.03em;
        }
        .footer-copy span {
            color: #C9A227;
        }
        /* Button */
        .btn {
            display: inline-block;
            background: linear-gradient(180deg, #E4C766, #C9A227);
            color: #1B1204 !important;
            padding: 12px 32px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.25s ease;
            margin-top: 8px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(201, 162, 39, 0.35);
        }
        /* Responsive */
        @media (max-width: 480px) {
            .container {
                padding: 28px 20px;
            }
            .details-row {
                flex-direction: column;
                gap: 2px;
                padding: 8px 0;
            }
            .footer-links {
                flex-wrap: wrap;
                gap: 12px;
            }
            .logo {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">Savora <span>Dining</span></div>
            <div class="badge">✓ Booking Confirmed</div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Dear <span>{{ $guest_name ?? 'Guest' }}</span>,
            </div>
            <p class="sub-greeting">
                Your table has been reserved. We look forward to welcoming you.
            </p>

            <!-- Booking Details -->
            <div class="details-card">
                <div class="details-row">
                    <span class="details-label">Booking Reference</span>
                    <span class="details-value reference">{{ $booking_id ?? 'N/A' }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Date</span>
                    <span class="details-value">{{ $date ?? 'N/A' }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Time</span>
                    <span class="details-value">{{ $time ?? 'N/A' }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Party Size</span>
                    <span class="details-value">{{ $party_size ?? 'N/A' }} guests</span>
                </div>
                <div class="details-row" style="border-bottom: none; padding-bottom: 4px;">
                    <span class="details-label">Status</span>
                    <span class="details-value">
                        <span class="status-badge">
                            <span class="dot"></span>
                            Confirmed
                        </span>
                    </span>
                </div>
            </div>

            <!-- Message -->
            <div class="message">
                <strong>📌 Important:</strong> Please arrive 10 minutes before your booking time. 
                If you need to cancel or modify your reservation, please contact us at least 
                4 hours in advance.
            </div>

            <!-- Button -->
            <div style="text-align: center; margin-top: 8px;">
                <a href="{{ $restaurant_url ?? '/' }}" class="btn">
                    View My Booking
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact</a>
            </div>
            <div class="footer-copy">
                &copy; {{ date('Y') }} <span>Savora</span> — a NEMESIS product.
                <br>
                <span style="font-size: 11px; color: #6D7C74;">
                    This email was sent to {{ $guest_email ?? 'you' }}.
                </span>
            </div>
        </div>
    </div>
</body>
</html>