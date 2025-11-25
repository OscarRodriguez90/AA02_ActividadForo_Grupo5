<?php
session_start();
if(isset($_SESSION['usuario_id'])) header("Location: view/panel.php");
else header("Location: view/login.php");
exit();
?>
