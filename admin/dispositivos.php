<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';
require '../vendor/autoload.php'; // Para el generador de códigos de barra

use Picqer\Barcode\BarcodeGeneratorPNG;

$conex = new database();
$con = $conex->connect();

$id = $_SESSION['doc'];
$nit_empresa = $_SESSION['nit_'];

$sql = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$sql->execute([$id]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

// Paginación
$limit = 5; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sqlDispositivos = $con->prepare("
  SELECT d.*, e.estado AS estado 
  FROM dispositivos d 
  INNER JOIN estado e ON d.id_estado = e.id_estado
  LIMIT $limit OFFSET $offset
");
$sqlDispositivos->execute();
$dispositivos = $sqlDispositivos->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de dispositivos para calcular el número total de páginas
$sqlCount = $con->prepare("SELECT COUNT(*) FROM dispositivos");
$sqlCount->execute();
$totalDispositivos = $sqlCount->fetchColumn();
$totalPages = ceil($totalDispositivos / $limit);

$sqlEstados = $con->prepare("SELECT * FROM estado");
$sqlEstados->execute();
$estados = $sqlEstados->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $nombre = $_POST['nombre'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $serial = $_POST['serial'];
    $id_estado = $_POST['estado'];
    $barras = $_POST['barras'];
    $fecha_registro = date("Y-m-d H:i:s");

    $stmtInsert = $con->prepare("INSERT INTO dispositivos (nombre, marca, modelo, serial, id_estado, fecha_registro, nit) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtInsert->execute([$nombre, $marca, $modelo, $serial, $id_estado, $fecha_registro, $nit_empresa]);

    $id_dispositivo = $con->lastInsertId();


    if (empty($barras)) {
      $barras = "DISP-" . str_pad($id_dispositivo, 6, rand(), STR_PAD_LEFT);
      
    }

    $verifica = $con->prepare("SELECT COUNT(*) FROM dispositivos WHERE codigo_barra = ?");
    $verifica->execute([$barras]);
    if ($verifica->fetchColumn() > 0) {
        echo "<script>alert('El código de barras ya existe. Intenta con otro.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    if (!file_exists('../barcodes/')) {
        mkdir('../barcodes/', 0777, true);
    }

    $stmtUpdate = $con->prepare("UPDATE dispositivos SET codigo_barra = ? WHERE id = ?");
    $stmtUpdate->execute([$barras, $id_dispositivo]);
    

    $generator = new BarcodeGeneratorPNG();
    $barcode = $generator->getBarcode($barras, $generator::TYPE_CODE_128);
    file_put_contents("../barcodes/{$barras}.png", $barcode);

    echo "<script>alert('Dispositivo creado con éxito');</script>";
    echo "<script>window.location.href='dispositivos.php';</script>";
}

// Actualizar dispositivo
if (isset($_POST['editar'])) {
    $idDispositivo = $_POST['id'];
    $nombre = $_POST['nombre'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $serial = $_POST['serial'];
    $estado = $_POST['estado'];

    $update = $con->prepare("UPDATE dispositivos SET nombre = ?, marca = ?, modelo = ?, serial = ?, id_estado = ? WHERE id = ?");
    $update->execute([$nombre, $marca, $modelo, $serial, $estado, $idDispositivo]);

    echo "<script>alert('Dispositivo actualizado'); window.location.href='dispositivos.php';</script>";
}

// Eliminar dispositivo
if (isset($_POST['eliminar'])) {
    $idEliminar = $_POST['id'];
    $delete = $con->prepare("DELETE FROM dispositivos WHERE id = ?");
    $delete->execute([$idEliminar]);
    echo "<script>alert('Dispositivo eliminado'); window.location.href='dispositivos.php';</script>";
}
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
  <h3>Dispositivos Registrados</h3>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Código de barras</th>
        <th>Nombre</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Serial</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($dispositivos as $dispositivo) { ?>
        <tr>
          <td><?php echo $dispositivo['id']; ?></td>
          <td>
            <?php if (!empty($dispositivo['codigo_barra'])) { ?>
              <img src="../barcodes/<?php echo $dispositivo['codigo_barra']; ?>.png" width="150"><br>
              <a href="../barcodes/<?php echo $dispositivo['codigo_barra']; ?>.png" download>Descargar</a>
            <?php } else { echo '<em>Sin código</em>'; } ?>
          </td>
          <td><?php echo $dispositivo['nombre']; ?></td>
          <td><?php echo $dispositivo['marca']; ?></td>
          <td><?php echo $dispositivo['modelo']; ?></td>
          <td><?php echo $dispositivo['serial']; ?></td>
          <td><?php echo $dispositivo['estado']; ?></td>
          <td>
            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editar<?php echo $dispositivo['id']; ?>">Editar</button>
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#eliminar<?php echo $dispositivo['id']; ?>">Eliminar</button>
          </td>
        </tr>

        <!-- Modal Editar -->
        <div class="modal fade" id="editar<?php echo $dispositivo['id']; ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header"><h5 class="modal-title">Editar dispositivo</h5></div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?php echo $dispositivo['id']; ?>">
                  <div class="mb-2"><label>Nombre</label>
                    <input type="text" class="form-control" name="nombre" value="<?php echo $dispositivo['nombre']; ?>" required>
                  </div>
                  <div class="mb-2"><label>Marca</label>
                    <input type="text" class="form-control" name="marca" value="<?php echo $dispositivo['marca']; ?>" required>
                  </div>
                  <div class="mb-2"><label>Modelo</label>
                    <input type="text" class="form-control" name="modelo" value="<?php echo $dispositivo['modelo']; ?>" required>
                  </div>
                  <div class="mb-2"><label>Serial</label>
                    <input type="text" class="form-control" name="serial" value="<?php echo $dispositivo['serial']; ?>" required>
                  </div>
                  <div class="mb-2"><label>Estado</label>
                    <select name="estado" class="form-select">
                        <?php foreach ($estados as $estado) {
                        if ($estado['id_estado'] > 2) { ?>
                            <option value="<?php echo $estado['id_estado']; ?>"><?php echo $estado['estado']; ?></option>
                        <?php } } ?>
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="editar" class="btn btn-primary">Guardar cambios</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Modal Eliminar -->
        <div class="modal fade" id="eliminar<?php echo $dispositivo['id']; ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header"><h5 class="modal-title">Eliminar dispositivo</h5></div>
                <div class="modal-body">
                  <p>¿Estás seguro de eliminar el dispositivo <strong><?php echo $dispositivo['nombre'];?>-<?php echo $dispositivo['serial']; ?></strong>?</p>
                  <input type="hidden" name="id" value="<?php echo $dispositivo['id']; ?>">
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

  
    <a href="#formulario" class="btn btn-success mb-4" data-bs-toggle="modal">Agregar dispositivo</a>
  

  <!-- Paginación -->
  <nav>
    <ul class="pagination">
      <li class="page-item <?php echo ($page == 1) ? 'disabled' : ''; ?>">
        <a class="page-link" href="dispositivos.php?page=<?php echo $page - 1; ?>">Anterior</a>
      </li>
      <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="dispositivos.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
      <?php } ?>
      <li class="page-item <?php echo ($page == $totalPages) ? 'disabled' : ''; ?>">
        <a class="page-link" href="dispositivos.php?page=<?php echo $page + 1; ?>">Siguiente</a>
      </li>
    </ul>
  </nav>

  

  <!-- Modal Crear dispositivo -->
  <div class="modal fade" id="formulario" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header"><h5 class="modal-title">Crear dispositivo</h5></div>
          <div class="modal-body">
            <div class="mb-2"><label>Nombre</label>
              <input type="text" class="form-control" name="nombre" required>
            </div>
            <div class="mb-2"><label>Marca</label>
              <input type="text" class="form-control" name="marca" required>
            </div>
            <div class="mb-2"><label>Modelo</label>
              <input type="text" class="form-control" name="modelo" required>
            </div>
            <div class="mb-2"><label>Serial</label>
              <input type="text" class="form-control" name="serial" required>
            </div>
            <div class="mb-2"><label>Codigo de barras</label>
              <input type="text" class="form-control" name="barras">
            </div>
            <div class="mb-2"><label>Estado</label>
              <select name="estado" class="form-select">
                <?php foreach ($estados as $estado) {
                    if ($estado['id_estado'] == 3) { ?>
                        <option value="<?php echo $estado['id_estado']; ?>"><?php echo $estado['estado']; ?></option>
                    <?php } } ?>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="crear" class="btn btn-success">Crear</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
