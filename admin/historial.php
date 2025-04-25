<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';

$conex = new database();
$con = $conex->connect();

$id = $_SESSION['doc'];
$nit_empresa = $_SESSION['nit_'];

// Obtener los datos del usuario actual
$sql = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$sql->execute([$id]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

// Paginación
$registrosPorPagina = 5;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Contar total de registros
$sqlTotal = $con->prepare("SELECT COUNT(*) FROM asignaciones a INNER JOIN usuarios u ON u.id = a.id_empleado WHERE u.nit = ?");
$sqlTotal->execute([$nit_empresa]);
$totalRegistros = $sqlTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Consulta paginada
$sqlHistorial = $con->prepare("
  SELECT 
    u.id AS id_empleado,
    u.nombres AS nombre_empleado,
    d.id AS id_dispositivo,
    d.nombre AS nombre_dispositivo,
    d.serial,
    a.fecha_asignacion,
    a.fecha_devolucion
  FROM usuarios u
  INNER JOIN asignaciones a ON u.id = a.id_empleado
  INNER JOIN dispositivos d ON a.dispositivo_id = d.id
  WHERE u.nit = ?
  LIMIT ? OFFSET ?
");
$sqlHistorial->bindParam(1, $nit_empresa, PDO::PARAM_STR);
$sqlHistorial->bindParam(2, $registrosPorPagina, PDO::PARAM_INT);
$sqlHistorial->bindParam(3, $offset, PDO::PARAM_INT);
$sqlHistorial->execute();
$historial = $sqlHistorial->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Asignaciones</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Bienvenid@ <?php echo $user['nombres'] ?></a>
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

<div class="container my-5">
  <a href="asignaciones.php"><button class="btn btn-info mb-4">Regresar</button></a>
  <h3>Historial de Asignaciones</h3>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID Empleado</th>
        <th>Nombre Empleado</th>
        <th>ID Dispositivo</th>
        <th>Nombre Dispositivo</th>
        <th>Serial</th>
        <th>Fecha Asignación</th>
        <th>Fecha Devolución</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($historial as $asignacion) { ?>
        <tr>
          <td><?php echo $asignacion['id_empleado']; ?></td>
          <td><?php echo $asignacion['nombre_empleado']; ?></td>
          <td><?php echo $asignacion['id_dispositivo']; ?></td>
          <td><?php echo $asignacion['nombre_dispositivo']; ?></td>
          <td><?php echo $asignacion['serial']; ?></td>
          <td><?php echo $asignacion['fecha_asignacion']; ?></td>
          <td><?php echo $asignacion['fecha_devolucion'] ? $asignacion['fecha_devolucion'] : 'Pendiente'; ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <!-- Paginación -->
  <nav>
  <ul class="pagination justify-content-center">
    <!-- Botón Anterior -->
    <li class="page-item <?php echo ($paginaActual == 1) ? 'disabled' : ''; ?>">
      <a class="page-link" href="?pagina=<?php echo $paginaActual - 1; ?>">Anterior</a>
    </li>

    <!-- Enlaces de las páginas -->
    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
      <li class="page-item <?php echo ($i === $paginaActual) ? 'active' : ''; ?>">
        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
      </li>
    <?php endfor; ?>

    <!-- Botón Siguiente -->
    <li class="page-item <?php echo ($paginaActual == $totalPaginas) ? 'disabled' : ''; ?>">
      <a class="page-link" href="?pagina=<?php echo $paginaActual + 1; ?>">Siguiente</a>
    </li>
  </ul>
</nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
