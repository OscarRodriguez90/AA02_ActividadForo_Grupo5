<?php
session_start();
require_once 'config/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: view/login.php");
    exit();
}

$my_id = $_SESSION['user_id'];
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $my_id;
$is_own_profile = ($profile_id === $my_id);

// Obtener datos del usuario (del perfil que estamos viendo)
$stmt = $conn->prepare("SELECT * FROM tbl_usuarios WHERE id = :id");
$stmt->bindParam(':id', $profile_id);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar estado de amistad si no es mi perfil
$friendship_status = null;
$friendship_id = null;

if (!$is_own_profile) {
    $stmt_friend = $conn->prepare("SELECT id, estado, id_usuario1 FROM tbl_amistades WHERE (id_usuario1 = :me AND id_usuario2 = :other) OR (id_usuario1 = :other AND id_usuario2 = :me)");
    $stmt_friend->bindParam(':me', $my_id);
    $stmt_friend->bindParam(':other', $profile_id);
    $stmt_friend->execute();
    $friendship = $stmt_friend->fetch(PDO::FETCH_ASSOC);
    
    if ($friendship) {
        $friendship_status = $friendship['estado'];
        $friendship_id = $friendship['id'];
        // Si está pendiente, ver quién la envió
        if ($friendship_status === 'pendiente') {
             if ($friendship['id_usuario1'] == $my_id) {
                 $friendship_status = 'sent'; // Enviada por mí
             } else {
                 $friendship_status = 'received'; // Recibida por mí
             }
        }
    }
}

if (!$usuario) {
    echo "Usuario no encontrado.";
    exit();
}

// Obtener estadísticas
// 1. Número de preguntas (publicaciones sin padre)
$stmt_preguntas = $conn->prepare("SELECT COUNT(*) FROM tbl_publicaciones WHERE id_autor = :id AND id_padre IS NULL");
$stmt_preguntas->bindParam(':id', $profile_id);
$stmt_preguntas->execute();
$num_preguntas = $stmt_preguntas->fetchColumn();

// 2. Número de respuestas (publicaciones con padre)
$stmt_respuestas = $conn->prepare("SELECT COUNT(*) FROM tbl_publicaciones WHERE id_autor = :id AND id_padre IS NOT NULL");
$stmt_respuestas->bindParam(':id', $profile_id);
$stmt_respuestas->execute();
$num_respuestas = $stmt_respuestas->fetchColumn();

// 3. Likes dados
$stmt_likes = $conn->prepare("SELECT COUNT(*) FROM tbl_likes WHERE id_usuario = :id");
$stmt_likes->bindParam(':id', $profile_id);
$stmt_likes->execute();
$num_likes = $stmt_likes->fetchColumn();

// Obtener últimas preguntas
$stmt_last_questions = $conn->prepare("SELECT * FROM tbl_publicaciones WHERE id_autor = :id AND id_padre IS NULL ORDER BY fecha DESC LIMIT 5");
$stmt_last_questions->bindParam(':id', $profile_id);
$stmt_last_questions->execute();
$mis_preguntas = $stmt_last_questions->fetchAll(PDO::FETCH_ASSOC);

