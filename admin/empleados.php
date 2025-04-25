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

// Procesar agregar
if (isset($_POST['agregar'])) {
  $doc = $_POST['documento'];
  $nombre = $_POST['nombre'];
  $correo = $_POST['correo'];
  $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT, array ("cost" => 12));

  $stmt = $con->prepare("INSERT INTO usuarios (id, nombres, correo, contrasena, id_rol, nit) VALUES (?, ?, ?, ?, 3, ?)");
  $stmt->execute([$doc, $nombre, $correo, $clave, $nit_empresa]);
  header("Location: empleados.php");
  exit;
}

// Procesar edición
if (isset($_POST['editar'])) {
  $idEdit = $_POST['id'];
  $nombreEdit = $_POST['nombre'];
  $correoEdit = $_POST['correo'];

  $stmt = $con->prepare("UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ? AND nit = ?");
  $stmt->execute([$nombreEdit, $correoEdit, $idEdit, $nit_empresa]);
  header("Location: empleados.php");
  exit;
}

// Procesar eliminación
if (isset($_POST['eliminar'])) {
  $idDelete = $_POST['id'];

  $stmt = $con->prepare("DELETE FROM usuarios WHERE id = ? AND nit = ?");
  $stmt->execute([$idDelete, $nit_empresa]);
  header("Location: empleados.php");
  exit;
}

// Paginación
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Consultar empleados
$sqlEmpleados = $con->prepare("
  SELECT u.*, r.rol 
  FROM usuarios u
  INNER JOIN roles r ON u.id_rol = r.id_rol
  WHERE u.nit = ? AND u.id_rol = 3
  LIMIT $limit OFFSET $offset
");
$sqlEmpleados->execute([$nit_empresa]);
$empleados = $sqlEmpleados->fetchAll(PDO::FETCH_ASSOC);

// Contar total
$sqlCount = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE nit = ? AND id_rol = 3");
$sqlCount->execute([$nit_empresa]);
$totalEmpleados = $sqlCount->fetchColumn();
$totalPages = ceil($totalEmpleados / $limit);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Empleados</title>
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
  <h3>Empleados Asociados a tu Empresa</h3>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Rol</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($empleados as $empleado) { ?>
        <tr>
          <td><?php echo $empleado['id']; ?></td>
          <td><?php echo $empleado['nombres']; ?></td>
          <td><?php echo $empleado['correo']; ?></td>
          <td><?php echo $empleado['rol']; ?></td>
          <td>
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editar<?php echo $empleado['id']; ?>">Editar</button>
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#eliminar<?php echo $empleado['id']; ?>">Eliminar</button>
          </td>
        </tr>

        <!-- Modal Editar -->
        <div class="modal fade" id="editar<?php echo $empleado['id']; ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header"><h5 class="modal-title">Editar empleado</h5></div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?php echo $empleado['id']; ?>">
                  <div class="mb-2"><label>Nombre</label>
                    <input type="text" class="form-control" name="nombre" value="<?php echo $empleado['nombres']; ?>" required>
                  </div>
                  <div class="mb-2"><label>Correo</label>
                    <input type="email" class="form-control" name="correo" value="<?php echo $empleado['correo']; ?>" required>
                  </div>
                  
                </div>
                <div class="modal-footer">
                  <button type="submit" name="editar" class="btn btn-primary">Guardar</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Modal Eliminar -->
        <div class="modal fade" id="eliminar<?php echo $empleado['id']; ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header"><h5 class="modal-title">Eliminar empleado</h5></div>
                <div class="modal-body">
                  ¿Eliminar a <strong><?php echo $empleado['nombres']; ?></strong>?
                  <input type="hidden" name="id" value="<?php echo $empleado['id']; ?>">
                </div>
                <div class="modal-footer">
                  <button type="submit" name="eliminar" class="btn btn-danger">Eliminar</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
              </form>
            </div>
          </div>
        </div>

      <?php } ?>
    </tbody>
  </table>

  <button class="btn btn-success mb-5" data-bs-toggle="modal" data-bs-target="#agregarEmpleadoModal">Agregar Nuevo Empleado</button>

  <!-- Paginación -->
  <nav>
    <ul class="pagination">
      <li class="page-item <?php echo ($page == 1) ? 'disabled' : ''; ?>">
        <a class="page-link" href="empleados.php?page=<?php echo $page - 1; ?>">Anterior</a>
      </li>
      <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
          <a class="page-link" href="empleados.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php } ?>
      <li class="page-item <?php echo ($page == $totalPages) ? 'disabled' : ''; ?>">
        <a class="page-link" href="empleados.php?page=<?php echo $page + 1; ?>">Siguiente</a>
      </li>
    </ul>
  </nav>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="agregarEmpleadoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Empleado</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2"><label>N° de Documento</label>
            <input type="text" class="form-control" name="documento" required>
          </div>
          <div class="mb-2"><label>Nombres</label>
            <input type="text" class="form-control" name="nombre" required>
          </div>
          <div class="mb-2"><label>Correo</label>
            <input type="email" class="form-control" name="correo" required>
          </div>
          <div class="mb-2"><label>Contraseña</label>
            <input type="password" class="form-control" name="clave" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="agregar" class="btn btn-success">Agregar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
