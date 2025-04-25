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
$total_stmt = $con->prepare("SELECT COUNT(*) FROM empresa e LEFT JOIN usuarios u ON e.nit = u.nit WHERE u.id_rol = 2");
$total_stmt->execute();
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Consulta paginada
$empresa = $con->prepare("SELECT e.nit, e.nombre, u.nombres AS administrador, tl.licencia AS tipo_licencia, l.fecha_fin
                          FROM empresa e 
                          LEFT JOIN licencias l ON e.nit = l.nit
                          LEFT JOIN tipo_licencia tl ON l.id_tipo_licencia = tl.id_tipo_licencia
                          LEFT JOIN usuarios u ON e.nit = u.nit
                          WHERE u.id_rol = 2
                          LIMIT $limit OFFSET $offset");
$empresa->execute();
$empresas = $empresa->fetchAll(PDO::FETCH_ASSOC);

function generarIdLicenciaUnico($con, $longitud = 10) {
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $caracteres_length = strlen($caracteres);

    do {
        $id = '';
        for ($i = 0; $i < $longitud; $i++) {
            $id .= $caracteres[rand(0, $caracteres_length - 1)];
        }

        // Verificar que no exista en la tabla licencias
        $stmt = $con->prepare("SELECT COUNT(*) FROM licencias WHERE id_licencia = ?");
        $stmt->execute([$id]);
        $existe = $stmt->fetchColumn();

    } while ($existe > 0);

    return $id;
}


if (isset($_POST['submit'])) {
  $nit = $_POST['nit'];
  $nombre_empresa = $_POST['nombre'];

  $id_admin = $_POST['id_admin'];
  $nombres_admin = $_POST['nombres_admin'];
  $correo_admin = $_POST['correo_admin'];
  $contrasena_admin = password_hash($_POST['contrasena_admin'], PASSWORD_DEFAULT, array ("cost" => 12));

  // Verificar si ya existe la empresa
  $verificarEmpresa = $con->prepare("SELECT COUNT(*) FROM empresa WHERE nit = ?");
  $verificarEmpresa->execute([$nit]);
  $existeEmpresa = $verificarEmpresa->fetchColumn();

  // Verificar si ya existe el administrador por ID o correo
  $verificarAdmin = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ? OR correo = ?");
  $verificarAdmin->execute([$id_admin, $correo_admin]);
  $existeAdmin = $verificarAdmin->fetchColumn();

  if ($existeEmpresa > 0) {
      echo "<script>alert('La empresa con NIT $nit ya está registrada.');</script>";
  } elseif ($existeAdmin > 0) {
      echo "<script>alert('El administrador con ese documento o correo ya existe.');</script>";
  } else {
      try {
          $con->beginTransaction();

          // Insertar empresa
          $empresa1 = $con->prepare("INSERT INTO empresa (nit, nombre) VALUES (?, ?)");
          $empresa1->execute([$nit, $nombre_empresa]);

          // Insertar administrador
          $admin = $con->prepare("INSERT INTO usuarios (id, nombres, correo, contrasena, nit, id_rol) VALUES (?, ?, ?, ?, ?, 2)");
          $admin->execute([$id_admin, $nombres_admin, $correo_admin, $contrasena_admin, $nit]);

          $con->commit();
          echo "<script>alert('Empresa y administrador registrados correctamente');</script>";
          echo "<script>window.location.href='empresas.php';</script>";
      } catch (PDOException $e) {
          $con->rollBack();
          echo "<script>alert('Error al registrar: " . $e->getMessage() . "');</script>";
      }
  }
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

<div class="container">
    
  <div class="text-center mb-4">
    <h2>Registrar empresas</h2>
  </div>

  <form action="" method="POST">
  <div class="row">
    <!-- Columna Empresa -->
    <div class="col-md-6">
      <h5 class="mb-3">Datos de la Empresa</h5>

      <div class="mb-3">
        <label for="nitEmpresa" class="form-label">NIT de la empresa</label>
        <input type="number" class="form-control" name="nit" id="nitEmpresa" required>
      </div>

      <div class="mb-3">
        <label for="nombreEmpresa" class="form-label">Nombre de la empresa</label>
        <input type="text" class="form-control" name="nombre" id="nombreEmpresa" required>
      </div>


      

      
    </div>

    <!-- Columna Administrador -->
    <div class="col-md-6">
      <h5 class="mb-3">Datos del Administrador</h5>

      <div class="mb-3">
        <label for="documentoAdmin" class="form-label">Documento</label>
        <input type="number" class="form-control" name="id_admin" id="documentoAdmin" required>
      </div>

      <div class="mb-3">
        <label for="nombresAdmin" class="form-label">Nombres</label>
        <input type="text" class="form-control" name="nombres_admin" id="nombresAdmin" required>
      </div>

      <div class="mb-3">
        <label for="correoAdmin" class="form-label">Correo</label>
        <input type="email" class="form-control" name="correo_admin" id="correoAdmin" required>
      </div>

      <div class="mb-3">
        <label for="contrasenaAdmin" class="form-label">Contraseña</label>
        <input type="password" class="form-control" name="contrasena_admin" id="contrasenaAdmin" required>
      </div>
    </div>
  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-primary mt-3" name="submit">Registrar</button>
  </div>
</form>



</body>
</html>
