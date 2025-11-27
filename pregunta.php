<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_pregunta = $_GET['id'];
$user_id = $_SESSION['user_id'] ?? 0;

// 1. Obtener la pregunta principal
$sql = "SELECT p.*, u.nombre_usuario,
               (SELECT COUNT(*) FROM tbl_likes WHERE id_publicacion = p.id) as num_likes,
               (SELECT COUNT(*) FROM tbl_likes WHERE id_publicacion = p.id AND id_usuario = :uid) as user_liked
        FROM tbl_publicaciones p 
        JOIN tbl_usuarios u ON p.id_autor = u.id 
        WHERE p.id = :id AND p.id_padre IS NULL";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_pregunta, ':uid' => $user_id]);
$pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pregunta) {
    echo "<div class='container mt-4'><h1>Pregunta no encontrada</h1><a href='index.php'>Volver</a></div>";
    exit;
}

// 2. Obtener archivos de la pregunta principal
$sqlArchivos = "SELECT * FROM tbl_archivos WHERE id_publicacion = :id";
$stmtArchivos = $conn->prepare($sqlArchivos);
$stmtArchivos->execute([':id' => $id_pregunta]);
$archivos = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);

// 3. Obtener las respuestas
$sqlResp = "SELECT p.*, u.nombre_usuario,
                   (SELECT COUNT(*) FROM tbl_likes WHERE id_publicacion = p.id) as num_likes,
                   (SELECT COUNT(*) FROM tbl_likes WHERE id_publicacion = p.id AND id_usuario = :uid) as user_liked
            FROM tbl_publicaciones p 
            JOIN tbl_usuarios u ON p.id_autor = u.id 
            WHERE p.id_padre = :id 
            ORDER BY p.fecha ASC";
