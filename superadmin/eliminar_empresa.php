<?php
require_once('../conexion/conexion.php');
$conex = new database();
$con = $conex->connect();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nit'])) {
    $nit = $_POST['nit'];

    $con->prepare("DELETE FROM licencias WHERE nit = ?")->execute([$nit]);
    $con->prepare("DELETE FROM usuarios WHERE nit = ?")->execute([$nit]);

    // Luego la empresa
    $stmt = $con->prepare("DELETE FROM empresa WHERE nit = ?");
    $stmt->execute([$nit]);

    header("Location: empresas.php?deleted=true");
    exit();
}
?>
