<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: view/login.php");
    exit;
}

$my_id = $_SESSION['user_id'];
$message = '';

// --------------------------------------------------------------------------------
// ACCIONES (Enviar solicitud, Aceptar, Rechazar)
// --------------------------------------------------------------------------------
if (isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    try {
        if ($action === 'add') {
            // Verificar si ya existe relación
            $stmt = $conn->prepare("SELECT id FROM tbl_amistades WHERE (id_usuario1 = :u1 AND id_usuario2 = :u2) OR (id_usuario1 = :u2 AND id_usuario2 = :u1)");
            $stmt->bindParam(':u1', $my_id);
            $stmt->bindParam(':u2', $target_id);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                $stmt = $conn->prepare("INSERT INTO tbl_amistades (id_usuario1, id_usuario2, estado) VALUES (:u1, :u2, 'pendiente')");
                $stmt->bindParam(':u1', $my_id);
                $stmt->bindParam(':u2', $target_id);
                $stmt->execute();
                $message = "Solicitud enviada correctamente.";
            }
        } elseif ($action === 'accept') {
            $stmt = $conn->prepare("UPDATE tbl_amistades SET estado = 'aceptada' WHERE id = :id AND id_usuario2 = :me");
            $stmt->bindParam(':id', $target_id); // Aquí target_id es el ID de la amistad, no del usuario
            $stmt->bindParam(':me', $my_id);
            $stmt->execute();
            $message = "Solicitud aceptada. ¡Ahora sois amigos!";
        } elseif ($action === 'deny') {
            $stmt = $conn->prepare("DELETE FROM tbl_amistades WHERE id = :id AND (id_usuario1 = :me OR id_usuario2 = :me)");
            $stmt->bindParam(':id', $target_id); // ID de la amistad
            $stmt->bindParam(':me', $my_id);
            $stmt->execute();
            $message = "Solicitud cancelada/rechazada.";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// --------------------------------------------------------------------------------
// BÚSQUEDA DE USUARIOS
// --------------------------------------------------------------------------------
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . trim($_GET['search']) . '%';
    try {
        // Buscar usuarios que NO soy yo
        $stmt = $conn->prepare("
            SELECT u.id, u.username as nombre_usuario, CONCAT(u.nombre, ' ', u.apellidos) as nombre_real,
            (SELECT estado FROM tbl_amistades WHERE (id_usuario1 = u.id AND id_usuario2 = :me) OR (id_usuario1 = :me AND id_usuario2 = u.id)) as friendship_status
            FROM tbl_usuarios u 
            WHERE (u.username LIKE :search OR u.nombre LIKE :search OR u.apellidos LIKE :search) AND u.id != :me
        ");
        $stmt->bindParam(':search', $search);
        $stmt->bindParam(':me', $my_id);
        $stmt->execute();
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $search_results = [];
    }
}

// --------------------------------------------------------------------------------
// LISTAS (Amigos, Solicitudes pendientes)
// --------------------------------------------------------------------------------
try {
    // Mis amigos
    $stmt = $conn->prepare("
        SELECT u.id, u.username as nombre_usuario, u.email 
        FROM tbl_usuarios u
        JOIN tbl_amistades f ON (f.id_usuario1 = u.id OR f.id_usuario2 = u.id)
        WHERE (f.id_usuario1 = :me OR f.id_usuario2 = :me) 
          AND u.id != :me 
          AND f.estado = 'aceptada'
    ");
    $stmt->bindParam(':me', $my_id);
    $stmt->execute();
    $my_friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Solicitudes recibidas
    $stmt = $conn->prepare("
        SELECT f.id as friendship_id, u.id as user_id, u.username as nombre_usuario 
        FROM tbl_amistades f
        JOIN tbl_usuarios u ON f.id_usuario1 = u.id
        WHERE f.id_usuario2 = :me AND f.estado = 'pendiente'
    ");
    $stmt->bindParam(':me', $my_id);
    $stmt->execute();
    $requests_received = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Solicitudes enviadas
    $stmt = $conn->prepare("
        SELECT f.id as friendship_id, u.id as user_id, u.username as nombre_usuario 
        FROM tbl_amistades f
        JOIN tbl_usuarios u ON f.id_usuario2 = u.id
        WHERE f.id_usuario1 = :me AND f.estado = 'pendiente'
    ");
    $stmt->bindParam(':me', $my_id);
    $stmt->execute();
    $requests_sent = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $my_friends = [];
    $my_friends = [];
    $requests_received = [];
    $requests_sent = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Amigos - Foro</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <nav>
        <a href="index.php" class="logo">TBForo</a>
        <ul class="nav-links">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="crear_pregunta.php">Nueva Pregunta</a></li>
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="friends.php" class="active" style="color: var(--color-orange);">Amigos</a></li>
            <li><a href="chat.php">Chat</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li class="welcome-text">Bienvenido <?= htmlspecialchars($_SESSION['username'] ?? '') ?></li>
                <li><a href="view/logout.php">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="view/login.php">Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="friends-container">
    <h1 class="text-center">Gestionar Amigos</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Buscador -->
    <div class="card">
        <h3 class="sidebar-title">Buscar Usuarios</h3>
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Nombre de usuario..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>

        <?php if (!empty($search_results)): ?>
            <div class="grid">
                <?php foreach ($search_results as $user): ?>
                    <div class="user-card">
                        <div class="user-info">
                            <h3><?= htmlspecialchars($user['nombre_usuario']) ?></h3>
                            <p><?= htmlspecialchars($user['nombre_real']) ?></p>
                        </div>
                        <div class="actions">
                            <?php if ($user['friendship_status'] === 'aceptada'): ?>
                                <span class="badge">Amigos</span>
                                <a href="chat.php?chat_with=<?= $user['id'] ?>" class="btn btn-primary btn-sm">Chatear 💬</a>
                            <?php elseif ($user['friendship_status'] === 'pendiente'): ?>
                                <span class="tag">Solicitud Pendiente</span>
                            <?php endif; ?>
                            <a href="perfil.php?id=<?= $user['id'] ?>" class="btn btn-ghost btn-sm">Ver perfil</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_GET['search'])): ?>
            <p class="text-center empty-messages">No se encontraron usuarios.</p>
        <?php endif; ?>
    </div>

    <!-- Solicitudes Pendientes -->
    <?php if (!empty($requests_received)): ?>
        <h2 class="section-title">Solicitudes Recibidas</h2>
        <div class="grid">
            <?php foreach ($requests_received as $req): ?>
                <div class="user-card user-card-highlighted">
                    <div class="user-info">
                        <h3><?= htmlspecialchars($req['nombre_usuario']) ?></h3>
                        <p>Quiere ser tu amigo</p>
                    </div>
                    <div class="actions">
                        <a href="?action=accept&id=<?= $req['friendship_id'] ?>" class="btn btn-primary btn-sm">Aceptar</a>
                        <a href="?action=deny&id=<?= $req['friendship_id'] ?>" class="btn btn-ghost btn-sm">Rechazar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Solicitudes Enviadas -->
    <?php if (!empty($requests_sent)): ?>
        <h2 class="section-title">Solicitudes Enviadas</h2>
        <div class="grid">
            <?php foreach ($requests_sent as $req): ?>
                <div class="user-card" style="opacity: 0.8;">
                    <div class="user-info">
                        <h3><?= htmlspecialchars($req['nombre_usuario']) ?></h3>
                        <p>Solicitud enviada</p>
                    </div>
                    <div class="actions">
                        <a href="?action=deny&id=<?= $req['friendship_id'] ?>" class="btn btn-ghost btn-sm">Cancelar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Mis Amigos -->
    <h2 class="section-title">Mis Amigos</h2>
    <?php if (empty($my_friends)): ?>
        <p class="text-center empty-messages">No tienes amigos aún. ¡Busca a alguien arriba!</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($my_friends as $friend): ?>
                <div class="user-card">
                    <div class="user-info">
                        <h3><?= htmlspecialchars($friend['nombre_usuario']) ?></h3>
                        <p><?= htmlspecialchars($friend['email']) ?></p>
                    </div>
                    <div class="actions">
                        <a href="chat.php?chat_with=<?= $friend['id'] ?>" class="btn btn-primary btn-sm">Chatear 💬</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
