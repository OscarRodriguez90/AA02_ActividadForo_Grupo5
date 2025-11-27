<?php
session_start();
if(!isset($_SESSION['usuario_id'])){
    header("Location: login.php");
    exit();
}
?>
<link rel="stylesheet" href="../estilos/style.css">
<h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
<p>Este es tu panel de usuario.</p>
<a href="logout.php"><button>Cerrar sesión</button></a>
