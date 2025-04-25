<?php

session_start();


if (!isset($_SESSION['doc'])) {
    
    echo '<script>alert("Credenciales incorrectas.")</script>';
    echo '<script>window.location = "../index.php"</script>';
    exit();
}


