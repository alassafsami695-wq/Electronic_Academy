<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>

    <h2>مرحبا {{ $user->name }} 👋</h2>

    <p>رمز التحقق الخاص بك هو:</p>

    <h1 style="font-size: 32px; letter-spacing: 5px;">{{ $code }}</h1>

    <p>صلاحية الرمز: 10 دقائق.</p>

</body>
</html>
