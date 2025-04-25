<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';

$conex = new database();
$con = $conex->connect();

$id = $_SESSION['doc'];
$nit_empresa = $_SESSION['nit_'];

// Obtener datos del usuario
$sql = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$sql->execute([$id]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

// Total de dispositivos asignados a empleados de la empresa (JOIN con usuarios)
$stmt1 = $con->prepare("
    SELECT COUNT(*) AS total_dispositivos
    FROM asignaciones a
    INNER JOIN usuarios u ON a.id_empleado = u.id
    WHERE u.nit = ?
");
$stmt1->execute([$nit_empresa]);
$total_dispositivos = $stmt1->fetch(PDO::FETCH_ASSOC)['total_dispositivos'];

// Total de empleados únicos con dispositivos asignados
$stmt2 = $con->prepare("
    SELECT COUNT(DISTINCT a.id_empleado) AS total_trabajadores
    FROM asignaciones a
    INNER JOIN usuarios u ON a.id_empleado = u.id
    WHERE u.nit = ?
");
$stmt2->execute([$nit_empresa]);
$total_trabajadores = $stmt2->fetch(PDO::FETCH_ASSOC)['total_trabajadores'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Panel de Administración</title>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Bienvenid@ <?php echo $user['nombres'] ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="dispositivos.php">Dispositivos</a></li>
        <li class="nav-item"><a class="nav-link" href="empleados.php">Empleados</a></li>
        <li class="nav-item"><a class="nav-link" href="asignaciones.php">Asignaciones</a></li>
      </ul>
      <span class="navbar-text">
        <a href="../includes/exit.php"><button class="btn btn-danger">Cerrar sesión</button></a>
      </span>
    </div>
  </div>
</nav>

<br><br>

<div class="container text-center my-4">
  <h2 class="mb-4">Resumen de asignaciones</h2>
  <div class="row justify-content-center">
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm border-success">
        <div class="card-body">
          <h5 class="card-title text-success">Dispositivos Asignados</h5>
          <p class="card-text fs-3"><?php echo $total_dispositivos; ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm border-primary">
        <div class="card-body">
          <h5 class="card-title text-primary">Trabajadores con Dispositivos</h5>
          <p class="card-text fs-3"><?php echo $total_trabajadores; ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
