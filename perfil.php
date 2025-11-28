<?php
/**
 * PERFIL DE USUARIO - VISTA
 * Frontend para visualización de perfiles de usuario
 */

// Incluir la lógica de negocio
require_once 'actions/perfil_actions.php';

// Todas las variables están disponibles desde perfil_actions.php:
// - $user: información del usuario
// - $my_id: ID del usuario actual
// - $profile_id: ID del perfil que se está viendo
// - $friendship_status: estado de la amistad
// - $am_i_sender: si yo envié la solicitud
// - $user_stats: estadísticas del usuario
// - $user_posts: publicaciones del usuario
// - $message: mensajes de éxito
// - $error: mensajes de error
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($user['nombre_usuario']) ?> - Foro</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <nav>
        <div class="logo">Foro<span class="username-highlight">Chat</span></div>
        <ul class="nav-links">
            <li><a href="./index.php">Foro</a></li>
            <li><a href="friends.php">Amigos</a></li>
            <li><a href="chat.php">Chat</a></li>
            <li><a href="buscar.php">Buscar</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="friends-container">
        <h1 class="text-center">Perfil de Usuario</h1>
        
        <!-- Mensajes de éxito -->
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <!-- Mensajes de error -->
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Información del perfil -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 style="margin: 0;"><?= htmlspecialchars($user['nombre_usuario']) ?></h2>
                    <p style="margin: 0.5rem 0 0 0; color: var(--color-gray);">
                        <?= htmlspecialchars($user['nombre_real']) ?>
                    </p>
                </div>
                <div class="actions">
                    <?php if ($profile_id !== $my_id): ?>
                        <?php if ($friendship_status === 'aceptada'): ?>
                            <!-- Ya son amigos: mostrar botón de chat -->
                            <span class="badge">✓ Amigos</span>
                            <a href="chat.php?chat_with=<?= $profile_id ?>" class="btn btn-primary">
                                💬 Chatear
                            </a>
                        <?php elseif ($friendship_status === 'pendiente'): ?>
                            <!-- Solicitud pendiente -->
                            <?php if ($am_i_sender): ?>
                                <span class="tag">Solicitud enviada</span>
                            <?php else: ?>
                                <span class="tag">Solicitud recibida (revisar en Amigos)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- No son amigos: mostrar botón para enviar solicitud -->
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="send_friend_request" class="btn btn-secondary">
                                    + Añadir como amigo
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge">Mi Perfil</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-content">
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
        
        <!-- Estadísticas del usuario -->
        <h2 class="section-title">Estadísticas</h2>
        <div class="stats">
            <div class="stat-item">
                <span class="stat-value"><?= $user_stats['publicaciones'] ?></span>
                <span class="stat-label">Preguntas</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= $user_stats['respuestas'] ?></span>
                <span class="stat-label">Respuestas</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?= $user_stats['likes_recibidos'] ?></span>
                <span class="stat-label">Likes recibidos</span>
            </div>
        </div>
        
        <!-- Últimas publicaciones -->
        <h2 class="section-title">Últimas Preguntas</h2>
        <?php if (empty($user_posts)): ?>
            <p class="text-center empty-messages">Este usuario aún no ha publicado ninguna pregunta.</p>
        <?php else: ?>
            <?php foreach ($user_posts as $post): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= htmlspecialchars($post['titulo']) ?></h3>
                        <span class="tag"><?= date('d/m/Y', strtotime($post['fecha'])) ?></span>
                    </div>
                    <div class="card-meta" style="display: flex; align-items: center; gap: 10px;">
                        <form action="actions/like.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $post['id'] ?>">
                            <input type="hidden" name="redirect" value="../perfil.php?id=<?= $profile_id ?>">
                            <button type="submit" class="btn <?= $post['user_liked'] ? 'btn-primary' : 'btn-secondary' ?>" style="padding: 2px 8px; font-size: 0.8rem;" title="<?= $post['user_liked'] ? 'Quitar like' : 'Dar like' ?>">
                                <?= $post['user_liked'] ? '❤️' : '🤍' ?> <?= $post['num_likes'] ?>
                            </button>
                        </form>
                        <span>💬 <?= $post['num_respuestas'] ?> respuestas</span>
                    </div>
                    <div class="card-content">
                        <?= nl2br(htmlspecialchars(substr($post['contenido'], 0, 200))) ?>
                        <?= strlen($post['contenido']) > 200 ? '...' : '' ?>
                    </div>
                    <div class="card-footer">
                        <a href="question_detail.php?id=<?= $post['id'] ?>" class="btn btn-ghost btn-sm">
                            Ver pregunta completa →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Botón volver -->
        <div class="text-center" style="margin-top: var(--spacing-xl);">
            <a href="friends.php" class="btn btn-secondary">← Volver a Amigos</a>
        </div>
    </div>
</div>

</body>
</html>
