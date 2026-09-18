<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата успішна</title>
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .success-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
        }

        .success-container img {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
        }

        .success-container h1 {
            color: #16a34a;
            margin-bottom: 10px;
        }

        .success-container p {
            color: #374151;
            font-size: 16px;
        }

        .btn-home {
            margin-top: 25px;
            padding: 12px 24px;
            background-color: #16a34a;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-home:hover {
            background-color: #15803d;
        }
    </style>
</head>
<body>
<div class="success-container">
    <img src="{{ asset('images/pay-success.png') }}" alt="Успішно">
    <h1>Оплата пройшла успішно!</h1>
    <p>Дякуємо за вашу оплату. Квитанцію буде надіслано на вашу електронну пошту.</p>
    <a href="{{ url('/atmosphera') }}" class="btn-home">На головну</a>
</div>
</body>
</html>
