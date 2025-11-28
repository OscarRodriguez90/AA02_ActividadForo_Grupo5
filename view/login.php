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

<<<<<<< HEAD
        if ($usuario && password_verify($password, $usuario['password'])) {

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];

            header("Location: panel.php");
=======
        if($usuario && password_verify($password,$usuario['password'])){
            $_SESSION['user_id']=$usuario['id'];
            $_SESSION['username']=$usuario['username'];
            header("Location: ../index.php");
>>>>>>> e64d5eaf44c667dd8ef8a6610b1874f69c2b1724
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
<<<<<<< HEAD
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

=======
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Foro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 5rem;">
        <div class="card">
            <h1 style="text-align: center; margin-bottom: 2rem;">Iniciar Sesión</h1>
            
            <?php if(!empty($errores)): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <?php foreach($errores as $e): ?>
                        <p style="margin: 0.5rem 0;"><?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="usuario_o_email">Usuario o Correo Electrónico</label>
                    <input type="text" 
                           id="usuario_o_email"
                           name="usuario_o_email" 
                           placeholder="Introduce tu usuario o email" >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" 
                           id="password"
                           name="password" 
                           placeholder="Introduce tu contraseña" >
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Iniciar Sesión
                </button>
            </form>

            <p style="text-align: center; margin-top: 1.5rem; color: var(--color-gray);">
                ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
            </p>
        </div>
    </div>
>>>>>>> e64d5eaf44c667dd8ef8a6610b1874f69c2b1724
</body>
</html>
