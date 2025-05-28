<!DOCTYPE html>
<html>
<head>
    <style>
        .email-container {
            font-family: 'Arial', sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #1a5276;
            margin-bottom: 20px;
        }
        .logo {
            height: 80px;
            margin-bottom: 15px;
        }
        .school-name {
            color: #1a5276;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .system-name {
            color: #2874a6;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .otp-box {
            background-color: #eaf2f8;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .otp-code {
            font-size: 28px;
            letter-spacing: 3px;
            color: #2c3e50;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #7f8c8d;
            text-align: center;
        }
        .note {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="https://resources.finalsite.net/images/f_auto,q_auto,t_image_size_1/v1697025002/flipperschoolcom/umv1hfkk03vzp206sn4q/Flipper_Logo1.png" alt="Flippers International School Logo" class="logo">
            <div class="school-name">Flippers International School</div>
            <div class="system-name">Digital Library Management System</div>
        </div>
        
        <h2>Password Reset OTP</h2>
        <p>Dear User,</p>
        <p>We received a request to reset your password for the Digital Library account. Please use the following One-Time Password (OTP) to proceed:</p>
        
        <div class="otp-box">
            Your OTP Code: <span class="otp-code">{{ $otp }}</span>
        </div>
        
        <p>This code is valid for <strong>15 minutes</strong> only. Please do not share this code with anyone.</p>
        <p class="note">If you didn't request this password reset, please ignore this email or contact our support team immediately.</p>
        
        <div class="footer">
            © {{ date('Y') }} Flippers International School. All rights reserved.
        </div>
    </div>
</body>
</html>
