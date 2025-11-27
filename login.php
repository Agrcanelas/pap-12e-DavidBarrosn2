<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Humani Care</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Georgia, serif;
            background-color: #f5f5f0;
        }
        header {
            background-color: #d4d2c8;
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 2px solid #bcb9ae;
        }
        header h1 {
            margin: 0;
            font-size: 32px;
            color: #7a8f47;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 60px;
        }
        .login-box {
            background-color: white;
            width: 360px;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-box h2 {
            text-align: center;
            color: #7a8f47;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #bbb;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #7a8f47;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
        }
        button:hover {
            background-color: #6a7f3f;
        }
        .extra-links {
            text-align: center;
            margin-top: 15px;
        }
        .extra-links a {
            text-decoration: none;
            color: #7a8f47;
        }
    </style>
</head>
<body>
    <header>
        <h1>HUMANI CARE</h1>
    </header>

    <div class="container">
        <div class="login-box">
            <h2>Login</h2>
            <form>
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required>

                <label for="password">Palavra-passe:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Entrar</button>
            </form>

            <div class="extra-links">
                <p><a href="#">Esqueceu a palavra-passe?</a></p>
                <p><a href="#">Criar nova conta</a></p>
            </div>
        </div>
    </div>
</body>
</html>
