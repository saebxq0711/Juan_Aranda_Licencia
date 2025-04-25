<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/1ab94d0eba.js" crossorigin="anonymous"></script>
    <title>Login Form</title>
    <link rel="stylesheet" href="../../style/style.css">
</head>
<body>
    <main class="container">
        <h2>Iniciar sesión</h2>
        <form action="../../includes/start.php" method="POST">
            <input type="hidden" name="modo" value="superadmin"> 
            <div class="input-field">
                <input type="number" name="id" id="id" placeholder="Ingresa tu numero de documento">
                <div class="underline"></div>
            </div><br>

            <div class="input-field">
                <input type="password" name="password" id="password" placeholder="Ingresa tu contraseña" autocomplete="off">
                <div class="underline"></div>
            </div>

            <input type="submit" name="submit" value="Iniciar sesión">
        </form>


        
    </main>
</body>
</html>