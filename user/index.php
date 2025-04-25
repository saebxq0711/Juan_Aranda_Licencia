<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';

$conex = new database();
$con = $conex->connect();

$id = $_SESSION['doc'];
$nit_empresa = $_SESSION['nit_'];

// Manejar devolución
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['devolver_dispositivo'])) {
    $idAsignacion = $_POST['dispositivo_id'];
    $fecha = date('Y-m-d H:i:s');

    // Obtener el ID del dispositivo desde la asignación
    $stmtGetDispositivo = $con->prepare("SELECT dispositivo_id FROM asignaciones WHERE id = ? AND id_empleado = ?");
    $stmtGetDispositivo->execute([$idAsignacion, $id]);
    $dispositivoRow = $stmtGetDispositivo->fetch(PDO::FETCH_ASSOC);

    if ($dispositivoRow) {
        $dispositivoId = $dispositivoRow['dispositivo_id'];

        // Actualizar asignación
        $stmtDevolver = $con->prepare("UPDATE asignaciones SET fecha_devolucion = ? WHERE id = ? AND id_empleado = ?");
        $stmtDevolver->execute([$fecha, $idAsignacion, $id]);

        // Cambiar estado del dispositivo a disponible (3)
        $dispositivo_devolver = $con->prepare("UPDATE dispositivos SET id_estado = 3 WHERE id = ?");
        $dispositivo_devolver->execute([$dispositivoId]);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Obtener datos del usuario
$sql = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$sql->execute([$id]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

// Configuración de la paginación
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Obtener dispositivos asignados al usuario
$stmt = $con->prepare("
    SELECT a.id, d.nombre, d.marca, d.modelo, a.fecha_asignacion, a.fecha_devolucion
    FROM asignaciones a
    INNER JOIN dispositivos d ON a.dispositivo_id = d.id
    WHERE a.id_empleado = ? AND a.fecha_devolucion IS NULL
    LIMIT ? OFFSET ?
");
$stmt->execute([$id, $limit, $offset]);
$dispositivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total para paginación
$stmtTotal = $con->prepare("
    SELECT COUNT(*) AS total
    FROM asignaciones a
    WHERE a.id_empleado = ? AND a.fecha_devolucion IS NULL
");
$stmtTotal->execute([$id]);
$totalDispositivos = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalDispositivos / $limit);

$stmtDisponibles = $con->prepare("
    SELECT * FROM dispositivos 
    WHERE id_estado = 3 
    AND nit = ?
    AND id NOT IN (
        SELECT dispositivo_id 
        FROM asignaciones 
        WHERE id_empleado = ? 
        AND fecha_devolucion IS NULL
    )
");
$stmtDisponibles->execute([$nit_empresa, $id]);

$dispositivosDisponibles = $stmtDisponibles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Bienvenid@ <?php echo $user['nombres']; ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
      </ul>
      <span class="navbar-text">
        <a href="../includes/exit.php"><button class="btn btn-danger">Cerrar sesión</button></a>
      </span>
    </div>
  </div>
</nav>

<div class="container text-center my-4">
  <h2 class="mb-4">Resumen de dispositivos asignados</h2>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Dispositivo</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Fecha de Asignación</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($dispositivos as $dispositivo): ?>
        <tr>
          <td><?= $dispositivo['nombre'] ?></td>
          <td><?= $dispositivo['marca'] ?></td>
          <td><?= $dispositivo['modelo'] ?></td>
          <td><?= $dispositivo['fecha_asignacion'] ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">
              <button type="submit" name="devolver_dispositivo" class="btn btn-danger"
                onclick="return confirm('¿Estás seguro que deseas devolver este dispositivo?')">
                Devolver
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#solicitarModal">Solicitar Dispositivo</button>

  <nav>
    <ul class="pagination">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page - 1 ?>">Anterior</a>
      </li>
      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page + 1 ?>">Siguiente</a>
      </li>
    </ul>
  </nav>
</div>

<!-- Modal Solicitud -->
<div class="modal fade" id="solicitarModal" tabindex="-1" aria-labelledby="solicitarModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Solicitar Dispositivo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="solicitar_dispositivo.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Seleccionar Dispositivo</label>
            <select name="dispositivo_id" class="form-select">
              <?php foreach ($dispositivosDisponibles as $d): ?>
                <option value="<?= $d['id'] ?>"><?= $d['nombre'] ?> - <?= $d['marca'] ?> - <?= $d['modelo'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-success">Solicitar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
