<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';
$conex = new database();
$con = $conex->connect();

$id = $_SESSION['doc'];
$sql = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$sql->execute([$id]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

// Paginación
$empresasPorPagina = 3;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $empresasPorPagina;

// Total de empresas
$totalEmpresasStmt = $con->query("SELECT COUNT(DISTINCT nombre) AS total FROM empresa");
$totalEmpresas = $totalEmpresasStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalEmpresas / $empresasPorPagina);

$query = $con->prepare("
  SELECT e.nit, e.nombre AS empresa, u.id, u.nombres, u.correo
  FROM empresa e
  INNER JOIN usuarios u ON e.nit = u.nit
  WHERE u.id_rol = 2
  ORDER BY e.nombre
  LIMIT :limite OFFSET :offset
");
$query->bindValue(':limite', $empresasPorPagina, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$datos = $query->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por empresa
$empresas = [];
foreach ($datos as $fila) {
    $empresas[$fila['empresa']][] = $fila;
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

<div class="container mt-4">
  <h2 class="text-center mb-5">Administradores por empresa</h2>

  <div class="row">
    <?php foreach ($empresas as $nombreEmpresa => $admins): ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><?php echo $nombreEmpresa; ?></h5>
          </div>
          <div class="card-body">
            <?php foreach ($admins as $admin): ?>
              <div class="border p-2 mb-2 rounded">
                <strong><?php echo $admin['nombres']; ?></strong><br>
                Documento: <?php echo $admin['id']; ?><br>

                <!-- Botones -->
                <div class="mt-2">
                  <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalEditar_<?php echo $admin['id']; ?>">Editar</button>
                  <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar_<?php echo $admin['id']; ?>">Eliminar</button>
                </div>

                <!-- Modal Editar -->
                <div class="modal fade" id="modalEditar_<?php echo $admin['id']; ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <form method="POST" action="editar_admin.php" class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Editar administrador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                        <div class="mb-3">
                          <label class="form-label">Nombres</label>
                          <input type="text" class="form-control" name="nombres" value="<?php echo $admin['nombres']; ?>" required>
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Correo</label>
                          <input type="text" class="form-control" name="correo" value="<?php echo $admin['correo']; ?>" required>
                        </div>
                        
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Modal Eliminar -->
                <div class="modal fade" id="modalEliminar_<?php echo $admin['id']; ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <form method="POST" action="eliminar_admin.php" class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title text-danger">Eliminar administrador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        ¿Está seguro de eliminar a <strong><?php echo $admin['nombres']; ?></strong>?
                        <input type="hidden" name="id" value="<?php echo $admin['id']; ?>">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                      </div>
                    </form>
                  </div>
                </div>

              </div>
            <?php endforeach; ?>
          </div>
            
        </div>
      </div>
    <?php endforeach; ?>
  </div>
    <div class="text-center mt-4">
        <nav>
            <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>