<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';

$conex = new database();
$con = $conex->connect();

$id = $_SESSION['doc'];
$nit_empresa = $_SESSION['nit_'];

// Obtener los datos del usuario actual (administrador)
$sql = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$sql->execute([$id]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

// Paginación
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Consultar empleados con dispositivos asignados y sin fecha de devolución
$sqlEmpleados = $con->prepare("
  SELECT 
    u.id AS id_empleado,
    u.nombres AS nombre_empleado,
    d.id AS id_dispositivo,
    d.nombre AS nombre_dispositivo,
    d.marca,
    d.modelo,
    d.serial,
    a.fecha_asignacion,
    a.fecha_devolucion
  FROM usuarios u
  INNER JOIN asignaciones a ON u.id = a.id_empleado
  INNER JOIN dispositivos d ON a.dispositivo_id = d.id
  WHERE u.nit = ? 
    AND u.id_rol = 3 
    AND a.fecha_devolucion IS NULL -- Solo asignaciones activas (sin fecha de devolución)
  LIMIT $limit OFFSET $offset
");
$sqlEmpleados->execute([$nit_empresa]);
$empleados = $sqlEmpleados->fetchAll(PDO::FETCH_ASSOC);

// Contar total para paginación
$sqlCount = $con->prepare("
  SELECT COUNT(*) 
  FROM usuarios u
  INNER JOIN asignaciones a ON u.id = a.id_empleado
  WHERE u.nit = ? 
    AND u.id_rol = 3 
    AND a.fecha_devolucion IS NULL
");
$sqlCount->execute([$nit_empresa]);
$totalEmpleados = $sqlCount->fetchColumn();
$totalPages = ceil($totalEmpleados / $limit);

// Obtener el código de barras escaneado y consultar el dispositivo
$codigo_barras = isset($_GET['codigo_barras']) ? $_GET['codigo_barras'] : '';
$id_dispositivo = is_numeric($codigo_barras) ? (int)$codigo_barras : 0; // 0 si no es numérico

// Consultar el dispositivo correspondiente al código de barras
if ($codigo_barras) {
    $sqlDispositivo = $con->prepare("
      SELECT 
        u.nombres AS nombre_empleado,
        d.nombre AS nombre_dispositivo,
        d.marca,
        d.modelo,
        d.serial,
        d.codigo_barra,
        d.fecha_registro,
        a.fecha_asignacion
        
      FROM usuarios u
      INNER JOIN asignaciones a ON u.id = a.id_empleado
      INNER JOIN dispositivos d ON a.dispositivo_id = d.id
      WHERE u.nit = ? 
        AND u.id_rol = 3 
        AND a.fecha_devolucion IS NULL
        AND (d.codigo_barra LIKE ? OR d.id=?) -- Buscar por código de barras, serial o nombre
    ");
    $codigo_barras_like = "%$codigo_barras%"; // Usamos LIKE para permitir coincidencias parciales
    $sqlDispositivo->execute([$nit_empresa, $codigo_barras_like, $id_dispositivo]);
    $dispositivo = $sqlDispositivo->fetch(PDO::FETCH_ASSOC);

    if ($dispositivo) {
        // Si encontramos el dispositivo, devolverlo como JSON
        echo json_encode($dispositivo);
        exit;
    } else {
        // Si no se encuentra el dispositivo, devolver un mensaje vacío
        echo json_encode(null);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Asignaciones Activas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
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
  <h3>Empleados Asociados a tu Empresa con Dispositivos Asignados</h3>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID Empleado</th>
        <th>Nombre</th>
        <th>ID Dispositivo</th>
        <th>Nombre Dispositivo</th>
        <th>Serial</th>
        <th>Fecha Asignación</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($empleados as $empleado) { ?>
        <tr>
          <td><?php echo $empleado['id_empleado']; ?></td>
          <td><?php echo $empleado['nombre_empleado']; ?></td>
          <td><?php echo $empleado['id_dispositivo']; ?></td>
          <td><?php echo $empleado['nombre_dispositivo']; ?></td>
          <td><?php echo $empleado['serial']; ?></td>
          <td><?php echo $empleado['fecha_asignacion']; ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <div class="d-flex justify-content-between mb-5">
    <a href="historial.php" class="btn btn-info">Ver Historial de Asignaciones</a>
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalDispositivo">Buscar dispositivo</button>
  </div>

  <!-- Paginación -->
  <nav>
    <ul class="pagination">
      <li class="page-item <?php echo ($page == 1) ? 'disabled' : ''; ?>">
        <a class="page-link" href="asignaciones.php?page=<?php echo $page - 1; ?>">Anterior</a>
      </li>
      <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
          <a class="page-link" href="asignaciones.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php } ?>
      <li class="page-item <?php echo ($page == $totalPages) ? 'disabled' : ''; ?>">
        <a class="page-link" href="asignaciones.php?page=<?php echo $page + 1; ?>">Siguiente</a>
      </li>
    </ul>
  </nav>
</div>

<!-- Formulario invisible que captura el código de barras -->
<form id="barcode-form" method="GET" action="asignaciones.php" style="display: none;" name="barcode">
    <input type="text" name="codigo_barras" id="codigo_barras" autocomplete="off" />
</form>

<!-- Modal de resultados -->
<div class="modal fade" id="modalDispositivo" tabindex="-1" aria-labelledby="modalDispositivoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDispositivoLabel">Escanea el Código de Barras</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Escanea el código de barras del dispositivo.</p>
        <input type="text" id="codigo_barras_input" name="codigo_barras_input" class="form-control" autofocus placeholder="Escanear código de barras">
        <div id="resultado_dispositivo" class="mt-3">
          <!-- Aquí se mostrarán los datos del dispositivo -->
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Cuando se escanea el código de barras
document.getElementById('codigo_barras_input').addEventListener('input', function(event) {
  if (event.target.value) {
    var codigoBarras = event.target.value;
    // Enviar el código de barras para buscar el dispositivo
    fetch('?codigo_barras=' + codigoBarras)
      .then(response => response.json())
      .then(data => {
        var resultadoDiv = document.getElementById('resultado_dispositivo');
        if (data) {
          // Mostrar los detalles del dispositivo
          resultadoDiv.innerHTML = `
            <strong>Empleado:</strong> ${data.nombre_empleado}<br>
            <strong>Dispositivo:</strong> ${data.nombre_dispositivo}<br>
            <strong>Marca:</strong> ${data.marca}<br>
            <strong>Modelo:</strong> ${data.modelo}<br>
            <strong>Serial:</strong> ${data.serial}<br>
            <strong>Fecha de Asignación:</strong> ${data.fecha_asignacion}<br>
            
          `;
        } else {
          resultadoDiv.innerHTML = '<p>No se encontró el dispositivo.</p>';
        }
      })
      .catch(() => {
        alert('Hubo un error al buscar el dispositivo.');
      });
  }
});
</script>

</body>
</html>
