<?php
session_start();
require '../config/conexion.php';
require '../proc/validaciones.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario_o_email = trim($_POST['usuario_o_email']);
    $password = $_POST['password'];

    $errores = validarLogin($usuario_o_email, $password);

    if (empty($errores)) {

        $stmt = $conn->prepare("SELECT * FROM tbl_usuarios WHERE username = :u OR email = :u");
        $stmt->execute(['u' => $usuario_o_email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];

            header("Location: panel.php");
            exit();

        } else {
            $errores[] = "Usuario o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../estilos/style.css">
</head>
<body>

<form method="post" action="login.php">

    <input type="text" name="usuario_o_email" placeholder="Usuario o correo electrónico"
           value="<?= isset($usuario_o_email) ? htmlspecialchars($usuario_o_email) : '' ?>">
    <div class="mensaje-error">
        <?php foreach ($errores as $e) if (str_contains($e, "usuario") || str_contains($e, "correo")) echo $e; ?>
    </div>

    <input type="password" name="password" placeholder="Contraseña">
    <div class="mensaje-error">
        <?php foreach ($errores as $e) if (str_contains($e, "contraseña")) echo $e; ?>
    </div>

    <button type="submit">Iniciar sesión</button>
</form>

<p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>

</body>
</html>
