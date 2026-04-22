<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Cairo', sans-serif;
            background: #0a0e1a;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wrapper {
            max-width: 560px;
            width: 100%;
            margin: 0 auto;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            border-radius: 20px 20px 0 0;
            padding: 36px 40px 28px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -60px; left: 50%;
            transform: translateX(-50%);
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(99,102,241,0.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.6), transparent);
        }

        .logo-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            box-shadow: 0 0 24px rgba(99,102,241,0.5);
        }

        .logo-icon svg {
            width: 28px; height: 28px;
            fill: white;
        }

        .brand-name {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #ffffff;
            line-height: 1;
        }

        .brand-name span {
            color: #818cf8;
        }

        .brand-tagline {
            font-size: 12px;
            color: #6366f1;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 4px;
            font-weight: 600;
        }

        /* ── Body ── */
        .body {
            background: #0f172a;
            padding: 40px;
            border-left: 1px solid rgba(99,102,241,0.15);
            border-right: 1px solid rgba(99,102,241,0.15);
        }

        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 10px;
        }

        .message-text {
            font-size: 15px;
            color: #94a3b8;
            line-height: 2;
            margin-bottom: 32px;
        }

        /* ── OTP Box ── */
        .otp-container {
            background: linear-gradient(135deg, #0d1526, #111827);
            border: 1px solid rgba(99,102,241,0.35);
            border-radius: 16px;
            padding: 32px 24px;
            text-align: center;
            margin: 8px 0 32px;
            position: relative;
            overflow: hidden;
        }

        .otp-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #6366f1, #8b5cf6, transparent);
        }

        .otp-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 4px;
            color: #6366f1;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .otp-code {
            font-size: 48px;
            font-weight: 900;
            letter-spacing: 14px;
            background: linear-gradient(135deg, #a5b4fc, #818cf8, #c4b5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            text-shadow: none;
            display: block;
            padding-right: 14px; /* offset for letter-spacing */
        }

        .otp-divider {
            width: 60px; height: 2px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            margin: 20px auto 14px;
            border-radius: 2px;
        }

        .otp-validity {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.25);
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 13px;
            color: #818cf8;
            font-weight: 600;
        }

        .otp-validity::before {
            content: '⏱';
            font-size: 14px;
        }

        /* ── Notice ── */
        .notice {
            background: rgba(245,158,11,0.07);
            border: 1px solid rgba(245,158,11,0.2);
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 13px;
            color: #fbbf24;
            line-height: 1.9;
        }

        .notice::before {
            content: '⚠ ';
        }

        /* ── Footer ── */
        .footer {
            background: #080c17;
            border-radius: 0 0 20px 20px;
            border: 1px solid rgba(99,102,241,0.1);
            border-top: none;
            padding: 24px 40px;
            text-align: center;
        }

        .footer-line {
            width: 100%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.3), transparent);
            margin-bottom: 20px;
        }

        .footer-text {
            font-size: 12px;
            color: #334155;
            line-height: 2;
        }

        .footer-text a {
            color: #6366f1;
            text-decoration: none;
        }

        .footer-brand {
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            margin-top: 6px;
        }

        .footer-brand span { color: #6366f1; }

        /* glow dots decoration */
        .dot {
            display: inline-block;
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #6366f1;
            margin: 0 4px;
            vertical-align: middle;
            opacity: 0.5;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- HEADER -->
    <div class="header">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/>
            </svg>
        </div>
        <div class="brand-name">Neo<span>Campus</span></div>
        <div class="brand-tagline">منصة التعلم الذكي</div>
    </div>

    <!-- BODY -->
    <div class="body">
        <div class="greeting">مرحباً بك 👋</div>
        <p class="message-text">
            لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك على منصة NeoCampus.
            يرجى استخدام رمز التحقق أدناه لإتمام العملية.
        </p>

        <div class="otp-container">
            <div class="otp-label">رمز التحقق الخاص بك</div>
            <span class="otp-code">{{ $otp }}</span>
            <div class="otp-divider"></div>
            <span class="otp-validity">صالح لمدة 60 دقيقة فقط</span>
        </div>

        <div class="notice">
            إذا لم تكن أنت من طلب هذا الإجراء، يمكنك تجاهل هذا البريد بأمان تام. لن يتم إجراء أي تغييرات على حسابك.
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="footer-line"></div>
        <p class="footer-text">
            هذا بريد إلكتروني آلي، يرجى عدم الرد عليه مباشرةً.
            <br>للدعم: <a href="mailto:support@neocampus.io">support@neocampus.io</a>
        </p>
        <p class="footer-brand">
            <span class="dot"></span>
            &copy; {{ date('Y') }} <span>NeoCampus</span> — جميع الحقوق محفوظة
            <span class="dot"></span>
        </p>
    </div>

</div>
</body>
</html>