$stmtResp = $conn->prepare($sqlResp);
$stmtResp->execute([':id' => $id_pregunta, ':uid' => $user_id]);
$respuestas = $stmtResp->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pregunta['titulo']) ?> - Foro</title>
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
        
        <div class="card" style="border-left: 4px solid var(--color-orange); margin-top: 2rem;">
            <div class="card-header">
                <h1><?= htmlspecialchars($pregunta['titulo']) ?></h1>
                <div class="card-meta">
                    <span>👤 Publicado por <strong><?= htmlspecialchars($pregunta['nombre_usuario']) ?></strong></span>
                    <span>📅 <?= date('d/m/Y H:i', strtotime($pregunta['fecha'])) ?></span>
                </div>
            </div>

            <div class="card-content" style="font-size: 1.1rem; min-height: 100px;">
                <?= nl2br(htmlspecialchars($pregunta['contenido'])) ?>
            </div>

            <div class="card-footer" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px; margin-top: 10px;">
                <form action="actions/like.php" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $pregunta['id'] ?>">
                    <input type="hidden" name="redirect" value="../pregunta.php?id=<?= $pregunta['id'] ?>">
                    <button type="submit" class="btn <?= $pregunta['user_liked'] ? 'btn-primary' : 'btn-secondary' ?>" style="padding: 5px 10px; font-size: 0.9rem;" title="<?= $pregunta['user_liked'] ? 'Quitar like' : 'Dar like' ?>">
                        <?= $pregunta['user_liked'] ? '❤️' : '🤍' ?> <?= $pregunta['num_likes'] ?>
                    </button>
                </form>
            </div>

            <?php 
                $usuario_actual = $_SESSION['user_id'] ?? 0;
                
                if ($pregunta['id_autor'] == $usuario_actual): 
            ?>
                <div class="mt-3 text-right">
                    <a href="editar_pregunta.php?id=<?= $pregunta['id'] ?>" class="btn btn-secondary btn-sm">
                        ✏️ Editar
                    </a>
                    
                    <a href="actions/delete_question.php?id=<?= $pregunta['id'] ?>" 
                       class="btn btn-primary btn-sm"
                       style="background: #dc3545; border-color: #dc3545;"
                       onclick="return confirm('¿Estás seguro de que quieres borrar esta pregunta? Se borrarán también las respuestas.');">
                        🗑️ Eliminar
                    </a>
                </div>
            <?php endif; ?>
            <?php if (count($archivos) > 0): ?>
                <div class="mt-3 p-3" style="background: rgba(0,0,0,0.2); border-radius: 8px;">
                    <h4>📎 Archivos Adjuntos:</h4>
                    <div class="flex gap-2" style="flex-wrap: wrap;">
                        <?php foreach ($archivos as $archivo): ?>
                            <?php 
                                $ext = pathinfo($archivo['ruta_archivo'], PATHINFO_EXTENSION);
                                if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): 
                            ?>
                                <a href="<?= $archivo['ruta_archivo'] ?>" target="_blank">
                                    <img src="<?= $archivo['ruta_archivo'] ?>" style="height: 150px; object-fit: cover; border-radius: 8px; border: 1px solid #555;">
                                </a>
                            <?php else: ?>
                                <a href="<?= $archivo['ruta_archivo'] ?>" class="btn btn-secondary btn-sm" target="_blank">
                                    📄 Descargar (<?= $ext ?>)
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <h3 class="mt-4 mb-2">Respuestas (<?= count($respuestas) ?>)</h3>
        <hr style="border-color: #333; margin-bottom: 2rem;">

        <?php if (count($respuestas) > 0): ?>
            <?php foreach ($respuestas as $respuesta): ?>
                
                <?php
                    $sqlArchivosResp = "SELECT * FROM tbl_archivos WHERE id_publicacion = :id_resp";
                    $stmtArchivosResp = $conn->prepare($sqlArchivosResp);
                    $stmtArchivosResp->execute([':id_resp' => $respuesta['id']]);
                    $archivosResp = $stmtArchivosResp->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="card">
                    <div class="card-header">
                        <span class="text-orange" style="font-weight: bold;"><?= htmlspecialchars($respuesta['nombre_usuario']) ?></span> respondió:
                        <span style="font-size: 0.8rem; color: #888; float: right;"><?= date('d/m/Y H:i', strtotime($respuesta['fecha'])) ?></span>
                    </div>
                    <div class="card-content">
                        <?= nl2br(htmlspecialchars($respuesta['contenido'])) ?>
                    </div>

                    <div class="card-footer" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px; margin-top: 10px;">
                        <form action="actions/like.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $respuesta['id'] ?>">
                            <input type="hidden" name="redirect" value="../pregunta.php?id=<?= $pregunta['id'] ?>">
                            <button type="submit" class="btn <?= $respuesta['user_liked'] ? 'btn-primary' : 'btn-secondary' ?>" style="padding: 5px 10px; font-size: 0.9rem;" title="<?= $respuesta['user_liked'] ? 'Quitar like' : 'Dar like' ?>">
                                <?= $respuesta['user_liked'] ? '❤️' : '🤍' ?> <?= $respuesta['num_likes'] ?>
                            </button>
                        </form>
                    </div>

                    <?php if (count($archivosResp) > 0): ?>
                        <div class="mt-3 p-2" style="background: rgba(255,255,255,0.05); border-radius: 6px;">
                            <strong style="font-size: 0.9rem; color: var(--color-orange);">Adjuntos:</strong>
                            <div class="flex gap-2 mt-1" style="flex-wrap: wrap;">
                                <?php foreach ($archivosResp as $arch): ?>
                                    <?php 
                                        $ext = pathinfo($arch['ruta_archivo'], PATHINFO_EXTENSION);
                                        if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])): 
                                    ?>
                                        <a href="<?= $arch['ruta_archivo'] ?>" target="_blank">
                                            <img src="<?= $arch['ruta_archivo'] ?>" style="height: 100px; border-radius: 4px; border: 1px solid #555;">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= $arch['ruta_archivo'] ?>" class="btn btn-secondary btn-sm" target="_blank">
                                            📄 <?= $ext ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">Nadie ha respondido aún. ¡Sé el primero!</div>
        <?php endif; ?>

        <div class="mt-4 mb-4">
            <div class="card" style="background: rgba(255,255,255,0.03);">
                <h4>Tu Respuesta</h4>
                <form action="actions/post_answer.php" method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="question_id" value="<?= $pregunta['id'] ?>">

                    <div class="form-group">
                        <textarea name="content" placeholder="Escribe aquí tu solución (Máx 500 caracteres)..." maxlength="500" style="width: 100%; height: 120px;"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Adjuntar archivo (Opcional):</label><br>
                        <input type="file" name="files[]" multiple>
                    </div>

                    <button type="submit" class="btn btn-primary">Publicar Respuesta</button>
                </form>
            </div>
        </div>

    </div>
    <script src="./assets/js/main.js"></script>
</body>
</html>