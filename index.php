<?php
session_start();
require_once 'config/conexion.php';

$sql = "SELECT p.*, u.nombre_usuario 
        FROM tbl_publicaciones p 
        LEFT JOIN tbl_usuarios u ON p.id_autor = u.id 
        WHERE p.id_padre IS NULL 
        ORDER BY p.fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro - Inicio</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <nav>
            <a href="index.php" class="logo">Foro</a>
            <ul class="nav-links">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="crear_pregunta.php">Nueva Pregunta</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <?php if(isset($_SESSION['usuario'])): ?>
                    <li><a href="actions/logout.php">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        
        <div class="flex flex-between mb-4" style="align-items: center; margin-top: 2rem;">
            <h1>Últimas Preguntas</h1>
            <a href="crear_pregunta.php" class="btn btn-primary hover-lift">
                + Hacer Pregunta
            </a>
        </div>

        <?php if (count($preguntas) > 0): ?>
            <div class="grid">
                <?php foreach ($preguntas as $pregunta): ?>
                    <div class="card hover-glow">
                        <div class="card-header">
                            <h3 class="card-title">
                                <a href="pregunta.php?id=<?= $pregunta['id'] ?>">
                                    <?= htmlspecialchars($pregunta['titulo']) ?>
                                </a>
                            </h3>
                            <div class="card-meta">
                                <span>👤 <?= htmlspecialchars($pregunta['nombre_usuario'] ?? 'Anónimo') ?></span>
                                <span>📅 <?= date('d/m/Y', strtotime($pregunta['fecha'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="card-content">
                            <?= htmlspecialchars(substr($pregunta['contenido'], 0, 150)) ?>...
                        </div>

                        <div class="card-footer">
                            <a href="pregunta.php?id=<?= $pregunta['id'] ?>" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.9rem;">
                                Ver Respuestas
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                Aún no hay preguntas en el foro. ¡Sé el primero en participar!
            </div>
        <?php endif; ?>

    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 Foro de Preguntas.</p>
        </div>
    </footer>

    <script src="./assets/js/main.js"></script>
</body>
</html>