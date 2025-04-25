<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';
$conex = new database();
$con = $conex->connect();


$id = $_SESSION['doc'];


$sql = $con->prepare("SELECT * FROM usuarios WHERE id=$id");
$sql->execute();

$user = $sql->fetch(PDO::FETCH_ASSOC);


if (isset($_POST ['submit'])) {
    $id = $_POST ['id_admin'];
    $nombres_admin = $_POST ['nombres_admin'];
    $correo_admin = $_POST ['correo_admin'];
    $contraseña_admin = password_hash($_POST ['contrasena_admin'], PASSWORD_DEFAULT, array ("cost" => 12));
    $nit = $_POST ['nitEmpresa'];

    $admin = $con->prepare("INSERT INTO usuarios (id, nombres, correo, contrasena, nit, id_rol) VALUES ($id, '$nombres_admin', '$correo_admin', '$contraseña_admin', $nit, 2)");
    $admin->execute();

}


?>




<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Superadmin</title>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Bienvenid@ <?php echo $user['nombres'] ?></a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="empresas.php">Empresas</a></li>
        <li class="nav-item"><a class="nav-link" href="licencias.php">Licencias</a></li>
        <li class="nav-item"><a class="nav-link" href="administradores.php">Administradores</a></li>

      </ul>
      <span class="navbar-text">
        <a href="../includes/exit.php"><button class="btn btn-danger">Cerrar sesión</button></a>
      </span>
    </div>
  </div>
</nav><br><br>

<div class="container">
    
  <div class="text-center mb-4">
    <h2>Registrar administrador</h2>
  </div>

  <form action="" method="POST">
  <div class="d-flex justify-content-center">
    <!-- Columna Administrador -->
    <div class="col-md-6">
      <h5 class="mb-3">Datos del Administrador</h5>

      <div class="mb-3">
        <label for="documentoAdmin" class="form-label">Documento</label>
        <input type="number" class="form-control" name="id_admin" id="documentoAdmin" required>
      </div>

      <div class="mb-3">
        <label for="nombresAdmin" class="form-label">Nombres</label>
        <input type="text" class="form-control" name="nombres_admin" id="nombresAdmin" required>
      </div>

      <div class="mb-3">
        <label for="correoAdmin" class="form-label">Correo</label>
        <input type="email" class="form-control" name="correo_admin" id="correoAdmin" required>
      </div>

      <div class="mb-3">
        <label for="contrasenaAdmin" class="form-label">Contraseña</label>
        <input type="password" class="form-control" name="contrasena_admin" id="contrasenaAdmin" required>
      </div>

      <div class="mb-3">
        <label for="empresa" class="form-label">Empresa</label>
        <select name="nitEmpresa" id="nitEmpresa" class="form-select">
            <option value="">Seleccionar empresa</option>
            <?php

                $empresas = $con->prepare("SELECT * FROM empresa");
                $empresas->execute();

                foreach ($empresas AS $empresa) {
                    echo "<option value='{$empresa['nit']}'>{$empresa['nombre']}</option>"; 
                }
            ?>
        </select>
      </div>

    </div>
  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary mt-3" name="submit">Registrar</button>
  </div>
</form>



</body>
</html>
