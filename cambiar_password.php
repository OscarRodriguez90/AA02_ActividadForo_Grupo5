<?php
session_start();
require_once 'config/conexion.php';

// Verificar login
if (!isset($_SESSION['user_id'])) {
    header("Location: view/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$mensaje = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_repite = $_POST['password_repite'] ?? '';

    // Validar campos vacíos
    if (empty($password_actual) || empty($password_nueva) || empty($password_repite)) {
        $mensaje = "⚠️ Todos los campos son obligatorios.";
    } else if ($password_nueva !== $password_repite) {
        $mensaje = "❌ Las contraseñas nuevas no coinciden.";
    } else {

        // Obtener contraseña actual
        $stmt = $conn->prepare("SELECT password FROM tbl_usuarios WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password_actual, $user['password'])) {
            $mensaje = "❌ La contraseña actual no es correcta.";
        } else {
            // Actualizar contraseña
            $hash = password_hash($password_nueva, PASSWORD_DEFAULT);

            $stmt_update = $conn->prepare("
                UPDATE tbl_usuarios SET password = :pass WHERE id = :id
            ");
            $stmt_update->bindParam(':pass', $hash);
            $stmt_update->bindParam(':id', $user_id);

            if ($stmt_update->execute()) {
                $mensaje = "✅ Contraseña cambiada correctamente.";
            } else {
                $mensaje = "⚠️ Error inesperado, intenta nuevamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <nav>
        <a href="index.php" class="logo">TBForo</a>
        <ul class="nav-links">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="crear_pregunta.php">Nueva Pregunta</a></li>
            <li><a href="perfil.php" class="active" style="color: var(--color-orange);">Perfil</a></li>
            <li><a href="friends.php">Amigos</a></li>
            <li><a href="chat.php">Chat</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="view/logout.php">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="view/login.php">Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="card password-card">
    <h2>🔒 Cambiar Contraseña</h2>

    <?php if (!empty($mensaje)): ?>
        <p class="password-message"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Contraseña Actual</label>
            <input type="password" name="password_actual" required>
        </div>

        <div class="form-group">
            <label>Nueva Contraseña</label>
            <input type="password" name="password_nueva" required>
        </div>

        <div class="form-group">
            <label>Repetir Nueva Contraseña</label>
            <input type="password" name="password_repite" required>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>
</div>

</body>
</html>
