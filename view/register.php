<?php
require '../conexion/conexion.php';
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

        $stmt = $conn->prepare("INSERT INTO tbl_usuarios (username, nombre, apellidos, email, fecha_nacimiento, genero, password) VALUES (:username, :nombre, :apellidos, :email, :fecha_nacimiento, :genero, :password)");
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
    <link rel="stylesheet" href="../estilos/style.css">
    <script src="../js/validaciones.js"></script>
</head>
<body>

<?php if ($exito): ?>
<div style="max-width:400px; margin:50px auto; text-align:center; background:#fff; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
    <h2>¡Registro completado con éxito!</h2>
    <p>Ahora puedes iniciar sesión con tus credenciales.</p>
    <a href="login.php"><button>Ir a Login</button></a>
</div>
<?php else: ?>

<form method="post" action="register.php" id="formRegistro">
    <input type="text" id="username" name="username" placeholder="Nombre de usuario" value="<?= isset($username)?htmlspecialchars($username):'' ?>">
    <div id="error-username" class="mensaje-error"><?= in_array("Este username ya está en uso.", $errores) ? "Este username ya está en uso." : "" ?></div>

    <input type="text" id="nombre" name="nombre" placeholder="Nombre" value="<?= isset($nombre)?htmlspecialchars($nombre):'' ?>">
    <div id="error-nombre" class="mensaje-error"></div>

    <input type="text" id="apellidos" name="apellidos" placeholder="Apellidos" value="<?= isset($apellidos)?htmlspecialchars($apellidos):'' ?>">
    <div id="error-apellidos" class="mensaje-error"></div>

    <input type="email" id="email" name="email" placeholder="Correo electrónico" value="<?= isset($email)?htmlspecialchars($email):'' ?>">
    <div id="error-email" class="mensaje-error"><?= in_array("Esta dirección de correo electrónico ya está en uso.", $errores) ? "Esta dirección de correo electrónico ya está en uso." : "" ?></div>

    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= isset($fecha_nacimiento)?htmlspecialchars($fecha_nacimiento):'' ?>">
    <div id="error-fecha_nacimiento" class="mensaje-error"></div>

    <select id="genero" name="genero">
        <option value="">Selecciona tu género</option>
        <option value="hombre" <?= (isset($genero) && $genero==="hombre")?"selected":"" ?>>Hombre</option>
        <option value="mujer" <?= (isset($genero) && $genero==="mujer")?"selected":"" ?>>Mujer</option>
        <option value="otro" <?= (isset($genero) && $genero==="otro")?"selected":"" ?>>Otro</option>
    </select>
    <div id="error-genero" class="mensaje-error"></div>

    <input type="password" id="password" name="password" placeholder="Contraseña">
    <div id="error-password" class="mensaje-error"></div>

    <input type="password" id="confirmar_password" name="confirmar_password" placeholder="Confirmar contraseña">
    <div id="error-confirmar_password" class="mensaje-error"></div>

    <button type="submit" id="btnEnviar">Registrarse</button>
</form>

<?php
if(!empty($errores)) {
    echo '<div style="max-width:400px;margin:10px auto;color:red;">';
    foreach($errores as $e) echo "<p>$e</p>";
    echo '</div>';
}
?>

<?php endif; ?>
</body>
</html>
