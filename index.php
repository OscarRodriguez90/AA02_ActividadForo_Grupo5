<?php
session_start();
require_once 'config/conexion.php';

$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$params = [];
$user_id = $_SESSION['user_id'] ?? 0;

$sql = "SELECT p.*, u.nombre_usuario,
               (SELECT COUNT(*) FROM tbl_likes WHERE id_publicacion = p.id) as num_likes,
               (SELECT COUNT(*) FROM tbl_likes WHERE id_publicacion = p.id AND id_usuario = :uid) as user_liked
        FROM tbl_publicaciones p 
        LEFT JOIN tbl_usuarios u ON p.id_autor = u.id 
        WHERE p.id_padre IS NULL";

if (!empty($busqueda)) {
    $sql .= " AND p.titulo LIKE :busqueda";
    $params[':busqueda'] = "%" . $busqueda . "%";
}

$sql .= " ORDER BY p.fecha DESC";

$params[':uid'] = $user_id;

$stmt = $conn->prepare($sql);
$stmt->execute($params);
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="./view/logout.php">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="./view/login.php">Iniciar Sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container">
        
        <div class="flex flex-between mb-4" style="align-items: center; margin-top: 2rem; flex-wrap: wrap; gap: 1rem;">
            <h1>
                <?php if($busqueda): ?>
                    Resultados para: "<?= htmlspecialchars($busqueda) ?>"
                <?php else: ?>
                    Últimas Preguntas
                <?php endif; ?>
            </h1>
            
            <div class="flex gap-2">
                <form action="index.php" method="GET" style="display: flex; gap: 0.5rem;">
                    <input type="text" name="q" placeholder="Buscar por título..." 
                           value="<?= htmlspecialchars($busqueda) ?>"
                           style="padding: 8px; width: 250px;">
                    <button type="submit" class="btn btn-secondary">🔍</button>
                    
                    <?php if($busqueda): ?>
                        <a href="index.php" class="btn btn-ghost" title="Limpiar búsqueda">✖</a>
                    <?php endif; ?>
                </form>

                <a href="crear_pregunta.php" class="btn btn-primary hover-lift">
                    + Hacer Pregunta
                </a>
            </div>
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
                                <span>👤 <?= htmlspecialchars($pregunta['username'] ?? 'Anónimo') ?></span>
                                <span>📅 <?= date('d/m/Y', strtotime($pregunta['fecha'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="card-content">
                            <?= htmlspecialchars(substr($pregunta['contenido'], 0, 150)) ?>...
                        </div>

                        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
                            <form action="actions/like.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $pregunta['id'] ?>">
                                <input type="hidden" name="redirect" value="../index.php">
                                <button type="submit" class="btn <?= $pregunta['user_liked'] ? 'btn-primary' : 'btn-secondary' ?>" style="padding: 5px 10px; font-size: 0.9rem;" title="<?= $pregunta['user_liked'] ? 'Quitar like' : 'Dar like' ?>">
                                    <?= $pregunta['user_liked'] ? '❤️' : '🤍' ?> <?= $pregunta['num_likes'] ?>
                                </button>
                            </form>
                            <a href="pregunta.php?id=<?= $pregunta['id'] ?>" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.9rem;">
                                Ver Respuestas
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <?php if($busqueda): ?>
                    No encontramos preguntas que coincidan con "<strong><?= htmlspecialchars($busqueda) ?></strong>".
                    <br><a href="index.php">Ver todas las preguntas</a>
                <?php else: ?>
                    Aún no hay preguntas en el foro. ¡Sé el primero en participar!
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
