<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';
$conex = new database();
$con = $conex->connect();

$id = $_SESSION['doc'];
$sql = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$sql->execute([$id]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

// Consulta todas las licencias
$licencias = $con->prepare("SELECT * FROM tipo_licencia");
$licencias->execute();
$lista_licencias = $licencias->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Licencias</title>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Bienvenid@ <?php echo $user['nombres'] ?></a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="empresas.php">Empresas</a></li>
        <li class="nav-item"><a class="nav-link active" href="licencias.php">Licencias</a></li>
        <li class="nav-item"><a class="nav-link active" href="administradores.php">Administradores</a></li>

      </ul>
      <span class="navbar-text">
        <a href="../includes/exit.php"><button class="btn btn-danger">Cerrar sesión</button></a>
      </span>
    </div>
  </div>
</nav><br><br>

<div class="container">
  <div class="text-center mb-4">
    <h2>Licencias registradas</h2>
  </div>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre de Licencia</th>
        <th>Duración (días)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lista_licencias as $lic): ?>
      <tr>
        <td><?php echo $lic['id_tipo_licencia']; ?></td>
        <td><?php echo $lic['licencia']; ?></td>
        <td><?php echo $lic['duracion']; ?> días</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="crear_licencia.php" class="btn btn-success">Agregar nueva licencia</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
