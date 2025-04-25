<?php
session_start();
require_once('../conexion/conexion.php');
$conex = new database();
$con = $conex->connect();

if (isset($_POST['submit'])) {
    $id = $_POST['id']; 
    $pass_desc = $_POST['password']; 
    $modo = $_POST['modo'] ?? 'normal';  // Verifica si es login de superadmin

    if ($id == '' || $pass_desc == '') {
        echo '<script>alert("Ningún dato puede estar vacío")</script>';
        echo '<script>window.location = "../index.php"</script>';
        exit();
    }

    // Si es login de superadmin, solo validamos por documento
    if ($modo === 'superadmin') {
        $sql = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
        $sql->execute([$id]);
    } else {
        // Si no es superadmin, también se debe verificar el correo
        $email = $_POST['email'] ?? null;
        if (!$email) {
            echo '<script>alert("Falta el correo electrónico")</script>';
            echo '<script>window.location = "../index.php"</script>';
            exit();
        }
        $sql = $con->prepare("SELECT * FROM usuarios WHERE id = ? AND correo = ?");
        $sql->execute([$id, $email]);
    }

    $fila = $sql->fetch(PDO::FETCH_ASSOC);

    if ($fila && password_verify($pass_desc, $fila['contrasena'])) {
        $rol = $fila['id_rol'];
        $nit = $fila['nit'];

        // Validar licencia solo si no es superadmin
        if ($rol != 1) {
            $checkLicencia = $con->prepare("SELECT * FROM licencias WHERE nit = ? AND id_estado = 1");
            $checkLicencia->execute([$nit]);
            if (!$checkLicencia->fetch(PDO::FETCH_ASSOC)) {
                echo '<script>alert("La empresa no tiene una licencia activa.")</script>';
                echo '<script>window.location = "../index.php"</script>';
                exit();
            }
        }

        $_SESSION['doc'] = $fila['id'];
        $_SESSION['rol'] = $rol;
        $_SESSION['nit_'] = $nit;

        // Redirigir según el rol
        switch ($rol) {
            case 1:
                header("Location: ../superadmin/index.php");
                break;
            case 2:
                header("Location: ../admin/index.php");
                break;
            case 3:
                header("Location: ../user/index.php");
                break;
        }
        exit();
    } else {
        echo '<script>alert("Credenciales inválidas")</script>';
        echo '<script>window.location = "../index.php"</script>';
    }
}
