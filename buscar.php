<?php
session_start();
require_once 'config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: view/login.php");
    exit;
}

$my_id = $_SESSION['user_id'];
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (!empty($search_query)) {
    try {
        $search = '%' . $search_query . '%';
        $stmt = $conn->prepare("
            SELECT u.id, u.username as nombre_usuario, CONCAT(u.nombre, ' ', u.apellidos) as nombre_real, u.email,
            (SELECT estado FROM tbl_amistades WHERE (id_usuario1 = u.id AND id_usuario2 = :me) OR (id_usuario1 = :me AND id_usuario2 = u.id)) as friendship_status
            FROM tbl_usuarios u 
            WHERE (u.username LIKE :search OR u.nombre LIKE :search OR u.apellidos LIKE :search) AND u.id != :me
            LIMIT 20
        ");
        $stmt->bindParam(':search', $search);
        $stmt->bindParam(':me', $my_id);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error en la búsqueda: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Usuarios - Foro</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <nav>
        <div class="logo">Foro<span class="username-highlight">Chat</span></div>
        <ul class="nav-links">
            <li><a href="questions.php">Foro</a></li>
            <li><a href="friends.php">Amigos</a></li>
            <li><a href="chat.php">Chat</a></li>
            <li><a href="buscar.php">Buscar</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="friends-container">
        <h1 class="text-center">Buscar Usuarios</h1>
        
        <!-- Buscador -->
        <div class="card">
            <form method="GET" class="search-box">
                <input type="text" name="q" placeholder="Busca por nombre de usuario o nombre real..." value="<?= htmlspecialchars($search_query) ?>" required>
                <button type="submit" class="btn btn-primary">🔍 Buscar</button>
            </form>
        </div>
        
        <?php if (!empty($search_query)): ?>
            <h2 class="section-title">Resultados de búsqueda: "<?= htmlspecialchars($search_query) ?>"</h2>
            
            <?php if (!empty($results)): ?>
                <div class="grid">
                    <?php foreach ($results as $user): ?>
                        <div class="user-card">
                            <div class="user-info">
                                <h3><?= htmlspecialchars($user['nombre_usuario']) ?></h3>
                                <p><?= htmlspecialchars($user['nombre_real']) ?></p>
                            </div>
                            <div class="actions">
                                <?php if ($user['friendship_status'] === 'aceptada'): ?>
                                    <span class="badge">✓ Amigos</span>
                                <?php elseif ($user['friendship_status'] === 'pendiente'): ?>
                                    <span class="tag">Solicitud Pendiente</span>
                                <?php endif; ?>
                                <a href="perfil.php?id=<?= $user['id'] ?>" class="btn btn-primary btn-sm">Ver perfil</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center empty-messages">
                    No se encontraron usuarios con ese criterio de búsqueda.
                </p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-center empty-messages">
                Utiliza el buscador para encontrar usuarios por su nombre de usuario o nombre real.
            </p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
