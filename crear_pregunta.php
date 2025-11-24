<?php 

require_once './config/conexion.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Pregunta</title>
</head>
<body>

    <h1>Hacer una nueva pregunta</h1>

    <form action="actions/publish_question.php" method="POST" enctype="multipart/form-data">
        
        <label for="title">Título:</label><br>
        <input type="text" name="title" required placeholder="Ej: Error en PHP"><br><br>

        <label for="description">Descripción:</label><br>
        <textarea name="description" required placeholder="Explica tu duda..."></textarea><br><br>

        <label>Adjuntar archivos (Opcional):</label><br>
        <input type="file" name="files[]" multiple><br><br>

        <button type="submit">Publicar Pregunta</button>
    </form>

</body>
</html>