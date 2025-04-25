<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';

$conex = new database();
$con = $conex->connect();

$id = $_SESSION['doc'];
$sql = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$sql->execute([$id]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['submit'])) {
    $licencia = $_POST['licencia'];
    $duracion = $_POST['duracion'];

    $insert = $con->prepare("INSERT INTO tipo_licencia (licencia, duracion) VALUES (?, ?)");
    $insert->execute([$licencia, $duracion]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Licencia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <h2>Crear Nueva Licencia</h2>
  </div>

  <!-- Contenedor centrado y estrecho -->
  <div class="mx-auto" style="max-width: 500px;">
    <form method="POST">
      <div class="mb-3">
        <label for="licencia" class="form-label">Nombre de la licencia</label>
        <input type="text" class="form-control" id="licencia" name="licencia" required>
      </div>

      <div class="mb-3">
        <label for="duracion" class="form-label">Duración (en días)</label>
        <input type="number" class="form-control" id="duracion" name="duracion" required>
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-primary" name="submit">Registrar licencia</button>
      </div>
    </form>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