// Obtener últimas respuestas (con el título de la pregunta original)
$stmt_last_answers = $conn->prepare("
    SELECT r.*, p.titulo as titulo_pregunta, p.id as id_pregunta 
    FROM tbl_publicaciones r 
    JOIN tbl_publicaciones p ON r.id_padre = p.id 
    WHERE r.id_autor = :id AND r.id_padre IS NOT NULL 
    ORDER BY r.fecha DESC LIMIT 5
");
$stmt_last_answers->bindParam(':id', $profile_id);
$stmt_last_answers->execute();
$mis_respuestas = $stmt_last_answers->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Foro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            background: var(--bg-card, #1e1e1e); /* Fallback color */
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid var(--color-orange, #ff6b00);
            object-fit: cover;
            box-shadow: 0 0 15px rgba(255, 107, 0, 0.3);
        }
        .profile-info {
            flex: 1;
        }
        .profile-info h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
            color: var(--text-primary, #fff);
        }
        .profile-info p {
            font-size: 1.1rem;
            color: var(--text-secondary, #aaa);
            margin-bottom: 0.5rem;
        }
        .profile-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .detail-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .detail-label {
            display: block;
            font-size: 0.9rem;
            color: var(--color-orange, #ff6b00);
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            font-size: 1.2rem;
            color: var(--text-primary, #fff);
            font-weight: 500;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .stat-item {
            background: linear-gradient(145deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.2s;
        }
        .stat-item:hover {
            transform: translateY(-5px);
            border-color: var(--color-orange, #ff6b00);
        }
        .stat-value {
            display: block;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-orange, #ff6b00);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: var(--text-secondary, #aaa);
            font-size: 0.9rem;
        }

        .activity-section {
            margin-top: 3rem;
        }
        .activity-list {
            display: grid;
            gap: 1rem;
        }
        .activity-item {
            background: rgba(255,255,255,0.03);
            padding: 1rem;
            border-radius: 8px;
            border-left: 3px solid var(--color-orange, #ff6b00);
            transition: background 0.2s;
        }
        .activity-item:hover {
            background: rgba(255,255,255,0.06);
        }
        .activity-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        .activity-title a {
            color: var(--text-primary, #fff);
            text-decoration: none;
        }
        .activity-title a:hover {
            color: var(--color-orange, #ff6b00);
        }
        .activity-meta {
            font-size: 0.85rem;
            color: var(--text-secondary, #aaa);
        }
    </style>
</head>
<body>

    <header>
        <nav>
            <a href="index.php" class="logo">TBForo</a>
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
        
        <div class="profile-header">
            <!-- Avatar generado con UI Avatars -->
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombre'] . ' ' . $usuario['apellidos']) ?>&background=random&size=150&bold=true" alt="Avatar" class="profile-avatar">
            
            <div class="profile-info">
                <h1><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></h1>
                <p>@<?= htmlspecialchars($usuario['username']) ?></p>
                
                <div class="profile-actions">
                    <?php if ($is_own_profile): ?>
                        <a href="friends.php" class="btn btn-primary">
                            👥 Mis Amigos
                        </a>
                        <a href="editar_perfil.php" class="btn btn-secondary">
                            ✏️ Editar Perfil
                        </a>
                        <a href="./cambiar_password.php" class="btn btn-secondary">
                            🔒 Cambiar Contraseña
                        </a>
                    <?php else: ?>
                        <!-- Botones para otros usuarios -->
                        <?php if (!$friendship_status): ?>
                            <a href="friends.php?action=add&id=<?= $profile_id ?>" class="btn btn-primary">
                                ➕ Enviar Solicitud de Amistad
                            </a>
                        <?php elseif ($friendship_status === 'sent'): ?>
                            <button class="btn btn-secondary" disabled>
                                ⏳ Solicitud Enviada
                            </button>
                        <?php elseif ($friendship_status === 'received'): ?>
                            <a href="friends.php?action=accept&id=<?= $friendship_id ?>" class="btn btn-primary">
                                ✅ Aceptar Solicitud
                            </a>
                            <a href="friends.php?action=deny&id=<?= $friendship_id ?>" class="btn btn-ghost">
                                ❌ Rechazar
                            </a>
                        <?php elseif ($friendship_status === 'aceptada'): ?>
                            <a href="chat.php?chat_with=<?= $profile_id ?>" class="btn btn-primary">
                                💬 Enviar Mensaje
                            </a>
                            <button class="btn btn-secondary" disabled>
                                ✅ Amigos
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="profile-details">
            <div class="detail-item">
                <span class="detail-label">Correo Electrónico</span>
                <span class="detail-value"><?= htmlspecialchars($usuario['email']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Género</span>
                <span class="detail-value"><?= ucfirst(htmlspecialchars($usuario['genero'])) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Fecha de Nacimiento</span>
                <span class="detail-value"><?= date('d/m/Y', strtotime($usuario['fecha_nacimiento'])) ?></span>
            </div>
        </div>

        <h2 style="margin-top: 3rem; margin-bottom: 1.5rem;">Mis Estadísticas</h2>
        
        <div class="stats">
            <div class="stat-item">
                <span class="stat-value"><?= $num_preguntas ?></span>
                <span class="stat-label">Preguntas</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= $num_respuestas ?></span>
                <span class="stat-label">Respuestas</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= $num_likes ?></span>
                <span class="stat-label">Likes Dados</span>
            </div>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 3rem;">
            
            <!-- Mis Últimas Preguntas -->
            <div>
                <h3 style="margin-bottom: 1rem; border-bottom: 2px solid var(--color-orange); padding-bottom: 0.5rem;">
                    Mis Últimas Preguntas
                </h3>
                <?php if (count($mis_preguntas) > 0): ?>
                    <div class="activity-list">
                        <?php foreach ($mis_preguntas as $pregunta): ?>
                            <div class="activity-item">
                                <div class="activity-title">
                                    <a href="pregunta.php?id=<?= $pregunta['id'] ?>">
                                        <?= htmlspecialchars($pregunta['titulo']) ?>
                                    </a>
                                </div>
                                <div class="activity-meta">
                                    📅 <?= date('d/m/Y H:i', strtotime($pregunta['fecha'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No has hecho ninguna pregunta aún.</p>
                    <a href="crear_pregunta.php" class="btn btn-sm btn-primary" style="margin-top: 10px;">Hacer una pregunta</a>
                <?php endif; ?>
            </div>

            <!-- Mis Últimas Respuestas -->
            <div>
                <h3 style="margin-bottom: 1rem; border-bottom: 2px solid var(--color-orange); padding-bottom: 0.5rem;">
                    Mis Últimas Respuestas
                </h3>
                <?php if (count($mis_respuestas) > 0): ?>
                    <div class="activity-list">
                        <?php foreach ($mis_respuestas as $respuesta): ?>
                            <div class="activity-item">
                                <div class="activity-title">
                                    <span style="font-size: 0.9em; color: #888;">En: </span>
                                    <a href="pregunta.php?id=<?= $respuesta['id_pregunta'] ?>">
                                        <?= htmlspecialchars($respuesta['titulo_pregunta']) ?>
                                    </a>
                                </div>
                                <div style="font-size: 0.95rem; margin: 5px 0; color: #ddd;">
                                    "<?= htmlspecialchars(substr($respuesta['contenido'], 0, 60)) . (strlen($respuesta['contenido']) > 60 ? '...' : '') ?>"
                                </div>
                                <div class="activity-meta">
                                    📅 <?= date('d/m/Y H:i', strtotime($respuesta['fecha'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No has respondido a ninguna pregunta aún.</p>
                <?php endif; ?>
            </div>

        </div>

    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
