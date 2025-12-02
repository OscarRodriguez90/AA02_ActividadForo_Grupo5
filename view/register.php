<?php
session_start();
require '../config/conexion.php';
require '../proc/validaciones.php';

$errores = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $genero = trim($_POST['genero']);
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];

    $errores = validarRegistro($username, $nombre, $apellidos, $email, $fecha_nacimiento, $genero, $password, $confirmar_password, $conn);

    if (empty($errores)) {
        $nombre = ucfirst(strtolower($nombre));
        $apellidosArray = explode(' ', $apellidos);
        foreach ($apellidosArray as &$apellido) $apellido = ucfirst(strtolower($apellido));
        $apellidos = implode(' ', $apellidosArray);

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO tbl_usuarios 
        (username, nombre, apellidos, email, fecha_nacimiento, genero, password) 
        VALUES (:username, :nombre, :apellidos, :email, :fecha_nacimiento, :genero, :password)");
        
        $stmt->execute([
            'username' => $username,
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'email' => $email,
            'fecha_nacimiento' => $fecha_nacimiento,
            'genero' => $genero,
            'password' => $passwordHash
        ]);

        $exito = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/validaciones.js"></script>
</head>
<body>

<header>
    <nav>
        <a href="../index.php" class="logo">Foro</a>
        <ul class="nav-links">
            <li><a href="../index.php">Inicio</a></li>
            <li><a href="../crear_pregunta.php">Nueva Pregunta</a></li>
            <li><a href="../perfil.php">Perfil</a></li>
            <li><a href="../friends.php">Amigos</a></li>
            <li><a href="../chat.php">Chat</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li class="welcome-text">Bienvenido <?= htmlspecialchars($_SESSION['username'] ?? '') ?></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="login.php">Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="container" style="max-width: 600px; margin-top: 3rem; margin-bottom: 3rem;">
    <div class="card">
        <?php if ($exito): ?>
            <div style="text-align:center; padding: 2rem;">
                <h2 style="color: var(--color-orange); margin-bottom: 1rem;">¡Registro completado con éxito!</h2>
                <p style="margin-bottom: 2rem;">Ahora puedes iniciar sesión con tus credenciales.</p>
                <a href="login.php" class="btn btn-primary">Ir a Login</a>
            </div>
        <?php else: ?>
            <h1 style="text-align: center; margin-bottom: 2rem;">Registro de Usuario</h1>
            
            <?php if(!empty($errores)): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <?php foreach($errores as $e): ?>
                        <p style="margin: 0.5rem 0;"><?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="register.php" id="formRegistro">
                <div class="form-group">
                    <label for="username">Nombre de usuario</label>
                    <input type="text" id="username" name="username" 
                           placeholder="Nombre de usuario" 
                           value="<?= isset($username)?htmlspecialchars($username):'' ?>">
                    <div id="error-username" class="mensaje-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem;">
                        <?= in_array("Este username ya está en uso.", $errores) ? "Este username ya está en uso." : "" ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" 
                           placeholder="Nombre" 
                           value="<?= isset($nombre)?htmlspecialchars($nombre):'' ?>">
                    <div id="error-nombre" class="mensaje-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" 
                           placeholder="Apellidos" 
                           value="<?= isset($apellidos)?htmlspecialchars($apellidos):'' ?>">
                    <div id="error-apellidos" class="mensaje-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" 
                           placeholder="Correo electrónico" 
                           value="<?= isset($email)?htmlspecialchars($email):'' ?>">
                    <div id="error-email" class="mensaje-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem;">
                        <?= in_array("Esta dirección de correo electrónico ya está en uso.", $errores) ? "Esta dirección de correo electrónico ya está en uso." : "" ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                           value="<?= isset($fecha_nacimiento)?htmlspecialchars($fecha_nacimiento):'' ?>">
                    <div id="error-fecha_nacimiento" class="mensaje-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="genero">Género</label>
                    <select id="genero" name="genero">
                        <option value="">Selecciona tu género</option>
                        <option value="hombre" <?= (isset($genero) && $genero==="hombre")?"selected":"" ?>>Hombre</option>
                        <option value="mujer" <?= (isset($genero) && $genero==="mujer")?"selected":"" ?>>Mujer</option>
                        <option value="otro" <?= (isset($genero) && $genero==="otro")?"selected":"" ?>>Otro</option>
                    </select>
                    <div id="error-genero" class="mensaje-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Contraseña">
                    <div id="error-password" class="mensaje-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label for="confirmar_password">Confirmar contraseña</label>
                    <input type="password" id="confirmar_password" name="confirmar_password" 
                           placeholder="Confirmar contraseña">
                    <div id="error-confirmar_password" class="mensaje-error" style="color: #ef4444; font-size: 0.85rem; margin-top: 0.5rem;"></div>
                </div>

                <button type="submit" id="btnEnviar" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Registrarse
                </button>
            </form>

            <p style="text-align: center; margin-top: 1.5rem; color: var(--color-gray);">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
            </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
