<?php 
session_start();
require_once './config/conexion.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Crea una nueva pregunta en nuestro foro">
    <title>Nueva Pregunta - Foro</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- HEADER / NAVEGACIÓN -->
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

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container">
        <div style="max-width: 800px; margin: 3rem auto;">
            
            <h1 class="text-center mb-4">Hacer una nueva pregunta</h1>
            
            <div class="card">
                <form action="actions/publish_question.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label for="title">Título de la pregunta</label>
                        <input type="text" 
                               id="title"
                               name="title"
                               placeholder="Ej: ¿Cómo soluciono un error de conexión en PHP?">
                    </div>

                    <div class="form-group">
                        <label for="description">Descripción detallada</label>
                        <textarea id="description"
                                  name="description"
                                  placeholder="Explica tu duda con el mayor detalle posible. Incluye código si es necesario..."
                                  rows="8"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="files">Adjuntar archivos (Opcional)</label>
                        <input type="file" 
                               id="files"
                               name="files[]" 
                               multiple
                               style="padding: 0.75rem;">
                        <p style="font-size: 0.85rem; color: var(--color-gray); margin-top: 0.5rem;">
                            Puedes adjuntar capturas de pantalla, código o archivos relacionados
                        </p>
                    </div>

                    <div class="flex gap-2" style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            Publicar Pregunta
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            Cancelar
                        </a>
                    </div>
                    
                </form>
            </div>

        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Foro de Preguntas. Todos los derechos reservados.</p>
        </div>
    </footer>
<script src="assets/js/main.js"></script>
</body>
</html>