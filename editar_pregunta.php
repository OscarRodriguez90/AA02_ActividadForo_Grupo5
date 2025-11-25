<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_pregunta = $_GET['id'];
$id_usuario_actual = $_SESSION['user_id'] ?? 1;

$sql = "SELECT * FROM tbl_publicaciones WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_pregunta]);
$pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pregunta) {
    die("La pregunta no existe.");
}
if ($pregunta['id_autor'] != $id_usuario_actual) {
    die("⛔ Acceso denegado: No puedes editar una pregunta que no es tuya.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Pregunta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">Foro</a>
            <ul class="nav-links">
                <li><a href="index.php">Cancelar y Volver</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div style="max-width: 800px; margin: 3rem auto;">
            <h1>✏️ Editar Pregunta</h1>
            
            <div class="card">
                <form action="actions/update_question.php" method="POST">
                    
                    <input type="hidden" name="id" value="<?= $pregunta['id'] ?>">

                    <div class="form-group">
                        <label for="title">Título:</label>
                        <input type="text" name="title" id="title"
                               value="<?= htmlspecialchars($pregunta['titulo']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Descripción:</label>
                        <textarea name="description" id="description" rows="8"><?= htmlspecialchars($pregunta['contenido']) ?></textarea>
                    </div>

                    <div class="flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="pregunta.php?id=<?= $pregunta['id'] ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>