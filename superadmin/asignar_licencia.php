<?php
require_once('../conexion/conexion.php');
$conex = new database();
$con = $conex->connect();

// Función para generar un ID de licencia único de hasta 10 caracteres
function generarIdLicencia($con) {
    do {
        $id = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 10);
        $stmt = $con->prepare("SELECT COUNT(*) FROM licencias WHERE id_licencia = ?");
        $stmt->execute([$id]);
        $existe = $stmt->fetchColumn();
    } while ($existe > 0);
    return $id;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nit = $_POST['nit'];
    $id_tipo_licencia = $_POST['id_tipo_licencia'];
    $fecha_ini = $_POST['fecha_ini'];

    // Generar ID licencia único
    $id_licencia = generarIdLicencia($con);

    // Obtener duración del tipo de licencia
    $stmt = $con->prepare("SELECT duracion FROM tipo_licencia WHERE id_tipo_licencia = ?");
    $stmt->execute([$id_tipo_licencia]);
    $duracion = $stmt->fetchColumn();

    if ($duracion === false) {
        die("Tipo de licencia no válido.");
    }

    // Calcular fecha de fin
    $fecha_inicio = new DateTime($fecha_ini);
    $fecha_fin = clone $fecha_inicio;
    $fecha_fin->modify("+$duracion days");

    // Insertar la nueva licencia
    $insert = $con->prepare("INSERT INTO licencias (id_licencia, nit, id_tipo_licencia, fecha_ini, fecha_fin, id_estado) 
                             VALUES (?, ?, ?, ?, ?, 1)");
    $insert->execute([
        $id_licencia,
        $nit,
        $id_tipo_licencia,
        $fecha_inicio->format('Y-m-d H:i:s'),
        $fecha_fin->format('Y-m-d H:i:s')
    ]);

    header("Location: empresas.php");
    exit();
} else {
    echo "Acceso denegado.";
}
