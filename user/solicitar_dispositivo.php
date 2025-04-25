<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';

$conex = new database();
$con = $conex->connect();

$idEmpleado = $_SESSION['doc'];
$fecha = date('Y-m-d H:i:s');

// Verifica si viene el ID del dispositivo desde el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dispositivo_id'])) {
    $dispositivoId = $_POST['dispositivo_id'];

    // Asignar dispositivo
    $stmtAsignar = $con->prepare("
        INSERT INTO asignaciones (id_empleado, dispositivo_id, fecha_asignacion)
        VALUES (?, ?, ?)
    ");
    $stmtAsignar->execute([$idEmpleado, $dispositivoId, $fecha]);

    // Cambiar estado del dispositivo a asignado (2)
    $stmtActualizarEstado = $con->prepare("UPDATE dispositivos SET id_estado = 4 WHERE id = ?");
    $stmtActualizarEstado->execute([$dispositivoId]);

    // Redirigir de vuelta al panel
    header("Location: index.php");
    exit;
} else {
    // Redirigir si no viene el dato correcto
    header("Location: index.php?error=1");
    exit;
}
?>
