<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .code {
            font-size: 1.5em;
            font-weight: bold;
            color: #2c3e50;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>Hello,</p>
        <p>Your verification code is: <span class="code">{{ $code }}</span></p>
        <p>Use this code to verify your email address.</p>
        <p class="footer">If you did not request this, please ignore this email.</p>
    </div>
</body>
</html>
