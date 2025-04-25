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
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// Total de registros
$total_stmt = $con->prepare("SELECT COUNT(*) FROM empresa e");
$total_stmt->execute();
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Consulta paginada
$empresa = $con->prepare("SELECT e.nit, e.nombre, tl.licencia AS tipo_licencia, l.fecha_fin, es.estado
                          FROM empresa e 
                          LEFT JOIN licencias l ON e.nit = l.nit
                          LEFT JOIN tipo_licencia tl ON l.id_tipo_licencia = tl.id_tipo_licencia
                          LEFT JOIN estado es ON l.id_estado = es.id_estado 
                          LIMIT $limit OFFSET $offset");
$empresa->execute();
$empresas = $empresa->fetchAll(PDO::FETCH_ASSOC);
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
    <h2>Ver o registrar empresas</h2>
  </div>

  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>NIT empresa</th>
        <th>Nombre</th>
        <th>Licencia</th>
        <th>Fecha fin</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($empresas as $fila): ?>
      <tr>
        <td><?php echo $fila['nit']; ?></td>
        <td><?php echo $fila['nombre']; ?></td>
        <td><?php echo $fila['tipo_licencia']; ?></td>
        <td><?php echo $fila['fecha_fin']; ?></td>
        <td><?php echo $fila['estado']; ?></td>
        <td>
          <?php
            // Mostrar botón según estado
            if (empty($fila['tipo_licencia'])) {
                $textoBoton = "Asignar licencia";
                $mostrarBoton = true;
            } elseif ($fila['estado'] === 'Inactivo') {
                $textoBoton = "Actualizar licencia";
                $mostrarBoton = true;
            } else {
                $mostrarBoton = false;
            }
          ?>

          <?php if ($mostrarBoton): ?>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalLicencia_<?php echo $fila['nit']; ?>">
              <?php echo $textoBoton; ?>
            </button>
          <?php endif; ?>

          <!-- Botón eliminar -->
          <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalEliminar_<?php echo $fila['nit']; ?>">
            Eliminar
          </button>

          <!-- Modal licencia -->
          <div class="modal fade" id="modalLicencia_<?php echo $fila['nit']; ?>" tabindex="-1">
            <div class="modal-dialog">
              <form method="POST" action="asignar_licencia.php" class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Asignar licencia a <?php echo $fila['nombre']; ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="nit" value="<?php echo $fila['nit']; ?>">
                  <div class="mb-3">
                    <label class="form-label">Tipo de licencia</label>
                    <select class="form-select" name="id_tipo_licencia" required>
                      <option value="">Seleccionar</option>
                      <?php
                      $licencias = $con->prepare("SELECT * FROM tipo_licencia");
                      $licencias->execute();
                      foreach ($licencias as $lic) {
                        echo "<option value='{$lic['id_tipo_licencia']}'>{$lic['licencia']}</option>";
                      }
                      ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Fecha de inicio</label>
                    <input type="datetime-local" class="form-control" name="fecha_ini" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-primary">Asignar</button>
                </div>
              </form>
            </div>
          </div>

          <!-- Modal eliminar -->
          <div class="modal fade" id="modalEliminar_<?php echo $fila['nit']; ?>" tabindex="-1">
            <div class="modal-dialog">
              <form method="POST" action="eliminar_empresa.php" class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title text-danger">Eliminar empresa</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  ¿Está seguro que desea eliminar la empresa <strong><?php echo $fila['nombre']; ?></strong> con NIT <strong><?php echo $fila['nit']; ?></strong>?
                  <input type="hidden" name="nit" value="<?php echo $fila['nit']; ?>">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
              </form>
            </div>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <a href="agregar.php" class="btn btn-primary">Agregar empresa</a>

  



  <div class="d-flex justify-content-between align-items-center mt-4">
    <a href="new_admin.php" class="btn btn-outline-success">Agregar administrador</a>

    

    <nav aria-label="...">
      <ul class="pagination mb-0">

        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
          <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>">Previous</a>
        </li>

        <?php
        $range = 2;
        $start = max(1, $page - $range);
        $end = min($total_pages, $page + $range);
        for ($i = $start; $i <= $end; $i++): ?>
          <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor; ?>

        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
          <a class="page-link" href="?page=<?php echo min($total_pages, $page + 1); ?>">Next</a>
        </li>
      </ul>
    </nav>
  </div>

  
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
