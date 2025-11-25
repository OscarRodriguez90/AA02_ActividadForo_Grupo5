<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_pregunta = $_GET['id'];

$sql = "SELECT p.*, u.nombre_usuario 
        FROM tbl_publicaciones p 
        JOIN tbl_usuarios u ON p.id_autor = u.id 
        WHERE p.id = :id AND p.id_padre IS NULL";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_pregunta]);
$pregunta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pregunta) {
    echo "<div class='container mt-4'><h1>Pregunta no encontrada</h1><a href='index.php'>Volver</a></div>";
    exit;
}

$sqlArchivos = "SELECT * FROM tbl_archivos WHERE id_publicacion = :id";
$stmtArchivos = $conn->prepare($sqlArchivos);
$stmtArchivos->execute([':id' => $id_pregunta]);
$archivos = $stmtArchivos->fetchAll(PDO::FETCH_ASSOC);

$sqlResp = "SELECT p.*, u.nombre_usuario 
            FROM tbl_publicaciones p 
            JOIN tbl_usuarios u ON p.id_autor = u.id 
            WHERE p.id_padre = :id 
            ORDER BY p.fecha ASC";
$stmtResp = $conn->prepare($sqlResp);
$stmtResp->execute([':id' => $id_pregunta]);
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

    <?php require 'includes/header.php'; // Asegúrate de que este archivo existe, si no copia el nav del index ?>

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
                <div class="card">
                    <div class="card-header">
                        <span class="text-orange" style="font-weight: bold;"><?= htmlspecialchars($respuesta['nombre_usuario']) ?></span> respondió:
                        <span style="font-size: 0.8rem; color: #888; float: right;"><?= date('d/m/Y H:i', strtotime($respuesta['fecha'])) ?></span>
                    </div>
                    <div class="card-content">
                        <?= nl2br(htmlspecialchars($respuesta['contenido'])) ?>
                    </div>
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