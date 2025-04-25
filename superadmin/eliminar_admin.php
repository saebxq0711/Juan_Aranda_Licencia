<?php
require_once('../conexion/conexion.php');
include '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $conex = new database();
    $con = $conex->connect();

    // Verifica que el usuario es administrador (id_rol = 2)
    $stmt = $con->prepare("SELECT * FROM usuarios WHERE id = ? AND id_rol = 2");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $sql = $con->prepare("DELETE FROM usuarios WHERE id = ? AND id_rol = 2");
        $sql->execute([$id]);

        header("Location: administradores.php?mensaje=eliminado");
        exit();
    } else {
        header("Location: administradores.php?error=no_encontrado");
        exit();
    }
}
?>
