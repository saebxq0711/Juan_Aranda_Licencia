<?php

require_once('../conexion/conexion.php');
include '../includes/session.php';
$conex = new database();
$con = $conex->connect();


$id = $_SESSION['doc'];


$sql = $con->prepare("SELECT * FROM usuarios WHERE id=$id");
$sql->execute();

$user = $sql->fetch(PDO::FETCH_ASSOC);

$empresa = $con->prepare("SELECT e.nit, e.nombre, tl.licencia AS tipo_licencia, l.fecha_fin
                          FROM empresa e 
                          LEFT JOIN licencias l ON e.nit = l.nit
                          LEFT JOIN tipo_licencia tl ON l.id_tipo_licencia = tl.id_tipo_licencia
                          WHERE l.id_estado = 1");
$empresa->execute();
$empresas = $empresa->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <title>Superadmin</title>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Bienvenid@ <?php echo $user['nombres'] ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link"  href="index.php">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="empresas.php">Empresas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="licencias.php">Licencias</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="administradores.php">Administradores</a>
        </li>
      </ul>
      <span class="navbar-text">
        <a href="../includes/exit.php"><button class="btn btn-danger">Cerrar sesion</button></a>
      </span>
    </div>
  </div>
</nav><br><br><br>


<div class="d-flex justify-content-center">
    <h1>Empresas activas</h1>
</div>


<div class="container">
<table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>NIT empresa</th>
        <th>Nombre</th>
        <th>Licencia</th>
        <th>Fecha fin</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($empresas as $fila): ?>
      <tr>
        <td><?php echo $fila['nit']; ?></td>
        <td><?php echo $fila['nombre']; ?></td>
        <td><?php echo $fila['tipo_licencia']; ?></td>
        <td><?php echo $fila['fecha_fin']; ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>


</body>
</html>