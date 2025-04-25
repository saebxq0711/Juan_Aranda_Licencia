<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombres = $_POST['nombres'];
    $correo = $_POST['correo'];

    $conex = new database();
    $con = $conex->connect();

    // Verifica si el usuario pertenece a una empresa
    $stmt = $con->prepare("SELECT * FROM usuarios WHERE id = ? AND id_rol = 2");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $sql = $con->prepare("UPDATE usuarios SET nombres = ?, correo = ? WHERE id = ? AND id_rol = 2");
        $sql->execute([$nombres, $correo, $id]);

        header("Location: administradores.php?mensaje=actualizado");
        exit();
    } else {
        header("Location: administradores.php?error=no_encontrado");
        exit();
    }
}
?>
