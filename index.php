<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/1ab94d0eba.js" crossorigin="anonymous"></script>
    <title>Login Form</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body onload="login.id.focus()">
    <main class="container">
        <h2>Iniciar sesión</h2>
        <form action="includes/start.php" method="POST" name="login">
            <div class="input-field">
                <input type="number" name="id" id="id"
                    placeholder="Ingresa tu numero de documento" tabindex="1">
                <div class="underline"></div>
            </div>

            <div class="input-field">
                <input type="email" name="email" id="email"
                    placeholder="Ingresa tu correo" tabindex="3">
                <div class="underline"></div>
            </div><br>
        

            <div class="input-field">
                <input type="password" name="password" id="password"
                    placeholder="Ingresa tu contraseña" autocomplete="off" tabindex="2">
                <div class="underline"></div>
            </div>

            

            <input type="submit" name="submit" value="Iniciar sesión" tabindex="4">
        </form>

        
    </main>
</body>
</html>