<?php
session_start();
unset($_SESSION['doc']);
unset($_SESSION['rol']);
unset($_SESSION['nit_']);

session_destroy();
session_write_close();

echo '<script>window.location = "../index.php"</script>';

?>