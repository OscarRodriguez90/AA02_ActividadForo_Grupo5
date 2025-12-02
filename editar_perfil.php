<?php
session_start();
require_once 'config/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: view/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';

// Obtener datos actuales del usuario
$stmt = $conn->prepare("SELECT * FROM tbl_usuarios WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuario no encontrado.";
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];

    // Validaciones básicas
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($fecha_nacimiento) || empty($genero)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        try {
            // Actualizar datos
            $sql = "UPDATE tbl_usuarios SET 
                    nombre = :nombre, 
                    apellidos = :apellidos, 
                    email = :email, 
                    fecha_nacimiento = :fecha_nacimiento, 
                    genero = :genero 
                    WHERE id = :id";
            
            $stmt_update = $conn->prepare($sql);
            $stmt_update->bindParam(':nombre', $nombre);
            $stmt_update->bindParam(':apellidos', $apellidos);
            $stmt_update->bindParam(':email', $email);
            $stmt_update->bindParam(':fecha_nacimiento', $fecha_nacimiento);
            $stmt_update->bindParam(':genero', $genero);
            $stmt_update->bindParam(':id', $user_id);
            
            if ($stmt_update->execute()) {
                // Redirigir a perfil.php después de guardar exitosamente
                header("Location: ./perfil.php");
                exit();
            } else {
                $error = "Error al actualizar el perfil.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Error de duplicidad (email único)
                $error = "El correo electrónico ya está registrado por otro usuario.";
            } else {
                $error = "Error en la base de datos: " . $e->getMessage();
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
    <title>Editar Perfil - Foro</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <nav>
            <a href="index.php" class="logo">Foro</a>
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

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <div class="card-header">
                <h2>Editar Perfil</h2>
            </div>
            
            <div class="card-content">
                <?php if ($error): ?>
                    <div class="alert alert-danger" style="color: red; background: rgba(255,0,0,0.1); border: 1px solid red; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="editar_perfil.php" method="POST">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>">
                        <div id="error-nombre" class="mensaje-error" style="color: red; font-size: 0.875rem; margin-top: 0.25rem;"></div>
                    </div>

                    <div class="form-group">
                        <label for="apellidos">Apellidos</label>
                        <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($usuario['apellidos']) ?>" >
                        <div id="error-apellidos" class="mensaje-error" style="color: red; font-size: 0.875rem; margin-top: 0.25rem;"></div>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" >
                        <div id="error-email" class="mensaje-error" style="color: red; font-size: 0.875rem; margin-top: 0.25rem;"></div>
                    </div>

                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['fecha_nacimiento']) ?>" >
                        <div id="error-fecha_nacimiento" class="mensaje-error" style="color: red; font-size: 0.875rem; margin-top: 0.25rem;"></div>
                    </div>

                    <div class="form-group">
                        <label for="genero">Género</label>
                        <select id="genero" name="genero" >
                            <option value="hombre" <?= $usuario['genero'] == 'hombre' ? 'selected' : '' ?>>Hombre</option>
                            <option value="mujer" <?= $usuario['genero'] == 'mujer' ? 'selected' : '' ?>>Mujer</option>
                            <option value="otro" <?= $usuario['genero'] == 'otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                        <div id="error-genero" class="mensaje-error" style="color: red; font-size: 0.875rem; margin-top: 0.25rem;"></div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="perfil.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/validaciones.js"></script>
</body>
</html>
