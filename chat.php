<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: view/login.php");
    exit;
}

// --------------------------------------------------------------------------------
// 2. INICIALIZACIÓN DE VARIABLES
// --------------------------------------------------------------------------------
$my_id = $_SESSION['user_id']; // ID del usuario actual
// Obtenemos el ID del amigo con el que queremos chatear desde la URL (?chat_with=X)
// Si no hay parámetro, será null.
$chat_with_id = isset($_GET['chat_with']) ? (int)$_GET['chat_with'] : null;

// --------------------------------------------------------------------------------
// 3. OBTENER LISTA DE AMIGOS
// --------------------------------------------------------------------------------
// Consultamos la base de datos para obtener SOLO los usuarios que son amigos.
// Buscamos en la tabla 'tbl_amistades' donde el usuario actual sea id_usuario1 O id_usuario2.
try {
    $stmt = $conn->prepare("
        SELECT u.id, u.username as username 
        FROM tbl_usuarios u
        JOIN tbl_amistades f ON (f.id_usuario1 = u.id OR f.id_usuario2 = u.id)
        WHERE (f.id_usuario1 = :uid OR f.id_usuario2 = :uid) 
          AND u.id != :uid
          AND f.estado = 'aceptada'
    ");
    $stmt->bindParam(':uid', $my_id);
    $stmt->execute();
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $friends = [];
}



// --------------------------------------------------------------------------------
// 4. VALIDACIÓN DE SEGURIDAD (¿Es realmente mi amigo?)
// --------------------------------------------------------------------------------
// Si intentan chatear con alguien ($chat_with_id), verificamos que ese ID esté en nuestra lista de amigos.
// Esto evita que alguien cambie la URL manualmente para hablar con desconocidos.
$is_friend = false;
$chat_user = null; // Guardará los datos del usuario con el que hablamos (nombre, etc.)

if ($chat_with_id) {
    foreach ($friends as $f) {
        if ($f['id'] == $chat_with_id) {
            $is_friend = true;
            $chat_user = $f;
            break;
        }
    }
    // Si no lo encontramos en la lista de amigos, anulamos el ID para bloquear el chat
    if (!$is_friend) {
        $chat_with_id = null;
    }
}

// --------------------------------------------------------------------------------
// 5. PROCESAR ENVÍO DE MENSAJE (POST)
// --------------------------------------------------------------------------------
// Si recibimos una petición POST con 'content' y tenemos un chat abierto válido...
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content']) && $chat_with_id) {
    $content = trim($_POST['content']); // Limpiamos espacios en blanco
    
    if (!empty($content)) {
        try {
            // Insertamos el mensaje en la tabla 'tbl_mensajes_privados'
            $stmt = $conn->prepare("INSERT INTO tbl_mensajes_privados (id_emisor, id_receptor, mensaje) VALUES (:sender_id, :receiver_id, :content)");
            $stmt->bindParam(':sender_id', $my_id);
            $stmt->bindParam(':receiver_id', $chat_with_id);
            $stmt->bindParam(':content', $content);
            $stmt->execute();
            
            // Redirigimos a la misma página (PRG Pattern) para evitar que al recargar se reenvíe el formulario.
            // Añadimos #ultimo para que el navegador baje automáticamente al final del chat.
            header("Location: chat.php?chat_with=$chat_with_id#ultimo");
            exit;
        } catch (PDOException $e) {
            $error = "Error al enviar: " . $e->getMessage();
        }
    }
}

// --------------------------------------------------------------------------------
// 6. OBTENER HISTORIAL DE MENSAJES
// --------------------------------------------------------------------------------
// Si tenemos un chat abierto, cargamos los mensajes entre los dos usuarios.
$messages = [];
if ($chat_with_id) {
    try {
        // Seleccionamos mensajes donde:
        // (Yo soy emisor Y él es receptor) O (Él es emisor Y yo soy receptor)
        // Ordenados por fecha ascendente (más antiguos primero)
        $stmt = $conn->prepare("
            SELECT m.*, u.username as sender_name, m.mensaje as content, m.fecha as created_at, m.id_emisor as sender_id
            FROM tbl_mensajes_privados m 
            JOIN tbl_usuarios u ON m.id_emisor = u.id
            WHERE (id_emisor = :current_id AND id_receptor = :other_id) 
               OR (id_emisor = :other_id AND id_receptor = :current_id)
            ORDER BY fecha ASC
        ");
        $stmt->bindParam(':current_id', $my_id);
        $stmt->bindParam(':other_id', $chat_with_id);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error al cargar mensajes";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Foro de Preguntas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="chat-layout">

<div class="chat-sidebar">
    <div class="chat-sidebar-header">
        <h2 class="chat-sidebar-title">Mis Amigos</h2>
        <a href="friends.php" class="manage-link">Gestionar</a>
    </div>
    
    <a href="./index.php" class="back-link">← Volver a Preguntas</a>

    <?php if (empty($friends)): ?>
        <div class="empty-friends">
            No tienes amigos aún.<br>
            <a href="friends.php" class="btn btn-secondary mt-2">Buscar amigos</a>
        </div>
    <?php else: ?>
        <?php foreach ($friends as $user): ?>
            <a href="?chat_with=<?= $user['id'] ?>#ultimo" class="friend-item <?= ($chat_with_id == $user['id']) ? 'active' : '' ?>">
                <?= htmlspecialchars($user['username']) ?>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <div class="logout-container">
        <a href="?logout=1" class="logout-link">Cerrar Sesión</a>
    </div>
</div>

<div class="chat-main">
    <?php if ($chat_user): ?>
        <div class="chat-main-header">
            Chat con <span class="username-highlight"><?= htmlspecialchars($chat_user['username']) ?></span>
        </div>
        
        <div class="chat-messages-container" id="messages-container">
            <?php if (empty($messages)): ?>
                <div class="empty-messages">
                    <div style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;">💬</div>
                    No hay mensajes aún. ¡Inicia la conversación!
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): 
                    $is_me = ($msg['sender_id'] == $my_id);
                ?>
                    <div class="message-bubble <?= $is_me ? 'sent' : 'received' ?>">
                        <div class="message-sender"><?= htmlspecialchars($is_me ? 'Tú' : $msg['sender_name']) ?></div>
                        <div class="message-content"><?= htmlspecialchars($msg['content']) ?></div>
                        <div class="message-time"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <a name="ultimo"></a>
        </div>

        <div class="chat-input-container">
            <form method="POST" class="chat-input-form">
                <input type="text" name="content" class="chat-input-field" placeholder="Escribe un mensaje..." required autofocus autocomplete="off">
                <button type="submit" class="chat-send-btn">Enviar ➤</button>
            </form>
        </div>
    <?php else: ?>
        <div class="no-chat-selected">
            <div class="no-chat-icon">💬</div>
            <?php if ($chat_with_id): ?>
                <div>Este usuario no es tu amigo.</div>
                <a href="friends.php" class="btn btn-primary">Añadir amigo</a>
            <?php else: ?>
                <div>Selecciona un amigo para chatear</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: chat.php");
    exit;
}
?>
<script src="assets/js/main.js"></script>
</body>
</html>